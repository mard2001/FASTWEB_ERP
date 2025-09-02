// User Management JavaScript
// Following the same patterns as other modules with pre-loaded data

let usersData = [];
let filteredUsers = [];
let currentEditingUser = null;
let usersTable = null;

// Initialize page when DOM is loaded
$(document).ready(function() {
    initializeUserManagement();
    setupEventListeners();
    loadUsersData();
});

function initializeUserManagement() {
    // Initialize VirtualSelect for filters
    VirtualSelect.init({
        ele: '#userType_VS',
        options: [
            { label: 'All Types', value: '' },
            { label: 'User', value: 'user' },
            { label: 'Admin', value: 'admin' },
            { label: 'Super Admin', value: 'super_admin' },
            { label: 'Developer', value: 'developer' }
        ],
        placeholder: 'Select User Type'
    });

    VirtualSelect.init({
        ele: '#userStatus_VS',
        options: [
            { label: 'All Status', value: '' },
            { label: 'Verified', value: 'verified' },
            { label: 'Unverified', value: 'unverified' }
        ],
        placeholder: 'Select Status'
    });

    // Generate initial password
    generatePassword();
}

function setupEventListeners() {
    // Filter event listeners
    $('#userType_VS').on('change', filterUsers);
    $('#userStatus_VS').on('change', filterUsers);
    $('#searchUser').on('input', filterUsers);

    // Modal event listeners
    $('#saveEdit').on('click', function() {
        if ($(this).text() === 'Edit details') {
            // Switch to edit mode
            $(this).text('Save Changes').removeClass('btn-info').addClass('btn-primary');
            $('#deleteBtn').text('Cancel');
            UserModal.setTitle('Edit User', 'Modify user account details');
            UserModal.enable(true);
        } else {
            // Save the user
            saveUser();
        }
    });
    $('#addBtn').on('click', openAddUserModal);
    
    // Add button click handler
    $('#addBtn').on('click', async function () {
        UserModal.enable(true);
        UserModal.clear();
        
        $('#deleteBtn').hide();
        $('#saveEdit').show();
        $('#saveEdit').text('Add User');
        
        UserModal.show();
    });
    
    // Edit functionality is now handled by the saveEdit button when in edit mode
    // The saveEdit button will handle both add and edit operations
    
    // Delete button click handler
    $("#deleteBtn").on("click", async function () {
        if ($(this).text().toLowerCase() == 'cancel') {
            $(this).text('Delete');
            $('#saveEdit').removeClass('btn-primary').addClass('btn-info');
            $('#saveEdit').text('Edit details');
            
            UserModal.fill(currentEditingUser);
            UserModal.setTitle('User Details', `View and manage ${currentEditingUser.name}'s account`);
            UserModal.enable(false);
        } else {
            Swal.fire({
                title: "Are you sure?",
                text: "You won't be able to revert this!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "var(--danger-color, #d33)",
                cancelButtonColor: "var(--primary-color, #3085d6)",
                confirmButtonText: "Yes, delete it!"
            }).then(async (result) => {
                if (result.isConfirmed) {
                    await deleteUser(currentEditingUser.id);
                }
            });
        }
    });

    // Form validation
    $('#firstName, #lastName, #email, #mobile, #userType').on('input change', validateForm);
}

// Edit user function
function editUser(userId) {
    const token = localStorage.getItem('api_token');
    if (!token) {
        Swal.fire({
            title: 'Authentication Required',
            text: 'Please log in to continue.',
            icon: 'warning',
            confirmButtonText: 'Go to Login'
        }).then(() => {
            window.location.href = '/login';
        });
        return;
    }

    const user = usersData.find(u => u.id === userId);
    if (!user) {
        Swal.fire('Error', 'User not found', 'error');
        return;
    }
    
    currentEditingUser = user;
    
    // Split name into first and last name
    const nameParts = user.name.split(' ');
    const firstName = nameParts[0] || '';
    const lastName = nameParts.slice(1).join(' ') || '';
    
    // Populate form fields
    $('#firstName').val(firstName);
    $('#lastName').val(lastName);
    $('#email').val(user.email);
    $('#mobile').val(user.mobile);
    $('#userType').val(user.user_type);
    
    // Change button text for editing
    $('#saveEdit').text('Update User');
    
    // Show modal
    $('#editXmlDataModal').modal('show');
}

// Delete user function
function deleteUser(userId) {
    const token = localStorage.getItem('api_token');
    if (!token) {
        Swal.fire({
            title: 'Authentication Required',
            text: 'Please log in to continue.',
            icon: 'warning',
            confirmButtonText: 'Go to Login'
        }).then(() => {
            window.location.href = '/login';
        });
        return;
    }

    const user = usersData.find(u => u.id === userId);
    if (!user) {
        Swal.fire('Error', 'User not found', 'error');
        return;
    }
    
    // Remove the duplicate confirmation dialog - it's already handled in the button click handler
    $.ajax({
                url: `/api/users/${userId}`,
                method: 'DELETE',
                headers: {
                    'Authorization': 'Bearer ' + token,
                    'Content-Type': 'application/json'
                },
                success: function(response) {
                    if (response.response_stat === 1) {
                        Swal.fire('Deleted!', 'User has been deleted.', 'success');
                        $('#editXmlDataModal').modal('hide');
                        loadUsersData();
                        currentEditingUser = null;
                    } else {
                        Swal.fire('Error', response.message || 'Failed to delete user', 'error');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error deleting user:', xhr.responseText || error);
                    
                    if (xhr.status === 401) {
                        Swal.fire({
                            title: 'Session Expired',
                            text: 'Your session has expired. Please log in again.',
                            icon: 'warning',
                            confirmButtonText: 'Go to Login'
                        }).then(() => {
                            localStorage.removeItem('api_token');
                            localStorage.removeItem('user');
                            window.location.href = '/login';
                        });
                    } else if (xhr.status === 403) {
                        Swal.fire({
                            title: 'Access Denied',
                            text: 'You do not have permission to delete users.',
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    } else {
                        Swal.fire('Error', 'Failed to delete user: ' + (xhr.responseText || error), 'error');
                    }
                }
            });
}

function loadUsersData() {
    // Check if user is authenticated
    const token = localStorage.getItem('api_token');
    if (!token) {
        Swal.fire({
            title: 'Authentication Required',
            text: 'Please log in to access user management.',
            icon: 'warning',
            confirmButtonText: 'Go to Login'
        }).then(() => {
            window.location.href = '/login';
        });
        return;
    }

    $.ajax({
        url: '/api/users',
        method: 'GET',
        headers: {
            'Authorization': 'Bearer ' + token,
            'Content-Type': 'application/json'
        },
        success: function(response) {
            if (response.status_response === 1) {
                usersData = response.response;
                filteredUsers = [...usersData];
                updateDashboardStats();
                populateUsersTable();
            } else {
                console.error('Failed to load users:', response.response);
                Swal.fire('Error', 'Failed to load users', 'error');
            }
        },
        error: function(xhr, status, error) {
            console.error('Error loading users:', xhr.responseText || error);
            
            if (xhr.status === 401) {
                Swal.fire({
                    title: 'Session Expired',
                    text: 'Your session has expired. Please log in again.',
                    icon: 'warning',
                    confirmButtonText: 'Go to Login'
                }).then(() => {
                    localStorage.removeItem('api_token');
                    localStorage.removeItem('user');
                    window.location.href = '/login';
                });
            } else if (xhr.status === 403) {
                Swal.fire({
                    title: 'Access Denied',
                    text: 'You do not have permission to access user management. Please contact your administrator.',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            } else {
                Swal.fire('Error', 'Error loading users: ' + (xhr.responseText || error), 'error');
            }
        }
    });
}

function updateDashboardStats() {
    const totalUsers = usersData.length;
    const activeUsers = usersData.filter(user => user.email_verified_at !== null).length;
    
    // Calculate new users this month
    const currentMonth = new Date().getMonth();
    const currentYear = new Date().getFullYear();
    const newUsers = usersData.filter(user => {
        const createdDate = new Date(user.created_at);
        return createdDate.getMonth() === currentMonth && createdDate.getFullYear() === currentYear;
    }).length;

    $('#total-users').text(`${totalUsers} Users`);
    $('#active-users').text(`${activeUsers} Users`);
    $('#new-users').text(`${newUsers} Users`);
}

function populateUsersTable() {
    if (usersTable) {
        usersTable.clear().draw();
        usersTable.rows.add(filteredUsers).draw();
    } else {
        usersTable = $('#getXmlData').DataTable({
            data: filteredUsers,
            language: {
                searchPlaceholder: "Search users..."
            },
            columns: [
                { data: 'id', title: 'ID' },
                { data: 'name', title: 'NAME' },
                { data: 'email', title: 'EMAIL' },
                { 
                    data: 'mobile', 
                    title: 'MOBILE',
                    render: function(data, type, row) {
                        return `+63${data}`;
                    }
                },
                { 
                    data: 'user_type', 
                    title: 'USER TYPE',
                    render: function(data, type, row) {
                        const userTypeClass = {
                            'user': 'bg-primary',
                            'admin': 'bg-info',
                            'super_admin': 'bg-danger',
                            'developer': 'bg-dark'
                        }[data] || 'bg-secondary';
                        return `<span class="badge ${userTypeClass}">${data.replace('_', ' ').toUpperCase()}</span>`;
                    }
                },
                { 
                    data: 'created_at', 
                    title: 'CREATED AT',
                    render: function(data, type, row) {
                        return formatDateTime(data);
                    }
                },
                { 
                    data: 'updated_at', 
                    title: 'UPDATED AT',
                    render: function(data, type, row) {
                        return formatDateTime(data);
                    }
                }
            ],
            columnDefs: [
                { className: "text-center", targets: [0, 4] },
                { className: "text-start", targets: [1, 2, 3, 5, 6] },
                { className: "text-nowrap", targets: '_all' }
            ],
            scrollCollapse: true,
            scrollY: '100%',
            scrollX: '100%',
            "createdRow": function (row, data) {
                $(row).attr('id', data.id);
            },
            pageLength: 15,
            lengthChange: false,
            order: [[0, 'asc']],
            initComplete: function () {
                $(this.api().table().container()).find('#dt-search-0').addClass('p-1 mx-0 dtsearchInput nofocus');
                $(this.api().table().container()).find('.dt-search label').addClass('py-1 px-3 mx-0 dtsearchLabel').html('<span class="mdi mdi-magnify"></span>');
                $(this.api().table().container()).find('.dt-layout-row').first().find('.dt-layout-cell').each(function() { this.style.setProperty('height', '38px', 'important'); });
                $(this.api().table().container()).find('.dt-layout-table').removeClass('px-4');
                $(this.api().table().container()).find('.dt-scroll-body').addClass('rmvBorder');
                $(this.api().table().container()).find('.dt-layout-table').addClass('btmdtborder');
                $(this.api().table().container()).find('.dt-search').addClass('d-flex justify-content-end');
                
                $('.loadingScreen').remove();
                $('#dattableDiv').removeClass('opacity-0');
                
                // Add blue header to the table
                const tableDiv = $('.dt-layout-row').first();
                tableDiv.after('<div style="background: linear-gradient(to right, var(--primary-color, #1b438f), var(--secondary-color, #33336F)); color: var(--text-color-light, #FFF); margin-top:10px; padding: 10px 15px; border-top-left-radius:10px; border-top-right-radius: 10px;"><p style="margin:0px; color: var(--text-color-light, #FFF);">User Management</p></div>');
            }
        });
        
        // Add row click event handler similar to warehouse module
        $("#getXmlData").on("click", "tbody tr", async function () {
            $("#getXmlData tbody").css('pointer-events', 'none');
            const selectedUserId = $(this).attr('id');
            
            const user = usersData.find(u => u.id == selectedUserId);
            if (user) {
                UserModal.viewMode(user);
                currentEditingUser = user;
            } else {
                Swal.fire({
                    title: "Opppps..",
                    text: "User not found",
                    icon: "error"
                });
            }
            $("#getXmlData tbody").css('pointer-events', 'auto');
        });
    }
}

function filterUsers() {
    const userTypeFilter = $('#userType_VS').val();
    const statusFilter = $('#userStatus_VS').val();
    const searchTerm = $('#searchUser').val().toLowerCase();

    filteredUsers = usersData.filter(user => {
        const matchesType = !userTypeFilter || user.user_type === userTypeFilter;
        const matchesStatus = !statusFilter || 
            (statusFilter === 'verified' && user.email_verified_at !== null) ||
            (statusFilter === 'unverified' && user.email_verified_at === null);
        const matchesSearch = !searchTerm || 
            user.name.toLowerCase().includes(searchTerm) ||
            user.email.toLowerCase().includes(searchTerm);

        return matchesType && matchesStatus && matchesSearch;
    });

    populateUsersTable();
}

function openAddUserModal() {
    currentEditingUser = null;
    
    // Clear form
    $('#firstName').val('');
    $('#lastName').val('');
    $('#email').val('');
    $('#mobile').val('');
    $('#userType').val('');
    
    // Change button text for adding
    $('#saveEdit').text('Add User');
    
    // Show modal
    $('#editXmlDataModal').modal('show');
}

// Duplicate functions removed - using the API-integrated versions above

function saveUser() {
    if (!validateForm()) {
        return;
    }

    const token = localStorage.getItem('api_token');
    if (!token) {
        Swal.fire({
            title: 'Authentication Required',
            text: 'Please log in to continue.',
            icon: 'warning',
            confirmButtonText: 'Go to Login'
        }).then(() => {
            window.location.href = '/login';
        });
        return;
    }

    let mobileNumber = $('#mobile').val().trim();
    // If mobile number is 10 digits, add '0' prefix to make it 11 digits
    if (mobileNumber.length === 10 && /^\d{10}$/.test(mobileNumber)) {
        mobileNumber = '0' + mobileNumber;
    }

    const userData = {
        firstName: $('#firstName').val(),
        lastName: $('#lastName').val(),
        email: $('#email').val(),
        mobile: mobileNumber,
        userType: $('#userType').val()
    };

    const url = currentEditingUser ? `/api/users/${currentEditingUser.id}` : '/api/users';
    const method = currentEditingUser ? 'PUT' : 'POST';

    $.ajax({
        url: url,
        method: method,
        headers: {
            'Authorization': 'Bearer ' + token,
            'Content-Type': 'application/json'
        },
        data: JSON.stringify(userData),
        success: function(response) {
            if (response.response_stat === 1) {
                Swal.fire({
                    title: 'Success!',
                    text: currentEditingUser ? 'User updated successfully!' : 'User created successfully!',
                    icon: 'success'
                }).then(() => {
                    $('#editXmlDataModal').modal('hide');
                    loadUsersData();
                    clearForm();
                    currentEditingUser = null;
                });
            } else {
                Swal.fire('Error', response.message || 'Failed to save user', 'error');
            }
        },
        error: function(xhr, status, error) {
            console.error('Error saving user:', xhr.responseText || error);
            
            if (xhr.status === 401) {
                Swal.fire({
                    title: 'Session Expired',
                    text: 'Your session has expired. Please log in again.',
                    icon: 'warning',
                    confirmButtonText: 'Go to Login'
                }).then(() => {
                    localStorage.removeItem('api_token');
                    localStorage.removeItem('user');
                    window.location.href = '/login';
                });
            } else if (xhr.status === 403) {
                Swal.fire({
                    title: 'Access Denied',
                    text: 'You do not have permission to perform this action.',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            } else {
                Swal.fire('Error', 'Failed to save user: ' + (xhr.responseText || error), 'error');
            }
        }
    });
}

function validateForm() {
    const firstName = $('#firstName').val().trim();
    const lastName = $('#lastName').val().trim();
    const email = $('#email').val().trim();
    const mobile = $('#mobile').val().trim();
    const userType = $('#userType').val();

    let isValid = true;

    // Reset validation states
    $('.form-control, .form-select').removeClass('is-invalid');

    if (!firstName) {
        $('#firstName').addClass('is-invalid');
        isValid = false;
    }

    if (!lastName) {
        $('#lastName').addClass('is-invalid');
        isValid = false;
    }

    if (!email || !isValidEmail(email)) {
        $('#email').addClass('is-invalid');
        isValid = false;
    }

    if (!mobile || mobile.length !== 10) {
        $('#mobile').addClass('is-invalid');
        isValid = false;
    }

    if (!userType) {
        $('#userType').addClass('is-invalid');
        isValid = false;
    }

    // Check for duplicate email (exclude current user when editing)
    const existingEmailUser = usersData.find(u => 
        u.email.toLowerCase() === email.toLowerCase() && 
        (!currentEditingUser || u.id !== currentEditingUser.id)
    );
    
    if (existingEmailUser) {
        $('#email').addClass('is-invalid');
        Swal.fire('Error', 'Email address already exists.', 'error');
        isValid = false;
    }

    // Check for duplicate mobile (handle both 10 and 11 digit formats)
    let mobileToCheck = mobile;
    if (mobile.length === 10 && /^\d{10}$/.test(mobile)) {
        mobileToCheck = '0' + mobile;
    }
    
    const existingMobileUser = usersData.find(u => 
        u.mobile === mobileToCheck && 
        (!currentEditingUser || u.id !== currentEditingUser.id)
    );
    
    if (existingMobileUser) {
        $('#mobile').addClass('is-invalid');
        Swal.fire('Error', 'Mobile number already exists.', 'error');
        isValid = false;
    }

    return isValid;
}

function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

function generatePassword() {
    const length = 12;
    const charset = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
    let password = '';
    
    for (let i = 0; i < length; i++) {
        password += charset.charAt(Math.floor(Math.random() * charset.length));
    }
    
    $('#password').val(password);
}

function togglePasswordVisibility() {
    const passwordField = $('#password');
    const toggleIcon = $('#togglePassword i');
    
    if (passwordField.attr('type') === 'password') {
        passwordField.attr('type', 'text');
        toggleIcon.removeClass('mdi-eye').addClass('mdi-eye-off');
    } else {
        passwordField.attr('type', 'password');
        toggleIcon.removeClass('mdi-eye-off').addClass('mdi-eye');
    }
}

function clearForm() {
    $('#firstName, #lastName, #email, #mobile, #password').val('');
    $('#userType').val('');
    $('#sendCredentials').prop('checked', true);
}

// UserModal object similar to WHModal in warehouse module
const UserModal = {
    isValid: () => {
        return $('#modalFields')[0].checkValidity();
    },
    hide: () => {
        $('#editXmlDataModal').modal('hide');
    },
    show: () => {
        $('#editXmlDataModal').modal('show');
    },
    setTitle: (title, subtitle) => {
        $('.modalHeaderTitle').text(title);
        if (subtitle) {
            $('.modalHeaderTitle').next('small').text(subtitle);
        }
    },
    clear: () => {
        $('#firstName').val('');
        $('#lastName').val('');
        $('#email').val('');
        $('#mobile').val('');
        $('#userType').val('');
        $('#password').val('');
        $('#sendCredentials').prop('checked', true);
        
        // Clear validation states
        $('.form-control, .form-select').removeClass('is-invalid');
        
        // Generate new password
        generatePassword();
    },
    enable: (enable) => {
        $('#firstName').prop('disabled', !enable);
        $('#lastName').prop('disabled', !enable);
        $('#email').prop('disabled', !enable);
        $('#mobile').prop('disabled', !enable);
        $('#userType').prop('disabled', !enable);
        $('#password').prop('disabled', !enable);
        $('#sendCredentials').prop('disabled', !enable);
    },
    viewMode: async (userData) => {
        UserModal.setTitle('User Details', `View and manage ${userData.name}'s account`);
        UserModal.fill(userData);
        
        // Get current user role from localStorage
        const currentUser = JSON.parse(localStorage.getItem('user') || '{}');
        const currentUserType = currentUser.user_type || 'user';
        
        // Hide edit and delete buttons if current user is admin
        if (currentUserType === 'admin') {
            $('#deleteBtn').hide();
            $('#saveEdit').hide();
        } else {
            $('#deleteBtn').show();
            $('#saveEdit').show();
            $('#saveEdit').text('Edit details').removeClass('btn-primary').addClass('btn-info');
            $('#deleteBtn').text('Delete');
        }
        
        UserModal.enable(false);
        UserModal.show();
    },
    fill: async (userData) => {
        // Split name into first and last name
        const nameParts = userData.name.split(' ');
        const firstName = nameParts[0] || '';
        const lastName = nameParts.slice(1).join(' ') || '';
        
        $('#firstName').val(firstName);
        $('#lastName').val(lastName);
        $('#email').val(userData.email);
        $('#mobile').val(userData.mobile);
        $('#userType').val(userData.user_type);
        $('#password').val(''); // Don't show existing password
        $('#sendCredentials').prop('checked', false);
    },
    getData: () => {
        const firstName = $('#firstName').val().trim();
        const lastName = $('#lastName').val().trim();
        const name = `${firstName} ${lastName}`.trim();
        
        return {
            name: name,
            email: $('#email').val().trim(),
            mobile: $('#mobile').val().trim(),
            user_type: $('#userType').val(),
            password: $('#password').val(),
            send_credentials: $('#sendCredentials').is(':checked')
        };
    }
};
    $('.form-control, .form-select').removeClass('is-invalid');


function formatDateTime(dateString) {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleDateString() + ' ' + date.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
}
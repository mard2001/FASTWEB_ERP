$(document).ready(function() {
    // Initialize Virtual Select components
    initializeVirtualSelects();
    
    // Initialize DataTable variable
    let MainTH;
    
    // Initialize Virtual Select dropdowns
    function initializeVirtualSelects() {
        // Activity Type Virtual Select
        VirtualSelect.init({
            ele: '#activityType_VS',
            options: [
                {label: 'All Activities', value: ''},
                {label: 'Created', value: 'created'},
                {label: 'Updated', value: 'updated'},
                {label: 'Deleted', value: 'deleted'},
                {label: 'Confirmed', value: 'confirmed'},
                {label: 'Login', value: 'login'},
                {label: 'Logout', value: 'logout'},
                {label: '--- Sales Orders ---', value: '', disabled: true},
                {label: 'SO Created', value: 'so_created'},
                {label: 'SO Updated', value: 'so_updated'},
                {label: 'SO Available', value: 'so_available'},
                {label: 'SO Unavailable', value: 'so_unavailable'},
                {label: 'SO Restocked', value: 'so_restocked'},
                {label: 'SO Suspense', value: 'so_suspense'},
                {label: 'SO To Invoice', value: 'so_invoice'},
                {label: 'SO Completed', value: 'so_completed'},
                {label: 'SO Deleted', value: 'so_deleted'}
            ],
            placeholder: 'Select Activity Type',
            search: true,
            hasOptionDescription: false
        });

        // Module Virtual Select
        VirtualSelect.init({
            ele: '#subjectType_VS',
            options: [
                {label: 'All Modules', value: ''},
                {label: 'Purchase Orders', value: 'App\\Models\\Orders\\PO'},
                {label: 'Sales Orders', value: 'App\\Models\\SalesOrder\\SO'},
                {label: 'User Management', value: 'App\\Models\\User'},
                {label: 'Customers', value: 'App\\Models\\Customer\\Customer'},
                {label: 'Suppliers', value: 'App\\Models\\Supplier'},
                {label: 'Receiving Report', value: 'App\\Models\\ReceivingReports\\ReceivingRHeader'}
            ],
            placeholder: 'Select Module',
            search: true,
            hasOptionDescription: false
        });
    }

    // Initialize Activity Log DataTable (following inventory movements pattern)
    const initActivityLogDatatable = (response) => {
        if (response.success) {
            if (MainTH) {
                MainTH.clear().draw();
                MainTH.rows.add(response.data).draw();
            } else {
                MainTH = $('#ActivityLogTable').DataTable({
                    data: response.data,
                    language: {
                        searchPlaceholder: "Search here..."
                    },
                    columns: [
                        { 
                            data: 'created_at', 
                            title: 'Date & Time',
                            render: function (data, type, row) {
                                if (!data) return '';
                                // Convert UTC time to local time
                                const dateObj = new Date(data + (data.includes('Z') ? '' : 'Z'));
                                if (type === 'display' || type === 'filter') {
                                    return dateObj.toLocaleString('en-GB', {
                                        day: '2-digit',
                                        month: 'short',
                                        year: 'numeric',
                                        hour: '2-digit',
                                        minute: '2-digit',
                                        hour12: false,
                                        timeZone: Intl.DateTimeFormat().resolvedOptions().timeZone
                                    });
                                }
                                return dateObj.toISOString();
                            }
                        },
                        { 
                            data: 'causer_name', 
                            title: 'User',
                            render: function (data, type, row) {
                                console.log('User column data:', data, 'Full row:', row); // Debug log
                                if (!data || data === null || data === undefined) {
                                    return '<span style="font-size:10px; color:#808080;">System</span>';
                                }
                                return `<span class="fw-bold">${data}</span>`;
                            }
                        },
                        { 
                            data: 'event', 
                            title: 'Activity',
                            render: function (data, type, row) {
                                // If no event, try to detect from description for Sales Orders
                                if (!data || data === null || data === undefined || data === '') {
                                    // Check if this is a sales order activity by looking at log_name or description
                                    if (row.log_name === 'sales_order' && row.description) {
                                        console.log('Detecting SO event for:', row.description); // Debug log
                                        const detectedEvent = detectSalesOrderEvent(row.description);
                                        if (detectedEvent) {
                                            console.log('Detected event:', detectedEvent); // Debug log
                                            const badgeClass = getActivityBadgeClass(detectedEvent.event);
                                            return `<span class="${badgeClass}">${detectedEvent.label}</span>`;
                                        }
                                    }
                                    return '<span style="font-size:10px; color:#808080;">Activity</span>';
                                }
                                const badgeClass = getActivityBadgeClass(data);
                                return `<span class="${badgeClass}">${capitalizeFirst(data)}</span>`;
                            }
                        },
                        { 
                            data: 'subject_type', 
                            title: 'Module',
                            render: function (data, type, row) {
                                // If no subject_type but it's a sales order activity, show Sales Orders
                                if (!data && row.log_name === 'sales_order') {
                                    return 'Sales Orders';
                                }
                                if (!data) return '<span style="font-size:10px; color:#808080;">---</span>';
                                return getModuleName(data);
                            }
                        },
                        { 
                            data: 'description', 
                            title: 'Description',
                            render: function (data, type, row) {
                                if (!data) return '<span style="font-size:10px; color:#808080;">---</span>';
                                if (data.length > 50) {
                                    return data.substring(0, 50) + '...';
                                }
                                return data;
                            }
                        },
                        { 
                            data: 'properties', 
                            title: 'OS & Browser',
                            render: function (data, type, row) {
                                let properties = {};
                                try {
                                    if (typeof data === 'string') {
                                        properties = JSON.parse(data || '{}');
                                    } else if (typeof data === 'object' && data !== null) {
                                        properties = data;
                                    }
                                } catch (e) {
                                    console.warn('Failed to parse properties:', data);
                                    properties = {};
                                }
                                const userAgent = properties.user_agent || 'Unknown';
                                return generateUserAgentIcons(userAgent);
                            }
                        },
                        { 
                            data: 'properties', 
                            title: 'IP Address',
                            render: function (data, type, row) {
                                let properties = {};
                                try {
                                    if (typeof data === 'string') {
                                        properties = JSON.parse(data || '{}');
                                    } else if (typeof data === 'object' && data !== null) {
                                        properties = data;
                                    }
                                } catch (e) {
                                    console.warn('Failed to parse properties:', data);
                                    properties = {};
                                }
                                const ip = properties.ip || 'Unknown';
                                return ip;
                            }
                        }
                    ],
                    columnDefs: [
                        { className: "text-start", targets: [0, 1, 4, 5] },
                        { className: "text-center", targets: [2, 3, 6] },
                        { className: "text-nowrap", targets: '_all' }
                    ],
                    scrollCollapse: true,
                    scrollY: '100%',
                    scrollX: '100%',
                    "createdRow": function (row, data) {
                        $(row).attr('id', 'activity-' + data.id);
                        $(row).attr('data-id', data.id);
                        $(row).addClass('clickable-row');
                        $(row).css('cursor', 'pointer');
                        $(row).attr('title', 'Click to view details');
                    },
                    "pageLength": 25,
                    "lengthChange": false,
                    order: [[0, 'desc']],
                    initComplete: function () {
                        $(this.api().table().container()).find('#dt-search-0').addClass('p-1 mx-0 dtsearchInput nofocus');
                        $(this.api().table().container()).find('.dt-search label').addClass('py-1 px-3 mx-0 dtsearchLabel').html('<span class="mdi mdi-magnify"></span>');
                        $(this.api().table().container()).find('.dt-layout-row').first().find('.dt-layout-cell').each(function() { this.style.setProperty('height', '38px', 'important'); });
                        $(this.api().table().container()).find('.dt-layout-table').removeClass('px-4');
                        $(this.api().table().container()).find('.dt-scroll-body').addClass('rmvBorder');
                        $(this.api().table().container()).find('.dt-layout-table').addClass('btmdtborder');

                        const dtlayoutTE = $('.dt-layout-cell.dt-end').first();
                        dtlayoutTE.addClass('d-flex justify-content-end');
                        dtlayoutTE.prepend('<div id="filterActivityVS" name="filter" style="width: 150px" class="bg-white p-0 mx-1">Filter</div>');
                        $(this.api().table().container()).find('.dt-search').addClass('d-flex justify-content-end');
                        $('.loadingScreen').remove();
                        $('#dattableDiv').removeClass('opacity-0');

                        const tableDiv = $('.dt-layout-row').first();
                        tableDiv.after('<div style="background: linear-gradient(to right, #1b438f, #33336F ); color: #FFF; margin-top:10px; padding: 10px 15px; border-top-left-radius:10px; border-top-right-radius: 10px;"><p style="margin:0px">Activity Log Report</p></div>');
                    }
                });
            }
            if (Swal.isVisible()) {
                Swal.close();
            }
        }
    };

    // Load Activity Logs Data
    function loadActivityLogsData() {
        const filters = {
            date_from: $('#dateFrom').val(),
            date_to: $('#dateTo').val(),
            activity_type: $('#activityType_VS')[0]?.value || '',
            subject_type: $('#subjectType_VS')[0]?.value || '',
            user_name: $('#userName').val()
        };

        $.ajax({
            url: '/api/activity-log',
            type: 'GET',
            data: filters,
            success: function(response) {
                console.log('API Response:', response); // Debug log
                // Update statistics if provided
                if (response.statistics) {
                    updateStatistics(response.statistics);
                }
                // Initialize/update DataTable
                initActivityLogDatatable(response);
            },
            error: function(xhr, error, code) {
                console.error('Error loading activity logs:', error);
                
                try {
                    const response = JSON.parse(xhr.responseText);
                    if (response.error === 'TABLE_NOT_FOUND') {
                        showTableNotFoundMessage();
                        return;
                    }
                    showAlert('Error loading activity logs: ' + (response.message || 'Please try again.'), 'error');
                } catch (e) {
                    showAlert('Error loading activity logs. Please try again.', 'error');
                }
            }
        });
    }

    // Initialize statistics
    function updateStatistics(stats) {
        if (stats) {
            $('#total-activities').text((stats.total_activities || 0) + ' Activities');
            $('#today-activities').text((stats.today_activities || 0) + ' Today');
            $('#unique-users').text((stats.unique_users || 0) + ' Users');
            $('#total-logins').text((stats.total_logins || 0) + ' Logins');
        }
    }

    // Load initial statistics
    function loadStatistics() {
        $.ajax({
            url: '/api/activity-log/statistics',
            type: 'GET',
            success: function(response) {
                if (response.success) {
                    updateStatistics(response.data);
                } else {
                    // Handle table not found error
                    if (response.error === 'TABLE_NOT_FOUND') {
                        showTableNotFoundMessage();
                        updateStatistics({
                            total_activities: 0,
                            today_activities: 0,
                            unique_users: 0,
                            total_logins: 0
                        });
                    } else {
                        console.error('Error loading statistics:', response.message);
                        showAlert('Error loading statistics: ' + response.message, 'error');
                    }
                }
            },
            error: function(xhr, error, code) {
                console.error('Error loading statistics:', error);
                
                // Try to parse error response
                let errorMessage = 'Error loading statistics';
                try {
                    const response = JSON.parse(xhr.responseText);
                    if (response.error === 'TABLE_NOT_FOUND') {
                        showTableNotFoundMessage();
                        updateStatistics({
                            total_activities: 0,
                            today_activities: 0,
                            unique_users: 0,
                            total_logins: 0
                        });
                        return;
                    }
                    errorMessage = response.message || errorMessage;
                } catch (e) {
                    // Use default error message
                }
                
                showAlert(errorMessage, 'error');
            }
        });
    }

    // Get badge class for activity type
    function getActivityBadgeClass(event) {
        const badgeMap = {
            'created': 'statusBadge1',      // Green - success/created
            'updated': 'statusBadge3',      // Orange - warning/updated  
            'deleted': 'statusBadge2',      // Red - danger/deleted
            'confirmed': 'statusBadge1',    // Green - success/confirmed
            'login': 'statusBadge4',        // Blue - info/login
            'logout': 'statusBadge3',       // Orange - warning/logout
            
            // Sales Order specific activities
            'so_created': 'statusBadge1',     // Green - SO created
            'so_updated': 'statusBadge3',     // Orange - SO updated  
            'so_available': 'statusBadge1',   // Green - Available
            'so_unavailable': 'statusBadge3', // Orange - Not Available
            'so_restocked': 'statusBadge1',   // Green - Restocked
            'so_suspense': 'statusBadge3',    // Orange - Suspense
            'so_invoice': 'statusBadge4',     // Blue - To Invoice
            'so_completed': 'statusBadge1',   // Green - Completed
            'so_deleted': 'statusBadge2',     // Red - Deleted
            'so_activity': 'statusBadge4'     // Blue - General SO activity
        };
        return badgeMap[event] || 'statusBadge4';  // Default to blue
    }

    // Get module name from class path
    function getModuleName(subjectType) {
        const moduleMap = {
            'App\\Models\\Orders\\PO': 'Purchase Orders',
            'App\\Models\\SalesOrder\\SO': 'Sales Orders',
            'App\\Models\\SalesOrder\\SOMaster': 'Sales Orders',
            'App\\Models\\User': 'User Management',
            'App\\Models\\Customer\\Customer': 'Customers',
            'App\\Models\\Supplier': 'Suppliers',
            'App\\Models\\Inventory\\Product': 'Products',
            'App\\Models\\Warehouse\\Warehouse': 'Warehouses',
            'App\\Models\\ReceivingReports\\ReceivingRHeader': 'Receiving Report'
        };
        return moduleMap[subjectType] || (subjectType ? subjectType.split('\\').pop() : '---');
    }

    // Capitalize first letter
    function capitalizeFirst(str) {
        if (!str || str === null || str === undefined) {
            return 'Unknown';
        }
        return str.charAt(0).toUpperCase() + str.slice(1);
    }

    // Detect Sales Order event type from description
    function detectSalesOrderEvent(description) {
        if (!description) return null;
        
        const desc = description.toLowerCase();
        
        // Check for different Sales Order activities
        if (desc.includes('created new sales order')) {
            return { event: 'so_created', label: 'Created' };
        }
        if (desc.includes('updated sales order')) {
            return { event: 'so_updated', label: 'Updated' };
        }
        if (desc.includes('available (in warehouse)')) {
            return { event: 'so_available', label: 'Available' };
        }
        if (desc.includes('not available') || desc.includes('open back order')) {
            return { event: 'so_unavailable', label: 'Unavailable' };
        }
        if (desc.includes('restocked') || desc.includes('release back order')) {
            return { event: 'so_restocked', label: 'Restocked' };
        }
        if (desc.includes('suspense order')) {
            return { event: 'so_suspense', label: 'Suspense' };
        }
        if (desc.includes('proceed to invoice') || desc.includes('to invoice')) {
            return { event: 'so_invoice', label: 'To Invoice' };
        }
        if (desc.includes('completed sales order')) {
            return { event: 'so_completed', label: 'Completed' };
        }
        if (desc.includes('deleted sales order')) {
            return { event: 'so_deleted', label: 'Deleted' };
        }
        
        // Default for sales order activities
        return { event: 'so_activity', label: 'SO Activity' };
    }

    // Parse user agent to detect OS and Browser
    function parseUserAgent(userAgent) {
        if (!userAgent || userAgent === 'Unknown') {
            return {
                os: { name: 'Unknown', icon: '/assets/resources/windows.png', type: 'image' },
                browser: { name: 'Unknown', icon: '/assets/resources/chrome.png', type: 'image' }
            };
        }

        // OS Detection with PNG Images
        let os = { name: 'Unknown', icon: '/assets/resources/windows.png', type: 'image' };
        if (/Windows NT 10.0/.test(userAgent)) {
            os = { name: 'Windows 10/11', icon: '/assets/resources/windows.png', type: 'image' };
        } else if (/Windows NT 6.3/.test(userAgent)) {
            os = { name: 'Windows 8.1', icon: '/assets/resources/windows.png', type: 'image' };
        } else if (/Windows NT 6.2/.test(userAgent)) {
            os = { name: 'Windows 8', icon: '/assets/resources/windows.png', type: 'image' };
        } else if (/Windows NT 6.1/.test(userAgent)) {
            os = { name: 'Windows 7', icon: '/assets/resources/windows.png', type: 'image' };
        } else if (/Windows/.test(userAgent)) {
            os = { name: 'Windows', icon: '/assets/resources/windows.png', type: 'image' };
        } else if (/Mac OS X|macOS/.test(userAgent)) {
            os = { name: 'macOS', icon: '/assets/resources/apple.png', type: 'image' };
        } else if (/Linux/.test(userAgent)) {
            os = { name: 'Linux', icon: '/assets/resources/android.png', type: 'image' }; // Use Android icon as fallback for Linux
        } else if (/Android/.test(userAgent)) {
            os = { name: 'Android', icon: '/assets/resources/android.png', type: 'image' };
        } else if (/iPhone|iPad|iPod/.test(userAgent)) {
            os = { name: 'iOS', icon: '/assets/resources/apple.png', type: 'image' };
        }

        // Browser Detection with Professional Icons
        let browser = { name: 'Unknown', icon: '/assets/resources/chrome.png', type: 'image' };
        if (/Edg\//.test(userAgent)) {
            browser = { name: 'Microsoft Edge', icon: '/assets/resources/edge.png', type: 'image' };
        } else if (/Chrome\//.test(userAgent) && !/Edg\//.test(userAgent)) {
            browser = { name: 'Chrome', icon: '/assets/resources/chrome.png', type: 'image' };
        } else if (/Firefox\//.test(userAgent)) {
            browser = { name: 'Firefox', icon: '/assets/resources/firefox.png', type: 'image' };
        } else if (/Safari\//.test(userAgent) && !/Chrome/.test(userAgent)) {
            browser = { name: 'Safari', icon: '/assets/resources/safari.png', type: 'image' };
        } else if (/Opera|OPR\//.test(userAgent)) {
            browser = { name: 'Opera', icon: '/assets/resources/opera.png', type: 'image' }; // Fallback to Chrome icon if no Opera icon
        } else if (/Trident\//.test(userAgent)) {
            browser = { name: 'Internet Explorer', icon: '/assets/resources/edge.png', type: 'image' }; // Fallback to Edge icon
        }

        return { os, browser };
    }

    // Generate user agent icons display
    function generateUserAgentIcons(userAgent) {
        const parsed = parseUserAgent(userAgent);
        
        // Both OS and Browser icons are now images
        const osIcon = `<img src="${parsed.os.icon}" title="${parsed.os.name}" style="width: 20px; height: 20px; cursor: help;" alt="${parsed.os.name}">`;
        const browserIcon = `<img src="${parsed.browser.icon}" title="${parsed.browser.name}" style="width: 20px; height: 20px; cursor: help;" alt="${parsed.browser.name}">`;
        
        return `
            <div class="os-browser-icons">
                ${osIcon}
                ${browserIcon}
            </div>
        `;
    }

    // Show table not found message
    function showTableNotFoundMessage() {
        const message = `
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <h6 class="alert-heading"><i class="fas fa-exclamation-triangle me-2"></i>Activity Log Table Not Found</h6>
                <p class="mb-2">The activity log table has not been created yet. Please execute the SQL script to create the required database table.</p>
                <hr>
                <p class="mb-0">
                    <strong>Steps to fix:</strong><br>
                    1. Open SQL Server Management Studio (SSMS)<br>
                    2. Connect to your database server<br>
                    3. Open the file: <code>database/sql_scripts/create_activity_log_table.sql</code><br>
                    4. Execute the SQL script<br>
                    5. Refresh this page
                </p>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
        $('.container-fluid').prepend(message);
    }

    // Show alert message
    function showAlert(message, type = 'info') {
        const alertClass = type === 'error' ? 'alert-danger' : `alert-${type}`;
        const alert = `
            <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
        $('.container-fluid').prepend(alert);
        
        // Auto dismiss after 5 seconds
        setTimeout(function() {
            $('.alert').fadeOut();
        }, 5000);
    }

    // Auto-refresh when filters change
    function setupFilterEvents() {
        // Date inputs
        $('#dateFrom, #dateTo').on('change', function() {
            loadActivityLogsData();
            loadStatistics();
        });

        // User name input (with debounce)
        let userNameTimeout;
        $('#userName').on('input', function() {
            clearTimeout(userNameTimeout);
            userNameTimeout = setTimeout(function() {
                loadActivityLogsData();
                loadStatistics();
            }, 500);
        });

        // Virtual Select change events
        $('#activityType_VS').on('change', function() {
            loadActivityLogsData();
            loadStatistics();
        });

        $('#subjectType_VS').on('change', function() {
            loadActivityLogsData();
            loadStatistics();
        });
    }

    // View activity details on row click
    $(document).on('click', '.clickable-row', function() {
        const activityId = $(this).data('id');
        
        $.ajax({
            url: `/api/activity-log/${activityId}`,
            type: 'GET',
            success: function(response) {
                const activity = response.data;
                displayActivityDetails(activity);
                $('#activityDetailsModal').modal('show');
            },
            error: function(xhr, error, code) {
                console.error('Error loading activity details:', error);
                showAlert('Error loading activity details. Please try again.', 'error');
            }
        });
    });

    // Display activity details in modal
    function displayActivityDetails(activity) {
        let properties = {};
        try {
            if (typeof activity.properties === 'string') {
                properties = JSON.parse(activity.properties || '{}');
            } else if (typeof activity.properties === 'object' && activity.properties !== null) {
                properties = activity.properties;
            }
        } catch (e) {
            console.warn('Failed to parse activity properties:', activity.properties);
            properties = {};
        }
        
        const attributes = properties.attributes || {};
        const old = properties.old || {};
        
        let detailsHTML = `
            <div class="row g-4">
                <div class="col">
                    <table style="font-size: 14px">
                        <tbody>
                            <tr>
                                <td></td>
                                <th></th>
                            </tr>
                            <tr>
                                <td style="white-space: nowrap;">Activity ID:</td>
                                <th class="px-2"><span style="font-weight: 550">${activity.id}</span></th>
                            </tr>
                            <tr>
                                <td style="white-space: nowrap;">Date & Time:</td>
                                <th class="px-2"><span style="font-weight: 550">${(() => {
                                    const dateObj = new Date(activity.created_at + (activity.created_at.includes('Z') ? '' : 'Z'));
                                    return dateObj.toLocaleString('en-US', {
                                        year: 'numeric',
                                        month: 'short', 
                                        day: 'numeric',
                                        hour: '2-digit',
                                        minute: '2-digit',
                                        second: '2-digit',
                                        hour12: false,
                                        timeZone: Intl.DateTimeFormat().resolvedOptions().timeZone
                                    });
                                })()}</span></th>
                            </tr>
                            <tr>
                                <td style="white-space: nowrap;">User:</td>
                                <th class="px-2"><span style="font-weight: 550">${activity.user_name || 'System'}</span></th>
                            </tr>
                            <tr>
                                <td style="white-space: nowrap;">Activity Type:</td>
                                <th class="px-2"><span class="${getActivityBadgeClass(activity.event || properties.event)}">${capitalizeFirst(activity.event || properties.event || 'Unknown')}</span></th>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="col">
                    <table style="font-size: 14px">
                        <tbody>
                            <tr>
                                <td></td>
                                <th></th>
                            </tr>
                            <tr>
                                <td style="white-space: nowrap;">Module:</td>
                                <th class="px-2"><span style="font-weight: 550">${getModuleName(activity.subject_type || properties.subject_type || '')}</span></th>
                            </tr>
                            <tr>
                                <td style="white-space: nowrap;">Subject ID:</td>
                                <th class="px-2"><code>${activity.subject_id || properties.subject_id || '-'}</code></th>
                            </tr>
                            <tr>
                                <td style="white-space: nowrap;">IP Address:</td>
                                <th class="px-2"><span style="font-weight: 550">${properties.ip || 'Unknown'}</span></th>
                            </tr>
                            <tr>
                                <td style="white-space: nowrap;">Log Name:</td>
                                <th class="px-2"><span style="font-weight: 550">${activity.log_name}</span></th>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <hr>
            <div class="row mt-3">
                <div class="col-12">
                    <h6 class="text-primary">Description</h6>
                    <p class="border p-2 rounded bg-light" style="font-size: 14px;">${activity.description}</p>
                </div>
            </div>
        `;

        // Show technical details
        if (properties.user_agent) {
            detailsHTML += `
                <div class="row mt-3">
                    <div class="col-12">
                        <h6 class="text-primary">Technical Details</h6>
                        <table class="table table-sm" style="font-size: 12px">
                            <tbody>
                                <tr>
                                    <td><strong>User Agent:</strong></td>
                                    <td>${properties.user_agent}</td>
                                </tr>
                                <tr>
                                    <td><strong>Subject Type:</strong></td>
                                    <td><small>${activity.subject_type || properties.subject_type || '-'}</small></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            `;
        }

        // Show changes for update events
        if (activity.event === 'updated' && Object.keys(old).length > 0) {
            detailsHTML += `
                <div class="row mt-3">
                    <div class="col-12">
                        <h6 class="text-primary">Changes Made</h6>
                        <table class="table table-sm table-bordered" style="font-size: 12px">
                            <thead class="table-light">
                                <tr>
                                    <th>Field</th>
                                    <th>Old Value</th>
                                    <th>New Value</th>
                                </tr>
                            </thead>
                            <tbody>
            `;
            
            for (const [key, newValue] of Object.entries(attributes)) {
                const oldValue = old[key];
                if (oldValue !== newValue) {
                    detailsHTML += `
                        <tr>
                            <td><strong>${key}</strong></td>
                            <td class="text-muted">${oldValue || '<em>empty</em>'}</td>
                            <td class="text-success">${newValue || '<em>empty</em>'}</td>
                        </tr>
                    `;
                }
            }
            
            detailsHTML += `
                            </tbody>
                        </table>
                    </div>
                </div>
            `;
        }

        $('#activityDetailsContent').html(detailsHTML);
    }

    // Export to Excel
    $('#exportData').click(function() {
        const params = new URLSearchParams({
            date_from: $('#dateFrom').val(),
            date_to: $('#dateTo').val(),
            activity_type: $('#activityType').val(),
            subject_type: $('#subjectType').val(),
            user_name: $('#userName').val(),
            description: $('#description').val()
        });

        window.open(`/api/activity-log/export?${params.toString()}`, '_blank');
        showAlert('Export started! File will download shortly.', 'info');
    });

    // Set default date range (last 30 days)
    const today = new Date();
    const thirtyDaysAgo = new Date(today.getTime() - (30 * 24 * 60 * 60 * 1000));
    
    $('#dateTo').val(today.toISOString().split('T')[0]);
    $('#dateFrom').val(thirtyDaysAgo.toISOString().split('T')[0]);

    // Initialize everything
    loadStatistics();
    loadActivityLogsData();
    setupFilterEvents();

    // Enable tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});

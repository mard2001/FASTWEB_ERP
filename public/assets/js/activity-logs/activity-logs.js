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
                {label: '--- Stock Transfer ---', value: '', disabled: true},
                {label: 'Stock Transferred', value: 'transferred'},
                {label: '--- Sales Orders ---', value: '', disabled: true},
                {label: 'SO Created', value: 'so_created'},
                {label: 'SO Updated', value: 'so_updated'},
                {label: 'SO Available', value: 'so_available'},
                {label: 'SO Unavailable', value: 'so_unavailable'},
                {label: 'SO Restocked', value: 'so_restocked'},
                {label: 'SO Suspense', value: 'so_suspense'},
                {label: 'SO To Invoice', value: 'so_invoice'},
                {label: 'SO Completed', value: 'so_completed'},
                {label: 'SO Deleted', value: 'so_deleted'},
                {label: '--- Salesman Management ---', value: '', disabled: true},
                {label: 'Salesman Created', value: 'salesman_created'},
                {label: 'Salesman Updated', value: 'salesman_updated'},
                {label: 'Salesman Deleted', value: 'salesman_deleted'},
                {label: '--- Supplier Management ---', value: '', disabled: true},
                {label: 'Supplier Created', value: 'supplier_created'},
                {label: 'Supplier Updated', value: 'supplier_updated'},
                {label: 'Supplier Deleted', value: 'supplier_deleted'},
                {label: '--- Warehouse Management ---', value: '', disabled: true},
                {label: 'Warehouse Created', value: 'warehouse_created'},
                {label: 'Warehouse Updated', value: 'warehouse_updated'},
                {label: 'Warehouse Deleted', value: 'warehouse_deleted'}
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
                {label: 'Stock Transfer', value: 'App\\Models\\Inventory\\InvMovements'},
                {label: 'Purchase Orders', value: 'App\\Models\\Orders\\PO'},
                {label: 'Sales Orders', value: 'App\\Models\\SalesOrder\\SO'},
                {label: 'User Management', value: 'App\\Models\\User'},
                {label: 'Customers', value: 'App\\Models\\Customer\\Customer'},
                {label: 'Products', value: 'App\\Models\\Product'},
                {label: 'Suppliers', value: 'App\\Models\\Supplier'},
                {label: 'Warehouses', value: 'App\\Models\\Warehouse\\WHTagging'},
                {label: 'Salesman Management', value: 'App\\Models\\Salesman\\Salesperson'},
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
                                if (type === 'display' || type === 'filter') {
                                    // Backend already sends Asia/Manila timezone, so just format it nicely
                                    const dateObj = new Date(data);
                                    return dateObj.toLocaleString('en-GB', {
                                        day: '2-digit',
                                        month: 'short',
                                        year: 'numeric',
                                        hour: '2-digit',
                                        minute: '2-digit',
                                        hour12: true
                                    });
                                }
                                return data;
                            }
                        },
                        { 
                            data: 'causer_name', 
                            title: 'User',
                            render: function (data, type, row) {
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
                                    // Check if this is a stock transfer activity
                                    if (row.log_name === 'stock_transfer' && row.description) {
                                        const detectedEvent = detectStockTransferEvent(row.description);
                                        if (detectedEvent) {
                                            const badgeClass = getActivityBadgeClass(detectedEvent.event);
                                            return `<span class="${badgeClass}">${detectedEvent.label}</span>`;
                                        }
                                    }
                                    // Check if this is a sales order activity by looking at log_name or description
                                    if (row.log_name === 'sales_order' && row.description) {
                                        const detectedEvent = detectSalesOrderEvent(row.description);
                                        if (detectedEvent) {
                                            const badgeClass = getActivityBadgeClass(detectedEvent.event);
                                            return `<span class="${badgeClass}">${detectedEvent.label}</span>`;
                                        }
                                    }
                                    // Check if this is an inventory adjustment activity
                                    if (row.log_name === 'inventory_adjustment' && row.description) {
                                        const detectedEvent = detectInventoryEvent(row.description);
                                        if (detectedEvent) {
                                            const badgeClass = getActivityBadgeClass(detectedEvent.event);
                                            return `<span class="${badgeClass}">${detectedEvent.label}</span>`;
                                        }
                                    }
                                    // Check if this is a stock count activity
                                    if (row.log_name === 'stock_count' && row.description) {
                                        const detectedEvent = detectStockCountEvent(row.description);
                                        if (detectedEvent) {
                                            const badgeClass = getActivityBadgeClass(detectedEvent.event);
                                            return `<span class="${badgeClass}">${detectedEvent.label}</span>`;
                                        }
                                    }
                                    // Check if this is a customer activity
                                    if (row.log_name === 'customer_maintenance' && row.description) {
                                        const detectedEvent = detectCustomerEvent(row.description);
                                        if (detectedEvent) {
                                            const badgeClass = getActivityBadgeClass(detectedEvent.event);
                                            return `<span class="${badgeClass}">${detectedEvent.label}</span>`;
                                        }
                                    }
                                    // Check if this is a product activity
                                    if (row.log_name === 'product_maintenance' && row.description) {
                                        const detectedEvent = detectProductEvent(row.description);
                                        if (detectedEvent) {
                                            const badgeClass = getActivityBadgeClass(detectedEvent.event);
                                            return `<span class="${badgeClass}">${detectedEvent.label}</span>`;
                                        }
                                    }
                                    // Check if this is a salesman activity
                                    if (row.log_name === 'salesman' && row.description) {
                                        const detectedEvent = detectSalesmanEvent(row.description);
                                        if (detectedEvent) {
                                            const badgeClass = getActivityBadgeClass(detectedEvent.event);
                                            return `<span class="${badgeClass}">${detectedEvent.label}</span>`;
                                        }
                                    }
                                    // Check if this is a supplier activity
                                    if (row.log_name === 'supplier' && row.description) {
                                        const detectedEvent = detectSupplierEvent(row.description);
                                        if (detectedEvent) {
                                            const badgeClass = getActivityBadgeClass(detectedEvent.event);
                                            return `<span class="${badgeClass}">${detectedEvent.label}</span>`;
                                        }
                                    }
                                    // Check if this is a warehouse activity
                                    if (row.log_name === 'warehouse' && row.description) {
                                        const detectedEvent = detectWarehouseEvent(row.description);
                                        if (detectedEvent) {
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
                                // If no subject_type but it's a stock transfer activity, show Stock Transfer
                                if (!data && row.log_name === 'stock_transfer') {
                                    return 'Stock Transfer';
                                }
                                // If no subject_type but it's a sales order activity, show Sales Orders
                                if (!data && row.log_name === 'sales_order') {
                                    return 'Sales Orders';
                                }
                                // If no subject_type but it's an inventory adjustment activity, show Stock Adjustment
                                if (!data && row.log_name === 'inventory_adjustment') {
                                    return 'Stock Adjustment';
                                }
                                // If no subject_type but it's a stock count activity, show Stock Count
                                if (!data && row.log_name === 'stock_count') {
                                    return 'Stock Count';
                                }
                                // If no subject_type but it's a customer activity, show Customers
                                if (!data && row.log_name === 'customer_maintenance') {
                                    return 'Customers';
                                }
                                // If no subject_type but it's a product activity, show Products
                                if (!data && row.log_name === 'product_maintenance') {
                                    return 'Products';
                                }
                                // If no subject_type but it's a supplier activity, show Suppliers
                                if (!data && row.log_name === 'supplier') {
                                    return 'Suppliers';
                                }
                                // If no subject_type but it's a warehouse activity, show Warehouses
                                if (!data && row.log_name === 'warehouse') {
                                    return 'Warehouses';
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
            
            // Stock Transfer specific activities
            'transferred': 'statusBadge4',    // Blue - Stock Transferred
            'transfer_activity': 'statusBadge4', // Blue - General Transfer activity
            
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
            'so_activity': 'statusBadge4',    // Blue - General SO activity
            
            // Inventory Adjustment specific activities
            'inv_adjustment': 'statusBadge3', // Orange - Stock Adjusted
            'inv_activity': 'statusBadge4',   // Blue - General Inventory activity
            
            // Stock Count specific activities
            'sc_confirmed': 'statusBadge1',   // Green - Count Confirmed
            'sc_updated': 'statusBadge3',     // Orange - Count Updated
            'sc_deleted': 'statusBadge2',     // Red - Count Deleted
            'sc_created': 'statusBadge1',     // Green - Count Created
            'sc_printed': 'statusBadge4',     // Blue - Sheet Printed
            'sc_activity': 'statusBadge4',    // Blue - General Stock Count activity
            
            // Customer specific activities
            'customer_created': 'statusBadge1',  // Green - Customer Created
            'customer_updated': 'statusBadge3',  // Orange - Customer Updated
            'customer_deleted': 'statusBadge2',  // Red - Customer Deleted
            'customer_activity': 'statusBadge4', // Blue - General Customer activity
            
            // Product specific activities
            'product_created': 'statusBadge1',   // Green - Product Created
            'product_updated': 'statusBadge3',   // Orange - Product Updated
            'product_deleted': 'statusBadge2',   // Red - Product Deleted
            'product_activity': 'statusBadge4',  // Blue - General Product activity
            
            // Salesman specific activities
            'salesman_created': 'statusBadge1',   // Green - Salesman Created
            'salesman_updated': 'statusBadge3',   // Orange - Salesman Updated
            'salesman_deleted': 'statusBadge2',   // Red - Salesman Deleted
            'salesman_activity': 'statusBadge4',  // Blue - General Salesman activity
            
            // Supplier specific activities
            'supplier_created': 'statusBadge1',   // Green - Supplier Created
            'supplier_updated': 'statusBadge3',   // Orange - Supplier Updated
            'supplier_deleted': 'statusBadge2',   // Red - Supplier Deleted
            'supplier_activity': 'statusBadge4',   // Blue - General Supplier activity
            
            // Warehouse specific activities
            'warehouse_created': 'statusBadge1',   // Green - Warehouse Created
            'warehouse_updated': 'statusBadge3',   // Orange - Warehouse Updated
            'warehouse_deleted': 'statusBadge2',   // Red - Warehouse Deleted
            'warehouse_activity': 'statusBadge4'   // Blue - General Warehouse activity
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
            'App\\Models\\Product': 'Products',
            'App\\Models\\Warehouse\\WHTagging': 'Warehouses',
            'App\\Models\\Inventory\\InvMovements': 'Stock Transfer',
            'App\\Models\\Inventory\\InvAdjustmentLogs': 'Stock Adjustment',
            'App\\Models\\Inventory\\CSHeader': 'Stock Count',
            'App\\Models\\Warehouse\\Warehouse': 'Warehouses',
            'App\\Models\\Salesman\\Salesperson': 'Salesman Management',
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

    // Detect Stock Transfer event type from description
    function detectStockTransferEvent(description) {
        if (!description) return null;
        
        const desc = description.toLowerCase();
        
        // Check for different Stock Transfer activities
        if (desc.includes('stock transfer') && (desc.includes('from') || desc.includes('to'))) {
            return { event: 'transferred', label: 'Transferred' };
        }
        
        // Default for stock transfer activities
        return { event: 'transfer_activity', label: 'Transfer Activity' };
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

    // Detect Inventory Adjustment event type from description
    function detectInventoryEvent(description) {
        if (!description) return null;
        
        const desc = description.toLowerCase();
        
        // Check for different Inventory activities
        if (desc.includes('stock adjustment') && desc.includes('created')) {
            return { event: 'inv_adjustment', label: 'Stock Adjusted' };
        }
        
        // Default for inventory activities
        return { event: 'inv_activity', label: 'Inventory Activity' };
    }

    // Detect Stock Count event type from description
    function detectStockCountEvent(description) {
        if (!description) return null;
        
        const desc = description.toLowerCase();
        
        // Check for different Stock Count activities
        if (desc.includes('stock count') && desc.includes('confirmed')) {
            return { event: 'sc_confirmed', label: 'Count Confirmed' };
        }
        if (desc.includes('stock count') && desc.includes('deleted')) {
            return { event: 'sc_deleted', label: 'Count Deleted' };
        }
        if (desc.includes('stock count') && desc.includes('updated')) {
            return { event: 'sc_updated', label: 'Count Updated' };
        }
        if (desc.includes('stock count') && desc.includes('created')) {
            return { event: 'sc_created', label: 'Count Created' };
        }
        if (desc.includes('stock count') && desc.includes('printed')) {
            return { event: 'sc_printed', label: 'Sheet Printed' };
        }
        
        // Default for stock count activities
        return { event: 'sc_activity', label: 'Stock Count Activity' };
    }

    // Detect customer events from description
    function detectCustomerEvent(description) {
        if (!description) return { event: 'customer_activity', label: 'Customer Activity' };
        
        const desc = description.toLowerCase();
        
        if (desc.includes('created new customer') || desc.includes('new customer')) {
            return { event: 'customer_created', label: 'Created' };
        }
        if (desc.includes('updated customer') || desc.includes('edited customer')) {
            return { event: 'customer_updated', label: 'Updated' };
        }
        if (desc.includes('deleted customer') || desc.includes('customer deleted')) {
            return { event: 'customer_deleted', label: 'Deleted' };
        }
        
        // Default for customer activities
        return { event: 'customer_activity', label: 'Customer Activity' };
    }

    // Detect product events from description
    function detectProductEvent(description) {
        if (!description) return { event: 'product_activity', label: 'Product Activity' };
        
        const desc = description.toLowerCase();
        
        if (desc.includes('created new product') || desc.includes('new product')) {
            return { event: 'product_created', label: 'Created' };
        }
        if (desc.includes('updated product') || desc.includes('edited product')) {
            return { event: 'product_updated', label: 'Updated' };
        }
        if (desc.includes('deleted product') || desc.includes('product deleted')) {
            return { event: 'product_deleted', label: 'Deleted' };
        }
        
        // Default for product activities
        return { event: 'product_activity', label: 'Product Activity' };
    }

    // Detect salesman events from description
    function detectSalesmanEvent(description) {
        if (!description) return { event: 'salesman_activity', label: 'Salesman Activity' };
        
        const desc = description.toLowerCase();
        
        if (desc.includes('created new salesman') || desc.includes('new salesman')) {
            return { event: 'salesman_created', label: 'Created' };
        }
        if (desc.includes('updated salesman') || desc.includes('edited salesman')) {
            return { event: 'salesman_updated', label: 'Updated' };
        }
        if (desc.includes('deleted salesman') || desc.includes('salesman deleted')) {
            return { event: 'salesman_deleted', label: 'Deleted' };
        }
        
        // Default for salesman activities
        return { event: 'salesman_activity', label: 'Salesman Activity' };
    }

    // Detect supplier events from description
    function detectSupplierEvent(description) {
        if (!description) return { event: 'supplier_activity', label: 'Supplier Activity' };
        
        const desc = description.toLowerCase();
        
        if (desc.includes('created new supplier') || desc.includes('new supplier')) {
            return { event: 'supplier_created', label: 'Created' };
        }
        if (desc.includes('updated supplier') || desc.includes('edited supplier')) {
            return { event: 'supplier_updated', label: 'Updated' };
        }
        if (desc.includes('deleted supplier') || desc.includes('supplier deleted')) {
            return { event: 'supplier_deleted', label: 'Deleted' };
        }
        
        // Default for supplier activities
        return { event: 'supplier_activity', label: 'Supplier Activity' };
    }

    // Detect warehouse events from description
    function detectWarehouseEvent(description) {
        if (!description) return { event: 'warehouse_activity', label: 'Warehouse Activity' };
        
        const desc = description.toLowerCase();
        
        if (desc.includes('created new warehouse') || desc.includes('new warehouse')) {
            return { event: 'warehouse_created', label: 'Created' };
        }
        if (desc.includes('updated warehouse') || desc.includes('edited warehouse')) {
            return { event: 'warehouse_updated', label: 'Updated' };
        }
        if (desc.includes('deleted warehouse') || desc.includes('warehouse deleted')) {
            return { event: 'warehouse_deleted', label: 'Deleted' };
        }
        
        // Default for warehouse activities
        return { event: 'warehouse_activity', label: 'Warehouse Activity' };
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
                    3. Open the file: <code>database/sql_scripts/create_tblActivityLog_table.sql</code><br>
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
                                    // Backend already sends Asia/Manila timezone, so just format it nicely
                                    const dateObj = new Date(activity.created_at);
                                    return dateObj.toLocaleString('en-US', {
                                        year: 'numeric',
                                        month: 'short', 
                                        day: 'numeric',
                                        hour: '2-digit',
                                        minute: '2-digit',
                                        second: '2-digit',
                                        hour12: true
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

        // Show changes for update events (for both salesman and customer activities)
        const eventType = activity.event || properties.event;
        if (eventType === 'updated' && (Object.keys(old).length > 0 || Object.keys(attributes).length > 0)) {
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
            
            let hasChanges = false;
            
            // Field name mappings for better display (for salesman, customer, and product fields)
            const fieldNameMappings = {
                // Salesman fields
                'EmployeeID': 'Employee ID',
                'mdCode': 'MD Code',
                'ContactNo': 'Contact Number',
                'ContactHP': 'Contact HP',
                'ContacteMail': 'Contact Email',
                'Addr1': 'Address 1',
                'Addr2': 'Address 2',
                'Addr3': 'Address 3',
                'Addr4': 'Address 4',
                'SourceWarehouse': 'Source Warehouse',
                'Group1': 'Group 1',
                'Group2': 'Group 2',
                'Group3': 'Group 3',
                
                // Customer fields
                'CustomerID': 'Customer ID',
                'CustName': 'Customer Name',
                'CompanyName': 'Company Name',
                'ContactPerson': 'Contact Person',
                'CustType': 'Customer Type',
                'TelNo': 'Telephone Number',
                'FaxNo': 'Fax Number',
                'Email': 'Email Address',
                'Website': 'Website',
                'TINNo': 'TIN Number',
                'VATType': 'VAT Type',
                'PaymentTerms': 'Payment Terms',
                'CreditLimit': 'Credit Limit',
                'PriceLevel': 'Price Level',
                'SalesmanCode': 'Salesman Code',
                'Territory': 'Territory',
                'CustomerClass': 'Customer Class',
                'DateCreated': 'Date Created',
                'CreatedBy': 'Created By',
                'LastModified': 'Last Modified',
                'ModifiedBy': 'Modified By',
                'IsActive': 'Active Status',
                'BillingAddr1': 'Billing Address 1',
                'BillingAddr2': 'Billing Address 2',
                'BillingAddr3': 'Billing Address 3',
                'BillingAddr4': 'Billing Address 4',
                'BillingCity': 'Billing City',
                'BillingProvince': 'Billing Province',
                'BillingZipCode': 'Billing Zip Code',
                'BillingCountry': 'Billing Country',
                'ShippingAddr1': 'Shipping Address 1',
                'ShippingAddr2': 'Shipping Address 2',
                'ShippingAddr3': 'Shipping Address 3',
                'ShippingAddr4': 'Shipping Address 4',
                'ShippingCity': 'Shipping City',
                'ShippingProvince': 'Shipping Province',
                'ShippingZipCode': 'Shipping Zip Code',
                'ShippingCountry': 'Shipping Country',
                
                // Product fields
                'StockCode': 'Product Code',
                'Description': 'Product Description',
                'LongDesc': 'Long Description',
                'Brand': 'Brand',
                'StockUom': 'Stock UOM',
                'AlternateUom': 'Alternate UOM',
                'OtherUom': 'Other UOM',
                'ConvFactAltUom': 'Alt UOM Conversion Factor',
                'ConvFactOthUom': 'Other UOM Conversion Factor',
                'Mass': 'Mass',
                'Volume': 'Volume',
                'Supplier': 'Supplier',
                'MinPricePct': 'Min Price Percentage',
                'LabourCost': 'Labour Cost',
                'MaterialCost': 'Material Cost',
                'FixOverhead': 'Fixed Overhead',
                'VariableOverhead': 'Variable Overhead',
                'WarehouseToUse': 'Warehouse to Use',
                'SpecificGravity': 'Specific Gravity',
                'PanSize': 'Pan Size',
                'DockToStock': 'Dock to Stock',
                'ShelfLife': 'Shelf Life',
                'DemandTimeFence': 'Demand Time Fence',
                'ManufLeadTime': 'Manufacturing Lead Time',
                'AbcPreProd': 'ABC Pre-Production',
                'AbcManufacturing': 'ABC Manufacturing',
                'AbcSales': 'ABC Sales',
                'SerEntryAtSale': 'Serial Entry at Sale',
                'UserField2': 'User Field 2',
                'UserField4': 'User Field 4',
                'StdLctRoute': 'Standard LCT Route',
                'StdLabCostsBill': 'Standard Labour Costs Bill',
                'AlternateKey1': 'Alternate Key 1',
                'ImplosionNum': 'Implosion Number',
                'ComponentCount': 'Component Count',
                'AbcCumPreProd': 'ABC Cumulative Pre-Production',
                'AbcCumManuf': 'ABC Cumulative Manufacturing',
                
                // Purchase Order fields
                'PurchaseOrder': 'Purchase Order Number',
                'Supplier': 'Supplier Code',
                'SupplierName': 'Supplier Name',
                'OrderDate': 'Order Date',
                'ReqDeliveryDate': 'Required Delivery Date',
                'ActualDeliveryDate': 'Actual Delivery Date',
                'TermsCode': 'Terms Code',
                'ShipViaCode': 'Ship Via Code',
                'Buyer': 'Buyer',
                'OrderStatus': 'Order Status',
                'LastGtrReference': 'Last GTR Reference',
                'EntrySystemDate': 'Entry System Date',
                'CompletedDate': 'Completed Date',
                'PrintedFlag': 'Printed Flag',
                'EmailFlag': 'Email Flag',
                'ApprovalFlag': 'Approval Flag',
                'OrderType': 'Order Type',
                'FixExchangeRate': 'Fixed Exchange Rate',
                'ExchangeRate': 'Exchange Rate',
                'LocalCurrencyFlag': 'Local Currency Flag',
                'GtrCount': 'GTR Count',
                'UserDef1': 'User Defined 1',
                'UserDef2': 'User Defined 2',
                'UserDef3': 'User Defined 3',
                'TaxStatus': 'Tax Status',
                'SpecialInstruction': 'Special Instruction',
                'ShipTo': 'Ship To',
                'FOB': 'F.O.B',
                'Fob': 'F.O.B',
                'DeliveryNote': 'Delivery Note',
                'Branch': 'Branch',
                'Area': 'Area',
                'Currency': 'Currency',
                'TotalMerchandise': 'Total Merchandise',
                'DiscountValue': 'Discount Value',
                'DiscountPercent': 'Discount Percent',
                'FreightValue': 'Freight Value',
                'MiscChargeValue': 'Misc Charge Value',
                'TaxValue': 'Tax Value',
                'OrderValue': 'Order Value',
                
                // Supplier fields
                'SupplierCode': 'Supplier Code',
                'SupplierName': 'Supplier Name',
                'SupplierType': 'Supplier Type',
                'TermsCode': 'Terms Code',
                'ContactPerson': 'Contact Person',
                'ContactNo': 'Contact Number',
                'CompleteAddress': 'Complete Address',
                'Region': 'Region',
                'Province': 'Province',
                'City': 'City',
                'Municipality': 'Municipality',
                'Barangay': 'Barangay',
                'PriceCode': 'Price Code',
                'holdStatus': 'Hold Status',
                
                // Warehouse fields
                'Warehouse': 'Warehouse Code',
                'WHType': 'Warehouse Type',
                'WHGroupCode': 'WH Group Code',
                'WHGroupDesc': 'WH Group Description',
                'Status': 'Status',
                'DateUpdated': 'Date Updated'
            };
            
            for (const [key, newValue] of Object.entries(attributes)) {
                const oldValue = old[key];
                
                // Skip timestamps and irrelevant fields
                if (['lastUpdated', 'created_at', 'updated_at', 'LastModified', 'DateCreated'].includes(key)) {
                    continue;
                }
                
                // Convert values to strings for comparison to handle null/undefined/empty cases
                const oldValueStr = (oldValue === null || oldValue === undefined) ? '' : String(oldValue).trim();
                const newValueStr = (newValue === null || newValue === undefined) ? '' : String(newValue).trim();
                
                // Only show if there's an actual change
                if (oldValueStr !== newValueStr) {
                    hasChanges = true;
                    
                    // Use mapped field name or format the original field name
                    const fieldDisplayName = fieldNameMappings[key] || key.replace(/([A-Z])/g, ' $1').replace(/^./, str => str.toUpperCase());
                    
                    // Handle empty values display
                    const displayOldValue = oldValueStr || '<em class="text-muted">empty</em>';
                    const displayNewValue = newValueStr || '<em class="text-muted">empty</em>';
                    
                    // Special formatting for certain field types
                    let formattedOldValue = displayOldValue;
                    let formattedNewValue = displayNewValue;
                    
                    // Format boolean values
                    if (key === 'IsActive' || key === 'SerEntryAtSale') {
                        formattedOldValue = oldValue === 1 || oldValue === '1' || oldValue === true ? 'Active' : 'Inactive';
                        formattedNewValue = newValue === 1 || newValue === '1' || newValue === true ? 'Active' : 'Inactive';
                    }
                    
                    // Format supplier hold status
                    if (key === 'holdStatus') {
                        const statusMap = { 'Y': 'On Hold', 'N': 'Active', '': 'Active' };
                        formattedOldValue = statusMap[oldValue] || oldValue;
                        formattedNewValue = statusMap[newValue] || newValue;
                    }
                    
                    // Format warehouse status
                    if (key === 'Status' && (oldValueStr || newValueStr)) {
                        const statusMap = { 'A': 'Active', 'I': 'Inactive', '': 'Active' };
                        if (oldValueStr) formattedOldValue = statusMap[oldValue] || oldValue;
                        if (newValueStr) formattedNewValue = statusMap[newValue] || newValue;
                    }
                    
                    // Format currency values
                    if (['CreditLimit', 'LabourCost', 'MaterialCost', 'FixOverhead', 'VariableOverhead', 'MinPricePct', 
                         'TotalMerchandise', 'DiscountValue', 'FreightValue', 'MiscChargeValue', 'TaxValue', 'OrderValue', 'ExchangeRate'].includes(key) && (oldValueStr || newValueStr)) {
                        if (oldValueStr) {
                            if (key === 'MinPricePct' || key === 'DiscountPercent') {
                                formattedOldValue = parseFloat(oldValueStr).toFixed(2) + '%';
                            } else {
                                formattedOldValue = '₱' + parseFloat(oldValueStr).toLocaleString();
                            }
                        }
                        if (newValueStr) {
                            if (key === 'MinPricePct' || key === 'DiscountPercent') {
                                formattedNewValue = parseFloat(newValueStr).toFixed(2) + '%';
                            } else {
                                formattedNewValue = '₱' + parseFloat(newValueStr).toLocaleString();
                            }
                        }
                    }
                    
                    // Format numeric values with units
                    if (['Mass', 'Volume', 'ShelfLife', 'DemandTimeFence', 'ManufLeadTime', 'DockToStock'].includes(key) && (oldValueStr || newValueStr)) {
                        const units = {
                            'Mass': 'kg',
                            'Volume': 'm³',
                            'ShelfLife': 'days',
                            'DemandTimeFence': 'days',
                            'ManufLeadTime': 'days',
                            'DockToStock': 'days'
                        };
                        if (oldValueStr) formattedOldValue = oldValueStr + ' ' + units[key];
                        if (newValueStr) formattedNewValue = newValueStr + ' ' + units[key];
                    }
                    
                    // Format date values
                    if (['OrderDate', 'ReqDeliveryDate', 'ActualDeliveryDate', 'EntrySystemDate', 'CompletedDate'].includes(key) && (oldValueStr || newValueStr)) {
                        if (oldValueStr) {
                            try {
                                const date = new Date(oldValueStr);
                                formattedOldValue = date.toLocaleDateString();
                            } catch (e) {
                                formattedOldValue = oldValueStr;
                            }
                        }
                        if (newValueStr) {
                            try {
                                const date = new Date(newValueStr);
                                formattedNewValue = date.toLocaleDateString();
                            } catch (e) {
                                formattedNewValue = newValueStr;
                            }
                        }
                    }
                    
                    // Format flag values (Yes/No)
                    if (['PrintedFlag', 'EmailFlag', 'ApprovalFlag', 'LocalCurrencyFlag'].includes(key)) {
                        formattedOldValue = (oldValue === 1 || oldValue === '1' || oldValue === true) ? 'Yes' : 'No';
                        formattedNewValue = (newValue === 1 || newValue === '1' || newValue === true) ? 'Yes' : 'No';
                    }
                    
                    detailsHTML += `
                        <tr>
                            <td><strong>${fieldDisplayName}</strong></td>
                            <td class="text-muted">${formattedOldValue}</td>
                            <td class="text-success"><strong>${formattedNewValue}</strong></td>
                        </tr>
                    `;
                }
            }
            
            // If no changes found, show a message
            if (!hasChanges) {
                detailsHTML += `
                    <tr>
                        <td colspan="3" class="text-center text-muted">
                            <em>No field changes detected</em>
                        </td>
                    </tr>
                `;
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

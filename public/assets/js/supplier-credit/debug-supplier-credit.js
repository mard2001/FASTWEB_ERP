/**
 * Test functions to verify supplier credit fixes
 */

// Add debug logging to supplier credit loading
const originalAjax = ajax;
function debugAjax(endpoint, method, data, successCallback, errorCallback) {
    console.log('🔍 Supplier Credit API Call:', {
        endpoint,
        method,
        timestamp: new Date().toISOString()
    });

    return originalAjax(endpoint, method, data, 
        function(response) {
            console.log('✅ Supplier Credit API Success:', {
                endpoint,
                dataCount: response.data ? response.data.length : 0,
                suppliers: response.data ? response.data.map(s => s.SupplierCode + ' - ' + s.SupplierName) : []
            });
            
            if (successCallback) successCallback(response);
        }, 
        function(xhr, status, error) {
            console.error('❌ Supplier Credit API Error:', {
                endpoint,
                status: xhr.status,
                statusText: xhr.statusText,
                error: error,
                response: xhr.responseText
            });
            
            if (errorCallback) errorCallback(xhr, status, error);
        }
    );
}

// Debug version of loadSupplierData
const debugDatatables = {
    loadSupplierData: async () => {
        console.log('🚀 Loading supplier credit data...');
        
        const supplierData = await debugAjax('api/supplier-credit', 'GET', null, (response) => {
            console.log('📊 Supplier data loaded:', response);
            suppliersData = response.data;
            datatables.initSupplierDatatable(response);
        }, (xhr, status, error) => {
            console.error('💥 Error loading supplier credit data:', error);
            console.error('XHR Details:', xhr);
        });
    }
};

// Function to manually refresh supplier credit data
window.refreshSupplierCreditData = function() {
    console.log('🔄 Manually refreshing supplier credit data...');
    
    debugAjax('api/supplier-credit/refresh', 'POST', {}, 
        function(response) {
            console.log('✅ Refresh successful:', response);
            // Reload the table data
            debugDatatables.loadSupplierData();
        },
        function(xhr, status, error) {
            console.error('❌ Refresh failed:', error);
        }
    );
};

// Function to check if a specific supplier exists in the data
window.checkSupplierExists = function(supplierCode) {
    console.log('🔍 Checking if supplier exists:', supplierCode);
    
    const supplier = suppliersData.find(s => s.SupplierCode === supplierCode);
    if (supplier) {
        console.log('✅ Supplier found:', supplier);
        return supplier;
    } else {
        console.log('❌ Supplier not found in current data');
        console.log('Available suppliers:', suppliersData.map(s => s.SupplierCode));
        return null;
    }
};

// Add debugging to the main supplier credit loading
if (typeof datatables !== 'undefined' && datatables.loadSupplierData) {
    const originalLoadSupplierData = datatables.loadSupplierData;
    datatables.loadSupplierData = debugDatatables.loadSupplierData;
}

console.log('🛠️ Supplier Credit Debug Tools Loaded');
console.log('Available debug functions:');
console.log('- refreshSupplierCreditData(): Manually refresh data');
console.log('- checkSupplierExists(code): Check if supplier exists');
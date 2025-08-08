// Mobile-friendly API configuration
function getMobileApiUrl() {
    // Get the current host from the browser
    const currentHost = window.location.hostname;
    const currentProtocol = window.location.protocol;
    const currentPort = window.location.port;
    
    // For mobile devices accessing via IP or domain
    if (currentHost !== 'localhost' && currentHost !== '127.0.0.1') {
        return `${currentProtocol}//${currentHost}${currentPort ? ':' + currentPort : ''}/`;
    }
    
    // Default fallback for local development
    return "http://127.0.0.1:8000/";
}

// Set global API URL dynamically
window.globalApi = getMobileApiUrl();

// Debug function to check API URL
function debugApiUrl() {
    console.log('Current API URL:', window.globalApi);
    console.log('Current host:', window.location.hostname);
    console.log('Current protocol:', window.location.protocol);
    console.log('Current port:', window.location.port);
}

// Call debug function on load (can be removed in production)
debugApiUrl();

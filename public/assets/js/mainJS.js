// Dynamic API URL configuration for mobile compatibility
function getMobileApiUrl() {
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

var globalApi = getMobileApiUrl();

// Global Philippine location data
var philippineData = {
    regions: [],
    provinces: [],
    cities: [],
    barangays: []
};

// Global function to load Philippine location data
async function loadPhilippineData() {
    try {
        console.log('Loading Philippine location data...');
        
        const [regionsResponse, provincesResponse, citiesResponse, barangaysResponse] = await Promise.all([
            fetch('/assets/philippine-geolocation/region.json'),
            fetch('/assets/philippine-geolocation/province.json'),
            fetch('/assets/philippine-geolocation/city.json'),
            fetch('/assets/philippine-geolocation/barangay.json')
        ]);

        if (!regionsResponse.ok || !provincesResponse.ok || !citiesResponse.ok || !barangaysResponse.ok) {
            throw new Error('Failed to fetch Philippine location data');
        }

        philippineData.regions = await regionsResponse.json();
        philippineData.provinces = await provincesResponse.json();
        philippineData.cities = await citiesResponse.json();
        philippineData.barangays = await barangaysResponse.json();

        console.log('Philippine location data loaded successfully:', {
            regions: philippineData.regions.length,
            provinces: philippineData.provinces.length,
            cities: philippineData.cities.length,
            barangays: philippineData.barangays.length
        });

        return philippineData;
    } catch (error) {
        console.error('Error loading Philippine location data:', error);
        throw error;
    }
}

// Global helper functions for Philippine location data
window.PhilippineLocationHelpers = {
    // Get provinces by region code
    getProvincesByRegion: function(regionCode) {
        return philippineData.provinces.filter(province => province.region_code === regionCode);
    },
    
    // Get cities by province code
    getCitiesByProvince: function(provinceCode) {
        return philippineData.cities.filter(city => city.province_code === provinceCode);
    },
    
    // Get barangays by city code
    getBarangaysByCity: function(cityCode) {
        return philippineData.barangays.filter(barangay => barangay.city_code === cityCode);
    },
    
    // Find region by code
    findRegion: function(regionCode) {
        return philippineData.regions.find(region => region.region_code === regionCode);
    },
    
    // Find province by code
    findProvince: function(provinceCode) {
        return philippineData.provinces.find(province => province.province_code === provinceCode);
    },
    
    // Find city by code
    findCity: function(cityCode) {
        return philippineData.cities.find(city => city.city_code === cityCode);
    },
    
    // Find barangay by code
    findBarangay: function(barangayCode) {
        return philippineData.barangays.find(barangay => barangay.brgy_code === barangayCode);
    },
    
    // Get complete address string
    getCompleteAddress: function(regionCode, provinceCode, cityCode, barangayCode) {
        const region = this.findRegion(regionCode);
        const province = this.findProvince(provinceCode);
        const city = this.findCity(cityCode);
        const barangay = this.findBarangay(barangayCode);
        
        const parts = [];
        if (barangay) parts.push(barangay.brgy_name);
        if (city) parts.push(city.city_name);
        if (province) parts.push(province.province_name);
        if (region) parts.push(region.region_name);
        
        return parts.join(', ');
    },
    
    // Check if data is loaded
    isDataLoaded: function() {
        return philippineData.regions.length > 0 && 
               philippineData.provinces.length > 0 && 
               philippineData.cities.length > 0 && 
               philippineData.barangays.length > 0;
    }
};

// Global Character Counter Helper
window.CharacterCounterHelper = {
    /**
     * Initialize character counter for an input field
     * @param {string} inputSelector - jQuery selector for the input field
     * @param {string} counterSelector - jQuery selector for the counter display element
     * @param {number} maxLength - Maximum character length (defaults to input's maxlength attribute)
     * @param {Object} options - Optional configuration
     */
    init: function(inputSelector, counterSelector, maxLength = null, options = {}) {
        const defaultOptions = {
            warningThreshold: 0.50, // 50% of max length
            dangerThreshold: 0.9,   // 90% of max length
            warningColor: '#fd7e14', // Orange
            dangerColor: '#dc3545',  // Red
            normalColor: '#6c757d',  // Gray
            updateOnModalShow: false, // Whether to update when modal is shown
            modalSelector: null       // Modal selector if updateOnModalShow is true
        };
        
        const config = Object.assign(defaultOptions, options);
        const $input = $(inputSelector);
        const $counter = $(counterSelector);
        
        if ($input.length === 0 || $counter.length === 0) {
            console.warn('CharacterCounter: Input or counter element not found', {
                inputSelector,
                counterSelector
            });
            return;
        }
        
        // Get max length from input attribute if not provided
        if (maxLength === null) {
            maxLength = parseInt($input.attr('maxlength')) || 100;
        }
        
        // Update counter function
        function updateCounter() {
            const currentLength = $input.val().length;
            $counter.text(currentLength);
            
            // Calculate thresholds
            const warningThreshold = Math.floor(maxLength * config.warningThreshold);
            const dangerThreshold = Math.floor(maxLength * config.dangerThreshold);
            
            // Update color based on length
            if (currentLength >= dangerThreshold) {
                $counter.css('color', config.dangerColor);
            } else if (currentLength >= warningThreshold) {
                $counter.css('color', config.warningColor);
            } else {
                $counter.css('color', config.normalColor);
            }
        }
        
        // Bind input event
        $input.on('input', updateCounter);
        
        // Bind modal show event if specified
        if (config.updateOnModalShow && config.modalSelector) {
            $(config.modalSelector).on('shown.bs.modal', updateCounter);
        }
        
        // Initial update
        updateCounter();
        
        // Return object with utility methods
        return {
            update: updateCounter,
            destroy: function() {
                $input.off('input', updateCounter);
                if (config.updateOnModalShow && config.modalSelector) {
                    $(config.modalSelector).off('shown.bs.modal', updateCounter);
                }
            }
        };
    },
    
    /**
     * Quick setup for standard address fields with 100 character limit
     * @param {string} inputSelector - jQuery selector for the address input field
     * @param {string} counterSelector - jQuery selector for the counter display element
     * @param {string} modalSelector - Optional modal selector for auto-update
     */
    initAddressField: function(inputSelector, counterSelector, modalSelector = null) {
        return this.init(inputSelector, counterSelector, 100, {
            updateOnModalShow: modalSelector !== null,
            modalSelector: modalSelector
        });
    }
};


$(document).ready(function() {
  try {
    const user = localStorage.getItem('user');

    if (user) {
      const userObject = JSON.parse(user);
      $('#userName').text(userObject.name);
      $('#userEmail').text(userObject.email);
    }

    isTokenExist();
    GlobalUX();
  } catch (error) {
    console.error('Error in document ready:', error);
  }

  $(document).on('click', function (e) {
    const sidebar = $('#sidebar');

    if (sidebar.hasClass('expand')) {
      // Check if the click was outside the sidebar
      if (!$(e.target).closest('#sidebar').length) {
        sidebar.removeClass('expand');
        
        Array.from($('.showdropdown')).forEach(ul => {
          ul.classList.remove('showdropdown');
          ul.previousElementSibling.classList.remove('rotate');
        })
      }
    }
  });
});


// Set up CSRF token for AJAX
$.ajaxSetup({
    headers: {
        'Authorization': 'Bearer ' + localStorage.getItem('api_token')
    },
});

// set up auth error redirect
$(document).ajaxError(function (event, jqXHR, ajaxSettings, thrownError) {
    if (jqXHR.status === 401) {
        // Redirect to the login page (or any other page)
        window.location.href = "/login"; // Replace with your desired URL
    }
});

function isTokenExist() {
    if (!localStorage.getItem('api_token')) {
      localStorage.setItem('api_token', 'null');
      window.location.href = "/login";
    }
}

function GlobalUX() {
    //UI
    const hamBurger = document.querySelector(".btn-toggle");

    // Check if hamburger menu exists before adding event listener
    if (hamBurger) {
        hamBurger.addEventListener("click", async function () {
          document.querySelector("#sidebar").classList.toggle("expand");
          
          if (!$('#sidebar').hasClass('expand')) {
            Array.from($('.showdropdown')).forEach(ul => {
              ul.classList.remove('showdropdown');
              ul.previousElementSibling.classList.remove('rotate');
            })
          }
        });
    }

    // Get the pathname part of the URL
    var path = window.location.pathname;
    // Split the path by "/" and get the last segment
    var lastSegment = path.substring(path.lastIndexOf('/') + 1);
    
    function returnSideBarItemBaseOnIndex(i) {
        var sidebar = $('.sidebar-item').eq(i);
        sidebar.addClass('selectedlink');
        sidebar.find('span').addClass('selectedlinkSpan');
    }
    
    switch (lastSegment.toLocaleLowerCase()) {
      case 'product':
        returnSideBarItemBaseOnIndex(0);
        break;
      case 'salesman':
        returnSideBarItemBaseOnIndex(1);
        break;
      case 'customer':
        returnSideBarItemBaseOnIndex(2);
        break;
      case 'inventory':
        returnSideBarItemBaseOnIndex(3);
        break;
      case 'picklist':
        returnSideBarItemBaseOnIndex(4);
        break;
      case 'pamasterlist':
        returnSideBarItemBaseOnIndex(5);
        break;
      case 'patarget':
        returnSideBarItemBaseOnIndex(6);
        break;
      case 'invoices':
        returnSideBarItemBaseOnIndex(7);
        break;
      case 'purchase-order':
        returnSideBarItemBaseOnIndex(8);
        break;
      case 'receiving-report':
        returnSideBarItemBaseOnIndex(9);
        break;
      case 'sales-order':
        returnSideBarItemBaseOnIndex(10);
        break;
    }
}

function toggleSubMenu(button){
  if (!$('#sidebar').hasClass('expand')) {
    $('#sidebar').addClass('expand')
  }
  button.nextElementSibling.classList.toggle('showdropdown');
  button.classList.toggle('rotate');
}

// Function to get cookie by name (native JavaScript)
function getCookie(name) {
  const value = `; ${document.cookie}`;
  const parts = value.split(`; ${name}=`);
  if (parts.length === 2) return parts.pop().split(';').shift();
  return null;
}

// Function to delete cookie by name (native JavaScript)
function deleteCookie(name) {
  document.cookie = name + '=; Path=/; Expires=Thu, 01 Jan 1970 00:00:01 GMT;';
}

// Make sure logout function is available globally
window.logoutDeleteStorageTokens = function(){
  // Prevent multiple executions
  if (window.logoutInProgress) {
    return;
  }
  
  window.logoutInProgress = true;
  
  // Disable the logout button to prevent multiple clicks
  $('button[onclick*="logoutDeleteStorageTokens"]').prop('disabled', true);
  
  try {
    // Prevent any default behavior
    if (typeof event !== 'undefined') {
      event.preventDefault();
      event.stopPropagation();
      event.stopImmediatePropagation();
    }
    
    // Get the token from cookies using native JavaScript
    const authToken = getCookie('auth_token');
    
    // Also check localStorage as backup
    const localStorageToken = localStorage.getItem('api_token');
    
    // Use localStorage token if cookie token is not found
    const finalToken = authToken || localStorageToken;
    
    // If no token exists, just redirect (user is already logged out)
    if (!finalToken) {
      localStorage.removeItem('api_token');
      localStorage.removeItem('user');
      window.logoutInProgress = false;
      window.location.href = globalApi;
      return;
    }
  
  // Use force logout for better reliability
  $.ajax({
    url: globalApi + 'api/auth/force-logout',
    type: 'POST',
    timeout: 5000,
    headers: {
      'Authorization': 'Bearer ' + finalToken,
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    },
    complete: function() {
      // Always clear tokens and redirect regardless of API success/failure
      deleteCookie('auth_token');
      localStorage.removeItem('api_token');
      localStorage.removeItem('user');
      window.logoutInProgress = false;
      window.location.href = globalApi;
    }
  });
  
  } catch (error) {
    // Even if there's an error, try to redirect to login
    window.location.href = globalApi;
  } finally {
    // Reset the flag when function completes
    window.logoutInProgress = false;
  }
}

// Also keep the original function name for backward compatibility
function logoutDeleteStorageTokens(){
  return window.logoutDeleteStorageTokens();
}
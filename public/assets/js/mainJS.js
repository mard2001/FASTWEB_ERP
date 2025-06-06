// var globalApi = "https://spc.sfa2.w-itsolutions.com/";
var globalApi = "http://127.0.0.1:8000/";


$(document).ready(function() {
  const user = localStorage.getItem('user');

  if (user) {
    const userObject = JSON.parse(user);
    $('#userName').text(userObject.name);
    $('#userEmail').text(userObject.email);
  }

  isTokenExist();
  GlobalUX();

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

    hamBurger.addEventListener("click", async function () {
      document.querySelector("#sidebar").classList.toggle("expand");
      
      if (!$('#sidebar').hasClass('expand')) {
        Array.from($('.showdropdown')).forEach(ul => {
          ul.classList.remove('showdropdown');
          ul.previousElementSibling.classList.remove('rotate');
        })
      }
    });

    // Get the pathname part of the URL
    var path = window.location.pathname;
    // Split the path by "/" and get the last segment
    var lastSegment = path.substring(path.lastIndexOf('/') + 1);
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

      function returnSideBarItemBaseOnIndex(i) {
        var sidebar = $('.sidebar-item').eq(i);
        sidebar.addClass('selectedlink');
        sidebar.find('span').addClass('selectedlinkSpan');
      }
    }
}

const Region = [
    {
      "region_id": 1,
      "region_name": "NCR",
      "region_description": "National Capital Region",
      "FIELD4": ""
    },
    {
      "region_id": 2,
      "region_name": "CAR",
      "region_description": "Cordillera Administrative Region",
      "FIELD4": ""
    },
    {
      "region_id": 3,
      "region_name": "Region I",
      "region_description": "Ilocos Region",
      "FIELD4": ""
    },
    {
      "region_id": 4,
      "region_name": "Region II",
      "region_description": "Cagayan Valley",
      "FIELD4": ""
    },
    {
      "region_id": 5,
      "region_name": "Region III",
      "region_description": "Central Luzon",
      "FIELD4": ""
    },
    {
      "region_id": 6,
      "region_name": "Region IV-A",
      "region_description": "CALABARZON",
      "FIELD4": ""
    },
    {
      "region_id": 7,
      "region_name": "Region IV-B",
      "region_description": "MIMAROPA",
      "FIELD4": ""
    },
    {
      "region_id": 8,
      "region_name": "Region V",
      "region_description": "Bicol Region",
      "FIELD4": ""
    },
    {
      "region_id": 9,
      "region_name": "Region VI",
      "region_description": "Western Visayas",
      "FIELD4": ""
    },
    {
      "region_id": 10,
      "region_name": "Region VII",
      "region_description": "Central Visayas",
      "FIELD4": ""
    },
    {
      "region_id": 11,
      "region_name": "Region VIII",
      "region_description": "Eastern Visayas",
      "FIELD4": ""
    },
    {
      "region_id": 12,
      "region_name": "Region IX",
      "region_description": "Zamboanga Peninsula",
      "FIELD4": ""
    },
    {
      "region_id": 13,
      "region_name": "Region X",
      "region_description": "Northern Mindanao",
      "FIELD4": ""
    },
    {
      "region_id": 14,
      "region_name": "Region XI",
      "region_description": "Davao Region",
      "FIELD4": ""
    },
    {
      "region_id": 15,
      "region_name": "Region XII",
      "region_description": "SOCCSKSARGEN",
      "FIELD4": ""
    },
    {
      "region_id": 16,
      "region_name": "Region XIII",
      "region_description": "CARAGA",
      "FIELD4": ""
    },
    {
      "region_id": 17,
      "region_name": "ARMM",
      "region_description": "Autonomous Region in Muslim Mindanao",
      "FIELD4": ""
    }
];

const Province = [
    {
      "province_id": 1,
      "region_id": 1,
      "province_name": "Metro Manila"
    },
    {
      "province_id": 2,
      "region_id": 3,
      "province_name": "Ilocos Norte"
    },
    {
      "province_id": 3,
      "region_id": 3,
      "province_name": "Ilocos Sur"
    },
    {
      "province_id": 4,
      "region_id": 3,
      "province_name": "La Union"
    },
    {
      "province_id": 5,
      "region_id": 3,
      "province_name": "Pangasinan"
    },
    {
      "province_id": 6,
      "region_id": 4,
      "province_name": "Batanes"
    },
    {
      "province_id": 7,
      "region_id": 4,
      "province_name": "Cagayan"
    },
    {
      "province_id": 8,
      "region_id": 4,
      "province_name": "Isabela"
    },
    {
      "province_id": 9,
      "region_id": 4,
      "province_name": "Nueva Vizcaya"
    },
    {
      "province_id": 10,
      "region_id": 4,
      "province_name": "Quirino"
    },
    {
      "province_id": 11,
      "region_id": 5,
      "province_name": "Bataan"
    },
    {
      "province_id": 12,
      "region_id": 5,
      "province_name": "Bulacan"
    },
    {
      "province_id": 13,
      "region_id": 5,
      "province_name": "Nueva Ecija"
    },
    {
      "province_id": 14,
      "region_id": 5,
      "province_name": "Pampanga"
    },
    {
      "province_id": 15,
      "region_id": 5,
      "province_name": "Tarlac"
    },
    {
      "province_id": 16,
      "region_id": 5,
      "province_name": "Zambales"
    },
    {
      "province_id": 17,
      "region_id": 5,
      "province_name": "Aurora"
    },
    {
      "province_id": 18,
      "region_id": 6,
      "province_name": "Batangas"
    },
    {
      "province_id": 19,
      "region_id": 6,
      "province_name": "Cavite"
    },
    {
      "province_id": 20,
      "region_id": 6,
      "province_name": "Laguna"
    },
    {
      "province_id": 21,
      "region_id": 6,
      "province_name": "Quezon"
    },
    {
      "province_id": 22,
      "region_id": 6,
      "province_name": "Rizal"
    },
    {
      "province_id": 23,
      "region_id": 7,
      "province_name": "Marinduque"
    },
    {
      "province_id": 24,
      "region_id": 7,
      "province_name": "Occidental Mindoro"
    },
    {
      "province_id": 25,
      "region_id": 7,
      "province_name": "Oriental Mindoro"
    },
    {
      "province_id": 26,
      "region_id": 7,
      "province_name": "Palawan"
    },
    {
      "province_id": 27,
      "region_id": 7,
      "province_name": "Romblon"
    },
    {
      "province_id": 28,
      "region_id": 8,
      "province_name": "Albay"
    },
    {
      "province_id": 29,
      "region_id": 8,
      "province_name": "Camarines Norte"
    },
    {
      "province_id": 30,
      "region_id": 8,
      "province_name": "Camarines Sur"
    },
    {
      "province_id": 31,
      "region_id": 8,
      "province_name": "Catanduanes"
    },
    {
      "province_id": 32,
      "region_id": 8,
      "province_name": "Masbate"
    },
    {
      "province_id": 33,
      "region_id": 8,
      "province_name": "Sorsogon"
    },
    {
      "province_id": 34,
      "region_id": 9,
      "province_name": "Aklan"
    },
    {
      "province_id": 35,
      "region_id": 9,
      "province_name": "Antique"
    },
    {
      "province_id": 36,
      "region_id": 9,
      "province_name": "Capiz"
    },
    {
      "province_id": 37,
      "region_id": 9,
      "province_name": "Iloilo"
    },
    {
      "province_id": 38,
      "region_id": 9,
      "province_name": "Negros Occidental"
    },
    {
      "province_id": 39,
      "region_id": 9,
      "province_name": "Guimaras"
    },
    {
      "province_id": 40,
      "region_id": 10,
      "province_name": "Bohol"
    },
    {
      "province_id": 41,
      "region_id": 10,
      "province_name": "Cebu"
    },
    {
      "province_id": 42,
      "region_id": 10,
      "province_name": "Negros Oriental"
    },
    {
      "province_id": 43,
      "region_id": 10,
      "province_name": "Siquijor"
    },
    {
      "province_id": 44,
      "region_id": 11,
      "province_name": "Eastern Samar"
    },
    {
      "province_id": 45,
      "region_id": 11,
      "province_name": "Leyte"
    },
    {
      "province_id": 46,
      "region_id": 11,
      "province_name": "Northern Samar"
    },
    {
      "province_id": 47,
      "region_id": 11,
      "province_name": "Samar"
    },
    {
      "province_id": 48,
      "region_id": 11,
      "province_name": "Southern Leyte"
    },
    {
      "province_id": 49,
      "region_id": 11,
      "province_name": "Biliran"
    },
    {
      "province_id": 50,
      "region_id": 12,
      "province_name": "Zamboanga del Norte"
    },
    {
      "province_id": 51,
      "region_id": 12,
      "province_name": "Zamboanga del Sur"
    },
    {
      "province_id": 52,
      "region_id": 12,
      "province_name": "Zamboanga Sibugay"
    },
    {
      "province_id": 53,
      "region_id": 13,
      "province_name": "Bukidnon"
    },
    {
      "province_id": 54,
      "region_id": 13,
      "province_name": "Camiguin"
    },
    {
      "province_id": 55,
      "region_id": 13,
      "province_name": "Lanao del Norte"
    },
    {
      "province_id": 56,
      "region_id": 13,
      "province_name": "Misamis Occidental"
    },
    {
      "province_id": 57,
      "region_id": 13,
      "province_name": "Misamis Oriental"
    },
    {
      "province_id": 58,
      "region_id": 14,
      "province_name": "Davao del Norte"
    },
    {
      "province_id": 59,
      "region_id": 14,
      "province_name": "Davao del Sur"
    },
    {
      "province_id": 60,
      "region_id": 14,
      "province_name": "Davao Oriental"
    },
    {
      "province_id": 61,
      "region_id": 14,
      "province_name": "Davao de Oro"
    },
    {
      "province_id": 62,
      "region_id": 14,
      "province_name": "Davao Occidental"
    },
    {
      "province_id": 63,
      "region_id": 15,
      "province_name": "Cotabato"
    },
    {
      "province_id": 64,
      "region_id": 15,
      "province_name": "South Cotabato"
    },
    {
      "province_id": 65,
      "region_id": 15,
      "province_name": "Sultan Kudarat"
    },
    {
      "province_id": 66,
      "region_id": 15,
      "province_name": "Sarangani"
    },
    {
      "province_id": 67,
      "region_id": 2,
      "province_name": "Abra"
    },
    {
      "province_id": 68,
      "region_id": 2,
      "province_name": "Benguet"
    },
    {
      "province_id": 69,
      "region_id": 2,
      "province_name": "Ifugao"
    },
    {
      "province_id": 70,
      "region_id": 2,
      "province_name": "Kalinga"
    },
    {
      "province_id": 71,
      "region_id": 2,
      "province_name": "Mountain Province"
    },
    {
      "province_id": 72,
      "region_id": 2,
      "province_name": "Apayao"
    },
    {
      "province_id": 73,
      "region_id": 17,
      "province_name": "Basilan"
    },
    {
      "province_id": 74,
      "region_id": 17,
      "province_name": "Lanao del Sur"
    },
    {
      "province_id": 75,
      "region_id": 17,
      "province_name": "Maguindanao"
    },
    {
      "province_id": 76,
      "region_id": 17,
      "province_name": "Sulu"
    },
    {
      "province_id": 77,
      "region_id": 17,
      "province_name": "Tawi-Tawi"
    },
    {
      "province_id": 78,
      "region_id": 16,
      "province_name": "Agusan del Norte"
    },
    {
      "province_id": 79,
      "region_id": 16,
      "province_name": "Agusan del Sur"
    },
    {
      "province_id": 80,
      "region_id": 16,
      "province_name": "Surigao del Norte"
    },
    {
      "province_id": 81,
      "region_id": 16,
      "province_name": "Surigao del Sur"
    },
    {
      "province_id": 82,
      "region_id": 16,
      "province_name": "Dinagat Islands"
    },
];

const Municipality = [
    {
      "municipality_id": 1,
      "province_id": 2,
      "municipality_name": "Adams"
    },
    {
      "municipality_id": 2,
      "province_id": 2,
      "municipality_name": "Bacarra"
    },
    {
      "municipality_id": 3,
      "province_id": 2,
      "municipality_name": "Badoc"
    },
    {
      "municipality_id": 4,
      "province_id": 2,
      "municipality_name": "Bangui"
    },
    {
      "municipality_id": 5,
      "province_id": 2,
      "municipality_name": "City of Batac"
    },
    {
      "municipality_id": 6,
      "province_id": 2,
      "municipality_name": "Burgos"
    },
    {
      "municipality_id": 7,
      "province_id": 2,
      "municipality_name": "Carasi"
    },
    {
      "municipality_id": 8,
      "province_id": 2,
      "municipality_name": "Currimao"
    },
    {
      "municipality_id": 9,
      "province_id": 2,
      "municipality_name": "Dingras"
    },
    {
      "municipality_id": 10,
      "province_id": 2,
      "municipality_name": "Dumalneg"
    },
    {
      "municipality_id": 11,
      "province_id": 2,
      "municipality_name": "Banna"
    },
    {
      "municipality_id": 12,
      "province_id": 2,
      "municipality_name": "City of Laoag"
    },
    {
      "municipality_id": 13,
      "province_id": 2,
      "municipality_name": "Marcos"
    },
    {
      "municipality_id": 14,
      "province_id": 2,
      "municipality_name": "Nueva Era"
    },
    {
      "municipality_id": 15,
      "province_id": 2,
      "municipality_name": "Pagudpud"
    },
    {
      "municipality_id": 16,
      "province_id": 2,
      "municipality_name": "Paoay"
    },
    {
      "municipality_id": 17,
      "province_id": 2,
      "municipality_name": "Pasuquin"
    },
    {
      "municipality_id": 18,
      "province_id": 2,
      "municipality_name": "Piddig"
    },
    {
      "municipality_id": 19,
      "province_id": 2,
      "municipality_name": "Pinili"
    },
    {
      "municipality_id": 20,
      "province_id": 2,
      "municipality_name": "San Nicolas"
    },
    {
      "municipality_id": 21,
      "province_id": 2,
      "municipality_name": "Sarrat"
    },
    {
      "municipality_id": 22,
      "province_id": 2,
      "municipality_name": "Solsona"
    },
    {
      "municipality_id": 23,
      "province_id": 2,
      "municipality_name": "Vintar"
    },
    {
      "municipality_id": 24,
      "province_id": 3,
      "municipality_name": "Alilem"
    },
    {
      "municipality_id": 25,
      "province_id": 3,
      "municipality_name": "Banayoyo"
    },
    {
      "municipality_id": 26,
      "province_id": 3,
      "municipality_name": "Bantay"
    },
    {
      "municipality_id": 27,
      "province_id": 3,
      "municipality_name": "Burgos"
    },
    {
      "municipality_id": 28,
      "province_id": 3,
      "municipality_name": "Cabugao"
    },
    {
      "municipality_id": 29,
      "province_id": 3,
      "municipality_name": "City of Candon"
    },
    {
      "municipality_id": 30,
      "province_id": 3,
      "municipality_name": "Caoayan"
    },
    {
      "municipality_id": 31,
      "province_id": 3,
      "municipality_name": "Cervantes"
    },
    {
      "municipality_id": 32,
      "province_id": 3,
      "municipality_name": "Galimuyod"
    },
    {
      "municipality_id": 33,
      "province_id": 3,
      "municipality_name": "Gregorio del Pilar"
    },
    {
      "municipality_id": 34,
      "province_id": 3,
      "municipality_name": "Lidlidda"
    },
    {
      "municipality_id": 35,
      "province_id": 3,
      "municipality_name": "Magsingal"
    },
    {
      "municipality_id": 36,
      "province_id": 3,
      "municipality_name": "Nagbukel"
    },
    {
      "municipality_id": 37,
      "province_id": 3,
      "municipality_name": "Narvacan"
    },
    {
      "municipality_id": 38,
      "province_id": 3,
      "municipality_name": "Quirino"
    },
    {
      "municipality_id": 39,
      "province_id": 3,
      "municipality_name": "Salcedo"
    },
    {
      "municipality_id": 40,
      "province_id": 3,
      "municipality_name": "San Emilio"
    },
    {
      "municipality_id": 41,
      "province_id": 3,
      "municipality_name": "San Esteban"
    },
    {
      "municipality_id": 42,
      "province_id": 3,
      "municipality_name": "San Ildefonso"
    },
    {
      "municipality_id": 43,
      "province_id": 3,
      "municipality_name": "San Juan"
    },
    {
      "municipality_id": 44,
      "province_id": 3,
      "municipality_name": "San Vicente"
    },
    {
      "municipality_id": 45,
      "province_id": 3,
      "municipality_name": "Santa"
    },
    {
      "municipality_id": 46,
      "province_id": 3,
      "municipality_name": "Santa Catalina"
    },
    {
      "municipality_id": 47,
      "province_id": 3,
      "municipality_name": "Santa Cruz"
    },
    {
      "municipality_id": 48,
      "province_id": 3,
      "municipality_name": "Santa Lucia"
    },
    {
      "municipality_id": 49,
      "province_id": 3,
      "municipality_name": "Santa Maria"
    },
    {
      "municipality_id": 50,
      "province_id": 3,
      "municipality_name": "Santiago"
    },
    {
      "municipality_id": 51,
      "province_id": 3,
      "municipality_name": "Santo Domingo"
    },
    {
      "municipality_id": 52,
      "province_id": 3,
      "municipality_name": "Sigay"
    },
    {
      "municipality_id": 53,
      "province_id": 3,
      "municipality_name": "Sinait"
    },
    {
      "municipality_id": 54,
      "province_id": 3,
      "municipality_name": "Sugpon"
    },
    {
      "municipality_id": 55,
      "province_id": 3,
      "municipality_name": "Suyo"
    },
    {
      "municipality_id": 56,
      "province_id": 3,
      "municipality_name": "Tagudin"
    },
    {
      "municipality_id": 57,
      "province_id": 3,
      "municipality_name": "City of Vigan"
    },
    {
      "municipality_id": 58,
      "province_id": 4,
      "municipality_name": "Agoo"
    },
    {
      "municipality_id": 59,
      "province_id": 4,
      "municipality_name": "Aringay"
    },
    {
      "municipality_id": 60,
      "province_id": 4,
      "municipality_name": "Bacnotan"
    },
    {
      "municipality_id": 61,
      "province_id": 4,
      "municipality_name": "Bagulin"
    },
    {
      "municipality_id": 62,
      "province_id": 4,
      "municipality_name": "Balaoan"
    },
    {
      "municipality_id": 63,
      "province_id": 4,
      "municipality_name": "Bangar"
    },
    {
      "municipality_id": 64,
      "province_id": 4,
      "municipality_name": "Bauang"
    },
    {
      "municipality_id": 65,
      "province_id": 4,
      "municipality_name": "Burgos"
    },
    {
      "municipality_id": 66,
      "province_id": 4,
      "municipality_name": "Caba"
    },
    {
      "municipality_id": 67,
      "province_id": 4,
      "municipality_name": "Luna"
    },
    {
      "municipality_id": 68,
      "province_id": 4,
      "municipality_name": "Naguilian"
    },
    {
      "municipality_id": 69,
      "province_id": 4,
      "municipality_name": "Pugo"
    },
    {
      "municipality_id": 70,
      "province_id": 4,
      "municipality_name": "Rosario"
    },
    {
      "municipality_id": 71,
      "province_id": 4,
      "municipality_name": "City of San Fernando"
    },
    {
      "municipality_id": 72,
      "province_id": 4,
      "municipality_name": "San Gabriel"
    },
    {
      "municipality_id": 73,
      "province_id": 4,
      "municipality_name": "San Juan"
    },
    {
      "municipality_id": 74,
      "province_id": 4,
      "municipality_name": "Santo Tomas"
    },
    {
      "municipality_id": 75,
      "province_id": 4,
      "municipality_name": "Santol"
    },
    {
      "municipality_id": 76,
      "province_id": 4,
      "municipality_name": "Sudipen"
    },
    {
      "municipality_id": 77,
      "province_id": 4,
      "municipality_name": "Tubao"
    },
    {
      "municipality_id": 78,
      "province_id": 5,
      "municipality_name": "Agno"
    },
    {
      "municipality_id": 79,
      "province_id": 5,
      "municipality_name": "Aguilar"
    },
    {
      "municipality_id": 80,
      "province_id": 5,
      "municipality_name": "City of Alaminos"
    },
    {
      "municipality_id": 81,
      "province_id": 5,
      "municipality_name": "Alcala"
    },
    {
      "municipality_id": 82,
      "province_id": 5,
      "municipality_name": "Anda"
    },
    {
      "municipality_id": 83,
      "province_id": 5,
      "municipality_name": "Asingan"
    },
    {
      "municipality_id": 84,
      "province_id": 5,
      "municipality_name": "Balungao"
    },
    {
      "municipality_id": 85,
      "province_id": 5,
      "municipality_name": "Bani"
    },
    {
      "municipality_id": 86,
      "province_id": 5,
      "municipality_name": "Basista"
    },
    {
      "municipality_id": 87,
      "province_id": 5,
      "municipality_name": "Bautista"
    },
    {
      "municipality_id": 88,
      "province_id": 5,
      "municipality_name": "Bayambang"
    },
    {
      "municipality_id": 89,
      "province_id": 5,
      "municipality_name": "Binalonan"
    },
    {
      "municipality_id": 90,
      "province_id": 5,
      "municipality_name": "Binmaley"
    },
    {
      "municipality_id": 91,
      "province_id": 5,
      "municipality_name": "Bolinao"
    },
    {
      "municipality_id": 92,
      "province_id": 5,
      "municipality_name": "Bugallon"
    },
    {
      "municipality_id": 93,
      "province_id": 5,
      "municipality_name": "Burgos"
    },
    {
      "municipality_id": 94,
      "province_id": 5,
      "municipality_name": "Calasiao"
    },
    {
      "municipality_id": 95,
      "province_id": 5,
      "municipality_name": "City of Dagupan"
    },
    {
      "municipality_id": 96,
      "province_id": 5,
      "municipality_name": "Dasol"
    },
    {
      "municipality_id": 97,
      "province_id": 5,
      "municipality_name": "Infanta"
    },
    {
      "municipality_id": 98,
      "province_id": 5,
      "municipality_name": "Labrador"
    },
    {
      "municipality_id": 99,
      "province_id": 5,
      "municipality_name": "Lingayen"
    },
    {
      "municipality_id": 100,
      "province_id": 5,
      "municipality_name": "Mabini"
    },
    {
      "municipality_id": 101,
      "province_id": 5,
      "municipality_name": "Malasiqui"
    },
    {
      "municipality_id": 102,
      "province_id": 5,
      "municipality_name": "Manaoag"
    },
    {
      "municipality_id": 103,
      "province_id": 5,
      "municipality_name": "Mangaldan"
    },
    {
      "municipality_id": 104,
      "province_id": 5,
      "municipality_name": "Mangatarem"
    },
    {
      "municipality_id": 105,
      "province_id": 5,
      "municipality_name": "Mapandan"
    },
    {
      "municipality_id": 106,
      "province_id": 5,
      "municipality_name": "Natividad"
    },
    {
      "municipality_id": 107,
      "province_id": 5,
      "municipality_name": "Pozorrubio"
    },
    {
      "municipality_id": 108,
      "province_id": 5,
      "municipality_name": "Rosales"
    },
    {
      "municipality_id": 109,
      "province_id": 5,
      "municipality_name": "City of San Carlos"
    },
    {
      "municipality_id": 110,
      "province_id": 5,
      "municipality_name": "San Fabian"
    },
    {
      "municipality_id": 111,
      "province_id": 5,
      "municipality_name": "San Jacinto"
    },
    {
      "municipality_id": 112,
      "province_id": 5,
      "municipality_name": "San Manuel"
    },
    {
      "municipality_id": 113,
      "province_id": 5,
      "municipality_name": "San Nicolas"
    },
    {
      "municipality_id": 114,
      "province_id": 5,
      "municipality_name": "San Quintin"
    },
    {
      "municipality_id": 115,
      "province_id": 5,
      "municipality_name": "Santa Barbara"
    },
    {
      "municipality_id": 116,
      "province_id": 5,
      "municipality_name": "Santa Maria"
    },
    {
      "municipality_id": 117,
      "province_id": 5,
      "municipality_name": "Santo Tomas"
    },
    {
      "municipality_id": 118,
      "province_id": 5,
      "municipality_name": "Sison"
    },
    {
      "municipality_id": 119,
      "province_id": 5,
      "municipality_name": "Sual"
    },
    {
      "municipality_id": 120,
      "province_id": 5,
      "municipality_name": "Tayug"
    },
    {
      "municipality_id": 121,
      "province_id": 5,
      "municipality_name": "Umingan"
    },
    {
      "municipality_id": 122,
      "province_id": 5,
      "municipality_name": "Urbiztondo"
    },
    {
      "municipality_id": 123,
      "province_id": 5,
      "municipality_name": "City of Urdaneta"
    },
    {
      "municipality_id": 124,
      "province_id": 5,
      "municipality_name": "Villasis"
    },
    {
      "municipality_id": 125,
      "province_id": 5,
      "municipality_name": "Laoac"
    },
    {
      "municipality_id": 126,
      "province_id": 6,
      "municipality_name": "Basco"
    },
    {
      "municipality_id": 127,
      "province_id": 6,
      "municipality_name": "Itbayat"
    },
    {
      "municipality_id": 128,
      "province_id": 6,
      "municipality_name": "Ivana"
    },
    {
      "municipality_id": 129,
      "province_id": 6,
      "municipality_name": "Mahatao"
    },
    {
      "municipality_id": 130,
      "province_id": 6,
      "municipality_name": "Sabtang"
    },
    {
      "municipality_id": 131,
      "province_id": 6,
      "municipality_name": "Uyugan"
    },
    {
      "municipality_id": 132,
      "province_id": 7,
      "municipality_name": "Abulug"
    },
    {
      "municipality_id": 133,
      "province_id": 7,
      "municipality_name": "Alcala"
    },
    {
      "municipality_id": 134,
      "province_id": 7,
      "municipality_name": "Allacapan"
    },
    {
      "municipality_id": 135,
      "province_id": 7,
      "municipality_name": "Amulung"
    },
    {
      "municipality_id": 136,
      "province_id": 7,
      "municipality_name": "Aparri"
    },
    {
      "municipality_id": 137,
      "province_id": 7,
      "municipality_name": "Baggao"
    },
    {
      "municipality_id": 138,
      "province_id": 7,
      "municipality_name": "Ballesteros"
    },
    {
      "municipality_id": 139,
      "province_id": 7,
      "municipality_name": "Buguey"
    },
    {
      "municipality_id": 140,
      "province_id": 7,
      "municipality_name": "Calayan"
    },
    {
      "municipality_id": 141,
      "province_id": 7,
      "municipality_name": "Camalaniugan"
    },
    {
      "municipality_id": 142,
      "province_id": 7,
      "municipality_name": "Claveria"
    },
    {
      "municipality_id": 143,
      "province_id": 7,
      "municipality_name": "Enrile"
    },
    {
      "municipality_id": 144,
      "province_id": 7,
      "municipality_name": "Gattaran"
    },
    {
      "municipality_id": 145,
      "province_id": 7,
      "municipality_name": "Gonzaga"
    },
    {
      "municipality_id": 146,
      "province_id": 7,
      "municipality_name": "Iguig"
    },
    {
      "municipality_id": 147,
      "province_id": 7,
      "municipality_name": "Lal-Lo"
    },
    {
      "municipality_id": 148,
      "province_id": 7,
      "municipality_name": "Lasam"
    },
    {
      "municipality_id": 149,
      "province_id": 7,
      "municipality_name": "Pamplona"
    },
    {
      "municipality_id": 150,
      "province_id": 7,
      "municipality_name": "Peñablanca"
    },
    {
      "municipality_id": 151,
      "province_id": 7,
      "municipality_name": "Piat"
    },
    {
      "municipality_id": 152,
      "province_id": 7,
      "municipality_name": "Rizal"
    },
    {
      "municipality_id": 153,
      "province_id": 7,
      "municipality_name": "Sanchez-Mira"
    },
    {
      "municipality_id": 154,
      "province_id": 7,
      "municipality_name": "Santa Ana"
    },
    {
      "municipality_id": 155,
      "province_id": 7,
      "municipality_name": "Santa Praxedes"
    },
    {
      "municipality_id": 156,
      "province_id": 7,
      "municipality_name": "Santa Teresita"
    },
    {
      "municipality_id": 157,
      "province_id": 7,
      "municipality_name": "Santo Niño"
    },
    {
      "municipality_id": 158,
      "province_id": 7,
      "municipality_name": "Solana"
    },
    {
      "municipality_id": 159,
      "province_id": 7,
      "municipality_name": "Tuao"
    },
    {
      "municipality_id": 160,
      "province_id": 7,
      "municipality_name": "Tuguegarao City"
    },
    {
      "municipality_id": 161,
      "province_id": 8,
      "municipality_name": "Alicia"
    },
    {
      "municipality_id": 162,
      "province_id": 8,
      "municipality_name": "Angadanan"
    },
    {
      "municipality_id": 163,
      "province_id": 8,
      "municipality_name": "Aurora"
    },
    {
      "municipality_id": 164,
      "province_id": 8,
      "municipality_name": "Benito Soliven"
    },
    {
      "municipality_id": 165,
      "province_id": 8,
      "municipality_name": "Burgos"
    },
    {
      "municipality_id": 166,
      "province_id": 8,
      "municipality_name": "Cabagan"
    },
    {
      "municipality_id": 167,
      "province_id": 8,
      "municipality_name": "Cabatuan"
    },
    {
      "municipality_id": 168,
      "province_id": 8,
      "municipality_name": "City of Cauayan"
    },
    {
      "municipality_id": 169,
      "province_id": 8,
      "municipality_name": "Cordon"
    },
    {
      "municipality_id": 170,
      "province_id": 8,
      "municipality_name": "Dinapigue"
    },
    {
      "municipality_id": 171,
      "province_id": 8,
      "municipality_name": "Divilacan"
    },
    {
      "municipality_id": 172,
      "province_id": 8,
      "municipality_name": "Echague"
    },
    {
      "municipality_id": 173,
      "province_id": 8,
      "municipality_name": "Gamu"
    },
    {
      "municipality_id": 174,
      "province_id": 8,
      "municipality_name": "City of Ilagan"
    },
    {
      "municipality_id": 175,
      "province_id": 8,
      "municipality_name": "Jones"
    },
    {
      "municipality_id": 176,
      "province_id": 8,
      "municipality_name": "Luna"
    },
    {
      "municipality_id": 177,
      "province_id": 8,
      "municipality_name": "Maconacon"
    },
    {
      "municipality_id": 178,
      "province_id": 8,
      "municipality_name": "Delfin Albano"
    },
    {
      "municipality_id": 179,
      "province_id": 8,
      "municipality_name": "Mallig"
    },
    {
      "municipality_id": 180,
      "province_id": 8,
      "municipality_name": "Naguilian"
    },
    {
      "municipality_id": 181,
      "province_id": 8,
      "municipality_name": "Palanan"
    },
    {
      "municipality_id": 182,
      "province_id": 8,
      "municipality_name": "Quezon"
    },
    {
      "municipality_id": 183,
      "province_id": 8,
      "municipality_name": "Quirino"
    },
    {
      "municipality_id": 184,
      "province_id": 8,
      "municipality_name": "Ramon"
    },
    {
      "municipality_id": 185,
      "province_id": 8,
      "municipality_name": "Reina Mercedes"
    },
    {
      "municipality_id": 186,
      "province_id": 8,
      "municipality_name": "Roxas"
    },
    {
      "municipality_id": 187,
      "province_id": 8,
      "municipality_name": "San Agustin"
    },
    {
      "municipality_id": 188,
      "province_id": 8,
      "municipality_name": "San Guillermo"
    },
    {
      "municipality_id": 189,
      "province_id": 8,
      "municipality_name": "San Isidro"
    },
    {
      "municipality_id": 190,
      "province_id": 8,
      "municipality_name": "San Manuel"
    },
    {
      "municipality_id": 191,
      "province_id": 8,
      "municipality_name": "San Mariano"
    },
    {
      "municipality_id": 192,
      "province_id": 8,
      "municipality_name": "San Mateo"
    },
    {
      "municipality_id": 193,
      "province_id": 8,
      "municipality_name": "San Pablo"
    },
    {
      "municipality_id": 194,
      "province_id": 8,
      "municipality_name": "Santa Maria"
    },
    {
      "municipality_id": 195,
      "province_id": 8,
      "municipality_name": "City of Santiago"
    },
    {
      "municipality_id": 196,
      "province_id": 8,
      "municipality_name": "Santo Tomas"
    },
    {
      "municipality_id": 197,
      "province_id": 8,
      "municipality_name": "Tumauini"
    },
    {
      "municipality_id": 198,
      "province_id": 9,
      "municipality_name": "Ambaguio"
    },
    {
      "municipality_id": 199,
      "province_id": 9,
      "municipality_name": "Aritao"
    },
    {
      "municipality_id": 200,
      "province_id": 9,
      "municipality_name": "Bagabag"
    },
    {
      "municipality_id": 201,
      "province_id": 9,
      "municipality_name": "Bambang"
    },
    {
      "municipality_id": 202,
      "province_id": 9,
      "municipality_name": "Bayombong"
    },
    {
      "municipality_id": 203,
      "province_id": 9,
      "municipality_name": "Diadi"
    },
    {
      "municipality_id": 204,
      "province_id": 9,
      "municipality_name": "Dupax del Norte"
    },
    {
      "municipality_id": 205,
      "province_id": 9,
      "municipality_name": "Dupax del Sur"
    },
    {
      "municipality_id": 206,
      "province_id": 9,
      "municipality_name": "Kasibu"
    },
    {
      "municipality_id": 207,
      "province_id": 9,
      "municipality_name": "Kayapa"
    },
    {
      "municipality_id": 208,
      "province_id": 9,
      "municipality_name": "Quezon"
    },
    {
      "municipality_id": 209,
      "province_id": 9,
      "municipality_name": "Santa Fe"
    },
    {
      "municipality_id": 210,
      "province_id": 9,
      "municipality_name": "Solano"
    },
    {
      "municipality_id": 211,
      "province_id": 9,
      "municipality_name": "Villaverde"
    },
    {
      "municipality_id": 212,
      "province_id": 9,
      "municipality_name": "Alfonso Castaneda"
    },
    {
      "municipality_id": 213,
      "province_id": 10,
      "municipality_name": "Aglipay"
    },
    {
      "municipality_id": 214,
      "province_id": 10,
      "municipality_name": "Cabarroguis"
    },
    {
      "municipality_id": 215,
      "province_id": 10,
      "municipality_name": "Diffun"
    },
    {
      "municipality_id": 216,
      "province_id": 10,
      "municipality_name": "Maddela"
    },
    {
      "municipality_id": 217,
      "province_id": 10,
      "municipality_name": "Saguday"
    },
    {
      "municipality_id": 218,
      "province_id": 10,
      "municipality_name": "Nagtipunan"
    },
    {
      "municipality_id": 219,
      "province_id": 11,
      "municipality_name": "Abucay"
    },
    {
      "municipality_id": 220,
      "province_id": 11,
      "municipality_name": "Bagac"
    },
    {
      "municipality_id": 221,
      "province_id": 11,
      "municipality_name": "City of Balanga"
    },
    {
      "municipality_id": 222,
      "province_id": 11,
      "municipality_name": "Dinalupihan"
    },
    {
      "municipality_id": 223,
      "province_id": 11,
      "municipality_name": "Hermosa"
    },
    {
      "municipality_id": 224,
      "province_id": 11,
      "municipality_name": "Limay"
    },
    {
      "municipality_id": 225,
      "province_id": 11,
      "municipality_name": "Mariveles"
    },
    {
      "municipality_id": 226,
      "province_id": 11,
      "municipality_name": "Morong"
    },
    {
      "municipality_id": 227,
      "province_id": 11,
      "municipality_name": "Orani"
    },
    {
      "municipality_id": 228,
      "province_id": 11,
      "municipality_name": "Orion"
    },
    {
      "municipality_id": 229,
      "province_id": 11,
      "municipality_name": "Pilar"
    },
    {
      "municipality_id": 230,
      "province_id": 11,
      "municipality_name": "Samal"
    },
    {
      "municipality_id": 231,
      "province_id": 12,
      "municipality_name": "Angat"
    },
    {
      "municipality_id": 232,
      "province_id": 12,
      "municipality_name": "Balagtas"
    },
    {
      "municipality_id": 233,
      "province_id": 12,
      "municipality_name": "Baliuag"
    },
    {
      "municipality_id": 234,
      "province_id": 12,
      "municipality_name": "Bocaue"
    },
    {
      "municipality_id": 235,
      "province_id": 12,
      "municipality_name": "Bulacan"
    },
    {
      "municipality_id": 236,
      "province_id": 12,
      "municipality_name": "Bustos"
    },
    {
      "municipality_id": 237,
      "province_id": 12,
      "municipality_name": "Calumpit"
    },
    {
      "municipality_id": 238,
      "province_id": 12,
      "municipality_name": "Guiguinto"
    },
    {
      "municipality_id": 239,
      "province_id": 12,
      "municipality_name": "Hagonoy"
    },
    {
      "municipality_id": 240,
      "province_id": 12,
      "municipality_name": "City of Malolos"
    },
    {
      "municipality_id": 241,
      "province_id": 12,
      "municipality_name": "Marilao"
    },
    {
      "municipality_id": 242,
      "province_id": 12,
      "municipality_name": "City of Meycauayan"
    },
    {
      "municipality_id": 243,
      "province_id": 12,
      "municipality_name": "Norzagaray"
    },
    {
      "municipality_id": 244,
      "province_id": 12,
      "municipality_name": "Obando"
    },
    {
      "municipality_id": 245,
      "province_id": 12,
      "municipality_name": "Pandi"
    },
    {
      "municipality_id": 246,
      "province_id": 12,
      "municipality_name": "Paombong"
    },
    {
      "municipality_id": 247,
      "province_id": 12,
      "municipality_name": "Plaridel"
    },
    {
      "municipality_id": 248,
      "province_id": 12,
      "municipality_name": "Pulilan"
    },
    {
      "municipality_id": 249,
      "province_id": 12,
      "municipality_name": "San Ildefonso"
    },
    {
      "municipality_id": 250,
      "province_id": 12,
      "municipality_name": "City of San Jose Del Monte"
    },
    {
      "municipality_id": 251,
      "province_id": 12,
      "municipality_name": "San Miguel"
    },
    {
      "municipality_id": 252,
      "province_id": 12,
      "municipality_name": "San Rafael"
    },
    {
      "municipality_id": 253,
      "province_id": 12,
      "municipality_name": "Santa Maria"
    },
    {
      "municipality_id": 254,
      "province_id": 12,
      "municipality_name": "Doña Remedios Trinidad"
    },
    {
      "municipality_id": 255,
      "province_id": 13,
      "municipality_name": "Aliaga"
    },
    {
      "municipality_id": 256,
      "province_id": 13,
      "municipality_name": "Bongabon"
    },
    {
      "municipality_id": 257,
      "province_id": 13,
      "municipality_name": "City of Cabanatuan"
    },
    {
      "municipality_id": 258,
      "province_id": 13,
      "municipality_name": "Cabiao"
    },
    {
      "municipality_id": 259,
      "province_id": 13,
      "municipality_name": "Carranglan"
    },
    {
      "municipality_id": 260,
      "province_id": 13,
      "municipality_name": "Cuyapo"
    },
    {
      "municipality_id": 261,
      "province_id": 13,
      "municipality_name": "Gabaldon"
    },
    {
      "municipality_id": 262,
      "province_id": 13,
      "municipality_name": "City of Gapan"
    },
    {
      "municipality_id": 263,
      "province_id": 13,
      "municipality_name": "General Mamerto Natividad"
    },
    {
      "municipality_id": 264,
      "province_id": 13,
      "municipality_name": "General Tinio"
    },
    {
      "municipality_id": 265,
      "province_id": 13,
      "municipality_name": "Guimba"
    },
    {
      "municipality_id": 266,
      "province_id": 13,
      "municipality_name": "Jaen"
    },
    {
      "municipality_id": 267,
      "province_id": 13,
      "municipality_name": "Laur"
    },
    {
      "municipality_id": 268,
      "province_id": 13,
      "municipality_name": "Licab"
    },
    {
      "municipality_id": 269,
      "province_id": 13,
      "municipality_name": "Llanera"
    },
    {
      "municipality_id": 270,
      "province_id": 13,
      "municipality_name": "Lupao"
    },
    {
      "municipality_id": 271,
      "province_id": 13,
      "municipality_name": "Science City of Muñoz"
    },
    {
      "municipality_id": 272,
      "province_id": 13,
      "municipality_name": "Nampicuan"
    },
    {
      "municipality_id": 273,
      "province_id": 13,
      "municipality_name": "City of Palayan"
    },
    {
      "municipality_id": 274,
      "province_id": 13,
      "municipality_name": "Pantabangan"
    },
    {
      "municipality_id": 275,
      "province_id": 13,
      "municipality_name": "Peñaranda"
    },
    {
      "municipality_id": 276,
      "province_id": 13,
      "municipality_name": "Quezon"
    },
    {
      "municipality_id": 277,
      "province_id": 13,
      "municipality_name": "Rizal"
    },
    {
      "municipality_id": 278,
      "province_id": 13,
      "municipality_name": "San Antonio"
    },
    {
      "municipality_id": 279,
      "province_id": 13,
      "municipality_name": "San Isidro"
    },
    {
      "municipality_id": 280,
      "province_id": 13,
      "municipality_name": "San Jose City"
    },
    {
      "municipality_id": 281,
      "province_id": 13,
      "municipality_name": "San Leonardo"
    },
    {
      "municipality_id": 282,
      "province_id": 13,
      "municipality_name": "Santa Rosa"
    },
    {
      "municipality_id": 283,
      "province_id": 13,
      "municipality_name": "Santo Domingo"
    },
    {
      "municipality_id": 284,
      "province_id": 13,
      "municipality_name": "Talavera"
    },
    {
      "municipality_id": 285,
      "province_id": 13,
      "municipality_name": "Talugtug"
    },
    {
      "municipality_id": 286,
      "province_id": 13,
      "municipality_name": "Zaragoza"
    },
    {
      "municipality_id": 287,
      "province_id": 14,
      "municipality_name": "City of Angeles"
    },
    {
      "municipality_id": 288,
      "province_id": 14,
      "municipality_name": "Apalit"
    },
    {
      "municipality_id": 289,
      "province_id": 14,
      "municipality_name": "Arayat"
    },
    {
      "municipality_id": 290,
      "province_id": 14,
      "municipality_name": "Bacolor"
    },
    {
      "municipality_id": 291,
      "province_id": 14,
      "municipality_name": "Candaba"
    },
    {
      "municipality_id": 292,
      "province_id": 14,
      "municipality_name": "Floridablanca"
    },
    {
      "municipality_id": 293,
      "province_id": 14,
      "municipality_name": "Guagua"
    },
    {
      "municipality_id": 294,
      "province_id": 14,
      "municipality_name": "Lubao"
    },
    {
      "municipality_id": 295,
      "province_id": 14,
      "municipality_name": "Mabalacat City"
    },
    {
      "municipality_id": 296,
      "province_id": 14,
      "municipality_name": "Macabebe"
    },
    {
      "municipality_id": 297,
      "province_id": 14,
      "municipality_name": "Magalang"
    },
    {
      "municipality_id": 298,
      "province_id": 14,
      "municipality_name": "Masantol"
    },
    {
      "municipality_id": 299,
      "province_id": 14,
      "municipality_name": "Mexico"
    },
    {
      "municipality_id": 300,
      "province_id": 14,
      "municipality_name": "Minalin"
    },
    {
      "municipality_id": 301,
      "province_id": 14,
      "municipality_name": "Porac"
    },
    {
      "municipality_id": 302,
      "province_id": 14,
      "municipality_name": "City of San Fernando"
    },
    {
      "municipality_id": 303,
      "province_id": 14,
      "municipality_name": "San Luis"
    },
    {
      "municipality_id": 304,
      "province_id": 14,
      "municipality_name": "San Simon"
    },
    {
      "municipality_id": 305,
      "province_id": 14,
      "municipality_name": "Santa Ana"
    },
    {
      "municipality_id": 306,
      "province_id": 14,
      "municipality_name": "Santa Rita"
    },
    {
      "municipality_id": 307,
      "province_id": 14,
      "municipality_name": "Santo Tomas"
    },
    {
      "municipality_id": 308,
      "province_id": 14,
      "municipality_name": "Sasmuan"
    },
    {
      "municipality_id": 309,
      "province_id": 15,
      "municipality_name": "Anao"
    },
    {
      "municipality_id": 310,
      "province_id": 15,
      "municipality_name": "Bamban"
    },
    {
      "municipality_id": 311,
      "province_id": 15,
      "municipality_name": "Camiling"
    },
    {
      "municipality_id": 312,
      "province_id": 15,
      "municipality_name": "Capas"
    },
    {
      "municipality_id": 313,
      "province_id": 15,
      "municipality_name": "Concepcion"
    },
    {
      "municipality_id": 314,
      "province_id": 15,
      "municipality_name": "Gerona"
    },
    {
      "municipality_id": 315,
      "province_id": 15,
      "municipality_name": "La Paz"
    },
    {
      "municipality_id": 316,
      "province_id": 15,
      "municipality_name": "Mayantoc"
    },
    {
      "municipality_id": 317,
      "province_id": 15,
      "municipality_name": "Moncada"
    },
    {
      "municipality_id": 318,
      "province_id": 15,
      "municipality_name": "Paniqui"
    },
    {
      "municipality_id": 319,
      "province_id": 15,
      "municipality_name": "Pura"
    },
    {
      "municipality_id": 320,
      "province_id": 15,
      "municipality_name": "Ramos"
    },
    {
      "municipality_id": 321,
      "province_id": 15,
      "municipality_name": "San Clemente"
    },
    {
      "municipality_id": 322,
      "province_id": 15,
      "municipality_name": "San Manuel"
    },
    {
      "municipality_id": 323,
      "province_id": 15,
      "municipality_name": "Santa Ignacia"
    },
    {
      "municipality_id": 324,
      "province_id": 15,
      "municipality_name": "City of Tarlac"
    },
    {
      "municipality_id": 325,
      "province_id": 15,
      "municipality_name": "Victoria"
    },
    {
      "municipality_id": 326,
      "province_id": 15,
      "municipality_name": "San Jose"
    },
    {
      "municipality_id": 327,
      "province_id": 16,
      "municipality_name": "Botolan"
    },
    {
      "municipality_id": 328,
      "province_id": 16,
      "municipality_name": "Cabangan"
    },
    {
      "municipality_id": 329,
      "province_id": 16,
      "municipality_name": "Candelaria"
    },
    {
      "municipality_id": 330,
      "province_id": 16,
      "municipality_name": "Castillejos"
    },
    {
      "municipality_id": 331,
      "province_id": 16,
      "municipality_name": "Iba"
    },
    {
      "municipality_id": 332,
      "province_id": 16,
      "municipality_name": "Masinloc"
    },
    {
      "municipality_id": 333,
      "province_id": 16,
      "municipality_name": "City of Olongapo"
    },
    {
      "municipality_id": 334,
      "province_id": 16,
      "municipality_name": "Palauig"
    },
    {
      "municipality_id": 335,
      "province_id": 16,
      "municipality_name": "San Antonio"
    },
    {
      "municipality_id": 336,
      "province_id": 16,
      "municipality_name": "San Felipe"
    },
    {
      "municipality_id": 337,
      "province_id": 16,
      "municipality_name": "San Marcelino"
    },
    {
      "municipality_id": 338,
      "province_id": 16,
      "municipality_name": "San Narciso"
    },
    {
      "municipality_id": 339,
      "province_id": 16,
      "municipality_name": "Santa Cruz"
    },
    {
      "municipality_id": 340,
      "province_id": 16,
      "municipality_name": "Subic"
    },
    {
      "municipality_id": 341,
      "province_id": 17,
      "municipality_name": "Baler"
    },
    {
      "municipality_id": 342,
      "province_id": 17,
      "municipality_name": "Casiguran"
    },
    {
      "municipality_id": 343,
      "province_id": 17,
      "municipality_name": "Dilasag"
    },
    {
      "municipality_id": 344,
      "province_id": 17,
      "municipality_name": "Dinalungan"
    },
    {
      "municipality_id": 345,
      "province_id": 17,
      "municipality_name": "Dingalan"
    },
    {
      "municipality_id": 346,
      "province_id": 17,
      "municipality_name": "Dipaculao"
    },
    {
      "municipality_id": 347,
      "province_id": 17,
      "municipality_name": "Maria Aurora"
    },
    {
      "municipality_id": 348,
      "province_id": 17,
      "municipality_name": "San Luis"
    },
    {
      "municipality_id": 349,
      "province_id": 18,
      "municipality_name": "Agoncillo"
    },
    {
      "municipality_id": 350,
      "province_id": 18,
      "municipality_name": "Alitagtag"
    },
    {
      "municipality_id": 351,
      "province_id": 18,
      "municipality_name": "Balayan"
    },
    {
      "municipality_id": 352,
      "province_id": 18,
      "municipality_name": "Balete"
    },
    {
      "municipality_id": 353,
      "province_id": 18,
      "municipality_name": "Batangas City"
    },
    {
      "municipality_id": 354,
      "province_id": 18,
      "municipality_name": "Bauan"
    },
    {
      "municipality_id": 355,
      "province_id": 18,
      "municipality_name": "Calaca"
    },
    {
      "municipality_id": 356,
      "province_id": 18,
      "municipality_name": "Calatagan"
    },
    {
      "municipality_id": 357,
      "province_id": 18,
      "municipality_name": "Cuenca"
    },
    {
      "municipality_id": 358,
      "province_id": 18,
      "municipality_name": "Ibaan"
    },
    {
      "municipality_id": 359,
      "province_id": 18,
      "municipality_name": "Laurel"
    },
    {
      "municipality_id": 360,
      "province_id": 18,
      "municipality_name": "Lemery"
    },
    {
      "municipality_id": 361,
      "province_id": 18,
      "municipality_name": "Lian"
    },
    {
      "municipality_id": 362,
      "province_id": 18,
      "municipality_name": "City of Lipa"
    },
    {
      "municipality_id": 363,
      "province_id": 18,
      "municipality_name": "Lobo"
    },
    {
      "municipality_id": 364,
      "province_id": 18,
      "municipality_name": "Mabini"
    },
    {
      "municipality_id": 365,
      "province_id": 18,
      "municipality_name": "Malvar"
    },
    {
      "municipality_id": 366,
      "province_id": 18,
      "municipality_name": "Mataasnakahoy"
    },
    {
      "municipality_id": 367,
      "province_id": 18,
      "municipality_name": "Nasugbu"
    },
    {
      "municipality_id": 368,
      "province_id": 18,
      "municipality_name": "Padre Garcia"
    },
    {
      "municipality_id": 369,
      "province_id": 18,
      "municipality_name": "Rosario"
    },
    {
      "municipality_id": 370,
      "province_id": 18,
      "municipality_name": "San Jose"
    },
    {
      "municipality_id": 371,
      "province_id": 18,
      "municipality_name": "San Juan"
    },
    {
      "municipality_id": 372,
      "province_id": 18,
      "municipality_name": "San Luis"
    },
    {
      "municipality_id": 373,
      "province_id": 18,
      "municipality_name": "San Nicolas"
    },
    {
      "municipality_id": 374,
      "province_id": 18,
      "municipality_name": "San Pascual"
    },
    {
      "municipality_id": 375,
      "province_id": 18,
      "municipality_name": "Santa Teresita"
    },
    {
      "municipality_id": 376,
      "province_id": 18,
      "municipality_name": "City of Sto. Tomas"
    },
    {
      "municipality_id": 377,
      "province_id": 18,
      "municipality_name": "Taal"
    },
    {
      "municipality_id": 378,
      "province_id": 18,
      "municipality_name": "Talisay"
    },
    {
      "municipality_id": 379,
      "province_id": 18,
      "municipality_name": "City of Tanauan"
    },
    {
      "municipality_id": 380,
      "province_id": 18,
      "municipality_name": "Taysan"
    },
    {
      "municipality_id": 381,
      "province_id": 18,
      "municipality_name": "Tingloy"
    },
    {
      "municipality_id": 382,
      "province_id": 18,
      "municipality_name": "Tuy"
    },
    {
      "municipality_id": 383,
      "province_id": 19,
      "municipality_name": "Alfonso"
    },
    {
      "municipality_id": 384,
      "province_id": 19,
      "municipality_name": "Amadeo"
    },
    {
      "municipality_id": 385,
      "province_id": 19,
      "municipality_name": "City of Bacoor"
    },
    {
      "municipality_id": 386,
      "province_id": 19,
      "municipality_name": "Carmona"
    },
    {
      "municipality_id": 387,
      "province_id": 19,
      "municipality_name": "City of Cavite"
    },
    {
      "municipality_id": 388,
      "province_id": 19,
      "municipality_name": "City of Dasmariñas"
    },
    {
      "municipality_id": 389,
      "province_id": 19,
      "municipality_name": "General Emilio Aguinaldo"
    },
    {
      "municipality_id": 390,
      "province_id": 19,
      "municipality_name": "City of General Trias"
    },
    {
      "municipality_id": 391,
      "province_id": 19,
      "municipality_name": "City of Imus"
    },
    {
      "municipality_id": 392,
      "province_id": 19,
      "municipality_name": "Indang"
    },
    {
      "municipality_id": 393,
      "province_id": 19,
      "municipality_name": "Kawit"
    },
    {
      "municipality_id": 394,
      "province_id": 19,
      "municipality_name": "Magallanes"
    },
    {
      "municipality_id": 395,
      "province_id": 19,
      "municipality_name": "Maragondon"
    },
    {
      "municipality_id": 396,
      "province_id": 19,
      "municipality_name": "Mendez"
    },
    {
      "municipality_id": 397,
      "province_id": 19,
      "municipality_name": "Naic"
    },
    {
      "municipality_id": 398,
      "province_id": 19,
      "municipality_name": "Noveleta"
    },
    {
      "municipality_id": 399,
      "province_id": 19,
      "municipality_name": "Rosario"
    },
    {
      "municipality_id": 400,
      "province_id": 19,
      "municipality_name": "Silang"
    },
    {
      "municipality_id": 401,
      "province_id": 19,
      "municipality_name": "City of Tagaytay"
    },
    {
      "municipality_id": 402,
      "province_id": 19,
      "municipality_name": "Tanza"
    },
    {
      "municipality_id": 403,
      "province_id": 19,
      "municipality_name": "Ternate"
    },
    {
      "municipality_id": 404,
      "province_id": 19,
      "municipality_name": "City of Trece Martires"
    },
    {
      "municipality_id": 405,
      "province_id": 19,
      "municipality_name": "Gen. Mariano Alvarez"
    },
    {
      "municipality_id": 406,
      "province_id": 20,
      "municipality_name": "Alaminos"
    },
    {
      "municipality_id": 407,
      "province_id": 20,
      "municipality_name": "Bay"
    },
    {
      "municipality_id": 408,
      "province_id": 20,
      "municipality_name": "City of Biñan"
    },
    {
      "municipality_id": 409,
      "province_id": 20,
      "municipality_name": "City of Cabuyao"
    },
    {
      "municipality_id": 410,
      "province_id": 20,
      "municipality_name": "City of Calamba"
    },
    {
      "municipality_id": 411,
      "province_id": 20,
      "municipality_name": "Calauan"
    },
    {
      "municipality_id": 412,
      "province_id": 20,
      "municipality_name": "Cavinti"
    },
    {
      "municipality_id": 413,
      "province_id": 20,
      "municipality_name": "Famy"
    },
    {
      "municipality_id": 414,
      "province_id": 20,
      "municipality_name": "Kalayaan"
    },
    {
      "municipality_id": 415,
      "province_id": 20,
      "municipality_name": "Liliw"
    },
    {
      "municipality_id": 416,
      "province_id": 20,
      "municipality_name": "Los Baños"
    },
    {
      "municipality_id": 417,
      "province_id": 20,
      "municipality_name": "Luisiana"
    },
    {
      "municipality_id": 418,
      "province_id": 20,
      "municipality_name": "Lumban"
    },
    {
      "municipality_id": 419,
      "province_id": 20,
      "municipality_name": "Mabitac"
    },
    {
      "municipality_id": 420,
      "province_id": 20,
      "municipality_name": "Magdalena"
    },
    {
      "municipality_id": 421,
      "province_id": 20,
      "municipality_name": "Majayjay"
    },
    {
      "municipality_id": 422,
      "province_id": 20,
      "municipality_name": "Nagcarlan"
    },
    {
      "municipality_id": 423,
      "province_id": 20,
      "municipality_name": "Paete"
    },
    {
      "municipality_id": 424,
      "province_id": 20,
      "municipality_name": "Pagsanjan"
    },
    {
      "municipality_id": 425,
      "province_id": 20,
      "municipality_name": "Pakil"
    },
    {
      "municipality_id": 426,
      "province_id": 20,
      "municipality_name": "Pangil"
    },
    {
      "municipality_id": 427,
      "province_id": 20,
      "municipality_name": "Pila"
    },
    {
      "municipality_id": 428,
      "province_id": 20,
      "municipality_name": "Rizal"
    },
    {
      "municipality_id": 429,
      "province_id": 20,
      "municipality_name": "City of San Pablo"
    },
    {
      "municipality_id": 430,
      "province_id": 20,
      "municipality_name": "City of San Pedro"
    },
    {
      "municipality_id": 431,
      "province_id": 20,
      "municipality_name": "Santa Cruz"
    },
    {
      "municipality_id": 432,
      "province_id": 20,
      "municipality_name": "Santa Maria"
    },
    {
      "municipality_id": 433,
      "province_id": 20,
      "municipality_name": "City of Santa Rosa"
    },
    {
      "municipality_id": 434,
      "province_id": 20,
      "municipality_name": "Siniloan"
    },
    {
      "municipality_id": 435,
      "province_id": 20,
      "municipality_name": "Victoria"
    },
    {
      "municipality_id": 436,
      "province_id": 21,
      "municipality_name": "Agdangan"
    },
    {
      "municipality_id": 437,
      "province_id": 21,
      "municipality_name": "Alabat"
    },
    {
      "municipality_id": 438,
      "province_id": 21,
      "municipality_name": "Atimonan"
    },
    {
      "municipality_id": 439,
      "province_id": 21,
      "municipality_name": "Buenavista"
    },
    {
      "municipality_id": 440,
      "province_id": 21,
      "municipality_name": "Burdeos"
    },
    {
      "municipality_id": 441,
      "province_id": 21,
      "municipality_name": "Calauag"
    },
    {
      "municipality_id": 442,
      "province_id": 21,
      "municipality_name": "Candelaria"
    },
    {
      "municipality_id": 443,
      "province_id": 21,
      "municipality_name": "Catanauan"
    },
    {
      "municipality_id": 444,
      "province_id": 21,
      "municipality_name": "Dolores"
    },
    {
      "municipality_id": 445,
      "province_id": 21,
      "municipality_name": "General Luna"
    },
    {
      "municipality_id": 446,
      "province_id": 21,
      "municipality_name": "General Nakar"
    },
    {
      "municipality_id": 447,
      "province_id": 21,
      "municipality_name": "Guinayangan"
    },
    {
      "municipality_id": 448,
      "province_id": 21,
      "municipality_name": "Gumaca"
    },
    {
      "municipality_id": 449,
      "province_id": 21,
      "municipality_name": "Infanta"
    },
    {
      "municipality_id": 450,
      "province_id": 21,
      "municipality_name": "Jomalig"
    },
    {
      "municipality_id": 451,
      "province_id": 21,
      "municipality_name": "Lopez"
    },
    {
      "municipality_id": 452,
      "province_id": 21,
      "municipality_name": "Lucban"
    },
    {
      "municipality_id": 453,
      "province_id": 21,
      "municipality_name": "City of Lucena"
    },
    {
      "municipality_id": 454,
      "province_id": 21,
      "municipality_name": "Macalelon"
    },
    {
      "municipality_id": 455,
      "province_id": 21,
      "municipality_name": "Mauban"
    },
    {
      "municipality_id": 456,
      "province_id": 21,
      "municipality_name": "Mulanay"
    },
    {
      "municipality_id": 457,
      "province_id": 21,
      "municipality_name": "Padre Burgos"
    },
    {
      "municipality_id": 458,
      "province_id": 21,
      "municipality_name": "Pagbilao"
    },
    {
      "municipality_id": 459,
      "province_id": 21,
      "municipality_name": "Panukulan"
    },
    {
      "municipality_id": 460,
      "province_id": 21,
      "municipality_name": "Patnanungan"
    },
    {
      "municipality_id": 461,
      "province_id": 21,
      "municipality_name": "Perez"
    },
    {
      "municipality_id": 462,
      "province_id": 21,
      "municipality_name": "Pitogo"
    },
    {
      "municipality_id": 463,
      "province_id": 21,
      "municipality_name": "Plaridel"
    },
    {
      "municipality_id": 464,
      "province_id": 21,
      "municipality_name": "Polillo"
    },
    {
      "municipality_id": 465,
      "province_id": 21,
      "municipality_name": "Quezon"
    },
    {
      "municipality_id": 466,
      "province_id": 21,
      "municipality_name": "Real"
    },
    {
      "municipality_id": 467,
      "province_id": 21,
      "municipality_name": "Sampaloc"
    },
    {
      "municipality_id": 468,
      "province_id": 21,
      "municipality_name": "San Andres"
    },
    {
      "municipality_id": 469,
      "province_id": 21,
      "municipality_name": "San Antonio"
    },
    {
      "municipality_id": 470,
      "province_id": 21,
      "municipality_name": "San Francisco"
    },
    {
      "municipality_id": 471,
      "province_id": 21,
      "municipality_name": "San Narciso"
    },
    {
      "municipality_id": 472,
      "province_id": 21,
      "municipality_name": "Sariaya"
    },
    {
      "municipality_id": 473,
      "province_id": 21,
      "municipality_name": "Tagkawayan"
    },
    {
      "municipality_id": 474,
      "province_id": 21,
      "municipality_name": "City of Tayabas"
    },
    {
      "municipality_id": 475,
      "province_id": 21,
      "municipality_name": "Tiaong"
    },
    {
      "municipality_id": 476,
      "province_id": 21,
      "municipality_name": "Unisan"
    },
    {
      "municipality_id": 477,
      "province_id": 22,
      "municipality_name": "Angono"
    },
    {
      "municipality_id": 478,
      "province_id": 22,
      "municipality_name": "City of Antipolo"
    },
    {
      "municipality_id": 479,
      "province_id": 22,
      "municipality_name": "Baras"
    },
    {
      "municipality_id": 480,
      "province_id": 22,
      "municipality_name": "Binangonan"
    },
    {
      "municipality_id": 481,
      "province_id": 22,
      "municipality_name": "Cainta"
    },
    {
      "municipality_id": 482,
      "province_id": 22,
      "municipality_name": "Cardona"
    },
    {
      "municipality_id": 483,
      "province_id": 22,
      "municipality_name": "Jala-Jala"
    },
    {
      "municipality_id": 484,
      "province_id": 22,
      "municipality_name": "Rodriguez"
    },
    {
      "municipality_id": 485,
      "province_id": 22,
      "municipality_name": "Morong"
    },
    {
      "municipality_id": 486,
      "province_id": 22,
      "municipality_name": "Pililla"
    },
    {
      "municipality_id": 487,
      "province_id": 22,
      "municipality_name": "San Mateo"
    },
    {
      "municipality_id": 488,
      "province_id": 22,
      "municipality_name": "Tanay"
    },
    {
      "municipality_id": 489,
      "province_id": 22,
      "municipality_name": "Taytay"
    },
    {
      "municipality_id": 490,
      "province_id": 22,
      "municipality_name": "Teresa"
    },
    {
      "municipality_id": 491,
      "province_id": 23,
      "municipality_name": "Boac"
    },
    {
      "municipality_id": 492,
      "province_id": 23,
      "municipality_name": "Buenavista"
    },
    {
      "municipality_id": 493,
      "province_id": 23,
      "municipality_name": "Gasan"
    },
    {
      "municipality_id": 494,
      "province_id": 23,
      "municipality_name": "Mogpog"
    },
    {
      "municipality_id": 495,
      "province_id": 23,
      "municipality_name": "Santa Cruz"
    },
    {
      "municipality_id": 496,
      "province_id": 23,
      "municipality_name": "Torrijos"
    },
    {
      "municipality_id": 497,
      "province_id": 24,
      "municipality_name": "Abra De Ilog"
    },
    {
      "municipality_id": 498,
      "province_id": 24,
      "municipality_name": "Calintaan"
    },
    {
      "municipality_id": 499,
      "province_id": 24,
      "municipality_name": "Looc"
    },
    {
      "municipality_id": 500,
      "province_id": 24,
      "municipality_name": "Lubang"
    },
    {
      "municipality_id": 501,
      "province_id": 24,
      "municipality_name": "Magsaysay"
    },
    {
      "municipality_id": 502,
      "province_id": 24,
      "municipality_name": "Mamburao"
    },
    {
      "municipality_id": 503,
      "province_id": 24,
      "municipality_name": "Paluan"
    },
    {
      "municipality_id": 504,
      "province_id": 24,
      "municipality_name": "Rizal"
    },
    {
      "municipality_id": 505,
      "province_id": 24,
      "municipality_name": "Sablayan"
    },
    {
      "municipality_id": 506,
      "province_id": 24,
      "municipality_name": "San Jose"
    },
    {
      "municipality_id": 507,
      "province_id": 24,
      "municipality_name": "Santa Cruz"
    },
    {
      "municipality_id": 508,
      "province_id": 25,
      "municipality_name": "Baco"
    },
    {
      "municipality_id": 509,
      "province_id": 25,
      "municipality_name": "Bansud"
    },
    {
      "municipality_id": 510,
      "province_id": 25,
      "municipality_name": "Bongabong"
    },
    {
      "municipality_id": 511,
      "province_id": 25,
      "municipality_name": "Bulalacao"
    },
    {
      "municipality_id": 512,
      "province_id": 25,
      "municipality_name": "City of Calapan"
    },
    {
      "municipality_id": 513,
      "province_id": 25,
      "municipality_name": "Gloria"
    },
    {
      "municipality_id": 514,
      "province_id": 25,
      "municipality_name": "Mansalay"
    },
    {
      "municipality_id": 515,
      "province_id": 25,
      "municipality_name": "Naujan"
    },
    {
      "municipality_id": 516,
      "province_id": 25,
      "municipality_name": "Pinamalayan"
    },
    {
      "municipality_id": 517,
      "province_id": 25,
      "municipality_name": "Pola"
    },
    {
      "municipality_id": 518,
      "province_id": 25,
      "municipality_name": "Puerto Galera"
    },
    {
      "municipality_id": 519,
      "province_id": 25,
      "municipality_name": "Roxas"
    },
    {
      "municipality_id": 520,
      "province_id": 25,
      "municipality_name": "San Teodoro"
    },
    {
      "municipality_id": 521,
      "province_id": 25,
      "municipality_name": "Socorro"
    },
    {
      "municipality_id": 522,
      "province_id": 25,
      "municipality_name": "Victoria"
    },
    {
      "municipality_id": 523,
      "province_id": 26,
      "municipality_name": "Aborlan"
    },
    {
      "municipality_id": 524,
      "province_id": 26,
      "municipality_name": "Agutaya"
    },
    {
      "municipality_id": 525,
      "province_id": 26,
      "municipality_name": "Araceli"
    },
    {
      "municipality_id": 526,
      "province_id": 26,
      "municipality_name": "Balabac"
    },
    {
      "municipality_id": 527,
      "province_id": 26,
      "municipality_name": "Bataraza"
    },
    {
      "municipality_id": 528,
      "province_id": 26,
      "municipality_name": "Brooke'S Point"
    },
    {
      "municipality_id": 529,
      "province_id": 26,
      "municipality_name": "Busuanga"
    },
    {
      "municipality_id": 530,
      "province_id": 26,
      "municipality_name": "Cagayancillo"
    },
    {
      "municipality_id": 531,
      "province_id": 26,
      "municipality_name": "Coron"
    },
    {
      "municipality_id": 532,
      "province_id": 26,
      "municipality_name": "Cuyo"
    },
    {
      "municipality_id": 533,
      "province_id": 26,
      "municipality_name": "Dumaran"
    },
    {
      "municipality_id": 534,
      "province_id": 26,
      "municipality_name": "El Nido"
    },
    {
      "municipality_id": 535,
      "province_id": 26,
      "municipality_name": "Linapacan"
    },
    {
      "municipality_id": 536,
      "province_id": 26,
      "municipality_name": "Magsaysay"
    },
    {
      "municipality_id": 537,
      "province_id": 26,
      "municipality_name": "Narra"
    },
    {
      "municipality_id": 538,
      "province_id": 26,
      "municipality_name": "City of Puerto Princesa"
    },
    {
      "municipality_id": 539,
      "province_id": 26,
      "municipality_name": "Quezon"
    },
    {
      "municipality_id": 540,
      "province_id": 26,
      "municipality_name": "Roxas"
    },
    {
      "municipality_id": 541,
      "province_id": 26,
      "municipality_name": "San Vicente"
    },
    {
      "municipality_id": 542,
      "province_id": 26,
      "municipality_name": "Taytay"
    },
    {
      "municipality_id": 543,
      "province_id": 26,
      "municipality_name": "Kalayaan"
    },
    {
      "municipality_id": 544,
      "province_id": 26,
      "municipality_name": "Culion"
    },
    {
      "municipality_id": 545,
      "province_id": 26,
      "municipality_name": "Rizal"
    },
    {
      "municipality_id": 546,
      "province_id": 26,
      "municipality_name": "Sofronio Española"
    },
    {
      "municipality_id": 547,
      "province_id": 27,
      "municipality_name": "Alcantara"
    },
    {
      "municipality_id": 548,
      "province_id": 27,
      "municipality_name": "Banton"
    },
    {
      "municipality_id": 549,
      "province_id": 27,
      "municipality_name": "Cajidiocan"
    },
    {
      "municipality_id": 550,
      "province_id": 27,
      "municipality_name": "Calatrava"
    },
    {
      "municipality_id": 551,
      "province_id": 27,
      "municipality_name": "Concepcion"
    },
    {
      "municipality_id": 552,
      "province_id": 27,
      "municipality_name": "Corcuera"
    },
    {
      "municipality_id": 553,
      "province_id": 27,
      "municipality_name": "Looc"
    },
    {
      "municipality_id": 554,
      "province_id": 27,
      "municipality_name": "Magdiwang"
    },
    {
      "municipality_id": 555,
      "province_id": 27,
      "municipality_name": "Odiongan"
    },
    {
      "municipality_id": 556,
      "province_id": 27,
      "municipality_name": "Romblon"
    },
    {
      "municipality_id": 557,
      "province_id": 27,
      "municipality_name": "San Agustin"
    },
    {
      "municipality_id": 558,
      "province_id": 27,
      "municipality_name": "San Andres"
    },
    {
      "municipality_id": 559,
      "province_id": 27,
      "municipality_name": "San Fernando"
    },
    {
      "municipality_id": 560,
      "province_id": 27,
      "municipality_name": "San Jose"
    },
    {
      "municipality_id": 561,
      "province_id": 27,
      "municipality_name": "Santa Fe"
    },
    {
      "municipality_id": 562,
      "province_id": 27,
      "municipality_name": "Ferrol"
    },
    {
      "municipality_id": 563,
      "province_id": 27,
      "municipality_name": "Santa Maria"
    },
    {
      "municipality_id": 564,
      "province_id": 28,
      "municipality_name": "Bacacay"
    },
    {
      "municipality_id": 565,
      "province_id": 28,
      "municipality_name": "Camalig"
    },
    {
      "municipality_id": 566,
      "province_id": 28,
      "municipality_name": "Daraga"
    },
    {
      "municipality_id": 567,
      "province_id": 28,
      "municipality_name": "Guinobatan"
    },
    {
      "municipality_id": 568,
      "province_id": 28,
      "municipality_name": "Jovellar"
    },
    {
      "municipality_id": 569,
      "province_id": 28,
      "municipality_name": "City of Legazpi"
    },
    {
      "municipality_id": 570,
      "province_id": 28,
      "municipality_name": "Libon"
    },
    {
      "municipality_id": 571,
      "province_id": 28,
      "municipality_name": "City of Ligao"
    },
    {
      "municipality_id": 572,
      "province_id": 28,
      "municipality_name": "Malilipot"
    },
    {
      "municipality_id": 573,
      "province_id": 28,
      "municipality_name": "Malinao"
    },
    {
      "municipality_id": 574,
      "province_id": 28,
      "municipality_name": "Manito"
    },
    {
      "municipality_id": 575,
      "province_id": 28,
      "municipality_name": "Oas"
    },
    {
      "municipality_id": 576,
      "province_id": 28,
      "municipality_name": "Pio Duran"
    },
    {
      "municipality_id": 577,
      "province_id": 28,
      "municipality_name": "Polangui"
    },
    {
      "municipality_id": 578,
      "province_id": 28,
      "municipality_name": "Rapu-Rapu"
    },
    {
      "municipality_id": 579,
      "province_id": 28,
      "municipality_name": "Santo Domingo"
    },
    {
      "municipality_id": 580,
      "province_id": 28,
      "municipality_name": "City of Tabaco"
    },
    {
      "municipality_id": 581,
      "province_id": 28,
      "municipality_name": "Tiwi"
    },
    {
      "municipality_id": 582,
      "province_id": 29,
      "municipality_name": "Basud"
    },
    {
      "municipality_id": 583,
      "province_id": 29,
      "municipality_name": "Capalonga"
    },
    {
      "municipality_id": 584,
      "province_id": 29,
      "municipality_name": "Daet"
    },
    {
      "municipality_id": 585,
      "province_id": 29,
      "municipality_name": "San Lorenzo Ruiz"
    },
    {
      "municipality_id": 586,
      "province_id": 29,
      "municipality_name": "Jose Panganiban"
    },
    {
      "municipality_id": 587,
      "province_id": 29,
      "municipality_name": "Labo"
    },
    {
      "municipality_id": 588,
      "province_id": 29,
      "municipality_name": "Mercedes"
    },
    {
      "municipality_id": 589,
      "province_id": 29,
      "municipality_name": "Paracale"
    },
    {
      "municipality_id": 590,
      "province_id": 29,
      "municipality_name": "San Vicente"
    },
    {
      "municipality_id": 591,
      "province_id": 29,
      "municipality_name": "Santa Elena"
    },
    {
      "municipality_id": 592,
      "province_id": 29,
      "municipality_name": "Talisay"
    },
    {
      "municipality_id": 593,
      "province_id": 29,
      "municipality_name": "Vinzons"
    },
    {
      "municipality_id": 594,
      "province_id": 30,
      "municipality_name": "Baao"
    },
    {
      "municipality_id": 595,
      "province_id": 30,
      "municipality_name": "Balatan"
    },
    {
      "municipality_id": 596,
      "province_id": 30,
      "municipality_name": "Bato"
    },
    {
      "municipality_id": 597,
      "province_id": 30,
      "municipality_name": "Bombon"
    },
    {
      "municipality_id": 598,
      "province_id": 30,
      "municipality_name": "Buhi"
    },
    {
      "municipality_id": 599,
      "province_id": 30,
      "municipality_name": "Bula"
    },
    {
      "municipality_id": 600,
      "province_id": 30,
      "municipality_name": "Cabusao"
    },
    {
      "municipality_id": 601,
      "province_id": 30,
      "municipality_name": "Calabanga"
    },
    {
      "municipality_id": 602,
      "province_id": 30,
      "municipality_name": "Camaligan"
    },
    {
      "municipality_id": 603,
      "province_id": 30,
      "municipality_name": "Canaman"
    },
    {
      "municipality_id": 604,
      "province_id": 30,
      "municipality_name": "Caramoan"
    },
    {
      "municipality_id": 605,
      "province_id": 30,
      "municipality_name": "Del Gallego"
    },
    {
      "municipality_id": 606,
      "province_id": 30,
      "municipality_name": "Gainza"
    },
    {
      "municipality_id": 607,
      "province_id": 30,
      "municipality_name": "Garchitorena"
    },
    {
      "municipality_id": 608,
      "province_id": 30,
      "municipality_name": "Goa"
    },
    {
      "municipality_id": 609,
      "province_id": 30,
      "municipality_name": "City of Iriga"
    },
    {
      "municipality_id": 610,
      "province_id": 30,
      "municipality_name": "Lagonoy"
    },
    {
      "municipality_id": 611,
      "province_id": 30,
      "municipality_name": "Libmanan"
    },
    {
      "municipality_id": 612,
      "province_id": 30,
      "municipality_name": "Lupi"
    },
    {
      "municipality_id": 613,
      "province_id": 30,
      "municipality_name": "Magarao"
    },
    {
      "municipality_id": 614,
      "province_id": 30,
      "municipality_name": "Milaor"
    },
    {
      "municipality_id": 615,
      "province_id": 30,
      "municipality_name": "Minalabac"
    },
    {
      "municipality_id": 616,
      "province_id": 30,
      "municipality_name": "Nabua"
    },
    {
      "municipality_id": 617,
      "province_id": 30,
      "municipality_name": "City of Naga"
    },
    {
      "municipality_id": 618,
      "province_id": 30,
      "municipality_name": "Ocampo"
    },
    {
      "municipality_id": 619,
      "province_id": 30,
      "municipality_name": "Pamplona"
    },
    {
      "municipality_id": 620,
      "province_id": 30,
      "municipality_name": "Pasacao"
    },
    {
      "municipality_id": 621,
      "province_id": 30,
      "municipality_name": "Pili"
    },
    {
      "municipality_id": 622,
      "province_id": 30,
      "municipality_name": "Presentacion"
    },
    {
      "municipality_id": 623,
      "province_id": 30,
      "municipality_name": "Ragay"
    },
    {
      "municipality_id": 624,
      "province_id": 30,
      "municipality_name": "Sagñay"
    },
    {
      "municipality_id": 625,
      "province_id": 30,
      "municipality_name": "San Fernando"
    },
    {
      "municipality_id": 626,
      "province_id": 30,
      "municipality_name": "San Jose"
    },
    {
      "municipality_id": 627,
      "province_id": 30,
      "municipality_name": "Sipocot"
    },
    {
      "municipality_id": 628,
      "province_id": 30,
      "municipality_name": "Siruma"
    },
    {
      "municipality_id": 629,
      "province_id": 30,
      "municipality_name": "Tigaon"
    },
    {
      "municipality_id": 630,
      "province_id": 30,
      "municipality_name": "Tinambac"
    },
    {
      "municipality_id": 631,
      "province_id": 31,
      "municipality_name": "Bagamanoc"
    },
    {
      "municipality_id": 632,
      "province_id": 31,
      "municipality_name": "Baras"
    },
    {
      "municipality_id": 633,
      "province_id": 31,
      "municipality_name": "Bato"
    },
    {
      "municipality_id": 634,
      "province_id": 31,
      "municipality_name": "Caramoran"
    },
    {
      "municipality_id": 635,
      "province_id": 31,
      "municipality_name": "Gigmoto"
    },
    {
      "municipality_id": 636,
      "province_id": 31,
      "municipality_name": "Pandan"
    },
    {
      "municipality_id": 637,
      "province_id": 31,
      "municipality_name": "Panganiban"
    },
    {
      "municipality_id": 638,
      "province_id": 31,
      "municipality_name": "San Andres"
    },
    {
      "municipality_id": 639,
      "province_id": 31,
      "municipality_name": "San Miguel"
    },
    {
      "municipality_id": 640,
      "province_id": 31,
      "municipality_name": "Viga"
    },
    {
      "municipality_id": 641,
      "province_id": 31,
      "municipality_name": "Virac"
    },
    {
      "municipality_id": 642,
      "province_id": 32,
      "municipality_name": "Aroroy"
    },
    {
      "municipality_id": 643,
      "province_id": 32,
      "municipality_name": "Baleno"
    },
    {
      "municipality_id": 644,
      "province_id": 32,
      "municipality_name": "Balud"
    },
    {
      "municipality_id": 645,
      "province_id": 32,
      "municipality_name": "Batuan"
    },
    {
      "municipality_id": 646,
      "province_id": 32,
      "municipality_name": "Cataingan"
    },
    {
      "municipality_id": 647,
      "province_id": 32,
      "municipality_name": "Cawayan"
    },
    {
      "municipality_id": 648,
      "province_id": 32,
      "municipality_name": "Claveria"
    },
    {
      "municipality_id": 649,
      "province_id": 32,
      "municipality_name": "Dimasalang"
    },
    {
      "municipality_id": 650,
      "province_id": 32,
      "municipality_name": "Esperanza"
    },
    {
      "municipality_id": 651,
      "province_id": 32,
      "municipality_name": "Mandaon"
    },
    {
      "municipality_id": 652,
      "province_id": 32,
      "municipality_name": "City of Masbate"
    },
    {
      "municipality_id": 653,
      "province_id": 32,
      "municipality_name": "Milagros"
    },
    {
      "municipality_id": 654,
      "province_id": 32,
      "municipality_name": "Mobo"
    },
    {
      "municipality_id": 655,
      "province_id": 32,
      "municipality_name": "Monreal"
    },
    {
      "municipality_id": 656,
      "province_id": 32,
      "municipality_name": "Palanas"
    },
    {
      "municipality_id": 657,
      "province_id": 32,
      "municipality_name": "Pio V. Corpuz"
    },
    {
      "municipality_id": 658,
      "province_id": 32,
      "municipality_name": "Placer"
    },
    {
      "municipality_id": 659,
      "province_id": 32,
      "municipality_name": "San Fernando"
    },
    {
      "municipality_id": 660,
      "province_id": 32,
      "municipality_name": "San Jacinto"
    },
    {
      "municipality_id": 661,
      "province_id": 32,
      "municipality_name": "San Pascual"
    },
    {
      "municipality_id": 662,
      "province_id": 32,
      "municipality_name": "Uson"
    },
    {
      "municipality_id": 663,
      "province_id": 33,
      "municipality_name": "Barcelona"
    },
    {
      "municipality_id": 664,
      "province_id": 33,
      "municipality_name": "Bulan"
    },
    {
      "municipality_id": 665,
      "province_id": 33,
      "municipality_name": "Bulusan"
    },
    {
      "municipality_id": 666,
      "province_id": 33,
      "municipality_name": "Casiguran"
    },
    {
      "municipality_id": 667,
      "province_id": 33,
      "municipality_name": "Castilla"
    },
    {
      "municipality_id": 668,
      "province_id": 33,
      "municipality_name": "Donsol"
    },
    {
      "municipality_id": 669,
      "province_id": 33,
      "municipality_name": "Gubat"
    },
    {
      "municipality_id": 670,
      "province_id": 33,
      "municipality_name": "Irosin"
    },
    {
      "municipality_id": 671,
      "province_id": 33,
      "municipality_name": "Juban"
    },
    {
      "municipality_id": 672,
      "province_id": 33,
      "municipality_name": "Magallanes"
    },
    {
      "municipality_id": 673,
      "province_id": 33,
      "municipality_name": "Matnog"
    },
    {
      "municipality_id": 674,
      "province_id": 33,
      "municipality_name": "Pilar"
    },
    {
      "municipality_id": 675,
      "province_id": 33,
      "municipality_name": "Prieto Diaz"
    },
    {
      "municipality_id": 676,
      "province_id": 33,
      "municipality_name": "Santa Magdalena"
    },
    {
      "municipality_id": 677,
      "province_id": 33,
      "municipality_name": "City of Sorsogon"
    },
    {
      "municipality_id": 678,
      "province_id": 34,
      "municipality_name": "Altavas"
    },
    {
      "municipality_id": 679,
      "province_id": 34,
      "municipality_name": "Balete"
    },
    {
      "municipality_id": 680,
      "province_id": 34,
      "municipality_name": "Banga"
    },
    {
      "municipality_id": 681,
      "province_id": 34,
      "municipality_name": "Batan"
    },
    {
      "municipality_id": 682,
      "province_id": 34,
      "municipality_name": "Buruanga"
    },
    {
      "municipality_id": 683,
      "province_id": 34,
      "municipality_name": "Ibajay"
    },
    {
      "municipality_id": 684,
      "province_id": 34,
      "municipality_name": "Kalibo"
    },
    {
      "municipality_id": 685,
      "province_id": 34,
      "municipality_name": "Lezo"
    },
    {
      "municipality_id": 686,
      "province_id": 34,
      "municipality_name": "Libacao"
    },
    {
      "municipality_id": 687,
      "province_id": 34,
      "municipality_name": "Madalag"
    },
    {
      "municipality_id": 688,
      "province_id": 34,
      "municipality_name": "Makato"
    },
    {
      "municipality_id": 689,
      "province_id": 34,
      "municipality_name": "Malay"
    },
    {
      "municipality_id": 690,
      "province_id": 34,
      "municipality_name": "Malinao"
    },
    {
      "municipality_id": 691,
      "province_id": 34,
      "municipality_name": "Nabas"
    },
    {
      "municipality_id": 692,
      "province_id": 34,
      "municipality_name": "New Washington"
    },
    {
      "municipality_id": 693,
      "province_id": 34,
      "municipality_name": "Numancia"
    },
    {
      "municipality_id": 694,
      "province_id": 34,
      "municipality_name": "Tangalan"
    },
    {
      "municipality_id": 695,
      "province_id": 35,
      "municipality_name": "Anini-Y"
    },
    {
      "municipality_id": 696,
      "province_id": 35,
      "municipality_name": "Barbaza"
    },
    {
      "municipality_id": 697,
      "province_id": 35,
      "municipality_name": "Belison"
    },
    {
      "municipality_id": 698,
      "province_id": 35,
      "municipality_name": "Bugasong"
    },
    {
      "municipality_id": 699,
      "province_id": 35,
      "municipality_name": "Caluya"
    },
    {
      "municipality_id": 700,
      "province_id": 35,
      "municipality_name": "Culasi"
    },
    {
      "municipality_id": 701,
      "province_id": 35,
      "municipality_name": "Tobias Fornier"
    },
    {
      "municipality_id": 702,
      "province_id": 35,
      "municipality_name": "Hamtic"
    },
    {
      "municipality_id": 703,
      "province_id": 35,
      "municipality_name": "Laua-An"
    },
    {
      "municipality_id": 704,
      "province_id": 35,
      "municipality_name": "Libertad"
    },
    {
      "municipality_id": 705,
      "province_id": 35,
      "municipality_name": "Pandan"
    },
    {
      "municipality_id": 706,
      "province_id": 35,
      "municipality_name": "Patnongon"
    },
    {
      "municipality_id": 707,
      "province_id": 35,
      "municipality_name": "San Jose"
    },
    {
      "municipality_id": 708,
      "province_id": 35,
      "municipality_name": "San Remigio"
    },
    {
      "municipality_id": 709,
      "province_id": 35,
      "municipality_name": "Sebaste"
    },
    {
      "municipality_id": 710,
      "province_id": 35,
      "municipality_name": "Sibalom"
    },
    {
      "municipality_id": 711,
      "province_id": 35,
      "municipality_name": "Tibiao"
    },
    {
      "municipality_id": 712,
      "province_id": 35,
      "municipality_name": "Valderrama"
    },
    {
      "municipality_id": 713,
      "province_id": 36,
      "municipality_name": "Cuartero"
    },
    {
      "municipality_id": 714,
      "province_id": 36,
      "municipality_name": "Dao"
    },
    {
      "municipality_id": 715,
      "province_id": 36,
      "municipality_name": "Dumalag"
    },
    {
      "municipality_id": 716,
      "province_id": 36,
      "municipality_name": "Dumarao"
    },
    {
      "municipality_id": 717,
      "province_id": 36,
      "municipality_name": "Ivisan"
    },
    {
      "municipality_id": 718,
      "province_id": 36,
      "municipality_name": "Jamindan"
    },
    {
      "municipality_id": 719,
      "province_id": 36,
      "municipality_name": "Ma-Ayon"
    },
    {
      "municipality_id": 720,
      "province_id": 36,
      "municipality_name": "Mambusao"
    },
    {
      "municipality_id": 721,
      "province_id": 36,
      "municipality_name": "Panay"
    },
    {
      "municipality_id": 722,
      "province_id": 36,
      "municipality_name": "Panitan"
    },
    {
      "municipality_id": 723,
      "province_id": 36,
      "municipality_name": "Pilar"
    },
    {
      "municipality_id": 724,
      "province_id": 36,
      "municipality_name": "Pontevedra"
    },
    {
      "municipality_id": 725,
      "province_id": 36,
      "municipality_name": "President Roxas"
    },
    {
      "municipality_id": 726,
      "province_id": 36,
      "municipality_name": "City of Roxas"
    },
    {
      "municipality_id": 727,
      "province_id": 36,
      "municipality_name": "Sapi-An"
    },
    {
      "municipality_id": 728,
      "province_id": 36,
      "municipality_name": "Sigma"
    },
    {
      "municipality_id": 729,
      "province_id": 36,
      "municipality_name": "Tapaz"
    },
    {
      "municipality_id": 730,
      "province_id": 37,
      "municipality_name": "Ajuy"
    },
    {
      "municipality_id": 731,
      "province_id": 37,
      "municipality_name": "Alimodian"
    },
    {
      "municipality_id": 732,
      "province_id": 37,
      "municipality_name": "Anilao"
    },
    {
      "municipality_id": 733,
      "province_id": 37,
      "municipality_name": "Badiangan"
    },
    {
      "municipality_id": 734,
      "province_id": 37,
      "municipality_name": "Balasan"
    },
    {
      "municipality_id": 735,
      "province_id": 37,
      "municipality_name": "Banate"
    },
    {
      "municipality_id": 736,
      "province_id": 37,
      "municipality_name": "Barotac Nuevo"
    },
    {
      "municipality_id": 737,
      "province_id": 37,
      "municipality_name": "Barotac Viejo"
    },
    {
      "municipality_id": 738,
      "province_id": 37,
      "municipality_name": "Batad"
    },
    {
      "municipality_id": 739,
      "province_id": 37,
      "municipality_name": "Bingawan"
    },
    {
      "municipality_id": 740,
      "province_id": 37,
      "municipality_name": "Cabatuan"
    },
    {
      "municipality_id": 741,
      "province_id": 37,
      "municipality_name": "Calinog"
    },
    {
      "municipality_id": 742,
      "province_id": 37,
      "municipality_name": "Carles"
    },
    {
      "municipality_id": 743,
      "province_id": 37,
      "municipality_name": "Concepcion"
    },
    {
      "municipality_id": 744,
      "province_id": 37,
      "municipality_name": "Dingle"
    },
    {
      "municipality_id": 745,
      "province_id": 37,
      "municipality_name": "Dueñas"
    },
    {
      "municipality_id": 746,
      "province_id": 37,
      "municipality_name": "Dumangas"
    },
    {
      "municipality_id": 747,
      "province_id": 37,
      "municipality_name": "Estancia"
    },
    {
      "municipality_id": 748,
      "province_id": 37,
      "municipality_name": "Guimbal"
    },
    {
      "municipality_id": 749,
      "province_id": 37,
      "municipality_name": "Igbaras"
    },
    {
      "municipality_id": 750,
      "province_id": 37,
      "municipality_name": "City of Iloilo"
    },
    {
      "municipality_id": 751,
      "province_id": 37,
      "municipality_name": "Janiuay"
    },
    {
      "municipality_id": 752,
      "province_id": 37,
      "municipality_name": "Lambunao"
    },
    {
      "municipality_id": 753,
      "province_id": 37,
      "municipality_name": "Leganes"
    },
    {
      "municipality_id": 754,
      "province_id": 37,
      "municipality_name": "Lemery"
    },
    {
      "municipality_id": 755,
      "province_id": 37,
      "municipality_name": "Leon"
    },
    {
      "municipality_id": 756,
      "province_id": 37,
      "municipality_name": "Maasin"
    },
    {
      "municipality_id": 757,
      "province_id": 37,
      "municipality_name": "Miagao"
    },
    {
      "municipality_id": 758,
      "province_id": 37,
      "municipality_name": "Mina"
    },
    {
      "municipality_id": 759,
      "province_id": 37,
      "municipality_name": "New Lucena"
    },
    {
      "municipality_id": 760,
      "province_id": 37,
      "municipality_name": "Oton"
    },
    {
      "municipality_id": 761,
      "province_id": 37,
      "municipality_name": "City of Passi"
    },
    {
      "municipality_id": 762,
      "province_id": 37,
      "municipality_name": "Pavia"
    },
    {
      "municipality_id": 763,
      "province_id": 37,
      "municipality_name": "Pototan"
    },
    {
      "municipality_id": 764,
      "province_id": 37,
      "municipality_name": "San Dionisio"
    },
    {
      "municipality_id": 765,
      "province_id": 37,
      "municipality_name": "San Enrique"
    },
    {
      "municipality_id": 766,
      "province_id": 37,
      "municipality_name": "San Joaquin"
    },
    {
      "municipality_id": 767,
      "province_id": 37,
      "municipality_name": "San Miguel"
    },
    {
      "municipality_id": 768,
      "province_id": 37,
      "municipality_name": "San Rafael"
    },
    {
      "municipality_id": 769,
      "province_id": 37,
      "municipality_name": "Santa Barbara"
    },
    {
      "municipality_id": 770,
      "province_id": 37,
      "municipality_name": "Sara"
    },
    {
      "municipality_id": 771,
      "province_id": 37,
      "municipality_name": "Tigbauan"
    },
    {
      "municipality_id": 772,
      "province_id": 37,
      "municipality_name": "Tubungan"
    },
    {
      "municipality_id": 773,
      "province_id": 37,
      "municipality_name": "Zarraga"
    },
    {
      "municipality_id": 774,
      "province_id": 38,
      "municipality_name": "City of Bacolod"
    },
    {
      "municipality_id": 775,
      "province_id": 38,
      "municipality_name": "City of Bago"
    },
    {
      "municipality_id": 776,
      "province_id": 38,
      "municipality_name": "Binalbagan"
    },
    {
      "municipality_id": 777,
      "province_id": 38,
      "municipality_name": "City of Cadiz"
    },
    {
      "municipality_id": 778,
      "province_id": 38,
      "municipality_name": "Calatrava"
    },
    {
      "municipality_id": 779,
      "province_id": 38,
      "municipality_name": "Candoni"
    },
    {
      "municipality_id": 780,
      "province_id": 38,
      "municipality_name": "Cauayan"
    },
    {
      "municipality_id": 781,
      "province_id": 38,
      "municipality_name": "Enrique B. Magalona"
    },
    {
      "municipality_id": 782,
      "province_id": 38,
      "municipality_name": "City of Escalante"
    },
    {
      "municipality_id": 783,
      "province_id": 38,
      "municipality_name": "City of Himamaylan"
    },
    {
      "municipality_id": 784,
      "province_id": 38,
      "municipality_name": "Hinigaran"
    },
    {
      "municipality_id": 785,
      "province_id": 38,
      "municipality_name": "Hinoba-an"
    },
    {
      "municipality_id": 786,
      "province_id": 38,
      "municipality_name": "Ilog"
    },
    {
      "municipality_id": 787,
      "province_id": 38,
      "municipality_name": "Isabela"
    },
    {
      "municipality_id": 788,
      "province_id": 38,
      "municipality_name": "City of Kabankalan"
    },
    {
      "municipality_id": 789,
      "province_id": 38,
      "municipality_name": "City of La Carlota"
    },
    {
      "municipality_id": 790,
      "province_id": 38,
      "municipality_name": "La Castellana"
    },
    {
      "municipality_id": 791,
      "province_id": 38,
      "municipality_name": "Manapla"
    },
    {
      "municipality_id": 792,
      "province_id": 38,
      "municipality_name": "Moises Padilla"
    },
    {
      "municipality_id": 793,
      "province_id": 38,
      "municipality_name": "Murcia"
    },
    {
      "municipality_id": 794,
      "province_id": 38,
      "municipality_name": "Pontevedra"
    },
    {
      "municipality_id": 795,
      "province_id": 38,
      "municipality_name": "Pulupandan"
    },
    {
      "municipality_id": 796,
      "province_id": 38,
      "municipality_name": "City of Sagay"
    },
    {
      "municipality_id": 797,
      "province_id": 38,
      "municipality_name": "City of San Carlos"
    },
    {
      "municipality_id": 798,
      "province_id": 38,
      "municipality_name": "San Enrique"
    },
    {
      "municipality_id": 799,
      "province_id": 38,
      "municipality_name": "City of Silay"
    },
    {
      "municipality_id": 800,
      "province_id": 38,
      "municipality_name": "City of Sipalay"
    },
    {
      "municipality_id": 801,
      "province_id": 38,
      "municipality_name": "City of Talisay"
    },
    {
      "municipality_id": 802,
      "province_id": 38,
      "municipality_name": "Toboso"
    },
    {
      "municipality_id": 803,
      "province_id": 38,
      "municipality_name": "Valladolid"
    },
    {
      "municipality_id": 804,
      "province_id": 38,
      "municipality_name": "City of Victorias"
    },
    {
      "municipality_id": 805,
      "province_id": 38,
      "municipality_name": "Salvador Benedicto"
    },
    {
      "municipality_id": 806,
      "province_id": 39,
      "municipality_name": "Buenavista"
    },
    {
      "municipality_id": 807,
      "province_id": 39,
      "municipality_name": "Jordan"
    },
    {
      "municipality_id": 808,
      "province_id": 39,
      "municipality_name": "Nueva Valencia"
    },
    {
      "municipality_id": 809,
      "province_id": 39,
      "municipality_name": "San Lorenzo"
    },
    {
      "municipality_id": 810,
      "province_id": 39,
      "municipality_name": "Sibunag"
    },
    {
      "municipality_id": 811,
      "province_id": 40,
      "municipality_name": "Alburquerque"
    },
    {
      "municipality_id": 812,
      "province_id": 40,
      "municipality_name": "Alicia"
    },
    {
      "municipality_id": 813,
      "province_id": 40,
      "municipality_name": "Anda"
    },
    {
      "municipality_id": 814,
      "province_id": 40,
      "municipality_name": "Antequera"
    },
    {
      "municipality_id": 815,
      "province_id": 40,
      "municipality_name": "Baclayon"
    },
    {
      "municipality_id": 816,
      "province_id": 40,
      "municipality_name": "Balilihan"
    },
    {
      "municipality_id": 817,
      "province_id": 40,
      "municipality_name": "Batuan"
    },
    {
      "municipality_id": 818,
      "province_id": 40,
      "municipality_name": "Bilar"
    },
    {
      "municipality_id": 819,
      "province_id": 40,
      "municipality_name": "Buenavista"
    },
    {
      "municipality_id": 820,
      "province_id": 40,
      "municipality_name": "Calape"
    },
    {
      "municipality_id": 821,
      "province_id": 40,
      "municipality_name": "Candijay"
    },
    {
      "municipality_id": 822,
      "province_id": 40,
      "municipality_name": "Carmen"
    },
    {
      "municipality_id": 823,
      "province_id": 40,
      "municipality_name": "Catigbian"
    },
    {
      "municipality_id": 824,
      "province_id": 40,
      "municipality_name": "Clarin"
    },
    {
      "municipality_id": 825,
      "province_id": 40,
      "municipality_name": "Corella"
    },
    {
      "municipality_id": 826,
      "province_id": 40,
      "municipality_name": "Cortes"
    },
    {
      "municipality_id": 827,
      "province_id": 40,
      "municipality_name": "Dagohoy"
    },
    {
      "municipality_id": 828,
      "province_id": 40,
      "municipality_name": "Danao"
    },
    {
      "municipality_id": 829,
      "province_id": 40,
      "municipality_name": "Dauis"
    },
    {
      "municipality_id": 830,
      "province_id": 40,
      "municipality_name": "Dimiao"
    },
    {
      "municipality_id": 831,
      "province_id": 40,
      "municipality_name": "Duero"
    },
    {
      "municipality_id": 832,
      "province_id": 40,
      "municipality_name": "Garcia Hernandez"
    },
    {
      "municipality_id": 833,
      "province_id": 40,
      "municipality_name": "Guindulman"
    },
    {
      "municipality_id": 834,
      "province_id": 40,
      "municipality_name": "Inabanga"
    },
    {
      "municipality_id": 835,
      "province_id": 40,
      "municipality_name": "Jagna"
    },
    {
      "municipality_id": 836,
      "province_id": 40,
      "municipality_name": "Getafe"
    },
    {
      "municipality_id": 837,
      "province_id": 40,
      "municipality_name": "Lila"
    },
    {
      "municipality_id": 838,
      "province_id": 40,
      "municipality_name": "Loay"
    },
    {
      "municipality_id": 839,
      "province_id": 40,
      "municipality_name": "Loboc"
    },
    {
      "municipality_id": 840,
      "province_id": 40,
      "municipality_name": "Loon"
    },
    {
      "municipality_id": 841,
      "province_id": 40,
      "municipality_name": "Mabini"
    },
    {
      "municipality_id": 842,
      "province_id": 40,
      "municipality_name": "Maribojoc"
    },
    {
      "municipality_id": 843,
      "province_id": 40,
      "municipality_name": "Panglao"
    },
    {
      "municipality_id": 844,
      "province_id": 40,
      "municipality_name": "Pilar"
    },
    {
      "municipality_id": 845,
      "province_id": 40,
      "municipality_name": "Pres. Carlos P. Garcia"
    },
    {
      "municipality_id": 846,
      "province_id": 40,
      "municipality_name": "Sagbayan"
    },
    {
      "municipality_id": 847,
      "province_id": 40,
      "municipality_name": "San Isidro"
    },
    {
      "municipality_id": 848,
      "province_id": 40,
      "municipality_name": "San Miguel"
    },
    {
      "municipality_id": 849,
      "province_id": 40,
      "municipality_name": "Sevilla"
    },
    {
      "municipality_id": 850,
      "province_id": 40,
      "municipality_name": "Sierra Bullones"
    },
    {
      "municipality_id": 851,
      "province_id": 40,
      "municipality_name": "Sikatuna"
    },
    {
      "municipality_id": 852,
      "province_id": 40,
      "municipality_name": "City of Tagbilaran"
    },
    {
      "municipality_id": 853,
      "province_id": 40,
      "municipality_name": "Talibon"
    },
    {
      "municipality_id": 854,
      "province_id": 40,
      "municipality_name": "Trinidad"
    },
    {
      "municipality_id": 855,
      "province_id": 40,
      "municipality_name": "Tubigon"
    },
    {
      "municipality_id": 856,
      "province_id": 40,
      "municipality_name": "Ubay"
    },
    {
      "municipality_id": 857,
      "province_id": 40,
      "municipality_name": "Valencia"
    },
    {
      "municipality_id": 858,
      "province_id": 40,
      "municipality_name": "Bien Unido"
    },
    {
      "municipality_id": 859,
      "province_id": 41,
      "municipality_name": "Alcantara"
    },
    {
      "municipality_id": 860,
      "province_id": 41,
      "municipality_name": "Alcoy"
    },
    {
      "municipality_id": 861,
      "province_id": 41,
      "municipality_name": "Alegria"
    },
    {
      "municipality_id": 862,
      "province_id": 41,
      "municipality_name": "Aloguinsan"
    },
    {
      "municipality_id": 863,
      "province_id": 41,
      "municipality_name": "Argao"
    },
    {
      "municipality_id": 864,
      "province_id": 41,
      "municipality_name": "Asturias"
    },
    {
      "municipality_id": 865,
      "province_id": 41,
      "municipality_name": "Badian"
    },
    {
      "municipality_id": 866,
      "province_id": 41,
      "municipality_name": "Balamban"
    },
    {
      "municipality_id": 867,
      "province_id": 41,
      "municipality_name": "Bantayan"
    },
    {
      "municipality_id": 868,
      "province_id": 41,
      "municipality_name": "Barili"
    },
    {
      "municipality_id": 869,
      "province_id": 41,
      "municipality_name": "City of Bogo"
    },
    {
      "municipality_id": 870,
      "province_id": 41,
      "municipality_name": "Boljoon"
    },
    {
      "municipality_id": 871,
      "province_id": 41,
      "municipality_name": "Borbon"
    },
    {
      "municipality_id": 872,
      "province_id": 41,
      "municipality_name": "City of Carcar"
    },
    {
      "municipality_id": 873,
      "province_id": 41,
      "municipality_name": "Carmen"
    },
    {
      "municipality_id": 874,
      "province_id": 41,
      "municipality_name": "Catmon"
    },
    {
      "municipality_id": 875,
      "province_id": 41,
      "municipality_name": "City of Cebu"
    },
    {
      "municipality_id": 876,
      "province_id": 41,
      "municipality_name": "Compostela"
    },
    {
      "municipality_id": 877,
      "province_id": 41,
      "municipality_name": "Consolacion"
    },
    {
      "municipality_id": 878,
      "province_id": 41,
      "municipality_name": "Cordova"
    },
    {
      "municipality_id": 879,
      "province_id": 41,
      "municipality_name": "Daanbantayan"
    },
    {
      "municipality_id": 880,
      "province_id": 41,
      "municipality_name": "Dalaguete"
    },
    {
      "municipality_id": 881,
      "province_id": 41,
      "municipality_name": "Danao City"
    },
    {
      "municipality_id": 882,
      "province_id": 41,
      "municipality_name": "Dumanjug"
    },
    {
      "municipality_id": 883,
      "province_id": 41,
      "municipality_name": "Ginatilan"
    },
    {
      "municipality_id": 884,
      "province_id": 41,
      "municipality_name": "City of Lapu-Lapu"
    },
    {
      "municipality_id": 885,
      "province_id": 41,
      "municipality_name": "Liloan"
    },
    {
      "municipality_id": 886,
      "province_id": 41,
      "municipality_name": "Madridejos"
    },
    {
      "municipality_id": 887,
      "province_id": 41,
      "municipality_name": "Malabuyoc"
    },
    {
      "municipality_id": 888,
      "province_id": 41,
      "municipality_name": "City of Mandaue"
    },
    {
      "municipality_id": 889,
      "province_id": 41,
      "municipality_name": "Medellin"
    },
    {
      "municipality_id": 890,
      "province_id": 41,
      "municipality_name": "Minglanilla"
    },
    {
      "municipality_id": 891,
      "province_id": 41,
      "municipality_name": "Moalboal"
    },
    {
      "municipality_id": 892,
      "province_id": 41,
      "municipality_name": "City of Naga"
    },
    {
      "municipality_id": 893,
      "province_id": 41,
      "municipality_name": "Oslob"
    },
    {
      "municipality_id": 894,
      "province_id": 41,
      "municipality_name": "Pilar"
    },
    {
      "municipality_id": 895,
      "province_id": 41,
      "municipality_name": "Pinamungajan"
    },
    {
      "municipality_id": 896,
      "province_id": 41,
      "municipality_name": "Poro"
    },
    {
      "municipality_id": 897,
      "province_id": 41,
      "municipality_name": "Ronda"
    },
    {
      "municipality_id": 898,
      "province_id": 41,
      "municipality_name": "Samboan"
    },
    {
      "municipality_id": 899,
      "province_id": 41,
      "municipality_name": "San Fernando"
    },
    {
      "municipality_id": 900,
      "province_id": 41,
      "municipality_name": "San Francisco"
    },
    {
      "municipality_id": 901,
      "province_id": 41,
      "municipality_name": "San Remigio"
    },
    {
      "municipality_id": 902,
      "province_id": 41,
      "municipality_name": "Santa Fe"
    },
    {
      "municipality_id": 903,
      "province_id": 41,
      "municipality_name": "Santander"
    },
    {
      "municipality_id": 904,
      "province_id": 41,
      "municipality_name": "Sibonga"
    },
    {
      "municipality_id": 905,
      "province_id": 41,
      "municipality_name": "Sogod"
    },
    {
      "municipality_id": 906,
      "province_id": 41,
      "municipality_name": "Tabogon"
    },
    {
      "municipality_id": 907,
      "province_id": 41,
      "municipality_name": "Tabuelan"
    },
    {
      "municipality_id": 908,
      "province_id": 41,
      "municipality_name": "City of Talisay"
    },
    {
      "municipality_id": 909,
      "province_id": 41,
      "municipality_name": "City of Toledo"
    },
    {
      "municipality_id": 910,
      "province_id": 41,
      "municipality_name": "Tuburan"
    },
    {
      "municipality_id": 911,
      "province_id": 41,
      "municipality_name": "Tudela"
    },
    {
      "municipality_id": 912,
      "province_id": 42,
      "municipality_name": "Amlan"
    },
    {
      "municipality_id": 913,
      "province_id": 42,
      "municipality_name": "Ayungon"
    },
    {
      "municipality_id": 914,
      "province_id": 42,
      "municipality_name": "Bacong"
    },
    {
      "municipality_id": 915,
      "province_id": 42,
      "municipality_name": "City of Bais"
    },
    {
      "municipality_id": 916,
      "province_id": 42,
      "municipality_name": "Basay"
    },
    {
      "municipality_id": 917,
      "province_id": 42,
      "municipality_name": "City of Bayawan"
    },
    {
      "municipality_id": 918,
      "province_id": 42,
      "municipality_name": "Bindoy"
    },
    {
      "municipality_id": 919,
      "province_id": 42,
      "municipality_name": "City of Canlaon"
    },
    {
      "municipality_id": 920,
      "province_id": 42,
      "municipality_name": "Dauin"
    },
    {
      "municipality_id": 921,
      "province_id": 42,
      "municipality_name": "City of Dumaguete"
    },
    {
      "municipality_id": 922,
      "province_id": 42,
      "municipality_name": "City of Guihulngan"
    },
    {
      "municipality_id": 923,
      "province_id": 42,
      "municipality_name": "Jimalalud"
    },
    {
      "municipality_id": 924,
      "province_id": 42,
      "municipality_name": "La Libertad"
    },
    {
      "municipality_id": 925,
      "province_id": 42,
      "municipality_name": "Mabinay"
    },
    {
      "municipality_id": 926,
      "province_id": 42,
      "municipality_name": "Manjuyod"
    },
    {
      "municipality_id": 927,
      "province_id": 42,
      "municipality_name": "Pamplona"
    },
    {
      "municipality_id": 928,
      "province_id": 42,
      "municipality_name": "San Jose"
    },
    {
      "municipality_id": 929,
      "province_id": 42,
      "municipality_name": "Santa Catalina"
    },
    {
      "municipality_id": 930,
      "province_id": 42,
      "municipality_name": "Siaton"
    },
    {
      "municipality_id": 931,
      "province_id": 42,
      "municipality_name": "Sibulan"
    },
    {
      "municipality_id": 932,
      "province_id": 42,
      "municipality_name": "City of Tanjay"
    },
    {
      "municipality_id": 933,
      "province_id": 42,
      "municipality_name": "Tayasan"
    },
    {
      "municipality_id": 934,
      "province_id": 42,
      "municipality_name": "Valencia"
    },
    {
      "municipality_id": 935,
      "province_id": 42,
      "municipality_name": "Vallehermoso"
    },
    {
      "municipality_id": 936,
      "province_id": 42,
      "municipality_name": "Zamboanguita"
    },
    {
      "municipality_id": 937,
      "province_id": 43,
      "municipality_name": "Enrique Villanueva"
    },
    {
      "municipality_id": 938,
      "province_id": 43,
      "municipality_name": "Larena"
    },
    {
      "municipality_id": 939,
      "province_id": 43,
      "municipality_name": "Lazi"
    },
    {
      "municipality_id": 940,
      "province_id": 43,
      "municipality_name": "Maria"
    },
    {
      "municipality_id": 941,
      "province_id": 43,
      "municipality_name": "San Juan"
    },
    {
      "municipality_id": 942,
      "province_id": 43,
      "municipality_name": "Siquijor"
    },
    {
      "municipality_id": 943,
      "province_id": 44,
      "municipality_name": "Arteche"
    },
    {
      "municipality_id": 944,
      "province_id": 44,
      "municipality_name": "Balangiga"
    },
    {
      "municipality_id": 945,
      "province_id": 44,
      "municipality_name": "Balangkayan"
    },
    {
      "municipality_id": 946,
      "province_id": 44,
      "municipality_name": "City of Borongan"
    },
    {
      "municipality_id": 947,
      "province_id": 44,
      "municipality_name": "Can-Avid"
    },
    {
      "municipality_id": 948,
      "province_id": 44,
      "municipality_name": "Dolores"
    },
    {
      "municipality_id": 949,
      "province_id": 44,
      "municipality_name": "General Macarthur"
    },
    {
      "municipality_id": 950,
      "province_id": 44,
      "municipality_name": "Giporlos"
    },
    {
      "municipality_id": 951,
      "province_id": 44,
      "municipality_name": "Guiuan"
    },
    {
      "municipality_id": 952,
      "province_id": 44,
      "municipality_name": "Hernani"
    },
    {
      "municipality_id": 953,
      "province_id": 44,
      "municipality_name": "Jipapad"
    },
    {
      "municipality_id": 954,
      "province_id": 44,
      "municipality_name": "Lawaan"
    },
    {
      "municipality_id": 955,
      "province_id": 44,
      "municipality_name": "Llorente"
    },
    {
      "municipality_id": 956,
      "province_id": 44,
      "municipality_name": "Maslog"
    },
    {
      "municipality_id": 957,
      "province_id": 44,
      "municipality_name": "Maydolong"
    },
    {
      "municipality_id": 958,
      "province_id": 44,
      "municipality_name": "Mercedes"
    },
    {
      "municipality_id": 959,
      "province_id": 44,
      "municipality_name": "Oras"
    },
    {
      "municipality_id": 960,
      "province_id": 44,
      "municipality_name": "Quinapondan"
    },
    {
      "municipality_id": 961,
      "province_id": 44,
      "municipality_name": "Salcedo"
    },
    {
      "municipality_id": 962,
      "province_id": 44,
      "municipality_name": "San Julian"
    },
    {
      "municipality_id": 963,
      "province_id": 44,
      "municipality_name": "San Policarpo"
    },
    {
      "municipality_id": 964,
      "province_id": 44,
      "municipality_name": "Sulat"
    },
    {
      "municipality_id": 965,
      "province_id": 44,
      "municipality_name": "Taft"
    },
    {
      "municipality_id": 966,
      "province_id": 45,
      "municipality_name": "Abuyog"
    },
    {
      "municipality_id": 967,
      "province_id": 45,
      "municipality_name": "Alangalang"
    },
    {
      "municipality_id": 968,
      "province_id": 45,
      "municipality_name": "Albuera"
    },
    {
      "municipality_id": 969,
      "province_id": 45,
      "municipality_name": "Babatngon"
    },
    {
      "municipality_id": 970,
      "province_id": 45,
      "municipality_name": "Barugo"
    },
    {
      "municipality_id": 971,
      "province_id": 45,
      "municipality_name": "Bato"
    },
    {
      "municipality_id": 972,
      "province_id": 45,
      "municipality_name": "City of Baybay"
    },
    {
      "municipality_id": 973,
      "province_id": 45,
      "municipality_name": "Burauen"
    },
    {
      "municipality_id": 974,
      "province_id": 45,
      "municipality_name": "Calubian"
    },
    {
      "municipality_id": 975,
      "province_id": 45,
      "municipality_name": "Capoocan"
    },
    {
      "municipality_id": 976,
      "province_id": 45,
      "municipality_name": "Carigara"
    },
    {
      "municipality_id": 977,
      "province_id": 45,
      "municipality_name": "Dagami"
    },
    {
      "municipality_id": 978,
      "province_id": 45,
      "municipality_name": "Dulag"
    },
    {
      "municipality_id": 979,
      "province_id": 45,
      "municipality_name": "Hilongos"
    },
    {
      "municipality_id": 980,
      "province_id": 45,
      "municipality_name": "Hindang"
    },
    {
      "municipality_id": 981,
      "province_id": 45,
      "municipality_name": "Inopacan"
    },
    {
      "municipality_id": 982,
      "province_id": 45,
      "municipality_name": "Isabel"
    },
    {
      "municipality_id": 983,
      "province_id": 45,
      "municipality_name": "Jaro"
    },
    {
      "municipality_id": 984,
      "province_id": 45,
      "municipality_name": "Javier"
    },
    {
      "municipality_id": 985,
      "province_id": 45,
      "municipality_name": "Julita"
    },
    {
      "municipality_id": 986,
      "province_id": 45,
      "municipality_name": "Kananga"
    },
    {
      "municipality_id": 987,
      "province_id": 45,
      "municipality_name": "La Paz"
    },
    {
      "municipality_id": 988,
      "province_id": 45,
      "municipality_name": "Leyte"
    },
    {
      "municipality_id": 989,
      "province_id": 45,
      "municipality_name": "Macarthur"
    },
    {
      "municipality_id": 990,
      "province_id": 45,
      "municipality_name": "Mahaplag"
    },
    {
      "municipality_id": 991,
      "province_id": 45,
      "municipality_name": "Matag-Ob"
    },
    {
      "municipality_id": 992,
      "province_id": 45,
      "municipality_name": "Matalom"
    },
    {
      "municipality_id": 993,
      "province_id": 45,
      "municipality_name": "Mayorga"
    },
    {
      "municipality_id": 994,
      "province_id": 45,
      "municipality_name": "Merida"
    },
    {
      "municipality_id": 995,
      "province_id": 45,
      "municipality_name": "Ormoc City"
    },
    {
      "municipality_id": 996,
      "province_id": 45,
      "municipality_name": "Palo"
    },
    {
      "municipality_id": 997,
      "province_id": 45,
      "municipality_name": "Palompon"
    },
    {
      "municipality_id": 998,
      "province_id": 45,
      "municipality_name": "Pastrana"
    },
    {
      "municipality_id": 999,
      "province_id": 45,
      "municipality_name": "San Isidro"
    },
    {
      "municipality_id": 1000,
      "province_id": 45,
      "municipality_name": "San Miguel"
    },
    {
      "municipality_id": 1001,
      "province_id": 45,
      "municipality_name": "Santa Fe"
    },
    {
      "municipality_id": 1002,
      "province_id": 45,
      "municipality_name": "Tabango"
    },
    {
      "municipality_id": 1003,
      "province_id": 45,
      "municipality_name": "Tabontabon"
    },
    {
      "municipality_id": 1004,
      "province_id": 45,
      "municipality_name": "City of Tacloban"
    },
    {
      "municipality_id": 1005,
      "province_id": 45,
      "municipality_name": "Tanauan"
    },
    {
      "municipality_id": 1006,
      "province_id": 45,
      "municipality_name": "Tolosa"
    },
    {
      "municipality_id": 1007,
      "province_id": 45,
      "municipality_name": "Tunga"
    },
    {
      "municipality_id": 1008,
      "province_id": 45,
      "municipality_name": "Villaba"
    },
    {
      "municipality_id": 1009,
      "province_id": 46,
      "municipality_name": "Allen"
    },
    {
      "municipality_id": 1010,
      "province_id": 46,
      "municipality_name": "Biri"
    },
    {
      "municipality_id": 1011,
      "province_id": 46,
      "municipality_name": "Bobon"
    },
    {
      "municipality_id": 1012,
      "province_id": 46,
      "municipality_name": "Capul"
    },
    {
      "municipality_id": 1013,
      "province_id": 46,
      "municipality_name": "Catarman"
    },
    {
      "municipality_id": 1014,
      "province_id": 46,
      "municipality_name": "Catubig"
    },
    {
      "municipality_id": 1015,
      "province_id": 46,
      "municipality_name": "Gamay"
    },
    {
      "municipality_id": 1016,
      "province_id": 46,
      "municipality_name": "Laoang"
    },
    {
      "municipality_id": 1017,
      "province_id": 46,
      "municipality_name": "Lapinig"
    },
    {
      "municipality_id": 1018,
      "province_id": 46,
      "municipality_name": "Las Navas"
    },
    {
      "municipality_id": 1019,
      "province_id": 46,
      "municipality_name": "Lavezares"
    },
    {
      "municipality_id": 1020,
      "province_id": 46,
      "municipality_name": "Mapanas"
    },
    {
      "municipality_id": 1021,
      "province_id": 46,
      "municipality_name": "Mondragon"
    },
    {
      "municipality_id": 1022,
      "province_id": 46,
      "municipality_name": "Palapag"
    },
    {
      "municipality_id": 1023,
      "province_id": 46,
      "municipality_name": "Pambujan"
    },
    {
      "municipality_id": 1024,
      "province_id": 46,
      "municipality_name": "Rosario"
    },
    {
      "municipality_id": 1025,
      "province_id": 46,
      "municipality_name": "San Antonio"
    },
    {
      "municipality_id": 1026,
      "province_id": 46,
      "municipality_name": "San Isidro"
    },
    {
      "municipality_id": 1027,
      "province_id": 46,
      "municipality_name": "San Jose"
    },
    {
      "municipality_id": 1028,
      "province_id": 46,
      "municipality_name": "San Roque"
    },
    {
      "municipality_id": 1029,
      "province_id": 46,
      "municipality_name": "San Vicente"
    },
    {
      "municipality_id": 1030,
      "province_id": 46,
      "municipality_name": "Silvino Lobos"
    },
    {
      "municipality_id": 1031,
      "province_id": 46,
      "municipality_name": "Victoria"
    },
    {
      "municipality_id": 1032,
      "province_id": 46,
      "municipality_name": "Lope De Vega"
    },
    {
      "municipality_id": 1033,
      "province_id": 47,
      "municipality_name": "Almagro"
    },
    {
      "municipality_id": 1034,
      "province_id": 47,
      "municipality_name": "Basey"
    },
    {
      "municipality_id": 1035,
      "province_id": 47,
      "municipality_name": "City of Calbayog"
    },
    {
      "municipality_id": 1036,
      "province_id": 47,
      "municipality_name": "Calbiga"
    },
    {
      "municipality_id": 1037,
      "province_id": 47,
      "municipality_name": "City of Catbalogan"
    },
    {
      "municipality_id": 1038,
      "province_id": 47,
      "municipality_name": "Daram"
    },
    {
      "municipality_id": 1039,
      "province_id": 47,
      "municipality_name": "Gandara"
    },
    {
      "municipality_id": 1040,
      "province_id": 47,
      "municipality_name": "Hinabangan"
    },
    {
      "municipality_id": 1041,
      "province_id": 47,
      "municipality_name": "Jiabong"
    },
    {
      "municipality_id": 1042,
      "province_id": 47,
      "municipality_name": "Marabut"
    },
    {
      "municipality_id": 1043,
      "province_id": 47,
      "municipality_name": "Matuguinao"
    },
    {
      "municipality_id": 1044,
      "province_id": 47,
      "municipality_name": "Motiong"
    },
    {
      "municipality_id": 1045,
      "province_id": 47,
      "municipality_name": "Pinabacdao"
    },
    {
      "municipality_id": 1046,
      "province_id": 47,
      "municipality_name": "San Jose De Buan"
    },
    {
      "municipality_id": 1047,
      "province_id": 47,
      "municipality_name": "San Sebastian"
    },
    {
      "municipality_id": 1048,
      "province_id": 47,
      "municipality_name": "Santa Margarita"
    },
    {
      "municipality_id": 1049,
      "province_id": 47,
      "municipality_name": "Santa Rita"
    },
    {
      "municipality_id": 1050,
      "province_id": 47,
      "municipality_name": "Santo Niño"
    },
    {
      "municipality_id": 1051,
      "province_id": 47,
      "municipality_name": "Talalora"
    },
    {
      "municipality_id": 1052,
      "province_id": 47,
      "municipality_name": "Tarangnan"
    },
    {
      "municipality_id": 1053,
      "province_id": 47,
      "municipality_name": "Villareal"
    },
    {
      "municipality_id": 1054,
      "province_id": 47,
      "municipality_name": "Paranas"
    },
    {
      "municipality_id": 1055,
      "province_id": 47,
      "municipality_name": "Zumarraga"
    },
    {
      "municipality_id": 1056,
      "province_id": 47,
      "municipality_name": "Tagapul-An"
    },
    {
      "municipality_id": 1057,
      "province_id": 47,
      "municipality_name": "San Jorge"
    },
    {
      "municipality_id": 1058,
      "province_id": 47,
      "municipality_name": "Pagsanghan"
    },
    {
      "municipality_id": 1059,
      "province_id": 48,
      "municipality_name": "Anahawan"
    },
    {
      "municipality_id": 1060,
      "province_id": 48,
      "municipality_name": "Bontoc"
    },
    {
      "municipality_id": 1061,
      "province_id": 48,
      "municipality_name": "Hinunangan"
    },
    {
      "municipality_id": 1062,
      "province_id": 48,
      "municipality_name": "Hinundayan"
    },
    {
      "municipality_id": 1063,
      "province_id": 48,
      "municipality_name": "Libagon"
    },
    {
      "municipality_id": 1064,
      "province_id": 48,
      "municipality_name": "Liloan"
    },
    {
      "municipality_id": 1065,
      "province_id": 48,
      "municipality_name": "City of Maasin"
    },
    {
      "municipality_id": 1066,
      "province_id": 48,
      "municipality_name": "Macrohon"
    },
    {
      "municipality_id": 1067,
      "province_id": 48,
      "municipality_name": "Malitbog"
    },
    {
      "municipality_id": 1068,
      "province_id": 48,
      "municipality_name": "Padre Burgos"
    },
    {
      "municipality_id": 1069,
      "province_id": 48,
      "municipality_name": "Pintuyan"
    },
    {
      "municipality_id": 1070,
      "province_id": 48,
      "municipality_name": "Saint Bernard"
    },
    {
      "municipality_id": 1071,
      "province_id": 48,
      "municipality_name": "San Francisco"
    },
    {
      "municipality_id": 1072,
      "province_id": 48,
      "municipality_name": "San Juan"
    },
    {
      "municipality_id": 1073,
      "province_id": 48,
      "municipality_name": "San Ricardo"
    },
    {
      "municipality_id": 1074,
      "province_id": 48,
      "municipality_name": "Silago"
    },
    {
      "municipality_id": 1075,
      "province_id": 48,
      "municipality_name": "Sogod"
    },
    {
      "municipality_id": 1076,
      "province_id": 48,
      "municipality_name": "Tomas Oppus"
    },
    {
      "municipality_id": 1077,
      "province_id": 48,
      "municipality_name": "Limasawa"
    },
    {
      "municipality_id": 1078,
      "province_id": 49,
      "municipality_name": "Almeria"
    },
    {
      "municipality_id": 1079,
      "province_id": 49,
      "municipality_name": "Biliran"
    },
    {
      "municipality_id": 1080,
      "province_id": 49,
      "municipality_name": "Cabucgayan"
    },
    {
      "municipality_id": 1081,
      "province_id": 49,
      "municipality_name": "Caibiran"
    },
    {
      "municipality_id": 1082,
      "province_id": 49,
      "municipality_name": "Culaba"
    },
    {
      "municipality_id": 1083,
      "province_id": 49,
      "municipality_name": "Kawayan"
    },
    {
      "municipality_id": 1084,
      "province_id": 49,
      "municipality_name": "Maripipi"
    },
    {
      "municipality_id": 1085,
      "province_id": 49,
      "municipality_name": "Naval"
    },
    {
      "municipality_id": 1086,
      "province_id": 50,
      "municipality_name": "City of Dapitan"
    },
    {
      "municipality_id": 1087,
      "province_id": 50,
      "municipality_name": "City of Dipolog"
    },
    {
      "municipality_id": 1088,
      "province_id": 50,
      "municipality_name": "Katipunan"
    },
    {
      "municipality_id": 1089,
      "province_id": 50,
      "municipality_name": "La Libertad"
    },
    {
      "municipality_id": 1090,
      "province_id": 50,
      "municipality_name": "Labason"
    },
    {
      "municipality_id": 1091,
      "province_id": 50,
      "municipality_name": "Liloy"
    },
    {
      "municipality_id": 1092,
      "province_id": 50,
      "municipality_name": "Manukan"
    },
    {
      "municipality_id": 1093,
      "province_id": 50,
      "municipality_name": "Mutia"
    },
    {
      "municipality_id": 1094,
      "province_id": 50,
      "municipality_name": "Piñan"
    },
    {
      "municipality_id": 1095,
      "province_id": 50,
      "municipality_name": "Polanco"
    },
    {
      "municipality_id": 1096,
      "province_id": 50,
      "municipality_name": "Pres. Manuel A. Roxas"
    },
    {
      "municipality_id": 1097,
      "province_id": 50,
      "municipality_name": "Rizal"
    },
    {
      "municipality_id": 1098,
      "province_id": 50,
      "municipality_name": "Salug"
    },
    {
      "municipality_id": 1099,
      "province_id": 50,
      "municipality_name": "Sergio Osmeña Sr."
    },
    {
      "municipality_id": 1100,
      "province_id": 50,
      "municipality_name": "Siayan"
    },
    {
      "municipality_id": 1101,
      "province_id": 50,
      "municipality_name": "Sibuco"
    },
    {
      "municipality_id": 1102,
      "province_id": 50,
      "municipality_name": "Sibutad"
    },
    {
      "municipality_id": 1103,
      "province_id": 50,
      "municipality_name": "Sindangan"
    },
    {
      "municipality_id": 1104,
      "province_id": 50,
      "municipality_name": "Siocon"
    },
    {
      "municipality_id": 1105,
      "province_id": 50,
      "municipality_name": "Sirawai"
    },
    {
      "municipality_id": 1106,
      "province_id": 50,
      "municipality_name": "Tampilisan"
    },
    {
      "municipality_id": 1107,
      "province_id": 50,
      "municipality_name": "Jose Dalman"
    },
    {
      "municipality_id": 1108,
      "province_id": 50,
      "municipality_name": "Gutalac"
    },
    {
      "municipality_id": 1109,
      "province_id": 50,
      "municipality_name": "Baliguian"
    },
    {
      "municipality_id": 1110,
      "province_id": 50,
      "municipality_name": "Godod"
    },
    {
      "municipality_id": 1111,
      "province_id": 50,
      "municipality_name": "Bacungan"
    },
    {
      "municipality_id": 1112,
      "province_id": 50,
      "municipality_name": "Kalawit"
    },
    {
      "municipality_id": 1113,
      "province_id": 51,
      "municipality_name": "Aurora"
    },
    {
      "municipality_id": 1114,
      "province_id": 51,
      "municipality_name": "Bayog"
    },
    {
      "municipality_id": 1115,
      "province_id": 51,
      "municipality_name": "Dimataling"
    },
    {
      "municipality_id": 1116,
      "province_id": 51,
      "municipality_name": "Dinas"
    },
    {
      "municipality_id": 1117,
      "province_id": 51,
      "municipality_name": "Dumalinao"
    },
    {
      "municipality_id": 1118,
      "province_id": 51,
      "municipality_name": "Dumingag"
    },
    {
      "municipality_id": 1119,
      "province_id": 51,
      "municipality_name": "Kumalarang"
    },
    {
      "municipality_id": 1120,
      "province_id": 51,
      "municipality_name": "Labangan"
    },
    {
      "municipality_id": 1121,
      "province_id": 51,
      "municipality_name": "Lapuyan"
    },
    {
      "municipality_id": 1122,
      "province_id": 51,
      "municipality_name": "Mahayag"
    },
    {
      "municipality_id": 1123,
      "province_id": 51,
      "municipality_name": "Margosatubig"
    },
    {
      "municipality_id": 1124,
      "province_id": 51,
      "municipality_name": "Midsalip"
    },
    {
      "municipality_id": 1125,
      "province_id": 51,
      "municipality_name": "Molave"
    },
    {
      "municipality_id": 1126,
      "province_id": 51,
      "municipality_name": "City of Pagadian"
    },
    {
      "municipality_id": 1127,
      "province_id": 51,
      "municipality_name": "Ramon Magsaysay"
    },
    {
      "municipality_id": 1128,
      "province_id": 51,
      "municipality_name": "San Miguel"
    },
    {
      "municipality_id": 1129,
      "province_id": 51,
      "municipality_name": "San Pablo"
    },
    {
      "municipality_id": 1130,
      "province_id": 51,
      "municipality_name": "Tabina"
    },
    {
      "municipality_id": 1131,
      "province_id": 51,
      "municipality_name": "Tambulig"
    },
    {
      "municipality_id": 1132,
      "province_id": 51,
      "municipality_name": "Tukuran"
    },
    {
      "municipality_id": 1133,
      "province_id": 51,
      "municipality_name": "City of Zamboanga"
    },
    {
      "municipality_id": 1134,
      "province_id": 51,
      "municipality_name": "Lakewood"
    },
    {
      "municipality_id": 1135,
      "province_id": 51,
      "municipality_name": "Josefina"
    },
    {
      "municipality_id": 1136,
      "province_id": 51,
      "municipality_name": "Pitogo"
    },
    {
      "municipality_id": 1137,
      "province_id": 51,
      "municipality_name": "Sominot"
    },
    {
      "municipality_id": 1138,
      "province_id": 51,
      "municipality_name": "Vincenzo A. Sagun"
    },
    {
      "municipality_id": 1139,
      "province_id": 51,
      "municipality_name": "Guipos"
    },
    {
      "municipality_id": 1140,
      "province_id": 51,
      "municipality_name": "Tigbao"
    },
    {
      "municipality_id": 1141,
      "province_id": 52,
      "municipality_name": "Alicia"
    },
    {
      "municipality_id": 1142,
      "province_id": 52,
      "municipality_name": "Buug"
    },
    {
      "municipality_id": 1143,
      "province_id": 52,
      "municipality_name": "Diplahan"
    },
    {
      "municipality_id": 1144,
      "province_id": 52,
      "municipality_name": "Imelda"
    },
    {
      "municipality_id": 1145,
      "province_id": 52,
      "municipality_name": "Ipil"
    },
    {
      "municipality_id": 1146,
      "province_id": 52,
      "municipality_name": "Kabasalan"
    },
    {
      "municipality_id": 1147,
      "province_id": 52,
      "municipality_name": "Mabuhay"
    },
    {
      "municipality_id": 1148,
      "province_id": 52,
      "municipality_name": "Malangas"
    },
    {
      "municipality_id": 1149,
      "province_id": 52,
      "municipality_name": "Naga"
    },
    {
      "municipality_id": 1150,
      "province_id": 52,
      "municipality_name": "Olutanga"
    },
    {
      "municipality_id": 1151,
      "province_id": 52,
      "municipality_name": "Payao"
    },
    {
      "municipality_id": 1152,
      "province_id": 52,
      "municipality_name": "Roseller Lim"
    },
    {
      "municipality_id": 1153,
      "province_id": 52,
      "municipality_name": "Siay"
    },
    {
      "municipality_id": 1154,
      "province_id": 52,
      "municipality_name": "Talusan"
    },
    {
      "municipality_id": 1155,
      "province_id": 52,
      "municipality_name": "Titay"
    },
    {
      "municipality_id": 1156,
      "province_id": 52,
      "municipality_name": "Tungawan"
    },
    {
      "municipality_id": 1157,
      "province_id": 52,
      "municipality_name": "City of Isabela"
    },
    {
      "municipality_id": 1158,
      "province_id": 53,
      "municipality_name": "Baungon"
    },
    {
      "municipality_id": 1159,
      "province_id": 53,
      "municipality_name": "Damulog"
    },
    {
      "municipality_id": 1160,
      "province_id": 53,
      "municipality_name": "Dangcagan"
    },
    {
      "municipality_id": 1161,
      "province_id": 53,
      "municipality_name": "Don Carlos"
    },
    {
      "municipality_id": 1162,
      "province_id": 53,
      "municipality_name": "Impasug-ong"
    },
    {
      "municipality_id": 1163,
      "province_id": 53,
      "municipality_name": "Kadingilan"
    },
    {
      "municipality_id": 1164,
      "province_id": 53,
      "municipality_name": "Kalilangan"
    },
    {
      "municipality_id": 1165,
      "province_id": 53,
      "municipality_name": "Kibawe"
    },
    {
      "municipality_id": 1166,
      "province_id": 53,
      "municipality_name": "Kitaotao"
    },
    {
      "municipality_id": 1167,
      "province_id": 53,
      "municipality_name": "Lantapan"
    },
    {
      "municipality_id": 1168,
      "province_id": 53,
      "municipality_name": "Libona"
    },
    {
      "municipality_id": 1169,
      "province_id": 53,
      "municipality_name": "City of Malaybalay"
    },
    {
      "municipality_id": 1170,
      "province_id": 53,
      "municipality_name": "Malitbog"
    },
    {
      "municipality_id": 1171,
      "province_id": 53,
      "municipality_name": "Manolo Fortich"
    },
    {
      "municipality_id": 1172,
      "province_id": 53,
      "municipality_name": "Maramag"
    },
    {
      "municipality_id": 1173,
      "province_id": 53,
      "municipality_name": "Pangantucan"
    },
    {
      "municipality_id": 1174,
      "province_id": 53,
      "municipality_name": "Quezon"
    },
    {
      "municipality_id": 1175,
      "province_id": 53,
      "municipality_name": "San Fernando"
    },
    {
      "municipality_id": 1176,
      "province_id": 53,
      "municipality_name": "Sumilao"
    },
    {
      "municipality_id": 1177,
      "province_id": 53,
      "municipality_name": "Talakag"
    },
    {
      "municipality_id": 1178,
      "province_id": 53,
      "municipality_name": "City of Valencia"
    },
    {
      "municipality_id": 1179,
      "province_id": 53,
      "municipality_name": "Cabanglasan"
    },
    {
      "municipality_id": 1180,
      "province_id": 54,
      "municipality_name": "Catarman"
    },
    {
      "municipality_id": 1181,
      "province_id": 54,
      "municipality_name": "Guinsiliban"
    },
    {
      "municipality_id": 1182,
      "province_id": 54,
      "municipality_name": "Mahinog"
    },
    {
      "municipality_id": 1183,
      "province_id": 54,
      "municipality_name": "Mambajao"
    },
    {
      "municipality_id": 1184,
      "province_id": 54,
      "municipality_name": "Sagay"
    },
    {
      "municipality_id": 1185,
      "province_id": 55,
      "municipality_name": "Bacolod"
    },
    {
      "municipality_id": 1186,
      "province_id": 55,
      "municipality_name": "Baloi"
    },
    {
      "municipality_id": 1187,
      "province_id": 55,
      "municipality_name": "Baroy"
    },
    {
      "municipality_id": 1188,
      "province_id": 55,
      "municipality_name": "City of Iligan"
    },
    {
      "municipality_id": 1189,
      "province_id": 55,
      "municipality_name": "Kapatagan"
    },
    {
      "municipality_id": 1190,
      "province_id": 55,
      "municipality_name": "Sultan Naga Dimaporo"
    },
    {
      "municipality_id": 1191,
      "province_id": 55,
      "municipality_name": "Kauswagan"
    },
    {
      "municipality_id": 1192,
      "province_id": 55,
      "municipality_name": "Kolambugan"
    },
    {
      "municipality_id": 1193,
      "province_id": 55,
      "municipality_name": "Lala"
    },
    {
      "municipality_id": 1194,
      "province_id": 55,
      "municipality_name": "Linamon"
    },
    {
      "municipality_id": 1195,
      "province_id": 55,
      "municipality_name": "Magsaysay"
    },
    {
      "municipality_id": 1196,
      "province_id": 55,
      "municipality_name": "Maigo"
    },
    {
      "municipality_id": 1197,
      "province_id": 55,
      "municipality_name": "Matungao"
    },
    {
      "municipality_id": 1198,
      "province_id": 55,
      "municipality_name": "Munai"
    },
    {
      "municipality_id": 1199,
      "province_id": 55,
      "municipality_name": "Nunungan"
    },
    {
      "municipality_id": 1200,
      "province_id": 55,
      "municipality_name": "Pantao Ragat"
    },
    {
      "municipality_id": 1201,
      "province_id": 55,
      "municipality_name": "Poona Piagapo"
    },
    {
      "municipality_id": 1202,
      "province_id": 55,
      "municipality_name": "Salvador"
    },
    {
      "municipality_id": 1203,
      "province_id": 55,
      "municipality_name": "Sapad"
    },
    {
      "municipality_id": 1204,
      "province_id": 55,
      "municipality_name": "Tagoloan"
    },
    {
      "municipality_id": 1205,
      "province_id": 55,
      "municipality_name": "Tangcal"
    },
    {
      "municipality_id": 1206,
      "province_id": 55,
      "municipality_name": "Tubod"
    },
    {
      "municipality_id": 1207,
      "province_id": 55,
      "municipality_name": "Pantar"
    },
    {
      "municipality_id": 1208,
      "province_id": 56,
      "municipality_name": "Aloran"
    },
    {
      "municipality_id": 1209,
      "province_id": 56,
      "municipality_name": "Baliangao"
    },
    {
      "municipality_id": 1210,
      "province_id": 56,
      "municipality_name": "Bonifacio"
    },
    {
      "municipality_id": 1211,
      "province_id": 56,
      "municipality_name": "Calamba"
    },
    {
      "municipality_id": 1212,
      "province_id": 56,
      "municipality_name": "Clarin"
    },
    {
      "municipality_id": 1213,
      "province_id": 56,
      "municipality_name": "Concepcion"
    },
    {
      "municipality_id": 1214,
      "province_id": 56,
      "municipality_name": "Jimenez"
    },
    {
      "municipality_id": 1215,
      "province_id": 56,
      "municipality_name": "Lopez Jaena"
    },
    {
      "municipality_id": 1216,
      "province_id": 56,
      "municipality_name": "City of Oroquieta"
    },
    {
      "municipality_id": 1217,
      "province_id": 56,
      "municipality_name": "City of Ozamiz"
    },
    {
      "municipality_id": 1218,
      "province_id": 56,
      "municipality_name": "Panaon"
    },
    {
      "municipality_id": 1219,
      "province_id": 56,
      "municipality_name": "Plaridel"
    },
    {
      "municipality_id": 1220,
      "province_id": 56,
      "municipality_name": "Sapang Dalaga"
    },
    {
      "municipality_id": 1221,
      "province_id": 56,
      "municipality_name": "Sinacaban"
    },
    {
      "municipality_id": 1222,
      "province_id": 56,
      "municipality_name": "City of Tangub"
    },
    {
      "municipality_id": 1223,
      "province_id": 56,
      "municipality_name": "Tudela"
    },
    {
      "municipality_id": 1224,
      "province_id": 56,
      "municipality_name": "Don Victoriano Chiongbian"
    },
    {
      "municipality_id": 1225,
      "province_id": 57,
      "municipality_name": "Alubijid"
    },
    {
      "municipality_id": 1226,
      "province_id": 57,
      "municipality_name": "Balingasag"
    },
    {
      "municipality_id": 1227,
      "province_id": 57,
      "municipality_name": "Balingoan"
    },
    {
      "municipality_id": 1228,
      "province_id": 57,
      "municipality_name": "Binuangan"
    },
    {
      "municipality_id": 1229,
      "province_id": 57,
      "municipality_name": "City of Cagayan De Oro"
    },
    {
      "municipality_id": 1230,
      "province_id": 57,
      "municipality_name": "Claveria"
    },
    {
      "municipality_id": 1231,
      "province_id": 57,
      "municipality_name": "City of El Salvador"
    },
    {
      "municipality_id": 1232,
      "province_id": 57,
      "municipality_name": "City of Gingoog"
    },
    {
      "municipality_id": 1233,
      "province_id": 57,
      "municipality_name": "Gitagum"
    },
    {
      "municipality_id": 1234,
      "province_id": 57,
      "municipality_name": "Initao"
    },
    {
      "municipality_id": 1235,
      "province_id": 57,
      "municipality_name": "Jasaan"
    },
    {
      "municipality_id": 1236,
      "province_id": 57,
      "municipality_name": "Kinoguitan"
    },
    {
      "municipality_id": 1237,
      "province_id": 57,
      "municipality_name": "Lagonglong"
    },
    {
      "municipality_id": 1238,
      "province_id": 57,
      "municipality_name": "Laguindingan"
    },
    {
      "municipality_id": 1239,
      "province_id": 57,
      "municipality_name": "Libertad"
    },
    {
      "municipality_id": 1240,
      "province_id": 57,
      "municipality_name": "Lugait"
    },
    {
      "municipality_id": 1241,
      "province_id": 57,
      "municipality_name": "Magsaysay"
    },
    {
      "municipality_id": 1242,
      "province_id": 57,
      "municipality_name": "Manticao"
    },
    {
      "municipality_id": 1243,
      "province_id": 57,
      "municipality_name": "Medina"
    },
    {
      "municipality_id": 1244,
      "province_id": 57,
      "municipality_name": "Naawan"
    },
    {
      "municipality_id": 1245,
      "province_id": 57,
      "municipality_name": "Opol"
    },
    {
      "municipality_id": 1246,
      "province_id": 57,
      "municipality_name": "Salay"
    },
    {
      "municipality_id": 1247,
      "province_id": 57,
      "municipality_name": "Sugbongcogon"
    },
    {
      "municipality_id": 1248,
      "province_id": 57,
      "municipality_name": "Tagoloan"
    },
    {
      "municipality_id": 1249,
      "province_id": 57,
      "municipality_name": "Talisayan"
    },
    {
      "municipality_id": 1250,
      "province_id": 57,
      "municipality_name": "Villanueva"
    },
    {
      "municipality_id": 1251,
      "province_id": 58,
      "municipality_name": "Asuncion"
    },
    {
      "municipality_id": 1252,
      "province_id": 58,
      "municipality_name": "Carmen"
    },
    {
      "municipality_id": 1253,
      "province_id": 58,
      "municipality_name": "Kapalong"
    },
    {
      "municipality_id": 1254,
      "province_id": 58,
      "municipality_name": "New Corella"
    },
    {
      "municipality_id": 1255,
      "province_id": 58,
      "municipality_name": "City of Panabo"
    },
    {
      "municipality_id": 1256,
      "province_id": 58,
      "municipality_name": "Island Garden City of Samal"
    },
    {
      "municipality_id": 1257,
      "province_id": 58,
      "municipality_name": "Santo Tomas"
    },
    {
      "municipality_id": 1258,
      "province_id": 58,
      "municipality_name": "City of Tagum"
    },
    {
      "municipality_id": 1259,
      "province_id": 58,
      "municipality_name": "Talaingod"
    },
    {
      "municipality_id": 1260,
      "province_id": 58,
      "municipality_name": "Braulio E. Dujali"
    },
    {
      "municipality_id": 1261,
      "province_id": 58,
      "municipality_name": "San Isidro"
    },
    {
      "municipality_id": 1262,
      "province_id": 59,
      "municipality_name": "Bansalan"
    },
    {
      "municipality_id": 1263,
      "province_id": 59,
      "municipality_name": "City of Davao"
    },
    {
      "municipality_id": 1264,
      "province_id": 59,
      "municipality_name": "City of Digos"
    },
    {
      "municipality_id": 1265,
      "province_id": 59,
      "municipality_name": "Hagonoy"
    },
    {
      "municipality_id": 1266,
      "province_id": 59,
      "municipality_name": "Kiblawan"
    },
    {
      "municipality_id": 1267,
      "province_id": 59,
      "municipality_name": "Magsaysay"
    },
    {
      "municipality_id": 1268,
      "province_id": 59,
      "municipality_name": "Malalag"
    },
    {
      "municipality_id": 1269,
      "province_id": 59,
      "municipality_name": "Matanao"
    },
    {
      "municipality_id": 1270,
      "province_id": 59,
      "municipality_name": "Padada"
    },
    {
      "municipality_id": 1271,
      "province_id": 59,
      "municipality_name": "Santa Cruz"
    },
    {
      "municipality_id": 1272,
      "province_id": 59,
      "municipality_name": "Sulop"
    },
    {
      "municipality_id": 1273,
      "province_id": 60,
      "municipality_name": "Baganga"
    },
    {
      "municipality_id": 1274,
      "province_id": 60,
      "municipality_name": "Banaybanay"
    },
    {
      "municipality_id": 1275,
      "province_id": 60,
      "municipality_name": "Boston"
    },
    {
      "municipality_id": 1276,
      "province_id": 60,
      "municipality_name": "Caraga"
    },
    {
      "municipality_id": 1277,
      "province_id": 60,
      "municipality_name": "Cateel"
    },
    {
      "municipality_id": 1278,
      "province_id": 60,
      "municipality_name": "Governor Generoso"
    },
    {
      "municipality_id": 1279,
      "province_id": 60,
      "municipality_name": "Lupon"
    },
    {
      "municipality_id": 1280,
      "province_id": 60,
      "municipality_name": "Manay"
    },
    {
      "municipality_id": 1281,
      "province_id": 60,
      "municipality_name": "City of Mati"
    },
    {
      "municipality_id": 1282,
      "province_id": 60,
      "municipality_name": "San Isidro"
    },
    {
      "municipality_id": 1283,
      "province_id": 60,
      "municipality_name": "Tarragona"
    },
    {
      "municipality_id": 1284,
      "province_id": 61,
      "municipality_name": "Compostela"
    },
    {
      "municipality_id": 1285,
      "province_id": 61,
      "municipality_name": "Laak"
    },
    {
      "municipality_id": 1286,
      "province_id": 61,
      "municipality_name": "Mabini"
    },
    {
      "municipality_id": 1287,
      "province_id": 61,
      "municipality_name": "Maco"
    },
    {
      "municipality_id": 1288,
      "province_id": 61,
      "municipality_name": "Maragusan"
    },
    {
      "municipality_id": 1289,
      "province_id": 61,
      "municipality_name": "Mawab"
    },
    {
      "municipality_id": 1290,
      "province_id": 61,
      "municipality_name": "Monkayo"
    },
    {
      "municipality_id": 1291,
      "province_id": 61,
      "municipality_name": "Montevista"
    },
    {
      "municipality_id": 1292,
      "province_id": 61,
      "municipality_name": "Nabunturan"
    },
    {
      "municipality_id": 1293,
      "province_id": 61,
      "municipality_name": "New Bataan"
    },
    {
      "municipality_id": 1294,
      "province_id": 61,
      "municipality_name": "Pantukan"
    },
    {
      "municipality_id": 1295,
      "province_id": 62,
      "municipality_name": "Don Marcelino"
    },
    {
      "municipality_id": 1296,
      "province_id": 62,
      "municipality_name": "Jose Abad Santos"
    },
    {
      "municipality_id": 1297,
      "province_id": 62,
      "municipality_name": "Malita"
    },
    {
      "municipality_id": 1298,
      "province_id": 62,
      "municipality_name": "Santa Maria"
    },
    {
      "municipality_id": 1299,
      "province_id": 62,
      "municipality_name": "Sarangani"
    },
    {
      "municipality_id": 1300,
      "province_id": 63,
      "municipality_name": "Alamada"
    },
    {
      "municipality_id": 1301,
      "province_id": 63,
      "municipality_name": "Carmen"
    },
    {
      "municipality_id": 1302,
      "province_id": 63,
      "municipality_name": "Kabacan"
    },
    {
      "municipality_id": 1303,
      "province_id": 63,
      "municipality_name": "City of Kidapawan"
    },
    {
      "municipality_id": 1304,
      "province_id": 63,
      "municipality_name": "Libungan"
    },
    {
      "municipality_id": 1305,
      "province_id": 63,
      "municipality_name": "Magpet"
    },
    {
      "municipality_id": 1306,
      "province_id": 63,
      "municipality_name": "Makilala"
    },
    {
      "municipality_id": 1307,
      "province_id": 63,
      "municipality_name": "Matalam"
    },
    {
      "municipality_id": 1308,
      "province_id": 63,
      "municipality_name": "Midsayap"
    },
    {
      "municipality_id": 1309,
      "province_id": 63,
      "municipality_name": "M'Lang"
    },
    {
      "municipality_id": 1310,
      "province_id": 63,
      "municipality_name": "Pigkawayan"
    },
    {
      "municipality_id": 1311,
      "province_id": 63,
      "municipality_name": "Pikit"
    },
    {
      "municipality_id": 1312,
      "province_id": 63,
      "municipality_name": "President Roxas"
    },
    {
      "municipality_id": 1313,
      "province_id": 63,
      "municipality_name": "Tulunan"
    },
    {
      "municipality_id": 1314,
      "province_id": 63,
      "municipality_name": "Antipas"
    },
    {
      "municipality_id": 1315,
      "province_id": 63,
      "municipality_name": "Banisilan"
    },
    {
      "municipality_id": 1316,
      "province_id": 63,
      "municipality_name": "Aleosan"
    },
    {
      "municipality_id": 1317,
      "province_id": 63,
      "municipality_name": "Arakan"
    },
    {
      "municipality_id": 1318,
      "province_id": 64,
      "municipality_name": "Banga"
    },
    {
      "municipality_id": 1319,
      "province_id": 64,
      "municipality_name": "City of General Santos"
    },
    {
      "municipality_id": 1320,
      "province_id": 64,
      "municipality_name": "City of Koronadal"
    },
    {
      "municipality_id": 1321,
      "province_id": 64,
      "municipality_name": "Norala"
    },
    {
      "municipality_id": 1322,
      "province_id": 64,
      "municipality_name": "Polomolok"
    },
    {
      "municipality_id": 1323,
      "province_id": 64,
      "municipality_name": "Surallah"
    },
    {
      "municipality_id": 1324,
      "province_id": 64,
      "municipality_name": "Tampakan"
    },
    {
      "municipality_id": 1325,
      "province_id": 64,
      "municipality_name": "Tantangan"
    },
    {
      "municipality_id": 1326,
      "province_id": 64,
      "municipality_name": "T'Boli"
    },
    {
      "municipality_id": 1327,
      "province_id": 64,
      "municipality_name": "Tupi"
    },
    {
      "municipality_id": 1328,
      "province_id": 64,
      "municipality_name": "Santo Niño"
    },
    {
      "municipality_id": 1329,
      "province_id": 64,
      "municipality_name": "Lake Sebu"
    },
    {
      "municipality_id": 1330,
      "province_id": 65,
      "municipality_name": "Bagumbayan"
    },
    {
      "municipality_id": 1331,
      "province_id": 65,
      "municipality_name": "Columbio"
    },
    {
      "municipality_id": 1332,
      "province_id": 65,
      "municipality_name": "Esperanza"
    },
    {
      "municipality_id": 1333,
      "province_id": 65,
      "municipality_name": "Isulan"
    },
    {
      "municipality_id": 1334,
      "province_id": 65,
      "municipality_name": "Kalamansig"
    },
    {
      "municipality_id": 1335,
      "province_id": 65,
      "municipality_name": "Lebak"
    },
    {
      "municipality_id": 1336,
      "province_id": 65,
      "municipality_name": "Lutayan"
    },
    {
      "municipality_id": 1337,
      "province_id": 65,
      "municipality_name": "Lambayong"
    },
    {
      "municipality_id": 1338,
      "province_id": 65,
      "municipality_name": "Palimbang"
    },
    {
      "municipality_id": 1339,
      "province_id": 65,
      "municipality_name": "President Quirino"
    },
    {
      "municipality_id": 1340,
      "province_id": 65,
      "municipality_name": "City of Tacurong"
    },
    {
      "municipality_id": 1341,
      "province_id": 65,
      "municipality_name": "Sen. Ninoy Aquino"
    },
    {
      "municipality_id": 1342,
      "province_id": 66,
      "municipality_name": "Alabel"
    },
    {
      "municipality_id": 1343,
      "province_id": 66,
      "municipality_name": "Glan"
    },
    {
      "municipality_id": 1344,
      "province_id": 66,
      "municipality_name": "Kiamba"
    },
    {
      "municipality_id": 1345,
      "province_id": 66,
      "municipality_name": "Maasim"
    },
    {
      "municipality_id": 1346,
      "province_id": 66,
      "municipality_name": "Maitum"
    },
    {
      "municipality_id": 1347,
      "province_id": 66,
      "municipality_name": "Malapatan"
    },
    {
      "municipality_id": 1348,
      "province_id": 66,
      "municipality_name": "Malungon"
    },
    {
      "municipality_id": 1349,
      "province_id": 66,
      "municipality_name": "Cotabato City"
    },
    {
      "municipality_id": 1350,
      "province_id": 1,
      "municipality_name": "Manila"
    },
    {
      "municipality_id": 1351,
      "province_id": 1,
      "municipality_name": "Mandaluyong City"
    },
    {
      "municipality_id": 1352,
      "province_id": 1,
      "municipality_name": "Marikina City"
    },
    {
      "municipality_id": 1353,
      "province_id": 1,
      "municipality_name": "Pasig City"
    },
    {
      "municipality_id": 1354,
      "province_id": 1,
      "municipality_name": "Quezon City"
    },
    {
      "municipality_id": 1355,
      "province_id": 1,
      "municipality_name": "San Juan City"
    },
    {
      "municipality_id": 1356,
      "province_id": 1,
      "municipality_name": "Caloocan City"
    },
    {
      "municipality_id": 1357,
      "province_id": 1,
      "municipality_name": "Malabon City"
    },
    {
      "municipality_id": 1358,
      "province_id": 1,
      "municipality_name": "Navotas City"
    },
    {
      "municipality_id": 1359,
      "province_id": 1,
      "municipality_name": "Valenzuela City"
    },
    {
      "municipality_id": 1360,
      "province_id": 1,
      "municipality_name": "Las Piñas City"
    },
    {
      "municipality_id": 1361,
      "province_id": 1,
      "municipality_name": "Makati City"
    },
    {
      "municipality_id": 1362,
      "province_id": 1,
      "municipality_name": "Muntinlupa City"
    },
    {
      "municipality_id": 1363,
      "province_id": 1,
      "municipality_name": "Parañaque City"
    },
    {
      "municipality_id": 1364,
      "province_id": 1,
      "municipality_name": "Pasay City"
    },
    {
      "municipality_id": 1365,
      "province_id": 1,
      "municipality_name": "Pateros"
    },
    {
      "municipality_id": 1366,
      "province_id": 1,
      "municipality_name": "Taguig City"
    },
    {
      "municipality_id": 1367,
      "province_id": 67,
      "municipality_name": "Bangued"
    },
    {
      "municipality_id": 1368,
      "province_id": 67,
      "municipality_name": "Boliney"
    },
    {
      "municipality_id": 1369,
      "province_id": 67,
      "municipality_name": "Bucay"
    },
    {
      "municipality_id": 1370,
      "province_id": 67,
      "municipality_name": "Bucloc"
    },
    {
      "municipality_id": 1371,
      "province_id": 67,
      "municipality_name": "Daguioman"
    },
    {
      "municipality_id": 1372,
      "province_id": 67,
      "municipality_name": "Danglas"
    },
    {
      "municipality_id": 1373,
      "province_id": 67,
      "municipality_name": "Dolores"
    },
    {
      "municipality_id": 1374,
      "province_id": 67,
      "municipality_name": "La Paz"
    },
    {
      "municipality_id": 1375,
      "province_id": 67,
      "municipality_name": "Lacub"
    },
    {
      "municipality_id": 1376,
      "province_id": 67,
      "municipality_name": "Lagangilang"
    },
    {
      "municipality_id": 1377,
      "province_id": 67,
      "municipality_name": "Lagayan"
    },
    {
      "municipality_id": 1378,
      "province_id": 67,
      "municipality_name": "Langiden"
    },
    {
      "municipality_id": 1379,
      "province_id": 67,
      "municipality_name": "Licuan-Baay"
    },
    {
      "municipality_id": 1380,
      "province_id": 67,
      "municipality_name": "Luba"
    },
    {
      "municipality_id": 1381,
      "province_id": 67,
      "municipality_name": "Malibcong"
    },
    {
      "municipality_id": 1382,
      "province_id": 67,
      "municipality_name": "Manabo"
    },
    {
      "municipality_id": 1383,
      "province_id": 67,
      "municipality_name": "Peñarrubia"
    },
    {
      "municipality_id": 1384,
      "province_id": 67,
      "municipality_name": "Pidigan"
    },
    {
      "municipality_id": 1385,
      "province_id": 67,
      "municipality_name": "Pilar"
    },
    {
      "municipality_id": 1386,
      "province_id": 67,
      "municipality_name": "Sallapadan"
    },
    {
      "municipality_id": 1387,
      "province_id": 67,
      "municipality_name": "San Isidro"
    },
    {
      "municipality_id": 1388,
      "province_id": 67,
      "municipality_name": "San Juan"
    },
    {
      "municipality_id": 1389,
      "province_id": 67,
      "municipality_name": "San Quintin"
    },
    {
      "municipality_id": 1390,
      "province_id": 67,
      "municipality_name": "Tayum"
    },
    {
      "municipality_id": 1391,
      "province_id": 67,
      "municipality_name": "Tineg"
    },
    {
      "municipality_id": 1392,
      "province_id": 67,
      "municipality_name": "Tubo"
    },
    {
      "municipality_id": 1393,
      "province_id": 67,
      "municipality_name": "Villaviciosa"
    },
    {
      "municipality_id": 1394,
      "province_id": 68,
      "municipality_name": "Atok"
    },
    {
      "municipality_id": 1395,
      "province_id": 68,
      "municipality_name": "City of Baguio"
    },
    {
      "municipality_id": 1396,
      "province_id": 68,
      "municipality_name": "Bakun"
    },
    {
      "municipality_id": 1397,
      "province_id": 68,
      "municipality_name": "Bokod"
    },
    {
      "municipality_id": 1398,
      "province_id": 68,
      "municipality_name": "Buguias"
    },
    {
      "municipality_id": 1399,
      "province_id": 68,
      "municipality_name": "Itogon"
    },
    {
      "municipality_id": 1400,
      "province_id": 68,
      "municipality_name": "Kabayan"
    },
    {
      "municipality_id": 1401,
      "province_id": 68,
      "municipality_name": "Kapangan"
    },
    {
      "municipality_id": 1402,
      "province_id": 68,
      "municipality_name": "Kibungan"
    },
    {
      "municipality_id": 1403,
      "province_id": 68,
      "municipality_name": "La Trinidad"
    },
    {
      "municipality_id": 1404,
      "province_id": 68,
      "municipality_name": "Mankayan"
    },
    {
      "municipality_id": 1405,
      "province_id": 68,
      "municipality_name": "Sablan"
    },
    {
      "municipality_id": 1406,
      "province_id": 68,
      "municipality_name": "Tuba"
    },
    {
      "municipality_id": 1407,
      "province_id": 68,
      "municipality_name": "Tublay"
    },
    {
      "municipality_id": 1408,
      "province_id": 69,
      "municipality_name": "Banaue"
    },
    {
      "municipality_id": 1409,
      "province_id": 69,
      "municipality_name": "Hungduan"
    },
    {
      "municipality_id": 1410,
      "province_id": 69,
      "municipality_name": "Kiangan"
    },
    {
      "municipality_id": 1411,
      "province_id": 69,
      "municipality_name": "Lagawe"
    },
    {
      "municipality_id": 1412,
      "province_id": 69,
      "municipality_name": "Lamut"
    },
    {
      "municipality_id": 1413,
      "province_id": 69,
      "municipality_name": "Mayoyao"
    },
    {
      "municipality_id": 1414,
      "province_id": 69,
      "municipality_name": "Alfonso Lista"
    },
    {
      "municipality_id": 1415,
      "province_id": 69,
      "municipality_name": "Aguinaldo"
    },
    {
      "municipality_id": 1416,
      "province_id": 69,
      "municipality_name": "Hingyon"
    },
    {
      "municipality_id": 1417,
      "province_id": 69,
      "municipality_name": "Tinoc"
    },
    {
      "municipality_id": 1418,
      "province_id": 69,
      "municipality_name": "Asipulo"
    },
    {
      "municipality_id": 1419,
      "province_id": 70,
      "municipality_name": "Balbalan"
    },
    {
      "municipality_id": 1420,
      "province_id": 70,
      "municipality_name": "Lubuagan"
    },
    {
      "municipality_id": 1421,
      "province_id": 70,
      "municipality_name": "Pasil"
    },
    {
      "municipality_id": 1422,
      "province_id": 70,
      "municipality_name": "Pinukpuk"
    },
    {
      "municipality_id": 1423,
      "province_id": 70,
      "municipality_name": "Rizal"
    },
    {
      "municipality_id": 1424,
      "province_id": 70,
      "municipality_name": "City of Tabuk"
    },
    {
      "municipality_id": 1425,
      "province_id": 70,
      "municipality_name": "Tanudan"
    },
    {
      "municipality_id": 1426,
      "province_id": 70,
      "municipality_name": "Tinglayan"
    },
    {
      "municipality_id": 1427,
      "province_id": 71,
      "municipality_name": "Barlig"
    },
    {
      "municipality_id": 1428,
      "province_id": 71,
      "municipality_name": "Bauko"
    },
    {
      "municipality_id": 1429,
      "province_id": 71,
      "municipality_name": "Besao"
    },
    {
      "municipality_id": 1430,
      "province_id": 71,
      "municipality_name": "Bontoc"
    },
    {
      "municipality_id": 1431,
      "province_id": 71,
      "municipality_name": "Natonin"
    },
    {
      "municipality_id": 1432,
      "province_id": 71,
      "municipality_name": "Paracelis"
    },
    {
      "municipality_id": 1433,
      "province_id": 71,
      "municipality_name": "Sabangan"
    },
    {
      "municipality_id": 1434,
      "province_id": 71,
      "municipality_name": "Sadanga"
    },
    {
      "municipality_id": 1435,
      "province_id": 71,
      "municipality_name": "Sagada"
    },
    {
      "municipality_id": 1436,
      "province_id": 71,
      "municipality_name": "Tadian"
    },
    {
      "municipality_id": 1437,
      "province_id": 72,
      "municipality_name": "Calanasan"
    },
    {
      "municipality_id": 1438,
      "province_id": 72,
      "municipality_name": "Conner"
    },
    {
      "municipality_id": 1439,
      "province_id": 72,
      "municipality_name": "Flora"
    },
    {
      "municipality_id": 1440,
      "province_id": 72,
      "municipality_name": "Kabugao"
    },
    {
      "municipality_id": 1441,
      "province_id": 72,
      "municipality_name": "Luna"
    },
    {
      "municipality_id": 1442,
      "province_id": 72,
      "municipality_name": "Pudtol"
    },
    {
      "municipality_id": 1443,
      "province_id": 72,
      "municipality_name": "Santa Marcela"
    },
    {
      "municipality_id": 1444,
      "province_id": 73,
      "municipality_name": "City of Lamitan"
    },
    {
      "municipality_id": 1445,
      "province_id": 73,
      "municipality_name": "Lantawan"
    },
    {
      "municipality_id": 1446,
      "province_id": 73,
      "municipality_name": "Maluso"
    },
    {
      "municipality_id": 1447,
      "province_id": 73,
      "municipality_name": "Sumisip"
    },
    {
      "municipality_id": 1448,
      "province_id": 73,
      "municipality_name": "Tipo-Tipo"
    },
    {
      "municipality_id": 1449,
      "province_id": 73,
      "municipality_name": "Tuburan"
    },
    {
      "municipality_id": 1450,
      "province_id": 73,
      "municipality_name": "Akbar"
    },
    {
      "municipality_id": 1451,
      "province_id": 73,
      "municipality_name": "Al-Barka"
    },
    {
      "municipality_id": 1452,
      "province_id": 73,
      "municipality_name": "Hadji Mohammad Ajul"
    },
    {
      "municipality_id": 1453,
      "province_id": 73,
      "municipality_name": "Ungkaya Pukan"
    },
    {
      "municipality_id": 1454,
      "province_id": 73,
      "municipality_name": "Hadji Muhtamad"
    },
    {
      "municipality_id": 1455,
      "province_id": 73,
      "municipality_name": "Tabuan-Lasa"
    },
    {
      "municipality_id": 1456,
      "province_id": 74,
      "municipality_name": "Bacolod-Kalawi"
    },
    {
      "municipality_id": 1457,
      "province_id": 74,
      "municipality_name": "Balabagan"
    },
    {
      "municipality_id": 1458,
      "province_id": 74,
      "municipality_name": "Balindong"
    },
    {
      "municipality_id": 1459,
      "province_id": 74,
      "municipality_name": "Bayang"
    },
    {
      "municipality_id": 1460,
      "province_id": 74,
      "municipality_name": "Binidayan"
    },
    {
      "municipality_id": 1461,
      "province_id": 74,
      "municipality_name": "Bubong"
    },
    {
      "municipality_id": 1462,
      "province_id": 74,
      "municipality_name": "Butig"
    },
    {
      "municipality_id": 1463,
      "province_id": 74,
      "municipality_name": "Ganassi"
    },
    {
      "municipality_id": 1464,
      "province_id": 74,
      "municipality_name": "Kapai"
    },
    {
      "municipality_id": 1465,
      "province_id": 74,
      "municipality_name": "Lumba-Bayabao"
    },
    {
      "municipality_id": 1466,
      "province_id": 74,
      "municipality_name": "Lumbatan"
    },
    {
      "municipality_id": 1467,
      "province_id": 74,
      "municipality_name": "Madalum"
    },
    {
      "municipality_id": 1468,
      "province_id": 74,
      "municipality_name": "Madamba"
    },
    {
      "municipality_id": 1469,
      "province_id": 74,
      "municipality_name": "Malabang"
    },
    {
      "municipality_id": 1470,
      "province_id": 74,
      "municipality_name": "Marantao"
    },
    {
      "municipality_id": 1471,
      "province_id": 74,
      "municipality_name": "City of Marawi"
    },
    {
      "municipality_id": 1472,
      "province_id": 74,
      "municipality_name": "Masiu"
    },
    {
      "municipality_id": 1473,
      "province_id": 74,
      "municipality_name": "Mulondo"
    },
    {
      "municipality_id": 1474,
      "province_id": 74,
      "municipality_name": "Pagayawan"
    },
    {
      "municipality_id": 1475,
      "province_id": 74,
      "municipality_name": "Piagapo"
    },
    {
      "municipality_id": 1476,
      "province_id": 74,
      "municipality_name": "Poona Bayabao"
    },
    {
      "municipality_id": 1477,
      "province_id": 74,
      "municipality_name": "Pualas"
    },
    {
      "municipality_id": 1478,
      "province_id": 74,
      "municipality_name": "Ditsaan-Ramain"
    },
    {
      "municipality_id": 1479,
      "province_id": 74,
      "municipality_name": "Saguiaran"
    },
    {
      "municipality_id": 1480,
      "province_id": 74,
      "municipality_name": "Tamparan"
    },
    {
      "municipality_id": 1481,
      "province_id": 74,
      "municipality_name": "Taraka"
    },
    {
      "municipality_id": 1482,
      "province_id": 74,
      "municipality_name": "Tubaran"
    },
    {
      "municipality_id": 1483,
      "province_id": 74,
      "municipality_name": "Tugaya"
    },
    {
      "municipality_id": 1484,
      "province_id": 74,
      "municipality_name": "Wao"
    },
    {
      "municipality_id": 1485,
      "province_id": 74,
      "municipality_name": "Marogong"
    },
    {
      "municipality_id": 1486,
      "province_id": 74,
      "municipality_name": "Calanogas"
    },
    {
      "municipality_id": 1487,
      "province_id": 74,
      "municipality_name": "Buadiposo-Buntong"
    },
    {
      "municipality_id": 1488,
      "province_id": 74,
      "municipality_name": "Maguing"
    },
    {
      "municipality_id": 1489,
      "province_id": 74,
      "municipality_name": "Picong"
    },
    {
      "municipality_id": 1490,
      "province_id": 74,
      "municipality_name": "Lumbayanague"
    },
    {
      "municipality_id": 1491,
      "province_id": 74,
      "municipality_name": "Amai Manabilang"
    },
    {
      "municipality_id": 1492,
      "province_id": 74,
      "municipality_name": "Tagoloan Ii"
    },
    {
      "municipality_id": 1493,
      "province_id": 74,
      "municipality_name": "Kapatagan"
    },
    {
      "municipality_id": 1494,
      "province_id": 74,
      "municipality_name": "Sultan Dumalondong"
    },
    {
      "municipality_id": 1495,
      "province_id": 74,
      "municipality_name": "Lumbaca-Unayan"
    },
    {
      "municipality_id": 1496,
      "province_id": 75,
      "municipality_name": "Ampatuan"
    },
    {
      "municipality_id": 1497,
      "province_id": 75,
      "municipality_name": "Buldon"
    },
    {
      "municipality_id": 1498,
      "province_id": 75,
      "municipality_name": "Buluan"
    },
    {
      "municipality_id": 1499,
      "province_id": 75,
      "municipality_name": "Datu Paglas"
    },
    {
      "municipality_id": 1500,
      "province_id": 75,
      "municipality_name": "Datu Piang"
    },
    {
      "municipality_id": 1501,
      "province_id": 75,
      "municipality_name": "Datu Odin Sinsuat"
    },
    {
      "municipality_id": 1502,
      "province_id": 75,
      "municipality_name": "Shariff Aguak"
    },
    {
      "municipality_id": 1503,
      "province_id": 75,
      "municipality_name": "Matanog"
    },
    {
      "municipality_id": 1504,
      "province_id": 75,
      "municipality_name": "Pagalungan"
    },
    {
      "municipality_id": 1505,
      "province_id": 75,
      "municipality_name": "Parang"
    },
    {
      "municipality_id": 1506,
      "province_id": 75,
      "municipality_name": "Sultan Kudarat"
    },
    {
      "municipality_id": 1507,
      "province_id": 75,
      "municipality_name": "Sultan Sa Barongis"
    },
    {
      "municipality_id": 1508,
      "province_id": 75,
      "municipality_name": "Kabuntalan"
    },
    {
      "municipality_id": 1509,
      "province_id": 75,
      "municipality_name": "Upi"
    },
    {
      "municipality_id": 1510,
      "province_id": 75,
      "municipality_name": "Talayan"
    },
    {
      "municipality_id": 1511,
      "province_id": 75,
      "municipality_name": "South Upi"
    },
    {
      "municipality_id": 1512,
      "province_id": 75,
      "municipality_name": "Barira"
    },
    {
      "municipality_id": 1513,
      "province_id": 75,
      "municipality_name": "Gen. S.K. Pendatun"
    },
    {
      "municipality_id": 1514,
      "province_id": 75,
      "municipality_name": "Mamasapano"
    },
    {
      "municipality_id": 1515,
      "province_id": 75,
      "municipality_name": "Talitay"
    },
    {
      "municipality_id": 1516,
      "province_id": 75,
      "municipality_name": "Pagagawan"
    },
    {
      "municipality_id": 1517,
      "province_id": 75,
      "municipality_name": "Paglat"
    },
    {
      "municipality_id": 1518,
      "province_id": 75,
      "municipality_name": "Sultan Mastura"
    },
    {
      "municipality_id": 1519,
      "province_id": 75,
      "municipality_name": "Guindulungan"
    },
    {
      "municipality_id": 1520,
      "province_id": 75,
      "municipality_name": "Datu Saudi-Ampatuan"
    },
    {
      "municipality_id": 1521,
      "province_id": 75,
      "municipality_name": "Datu Unsay"
    },
    {
      "municipality_id": 1522,
      "province_id": 75,
      "municipality_name": "Datu Abdullah Sangki"
    },
    {
      "municipality_id": 1523,
      "province_id": 75,
      "municipality_name": "Rajah Buayan"
    },
    {
      "municipality_id": 1524,
      "province_id": 75,
      "municipality_name": "Datu Blah T. Sinsuat"
    },
    {
      "municipality_id": 1525,
      "province_id": 75,
      "municipality_name": "Datu Anggal Midtimbang"
    },
    {
      "municipality_id": 1526,
      "province_id": 75,
      "municipality_name": "Mangudadatu"
    },
    {
      "municipality_id": 1527,
      "province_id": 75,
      "municipality_name": "Pandag"
    },
    {
      "municipality_id": 1528,
      "province_id": 75,
      "municipality_name": "Northern Kabuntalan"
    },
    {
      "municipality_id": 1529,
      "province_id": 75,
      "municipality_name": "Datu Hoffer Ampatuan"
    },
    {
      "municipality_id": 1530,
      "province_id": 75,
      "municipality_name": "Datu Salibo"
    },
    {
      "municipality_id": 1531,
      "province_id": 75,
      "municipality_name": "Shariff Saydona Mustapha"
    },
    {
      "municipality_id": 1532,
      "province_id": 76,
      "municipality_name": "Indanan"
    },
    {
      "municipality_id": 1533,
      "province_id": 76,
      "municipality_name": "Jolo"
    },
    {
      "municipality_id": 1534,
      "province_id": 76,
      "municipality_name": "Kalingalan Caluang"
    },
    {
      "municipality_id": 1535,
      "province_id": 76,
      "municipality_name": "Luuk"
    },
    {
      "municipality_id": 1536,
      "province_id": 76,
      "municipality_name": "Maimbung"
    },
    {
      "municipality_id": 1537,
      "province_id": 76,
      "municipality_name": "Hadji Panglima Tahil"
    },
    {
      "municipality_id": 1538,
      "province_id": 76,
      "municipality_name": "Old Panamao"
    },
    {
      "municipality_id": 1539,
      "province_id": 76,
      "municipality_name": "Pangutaran"
    },
    {
      "municipality_id": 1540,
      "province_id": 76,
      "municipality_name": "Parang"
    },
    {
      "municipality_id": 1541,
      "province_id": 76,
      "municipality_name": "Pata"
    },
    {
      "municipality_id": 1542,
      "province_id": 76,
      "municipality_name": "Patikul"
    },
    {
      "municipality_id": 1543,
      "province_id": 76,
      "municipality_name": "Siasi"
    },
    {
      "municipality_id": 1544,
      "province_id": 76,
      "municipality_name": "Talipao"
    },
    {
      "municipality_id": 1545,
      "province_id": 76,
      "municipality_name": "Tapul"
    },
    {
      "municipality_id": 1546,
      "province_id": 76,
      "municipality_name": "Tongkil"
    },
    {
      "municipality_id": 1547,
      "province_id": 76,
      "municipality_name": "Panglima Estino"
    },
    {
      "municipality_id": 1548,
      "province_id": 76,
      "municipality_name": "Lugus"
    },
    {
      "municipality_id": 1549,
      "province_id": 76,
      "municipality_name": "Pandami"
    },
    {
      "municipality_id": 1550,
      "province_id": 76,
      "municipality_name": "Omar"
    },
    {
      "municipality_id": 1551,
      "province_id": 77,
      "municipality_name": "Panglima Sugala"
    },
    {
      "municipality_id": 1552,
      "province_id": 77,
      "municipality_name": "Bongao (Capital)"
    },
    {
      "municipality_id": 1553,
      "province_id": 77,
      "municipality_name": "Mapun"
    },
    {
      "municipality_id": 1554,
      "province_id": 77,
      "municipality_name": "Simunul"
    },
    {
      "municipality_id": 1555,
      "province_id": 77,
      "municipality_name": "Sitangkai"
    },
    {
      "municipality_id": 1556,
      "province_id": 77,
      "municipality_name": "South Ubian"
    },
    {
      "municipality_id": 1557,
      "province_id": 77,
      "municipality_name": "Tandubas"
    },
    {
      "municipality_id": 1558,
      "province_id": 77,
      "municipality_name": "Turtle Islands"
    },
    {
      "municipality_id": 1559,
      "province_id": 77,
      "municipality_name": "Languyan"
    },
    {
      "municipality_id": 1560,
      "province_id": 77,
      "municipality_name": "Sapa-Sapa"
    },
    {
      "municipality_id": 1561,
      "province_id": 77,
      "municipality_name": "Sibutu"
    },
    {
      "municipality_id": 1562,
      "province_id": 78,
      "municipality_name": "Buenavista"
    },
    {
      "municipality_id": 1563,
      "province_id": 78,
      "municipality_name": "City of Butuan"
    },
    {
      "municipality_id": 1564,
      "province_id": 78,
      "municipality_name": "City of Cabadbaran"
    },
    {
      "municipality_id": 1565,
      "province_id": 78,
      "municipality_name": "Carmen"
    },
    {
      "municipality_id": 1566,
      "province_id": 78,
      "municipality_name": "Jabonga"
    },
    {
      "municipality_id": 1567,
      "province_id": 78,
      "municipality_name": "Kitcharao"
    },
    {
      "municipality_id": 1568,
      "province_id": 78,
      "municipality_name": "Las Nieves"
    },
    {
      "municipality_id": 1569,
      "province_id": 78,
      "municipality_name": "Magallanes"
    },
    {
      "municipality_id": 1570,
      "province_id": 78,
      "municipality_name": "Nasipit"
    },
    {
      "municipality_id": 1571,
      "province_id": 78,
      "municipality_name": "Santiago"
    },
    {
      "municipality_id": 1572,
      "province_id": 78,
      "municipality_name": "Tubay"
    },
    {
      "municipality_id": 1573,
      "province_id": 78,
      "municipality_name": "Remedios T. Romualdez"
    },
    {
      "municipality_id": 1574,
      "province_id": 79,
      "municipality_name": "City of Bayugan"
    },
    {
      "municipality_id": 1575,
      "province_id": 79,
      "municipality_name": "Bunawan"
    },
    {
      "municipality_id": 1576,
      "province_id": 79,
      "municipality_name": "Esperanza"
    },
    {
      "municipality_id": 1577,
      "province_id": 79,
      "municipality_name": "La Paz"
    },
    {
      "municipality_id": 1578,
      "province_id": 79,
      "municipality_name": "Loreto"
    },
    {
      "municipality_id": 1579,
      "province_id": 79,
      "municipality_name": "Prosperidad"
    },
    {
      "municipality_id": 1580,
      "province_id": 79,
      "municipality_name": "Rosario"
    },
    {
      "municipality_id": 1581,
      "province_id": 79,
      "municipality_name": "San Francisco"
    },
    {
      "municipality_id": 1582,
      "province_id": 79,
      "municipality_name": "San Luis"
    },
    {
      "municipality_id": 1583,
      "province_id": 79,
      "municipality_name": "Santa Josefa"
    },
    {
      "municipality_id": 1584,
      "province_id": 79,
      "municipality_name": "Talacogon"
    },
    {
      "municipality_id": 1585,
      "province_id": 79,
      "municipality_name": "Trento"
    },
    {
      "municipality_id": 1586,
      "province_id": 79,
      "municipality_name": "Veruela"
    },
    {
      "municipality_id": 1587,
      "province_id": 79,
      "municipality_name": "Sibagat"
    },
    {
      "municipality_id": 1588,
      "province_id": 80,
      "municipality_name": "Alegria"
    },
    {
      "municipality_id": 1589,
      "province_id": 80,
      "municipality_name": "Bacuag"
    },
    {
      "municipality_id": 1590,
      "province_id": 80,
      "municipality_name": "Burgos"
    },
    {
      "municipality_id": 1591,
      "province_id": 80,
      "municipality_name": "Claver"
    },
    {
      "municipality_id": 1592,
      "province_id": 80,
      "municipality_name": "Dapa"
    },
    {
      "municipality_id": 1593,
      "province_id": 80,
      "municipality_name": "Del Carmen"
    },
    {
      "municipality_id": 1594,
      "province_id": 80,
      "municipality_name": "General Luna"
    },
    {
      "municipality_id": 1595,
      "province_id": 80,
      "municipality_name": "Gigaquit"
    },
    {
      "municipality_id": 1596,
      "province_id": 80,
      "municipality_name": "Mainit"
    },
    {
      "municipality_id": 1597,
      "province_id": 80,
      "municipality_name": "Malimono"
    },
    {
      "municipality_id": 1598,
      "province_id": 80,
      "municipality_name": "Pilar"
    },
    {
      "municipality_id": 1599,
      "province_id": 80,
      "municipality_name": "Placer"
    },
    {
      "municipality_id": 1600,
      "province_id": 80,
      "municipality_name": "San Benito"
    },
    {
      "municipality_id": 1601,
      "province_id": 80,
      "municipality_name": "San Francisco"
    },
    {
      "municipality_id": 1602,
      "province_id": 80,
      "municipality_name": "San Isidro"
    },
    {
      "municipality_id": 1603,
      "province_id": 80,
      "municipality_name": "Santa Monica"
    },
    {
      "municipality_id": 1604,
      "province_id": 80,
      "municipality_name": "Sison"
    },
    {
      "municipality_id": 1605,
      "province_id": 80,
      "municipality_name": "Socorro"
    },
    {
      "municipality_id": 1606,
      "province_id": 80,
      "municipality_name": "City of Surigao"
    },
    {
      "municipality_id": 1607,
      "province_id": 80,
      "municipality_name": "Tagana-An"
    },
    {
      "municipality_id": 1608,
      "province_id": 80,
      "municipality_name": "Tubod"
    },
    {
      "municipality_id": 1609,
      "province_id": 81,
      "municipality_name": "Barobo"
    },
    {
      "municipality_id": 1610,
      "province_id": 81,
      "municipality_name": "Bayabas"
    },
    {
      "municipality_id": 1611,
      "province_id": 81,
      "municipality_name": "City of Bislig"
    },
    {
      "municipality_id": 1612,
      "province_id": 81,
      "municipality_name": "Cagwait"
    },
    {
      "municipality_id": 1613,
      "province_id": 81,
      "municipality_name": "Cantilan"
    },
    {
      "municipality_id": 1614,
      "province_id": 81,
      "municipality_name": "Carmen"
    },
    {
      "municipality_id": 1615,
      "province_id": 81,
      "municipality_name": "Carrascal"
    },
    {
      "municipality_id": 1616,
      "province_id": 81,
      "municipality_name": "Cortes"
    },
    {
      "municipality_id": 1617,
      "province_id": 81,
      "municipality_name": "Hinatuan"
    },
    {
      "municipality_id": 1618,
      "province_id": 81,
      "municipality_name": "Lanuza"
    },
    {
      "municipality_id": 1619,
      "province_id": 81,
      "municipality_name": "Lianga"
    },
    {
      "municipality_id": 1620,
      "province_id": 81,
      "municipality_name": "Lingig"
    },
    {
      "municipality_id": 1621,
      "province_id": 81,
      "municipality_name": "Madrid"
    },
    {
      "municipality_id": 1622,
      "province_id": 81,
      "municipality_name": "Marihatag"
    },
    {
      "municipality_id": 1623,
      "province_id": 81,
      "municipality_name": "San Agustin"
    },
    {
      "municipality_id": 1624,
      "province_id": 81,
      "municipality_name": "San Miguel"
    },
    {
      "municipality_id": 1625,
      "province_id": 81,
      "municipality_name": "Tagbina"
    },
    {
      "municipality_id": 1626,
      "province_id": 81,
      "municipality_name": "Tago"
    },
    {
      "municipality_id": 1627,
      "province_id": 81,
      "municipality_name": "City of Tandag"
    },
    {
      "municipality_id": 1628,
      "province_id": 82,
      "municipality_name": "Basilisa"
    },
    {
      "municipality_id": 1629,
      "province_id": 82,
      "municipality_name": "Cagdianao"
    },
    {
      "municipality_id": 1630,
      "province_id": 82,
      "municipality_name": "Dinagat"
    },
    {
      "municipality_id": 1631,
      "province_id": 82,
      "municipality_name": "Libjo"
    },
    {
      "municipality_id": 1632,
      "province_id": 82,
      "municipality_name": "Loreto"
    },
    {
      "municipality_id": 1633,
      "province_id": 82,
      "municipality_name": "San Jose"
    },
    {
      "municipality_id": 1634,
      "province_id": 82,
      "municipality_name": "Tubajon"
    }
];

function toggleSubMenu(button){
  if (!$('#sidebar').hasClass('expand')) {
    $('#sidebar').addClass('expand')
  }
  button.nextElementSibling.classList.toggle('showdropdown');
  button.classList.toggle('rotate');
}

function logoutDeleteStorageTokens(){
  // localStorage.removeItem('api_token');
  // localStorage.removeItem('user');

  window.location.href = globalApi;
}
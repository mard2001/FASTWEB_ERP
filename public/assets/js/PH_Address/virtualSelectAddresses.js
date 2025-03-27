function loadJSON(url) {
    return fetch(url).then(response => {
        if (!response.ok) {
            throw new Error(`Failed to load ${url}`);
        }
        return response.json();
    });
}

PHAddressInit(['Region', 'Province', 'CityMunicipality', 'Barangay']);


export function PHAddressInit(InputElements) {

    Promise.all([
        loadJSON('./assets/js/PH_Address/region.json'),
        loadJSON('./assets/js/PH_Address/province.json'),
        loadJSON('./assets/js/PH_Address/city.json'),
        loadJSON('./assets/js/PH_Address/barangay.json')
    ])
        .then(([region, province, city, barangay]) => {

            const AddressData = {
                region: region,
                province: province,
                city: city,
                barangay: barangay
            };

            InitElements(InputElements, AddressData);

        })
        .catch(error => {
            console.error("Error loading JSON:", error);
        });

    function InitElements(InputElements, AddressData) {
        const regionElem = '#' + InputElements[0];
        const provinceElem = '#' + InputElements[1];
        const cityElem = '#' + InputElements[2];
        const barangayElem = '#' + InputElements[3];

        initProvince(null);
        initCity(null);
        initBarangay(null);
        initRegion();

        let provinceListenInit, cityListenInit, brgyListenInit;


        function initRegion() {

            // Initialize VirtualSelect
            initVS(regionElem, restructure(AddressData.region, 'region_name', 'region_code'));


            /** in vanilla javascript */
            document.querySelector(regionElem).addEventListener('change', function () {

                const selectedOption = AddressData.region.find(region => region.region_code === this.value);
                const populateProvince = AddressData.province.filter(provnice => provnice.region_code === selectedOption.region_code);

                initProvince(restructure(populateProvince, 'province_name', 'province_code'));
                this.value = selectedOption.region_name;

                initCity(null);
                initBarangay(null);

                autoCompleteAddress();

            });

        }


        function initProvince(province) {

            if (document.querySelector(provinceElem)?.virtualSelect) {
                document.querySelector(provinceElem).destroy();
            }

            initVS(provinceElem, province);


            if (province) {

                document.querySelector(provinceElem).enable();

                if (!provinceListenInit) {
                    /** in vanilla javascript */
                    document.querySelector(provinceElem).addEventListener('change', function () {

                        const selectedOption = AddressData.province.find(province => province.province_code === this.value);
                        const populateCities = AddressData.city.filter(city => city.province_code === selectedOption.province_code);

                        initCity(restructure(populateCities, 'city_name', 'city_code'));
                        this.value = selectedOption.province_name;

                        initBarangay(null);

                        autoCompleteAddress();


                    });

                    provinceListenInit = true;

                }

                return true;

            } else {

                document.querySelector(provinceElem).disable();


            }

        }

        function initCity(cities) {

            if (document.querySelector(cityElem)?.virtualSelect) {
                document.querySelector(cityElem).destroy();
            }

            initVS(cityElem, cities);


            if (cities) {


                document.querySelector(cityElem).enable();

                if (!cityListenInit) {
                    /** in vanilla javascript */
                    document.querySelector(cityElem).addEventListener('change', function () {

                        const selectedOption = AddressData.city.find(city => city.city_code === this.value);
                        const populateBarangay = AddressData.barangay.filter(barangay => barangay.city_code === selectedOption.city_code);

                        initBarangay(restructure(populateBarangay, 'brgy_name', 'brgy_code'));
                        this.value = selectedOption.city_name;

                        autoCompleteAddress();

                    });

                    cityListenInit = true;
                }


            } else {

                document.querySelector(cityElem).disable();


            }



        }

        function initBarangay(barangay) {

            if (document.querySelector(barangayElem)?.virtualSelect) {
                document.querySelector(barangayElem).destroy();
            }

            initVS(barangayElem, barangay);

            if (barangay) {


                document.querySelector(barangayElem).enable();

                if (!brgyListenInit) {
                    /** in vanilla javascript */
                    document.querySelector(barangayElem).addEventListener('change', function () {


                        const selectedOption = AddressData.barangay.find(brgy => brgy.brgy_code === this.value);
                        this.value = selectedOption.brgy_name;
                        autoCompleteAddress();

                    });
                }

                brgyListenInit = true;


            } else {

                document.querySelector(barangayElem).disable();


            }



        }


        function autoCompleteAddress() {

            // console.log(barangayElem);

            // console.log(`${$(barangayElem).val()}`);

             const completeAddress = `${$(barangayElem).val() && $(barangayElem).val() + ', '}${$(cityElem).val() && $(cityElem).val() + ', '}${$(provinceElem).val() && $(provinceElem).val() + ', '}${$(regionElem).val() && $(regionElem).val()}`;
            $('#NVCompleteAddress').val(completeAddress);
        }

    }


    function restructure(data, labelKey, valueKey) {
        return data.map(item => ({
            label: item[labelKey],
            value: item[valueKey],
        }));
    }

    function initVS(elem, data) {
        // Initialize VirtualSelect
        VirtualSelect.init({
            ele: elem,             // Attach to the element
            options: data,                 // Provide options
            maxWidth: '100%',                 // Set maxWidth
            multiple: false,                   // Enable multiselect
            hideClearButton: true,            // Hide clear button
            search: true
        });
    }



}

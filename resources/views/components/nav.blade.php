<aside id="sidebar">
    <div class="d-flex mt-2 btn-toggle">
        <button class="toggle-btn btn-toggle" type="button">
            <i class="fa-solid fa-bars"></i>
        </button>
        <div class="sidebar-logo">
            <img src="{{ asset('assets/resources/ERP_header3t.png') }}" alt="FASET ERP" style="height: 65px;">

            {{-- <a href="#">FAST ERP </a> --}}
        </div>
    </div>
    <ul class="sidebar-nav">
        <li class="">
            <button class="dropdown-btn d-flex align-items-center" onclick="toggleSubMenu(this)">
                <span class="mdi mdi-package-variant"></span>
                <span>Master Data</span>
                <span class="mdi mdi-menu-down-outline ms-auto"></span>
            </button>
            <ul class="sub-menu">
                <div>
                    <li class="">
                        <a href="{{ route('customer') }}" class="sidebar-link">
                            <span class="mdi mdi-ray-start-arrow"></span>
                            <span class="ms-2 px-1">Customers</span>
                        </a>
                    </li>
                    <li class="">
                        <a href="{{ route('product') }}" class="sidebar-link">
                            <span class="mdi mdi-ray-start-arrow"></span>
                            <span class="ms-2 px-1">Products </span>
                        </a>
                    </li>
                    <li class="">
                        <a href="{{ route('salesman') }}" class="sidebar-link">
                            <span class="mdi mdi-ray-start-arrow"></span>
                            <span class="ms-2 px-1">Salesmans</span>
                        </a>
                    </li>
                    <li class="">
                        <a href="{{ route('supplier') }}" class="sidebar-link">
                            <span class="mdi mdi-ray-start-arrow"></span>
                            <span class="ms-2 px-1">Suppliers</span>
                        </a>
                    </li>
                    <li class="">
                        <a href="{{ route('warehouse') }}" class="sidebar-link">
                            <span class="mdi mdi-ray-start-arrow"></span>
                            <span class="ms-2 px-1">Warehouse</span>
                        </a>
                    </li>
                </div>
            </ul>
        </li>
        <li class="">
            <button class="dropdown-btn d-flex align-items-center" onclick="toggleSubMenu(this)">
                <span class="mdi mdi-notebook-edit-outline"></span>
                <span>Inventory</span>
                <span class="mdi mdi-menu-down-outline ms-auto"></span>
            </button>
            <ul class="sub-menu">
                <div>
                    <li class="">
                        <a href="{{ route('invWarehouse') }}" class="sidebar-link">
                            <span class="mdi mdi-ray-start-arrow"></span>
                            <span class="ms-2 px-1">Inventory Warehouse</span>
                        </a>
                    </li>
                    <li class="">
                        <a href="{{ route('invMovements') }}" class="sidebar-link">
                            <span class="mdi mdi-ray-start-arrow"></span>
                            <span class="ms-2 px-1">Inventory Movements</span>
                        </a>
                    </li>
                    <li class="">
                        <a href="{{ route('stockadjustment') }}" class="sidebar-link">
                            <span class="mdi mdi-ray-start-arrow"></span>
                            <span class="ms-2 px-1">Stock Adjustment</span>
                        </a>
                    </li>
                    <li class="">
                        <a href="{{ route('stockcount') }}" class="sidebar-link">
                            <span class="mdi mdi-ray-start-arrow"></span>
                            <span class="ms-2 px-1">Stock Count</span>
                        </a>
                    </li>
                    <li class="">
                        <a href="{{ route('invStockTransfer') }}" class="sidebar-link">
                            <span class="mdi mdi-ray-start-arrow"></span>
                            <span class="ms-2 px-1">Stock Transfer</span>
                        </a>
                    </li>
                    <li class="">
                        <a href="{{ route('invWarehouseMovements') }}" class="sidebar-link">
                            <span class="mdi mdi-ray-start-arrow"></span>
                            <span class="ms-2 px-1">Warehouse Movements</span>
                        </a>
                    </li>
                    
                </div>
            </ul>
        </li>
        <li class="">
            <button class="dropdown-btn d-flex align-items-center" onclick="toggleSubMenu(this)">
                <span class="mdi mdi-truck-fast-outline"></span>
                <span>Transactions</span>
                <span class="mdi mdi-menu-down-outline ms-auto"></span>
            </button>
            <ul class="sub-menu">
                <div>
                    <li class="">
                        <a href="{{ route('purchase-order') }}" class="sidebar-link">
                            <span class="mdi mdi-ray-start-arrow"></span>
                            <span class="ms-2 px-1">Purchase Order</span>
                        </a>
                    </li>
                    <li class="">
                        <a href="{{ route('receiving-report') }}" class="sidebar-link">
                            <span class="mdi mdi-ray-start-arrow"></span>
                            <span class="ms-2 px-1">Receiving Report</span>
                        </a>
                    </li>
                    <li class="">
                        <a href="{{ route('sales-order') }}" class="sidebar-link">
                            <span class="mdi mdi-ray-start-arrow"></span>
                            <span class="ms-2 px-1">Sales Order</span>
                        </a>
                    </li>
                </div>
            </ul>
        </li>

        <hr class="text-white">
        
        <li class="">
            <button class="dropdown-btn d-flex align-items-center" onclick="toggleSubMenu(this)">
                <span class="mdi mdi-cog-transfer"></span>
                <span>Settings</span>
                <span class="mdi mdi-menu-down-outline ms-auto"></span>
            </button>
            <ul class="sub-menu">
                <div>
                    <li class="">
                        <a href="{{ route('dbconfig') }}" class="sidebar-link">
                            <span class="mdi mdi-ray-start-arrow"></span>
                            <span class="ms-2 px-1">Database Connection</span>
                        </a>
                    </li>
                </div>
            </ul>
        </li>
    </ul>
</aside>


{{-- <aside id="sidebar">
    <div class="d-flex mt-2 btn-toggle">
        <button class="toggle-btn btn-toggle" type="button">
            <i class="fa-solid fa-bars"></i>
        </button>
        <div class="sidebar-logo">
            <img src="{{ asset('assets/resources/TempLogoIcon.png') }}" alt="FASET ERP" style="height: 50px; width: 50px;">

            <a href="#">FAST ERP </a>
        </div>
    </div>
    <ul class="sidebar-nav">
        <li class="sidebar-item">
            <a href="{{ route('product') }}" class="sidebar-link">
                <i class="svgFill">
                    <svg id="Icon" enable-background="new 0 0 96 96" height="512" viewBox="0 0 96 96" width="512" xmlns="http://www.w3.org/2000/svg">
                        <path id="Product_Package" d="m90.895 25.211-42-21c-.563-.281-1.227-.281-1.789 0l-42 21c-.678.339-1.106 1.031-1.106 1.789v42c0 .758.428 1.45 1.105 1.789l42 21c.282.141.588.211.895.211s.613-.07.895-.211l42-21c.677-.339 1.105-1.031 1.105-1.789v-42c0-.758-.428-1.45-1.105-1.789zm-42.895-16.975 37.528 18.764-8.028 4.014-37.527-18.764zm13.5 30.778-37.528-18.764 11.528-5.764 37.528 18.764zm1.5 3.722 12-6v14.877l-3.838-2.741c-.435-.311-.979-.434-1.506-.343-.527.093-.996.392-1.301.832l-5.355 7.737zm-43.5-20.25 37.527 18.764-9.027 4.514-37.528-18.764zm-11.5 7.75 38 19v37.527l-38-19zm42 56.528v-37.528l9-4.5v18.764c0 .875.568 1.648 1.403 1.909.198.062.398.091.597.091.644 0 1.264-.312 1.645-.861l7.845-11.331 5.349 3.82c.61.435 1.412.494 2.077.15.665-.342 1.084-1.029 1.084-1.778v-20.764l9-4.5v37.527z" />
                    </svg>
                </i>

                <span class="ms-2 px-1">Products</span>
            </a>
        </li>
        <li class="sidebar-item">
            <a href="{{ route('salesman') }}" class="sidebar-link">
                <i class="svgFill">
                    <!-- Generator: Adobe Illustrator 19.0.0, SVG Export Plug-In . SVG Version: 6.00 Build 0)  -->
                    <svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
                        viewBox="0 0 344.445 344.445" xml:space="preserve">
                        <path d="M254.557,221.867l-43.444-9.656v-13.728c16.211-11.7,26.944-30.472,27.661-51.778
                            c14.383-15.178,22.339-34.911,22.339-55.911V66.667c0-14.072-10.517-25.733-24.106-27.539C225.357,15.25,201.129,0,174.357,0
                            h-9.35c-18.072,0-35.878,6.111-50.144,17.206c-20.04,15.583-31.529,39.083-31.529,64.466V90.8
                            c0,20.994,7.956,40.733,22.339,55.911c0.722,21.306,11.45,40.078,27.661,51.778v13.728l-43.444,9.65
                            c-33.3,7.4-56.556,36.383-56.556,70.5v52.078h116.183h45.411h116.183v-52.078C311.112,258.25,287.857,229.267,254.557,221.867z
                            M215.984,224.672l-8.478,38.144l-26.2-18.711l25.711-21.428L215.984,224.672z M172.223,237.211l-27.778-23.15v-9.089
                            c8.467,3.9,17.856,6.139,27.778,6.139s19.311-2.239,27.778-6.139v9.089L172.223,237.211z M105.556,94.445v34.239
                            c-7.206-11.211-11.111-24.239-11.111-37.889v-9.123c0-21.928,9.928-42.228,27.239-55.694
                            c12.322-9.589,27.711-14.867,43.322-14.867h9.35c23.478,0,44.622,13.944,53.872,35.522L229.673,50h3.661
                            c9.189,0,16.667,7.478,16.667,16.667v24.128c0,13.65-3.906,26.678-11.111,37.889v-45.35h-7.75
                            c-10.683,0-20.594-5.306-26.517-14.194l-1.65-2.472h-11.678l-1.617,2.694c-9.278,15.472-26.256,25.083-44.294,25.083H105.556z
                            M116.668,144.444v-38.889h28.717c20.756,0,40.356-10.472,51.956-27.544c7.428,9.472,18.439,15.372,30.439,16.306v50.128
                            c0,30.633-24.922,55.556-55.556,55.556C141.59,200.001,116.668,175.078,116.668,144.444z M137.423,222.683l25.711,21.428
                            l-26.2,18.711l-8.478-38.144L137.423,222.683z M150.445,333.333h-50.444v-22.222H88.89v22.222H44.445v-40.967
                            c0-28.861,19.678-53.394,47.85-59.65l25.317-5.628l12.122,54.539l19.206-13.722l6.422,6.422L150.445,333.333z M182.851,333.333
                            h-21.256l5.267-63.217l-8.761-8.761l14.122-10.083l14.122,10.089l-8.761,8.761L182.851,333.333z M300.001,333.333h-44.444v-22.222
                            h-11.111v22.222h-50.445l-4.917-59.006l6.422-6.422l19.206,13.722l12.122-54.539l25.317,5.628
                            c28.172,6.256,47.85,30.789,47.85,59.65V333.333z" />
                    </svg>
                </i>
                <span class="ms-2 px-1">Salesman</span>
            </a>
        </li>
        <li class="sidebar-item">
            <a href="{{ route('customer') }}" class="sidebar-link">
                <i>
                    <svg xmlns="http://www.w3.org/2000/svg" class="hover-effect"
                        xmlns:svg="http://www.w3.org/2000/svg" version="1.1" id="svg633" viewBox="0 0 682.66669 682.66669">
                        <defs id="defs637">
                            <clipPath clipPathUnits="userSpaceOnUse" id="clipPath647">
                                <path d="M 0,512 H 512 V 0 H 0 Z" id="path645" />
                            </clipPath>
                        </defs>
                        <mask id="custom">
                            <rect id="bg" x="0" y="0" width="100%" height="100%" fill="white" />
                            <g transform="matrix(1.3333333,0,0,-1.3333333,0,682.66667)">
                                <path transform="matrix(1,0,0,1,256,116)" d="m 0,0 c 5.52,0 10,-4.48 10,-10 0,-5.52 -4.48,-10 -10,-10 -5.52,0 -10,4.48 -10,10 0,5.52 4.48,10 10,10" style="fill:#ffffff;fill-opacity:1;fill-rule:nonzero;stroke:none" id="path651" />
                            </g>
                        </mask>
                        <g mask="url(#custom)">
                            <g id="g639" transform="matrix(1.3333333,0,0,-1.3333333,0,682.66667)">
                                <g id="g641">
                                    <g id="g643" clip-path="url(#clipPath647)" class="stroke">
                                        <g id="g649" transform="translate(256,116)">
                                            <path d="m 0,0 c 5.52,0 10,-4.48 10,-10 0,-5.52 -4.48,-10 -10,-10 -5.52,0 -10,4.48 -10,10 0,5.52 4.48,10 10,10" style="fill:none;stroke:#ffffff;stroke-width:2;stroke-linecap:round;stroke-linejoin:round;" id="path651" />
                                        </g>
                                        <g id="g653" transform="translate(469,196)">
                                            <path d="m 0,0 c 0,-27.61 -22.39,-50 -50,-50 -27.61,0 -50,22.39 -50,50 0,27.61 22.39,50 50,50 C -22.39,50 0,27.61 0,0 Z" style="fill:none;stroke:#ffffff;stroke-width:20;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:10;" id="path655" />
                                        </g>
                                        <g id="g657" transform="translate(146,26)">
                                            <path d="m 0,0 h -136 v 20 c 0,44.18 38.82,80 83,80 27.01,0 53.89,-13.38 68.37,-33.88" style="fill:none;stroke:#ffffff;stroke-width:20;stroke-linecap:round;stroke-linejoin:round;" id="path659" />
                                        </g>
                                        <g id="g661" transform="translate(350.6299,92.1201)">
                                            <path d="m 0,0 c 14.48,20.5 41.36,33.88 68.37,33.88 44.18,0 83,-35.82 83,-80 v -20 h -136" style="fill:none;stroke:#ffffff;stroke-width:20;stroke-linecap:round;stroke-linejoin:round;" id="path663" />
                                        </g>
                                        <g id="g665" transform="translate(143,196)">
                                            <path d="m 0,0 c 0,-27.61 -22.39,-50 -50,-50 -27.61,0 -50,22.39 -50,50 0,27.61 22.39,50 50,50 C -22.39,50 0,27.61 0,0 Z" style="fill:none;stroke:#ffffff;stroke-width:20;stroke-linecap:round;stroke-linejoin:round;" id="path667" />
                                        </g>
                                        <g id="g669" transform="translate(326,236)">
                                            <path d="m 0,0 c 0,-38.66 -31.34,-70 -70,-70 -38.66,0 -70,31.34 -70,70 0,38.66 31.34,70 70,70 C -31.34,70 0,38.66 0,0 Z" style="fill:none;stroke:#ffffff;stroke-width:20;stroke-linecap:round;stroke-linejoin:round;" id="path671" />
                                        </g>
                                        <g id="g673" transform="translate(255.6396,486)">
                                            <path d="m 0,0 -21.63,-44.23 -48.36,-7.02 35,-34.11 -8.26,-48.17 43.25,22.74 c 16.25,-8.54 27.471,-14.44 43.261,-22.74 l -8.271,48.17 35,34.11 -48.36,7.02 z" style="fill:none;stroke:#ffffff;stroke-width:20;stroke-linecap:round;stroke-linejoin:round;" id="path675" />
                                        </g>
                                        <g id="g677" transform="translate(425.2803,317.4102)">
                                            <path d="M 0,0 30.79,-16.18 24.91,18.1 49.819,42.39 15.39,47.39 0,78.59 l -15.4,-31.2 -34.43,-5 24.91,-24.29 -5.88,-34.28 z" style="fill:none;stroke:#ffffff;stroke-width:20;stroke-linecap:round;stroke-linejoin:round;" id="path679" />
                                        </g>
                                        <g id="g681" transform="translate(85.2798,317.4102)">
                                            <path d="M 0,0 30.79,-16.18 24.91,18.1 49.82,42.39 15.39,47.39 0,78.59 -15.4,47.39 -49.83,42.39 -24.92,18.1 -30.8,-16.18 Z" style="fill:none;stroke:#ffffff;stroke-width:20;stroke-linecap:round;stroke-linejoin:round;" id="path683" />
                                        </g>
                                        <g id="g685" transform="translate(366,26)">
                                            <path d="m 0,0 h -220 v 10 c 0,20.5 5.61,39.69 15.37,56.12 19.18,32.26 54.38,53.88 94.63,53.88 30.38,0 57.88,-12.31 77.78,-32.22 6.47,-6.47 12.14,-13.74 16.85,-21.66 C -5.61,49.69 0,30.5 0,10 Z" style="fill:none;stroke:#ffffff;stroke-width:20;stroke-linecap:round;stroke-linejoin:round;" id="path687" />
                                        </g>
                                        <g id="g689" transform="translate(294.3301,94.5498)">
                                            <path d="M 0,0 C 10.7,-7.03 19.37,-16.91 24.91,-28.55" style="fill:none;stroke:#ffffff;stroke-width:20;stroke-linecap:round;stroke-linejoin:round;" id="path691" />
                                        </g>
                                    </g>
                                </g>
                            </g>
                        </g>
                    </svg>
                </i>
                <span class="ms-2 px-1">Customer</span>
            </a>
        </li>
        <li class="sidebar-item">
            <a href="{{ route('inventory') }}" class="sidebar-link d-flex">
                <i>
                    <svg id="Layer_1" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg" data-name="Layer 1">
                        <g fill="none" stroke-width="14">
                            <path d="m126.587 42.406h31.5a33.142 33.142 0 0 1 64.89 0h31.495a6.388 6.388 0 0 1 6.388 6.387v6.388h59.423a41.816 41.816 0 0 1 41.711 41.71v113.42l36.864 10.347 3.6-12.837a17.469 17.469 0 0 1 4.179-7.341 17.728 17.728 0 0 1 6.932-4.49l54.631-24.354a17.574 17.574 0 1 1 14.317 32.1l-48.448 21.6-55.109 196.336a42.375 42.375 0 0 1 -38.374 72.984h-.043l-.889-.249a42.247 42.247 0 0 1 -25.5-20.048q-.513-.913-.981-1.851h-252.396a41.816 41.816 0 0 1 -41.711-41.708v-333.909a41.816 41.816 0 0 1 41.711-41.71h59.423v-6.388a6.388 6.388 0 0 1 6.387-6.387z" />
                            <path d="m163.249 282.758a6.388 6.388 0 0 1 0-12.776h79.933l4.11-14.646h-84.043a6.388 6.388 0 0 1 0-12.776h87.629l13.422-47.82a11.419 11.419 0 0 1 14.051-7.91l43.29 12.15.022.006.022.006 2.8.785v-102.896a4.244 4.244 0 0 0 -4.214-4.213h-59.426v6.332a6.388 6.388 0 0 1 -6.388 6.388h-127.88a6.388 6.388 0 0 1 -6.388-6.388v-6.332h-59.422a4.244 4.244 0 0 0 -4.214 4.213v333.905a4.244 4.244 0 0 0 4.214 4.214h169.769l-32.205-9.039a17.583 17.583 0 0 1 9.5-33.858l1 .28 5.169-18.428h-50.751a6.388 6.388 0 0 1 0-12.776h54.337l4.111-14.647h-58.448a6.388 6.388 0 0 1 0-12.776h62.033l14.314-51zm0-118.618a6.388 6.388 0 0 1 0-12.776h115.3a6.388 6.388 0 0 1 0 12.776zm0 27.422a6.388 6.388 0 0 1 0-12.776h85.93a6.388 6.388 0 0 1 0 12.776zm-31.295 139.431 3.544-3.866a6.358 6.358 0 1 1 9.382 8.583l-4.713 5.142a32.592 32.592 0 1 1 -8.213-9.859zm0-91.2 3.544-3.867a6.358 6.358 0 1 1 9.382 8.584l-4.713 5.142a32.592 32.592 0 1 1 -8.213-9.859zm0-91.2 3.544-3.866a6.358 6.358 0 1 1 9.382 8.583l-4.713 5.142a32.592 32.592 0 1 1 -8.213-9.859z" />
                            <path d="m313.22 319.479-17.384-4.879-6.547 23.329 5.625-3.069a6.364 6.364 0 0 1 8.551 2.4h.007l3.2 5.541z" />
                            <path d="m359.1 425.084a29.629 29.629 0 0 0 -36.517 20.492l-.006.022a29.629 29.629 0 0 0 20.491 36.516l.022.006a29.62 29.62 0 0 0 16.01-57.036zm-4.073 24.586a5.562 5.562 0 1 1 -3.932-1.628 5.543 5.543 0 0 1 3.932 1.628zm-3.932-14.4a18.338 18.338 0 1 1 -12.965 5.371 18.276 18.276 0 0 1 12.963-5.375z" />
                            <!-- Additional paths omitted for brevity -->
                        </g>
                    </svg>
                </i>
                <span class="ms-2 px-1">Inventory</span>
            </a>
        </li>

        <hr class="text-white">

        <li class="sidebar-item *:">
            <a href="{{ route('picklist') }}" class="sidebar-link d-flex">
                <i class="svgFill fa-regular fa-file-lines ms-1" style="font-size: 22px; -webkit-text-stroke: -1px white;">
                </i>
                <span class="ms-2 px-1">Pick List</span>

            </a>
        </li>
        <li class="sidebar-item">
            <a href="{{ route('pamasterlist') }}" class="sidebar-link d-flex">
                <i class="svgFill fa-regular fa-file-lines ms-1" style="font-size: 22px; -webkit-text-stroke: -1px white;">
                </i>
                <span class="ms-2 px-1">PA Masterlist</span>

            </a>
        </li>
        <li class="sidebar-item">
            <a href="{{ route('patarget') }}" class="sidebar-link d-flex">
                <i class="svgFill fa-regular fa-file-lines ms-1" style="font-size: 22px; -webkit-text-stroke: -1px white;">
                </i>
                <span class="ms-2 px-1">PA Target</span>

            </a>
        </li>

        <hr class="text-white">

        <li class="sidebar-item *:">
            <a href="{{ route('invoices') }}" class="sidebar-link d-flex">
                <i class="svgFill fa-solid fa-file-pdf ms-1" style="font-size: 22px; -webkit-text-stroke: -1px white;">
                </i>
                <span class="ms-2 px-1">Invoices</span>

            </a>
        </li>
        <li class="sidebar-item">
            <a href="{{ route('purchase-order') }}" class="sidebar-link d-flex">
                <i class="svgFill fa-solid fa-file-pdf ms-1" style="font-size: 22px; -webkit-text-stroke: -1px white;">
                </i>
                <span class="ms-2 px-1">Purchase Order</span>

            </a>
        </li>
        <li class="sidebar-item">
            <a href="{{ route('receiving-report') }}" class="sidebar-link d-flex">
                <i class="svgFill fa-solid fa-file-pdf ms-1" style="font-size: 22px; -webkit-text-stroke: -1px white;">
                </i>
                <span class="ms-2 px-1">Receiving Report</span>

            </a>
        </li>
        <li class="sidebar-item">
            <a href="{{ route('sales-order') }}" class="sidebar-link d-flex">
                <i class="svgFill fa-solid fa-file-pdf ms-1" style="font-size: 22px; -webkit-text-stroke: -1px white;">
                </i>
                <span class="ms-2 px-1">Sales Order</span>

            </a>
        </li>

        <hr class="text-white">

        <li class="sidebar-item">
            <a href="{{ route('dbconfig') }}" class="sidebar-link d-flex" id="dbset">
                <i>
                    <svg viewBox="0 0 64 64" xmlns="http://www.w3.org/2000/svg">
                        <g style="fill:none;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:10;stroke-width:2.5">
                            <path d="m18 19h10" />
                            <path d="m40 19h6" stroke-dasharray="0 3" />
                            <path d="m18 32h10" />
                            <path d="m40 32h6" stroke-dasharray="0 3" />
                            <path d="m18 46h10" />
                            <path d="m34 53h-19c-2.2 0-4-1.8-4-4v-33c0-2.2 1.8-4 4-4h34c2.2 0 4 1.8 4 4v17" />
                            <path d="m11 25h42" />
                            <path d="m11 39h23" />
                            <path d="m55.2 52.5c.5-.6.9-1.3 1.2-2.1s.6-1.5.7-2.3l-1.9-.9c.1-.6.1-1.2.1-1.8l2-.7c-.2-1.6-.8-3.2-1.7-4.5l-2 .7c-.4-.5-.8-.9-1.2-1.3l.9-1.9c-.6-.5-1.3-.9-2.1-1.2s-1.5-.6-2.3-.7l-.9 1.9c-.6-.1-1.2-.1-1.8-.1l-.7-2c-1.6.2-3.2.8-4.5 1.7l.7 2c-.5.4-.9.8-1.3 1.2l-1.9-.9c-.5.6-.9 1.3-1.2 2.1s-.6 1.5-.7 2.3l1.9.9c-.1.6-.1 1.2-.1 1.8l-2 .7c.2 1.6.8 3.2 1.7 4.5l2-.7c.4.5.8.9 1.2 1.3l-.9 1.9c.6.5 1.3.9 2.1 1.2s1.5.6 2.3.7l.9-1.9c.6.1 1.2.1 1.8.1l.7 2c1.6-.2 3.2-.8 4.5-1.7l-.7-2c.5-.4.9-.8 1.3-1.2z" />
                            <circle cx="46.9" cy="46.1" r="5.2" />
                        </g>
                    </svg>
                </i>
                <span class="ms-2 px-1">Settings</span>

            </a>
        </li>
    </ul>
</aside> --}}



<aside id="sidebar">
    <div class="d-flex mt-2 btn-toggle">
        <button class="toggle-btn btn-toggle" type="button">
            <i class="fa-solid fa-bars"></i>
        </button>
        <div class="sidebar-logo">
            <img src="<?php echo e(asset('assets/resources/TempLogoIcon.png')); ?>" alt="FASET ERP" style="height: 50px; width: 50px;">

            <a href="#">FAST ERP </a>
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
                        <a href="<?php echo e(route('customer')); ?>" class="sidebar-link">
                            <span class="mdi mdi-ray-start-arrow"></span>
                            <span class="ms-2 px-1">Customers</span>
                        </a>
                    </li>
                    <li class="">
                        <a href="<?php echo e(route('product')); ?>" class="sidebar-link">
                            <span class="mdi mdi-ray-start-arrow"></span>
                            <span class="ms-2 px-1">Products </span>
                        </a>
                    </li>
                    <li class="">
                        <a href="<?php echo e(route('salesman')); ?>" class="sidebar-link">
                            <span class="mdi mdi-ray-start-arrow"></span>
                            <span class="ms-2 px-1">Salesmans</span>
                        </a>
                    </li>
                    <li class="">
                        <a href="<?php echo e(route('supplier')); ?>" class="sidebar-link">
                            <span class="mdi mdi-ray-start-arrow"></span>
                            <span class="ms-2 px-1">Suppliers</span>
                        </a>
                    </li>
                    <li class="">
                        <a href="<?php echo e(route('warehouse')); ?>" class="sidebar-link">
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
                        <a href="<?php echo e(route('invWarehouse')); ?>" class="sidebar-link">
                            <span class="mdi mdi-ray-start-arrow"></span>
                            <span class="ms-2 px-1">Inventory Warehouse</span>
                        </a>
                    </li>
                    <li class="">
                        <a href="<?php echo e(route('invMovements')); ?>" class="sidebar-link">
                            <span class="mdi mdi-ray-start-arrow"></span>
                            <span class="ms-2 px-1">Inventory Movements</span>
                        </a>
                    </li>
                    <li class="">
                        <a href="<?php echo e(route('stocktake')); ?>" class="sidebar-link">
                            <span class="mdi mdi-ray-start-arrow"></span>
                            <span class="ms-2 px-1">Stock Take</span>
                        </a>
                    </li>
                    <li class="">
                        <a href="<?php echo e(route('invStockTransfer')); ?>" class="sidebar-link">
                            <span class="mdi mdi-ray-start-arrow"></span>
                            <span class="ms-2 px-1">Stock Transfer</span>
                        </a>
                    </li>
                    <li class="">
                        <a href="<?php echo e(route('invWarehouseMovements')); ?>" class="sidebar-link">
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
                        <a href="<?php echo e(route('purchase-order')); ?>" class="sidebar-link">
                            <span class="mdi mdi-ray-start-arrow"></span>
                            <span class="ms-2 px-1">Purchase Order</span>
                        </a>
                    </li>
                    <li class="">
                        <a href="<?php echo e(route('receiving-report')); ?>" class="sidebar-link">
                            <span class="mdi mdi-ray-start-arrow"></span>
                            <span class="ms-2 px-1">Receiving Report</span>
                        </a>
                    </li>
                    <li class="">
                        <a href="<?php echo e(route('sales-order')); ?>" class="sidebar-link">
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
                        <a href="<?php echo e(route('dbconfig')); ?>" class="sidebar-link">
                            <span class="mdi mdi-ray-start-arrow"></span>
                            <span class="ms-2 px-1">Database Connection</span>
                        </a>
                    </li>
                </div>
            </ul>
        </li>
    </ul>
</aside>





<?php /**PATH C:\xampp\htdocs\bckup_FASTERP\FASTERP_2025-03-17\resources\views/Components/nav.blade.php ENDPATH**/ ?>
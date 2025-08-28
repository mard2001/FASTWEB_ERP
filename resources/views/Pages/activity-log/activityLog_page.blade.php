@extends('Layout.layout')

@section('html_title')
    <title>Activity Logs - FASTWEB ERP</title>
    <link href="https://cdn.materialdesignicons.com/6.5.95/css/materialdesignicons.min.css" rel="stylesheet">
@endsection

@section('title_header')
    <x-header title="Activity Logs" />
@endsection

@section('filtering_options')
<div class="filteringOptionDiv">
    <div class="d-flex">
        <div class="mb-1 mx-3" style="width: 180px;">
            <div class="VSLabel">DATE FROM</div>
            <input type="date" class="form-control" id="dateFrom">
        </div>
        <div class="mb-1 mx-3" style="width: 180px;">
            <div class="VSLabel">DATE TO</div>
            <input type="date" class="form-control" id="dateTo">
        </div>
        <div class="mb-1 mx-3" style="width: 200px;">
            <div class="VSLabel">ACTIVITY TYPE</div>
            <div id="activityType_VS" class="VSSelect"></div>
        </div>
        <div class="mb-1 mx-3" style="width: 250px;">
            <div class="VSLabel">MODULE</div>
            <div id="subjectType_VS" class="VSSelect"></div>
        </div>
        <div class="mb-1 mx-3" style="width: 200px;">
            <div class="VSLabel">USER NAME</div>
            <input type="text" class="form-control" id="userName" placeholder="Enter user name">
        </div>
    </div>
</div>
@endsection

@section('mini_dashboard_chart')
<div class="">
    <div class="row gx-2 mb-1">
        <div class="col-sm-12 col-md-3">
            <div class="containerStyle">
                <div class="d-flex mx-3 stockIn">
                    <div class="iconDiv align-middle">
                        <span class='mdi mdi-history'></span>
                    </div>
                    <div class="contentDiv">
                        <span class="contentTitle">Total Activities</span>
                        <p class="contentValue" id="total-activities">--- Activities</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-12 col-md-3">
            <div class="containerStyle">
                <div class="d-flex mx-3 stockOut">
                    <div class="iconDiv align-middle">
                        <span class='mdi mdi-calendar-today'></span>
                    </div>
                    <div class="contentDiv">
                        <span class="contentTitle">Today's Activities</span>
                        <p class="contentValue" id="today-activities">--- Today</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-12 col-md-3">
            <div class="containerStyle">
                <div class="d-flex mx-3 totalProfit">
                    <div class="iconDiv align-middle">
                        <span class='mdi mdi-account-multiple'></span>
                    </div>
                    <div class="contentDiv">
                        <span class="contentTitle">Active Users</span>
                        <p class="contentValue" id="unique-users">--- Users</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-12 col-md-3">
            <div class="containerStyle">
                <div class="d-flex mx-3 availableStock">
                    <div class="iconDiv align-middle">
                        <span class='mdi mdi-login'></span>
                    </div>
                    <div class="contentDiv">
                        <span class="contentTitle">Total Logins</span>
                        <p class="contentValue" id="total-logins">--- Logins</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('table')
    <style>
        #ActivityLogTable thead tr{
            white-space: nowrap;
        }
        .clickable-row:hover {
            background-color: var(--hover-bg-color, #f8f9fa) !important;
            transform: scale(1.01);
            transition: all 0.2s ease-in-out;
        }
        .clickable-row {
            transition: all 0.2s ease-in-out;
        }
        
        /* OS and Browser Icons Styling */
        .os-browser-icons {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            min-width: 80px;
        }
        
        .os-browser-icons img {
            width: 20px !important;
            height: 20px !important;
            transition: transform 0.2s ease;
            filter: drop-shadow(0 1px 2px rgba(0,0,0,0.1));
            cursor: help;
        }
        
        .os-browser-icons img:hover {
            transform: scale(1.1);
        }
        
        /* Ensure consistent spacing and alignment */
        .os-browser-icons > * {
            cursor: help;
        }
    </style>

    <x-contentButtonDiv downloadFunc="true"></x-contentButtonDiv>

    <x-table id="ActivityLogTable">
        <x-slot:td>
            <td class="col">Date & Time</td>
            <td class="col">User</td>
            <td class="col">Activity</td>
            <td class="col">Module</td>
            <td class="col">Description</td>
            <td class="col">OS & Browser</td>
            <td class="col">IP Address</td>
        </x-slot:td>
    </x-table>
@endsection

@section('modal')

    <x-mainModal mainModalTitle="activityDetailsModal" modalDialogClass="modal-xl" modalHeaderTitle="<span style='color: var(--primary-color, #0275d8);'>ACTIVITY DETAILS</span>" modalSubHeaderTitle="All key details related to this activity log entry.">
        <x-slot:form_fields>
            <div id="activityDetailsContent">
                <!-- Activity details will be loaded here -->
            </div>
        </x-slot:form_fields>

        <x-slot:modalFooterBtns>
            <div>
                <!-- Additional action buttons can be added here if needed -->
            </div>
            <div>
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </x-slot:modalFooterBtns>
    </x-mainModal>

@endsection

@section('pagejs')

<script type="text/javascript" src="{{ asset('assets/js/vendor/virtual-select.min.js') }}"></script>

<!-- Day.js core -->
<script src="https://cdn.jsdelivr.net/npm/dayjs@1/dayjs.min.js"></script>

<!-- Plugin: relativeTime -->
<script src="https://cdn.jsdelivr.net/npm/dayjs@1/plugin/relativeTime.js"></script>

<script src="{{ asset('assets/js/activity-logs/activity-logs.js') }}"></script>

@endsection
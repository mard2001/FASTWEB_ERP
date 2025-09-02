@extends('Layout.layout')

@section('html_title')
<title>User Management - FASTWEB ERP</title>
<link href="https://cdn.materialdesignicons.com/6.5.95/css/materialdesignicons.min.css" rel="stylesheet">
@endsection

@section('pagejs')
<script src="{{ asset('assets/js/user-management/users.js') }}"></script>
@endsection

@section('title_header')
<x-header title="User Management" />
@endsection

@section('filtering_options')
<div class="filteringOptionDiv">
    <div class="d-flex">
        <div class="mb-1 mx-3" style="width: 200px;">
            <div class="VSLabel">USER TYPE</div>
            <div id="userType_VS" class="VSSelect"></div>
        </div>
        <div class="mb-1 mx-3" style="width: 200px;">
            <div class="VSLabel">STATUS</div>
            <div id="userStatus_VS" class="VSSelect"></div>
        </div>
        <div class="mb-1 mx-3" style="width: 250px;">
            <div class="VSLabel">SEARCH USER</div>
            <input type="text" class="form-control" id="searchUser" placeholder="Search by name or email">
        </div>
    </div>
</div>
@endsection

@section('mini_dashboard_chart')
<div class="">
    <div class="row gx-2 mb-1">
        <div class="col-sm-12 col-md-4">
            <div class="containerStyle">
                <div class="d-flex mx-3 availableStock">
                    <div class="iconDiv align-middle">
                        <span class='mdi mdi-account-multiple'></span>
                    </div>
                    <div class="contentDiv">
                        <span class="contentTitle">Total Users</span>
                        <p class="contentValue" id="total-users">--- Users</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-12 col-md-4">
            <div class="containerStyle">
                <div class="d-flex mx-3 availableStock">
                    <div class="iconDiv align-middle">
                        <span class='mdi mdi-account-check'></span>
                    </div>
                    <div class="contentDiv">
                        <span class="contentTitle">Active Users</span>
                        <p class="contentValue" id="active-users">--- Users</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-12 col-md-4">
            <div class="containerStyle">
                <div class="d-flex mx-3 availableStock">
                    <div class="iconDiv align-middle">
                        <span class='mdi mdi-account-plus'></span>
                    </div>
                    <div class="contentDiv">
                        <span class="contentTitle">New This Month</span>
                        <p class="contentValue" id="new-users">--- Users</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('table')
<x-contentButtonDiv addFunc="true"></x-contentButtonDiv>

<x-table>
    <x-slot:td>
        <td class="col">ID</td>
        <td class="col">NAME</td>
        <td class="col">EMAIL</td>
        <td class="col">MOBILE</td>
        <td class="col">USER TYPE</td>
        <td class="col">CREATED AT</td>
        <td class="col">UPDATED AT</td>
    </x-slot:td>
</x-table>
@endsection

@section('modal')
<x-form_modal>
    <x-slot:form_fields>
        <div class="p-4 space-y-4">
            <div class="row">
                <div class="col-md-6">
                    <label for="firstName" class="form-label text-sm font-medium text-gray-700">First Name</label>
                    <input type="text" id="firstName" name="firstName" class="form-control mt-1 px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500"
                        required placeholder="Enter first name">
                </div>
                <div class="col-md-6">
                    <label for="lastName" class="form-label text-sm font-medium text-gray-700">Last Name</label>
                    <input type="text" id="lastName" name="lastName" class="form-control mt-1 px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500"
                        required placeholder="Enter last name">
                </div>
            </div>
            
            <div class="mb-3">
                <label for="mobile" class="form-label text-sm font-medium text-gray-700">Mobile Number</label>
                <div class="input-group">
                    <span class="input-group-text px-3 py-2 font-medium text-gray-700 bg-gray-100 border border-gray-300">+63</span>
                    <input type="text" id="mobile" name="mobile" class="form-control px-3 py-2 border border-gray-300 focus:ring-blue-500 focus:border-blue-500" 
                        maxlength="10" inputmode="numeric" oninput="this.value = this.value.replace(/[^0-9]/g, '')" 
                        required placeholder="9XXXXXXXXX">
                </div>
            </div>
            
            <div class="mb-3">
                <label for="email" class="form-label text-sm font-medium text-gray-700">Email Address</label>
                <input type="email" id="email" name="email" class="form-control mt-1 px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500"
                    required placeholder="Enter email address">
            </div>
            
            <div class="mb-3">
                <label for="userType" class="form-label text-sm font-medium text-gray-700">User Type</label>
                <select id="userType" name="userType" class="form-select mt-1 px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500" required>
                    <option value="">Select User Type</option>
                    <option value="user">User</option>
                    <option value="admin">Admin</option>
                    <option value="super_admin">Super Admin</option>
                    <option value="developer">Developer</option>
                </select>
            </div>
        </div>
    </x-slot:form_fields>
</x-form_modal>
@endsection

@section('pagejs')
<script src="{{ asset('assets/js/user-management/users.js') }}"></script>
@endsection
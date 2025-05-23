<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="{{ asset('assets/resources/ERP_icon1.png') }}">

    {{-- GOOGLE FONTS --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">

    @yield('html_title')
    @include('Links.main_stlyles_links')

</head>

<body>
    
    <div class="wrapper w-100">
        @include('Components.nav')
        <div class="main">
            @yield('title_header')
            @yield('filtering_options')
            @yield('mini_dashboard_chart')
            <div class="container-fluid mainInnerDiv">
                @yield('table')
            </div>
            
            @yield('modal')
            @include('Components.uploader_modal')
        </div>
    </div>
    
</body>

@include('Links.main_js_library_links')
@yield('pagejs')

</html>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- <title>Customer Maintenance</title> -->
    @yield('html_title')
    @yield('internal_css')
    @include('Links.main_stlyles_links')

</head>

<body>
    <div class="wrapper">
        @include('Components.nav')
        <div class="main">
            @yield('title_header')
            @yield('content')
        </div>
    </div>    

    <!-- @include('Links.main_js_library_links') -->
    @yield('pagejs')

</body>

</html>
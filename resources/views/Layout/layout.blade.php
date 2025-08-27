<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/png" href="{{ asset('assets/resources/ERP_icon1.png') }}">

    {{-- GOOGLE FONTS --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    @php
        $activeTheme = \App\Models\Theme::getActive();
        $fontsToLoad = ['Inter', 'Roboto']; // Default fonts
        if($activeTheme) {
            $fontsToLoad[] = $activeTheme->heading_font;
            $fontsToLoad[] = $activeTheme->body_font;
        }
        $fontsToLoad = array_unique($fontsToLoad);
        $fontQuery = [];
        foreach($fontsToLoad as $font) {
            $fontQuery[] = 'family=' . urlencode($font) . ':ital,wght@0,100..900;1,100..900';
        }
        $googleFontsUrl = 'https://fonts.googleapis.com/css2?' . implode('&', $fontQuery) . '&display=swap';
    @endphp
    <link href="{{ $googleFontsUrl }}" rel="stylesheet">
    
    @yield('html_title')
    @include('Links.main_stlyles_links')
    
    {{-- Dynamic Theme CSS Variables --}}
    @php
        $activeTheme = \App\Models\Theme::getActive();
    @endphp
    @if($activeTheme)
    <style>
        :root {
            --primary-color: {{ $activeTheme->primary_color }};
            --secondary-color: {{ $activeTheme->secondary_color }};
            --accent-color: {{ $activeTheme->accent_color }};
            --background-color: {{ $activeTheme->background_color }};
            --text-color: {{ $activeTheme->text_color }};
            --heading-font: '{{ $activeTheme->heading_font }}', sans-serif;
            --body-font: '{{ $activeTheme->body_font }}', sans-serif;
            --primary-rgb: {{ implode(', ', sscanf($activeTheme->primary_color, '#%02x%02x%02x')) }};
        }
    </style>
    @endif

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
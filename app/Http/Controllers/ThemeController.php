<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class ThemeController extends Controller
{
    /**
     * Display a listing of themes
     * Note: Data operations are handled by API\ThemeController
     */
    public function index(): View
    {
        return view('Pages.themes.index');
    }

    /**
     * Show the form for creating a new theme
     * Note: Form submission is handled by API\ThemeController
     */
    public function create(): View
    {
        return view('Pages.themes.form');
    }

    /**
     * Show the form for editing a theme
     * Note: Form submission is handled by API\ThemeController
     */
    public function edit($id): View
    {
        $theme = \App\Models\Theme::findOrFail($id);
        return view('Pages.themes.form', compact('theme'));
    }

    // Note: All data manipulation methods (store, update, destroy, activate, getThemes)
    // have been moved to App\Http\Controllers\api\ThemeController for uniform REST API approach
}
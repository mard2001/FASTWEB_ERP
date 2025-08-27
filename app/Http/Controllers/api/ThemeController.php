<?php

namespace App\Http\Controllers\api;

use App\Models\Theme;
use Illuminate\Http\Request;
use App\Traits\dbconfigs;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;

class ThemeController
{
    use dbconfigs;

    /**
     * Display a listing of themes.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(): JsonResponse
    {
        try {
            $themes = Theme::orderBy('is_active', 'desc')
                ->orderBy('created_at', 'desc')
                ->get();

            $activeTheme = Theme::getActive();
            $fonts = Theme::getAvailableFonts();

            return response()->json([
                'success' => true,
                'data' => [
                    'themes' => $themes,
                    'activeTheme' => $activeTheme,
                    'fonts' => $fonts
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch themes',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created theme.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255|unique:themes,name',
                'primary_color' => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
                'secondary_color' => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
                'accent_color' => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
                'background_color' => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
                'text_color' => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
                'heading_font' => 'required|string|max:100',
                'body_font' => 'required|string|max:100',
                'activate' => 'sometimes|boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = Auth::user();
            
            $themeData = $validator->validated();
            
            // Remove activate from theme data as it's not a fillable field
            $activate = $themeData['activate'] ?? false;
            unset($themeData['activate']);

            $theme = Theme::create($themeData);

            // Activate theme if requested
            if ($activate) {
                $theme->activate();
            }

            return response()->json([
                'success' => true,
                'message' => 'Theme created successfully',
                'data' => $theme
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create theme',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified theme.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id): JsonResponse
    {
        try {
            $theme = Theme::findOrFail($id);
            $fonts = Theme::getAvailableFonts();

            return response()->json([
                'success' => true,
                'data' => [
                    'theme' => $theme,
                    'fonts' => $fonts
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Theme not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update the specified theme.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $theme = Theme::findOrFail($id);
            
            $user = Auth::user();

            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255|unique:themes,name,' . $id,
                'primary_color' => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
                'secondary_color' => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
                'accent_color' => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
                'background_color' => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
                'text_color' => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
                'heading_font' => 'required|string|max:100',
                'body_font' => 'required|string|max:100',
                'activate' => 'sometimes|boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $themeData = $validator->validated();
            
            // Remove activate from theme data
            $activate = $themeData['activate'] ?? false;
            unset($themeData['activate']);

            $theme->update($themeData);

            // Activate theme if requested
            if ($activate) {
                $theme->activate();
            }

            return response()->json([
                'success' => true,
                'message' => 'Theme updated successfully',
                'data' => $theme
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update theme',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified theme.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id): JsonResponse
    {
        try {
            $theme = Theme::findOrFail($id);
            
            $user = Auth::user();

            // Prevent deletion of active theme
            if ($theme->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete active theme. Please activate another theme first.'
                ], 422);
            }

            // Prevent deletion of default themes
            if ($theme->isDefault()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete default theme'
                ], 422);
            }

            $theme->delete();

            return response()->json([
                'success' => true,
                'message' => 'Theme deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete theme',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Activate a theme.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function activate($id): JsonResponse
    {
        try {
            $theme = Theme::findOrFail($id);
            $theme->activate();

            return response()->json([
                'success' => true,
                'message' => 'Theme activated successfully',
                'data' => $theme
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to activate theme',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get the currently active theme.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getActive(): JsonResponse
    {
        try {
            $activeTheme = Theme::getActive();

            return response()->json([
                'success' => true,
                'data' => $activeTheme
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get active theme',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get theme CSS variables.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCss($id): JsonResponse
    {
        try {
            $theme = Theme::findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => [
                    'css' => $theme->toCss(),
                    'variables' => $theme->getCssVariables()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get theme CSS',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
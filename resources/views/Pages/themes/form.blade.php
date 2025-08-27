@extends('Layout.layout')

@php
if (!function_exists('hexToRgb')) {
    function hexToRgb($hex) {
        $hex = ltrim($hex, '#');
        if (strlen($hex) == 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }
        return implode(', ', [
            hexdec(substr($hex, 0, 2)),
            hexdec(substr($hex, 2, 2)),
            hexdec(substr($hex, 4, 2))
        ]);
    }
}

$isEdit = isset($theme) && $theme;
$pageTitle = $isEdit ? 'Edit Theme' : 'Create New Theme';
$pageDescription = $isEdit ? 'Modify your theme settings and appearance' : 'Design and customize your application\'s visual appearance';
$submitText = $isEdit ? 'Update Theme' : 'Create Theme';
$formId = $isEdit ? 'editThemeForm' : 'createThemeForm';
$backUrl = route('themes.index');
@endphp

@section('table')
<div class="container-fluid">
    <!-- Header Section -->
    <div class="header-container mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="display-5 mb-2">{{ $pageTitle }}</h1>
                <p class="text-muted mb-0">{{ $pageDescription }}</p>
                <div class="header-decoration"></div>
            </div>
            <div>
                <a href="{{ $backUrl }}" class="btn btn-outline-secondary me-2">
                    <i class="fas fa-arrow-left"></i> Back to Themes
                </a>
            </div>
        </div>
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
        <div class="alert alert-success custom-alert" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger custom-alert" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
        </div>
    @endif

    <form id="{{ $formId }}" novalidate>
        @csrf
        @if($isEdit)
            <input type="hidden" id="themeId" value="{{ $theme->id }}">
            <input type="hidden" id="isEdit" value="true">
        @else
            <input type="hidden" id="isEdit" value="false">
        @endif
        
        <div class="row">
            <!-- Theme Configuration -->
            <div class="col-12">
                <!-- Basic Information Card -->
                <div class="theme-config-card mb-4">
                    <div class="card-header">
                        <h5 class="section-title mb-0">
                            <i class="fas fa-info-circle me-2"></i>Basic Information
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="themeName" class="form-label">Theme Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control modern-input" id="themeName" name="name" required placeholder="Enter theme name" value="{{ $isEdit ? $theme->name : '' }}">
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control modern-input" id="description" name="description" rows="3" placeholder="Describe your theme">{{ $isEdit ? ($theme->description ?? '') : '' }}</textarea>
                            </div>
                        </div>
                        @if(!$isEdit)
                        <div class="row">
                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="activateTheme" name="activate">
                                    <label class="form-check-label" for="activateTheme">
                                        Activate this theme immediately after creation
                                    </label>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
                
                <!-- Color Palette Card -->
                <div class="theme-config-card mb-4">
                    <div class="card-header">
                        <h5 class="section-title mb-0">
                            <i class="fas fa-palette me-2"></i>Color Palette
                        </h5>
                        <small class="text-muted">Define the main colors for your theme</small>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label for="primaryColor" class="form-label">Primary Color <span class="text-danger">*</span></label>
                                <div class="color-input-group">
                                    <input type="color" class="form-control color-picker" id="primaryColor" name="primary_color" value="{{ $isEdit ? $theme->primary_color : '#4e73df' }}" required>
                                    <input type="text" class="form-control color-text" id="primaryColorText" value="{{ $isEdit ? $theme->primary_color : '#4e73df' }}">
                                </div>
                                <div class="invalid-feedback"></div>
                            </div>
                            
                            <div class="col-md-3 mb-3">
                                <label for="secondaryColor" class="form-label">Secondary Color <span class="text-danger">*</span></label>
                                <div class="color-input-group">
                                    <input type="color" class="form-control color-picker" id="secondaryColor" name="secondary_color" value="{{ $isEdit ? $theme->secondary_color : '#1E3C72' }}" required>
                                    <input type="text" class="form-control color-text" id="secondaryColorText" value="{{ $isEdit ? $theme->secondary_color : '#1E3C72' }}">
                                </div>
                                <div class="invalid-feedback"></div>
                            </div>
                            
                            <div class="col-md-3 mb-3">
                                <label for="accentColor" class="form-label">Accent Color <span class="text-danger">*</span></label>
                                <div class="color-input-group">
                                    <input type="color" class="form-control color-picker" id="accentColor" name="accent_color" value="{{ $isEdit ? $theme->accent_color : '#1cc88a' }}" required>
                                    <input type="text" class="form-control color-text" id="accentColorText" value="{{ $isEdit ? $theme->accent_color : '#1cc88a' }}">
                                </div>
                                <div class="invalid-feedback"></div>
                            </div>
                            
                            <div class="col-md-3 mb-3">
                                <label for="backgroundColor" class="form-label">Background Color <span class="text-danger">*</span></label>
                                <div class="color-input-group">
                                    <input type="color" class="form-control color-picker" id="backgroundColor" name="background_color" value="{{ $isEdit ? $theme->background_color : '#ffffff' }}" required>
                                    <input type="text" class="form-control color-text" id="backgroundColorText" value="{{ $isEdit ? $theme->background_color : '#ffffff' }}">
                                </div>
                                <div class="invalid-feedback"></div>
                            </div>
                            
                            <div class="col-md-12 mb-3">
                                <label for="textColor" class="form-label">Text Color <span class="text-danger">*</span></label>
                                <div class="color-input-group">
                                    <input type="color" class="form-control color-picker" id="textColor" name="text_color" value="{{ $isEdit ? $theme->text_color : '#212529' }}" required>
                                    <input type="text" class="form-control color-text" id="textColorText" value="{{ $isEdit ? $theme->text_color : '#212529' }}">
                                </div>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Typography Card -->
                <div class="theme-config-card mb-4">
                    <div class="card-header">
                        <h5 class="section-title mb-0">
                            <i class="fas fa-font me-2"></i>Typography
                        </h5>
                        <small class="text-muted">Choose fonts for your theme</small>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="headingFont" class="form-label">Heading Font <span class="text-danger">*</span></label>
                                <select class="form-select modern-select" id="headingFont" name="heading_font" required>
                                    <option value="">Select heading font...</option>
                                    <!-- Options will be populated via JavaScript -->
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="bodyFont" class="form-label">Body Font <span class="text-danger">*</span></label>
                                <select class="form-select modern-select" id="bodyFont" name="body_font" required>
                                    <option value="">Select body font...</option>
                                    <!-- Options will be populated via JavaScript -->
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="col-12">
                <div class="action-buttons mt-4">
                    <button type="submit" class="btn btn-primary btn-create me-2">
                        <i class="fas fa-save me-2"></i>{{ $submitText }}
                    </button>
                    <button type="button" class="btn btn-outline-secondary" id="resetBtn">
                        <i class="fas fa-undo me-2"></i>Reset {{ $isEdit ? 'Changes' : 'to Defaults' }}
                    </button>
                    @if($isEdit && !$theme->is_active)
                        <button type="button" class="btn btn-success ms-2" id="activateBtn" data-theme-id="{{ $theme->id }}">
                            <i class="fas fa-check-circle me-2"></i>Activate Theme
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Full Preview Modal -->
<div class="modal fade" id="fullPreviewModal" tabindex="-1" aria-labelledby="fullPreviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="fullPreviewModalLabel">Theme Full Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="fullPreviewContent">
                    <!-- Full preview content will be populated here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close Preview</button>
                <button type="button" class="btn btn-primary" id="saveFromPreviewBtn">
                    <i class="fas fa-save"></i> {{ $submitText }}
                </button>
            </div>
        </div>
    </div>
</div>

<style>
/* General Styles */
.container-fluid {
    background-color: #f8f9fa;
    min-height: 100vh;
}

/* Header Styles */
.header-container {
    position: relative;
    padding-bottom: 1.5rem;
}

.header-decoration {
    position: absolute;
    bottom: 0;
    left: 0;
    width: 100px;
    height: 5px;
    background: linear-gradient(90deg, var(--primary-color, #335DA6), var(--secondary-color, #1E3C72));
    border-radius: 10px;
}

.display-5 {
    background: linear-gradient(90deg, var(--primary-color, #335DA6), var(--secondary-color, #1E3C72));
    -webkit-background-clip: text;
    background-clip: text;
    color: transparent;
    font-weight: 800;
}

/* Alert Styles */
.custom-alert {
    border: none;
    border-radius: 10px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
    padding: 1rem;
}

/* Theme Configuration Cards */
.theme-config-card {
    background-color: white;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
    border: none;
    transition: all 0.3s ease;
}

.theme-config-card:hover {
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
    transform: translateY(-2px);
}

.theme-config-card .card-header {
    background-color: white;
    border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    padding: 1.5rem;
}

.section-title {
    color: var(--primary-color, #4e73df);
    font-weight: 600;
    display: flex;
    align-items: center;
}

.theme-config-card .card-body {
    padding: 1.5rem;
}

/* Form Elements */
.modern-input, .modern-select {
    border: 2px solid #e9ecef;
    border-radius: 10px;
    padding: 0.75rem 1rem;
    transition: all 0.3s ease;
    background-color: #fff;
}

.modern-input:focus, .modern-select:focus {
    border-color: var(--primary-color, #4e73df);
    box-shadow: 0 0 0 0.2rem rgba(var(--primary-rgb, 78, 115, 223), 0.25);
    background-color: #fff;
}

.form-label {
    font-weight: 600;
    color: #495057;
    margin-bottom: 0.5rem;
}

/* Color Input Styles */
.color-input-group {
    display: flex;
    gap: 10px;
    align-items: center;
}

.color-picker {
    width: 60px;
    height: 45px;
    border: 2px solid #e9ecef;
    border-radius: 10px;
    padding: 5px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.color-picker:hover {
    border-color: var(--primary-color, #4e73df);
    transform: scale(1.05);
}

.color-text {
    flex: 1;
    font-family: 'Courier New', monospace;
    text-transform: uppercase;
}

/* Action Buttons */
.action-buttons {
    padding: 0;
}

.btn-create {
    background: linear-gradient(45deg, var(--primary-color, #335DA6), var(--secondary-color, #1E3C72));
    border: none;
    border-radius: 10px;
    padding: 0.75rem 1.5rem;
    font-weight: 600;
    color: white;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}

.btn-create:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    filter: brightness(110%);
    color: white;
}

/* Responsive Design */
@media (max-width: 992px) {
    .header-container {
        padding-bottom: 1rem;
    }
    
    .display-5 {
        font-size: 1.8rem;
    }
}

@media (max-width: 768px) {
    .color-input-group {
        flex-direction: column;
        align-items: stretch;
    }
    
    .color-picker {
        width: 100%;
        height: 50px;
    }
}

/* Form Validation */
.is-invalid {
    border-color: #dc3545;
}

.invalid-feedback {
    display: block;
    width: 100%;
    margin-top: 0.25rem;
    font-size: 0.875rem;
    color: #dc3545;
}

/* Loading States */
.btn-create:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
}

.btn-create.loading {
    position: relative;
    color: transparent;
}

.btn-create.loading::after {
    content: '';
    position: absolute;
    width: 20px;
    height: 20px;
    top: 50%;
    left: 50%;
    margin-left: -10px;
    margin-top: -10px;
    border: 2px solid #ffffff;
    border-radius: 50%;
    border-top-color: transparent;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}
</style>
@endsection

@section('pagejs')
<script>
// Pass PHP variables to JavaScript
window.themeFormConfig = {
    isEdit: {{ $isEdit ? 'true' : 'false' }},
    @if($isEdit)
    themeData: {
        id: {{ $theme->id }},
        name: '{{ $theme->name }}',
        primary_color: '{{ $theme->primary_color }}',
        secondary_color: '{{ $theme->secondary_color }}',
        accent_color: '{{ $theme->accent_color }}',
        background_color: '{{ $theme->background_color }}',
        text_color: '{{ $theme->text_color }}',
        heading_font: '{{ $theme->heading_font }}',
        body_font: '{{ $theme->body_font }}',
        is_active: {{ $theme->is_active ? 'true' : 'false' }}
    }
    @endif
};
</script>
<script src="{{ asset('assets/js/themes/themes-form.js') }}"></script>
@endsection
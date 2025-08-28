@extends('Layout.layout')

@section('html_title')
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<title>Themes</title>
@endsection

@section('table')
<div class="container-fluid px-4 py-5" style="background-color: var(--background-color)">
    <div class="row justify-content-center">
        <div class="col-12 col-xl-10">
            <!-- Header Section -->
            <div class="header-container mb-5">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="display-5 fw-bold mb-0">Theme Gallery</h2>
                        <p class="text-muted lead">Customize the look and feel of your ERP system</p>
                    </div>
                    <button type="button" class="btn btn-primary btn-lg create-theme-btn" id="createThemeBtn">
                        <i class="bi bi-plus-circle me-2"></i>Create New Theme
                    </button>
                </div>
                <div class="header-decoration"></div>
            </div>

            <!-- Success/Error Messages -->
            <div id="alertContainer"></div>

            <!-- Theme Cards -->
            <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-5 mb-5" id="themesContainer">
                <!-- Theme cards will be populated via JavaScript -->
            </div>

            <!-- Delete Confirmation Modal -->
            <div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-labelledby="deleteConfirmModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="deleteConfirmModalLabel">
                                <i class="bi bi-exclamation-triangle text-warning me-2"></i>Confirm Delete
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p class="mb-3">Are you sure you want to delete the theme <strong id="deleteThemeName"></strong>?</p>
                            <div class="alert alert-warning d-flex align-items-center" role="alert">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                <div>This action cannot be undone. The theme will be permanently removed from the system.</div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                <i class="bi bi-x-circle me-1"></i>Cancel
                            </button>
                            <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
                                <i class="bi bi-trash me-1"></i>Delete Theme
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Theme Documentation -->
            <div class="documentation-card">
                <div class="doc-header">
                    <h4 class="doc-title"><i class="bi bi-info-circle me-2"></i>How Themes Work</h4>
                </div>
                <div class="doc-body">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="doc-section">
                                <h5 class="section-title"><i class="bi bi-palette me-2"></i>Color Settings</h5>
                                <ul class="feature-list">
                                    <li><span class="feature-name">Primary Color:</span> Main branding color used for headers and buttons</li>
                                    <li><span class="feature-name">Secondary Color:</span> Used for secondary elements and hover states</li>
                                    <li><span class="feature-name">Accent Color:</span> Used for highlights and important elements</li>
                                    <li><span class="feature-name">Background Color:</span> The main page background color</li>
                                    <li><span class="feature-name">Text Color:</span> The color used for body text</li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="doc-section">
                                <h5 class="section-title"><i class="bi bi-type me-2"></i>Font Settings</h5>
                                <ul class="feature-list">
                                    <li><span class="feature-name">Heading Font:</span> Used for headings, titles, and important text</li>
                                    <li><span class="feature-name">Body Font:</span> Used for general content and paragraph text</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* General Styles */
.container-fluid {
    background-color: #f8f9fa;
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
    background: linear-gradient(90deg, var(--primary-color, #4e73df), var(--secondary-color, #6f42c1));
    border-radius: 10px;
}

.display-5 {
    background: linear-gradient(90deg, var(--primary-color, #4e73df), var(--secondary-color, #6f42c1));
    -webkit-background-clip: text;
    background-clip: text;
    color: transparent;
    font-weight: 800;
}

.create-theme-btn {
    border-radius: 50px;
    padding: 0.75rem 1.5rem;
    font-weight: 600;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
    border: none;
    background-color: var(--primary-color, #4e73df);
}

.create-theme-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
    background-color: var(--primary-color, #4e73df);
    filter: brightness(110%);
}

/* Alert Styles */
.custom-alert {
    border: none;
    border-radius: 10px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
    padding: 1rem;
}

/* Theme Card Styles */
.theme-card-container {
    perspective: 1000px;
}

.theme-card {
    transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
    border-radius: 16px;
    overflow: hidden;
    border: none;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
    position: relative;
}

.theme-card:hover {
    transform: translateY(-10px) rotateX(5deg);
    box-shadow: 0 15px 30px rgba(0, 0, 0, 0.12);
}

.theme-card.active {
    box-shadow: 0 10px 25px rgba(var(--primary-rgb, 78, 115, 223), 0.25);
}

.theme-status-indicator {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 5px;
    background-color: #e9ecef;
}

.theme-card.active .theme-status-indicator {
    background-color: var(--primary-color, #4e73df);
}

.card-header {
    background-color: white;
    border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    padding: 1.25rem;
}

.theme-title {
    font-size: 1.25rem;
    font-weight: 700;
}

.active-badge {
    background-color: rgba(var(--primary-rgb, 78, 115, 223), 0.1);
    color: var(--primary-color, #4e73df);
    font-weight: 600;
    padding: 0.25rem 0.5rem;
    border-radius: 50px;
    font-size: 0.75rem;
    pointer-events: none;
    cursor: default;
}

/* Color Palette Styles */
.preview-title {
    font-size: 0.9rem;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: #6c757d;
    margin-bottom: 1rem;
    font-weight: 600;
}

.color-palette {
    display: flex;
    gap: 10px;
}

.color-item {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    pointer-events: none;
    cursor: default;
}

.color-swatch {
    height: 60px;
    width: 100%;
    border-radius: 12px;
    margin-bottom: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    pointer-events: none;
    cursor: default;
}

.color-label {
    font-size: 0.8rem;
    font-weight: 600;
    text-align: center;
    color: #495057;
    pointer-events: none;
    cursor: default;
}

/* Font Preview Styles */
.font-preview {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.font-item {
    padding: 10px;
    background-color: #f8f9fa;
    border-radius: 10px;
    pointer-events: none;
    cursor: default;
}

.font-name {
    font-size: 1.1rem;
    margin-bottom: 5px;
    font-weight: 600;
    pointer-events: none;
    cursor: default;
}

.font-label {
    font-size: 0.75rem;
    color: #6c757d;
    margin: 0;
    pointer-events: none;
    cursor: default;
}

/* Theme Actions Styles */
.theme-actions {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
    gap: 17px;
    margin-top: 1.5rem;
}

.btn-action {
    width: 100%;
    min-width: 120px;
    border-radius: 8px;
    padding: 0.6rem 1rem;
    font-weight: 500;
    transition: all 0.3s ease;
    text-align: center;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
}

.activate-btn {
    background-color: rgba(var(--primary-rgb, 78, 115, 223), 0.1);
    color: var(--primary-color, #4e73df);
    border: 1px solid rgba(var(--primary-rgb, 78, 115, 223), 0.2);
}

.activate-btn:hover {
    background-color: var(--primary-color, #4e73df);
    color: white;
}

.edit-btn {
    background-color: rgba(108, 117, 125, 0.1);
    color: #6c757d;
    border: 1px solid rgba(108, 117, 125, 0.2);
}

.edit-btn:hover {
    background-color: #6c757d;
    color: white;
}

.delete-btn {
    background-color: rgba(220, 53, 69, 0.1);
    color: #dc3545;
    border: 1px solid rgba(220, 53, 69, 0.2);
}

.delete-btn:hover {
    background-color: #dc3545;
    color: white;
}

/* Documentation Card Styles */
.documentation-card {
    background-color: white;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
    margin-top: 2rem;
}

.doc-header {
    background: linear-gradient(45deg, var(--primary-color, #4e73df), var(--secondary-color, #6f42c1));
    color: white;
    padding: 1.5rem;
    position: relative;
}

.doc-title {
    margin: 0;
    font-weight: 700;
}

.doc-body {
    padding: 2rem;
}

.doc-section {
    background-color: #f8f9fa;
    border-radius: 12px;
    padding: 1.5rem;
    height: 100%;
    transition: all 0.3s ease;
}

.doc-section:hover {
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
    transform: translateY(-5px);
}

.section-title {
    color: var(--primary-color, #4e73df);
    margin-bottom: 1rem;
    font-weight: 600;
    display: flex;
    align-items: center;
}

.feature-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.feature-list li {
    padding: 8px 0;
    border-bottom: 1px dashed #e9ecef;
    display: flex;
    flex-direction: column;
}

.feature-list li:last-child {
    border-bottom: none;
}

.feature-name {
    font-weight: 600;
    color: #495057;
    margin-bottom: 3px;
}

/* Responsive Adjustments */
@media (max-width: 768px) {
    .header-container {
        padding-bottom: 1rem;
    }
    
    .display-5 {
        font-size: 1.8rem;
    }
    
    .create-theme-btn {
        padding: 0.5rem 1rem;
        font-size: 0.9rem;
    }
    
    .color-swatch {
        height: 40px;
    }
    
    .theme-actions {
        grid-template-columns: 1fr;
    }
    
    .btn-action {
        width: 100%;
        min-width: 100%;
    }
    
    .doc-section {
        padding: 1rem;
    }
}
</style>
@endsection

@section('pagejs')
<script src="https://cdn.jsdelivr.net/npm/webfontloader@1.6.28/webfontloader.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{ asset('assets/js/themes/themes-index.js') }}"></script>
@endsection
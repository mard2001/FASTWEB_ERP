/**
 * Theme Management Index Page JavaScript
 * Handles theme listing, activation, deletion, and preview functionality
 */

$(document).ready(function() {
    // Initialize the page
    initializeThemesPage();
    
    // Event listeners
    setupEventListeners();
    
    // Load themes data
    loadThemes();
});

/**
 * Initialize the themes page
 */
function initializeThemesPage() {
    // Initialize tooltips
    if ($.fn.tooltip) {
        $('[data-bs-toggle="tooltip"]').tooltip();
    }
    
    // Initialize any other components as needed
    console.log('Themes page initialized');
}

/**
 * Setup event listeners
 */
function setupEventListeners() {
    // Create new theme button
    $('#createThemeBtn').on('click', function() {
        window.location.href = '/settings/themes/create';
    });
    
    // Theme card clicks are disabled - no preview modal on card click
    
    // Refresh button
    $('#refreshBtn').on('click', function() {
        loadThemes();
    });
    
    // Theme activation
    $(document).on('click', '.btn-activate', function() {
        const themeId = $(this).data('theme-id');
        const themeName = $(this).data('theme-name');
        activateTheme(themeId, themeName);
    });
    
    // Theme preview
    $(document).on('click', '.btn-preview', function() {
        const themeId = $(this).data('theme-id');
        showThemePreview(themeId);
    });
    
    // Theme edit
    $(document).on('click', '.btn-edit', function() {
        const themeId = $(this).data('theme-id');
        window.location.href = `/settings/themes/${themeId}/edit`;
    });
    
    // Theme delete
    $(document).on('click', '.btn-delete', function() {
        const themeId = $(this).data('theme-id');
        const themeName = $(this).data('theme-name');
        showDeleteConfirmation(themeId, themeName);
    });
    
    // Delete confirmation
    $('#confirmDeleteBtn').on('click', function() {
        const themeId = $(this).data('theme-id');
        deleteTheme(themeId);
    });
    
    // Activate from preview modal
    $('#activateFromPreviewBtn').on('click', function() {
        const themeId = $(this).data('theme-id');
        const themeName = $(this).data('theme-name');
        $('#themePreviewModal').modal('hide');
        activateTheme(themeId, themeName);
    });
}

/**
 * Load themes data from API
 */
function loadThemes() {
    showLoading();
    
    $.ajax({
        url: '/api/themes',
        method: 'GET',
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem('api_token'),
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            'Accept': 'application/json'
        },
        success: function(response) {
            if (response.success) {
                populateThemesTable(response.data.themes);
                updateActiveThemeDisplay(response.data.activeTheme);
            } else {
                showError('Failed to load themes: ' + response.message);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error loading themes:', error);
            showError('Failed to load themes. Please try again.');
        },
        complete: function() {
            hideLoading();
        }
    });
}

/**
 * Populate the themes container with card data
 */
function populateThemesTable(themes) {
    const container = $('#themesContainer');
    container.empty();
    
    if (themes.length === 0) {
        container.html(`
            <div class="col-12 text-center py-5">
                <div class="text-muted">
                    <i class="bi bi-palette" style="font-size: 3rem;"></i>
                    <h4 class="mt-3">No themes available</h4>
                    <p>Create your first theme to get started</p>
                </div>
            </div>
        `);
        return;
    }
    
    themes.forEach(function(theme) {
        const themeCard = generateThemeCard(theme);
        container.append(themeCard);
    });
    
    // Reinitialize tooltips for new content
    $('[data-bs-toggle="tooltip"]').tooltip();
}

/**
 * Generate a complete theme card
 */
function generateThemeCard(theme) {
    const isActive = theme.is_active;
    const activeClass = isActive ? 'active' : '';
    const activeBadge = isActive ? '<span class="active-badge"><i class="bi bi-check-circle me-1"></i>Active</span>' : '';
    const isDefault = theme.name === 'Default Theme';
    
    return `
        <div class="col theme-card-container">
            <div class="card theme-card ${activeClass}" data-theme-id="${theme.id}">
                <div class="theme-status-indicator"></div>
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="theme-title mb-0">${theme.name}</h5>
                        ${activeBadge}
                    </div>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h6 class="preview-title">Color Palette</h6>
                        <div class="color-palette">
                            <div class="color-item">
                                <div class="color-swatch" style="background-color: ${theme.primary_color}"></div>
                                <span class="color-label">Primary</span>
                            </div>
                            <div class="color-item">
                                <div class="color-swatch" style="background-color: ${theme.secondary_color}"></div>
                                <span class="color-label">Secondary</span>
                            </div>
                            <div class="color-item">
                                <div class="color-swatch" style="background-color: ${theme.accent_color}"></div>
                                <span class="color-label">Accent</span>
                            </div>
                            <div class="color-item">
                                <div class="color-swatch" style="background-color: ${theme.background_color}"></div>
                                <span class="color-label">Background</span>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <h6 class="preview-title">Typography</h6>
                        <div class="font-preview">
                            <div class="font-item">
                                <div class="font-name" style="font-family: '${theme.heading_font}', sans-serif">${theme.heading_font}</div>
                                <p class="font-label">Heading Font</p>
                            </div>
                            <div class="font-item">
                                <div class="font-name" style="font-family: '${theme.body_font}', sans-serif">${theme.body_font}</div>
                                <p class="font-label">Body Font</p>
                            </div>
                        </div>
                    </div>
                    <div class="theme-actions">
                        ${!isActive ? `<button class="btn btn-action activate-btn" onclick="activateTheme(${theme.id}, '${theme.name}')"><i class="bi bi-check-circle me-1"></i>Activate</button>` : ''}
                        ${!isDefault ? `<button class="btn btn-action edit-btn" onclick="window.location.href='/settings/themes/${theme.id}/edit'"><i class="bi bi-pencil me-1"></i>Edit</button>` : ''}
                        ${!isActive && !isDefault ? `<button class="btn btn-action delete-btn" onclick="showDeleteConfirmation(${theme.id}, '${theme.name}')"><i class="bi bi-trash me-1"></i>Delete</button>` : ''}
                    </div>
                </div>
                <div class="card-footer text-muted">
                    <small><i class="bi bi-person me-1"></i>Created by ${theme.creator ? theme.creator.name : 'System'} • ${formatDate(theme.created_at)}</small>
                </div>
            </div>
        </div>
    `;
}

/**
 * Generate color preview for table
 */
function generateColorPreview(theme) {
    return `
        <div class="table-color-preview">
            <div class="color-swatch primary" style="background-color: ${theme.primary_color}" 
                 title="Primary: ${theme.primary_color}"></div>
            <div class="color-swatch secondary" style="background-color: ${theme.secondary_color}" 
                 title="Secondary: ${theme.secondary_color}"></div>
            <div class="color-swatch accent" style="background-color: ${theme.accent_color}" 
                 title="Accent: ${theme.accent_color}"></div>
        </div>
    `;
}

/**
 * Generate color details for table
 */
function generateColorDetails(theme) {
    return `
        <small>
            <div><strong>Primary:</strong> ${theme.primary_color}</div>
            <div><strong>Secondary:</strong> ${theme.secondary_color}</div>
            <div><strong>Accent:</strong> ${theme.accent_color}</div>
        </small>
    `;
}

/**
 * Generate font details for table
 */
function generateFontDetails(theme) {
    return `
        <div class="font-display">
            <div class="heading-font">${theme.heading_font}</div>
            <div class="body-font">${theme.body_font}</div>
        </div>
    `;
}

/**
 * Generate status badge
 */
function generateStatusBadge(theme) {
    if (theme.is_active) {
        return '<span class="theme-status active">Active</span>';
    } else if (theme.name === 'Default' || theme.name === 'Light' || theme.name === 'Dark') {
        return '<span class="theme-status default">Default</span>';
    } else {
        return '<span class="theme-status inactive">Inactive</span>';
    }
}

/**
 * Generate action buttons for table
 */
function generateActionButtons(theme) {
    const isActive = theme.is_active;
    const isDefault = theme.name === 'Default Theme' || theme.name === 'Default' || theme.name === 'Light' || theme.name === 'Dark';
    const canDelete = !isActive && !isDefault;
    const canEdit = !isDefault;
    
    return `
        <div class="action-buttons">
            ${!isActive ? `<button class="btn btn-activate btn-sm" data-theme-id="${theme.id}" data-theme-name="${theme.name}" title="Activate Theme">
                <i class="fas fa-check"></i>
            </button>` : ''}
            <button class="btn btn-preview btn-sm" data-theme-id="${theme.id}" title="Preview Theme">
                <i class="fas fa-eye"></i>
            </button>
            ${canEdit ? `<button class="btn btn-edit btn-sm" data-theme-id="${theme.id}" title="Edit Theme">
                <i class="fas fa-edit"></i>
            </button>` : ''}
            <button class="btn btn-delete btn-sm" data-theme-id="${theme.id}" data-theme-name="${theme.name}" 
                    title="Delete Theme" ${!canDelete ? 'disabled' : ''}>
                <i class="fas fa-trash"></i>
            </button>
        </div>
    `;
}

/**
 * Update active theme display
 */
function updateActiveThemeDisplay(activeTheme) {
    if (activeTheme) {
        $('#activeThemeName').text(activeTheme.name);
        $('#activeThemeInfo').text(`Created by ${activeTheme.creator ? activeTheme.creator.name : 'System'} on ${formatDate(activeTheme.created_at)}`);
        
        const previewHtml = `
            <div class="color-swatch" style="background-color: ${activeTheme.primary_color}" title="Primary"></div>
            <div class="color-swatch" style="background-color: ${activeTheme.secondary_color}" title="Secondary"></div>
            <div class="color-swatch" style="background-color: ${activeTheme.accent_color}" title="Accent"></div>
            <div class="color-swatch" style="background-color: ${activeTheme.background_color}" title="Background"></div>
            <div class="color-swatch" style="background-color: ${activeTheme.text_color}" title="Text"></div>
        `;
        
        $('#activeThemePreview').html(previewHtml);
    } else {
        $('#activeThemeName').text('No Active Theme');
        $('#activeThemeInfo').text('No theme is currently active');
        $('#activeThemePreview').html('<i class="fas fa-exclamation-triangle text-warning"></i>');
    }
}

/**
 * Activate a theme
 */
function activateTheme(themeId, themeName) {
    Swal.fire({
        title: 'Activate Theme',
        text: `Are you sure you want to activate the "${themeName}" theme?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, activate it!'
    }).then((result) => {
        if (result.isConfirmed) {
            // Show loading state
            $(`.btn-activate[data-theme-id="${themeId}"]`).closest('tr').addClass('theme-activating');
            
            $.ajax({
                url: `/api/themes/${themeId}/activate`,
                method: 'POST',
                headers: {
                    'Authorization': 'Bearer ' + localStorage.getItem('api_token'),
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'Accept': 'application/json'
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            title: 'Success!',
                            text: `Theme "${themeName}" has been activated.`,
                            icon: 'success',
                            timer: 2000,
                            showConfirmButton: false
                        });
                        
                        // Apply the theme immediately without page reload
                        applyThemeImmediately(response.data);
                        
                        // Reload themes to update the UI
                        setTimeout(() => {
                            loadThemes();
                        }, 1000);
                    } else {
                        showError('Failed to activate theme: ' + response.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error activating theme:', error);
                    showError('Failed to activate theme. Please try again.');
                },
                complete: function() {
                    $(`.btn-activate[data-theme-id="${themeId}"]`).closest('tr').removeClass('theme-activating');
                }
            });
        }
    });
}

/**
 * Show theme preview modal
 */
function showThemePreview(themeId) {
    $.ajax({
        url: `/api/themes/${themeId}`,
        method: 'GET',
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem('api_token'),
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            'Accept': 'application/json'
        },
        success: function(response) {
            if (response.success) {
                const theme = response.data.theme;
                populatePreviewModal(theme);
                $('#themePreviewModal').modal('show');
            } else {
                showError('Failed to load theme preview: ' + response.message);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error loading theme preview:', error);
            showError('Failed to load theme preview. Please try again.');
        }
    });
}

/**
 * Populate preview modal with theme data
 */
function populatePreviewModal(theme) {
    $('#themePreviewModalLabel').text(`Preview: ${theme.name}`);
    
    const previewContent = `
        <div class="theme-preview-card">
            <div class="theme-preview-header">
                <h5>${theme.name}</h5>
                <span class="${theme.is_active ? 'theme-status active' : 'theme-status inactive'}">
                    ${theme.is_active ? 'Active' : 'Inactive'}
                </span>
            </div>
            
            <div class="theme-preview-colors">
                <div class="color-item">
                    <div class="color-swatch" style="background-color: ${theme.primary_color}"></div>
                    <div class="color-label">Primary</div>
                    <div class="color-value">${theme.primary_color}</div>
                </div>
                <div class="color-item">
                    <div class="color-swatch" style="background-color: ${theme.secondary_color}"></div>
                    <div class="color-label">Secondary</div>
                    <div class="color-value">${theme.secondary_color}</div>
                </div>
                <div class="color-item">
                    <div class="color-swatch" style="background-color: ${theme.accent_color}"></div>
                    <div class="color-label">Accent</div>
                    <div class="color-value">${theme.accent_color}</div>
                </div>
                <div class="color-item">
                    <div class="color-swatch" style="background-color: ${theme.background_color}"></div>
                    <div class="color-label">Background</div>
                    <div class="color-value">${theme.background_color}</div>
                </div>
                <div class="color-item">
                    <div class="color-swatch" style="background-color: ${theme.text_color}"></div>
                    <div class="color-label">Text</div>
                    <div class="color-value">${theme.text_color}</div>
                </div>
            </div>
            
            <div class="theme-preview-fonts">
                <div class="font-item">
                    <h6>Heading Font</h6>
                    <div class="font-sample" style="font-family: ${theme.heading_font}">
                        <h4 style="margin: 0; font-family: ${theme.heading_font};">Sample Heading Text</h4>
                    </div>
                </div>
                <div class="font-item">
                    <h6>Body Font</h6>
                    <div class="font-sample" style="font-family: ${theme.body_font}">
                        <p style="margin: 0; font-family: ${theme.body_font};">Sample body text content for preview purposes.</p>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    $('#themePreviewContent').html(previewContent);
    
    // Set up activate button
    $('#activateFromPreviewBtn')
        .data('theme-id', theme.id)
        .data('theme-name', theme.name)
        .toggle(!theme.is_active);
}

/**
 * Show delete confirmation modal
 */
function showDeleteConfirmation(themeId, themeName) {
    $('#deleteThemeName').text(themeName);
    $('#confirmDeleteBtn').data('theme-id', themeId);
    $('#deleteConfirmModal').modal('show');
}

/**
 * Delete a theme
 */
function deleteTheme(themeId) {
    $.ajax({
        url: `/api/themes/${themeId}`,
        method: 'DELETE',
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem('api_token'),
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            'Accept': 'application/json'
        },
        success: function(response) {
            if (response.success) {
                $('#deleteConfirmModal').modal('hide');
                Swal.fire({
                    title: 'Deleted!',
                    text: 'Theme has been deleted successfully.',
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false
                });
                loadThemes(); // Reload the table
            } else {
                showError('Failed to delete theme: ' + response.message);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error deleting theme:', error);
            showError('Failed to delete theme. Please try again.');
        }
    });
}

/**
 * Utility functions
 */
function showLoading() {
    $('#themesTable tbody').html('<tr><td colspan="9" class="table-loading"><div class="loading-spinner"></div> Loading themes...</td></tr>');
}

function hideLoading() {
    // Loading will be hidden when table is populated
}

function showError(message) {
    Swal.fire({
        title: 'Error',
        text: message,
        icon: 'error',
        confirmButtonColor: '#dc3545'
    });
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString() + ' ' + date.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
}

/**
 * Apply theme immediately without page reload
 */
function applyThemeImmediately(theme) {
    // Remove existing theme style if it exists
    const existingStyle = document.getElementById('dynamic-theme-style');
    if (existingStyle) {
        existingStyle.remove();
    }

    // Create new style element with theme variables
    const style = document.createElement('style');
    style.id = 'dynamic-theme-style';
    
    // Convert hex to RGB for primary color
    const hexToRgb = (hex) => {
        const result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
        return result ? {
            r: parseInt(result[1], 16),
            g: parseInt(result[2], 16),
            b: parseInt(result[3], 16)
        } : null;
    };
    
    const primaryRgb = hexToRgb(theme.primary_color);
    const primaryRgbString = primaryRgb ? `${primaryRgb.r}, ${primaryRgb.g}, ${primaryRgb.b}` : '0, 0, 0';
    
    style.textContent = `
        :root {
            --primary-color: ${theme.primary_color};
            --secondary-color: ${theme.secondary_color};
            --accent-color: ${theme.accent_color};
            --background-color: ${theme.background_color};
            --text-color: ${theme.text_color};
            --heading-font: '${theme.heading_font}', sans-serif;
            --body-font: '${theme.body_font}', sans-serif;
            --primary-rgb: ${primaryRgbString};
        }
    `;
    
    // Append to head
    document.head.appendChild(style);
    
    console.log('Theme applied immediately:', theme.name);
}
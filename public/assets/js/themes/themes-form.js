// Theme Form Management
class ThemeFormManager {
    constructor() {
        // Detect edit mode from hidden input
        this.isEdit = document.getElementById('isEdit')?.value === 'true';
        this.themeData = window.themeFormConfig?.themeData || {};
        this.form = document.getElementById(this.isEdit ? 'editThemeForm' : 'createThemeForm');
        this.fonts = [
            'Arial', 'Helvetica', 'Times New Roman', 'Georgia', 'Verdana',
            'Tahoma', 'Trebuchet MS', 'Impact', 'Comic Sans MS', 'Courier New',
            'Lucida Console', 'Palatino', 'Garamond', 'Bookman', 'Avant Garde',
            'Roboto', 'Open Sans', 'Lato', 'Montserrat', 'Source Sans Pro',
            'Raleway', 'Ubuntu', 'Nunito', 'Poppins', 'Inter'
        ];
        
        this.init();
    }

    init() {
        this.populateFontSelects();
        this.setupColorInputs();
        this.setupFormValidation();
        this.setupEventListeners();
        
        if (this.isEdit) {
            this.populateFormData();
        }
    }

    populateFontSelects() {
        const headingSelect = document.getElementById('headingFont');
        const bodySelect = document.getElementById('bodyFont');
        
        this.fonts.forEach(font => {
            const headingOption = new Option(font, font);
            const bodyOption = new Option(font, font);
            
            headingOption.style.fontFamily = font;
            bodyOption.style.fontFamily = font;
            
            headingSelect.appendChild(headingOption);
            bodySelect.appendChild(bodyOption);
        });
        
        // Set default values
        if (!this.isEdit) {
            headingSelect.value = 'Roboto';
            bodySelect.value = 'Open Sans';
        }
    }

    populateFormData() {
        if (!this.themeData) return;
        
        // Populate form fields with existing data
        const fields = {
            'themeName': this.themeData.name,
            'primaryColor': this.themeData.primary_color,
            'primaryColorText': this.themeData.primary_color,
            'secondaryColor': this.themeData.secondary_color,
            'secondaryColorText': this.themeData.secondary_color,
            'accentColor': this.themeData.accent_color,
            'accentColorText': this.themeData.accent_color,
            'backgroundColor': this.themeData.background_color,
            'backgroundColorText': this.themeData.background_color,
            'textColor': this.themeData.text_color,
            'textColorText': this.themeData.text_color,
            'headingFont': this.themeData.heading_font,
            'bodyFont': this.themeData.body_font
        };
        
        Object.entries(fields).forEach(([fieldId, value]) => {
            const field = document.getElementById(fieldId);
            if (field && value) {
                field.value = value;
            }
        });
    }

    setupColorInputs() {
        const colorInputs = document.querySelectorAll('.color-picker');
        
        colorInputs.forEach(input => {
            const textInput = document.getElementById(input.id + 'Text');
            
            // Sync color picker with text input
            input.addEventListener('input', (e) => {
                if (textInput) {
                    textInput.value = e.target.value.toUpperCase();
                }
                this.updatePreview();
            });
            
            // Sync text input with color picker
            if (textInput) {
                textInput.addEventListener('input', (e) => {
                    const value = e.target.value;
                    if (this.isValidHexColor(value)) {
                        input.value = value;
                        this.updatePreview();
                    }
                });
                
                textInput.addEventListener('blur', (e) => {
                    const value = e.target.value;
                    if (!this.isValidHexColor(value)) {
                        e.target.value = input.value;
                    }
                });
            }
        });
    }

    isValidHexColor(hex) {
        return /^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/.test(hex);
    }

    updatePreview() {
        const colors = {
            primary: document.getElementById('primaryColor').value,
            secondary: document.getElementById('secondaryColor').value,
            accent: document.getElementById('accentColor').value,
            background: document.getElementById('backgroundColor').value,
            text: document.getElementById('textColor').value
        };
        
        // Update CSS custom properties for live preview
        document.documentElement.style.setProperty('--primary-color', colors.primary);
        document.documentElement.style.setProperty('--secondary-color', colors.secondary);
        document.documentElement.style.setProperty('--accent-color', colors.accent);
        document.documentElement.style.setProperty('--background-color', colors.background);
        document.documentElement.style.setProperty('--text-color', colors.text);
        
        // Update RGB values for box shadows
        const primaryRgb = this.hexToRgb(colors.primary);
        if (primaryRgb) {
            document.documentElement.style.setProperty('--primary-rgb', primaryRgb);
        }
    }

    hexToRgb(hex) {
        const result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
        return result ? 
            `${parseInt(result[1], 16)}, ${parseInt(result[2], 16)}, ${parseInt(result[3], 16)}` : 
            null;
    }

    setupFormValidation() {
        const requiredFields = this.form.querySelectorAll('[required]');
        
        requiredFields.forEach(field => {
            field.addEventListener('blur', () => this.validateField(field));
            field.addEventListener('input', () => this.clearFieldError(field));
        });
    }

    validateField(field) {
        const value = field.value.trim();
        let isValid = true;
        let errorMessage = '';
        
        if (!value) {
            isValid = false;
            errorMessage = 'This field is required.';
        } else if (field.type === 'color' || field.classList.contains('color-text')) {
            if (!this.isValidHexColor(value)) {
                isValid = false;
                errorMessage = 'Please enter a valid hex color (e.g., #FF0000).';
            }
        }
        
        this.setFieldValidation(field, isValid, errorMessage);
        return isValid;
    }

    setFieldValidation(field, isValid, errorMessage) {
        const feedbackElement = field.parentNode.querySelector('.invalid-feedback');
        
        if (isValid) {
            field.classList.remove('is-invalid');
            field.classList.add('is-valid');
            if (feedbackElement) {
                feedbackElement.textContent = '';
            }
        } else {
            field.classList.remove('is-valid');
            field.classList.add('is-invalid');
            if (feedbackElement) {
                feedbackElement.textContent = errorMessage;
            }
        }
    }

    clearFieldError(field) {
        field.classList.remove('is-invalid');
        const feedbackElement = field.parentNode.querySelector('.invalid-feedback');
        if (feedbackElement) {
            feedbackElement.textContent = '';
        }
    }

    setupEventListeners() {
        // Form submission
        this.form.addEventListener('submit', (e) => this.handleFormSubmit(e));
        
        // Reset button
        const resetBtn = document.getElementById('resetBtn');
        if (resetBtn) {
            resetBtn.addEventListener('click', () => this.handleReset());
        }
        
        // Activate button (edit mode only)
        const activateBtn = document.getElementById('activateBtn');
        if (activateBtn) {
            activateBtn.addEventListener('click', () => this.handleActivateTheme());
        }
        
        // Font change listeners for preview
        const fontSelects = document.querySelectorAll('#headingFont, #bodyFont');
        fontSelects.forEach(select => {
            select.addEventListener('change', () => this.updateFontPreview());
        });
    }

    handleFormSubmit(e) {
        e.preventDefault();
        
        // Validate all required fields
        const requiredFields = this.form.querySelectorAll('[required]');
        let isFormValid = true;
        
        requiredFields.forEach(field => {
            if (!this.validateField(field)) {
                isFormValid = false;
            }
        });
        
        if (!isFormValid) {
            this.showAlert('Please fix the validation errors before submitting.', 'danger');
            return;
        }
        
        // Show loading state
        const submitBtn = this.form.querySelector('button[type="submit"]');
        this.setButtonLoading(submitBtn, true);
        
        // Prepare form data
        const formData = new FormData(this.form);
        const themeData = {};
        
        // Convert FormData to object
        for (let [key, value] of formData.entries()) {
            if (key !== '_token') {
                themeData[key] = value;
            }
        }
        
        // Handle checkbox for activate
        themeData.activate = document.getElementById('activateTheme')?.checked || false;
        
        // Determine API endpoint and method
        const isEdit = document.getElementById('isEdit')?.value === 'true';
        const themeId = document.getElementById('themeId')?.value;
        
        let url, method;
        if (isEdit) {
            url = `/api/themes/${themeId}`;
            method = 'PUT';
        } else {
            url = '/api/themes';
            method = 'POST';
        }
        
        // Make API call
        fetch(url, {
            method: method,
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('api_token'),
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify(themeData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                this.showAlert(data.message || (isEdit ? 'Theme updated successfully!' : 'Theme created successfully!'), 'success');
                
                // Redirect to themes index after a short delay
                setTimeout(() => {
                    window.location.href = '/settings/themes';
                }, 1500);
            } else {
                this.showAlert(data.message || 'Failed to save theme.', 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            this.showAlert('An error occurred while saving the theme.', 'danger');
        })
        .finally(() => {
            this.setButtonLoading(submitBtn, false);
        });
    }

    handleReset() {
        if (this.isEdit) {
            // Reset to original values
            this.populateFormData();
        } else {
            // Reset to defaults
            this.form.reset();
            document.getElementById('headingFont').value = 'Roboto';
            document.getElementById('bodyFont').value = 'Open Sans';
            
            // Reset color inputs to defaults
            const defaults = {
                'primaryColor': '#335DA6',
                'secondaryColor': '#1E3C72',
                'accentColor': '#33336F',
                'backgroundColor': '#ffffff',
                'textColor': '#212529'
            };
            
            Object.entries(defaults).forEach(([id, value]) => {
                const colorInput = document.getElementById(id);
                const textInput = document.getElementById(id + 'Text');
                if (colorInput) colorInput.value = value;
                if (textInput) textInput.value = value;
            });
        }
        
        // Clear validation states
        this.form.querySelectorAll('.is-invalid, .is-valid').forEach(field => {
            field.classList.remove('is-invalid', 'is-valid');
        });
        
        this.updatePreview();
        this.showAlert(this.isEdit ? 'Form reset to original values.' : 'Form reset to defaults.', 'info');
    }

    handleActivateTheme() {
        if (!this.isEdit || !this.themeData.id) return;
        
        const activateBtn = document.getElementById('activateBtn');
        this.setButtonLoading(activateBtn, true);
        
        fetch(`/api/themes/${this.themeData.id}/activate`, {
            method: 'POST',
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('api_token'),
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                this.showAlert('Theme activated successfully!', 'success');
                activateBtn.style.display = 'none';
            } else {
                this.showAlert(data.message || 'Failed to activate theme.', 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            this.showAlert('An error occurred while activating the theme.', 'danger');
        })
        .finally(() => {
            this.setButtonLoading(activateBtn, false);
        });
    }

    updateFontPreview() {
        const headingFont = document.getElementById('headingFont').value;
        const bodyFont = document.getElementById('bodyFont').value;
        
        if (headingFont) {
            document.documentElement.style.setProperty('--heading-font', headingFont);
        }
        if (bodyFont) {
            document.documentElement.style.setProperty('--body-font', bodyFont);
        }
    }

    setButtonLoading(button, loading) {
        if (loading) {
            button.disabled = true;
            button.classList.add('loading');
        } else {
            button.disabled = false;
            button.classList.remove('loading');
        }
    }

    showAlert(message, type = 'info') {
        // Remove existing alerts
        const existingAlerts = document.querySelectorAll('.custom-alert');
        existingAlerts.forEach(alert => alert.remove());
        
        // Create new alert
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} custom-alert`;
        alertDiv.innerHTML = `
            <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'danger' ? 'exclamation-circle' : 'info-circle'} me-2"></i>
            ${message}
        `;
        
        // Insert after header
        const header = document.querySelector('.header-container');
        header.insertAdjacentElement('afterend', alertDiv);
        
        // Auto-remove after 5 seconds
        setTimeout(() => {
            alertDiv.remove();
        }, 5000);
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    new ThemeFormManager();
});
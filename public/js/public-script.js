/**
 * Spelling Bee Pro - Public JavaScript
 *
 * Frontend functionality for registration forms, document uploads, and user interactions
 */

(function($) {
    'use strict';

    // Initialize when document is ready
    $(document).ready(function() {
        initRegistrationForm();
        initDocumentUpload();
        initFormValidation();
        initProgressTracking();
        console.log('OSB Public scripts loaded successfully');
    });

    /**
     * Initialize registration form functionality
     */
    function initRegistrationForm() {
        // Multi-step form navigation
        $('.osb-next-step').on('click', function(e) {
            e.preventDefault();
            const currentStep = $(this).closest('.osb-form-step');
            const nextStep = currentStep.next('.osb-form-step');

            if (validateStep(currentStep) && nextStep.length) {
                currentStep.hide();
                nextStep.show();
                updateProgressBar();
                updateStepIndicators();
            }
        });

        $('.osb-prev-step').on('click', function(e) {
            e.preventDefault();
            const currentStep = $(this).closest('.osb-form-step');
            const prevStep = currentStep.prev('.osb-form-step');

            if (prevStep.length) {
                currentStep.hide();
                prevStep.show();
                updateProgressBar();
                updateStepIndicators();
            }
        });

        // Form submission
        $('.osb-registration-form').on('submit', function(e) {
            e.preventDefault();

            if (validateAllSteps()) {
                submitRegistrationForm($(this));
            }
        });

        // Auto-save functionality
        $('.osb-registration-form input, .osb-registration-form select, .osb-registration-form textarea').on('change blur', function() {
            autoSaveFormData();
        });

        // Load saved form data
        loadSavedFormData();
    }

    /**
     * Initialize document upload functionality
     */
    function initDocumentUpload() {
        // Drag and drop upload
        $('.osb-upload-area').on('dragover', function(e) {
            e.preventDefault();
            $(this).addClass('dragover');
        });

        $('.osb-upload-area').on('dragleave', function(e) {
            e.preventDefault();
            $(this).removeClass('dragover');
        });

        $('.osb-upload-area').on('drop', function(e) {
            e.preventDefault();
            $(this).removeClass('dragover');

            const files = e.originalEvent.dataTransfer.files;
            handleFileUpload(files, $(this));
        });

        // Click to upload
        $('.osb-upload-area').on('click', function() {
            $(this).find('input[type="file"]').click();
        });

        // File input change
        $('.osb-upload-area input[type="file"]').on('change', function() {
            handleFileUpload(this.files, $(this).closest('.osb-upload-area'));
        });

        // File removal
        $(document).on('click', '.osb-file-remove', function() {
            $(this).closest('.osb-file-item').remove();
            updateFileList();
        });
    }

    /**
     * Initialize form validation
     */
    function initFormValidation() {
        // Real-time validation
        $('.osb-registration-form input[required], .osb-registration-form select[required]').on('blur', function() {
            validateField($(this));
        });

        // Email validation
        $('.osb-registration-form input[type="email"]').on('blur', function() {
            validateEmail($(this));
        });

        // Phone validation
        $('.osb-registration-form input[type="tel"]').on('blur', function() {
            validatePhone($(this));
        });

        // Age calculation
        $('.osb-registration-form input[name*="birth_date"]').on('change', function() {
            calculateAge($(this));
        });
    }

    /**
     * Initialize progress tracking
     */
    function initProgressTracking() {
        updateProgressBar();
        updateStepIndicators();
    }

    /**
     * Validate a single form step
     */
    function validateStep(step) {
        let isValid = true;
        const requiredFields = step.find('input[required], select[required], textarea[required]');

        requiredFields.each(function() {
            if (!validateField($(this))) {
                isValid = false;
            }
        });

        return isValid;
    }

    /**
     * Validate all form steps
     */
    function validateAllSteps() {
        let isValid = true;
        $('.osb-form-step').each(function() {
            if (!validateStep($(this))) {
                isValid = false;
            }
        });
        return isValid;
    }

    /**
     * Validate individual field
     */
    function validateField(field) {
        const value = field.val().trim();
        const fieldType = field.attr('type');
        let isValid = true;
        let errorMessage = '';

        // Remove existing error styling
        field.removeClass('error');
        field.siblings('.error-message').remove();

        // Required field validation
        if (field.prop('required') && !value) {
            isValid = false;
            errorMessage = 'This field is required.';
        }

        // Email validation
        if (fieldType === 'email' && value && !validateEmailFormat(value)) {
            isValid = false;
            errorMessage = 'Please enter a valid email address.';
        }

        // Phone validation
        if (fieldType === 'tel' && value && !validatePhoneFormat(value)) {
            isValid = false;
            errorMessage = 'Please enter a valid phone number.';
        }

        // Display error if invalid
        if (!isValid) {
            field.addClass('error');
            field.after('<span class="error-message" style="color: #e74c3c; font-size: 12px; display: block; margin-top: 5px;">' + errorMessage + '</span>');
        }

        return isValid;
    }

    /**
     * Validate email format
     */
    function validateEmail(field) {
        const email = field.val().trim();
        const isValid = validateEmailFormat(email);

        if (!isValid && email) {
            field.addClass('error');
            field.siblings('.error-message').remove();
            field.after('<span class="error-message" style="color: #e74c3c; font-size: 12px; display: block; margin-top: 5px;">Please enter a valid email address.</span>');
        } else {
            field.removeClass('error');
            field.siblings('.error-message').remove();
        }

        return isValid;
    }

    /**
     * Validate phone format
     */
    function validatePhone(field) {
        const phone = field.val().trim();
        const isValid = validatePhoneFormat(phone);

        if (!isValid && phone) {
            field.addClass('error');
            field.siblings('.error-message').remove();
            field.after('<span class="error-message" style="color: #e74c3c; font-size: 12px; display: block; margin-top: 5px;">Please enter a valid phone number.</span>');
        } else {
            field.removeClass('error');
            field.siblings('.error-message').remove();
        }

        return isValid;
    }

    /**
     * Calculate age from birth date
     */
    function calculateAge(field) {
        const birthDate = new Date(field.val());
        const today = new Date();
        let age = today.getFullYear() - birthDate.getFullYear();
        const monthDiff = today.getMonth() - birthDate.getMonth();

        if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
            age--;
        }

        const ageField = field.closest('.osb-form-section').find('input[name*="age"]');
        if (ageField.length) {
            ageField.val(age);
        }
    }

    /**
     * Handle file upload
     */
    function handleFileUpload(files, uploadArea) {
        const allowedTypes = ['image/jpeg', 'image/png', 'application/pdf'];
        const maxSize = 5 * 1024 * 1024; // 5MB

        Array.from(files).forEach(function(file) {
            // Validate file type
            if (!allowedTypes.includes(file.type)) {
                showMessage('Invalid file type. Please upload JPG, PNG, or PDF files only.', 'error');
                return;
            }

            // Validate file size
            if (file.size > maxSize) {
                showMessage('File size too large. Please upload files smaller than 5MB.', 'error');
                return;
            }

            // Add file to list
            addFileToList(file, uploadArea);

            // Upload file
            uploadFile(file, uploadArea);
        });
    }

    /**
     * Add file to the display list
     */
    function addFileToList(file, uploadArea) {
        const fileList = uploadArea.siblings('.osb-file-list');
        const fileItem = $('<div class="osb-file-item">' +
            '<div class="osb-file-info">' +
            '<div class="osb-file-name">' + file.name + '</div>' +
            '<div class="osb-file-size">' + formatFileSize(file.size) + '</div>' +
            '</div>' +
            '<div class="osb-file-status">Uploading...</div>' +
            '<button type="button" class="osb-file-remove">Remove</button>' +
            '</div>');

        fileList.append(fileItem);
    }

    /**
     * Upload file via AJAX
     */
    function uploadFile(file, uploadArea) {
        const formData = new FormData();
        formData.append('document', file);
        formData.append('action', 'osb_upload_file');
        formData.append('upload_type', 'documents');
        formData.append('nonce', osbPublic.nonce);

        $.ajax({
            url: osbPublic.ajaxUrl,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    uploadArea.siblings('.osb-file-list').find('.osb-file-item:last .osb-file-status').text('Uploaded');
                    showMessage('File uploaded successfully!', 'success');
                } else {
                    uploadArea.siblings('.osb-file-list').find('.osb-file-item:last .osb-file-status').text('Failed');
                    showMessage('Upload failed: ' + response.data, 'error');
                }
            },
            error: function() {
                uploadArea.siblings('.osb-file-list').find('.osb-file-item:last .osb-file-status').text('Failed');
                showMessage('Upload failed. Please try again.', 'error');
            }
        });
    }

    /**
     * Submit registration form
     */
    function submitRegistrationForm(form) {
        const submitBtn = form.find('button[type="submit"]');
        const originalText = submitBtn.text();

        // Disable submit button and show loading
        submitBtn.prop('disabled', true).text('Submitting...');
        form.addClass('osb-loading');

        const formData = new FormData(form[0]);
        formData.append('action', 'osb_frontend_ajax');
        formData.append('sub_action', 'submit_registration');
        formData.append('nonce', osbPublic.nonce);

        $.ajax({
            url: osbPublic.ajaxUrl,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    showMessage('Registration submitted successfully! You will receive a confirmation email shortly.', 'success');
                    form[0].reset();
                    clearSavedFormData();

                    // Redirect or show success page
                    if (response.data.redirect_url) {
                        setTimeout(function() {
                            window.location.href = response.data.redirect_url;
                        }, 2000);
                    }
                } else {
                    showMessage('Registration failed: ' + response.data, 'error');
                }
            },
            error: function() {
                showMessage('Registration failed. Please try again later.', 'error');
            },
            complete: function() {
                submitBtn.prop('disabled', false).text(originalText);
                form.removeClass('osb-loading');
            }
        });
    }

    /**
     * Auto-save form data to localStorage
     */
    function autoSaveFormData() {
        const formData = {};
        $('.osb-registration-form').find('input, select, textarea').each(function() {
            const field = $(this);
            const name = field.attr('name');
            if (name && field.attr('type') !== 'file') {
                formData[name] = field.val();
            }
        });
        localStorage.setItem('osb_form_data', JSON.stringify(formData));
    }

    /**
     * Load saved form data from localStorage
     */
    function loadSavedFormData() {
        const savedData = localStorage.getItem('osb_form_data');
        if (savedData) {
            const formData = JSON.parse(savedData);
            Object.keys(formData).forEach(function(name) {
                const field = $('.osb-registration-form').find('[name="' + name + '"]');
                if (field.length) {
                    field.val(formData[name]);
                }
            });
        }
    }

    /**
     * Clear saved form data
     */
    function clearSavedFormData() {
        localStorage.removeItem('osb_form_data');
    }

    /**
     * Update progress bar
     */
    function updateProgressBar() {
        const totalSteps = $('.osb-form-step').length;
        const currentStepIndex = $('.osb-form-step:visible').index('.osb-form-step');
        const progress = ((currentStepIndex + 1) / totalSteps) * 100;

        $('.osb-progress-bar').css('width', progress + '%');
    }

    /**
     * Update step indicators
     */
    function updateStepIndicators() {
        const currentStepIndex = $('.osb-form-step:visible').index('.osb-form-step');

        $('.osb-step').each(function(index) {
            const step = $(this);
            step.removeClass('active completed');

            if (index < currentStepIndex) {
                step.addClass('completed');
            } else if (index === currentStepIndex) {
                step.addClass('active');
            }
        });
    }

    /**
     * Show message to user
     */
    function showMessage(message, type) {
        type = type || 'info';
        const messageDiv = $('<div class="osb-message osb-message-' + type + '">' + message + '</div>');

        // Remove existing messages
        $('.osb-message').remove();

        // Add new message
        $('.osb-registration-form').prepend(messageDiv);

        // Auto-remove after 5 seconds
        setTimeout(function() {
            messageDiv.fadeOut(function() {
                $(this).remove();
            });
        }, 5000);

        // Scroll to message
        $('html, body').animate({
            scrollTop: messageDiv.offset().top - 20
        }, 300);
    }

    /**
     * Utility functions
     */
    function validateEmailFormat(email) {
        const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return regex.test(email);
    }

    function validatePhoneFormat(phone) {
        const regex = /^[\+]?[1-9][\d]{0,15}$/;
        return regex.test(phone.replace(/\s+/g, ''));
    }

    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    function updateFileList() {
        // Update file input with current files
        // This would need more complex implementation for proper file management
    }

})(jQuery);
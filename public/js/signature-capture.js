/**
 * Signature Capture and Form Enhancement for Enhanced EOI Form
 * Mobile-optimized signature pad with form validation and auto-save
 */

class OSBSignatureCapture {
    constructor() {
        this.canvas = null;
        this.ctx = null;
        this.isDrawing = false;
        this.hasSignature = false;
        this.points = [];
        this.autoSaveInterval = null;
        this.formStartTime = null;
        this.touchIdentifier = null;

        this.init();
    }

    init() {
        this.setupCanvas();
        this.setupEventListeners();
        this.initFormValidation();
        this.startAutoSave();
        this.updateProgress();
        this.formStartTime = Date.now();
    }

    setupCanvas() {
        this.canvas = document.getElementById('osb-signature-pad');
        if (!this.canvas) return;

        this.ctx = this.canvas.getContext('2d');
        this.resizeCanvas();
        this.setupCanvasStyles();

        // Resize canvas on window resize
        window.addEventListener('resize', () => this.resizeCanvas());
    }

    resizeCanvas() {
        const container = this.canvas.parentElement;
        const rect = container.getBoundingClientRect();

        // Set canvas size to container size
        this.canvas.width = rect.width;
        this.canvas.height = Math.max(200, rect.width * 0.3); // Responsive height

        this.setupCanvasStyles();
    }

    setupCanvasStyles() {
        this.ctx.strokeStyle = '#2c3e50';
        this.ctx.lineWidth = 2;
        this.ctx.lineCap = 'round';
        this.ctx.lineJoin = 'round';
    }

    setupEventListeners() {
        if (!this.canvas) return;

        // Mouse events
        this.canvas.addEventListener('mousedown', (e) => this.startDrawing(e));
        this.canvas.addEventListener('mousemove', (e) => this.draw(e));
        this.canvas.addEventListener('mouseup', () => this.stopDrawing());
        this.canvas.addEventListener('mouseout', () => this.stopDrawing());

        // Touch events for mobile
        this.canvas.addEventListener('touchstart', (e) => this.startDrawing(e), { passive: false });
        this.canvas.addEventListener('touchmove', (e) => this.draw(e), { passive: false });
        this.canvas.addEventListener('touchend', (e) => this.stopDrawing(e), { passive: false });
        this.canvas.addEventListener('touchcancel', () => this.stopDrawing());

        // Clear signature button
        const clearBtn = document.getElementById('osb-clear-signature');
        if (clearBtn) {
            clearBtn.addEventListener('click', () => this.clearSignature());
        }

        // Form submission
        const form = document.getElementById('osb-enhanced-eoi-form');
        if (form) {
            form.addEventListener('submit', (e) => this.handleFormSubmit(e));
        }

        // Hide overlay on first interaction
        this.canvas.addEventListener('mousedown', () => this.hideOverlay(), { once: true });
        this.canvas.addEventListener('touchstart', () => this.hideOverlay(), { once: true });
    }

    hideOverlay() {
        const overlay = document.getElementById('osb-signature-overlay');
        if (overlay) {
            overlay.style.display = 'none';
        }
    }

    getEventPosition(e) {
        const rect = this.canvas.getBoundingClientRect();
        const scaleX = this.canvas.width / rect.width;
        const scaleY = this.canvas.height / rect.height;

        let clientX, clientY;

        if (e.type.startsWith('touch')) {
            e.preventDefault();
            if (e.touches && e.touches.length > 0) {
                clientX = e.touches[0].clientX;
                clientY = e.touches[0].clientY;
                this.touchIdentifier = e.touches[0].identifier;
            } else if (e.changedTouches && e.changedTouches.length > 0) {
                clientX = e.changedTouches[0].clientX;
                clientY = e.changedTouches[0].clientY;
            } else {
                return null;
            }
        } else {
            clientX = e.clientX;
            clientY = e.clientY;
        }

        return {
            x: (clientX - rect.left) * scaleX,
            y: (clientY - rect.top) * scaleY
        };
    }

    startDrawing(e) {
        const pos = this.getEventPosition(e);
        if (!pos) return;

        this.isDrawing = true;
        this.ctx.beginPath();
        this.ctx.moveTo(pos.x, pos.y);

        this.points.push({ x: pos.x, y: pos.y, type: 'start' });
    }

    draw(e) {
        if (!this.isDrawing) return;

        const pos = this.getEventPosition(e);
        if (!pos) return;

        // Filter out unwanted touch events
        if (e.type.startsWith('touch') && e.touches && e.touches.length > 0) {
            const currentTouch = Array.from(e.touches).find(touch => touch.identifier === this.touchIdentifier);
            if (!currentTouch) return;
        }

        this.ctx.lineTo(pos.x, pos.y);
        this.ctx.stroke();

        this.points.push({ x: pos.x, y: pos.y, type: 'draw' });

        if (!this.hasSignature) {
            this.hasSignature = true;
            this.updateSignatureStatus();
        }
    }

    stopDrawing(e) {
        if (!this.isDrawing) return;

        this.isDrawing = false;
        this.touchIdentifier = null;

        if (this.hasSignature) {
            this.saveSignatureData();
            this.validateForm();
        }
    }

    clearSignature() {
        this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);
        this.hasSignature = false;
        this.points = [];
        this.updateSignatureStatus();

        // Clear hidden input
        const signatureInput = document.getElementById('osb-signature-data');
        if (signatureInput) {
            signatureInput.value = '';
        }

        this.validateForm();
    }

    saveSignatureData() {
        if (!this.hasSignature) return;

        try {
            const dataURL = this.canvas.toDataURL('image/png', 0.8);
            const signatureInput = document.getElementById('osb-signature-data');

            if (signatureInput) {
                signatureInput.value = dataURL;
            }
        } catch (error) {
            console.error('Error saving signature:', error);
        }
    }

    updateSignatureStatus() {
        const statusElement = document.getElementById('osb-signature-status');
        if (!statusElement) return;

        if (this.hasSignature) {
            statusElement.innerHTML = '<span class="osb-status-text osb-status-success">✓ Signature captured</span>';
            statusElement.className = 'osb-signature-status osb-status-valid';
        } else {
            statusElement.innerHTML = '<span class="osb-status-text osb-status-error">Signature required</span>';
            statusElement.className = 'osb-signature-status osb-status-invalid';
        }
    }

    // Form Validation System
    initFormValidation() {
        const form = document.getElementById('osb-enhanced-eoi-form');
        if (!form) return;

        // Add real-time validation to all form fields
        const inputs = form.querySelectorAll('input, select, textarea');
        inputs.forEach(input => {
            input.addEventListener('blur', () => this.validateField(input));
            input.addEventListener('input', () => {
                if (input.classList.contains('osb-invalid')) {
                    this.validateField(input);
                }
                this.updateProgress();
            });
            input.addEventListener('change', () => {
                this.validateField(input);
                this.updateProgress();
            });
        });
    }

    validateField(field) {
        const validation = field.getAttribute('data-validation');
        if (!validation) return true;

        const rules = validation.split('|');
        let isValid = true;
        let errorMessage = '';

        for (const rule of rules) {
            const [ruleName, ruleValue] = rule.split(':');

            switch (ruleName) {
                case 'required':
                    if (!field.value.trim()) {
                        isValid = false;
                        errorMessage = 'This field is required.';
                    }
                    break;

                case 'email':
                    if (field.value && !this.isValidEmail(field.value)) {
                        isValid = false;
                        errorMessage = 'Please enter a valid email address.';
                    }
                    break;

                case 'phone':
                    if (field.value && !this.isValidPhone(field.value)) {
                        isValid = false;
                        errorMessage = 'Please enter a valid phone number.';
                    }
                    break;

                case 'min':
                    if (field.value && field.value.length < parseInt(ruleValue)) {
                        isValid = false;
                        errorMessage = `Minimum ${ruleValue} characters required.`;
                    }
                    break;

                case 'year':
                    if (field.value && (isNaN(field.value) || field.value < 1800 || field.value > new Date().getFullYear())) {
                        isValid = false;
                        errorMessage = 'Please enter a valid year.';
                    }
                    break;
            }

            if (!isValid) break;
        }

        this.showFieldValidation(field, isValid, errorMessage);
        return isValid;
    }

    showFieldValidation(field, isValid, errorMessage) {
        const messageElement = field.parentElement.querySelector('.osb-validation-message');

        if (isValid) {
            field.classList.remove('osb-invalid');
            field.classList.add('osb-valid');
            if (messageElement) {
                messageElement.textContent = '';
                messageElement.style.display = 'none';
            }
        } else {
            field.classList.remove('osb-valid');
            field.classList.add('osb-invalid');
            if (messageElement) {
                messageElement.textContent = errorMessage;
                messageElement.style.display = 'block';
            }
        }
    }

    isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    isValidPhone(phone) {
        const phoneRegex = /^[\+]?[\d\s\-\(\)]{10,}$/;
        return phoneRegex.test(phone.replace(/\s/g, ''));
    }

    validateForm() {
        const form = document.getElementById('osb-enhanced-eoi-form');
        if (!form) return false;

        let isFormValid = true;

        // Validate all required fields
        const requiredFields = form.querySelectorAll('[data-validation*="required"]');
        requiredFields.forEach(field => {
            if (!this.validateField(field)) {
                isFormValid = false;
            }
        });

        // Validate signature
        if (!this.hasSignature) {
            isFormValid = false;
            const signatureValidation = document.getElementById('osb-signature-validation');
            if (signatureValidation) {
                signatureValidation.textContent = 'Digital signature is required.';
                signatureValidation.style.display = 'block';
            }
        } else {
            const signatureValidation = document.getElementById('osb-signature-validation');
            if (signatureValidation) {
                signatureValidation.style.display = 'none';
            }
        }

        // Update submit button state
        const submitBtn = document.getElementById('osb-submit-eoi');
        if (submitBtn) {
            submitBtn.disabled = !isFormValid;
        }

        return isFormValid;
    }

    // Progress Tracking
    updateProgress() {
        const form = document.getElementById('osb-enhanced-eoi-form');
        if (!form) return;

        const allFields = form.querySelectorAll('input[required], select[required], textarea[required]');
        const filledFields = Array.from(allFields).filter(field => {
            if (field.type === 'checkbox') {
                return field.checked;
            }
            return field.value.trim() !== '';
        });

        const signatureWeight = this.hasSignature ? 1 : 0;
        const progress = Math.round(((filledFields.length + signatureWeight) / (allFields.length + 1)) * 100);

        const progressFill = document.getElementById('osb-progress-fill');
        const progressText = document.getElementById('osb-progress-text');

        if (progressFill) {
            progressFill.style.width = progress + '%';
        }
        if (progressText) {
            progressText.textContent = progress + '% Complete';
        }
    }

    // Auto-save functionality
    startAutoSave() {
        this.autoSaveInterval = setInterval(() => {
            this.autoSaveForm();
        }, 30000); // Auto-save every 30 seconds
    }

    autoSaveForm() {
        const form = document.getElementById('osb-enhanced-eoi-form');
        if (!form) return;

        const formData = new FormData(form);
        const data = {};

        for (let [key, value] of formData.entries()) {
            if (key !== 'osb_eoi_nonce' && key !== 'action') {
                data[key] = value;
            }
        }

        // Add signature data if available
        if (this.hasSignature) {
            data.digital_signature = this.canvas.toDataURL('image/png', 0.8);
        }

        // Send auto-save request
        fetch(osb_ajax_object.ajax_url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                action: 'osb_autosave_eoi',
                nonce: document.querySelector('[name="osb_eoi_nonce"]')?.value || '',
                form_data: JSON.stringify(data)
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                this.showAutoSaveNotification();
            }
        })
        .catch(error => {
            console.error('Auto-save failed:', error);
        });
    }

    showAutoSaveNotification() {
        const notification = document.getElementById('osb-autosave-notification');
        if (notification) {
            notification.style.display = 'flex';
            setTimeout(() => {
                notification.style.display = 'none';
            }, 3000);
        }
    }

    // Form submission
    handleFormSubmit(e) {
        e.preventDefault();

        if (!this.validateForm()) {
            this.scrollToFirstError();
            return false;
        }

        const submitBtn = document.getElementById('osb-submit-eoi');
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.querySelector('.osb-btn-text').style.display = 'none';
            submitBtn.querySelector('.osb-btn-loading').style.display = 'inline-flex';
        }

        // Add form completion time
        const completionTime = Math.round((Date.now() - this.formStartTime) / 1000);
        const form = e.target;

        let completionTimeInput = form.querySelector('[name="form_completion_time"]');
        if (!completionTimeInput) {
            completionTimeInput = document.createElement('input');
            completionTimeInput.type = 'hidden';
            completionTimeInput.name = 'form_completion_time';
            form.appendChild(completionTimeInput);
        }
        completionTimeInput.value = completionTime;

        // Add device info
        let deviceInfoInput = form.querySelector('[name="device_info"]');
        if (!deviceInfoInput) {
            deviceInfoInput = document.createElement('input');
            deviceInfoInput.type = 'hidden';
            deviceInfoInput.name = 'device_info';
            form.appendChild(deviceInfoInput);
        }
        deviceInfoInput.value = JSON.stringify({
            userAgent: navigator.userAgent,
            platform: navigator.platform,
            screen: `${screen.width}x${screen.height}`,
            timestamp: new Date().toISOString()
        });

        // Execute reCAPTCHA
        if (typeof grecaptcha !== 'undefined') {
            grecaptcha.execute();
        } else {
            this.submitForm(form);
        }
    }

    submitForm(form) {
        const formData = new FormData(form);

        fetch(osb_ajax_object.ajax_url, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                this.handleSubmitSuccess(data);
            } else {
                this.handleSubmitError(data.data || 'Submission failed. Please try again.');
            }
        })
        .catch(error => {
            console.error('Submit error:', error);
            this.handleSubmitError('Network error. Please check your connection and try again.');
        });
    }

    handleSubmitSuccess(data) {
        // Clear auto-save interval
        if (this.autoSaveInterval) {
            clearInterval(this.autoSaveInterval);
        }

        // Show success message and redirect or reload
        alert('Expression of Interest submitted successfully! You will receive a confirmation email shortly.');

        if (data.redirect_url) {
            window.location.href = data.redirect_url;
        } else {
            window.location.reload();
        }
    }

    handleSubmitError(message) {
        const submitBtn = document.getElementById('osb-submit-eoi');
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.querySelector('.osb-btn-text').style.display = 'inline';
            submitBtn.querySelector('.osb-btn-loading').style.display = 'none';
        }

        alert('Error: ' + message);
    }

    scrollToFirstError() {
        const firstError = document.querySelector('.osb-invalid, .osb-status-invalid');
        if (firstError) {
            firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
            firstError.focus();
        }
    }

    // Cleanup
    destroy() {
        if (this.autoSaveInterval) {
            clearInterval(this.autoSaveInterval);
        }
    }
}

// reCAPTCHA callback
function onRecaptchaVerified(token) {
    const form = document.getElementById('osb-enhanced-eoi-form');
    if (form && window.osbSignature) {
        window.osbSignature.submitForm(form);
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('osb-signature-pad')) {
        window.osbSignature = new OSBSignatureCapture();
    }
});

// Cleanup on page unload
window.addEventListener('beforeunload', function() {
    if (window.osbSignature) {
        window.osbSignature.destroy();
    }
});
/**
 * LearnHub - Main JavaScript File
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize components that are actually used
    initFormValidation();
    initDateTime();
});

/**
 * Form Validation
 */
function initFormValidation() {
    const forms = document.querySelectorAll('.needs-validation');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!form.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    });

    // Password strength indicator
    const passwordInput = document.getElementById('password');
    const strengthIndicator = document.getElementById('password-strength');
    
    if (passwordInput && strengthIndicator) {
        passwordInput.addEventListener('input', function() {
            const strength = checkPasswordStrength(this.value);
            updateStrengthIndicator(strength, strengthIndicator);
        });
    }

    // Password confirmation match
    const confirmPassword = document.getElementById('confirm_password');
    if (confirmPassword && passwordInput) {
        confirmPassword.addEventListener('input', function() {
            if (this.value !== passwordInput.value) {
                this.setCustomValidity('Passwords do not match');
            } else {
                this.setCustomValidity('');
            }
        });
    }
}

/**
 * Check Password Strength
 */
function checkPasswordStrength(password) {
    let strength = 0;
    
    if (password.length >= 8) strength++;
    if (password.match(/[a-z]/)) strength++;
    if (password.match(/[A-Z]/)) strength++;
    if (password.match(/[0-9]/)) strength++;
    if (password.match(/[^a-zA-Z0-9]/)) strength++;
    
    return strength;
}

/**
 * Update Strength Indicator
 */
function updateStrengthIndicator(strength, indicator) {
    const labels = ['Very Weak', 'Weak', 'Fair', 'Good', 'Strong'];
    const colors = ['#ef4444', '#f59e0b', '#eab308', '#22c55e', '#10b981'];
    
    indicator.textContent = labels[strength - 1] || '';
    indicator.style.color = colors[strength - 1] || '#6b7280';
}

/**
 * Initialize Date and Time Updates
 */
function initDateTime() {
    const dateElement = document.getElementById('current-date');
    const timeElement = document.getElementById('current-time');
    
    if (dateElement && timeElement) {
        updateDateTime();
        setInterval(updateDateTime, 1000);
    }
}

/**
 * Update Date and Time in Footer
 */
function updateDateTime() {
    const now = new Date();
    const dateOptions = { 
        weekday: 'long', 
        year: 'numeric', 
        month: 'short', 
        day: 'numeric'
    };
    const timeOptions = { 
        hour: '2-digit', 
        minute: '2-digit', 
        second: '2-digit',
        hour12: true
    };
    
    const dateElement = document.getElementById('current-date');
    const timeElement = document.getElementById('current-time');
    
    if (dateElement && timeElement) {
        dateElement.textContent = now.toLocaleString('en-US', dateOptions);
        timeElement.textContent = now.toLocaleString('en-US', timeOptions);
    }
}

// Immediately start date/time updates (fallback)
(function() {
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initDateTime);
    } else {
        initDateTime();
    }
})();

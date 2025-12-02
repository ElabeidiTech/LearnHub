/**
 * Initialize all functions when DOM is fully loaded
 */
document.addEventListener('DOMContentLoaded', function() {
    initFormValidation();
    initDateTime();
    initCounterAnimation();
    cloneUniversityLogos();
});

/**
 * Initialize form validation for all forms with .needs-validation class
 * Also handles password strength checking and confirmation matching
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

    const passwordInput = document.getElementById('password');
    const strengthIndicator = document.getElementById('password-strength');
    
    if (passwordInput && strengthIndicator) {
        passwordInput.addEventListener('input', function() {
            const strength = checkPasswordStrength(this.value);
            updateStrengthIndicator(strength, strengthIndicator);
        });
    }

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
 * Check password strength based on various criteria
 * @param {string} password - The password to check
 * @return {number} Strength score from 0-5
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
 * Update the password strength indicator UI element
 * @param {number} strength - Strength score (0-5)
 * @param {HTMLElement} indicator - The indicator element to update
 */
function updateStrengthIndicator(strength, indicator) {
    const labels = ['Very Weak', 'Weak', 'Fair', 'Good', 'Strong'];
    const colors = ['#ef4444', '#f59e0b', '#eab308', '#22c55e', '#10b981'];
    
    indicator.textContent = labels[strength - 1] || '';
    indicator.style.color = colors[strength - 1] || '#6b7280';
}

/**
 * Initialize date and time display with real-time updates
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
 * Update the current date and time display
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

(function() {
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initDateTime);
    } else {
        initDateTime();
    }
})();

function initCounterAnimation() {
    const counters = document.querySelectorAll('.stat-number');
    
    const observerOptions = {
        threshold: 0.5,
        rootMargin: '0px'
    };
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting && !entry.target.classList.contains('counted')) {
                animateCounter(entry.target);
                entry.target.classList.add('counted');
            }
        });
    }, observerOptions);
    
    counters.forEach(counter => {
        observer.observe(counter);
    });
}

/**
 * Animate a counter element from 0 to its target value
 * @param {HTMLElement} element - The counter element with data-target attribute
 */
function animateCounter(element) {
    const target = parseInt(element.getAttribute('data-target'));
    const duration = 2000;
    const increment = target / (duration / 16);
    let current = 0;
    
    const updateCounter = () => {
        current += increment;
        
        if (current < target) {
            element.textContent = Math.floor(current).toLocaleString() + '+';
            requestAnimationFrame(updateCounter);
        } else {
            element.textContent = target.toLocaleString() + '+';
        }
    };
    
    updateCounter();
}

/**
 * Clone university logo elements to create infinite scrolling carousel effect
 */
function cloneUniversityLogos() {
    const track = document.querySelector('.university-logos-track');
    if (track) {
        const items = Array.from(track.children);
        items.forEach(item => {
            const clone = item.cloneNode(true);
            track.appendChild(clone);
        });
    }
}

/**
 * Show a confirmation modal with custom message and callback
 * @param {string} message - The confirmation message
 * @param {Function} onConfirm - Callback function to execute on confirmation
 * @param {string} title - Modal title (default: 'Confirm Action')
 */
function showConfirm(message, onConfirm, title = 'Confirm Action') {
    if (typeof bootstrap === 'undefined') {
        if (confirm(message)) {
            if (typeof onConfirm === 'function') {
                onConfirm();
            }
        }
        return;
    }
    
    const modalElement = document.getElementById('confirmModal');
    if (!modalElement) {
        console.error('Confirm modal not found');
        if (confirm(message)) {
            if (typeof onConfirm === 'function') {
                onConfirm();
            }
        }
        return;
    }
    
    const modal = new bootstrap.Modal(modalElement);
    const modalTitle = document.getElementById('confirmModalTitle');
    const modalMessage = document.getElementById('confirmModalMessage');
    const confirmBtn = document.getElementById('confirmModalAction');
    
    if (modalTitle) modalTitle.textContent = title;
    if (modalMessage) modalMessage.textContent = message;
    
    // Remove previous event listeners
    const newConfirmBtn = confirmBtn.cloneNode(true);
    confirmBtn.parentNode.replaceChild(newConfirmBtn, confirmBtn);
    
    newConfirmBtn.addEventListener('click', function() {
        if (typeof onConfirm === 'function') {
            onConfirm();
        }
        modal.hide();
    });
    
    modal.show();
}

/**
 * Show an alert modal with custom message
 * @param {string} message - The alert message
 * @param {Function} onOk - Optional callback function when OK is clicked
 * @param {string} title - Modal title (default: 'Notice')
 * @param {string} type - Alert type: info, warning, danger, success (default: 'info')
 */
function showAlert(message, onOk = null, title = 'Notice', type = 'info') {
    if (typeof bootstrap === 'undefined') {
        alert(message);
        if (typeof onOk === 'function') {
            onOk();
        }
        return;
    }
    
    const modalElement = document.getElementById('alertModal');
    if (!modalElement) {
        console.error('Alert modal not found');
        alert(message);
        if (typeof onOk === 'function') {
            onOk();
        }
        return;
    }
    
    const modal = new bootstrap.Modal(modalElement);
    const modalTitle = document.getElementById('alertModalTitle');
    const modalMessage = document.getElementById('alertModalMessage');
    const modalIcon = document.getElementById('alertModalIcon');
    const okBtn = document.getElementById('alertModalOk');
    
    if (modalTitle) modalTitle.textContent = title;
    if (modalMessage) modalMessage.textContent = message;
    
    const iconMap = {
        'info': 'fa-info-circle text-info',
        'warning': 'fa-exclamation-triangle text-warning',
        'danger': 'fa-exclamation-circle text-danger',
        'success': 'fa-check-circle text-success'
    };
    
    if (modalIcon) {
        modalIcon.className = 'fas me-2 ' + (iconMap[type] || iconMap['info']);
    }
    
    const newOkBtn = okBtn.cloneNode(true);
    okBtn.parentNode.replaceChild(newOkBtn, okBtn);
    
    if (typeof onOk === 'function') {
        newOkBtn.addEventListener('click', function() {
            modal.hide();
            onOk();
        });
    }
    
    modal.show();
}

/**
 * Toggle password visibility for a given input field
 * @param {string} fieldId - The ID of the password input field
 * @param {HTMLElement} button - The toggle button element
 */
function togglePassword(fieldId, button) {
    const field = document.getElementById(fieldId);
    const icon = button.querySelector('i');
    
    if (field.type === 'password') {
        field.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        field.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

/**
 * Handle file input change event and display selected filename
 * @param {Event} event - The change event
 * @param {string} displayElementId - ID of element to display filename in
 */
function handleFileInputChange(event, displayElementId) {
    const fileInput = event.target;
    const displayElement = document.getElementById(displayElementId);
    
    if (fileInput.files.length > 0) {
        displayElement.textContent = '✓ Selected: ' + fileInput.files[0].name;
    }
}

/**
 * Setup drag and drop file upload functionality
 * @param {string} uploadAreaId - ID of the drag/drop area element
 * @param {string} fileInputId - ID of the file input element
 * @param {string} displayElementId - ID of element to display filename in
 */
function setupDragAndDrop(uploadAreaId, fileInputId, displayElementId) {
    const uploadArea = document.getElementById(uploadAreaId);
    const fileInput = document.getElementById(fileInputId);
    const displayElement = document.getElementById(displayElementId);
    
    if (!uploadArea || !fileInput) return;
    
    uploadArea.addEventListener('dragover', (e) => {
        e.preventDefault();
        uploadArea.classList.add('border-primary');
    });
    
    uploadArea.addEventListener('dragleave', () => {
        uploadArea.classList.remove('border-primary');
    });
    
    uploadArea.addEventListener('drop', (e) => {
        e.preventDefault();
        uploadArea.classList.remove('border-primary');
        fileInput.files = e.dataTransfer.files;
        if (displayElement && e.dataTransfer.files.length > 0) {
            displayElement.textContent = '✓ Selected: ' + e.dataTransfer.files[0].name;
        }
    });
}

/**
 * Initialize profile picture upload button visibility
 */
function initProfilePictureUpload() {
    const profilePictureInput = document.getElementById('profilePictureInput');
    const uploadBtn = document.getElementById('uploadBtn');
    
    if (profilePictureInput && uploadBtn) {
        profilePictureInput.addEventListener('change', function() {
            if (this.files.length > 0) {
                uploadBtn.classList.remove('d-none');
            }
        });
    }
}

/**
 * Add a new question card to the quiz creation form
 */
function addQuestion() {
    const container = document.getElementById('questionsContainer');
    if (!container) return;
    
    const questionCount = container.querySelectorAll('.question-card').length + 1;
    const template = document.getElementById('questionTemplate');
    if (!template) return;
    
    const clone = template.content.cloneNode(true);
    const card = clone.querySelector('.question-card');
    
    card.querySelector('.q-number').textContent = questionCount;
    card.dataset.questionId = questionCount;
    
    const idx = questionCount - 1;
    card.querySelector('.question-text').name = `questions[${idx}][question]`;
    card.querySelector('.points-input').name = `questions[${idx}][points]`;
    card.querySelector('.option-a').name = `questions[${idx}][option_a]`;
    card.querySelector('.option-b').name = `questions[${idx}][option_b]`;
    card.querySelector('.option-c').name = `questions[${idx}][option_c]`;
    card.querySelector('.option-d').name = `questions[${idx}][option_d]`;
    
    const radios = card.querySelectorAll('.correct-radio');
    const radioName = `correct_${questionCount}`;
    radios.forEach(radio => {
        radio.name = radioName;
        radio.addEventListener('change', function() {
            card.querySelector(`input[name=\"questions[${idx}][correct]\"]`)?.remove();
            const hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.name = `questions[${idx}][correct]`;
            hidden.value = this.value;
            card.appendChild(hidden);
        });
    });
    
    container.appendChild(card);
    updateQuestionCount();
}

/**
 * Remove a question card from the quiz form
 * @param {HTMLElement} btn - The remove button that was clicked
 */
function removeQuestion(btn) {
    btn.closest('.question-card').remove();
    
    document.querySelectorAll('.question-card').forEach((card, index) => {
        card.querySelector('.q-number').textContent = index + 1;
    });
    
    updateQuestionCount();
}

/**
 * Update the question count and total points display
 */
function updateQuestionCount() {
    const questionCountElement = document.getElementById('questionCount');
    if (!questionCountElement) return;
    
    const questionCount = document.querySelectorAll('.question-card').length;
    let totalPoints = 0;
    
    document.querySelectorAll('.points-input').forEach(input => {
        totalPoints += parseInt(input.value) || 0;
    });
    
    const questionsText = questionCountElement.dataset.questionsText || 'questions';
    const pointsText = questionCountElement.dataset.pointsText || 'points';
    
    questionCountElement.textContent = `${questionCount} ${questionsText} • ${totalPoints} ${pointsText}`;
}

/**
 * Initialize listener for points input changes to update total
 */
function initQuizPointsListener() {
    const container = document.getElementById('questionsContainer');
    if (!container) return;
    
    container.addEventListener('input', function(e) {
        if (e.target.classList.contains('points-input')) {
            updateQuestionCount();
        }
    });
}

/**
 * Initialize teacher approval modal buttons and data binding
 */
function initTeacherApprovalModal() {
    document.querySelectorAll('.approve-teacher-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const teacherId = this.dataset.teacherId;
            const teacherName = this.dataset.teacherName;
            
            const teacherIdInput = document.getElementById('approveTeacherId');
            const teacherNameSpan = document.getElementById('approveTeacherName');
            
            if (teacherIdInput) teacherIdInput.value = teacherId;
            if (teacherNameSpan) teacherNameSpan.textContent = teacherName;
            
            const modalElement = document.getElementById('approveModal');
            if (modalElement && typeof bootstrap !== 'undefined') {
                const modal = new bootstrap.Modal(modalElement);
                modal.show();
            }
        });
    });
}

/**
 * Toggle password visibility in the login modal
 */
function toggleModalPassword() {
    const password = document.getElementById('password');
    const icon = document.getElementById('toggleModalIcon');
    
    if (!password || !icon) return;
    
    if (password.type === 'password') {
        password.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        password.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

/**
 * Initialize additional features when DOM is loaded
 */
document.addEventListener('DOMContentLoaded', function() {
    initProfilePictureUpload();
    initQuizPointsListener();
    initTeacherApprovalModal();
});

/**
 * Export functions to global window object for inline event handlers
 */
window.showConfirm = showConfirm;
window.showAlert = showAlert;
window.togglePassword = togglePassword;
window.toggleModalPassword = toggleModalPassword;
window.addQuestion = addQuestion;
window.removeQuestion = removeQuestion;
window.updateQuestionCount = updateQuestionCount;
window.handleFileInputChange = handleFileInputChange;
window.setupDragAndDrop = setupDragAndDrop;

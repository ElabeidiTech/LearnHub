/**
 * LearnHub - Main JavaScript File
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize all components
    initAnimations();
    initFormValidation();
    initTooltips();
    initSearchFilter();
    initProgressTracking();
    initDateTime();
});

/**
 * Scroll Animations
 */
function initAnimations() {
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate__animated', 'animate__fadeInUp');
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);

    document.querySelectorAll('.animate-on-scroll').forEach(el => {
        observer.observe(el);
    });
}

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
 * Initialize Tooltips
 */
function initTooltips() {
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    tooltipTriggerList.forEach(el => new bootstrap.Tooltip(el));
}

/**
 * Search and Filter
 */
function initSearchFilter() {
    const searchInput = document.getElementById('course-search');
    const categoryFilter = document.getElementById('category-filter');
    const levelFilter = document.getElementById('level-filter');
    
    if (searchInput) {
        searchInput.addEventListener('input', debounce(filterCourses, 300));
    }
    
    if (categoryFilter) {
        categoryFilter.addEventListener('change', filterCourses);
    }
    
    if (levelFilter) {
        levelFilter.addEventListener('change', filterCourses);
    }
}

/**
 * Filter Courses
 */
function filterCourses() {
    const searchTerm = document.getElementById('course-search')?.value.toLowerCase() || '';
    const category = document.getElementById('category-filter')?.value || '';
    const level = document.getElementById('level-filter')?.value || '';
    
    document.querySelectorAll('.course-card').forEach(card => {
        const title = card.dataset.title?.toLowerCase() || '';
        const cardCategory = card.dataset.category || '';
        const cardLevel = card.dataset.level || '';
        
        const matchesSearch = title.includes(searchTerm);
        const matchesCategory = !category || cardCategory === category;
        const matchesLevel = !level || cardLevel === level;
        
        if (matchesSearch && matchesCategory && matchesLevel) {
            card.style.display = '';
            card.classList.add('animate__animated', 'animate__fadeIn');
        } else {
            card.style.display = 'none';
        }
    });
}

/**
 * Progress Tracking
 */
function initProgressTracking() {
    document.querySelectorAll('.mark-complete').forEach(btn => {
        btn.addEventListener('click', async function() {
            const lessonId = this.dataset.lessonId;
            const courseId = this.dataset.courseId;
            
            try {
                const response = await fetch('/learnhub/api/progress.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ lesson_id: lessonId, course_id: courseId })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    this.classList.remove('btn-primary');
                    this.classList.add('btn-success');
                    this.innerHTML = '<i class="fas fa-check me-2"></i>Completed';
                    this.disabled = true;
                    
                    // Update progress bar
                    const progressBar = document.querySelector('.course-progress');
                    if (progressBar) {
                        progressBar.style.width = data.progress + '%';
                        progressBar.setAttribute('aria-valuenow', data.progress);
                    }
                    
                    showToast('Lesson completed!', 'success');
                }
            } catch (error) {
                showToast('Error updating progress', 'error');
            }
        });
    });
}

/**
 * Show Toast Notification
 */
function showToast(message, type = 'info') {
    const toastContainer = document.querySelector('.toast-container') || createToastContainer();
    
    const toast = document.createElement('div');
    toast.className = `toast align-items-center text-white bg-${type === 'success' ? 'success' : type === 'error' ? 'danger' : 'primary'} border-0`;
    toast.setAttribute('role', 'alert');
    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">${message}</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    `;
    
    toastContainer.appendChild(toast);
    const bsToast = new bootstrap.Toast(toast);
    bsToast.show();
    
    toast.addEventListener('hidden.bs.toast', () => toast.remove());
}

/**
 * Create Toast Container
 */
function createToastContainer() {
    const container = document.createElement('div');
    container.className = 'toast-container position-fixed top-0 end-0 p-3';
    container.style.zIndex = '9999';
    document.body.appendChild(container);
    return container;
}

/**
 * Debounce Function
 */
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

/**
 * Confirm Delete
 */
function confirmDelete(message = 'Are you sure you want to delete this item?') {
    return confirm(message);
}

/**
 * Format Currency
 */
function formatCurrency(amount) {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(amount);
}

/**
 * Preview Image before upload
 */
function previewImage(input, previewElement) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.querySelector(previewElement).src = e.target.result;
        };
        reader.readAsDataURL(input.files[0]);
    }
}

/**
 * Enroll in Course
 */
async function enrollCourse(courseId) {
    try {
        const response = await fetch('/learnhub/api/enroll.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ course_id: courseId })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showToast('Successfully enrolled!', 'success');
            setTimeout(() => {
                window.location.href = `/learnhub/student/course.php?id=${courseId}`;
            }, 1000);
        } else {
            showToast(data.message || 'Enrollment failed', 'error');
        }
    } catch (error) {
        showToast('Error enrolling in course', 'error');
    }
}

/**
 * Star Rating Widget
 */
function initStarRating() {
    document.querySelectorAll('.star-rating').forEach(container => {
        const stars = container.querySelectorAll('.star');
        const input = container.querySelector('input[type="hidden"]');
        
        stars.forEach((star, index) => {
            star.addEventListener('click', () => {
                const rating = index + 1;
                input.value = rating;
                
                stars.forEach((s, i) => {
                    s.classList.toggle('active', i < rating);
                });
            });
            
            star.addEventListener('mouseenter', () => {
                stars.forEach((s, i) => {
                    s.classList.toggle('hover', i <= index);
                });
            });
            
            star.addEventListener('mouseleave', () => {
                stars.forEach(s => s.classList.remove('hover'));
            });
        });
    });
}

// Initialize star rating if present
document.addEventListener('DOMContentLoaded', initStarRating);

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
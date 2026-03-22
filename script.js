/**
 * TechLearn Platform - Main JavaScript
 * ====================================
 */

// Dark Mode Management
const ThemeManager = {
    init() {
        const themeToggle = document.getElementById('theme-toggle');
        if (!themeToggle) return;

        // Check for saved theme preference
        const savedTheme = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-theme', savedTheme);
        this.updateIcon(savedTheme);

        themeToggle.addEventListener('click', () => {
            const currentTheme = document.documentElement.getAttribute('data-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            
            document.documentElement.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            this.updateIcon(newTheme);
        });
    },

    updateIcon(theme) {
        const themeToggle = document.getElementById('theme-toggle');
        if (!themeToggle) return;

        themeToggle.innerHTML = theme === 'dark' 
            ? '<i data-lucide="sun"></i>' 
            : '<i data-lucide="moon"></i>';
        
        // Re-initialize Lucide icons
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    }
};

// Mobile Navigation
const MobileNav = {
    init() {
        const menuBtn = document.getElementById('mobile-menu-btn');
        const navLinks = document.getElementById('nav-links');
        
        if (!menuBtn || !navLinks) return;

        menuBtn.addEventListener('click', () => {
            navLinks.classList.toggle('active');
            const isOpen = navLinks.classList.contains('active');
            menuBtn.innerHTML = isOpen 
                ? '<i data-lucide="x"></i>' 
                : '<i data-lucide="menu"></i>';
            
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        });
    }
};

// Lesson Tabs
const LessonTabs = {
    init() {
        const tabs = document.querySelectorAll('.lesson-tab');
        const sections = document.querySelectorAll('.lesson-section');

        tabs.forEach(tab => {
            tab.addEventListener('click', () => {
                const target = tab.dataset.tab;

                // Update active tab
                tabs.forEach(t => t.classList.remove('active'));
                tab.classList.add('active');

                // Show target section
                sections.forEach(section => {
                    section.classList.remove('active');
                    if (section.id === target) {
                        section.classList.add('active');
                    }
                });
            });
        });
    }
};

// Progress Bar Animation
const ProgressAnimation = {
    init() {
        const progressBars = document.querySelectorAll('.progress-bar');
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const bar = entry.target;
                    const width = bar.dataset.width || bar.style.width;
                    bar.style.width = '0%';
                    setTimeout(() => {
                        bar.style.width = width;
                    }, 100);
                    observer.unobserve(bar);
                }
            });
        }, { threshold: 0.5 });

        progressBars.forEach(bar => observer.observe(bar));
    }
};

// Smooth Scroll
const SmoothScroll = {
    init() {
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    }
};

// Navbar Scroll Effect
const NavbarScroll = {
    init() {
        const navbar = document.querySelector('.navbar');
        if (!navbar) return;

        window.addEventListener('scroll', () => {
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });
    }
};

// Form Validation
const FormValidation = {
    init() {
        const forms = document.querySelectorAll('form[data-validate]');
        
        forms.forEach(form => {
            form.addEventListener('submit', (e) => {
                let isValid = true;
                const requiredFields = form.querySelectorAll('[required]');

                requiredFields.forEach(field => {
                    if (!field.value.trim()) {
                        isValid = false;
                        this.showError(field, 'This field is required');
                    } else {
                        this.clearError(field);
                    }
                });

                // Email validation
                const emailFields = form.querySelectorAll('input[type="email"]');
                emailFields.forEach(field => {
                    if (field.value && !this.isValidEmail(field.value)) {
                        isValid = false;
                        this.showError(field, 'Please enter a valid email');
                    }
                });

                // Password confirmation
                const passwordConfirm = form.querySelector('input[data-confirm]');
                if (passwordConfirm) {
                    const passwordField = document.querySelector(passwordConfirm.dataset.confirm);
                    if (passwordField && passwordField.value !== passwordConfirm.value) {
                        isValid = false;
                        this.showError(passwordConfirm, 'Passwords do not match');
                    }
                }

                if (!isValid) {
                    e.preventDefault();
                }
            });
        });
    },

    showError(field, message) {
        this.clearError(field);
        field.classList.add('error');
        const errorDiv = document.createElement('div');
        errorDiv.className = 'form-error';
        errorDiv.textContent = message;
        field.parentNode.appendChild(errorDiv);
    },

    clearError(field) {
        field.classList.remove('error');
        const error = field.parentNode.querySelector('.form-error');
        if (error) {
            error.remove();
        }
    },

    isValidEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }
};

// Code Copy Functionality
const CodeCopy = {
    init() {
        document.querySelectorAll('.code-block').forEach(block => {
            const copyBtn = document.createElement('button');
            copyBtn.className = 'code-copy-btn';
            copyBtn.innerHTML = '<i data-lucide="copy"></i>';
            copyBtn.title = 'Copy to clipboard';
            
            copyBtn.addEventListener('click', () => {
                const code = block.querySelector('code').textContent;
                navigator.clipboard.writeText(code).then(() => {
                    copyBtn.innerHTML = '<i data-lucide="check"></i>';
                    if (typeof lucide !== 'undefined') {
                        lucide.createIcons();
                    }
                    setTimeout(() => {
                        copyBtn.innerHTML = '<i data-lucide="copy"></i>';
                        if (typeof lucide !== 'undefined') {
                            lucide.createIcons();
                        }
                    }, 2000);
                });
            });

            block.style.position = 'relative';
            block.appendChild(copyBtn);
        });
    }
};

// Like Button Handler
const LikeButton = {
    init() {
        document.querySelectorAll('.like-btn').forEach(btn => {
            btn.addEventListener('click', async (e) => {
                e.preventDefault();
                const type = btn.dataset.type;
                const id = btn.dataset.id;
                
                try {
                    const response = await fetch(`ajax/like.php?type=${type}&id=${id}`);
                    const data = await response.json();
                    
                    if (data.success) {
                        btn.classList.toggle('liked', data.liked);
                        const countSpan = btn.querySelector('.like-count');
                        if (countSpan) {
                            countSpan.textContent = data.count;
                        }
                    }
                } catch (error) {
                    console.error('Error:', error);
                }
            });
        });
    }
};

// File Upload Preview
const FileUpload = {
    init() {
        document.querySelectorAll('input[type="file"][data-preview]').forEach(input => {
            const previewId = input.dataset.preview;
            const preview = document.getElementById(previewId);
            
            input.addEventListener('change', () => {
                const file = input.files[0];
                if (file && preview) {
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        preview.src = e.target.result;
                        preview.style.display = 'block';
                    };
                    reader.readAsDataURL(file);
                }
            });
        });
    }
};

// Modal Handler
const ModalHandler = {
    init() {
        // Open modal
        document.querySelectorAll('[data-modal]').forEach(btn => {
            btn.addEventListener('click', () => {
                const modalId = btn.dataset.modal;
                const modal = document.getElementById(modalId);
                if (modal) {
                    modal.classList.add('active');
                    document.body.style.overflow = 'hidden';
                }
            });
        });

        // Close modal
        document.querySelectorAll('.modal-close, .modal-overlay').forEach(el => {
            el.addEventListener('click', () => {
                const modal = el.closest('.modal');
                if (modal) {
                    modal.classList.remove('active');
                    document.body.style.overflow = '';
                }
            });
        });

        // Close on Escape
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                document.querySelectorAll('.modal.active').forEach(modal => {
                    modal.classList.remove('active');
                    document.body.style.overflow = '';
                });
            }
        });
    }
};

// Toast Notifications
const Toast = {
    container: null,

    init() {
        this.container = document.createElement('div');
        this.container.className = 'toast-container';
        document.body.appendChild(this.container);
    },

    show(message, type = 'info', duration = 3000) {
        if (!this.container) this.init();

        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        toast.innerHTML = `
            <span>${message}</span>
            <button class="toast-close">&times;</button>
        `;

        toast.querySelector('.toast-close').addEventListener('click', () => {
            toast.remove();
        });

        this.container.appendChild(toast);

        // Animate in
        requestAnimationFrame(() => {
            toast.classList.add('show');
        });

        // Auto remove
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        }, duration);
    }
};

// AJAX Form Submissions
const AjaxForms = {
    init() {
        document.querySelectorAll('form[data-ajax]').forEach(form => {
            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                
                const submitBtn = form.querySelector('[type="submit"]');
                const originalText = submitBtn?.textContent;
                
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.textContent = 'Loading...';
                }

                try {
                    const formData = new FormData(form);
                    const response = await fetch(form.action, {
                        method: form.method || 'POST',
                        body: formData
                    });

                    const data = await response.json();

                    if (data.success) {
                        Toast.show(data.message || 'Success!', 'success');
                        if (data.redirect) {
                            setTimeout(() => window.location.href = data.redirect, 1000);
                        }
                    } else {
                        Toast.show(data.message || 'An error occurred', 'error');
                    }
                } catch (error) {
                    Toast.show('Network error. Please try again.', 'error');
                } finally {
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.textContent = originalText;
                    }
                }
            });
        });
    }
};

// Initialize everything when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    // Initialize Lucide icons
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }

    // Initialize all modules
    ThemeManager.init();
    MobileNav.init();
    LessonTabs.init();
    ProgressAnimation.init();
    SmoothScroll.init();
    NavbarScroll.init();
    FormValidation.init();
    CodeCopy.init();
    LikeButton.init();
    FileUpload.init();
    ModalHandler.init();
    AjaxForms.init();

    // Add animation classes on scroll
    const animateOnScroll = () => {
        const elements = document.querySelectorAll('[data-animate]');
        
        elements.forEach(el => {
            const rect = el.getBoundingClientRect();
            const isVisible = rect.top < window.innerHeight - 100;
            
            if (isVisible) {
                el.classList.add('animate-fade-in');
            }
        });
    };

    window.addEventListener('scroll', animateOnScroll);
    animateOnScroll(); // Initial check
});

// Export for global access
window.TechLearn = {
    Toast,
    ThemeManager
};
// Admin Dashboard JavaScript with jQuery
$(document).ready(function() {
    // Sidebar toggle functionality
    const $sidebar = $('#sidebar');
    const $sidebarToggle = $('#sidebarToggle');
    const $mainContent = $('#mainContent');
    const $sidebarOverlay = $('#sidebarOverlay');
    
    // Toggle sidebar
    $sidebarToggle.on('click', function() {
        if (window.innerWidth <= 768) {
            // Mobile behavior
            $sidebar.toggleClass('show');
            $sidebarOverlay.toggleClass('show');
            $('body').toggleClass('sidebar-open');
        } else {
            // Desktop behavior
            $sidebar.toggleClass('collapsed');
            $mainContent.toggleClass('expanded');
            
            // Store preference
            localStorage.setItem('sidebarCollapsed', $sidebar.hasClass('collapsed'));
        }
        
        // Close all submenus when collapsing
        if ($sidebar.hasClass('collapsed')) {
            $('.nav-submenu').removeClass('show');
            $('.nav-arrow').removeClass('rotated');
        }
    });
    
    // Sidebar hover functionality for collapsed state
    $sidebar.on('mouseenter', function() {
        if ($sidebar.hasClass('collapsed')) {
            $sidebar.addClass('hover-expanded');
        }
    });
    
    $sidebar.on('mouseleave', function() {
        $sidebar.removeClass('hover-expanded');
    });
    
    // Submenu toggle
    $('[data-toggle="submenu"]').on('click', function(e) {
        e.preventDefault();
        
        const targetId = $(this).data('target');
        const $target = $('#' + targetId);
        const $arrow = $(this).find('.nav-arrow');
        
        // Close other submenus
        $('.nav-submenu').not($target).removeClass('show');
        $('.nav-arrow').not($arrow).removeClass('rotated');
        
        // Toggle current submenu
        $target.toggleClass('show');
        $arrow.toggleClass('rotated');
    });
    
    // Mobile sidebar overlay
    $sidebarOverlay.on('click', function() {
        $sidebar.removeClass('show');
        $(this).removeClass('show');
        $('body').removeClass('sidebar-open');
    });
    
    // Page navigation
    $('.nav-link[data-page]').on('click', function(e) {
        const page = $(this).data('page');
        
        // Remove active class from all nav links
        $('.nav-link').removeClass('active');
        
        // Add active class to clicked link
        $(this).addClass('active');
        
        // Close mobile sidebar
        if (window.innerWidth <= 768) {
            $sidebar.removeClass('show');
            $sidebarOverlay.removeClass('show');
            $('body').removeClass('sidebar-open');
        }
        
        // Show loading state
        showPageLoading();
    });
    
    // Set active navigation item based on current page
    function setActiveNavItem() {
        const currentPage = window.location.pathname.split('/').pop() || 'index.html';
        
        $('.nav-link').each(function() {
            const href = $(this).attr('href');
            if (href && (href.includes(currentPage) || (currentPage === 'index.html' && href === 'index.html'))) {
                $(this).addClass('active');
                
                // Open parent submenu if this is a submenu item
                const $submenu = $(this).closest('.nav-submenu');
                if ($submenu.length) {
                    $submenu.addClass('show');
                    const $parentToggle = $('[data-target="' + $submenu.attr('id') + '"]');
                    if ($parentToggle.length) {
                        $parentToggle.find('.nav-arrow').addClass('rotated');
                    }
                }
            }
        });
    }
    
    // Show page loading state
    function showPageLoading() {
        const $contentArea = $('#contentArea');
        if ($contentArea.length) {
            $contentArea.css({
                'opacity': '0.5',
                'pointer-events': 'none'
            });
            
            // Reset after a short delay
            setTimeout(function() {
                $contentArea.css({
                    'opacity': '1',
                    'pointer-events': 'auto'
                });
            }, 300);
        }
    }
    
    // Smooth scrolling for anchor links
    $('a[href^="#"]').on('click', function(e) {
        e.preventDefault();
        
        const target = $($(this).attr('href'));
        if (target.length) {
            $('html, body').animate({
                scrollTop: target.offset().top - 100
            }, 500);
        }
    });
    
    // Initialize tooltips
    $('[data-bs-toggle="tooltip"]').tooltip();
    
    // Initialize popovers
    $('[data-bs-toggle="popover"]').popover();
    
    // Search functionality
    // $('.search-box input').on('input', function() {
    //     const searchTerm = $(this).val().toLowerCase();
        
    //     if (searchTerm.length > 0) {
    //         // Filter sidebar navigation
    //         $('.nav-item').each(function() {
    //             const text = $(this).text().toLowerCase();
    //             if (text.includes(searchTerm)) {
    //                 $(this).show();
    //             } else {
    //                 $(this).hide();
    //             }
    //         });
    //     } else {
    //         $('.nav-item').show();
    //     }
    // });
    
    // Notification dropdown
    $('.notification-btn').on('click', function() {
        console.log('Notification clicked');
    });
    
    // Profile dropdown
    $('.profile-btn').on('click', function() {
        console.log('Profile clicked');
    });
    
    // Auto-hide alerts after 5 seconds
    $('.alert').each(function() {
        const $alert = $(this);
        setTimeout(function() {
            $alert.fadeOut();
        }, 5000);
    });
    
    // Form validation enhancement
    $('form').on('submit', function(e) {
        const form = this;
        
        // Add custom validation logic here
        if (!form.checkValidity()) {
            e.preventDefault();
            e.stopPropagation();
        }
        
        $(form).addClass('was-validated');
    });
    
    // Resize handler
    $(window).on('resize', function() {
        handleResize();
    });
    
    // Handle window resize
    function handleResize() {
        if (window.innerWidth > 768) {
            // Desktop: remove mobile classes
            $sidebar.removeClass('show');
            $sidebarOverlay.removeClass('show');
            $('body').removeClass('sidebar-open');
        } else {
            // Mobile: remove desktop classes
            $sidebar.removeClass('collapsed');
            $mainContent.removeClass('expanded');
        }
    }
    
    // Restore sidebar state on desktop
    if (window.innerWidth > 768) {
        const sidebarCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
        if (sidebarCollapsed) {
            $sidebar.addClass('collapsed');
            $mainContent.addClass('expanded');
        }
    }
    
    // Initialize dashboard charts if on dashboard page
    if ($('#revenueChart').length) {
        initializeCharts();
    }
    
    // Chart initialization
    function initializeCharts() {
        // Revenue Chart
        const revenueCtx = document.getElementById('revenueChart');
        if (revenueCtx) {
            new Chart(revenueCtx, {
                type: 'line',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                    datasets: [{
                        label: 'Revenue',
                        data: [12000, 19000, 15000, 25000, 22000, 30000, 28000, 35000, 32000, 40000, 38000, 45000],
                        borderColor: '#6366f1',
                        backgroundColor: 'rgba(99, 102, 241, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: '#6366f1',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2,
                        pointRadius: 6,
                        pointHoverRadius: 8
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                color: '#64748b'
                            }
                        },
                        y: {
                            grid: {
                                color: '#e2e8f0'
                            },
                            ticks: {
                                color: '#64748b',
                                callback: function(value) {
                                    return '$' + value.toLocaleString();
                                }
                            }
                        }
                    }
                }
            });
        }

        // Traffic Chart
        const trafficCtx = document.getElementById('trafficChart');
        if (trafficCtx) {
            new Chart(trafficCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Direct', 'Social Media', 'Email', 'Search', 'Referral'],
                    datasets: [{
                        data: [35, 25, 20, 15, 5],
                        backgroundColor: [
                            '#6366f1',
                            '#8b5cf6',
                            '#10b981',
                            '#f59e0b',
                            '#ef4444'
                        ],
                        borderWidth: 0,
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 20,
                                usePointStyle: true,
                                color: '#64748b'
                            }
                        }
                    },
                    cutout: '60%'
                }
            });
        }
    }
    
    // Counter animation for stats
    function animateCounters() {
        $('.stats-value').each(function() {
            const $counter = $(this);
            const target = parseInt($counter.text().replace(/[^0-9]/g, ''));
            const duration = 2000;
            const step = target / (duration / 16);
            let current = 0;
            
            const timer = setInterval(function() {
                current += step;
                if (current >= target) {
                    current = target;
                    clearInterval(timer);
                }
                
                // Format the number
                const formatted = Math.floor(current).toLocaleString();
                const originalText = $counter.text();
                const prefix = originalText.match(/^[^0-9]*/)[0];
                
                $counter.text(prefix + formatted);
            }, 16);
        });
    }
    
    // Intersection Observer for fade-in animations
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(function(entry) {
            if (entry.isIntersecting) {
                $(entry.target).addClass('fade-in');
            }
        });
    }, observerOptions);

    // Observe elements for animation
    $('.stats-card, .card').each(function() {
        observer.observe(this);
    });
    
    // Initialize everything
    setActiveNavItem();
    animateCounters();
    handleResize();
    
    // Export functions for global access
    window.AdminTemplate = {
        showPageLoading: showPageLoading,
        setActiveNavItem: setActiveNavItem
    };
});

// Login & Register Page JavaScript
$(document).ready(function() {
    // Add CSS styles for password functionality
    const authStyles = `
        <style>
        .password-input-container {
            position: relative;
        }
        
        .password-toggle {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #6b7280;
            z-index: 10;
            font-size: 16px;
            transition: color 0.3s ease;
        }
        
        .password-toggle:hover {
            color: #4f46e5;
        }
        
        .password-strength {
            margin-top: 8px;
        }
        
        .strength-bar {
            height: 4px;
            background-color: #e5e7eb;
            border-radius: 2px;
            overflow: hidden;
            margin-bottom: 4px;
        }
        
        .strength-fill {
            height: 100%;
            width: 0%;
            transition: width 0.3s ease, background-color 0.3s ease;
            border-radius: 2px;
        }
        
        .password-strength.strength-weak .strength-fill {
            width: 33%;
            background-color: #ef4444;
        }
        
        .password-strength.strength-medium .strength-fill {
            width: 66%;
            background-color: #f59e0b;
        }
        
        .password-strength.strength-strong .strength-fill {
            width: 100%;
            background-color: #10b981;
        }
        
        .strength-text {
            font-size: 12px;
            font-weight: 500;
        }
        
        .shake {
            animation: shake 0.5s ease-in-out;
        }
        
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }
        
        .btn-loading {
            position: relative;
            pointer-events: none;
        }
        
        .btn-loading::after {
            content: '';
            position: absolute;
            width: 16px;
            height: 16px;
            top: 50%;
            left: 50%;
            margin-left: -8px;
            margin-top: -8px;
            border: 2px solid transparent;
            border-top-color: #ffffff;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        </style>
    `;
    
    $('head').append(authStyles);
    
    // Setup password fields with toggle functionality
    function setupPasswordField($passwordField) {
        // Wrap password input in container
        if (!$passwordField.parent().hasClass('password-input-container')) {
            $passwordField.wrap('<div class="password-input-container"></div>');
        }
        
        // Add toggle icon
        const $container = $passwordField.parent('.password-input-container');
        if (!$container.find('.password-toggle').length) {
            $container.append('<i class="bi bi-eye password-toggle"></i>');
        }
    }
    
    // Initialize password fields
    $('input[type="password"]').each(function() {
        setupPasswordField($(this));
    });
    
    // Password toggle functionality
    $(document).on('click', '.password-toggle', function() {
        const $input = $(this).siblings('input[type="password"], input[type="text"]');
        const type = $input.attr('type') === 'password' ? 'text' : 'password';
        
        $input.attr('type', type);
        $(this).toggleClass('bi-eye bi-eye-slash');
    });
    
    // Login Form Handler
    $('#loginForm, form[action*="login"]').on('submit', function(e) {
        e.preventDefault();
        
        const $form = $(this);
        const $submitBtn = $form.find('button[type="submit"]');
        const identity = $form.find('input[name="identity"], #identity, #username').val();
        const password = $form.find('input[name="password"], #password').val();
        
        // Clear previous alerts
        $('.alert').remove();
        
        // Basic validation
        if (!identity || !password) {
            showAlert('danger', 'Silakan isi semua field yang diperlukan.');
            return;
        }
        
        // Show loading state
        $submitBtn.addClass('btn-loading').prop('disabled', true);
        
        // Submit the form normally (remove preventDefault for actual form submission)
        // For demo purposes, we'll keep the preventDefault and show success message
        setTimeout(function() {
            // Remove loading state
            $submitBtn.removeClass('btn-loading').prop('disabled', false);
            
            // For actual Laravel form, remove this demo code and let form submit normally
            // showAlert('success', 'Login berhasil!');
            
            // Uncomment this line for actual form submission:
            // $form.off('submit').submit();
        }, 1500);
    });
    
    // Register Form Handler
    $('#registerForm, form[action*="register"]').on('submit', function(e) {
    e.preventDefault();
    
    const $form = $(this);
    const $submitBtn = $form.find('button[type="submit"]');
    
    $submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...');
    
    $.ajax({
        url: $form.attr('action'),
        method: 'POST',
        data: $form.serialize(),
        success: function(response) {
            if (response.redirect) {
                window.location.href = response.redirect;
            } else {
                // Fallback jika response tidak mengandung redirect
                window.location.href = '/login';
            }
        },
        error: function(xhr) {
            $submitBtn.prop('disabled', false).text('Daftar');
            var errors = xhr.responseJSON.errors;
            if (errors) {
                // Tampilkan error validasi
                for (var field in errors) {
                    var input = $form.find('[name="' + field + '"]');
                    input.addClass('is-invalid');
                    input.next('.invalid-feedback').text(errors[field][0]);
                }
            } else {
                // Tampilkan error umum
                alert('Registrasi gagal. Silakan cek kembali data Anda.');
            }
        }
    });
});
    
    // Real-time form validation
    $('.form-control').on('input blur', function() {
        validateField($(this));
    });
    
    // Password strength indicator
    $('input[name="password"], #password').on('input', function() {
        const password = $(this).val();
        updatePasswordStrength($(this), password);
    });
    
    // Form field validation
    function validateField($field) {
        const value = $field.val();
        const fieldType = $field.attr('type');
        const fieldName = $field.attr('name') || $field.attr('id');
        
        // Remove previous validation classes
        $field.removeClass('is-valid is-invalid');
        
        // Skip validation if field is empty (except for required fields on submit)
        if (!value) return;
        
        let isValid = true;
        
        switch (fieldType) {
            case 'email':
                isValid = isValidEmail(value);
                break;
            case 'password':
                isValid = value.length >= 6;
                break;
            default:
                if (fieldName === 'password_confirmation' || fieldName === 'confirmPassword') {
                    const password = $('input[name="password"], #password').val();
                    isValid = value === password;
                } else if (fieldName === 'username') {
                    isValid = value.length >= 3 && /^[a-zA-Z0-9_]+$/.test(value);
                } else {
                    isValid = value.length >= 2;
                }
        }
        
        $field.addClass(isValid ? 'is-valid' : 'is-invalid');
    }
    
    // Email validation
    function isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }
    
    // Password strength indicator
    function updatePasswordStrength($passwordField, password) {
        const $container = $passwordField.parent('.password-input-container');
        let $strengthContainer = $container.find('.password-strength');
        
        if (!password) {
            $strengthContainer.remove();
            return;
        }
        
        if (!$strengthContainer.length) {
            const strengthHtml = `
                <div class="password-strength">
                    <div class="strength-bar">
                        <div class="strength-fill"></div>
                    </div>
                    <div class="strength-text"></div>
                </div>
            `;
            $container.append(strengthHtml);
            $strengthContainer = $container.find('.password-strength');
        }
        
        const strength = calculatePasswordStrength(password);
        const $strengthText = $strengthContainer.find('.strength-text');
        
        // Remove previous strength classes
        $strengthContainer.removeClass('strength-weak strength-medium strength-strong');
        
        if (strength.score <= 2) {
            $strengthContainer.addClass('strength-weak');
            $strengthText.text('Password lemah').css('color', '#ef4444');
        } else if (strength.score <= 4) {
            $strengthContainer.addClass('strength-medium');
            $strengthText.text('Password sedang').css('color', '#f59e0b');
        } else {
            $strengthContainer.addClass('strength-strong');
            $strengthText.text('Password kuat').css('color', '#10b981');
        }
    }
    
    // Calculate password strength
    function calculatePasswordStrength(password) {
        let score = 0;
        
        // Length
        if (password.length >= 6) score++;
        if (password.length >= 8) score++;
        if (password.length >= 12) score++;
        
        // Character types
        if (/[a-z]/.test(password)) score++;
        if (/[A-Z]/.test(password)) score++;
        if (/[0-9]/.test(password)) score++;
        if (/[^A-Za-z0-9]/.test(password)) score++;
        
        return { score: score };
    }
    
    // Show alert message
    function showAlert(type, message) {
        const alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        
        $('.card').prepend(alertHtml);
        
        // Auto-hide after 5 seconds
        setTimeout(function() {
            $('.alert').fadeOut();
        }, 5000);
    }
    
    // Add floating label effect
    $('.form-control').each(function() {
        const $input = $(this);
        const $label = $input.prev('.form-label');
        
        if ($label.length) {
            $input.on('focus blur', function() {
                $label.toggleClass('focused', $(this).is(':focus') || $(this).val());
            });
        }
    });
    
    // Smooth page transitions
    $('a[href$=".html"]').on('click', function(e) {
        e.preventDefault();
        const href = $(this).attr('href');
        
        $('body').fadeOut(300, function() {
            window.location.href = href;
        });
    });
    
    // Page load animation
    $('body').hide().fadeIn(500);
});


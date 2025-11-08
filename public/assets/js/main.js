// Admin Dashboard JavaScript with jQuery
$(document).ready(function() {
    console.log('Main.js loaded - jQuery version');

    // Sidebar toggle functionality
    const $sidebar = $('#sidebar');
    const $sidebarToggle = $('#sidebarToggle');
    const $mainContent = $('#mainContent');
    const $sidebarBackdrop = $('#sidebar-backdrop');

    console.log('Sidebar elements:', {
        sidebar: $sidebar.length,
        toggle: $sidebarToggle.length,
        backdrop: $sidebarBackdrop.length
    });

    // Toggle sidebar
    $sidebarToggle.on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        console.log('Sidebar toggle clicked - jQuery');

        if (window.innerWidth <= 768) {
            // Mobile behavior
            $sidebar.toggleClass('-translate-x-full');
            $sidebarBackdrop.toggleClass('hidden');
            $('body').toggleClass('overflow-hidden');
        } else {
            // Desktop behavior - toggle collapsed state
            $sidebar.toggleClass('collapsed');
            $mainContent.toggleClass('expanded');
            
            // Store preference
            localStorage.setItem('sidebarCollapsed', $sidebar.hasClass('collapsed'));
        }
        
        // Close all submenus when collapsing sidebar
        if ($sidebar.hasClass('collapsed') || $sidebar.hasClass('-translate-x-full')) {
            $('.nav-submenu').removeClass('show');
            $('.nav-arrow').removeClass('rotated');
        }
    });
    
    // Sidebar backdrop for mobile
    $sidebarBackdrop.on('click', function(e) {
        e.preventDefault();
        console.log('Sidebar backdrop clicked');
        
        $sidebar.addClass('-translate-x-full');
        $sidebarBackdrop.addClass('hidden');
        $('body').removeClass('overflow-hidden');
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
    
    // Set active navigation item based on current page
    function setActiveNavItem() {
        const currentPath = window.location.pathname;
        
        $('.nav-link').each(function() {
            const href = $(this).attr('href');
            if (href === currentPath || (currentPath === '/' && href === '{{ route("dashboard.dashboard") }}')) {
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
    
    // Handle window resize
    function handleResize() {
        if (window.innerWidth > 768) {
            // Desktop: remove mobile classes
            $sidebar.removeClass('-translate-x-full');
            $sidebarBackdrop.addClass('hidden');
            $('body').removeClass('overflow-hidden');
        }
    }
    
    // Resize handler
    $(window).on('resize', function() {
        handleResize();
    });
    
    // Restore sidebar state on desktop
    if (window.innerWidth > 768) {
        const sidebarCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
        if (sidebarCollapsed) {
            $sidebar.addClass('collapsed');
            $mainContent.addClass('expanded');
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
    
    // Auto-hide alerts after 5 seconds
    $('.alert.auto-hide').each(function() {
        const $alert = $(this);
        setTimeout(function() {
            $alert.fadeOut(300, function() {
                $(this).remove();
            });
        }, 5000);
    });
    
    // Close alert buttons
    $('[data-alert-hide]').on('click', function() {
        const $alert = $(this).closest('.alert');
        $alert.fadeOut(300, function() {
            $(this).remove();
        });
    });

    // Form validation enhancement
    $('form').on('submit', function(e) {
        const form = this;
        
        if (!form.checkValidity()) {
            e.preventDefault();
            e.stopPropagation();
        }
        
        $(form).addClass('was-validated');
    });

    // Initialize everything
    setActiveNavItem();
    handleResize();

    console.log('Sidebar initialization complete');
});

// Utility functions
function formatNumber(number) {
    return new Intl.NumberFormat('id-ID').format(number);
}

function formatDate(dateString) {
    const options = { 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric',
        timeZone: 'Asia/Jakarta'
    };
    return new Date(dateString).toLocaleDateString('id-ID', options);
}

// AJAX helper
async function ajaxRequest(url, options = {}) {
    const defaultOptions = {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        }
    };

    const mergedOptions = { ...defaultOptions, ...options };
    
    try {
        const response = await fetch(url, mergedOptions);
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return await response.json();
    } catch (error) {
        console.error('AJAX request failed:', error);
        throw error;
    }
}

// Make functions globally available
window.formatNumber = formatNumber;
window.formatDate = formatDate;
window.ajaxRequest = ajaxRequest;
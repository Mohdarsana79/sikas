// Admin Dashboard JavaScript with jQuery - DEBUG VERSION
$(document).ready(function() {
    console.log('🚀 Main.js loaded - Debug mode');
    console.log('📍 Current path:', window.location.pathname);

    const $loadingSpinner = $('#loadingSpinner');
    console.log('🔍 Loading spinner found:', $loadingSpinner.length);

    function showLoading() {
        console.log('🎯 SHOW LOADING CALLED');
        $loadingSpinner.removeClass('hidden');
        $('body').css('overflow', 'hidden');
    }

    function hideLoading() {
        console.log('🎯 HIDE LOADING CALLED');
        $loadingSpinner.addClass('hidden');
        $('body').css('overflow', '');
    }

    // DEBUG: Log semua link di sidebar
    console.log('🔗 All sidebar links:');
    $('.sidebar a').each(function() {
        const $link = $(this);
        console.log('   -', $link.attr('href'), '| text:', $link.text().trim());
    });

    // VERSION 1: Direct event delegation pada sidebar
    $('.sidebar').on('click', 'a', function(e) {
        console.log('🎯 SIDEBAR LINK CLICKED V1');
        console.log('   Target:', $(this).attr('href'));
        console.log('   Text:', $(this).text().trim());
        
        const href = $(this).attr('href');
        
        // Skip conditions
        if ($(this).attr('data-toggle') === 'submenu' || 
            !href || 
            href === '#' || 
            href.startsWith('javascript:') ||
            href === window.location.pathname) {
            console.log('⏩ Skipped - submenu/current page');
            return true;
        }

        console.log('🎯 SHOWING LOADING FOR:', href);
        showLoading();
        
        setTimeout(() => {
            hideLoading();
        }, 10000);
        
        return true;
    });

    // VERSION 2: Alternative approach - lebih agresif
    $(document).on('click', 'a', function(e) {
        const $link = $(this);
        const href = $link.attr('href');
        
        // Hanya handle link yang ada di sidebar
        if (!$link.closest('.sidebar').length) {
            return true;
        }
        
        console.log('🎯 SIDEBAR LINK CLICKED V2');
        console.log('   Target:', href);
        console.log('   Text:', $link.text().trim());
        
        // Skip conditions
        if ($link.attr('data-toggle') === 'submenu' || 
            !href || 
            href === '#' || 
            href.startsWith('javascript:') ||
            href === window.location.pathname) {
            console.log('⏩ Skipped - submenu/current page');
            return true;
        }

        console.log('🎯 SHOWING LOADING FOR:', href);
        e.preventDefault(); // Prevent default sementara untuk testing
        showLoading();
        
        // Navigasi manual setelah 2 detik untuk testing
        setTimeout(() => {
            console.log('🔗 Navigating to:', href);
            window.location.href = href;
        }, 2000);
        
        return false;
    });

    // Hide loading on page load
    $(window).on('load', function() {
        console.log('📄 Page fully loaded');
        hideLoading();
    });

    // Existing sidebar functionality
    const $sidebar = $('#sidebar');
    const $sidebarToggle = $('#sidebarToggle');

    $sidebarToggle.on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        if (window.innerWidth <= 768) {
            $sidebar.toggleClass('-translate-x-full');
            $('#sidebar-backdrop').toggleClass('hidden');
            $('body').toggleClass('overflow-hidden');
        } else {
            $sidebar.toggleClass('collapsed');
            $('#mainContent').toggleClass('expanded');
            localStorage.setItem('sidebarCollapsed', $sidebar.hasClass('collapsed'));
        }
    });

    $('[data-toggle="submenu"]').on('click', function(e) {
        e.preventDefault();
        const targetId = $(this).data('target');
        const $target = $('#' + targetId);
        const $arrow = $(this).find('.nav-arrow');
        
        $('.nav-submenu').not($target).removeClass('show');
        $('.nav-arrow').not($arrow).removeClass('rotated');
        
        $target.toggleClass('show');
        $arrow.toggleClass('rotated');
    });

    console.log('✅ Debug initialization complete');
    
    // Test function
    window.testLoading = function() {
        console.log('🧪 Manual test started');
        showLoading();
        setTimeout(() => {
            hideLoading();
            console.log('🧪 Manual test completed');
        }, 3000);
    }
});
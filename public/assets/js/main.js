// Main JavaScript - General Application Functions
class MainApp {
    constructor() {
        this.init();
    }

    init() {
        this.handleNavigation();
        this.handleDropdowns();
        this.handleModals();
        this.handleSidebar();
        this.handleNotifications();
        this.initComponents();
    }

    // Navigation handling
    handleNavigation() {
        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
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

        // Active navigation state
        const currentPath = window.location.pathname;
        document.querySelectorAll('.nav-link').forEach(link => {
            if (link.getAttribute('href') === currentPath) {
                link.classList.add('active');
            }
        });
    }

    // Dropdown handling
    handleDropdowns() {
        document.addEventListener('click', (e) => {
            const dropdowns = document.querySelectorAll('.dropdown');
            
            dropdowns.forEach(dropdown => {
                if (!dropdown.contains(e.target)) {
                    const menu = dropdown.querySelector('.dropdown-menu');
                    if (menu) {
                        menu.classList.add('hidden');
                    }
                }
            });

            // Toggle dropdown when button is clicked
            if (e.target.closest('.dropdown-toggle')) {
                const dropdown = e.target.closest('.dropdown');
                const menu = dropdown.querySelector('.dropdown-menu');
                if (menu) {
                    menu.classList.toggle('hidden');
                }
            }
        });
    }

    // Modal handling
    handleModals() {
        // Open modal
        document.querySelectorAll('[data-modal-toggle]').forEach(button => {
            button.addEventListener('click', () => {
                const modalId = button.getAttribute('data-modal-toggle');
                const modal = document.getElementById(modalId);
                if (modal) {
                    modal.classList.remove('hidden');
                    modal.classList.add('flex');
                    document.body.classList.add('overflow-hidden');
                }
            });
        });

        // Close modal
        document.querySelectorAll('[data-modal-hide]').forEach(button => {
            button.addEventListener('click', () => {
                const modal = button.closest('.modal');
                if (modal) {
                    modal.classList.add('hidden');
                    modal.classList.remove('flex');
                    document.body.classList.remove('overflow-hidden');
                }
            });
        });

        // Close modal when clicking outside
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('modal')) {
                e.target.classList.add('hidden');
                e.target.classList.remove('flex');
                document.body.classList.remove('overflow-hidden');
            }
        });

        // Close modal with Escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                const openModal = document.querySelector('.modal:not(.hidden)');
                if (openModal) {
                    openModal.classList.add('hidden');
                    openModal.classList.remove('flex');
                    document.body.classList.remove('overflow-hidden');
                }
            }
        });
    }

    // Sidebar handling for mobile
    handleSidebar() {
        const sidebarToggle = document.querySelector('[data-drawer-toggle]');
        const sidebar = document.querySelector('#sidebar');
        const sidebarBackdrop = document.querySelector('#sidebar-backdrop');

        if (sidebarToggle && sidebar) {
            sidebarToggle.addEventListener('click', () => {
                sidebar.classList.toggle('-translate-x-full');
                if (sidebarBackdrop) {
                    sidebarBackdrop.classList.toggle('hidden');
                }
            });
        }

        if (sidebarBackdrop) {
            sidebarBackdrop.addEventListener('click', () => {
                sidebar.classList.add('-translate-x-full');
                sidebarBackdrop.classList.add('hidden');
            });
        }
    }

    // Notification handling
    handleNotifications() {
        // Auto-hide alerts after 5 seconds
        const autoHideAlerts = document.querySelectorAll('.alert.auto-hide');
        autoHideAlerts.forEach(alert => {
            setTimeout(() => {
                alert.style.opacity = '0';
                setTimeout(() => {
                    if (alert.parentNode) {
                        alert.remove();
                    }
                }, 300);
            }, 5000);
        });

        // Close alert buttons
        document.querySelectorAll('[data-alert-hide]').forEach(button => {
            button.addEventListener('click', () => {
                const alert = button.closest('.alert');
                if (alert) {
                    alert.style.opacity = '0';
                    setTimeout(() => {
                        if (alert.parentNode) {
                            alert.remove();
                        }
                    }, 300);
                }
            });
        });
    }

    // Initialize other components
    initComponents() {
        // Initialize tooltips
        this.initTooltips();
        
        // Initialize charts if any
        this.initCharts();
        
        // Initialize date pickers
        this.initDatePickers();
        
        // Initialize file uploads
        this.initFileUploads();
    }

    initTooltips() {
        const tooltips = document.querySelectorAll('[data-tooltip]');
        tooltips.forEach(element => {
            element.addEventListener('mouseenter', this.showTooltip);
            element.addEventListener('mouseleave', this.hideTooltip);
        });
    }

    showTooltip(e) {
        const tooltipText = this.getAttribute('data-tooltip');
        const tooltip = document.createElement('div');
        tooltip.className = 'absolute z-50 px-2 py-1 text-sm text-white bg-gray-900 rounded shadow-lg';
        tooltip.textContent = tooltipText;
        tooltip.id = 'tooltip-' + Date.now();
        
        document.body.appendChild(tooltip);
        
        const rect = this.getBoundingClientRect();
        tooltip.style.top = (rect.top - tooltip.offsetHeight - 5) + 'px';
        tooltip.style.left = (rect.left + (rect.width / 2) - (tooltip.offsetWidth / 2)) + 'px';
        
        this.tooltipElement = tooltip;
    }

    hideTooltip() {
        if (this.tooltipElement) {
            this.tooltipElement.remove();
            this.tooltipElement = null;
        }
    }

    initCharts() {
        // Initialize any charts if Chart.js is available
        if (typeof Chart !== 'undefined') {
            const chartElements = document.querySelectorAll('[data-chart]');
            chartElements.forEach(element => {
                const chartType = element.getAttribute('data-chart-type') || 'line';
                const chartData = JSON.parse(element.getAttribute('data-chart-data'));
                
                new Chart(element, {
                    type: chartType,
                    data: chartData,
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                    }
                });
            });
        }
    }

    initDatePickers() {
        // Initialize flatpickr if available
        if (typeof flatpickr !== 'undefined') {
            const dateInputs = document.querySelectorAll('[data-datepicker]');
            dateInputs.forEach(input => {
                flatpickr(input, {
                    dateFormat: 'Y-m-d',
                    allowInput: true
                });
            });
        }
    }

    initFileUploads() {
        const fileInputs = document.querySelectorAll('.file-input');
        fileInputs.forEach(input => {
            input.addEventListener('change', function() {
                const fileName = this.files[0]?.name || 'No file chosen';
                const fileLabel = this.nextElementSibling;
                if (fileLabel && fileLabel.classList.contains('file-label')) {
                    fileLabel.textContent = fileName;
                }
            });
        });
    }

    // Utility functions
    static formatNumber(number) {
        return new Intl.NumberFormat('id-ID').format(number);
    }

    static formatDate(dateString) {
        const options = { 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric',
            timeZone: 'Asia/Jakarta'
        };
        return new Date(dateString).toLocaleDateString('id-ID', options);
    }

    static showLoading(selector = 'body') {
        const element = document.querySelector(selector);
        if (element) {
            element.classList.add('loading');
        }
    }

    static hideLoading(selector = 'body') {
        const element = document.querySelector(selector);
        if (element) {
            element.classList.remove('loading');
        }
    }

    // AJAX helper
    static async ajaxRequest(url, options = {}) {
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
}

// Initialize when document is ready
document.addEventListener('DOMContentLoaded', function() {
    window.App = new MainApp();
    
    // Make utility functions globally available
    window.formatNumber = MainApp.formatNumber;
    window.formatDate = MainApp.formatDate;
    window.ajaxRequest = MainApp.ajaxRequest;
});

// jQuery compatibility (if jQuery is used)
if (typeof jQuery !== 'undefined') {
    jQuery(document).ready(function($) {
        // jQuery specific initializations can go here
        // But all auth-related jQuery code should be removed
        
        // Example: Initialize select2 if available
        if ($.fn.select2) {
            $('.select2').select2();
        }
        
        // Example: DataTables initialization
        if ($.fn.DataTable) {
            $('.datatable').DataTable({
                responsive: true,
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.10.25/i18n/Indonesian.json'
                }
            });
        }
    });
}

// Export for module usage
if (typeof module !== 'undefined' && module.exports) {
    module.exports = MainApp;
}
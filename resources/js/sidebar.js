/**
 * Sidebar functionality for CRM_AUTOMATIZADOR
 * Handles responsive sidebar behavior and interactions
 */

class SidebarManager {
    constructor() {
        this.sidebar = null;
        this.overlay = null;
        this.menuBtn = null;
        this.closeBtn = null;
        this.isOpen = false;
        this.isMobile = false;
        
        this.init();
    }

    init() {
        // Wait for DOM to be ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => this.setupSidebar());
        } else {
            this.setupSidebar();
        }
    }

    setupSidebar() {
        this.sidebar = document.getElementById('sidebar');
        this.overlay = document.getElementById('sidebar-overlay');
        this.menuBtn = document.getElementById('mobile-menu-btn');
        this.closeBtn = document.getElementById('sidebar-close-btn');

        if (!this.sidebar) {
            console.warn('Sidebar element not found');
            return;
        }

        this.bindEvents();
        this.checkScreenSize();
        this.handleResize();
    }

    bindEvents() {
        // Mobile menu button
        if (this.menuBtn) {
            this.menuBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.toggleSidebar();
            });
        }

        // Close button
        if (this.closeBtn) {
            this.closeBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.closeSidebar();
            });
        }

        // Overlay click
        if (this.overlay) {
            this.overlay.addEventListener('click', () => {
                this.closeSidebar();
            });
        }

        // Keyboard events
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.isOpen && this.isMobile) {
                this.closeSidebar();
            }
        });

        // Window resize
        window.addEventListener('resize', () => {
            this.handleResize();
        });

        // Navigation item clicks
        this.setupNavigation();
    }

    setupNavigation() {
        const navItems = document.querySelectorAll('.sidebar-nav-item');
        
        // Add stagger animation on load
        navItems.forEach((item, index) => {
            item.style.opacity = '0';
            item.style.transform = 'translateX(-20px)';
            
            setTimeout(() => {
                item.style.transition = 'all 0.4s ease';
                item.style.opacity = '1';
                item.style.transform = 'translateX(0)';
            }, index * 100);
        });
        
        navItems.forEach(item => {
            // Add ripple effect
            item.addEventListener('click', (e) => {
                // Create ripple element
                const ripple = document.createElement('span');
                const rect = item.getBoundingClientRect();
                const size = Math.max(rect.width, rect.height);
                const x = e.clientX - rect.left - size / 2;
                const y = e.clientY - rect.top - size / 2;
                
                ripple.style.cssText = `
                    position: absolute;
                    width: ${size}px;
                    height: ${size}px;
                    left: ${x}px;
                    top: ${y}px;
                    background: rgba(59, 130, 246, 0.3);
                    border-radius: 50%;
                    transform: scale(0);
                    animation: ripple 0.6s ease-out;
                    pointer-events: none;
                    z-index: 0;
                `;
                
                item.style.position = 'relative';
                item.appendChild(ripple);
                
                // Remove ripple after animation
                setTimeout(() => {
                    if (ripple.parentNode) {
                        ripple.parentNode.removeChild(ripple);
                    }
                }, 600);
                
                // Remove active class from all items
                navItems.forEach(nav => nav.classList.remove('active'));
                
                // Add active class to clicked item with animation
                item.classList.add('active');
                
                // Close sidebar on mobile after navigation
                if (this.isMobile) {
                    setTimeout(() => {
                        this.closeSidebar();
                    }, 150);
                }
            });

            // Add hover sound effect (optional)
            item.addEventListener('mouseenter', () => {
                item.style.transform = 'translateX(4px) scale(1.02)';
            });

            item.addEventListener('mouseleave', () => {
                if (!item.classList.contains('active')) {
                    item.style.transform = 'translateX(0) scale(1)';
                }
            });
        });

        // Add CSS for ripple animation
        if (!document.getElementById('ripple-styles')) {
            const style = document.createElement('style');
            style.id = 'ripple-styles';
            style.textContent = `
                @keyframes ripple {
                    to {
                        transform: scale(2);
                        opacity: 0;
                    }
                }
            `;
            document.head.appendChild(style);
        }
    }

    toggleSidebar() {
        if (this.isOpen) {
            this.closeSidebar();
        } else {
            this.openSidebar();
        }
    }

    openSidebar() {
        if (!this.sidebar) return;

        this.isOpen = true;
        this.sidebar.classList.add('open');
        
        if (this.overlay) {
            this.overlay.classList.add('active');
        }

        // Prevent body scroll when sidebar is open on mobile
        if (this.isMobile) {
            document.body.style.overflow = 'hidden';
        }

        // Trigger custom event
        this.triggerEvent('sidebarOpen');
    }

    closeSidebar() {
        if (!this.sidebar) return;

        this.isOpen = false;
        this.sidebar.classList.remove('open');
        
        if (this.overlay) {
            this.overlay.classList.remove('active');
        }

        // Restore body scroll
        document.body.style.overflow = '';

        // Trigger custom event
        this.triggerEvent('sidebarClose');
    }

    checkScreenSize() {
        this.isMobile = window.innerWidth < 1024; // lg breakpoint
        
        if (!this.isMobile) {
            this.closeSidebar();
            document.body.style.overflow = '';
        }
    }

    handleResize() {
        this.checkScreenSize();
        
        // Debounce resize handler
        clearTimeout(this.resizeTimeout);
        this.resizeTimeout = setTimeout(() => {
            this.checkScreenSize();
        }, 100);
    }

    triggerEvent(eventName) {
        const event = new CustomEvent(eventName, {
            detail: { 
                isOpen: this.isOpen,
                isMobile: this.isMobile 
            }
        });
        document.dispatchEvent(event);
    }

    // Public API methods
    open() {
        this.openSidebar();
    }

    close() {
        this.closeSidebar();
    }

    toggle() {
        this.toggleSidebar();
    }

    isOpened() {
        return this.isOpen;
    }
}

// User Profile Dropdown functionality
class UserDropdown {
    constructor() {
        this.dropdown = null;
        this.trigger = null;
        this.isOpen = false;
        
        this.init();
    }

    init() {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => this.setup());
        } else {
            this.setup();
        }
    }

    setup() {
        this.dropdown = document.getElementById('user-dropdown');
        this.trigger = document.getElementById('user-dropdown-trigger');

        if (!this.dropdown || !this.trigger) return;

        this.bindEvents();
    }

    bindEvents() {
        // Toggle dropdown
        this.trigger.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            this.toggle();
        });

        // Close on outside click
        document.addEventListener('click', (e) => {
            if (!this.dropdown.contains(e.target)) {
                this.close();
            }
        });

        // Close on escape
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.isOpen) {
                this.close();
            }
        });
    }

    toggle() {
        if (this.isOpen) {
            this.close();
        } else {
            this.open();
        }
    }

    open() {
        this.isOpen = true;
        this.dropdown.classList.remove('hidden');
        this.dropdown.classList.add('block');
    }

    close() {
        this.isOpen = false;
        this.dropdown.classList.add('hidden');
        this.dropdown.classList.remove('block');
    }
}

// Theme management
class ThemeManager {
    constructor() {
        this.currentTheme = localStorage.getItem('theme') || 'light';
        this.init();
    }

    init() {
        this.applyTheme();
        this.setupThemeToggle();
    }

    setupThemeToggle() {
        const themeToggle = document.getElementById('theme-toggle');
        if (themeToggle) {
            themeToggle.addEventListener('click', () => {
                this.toggleTheme();
            });
        }
    }

    toggleTheme() {
        this.currentTheme = this.currentTheme === 'light' ? 'dark' : 'light';
        this.applyTheme();
        localStorage.setItem('theme', this.currentTheme);
    }

    applyTheme() {
        if (this.currentTheme === 'dark') {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    }
}

// Initialize when DOM is ready
let sidebarManager, userDropdown, themeManager;

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeComponents);
} else {
    initializeComponents();
}

function initializeComponents() {
    sidebarManager = new SidebarManager();
    userDropdown = new UserDropdown();
    themeManager = new ThemeManager();
}

// Export for global access
window.CRM = window.CRM || {};
window.CRM.sidebar = () => sidebarManager;
window.CRM.userDropdown = () => userDropdown;
window.CRM.theme = () => themeManager;

// Utility functions
window.CRM.utils = {
    // Show notification
    showNotification(message, type = 'info', duration = 3000) {
        const notification = document.createElement('div');
        notification.className = `fixed top-4 right-4 z-50 px-4 py-2 rounded-lg shadow-lg transition-all duration-300 transform translate-x-full`;
        
        const colors = {
            success: 'bg-green-500 text-white',
            error: 'bg-red-500 text-white', 
            warning: 'bg-yellow-500 text-black',
            info: 'bg-blue-500 text-white'
        };
        
        notification.className += ` ${colors[type] || colors.info}`;
        notification.textContent = message;
        
        document.body.appendChild(notification);
        
        // Animate in
        setTimeout(() => {
            notification.classList.remove('translate-x-full');
        }, 10);
        
        // Remove after duration
        setTimeout(() => {
            notification.classList.add('translate-x-full');
            setTimeout(() => {
                document.body.removeChild(notification);
            }, 300);
        }, duration);
    },

    // Format currency
    formatCurrency(amount, currency = 'USD') {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: currency
        }).format(amount);
    },

    // Debounce function
    debounce(func, wait) {
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
};
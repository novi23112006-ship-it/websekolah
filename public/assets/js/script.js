/**
 * SISTEM ABSENSI SMK — CORE SCRIPTS
 * Responsif Sidebar Toggle & UI Utilities
 */

document.addEventListener('DOMContentLoaded', function() {
    const appLayout = document.getElementById('appLayout');
    const toggleButtons = document.querySelectorAll('.btn-sidebar-toggle');
    const btnClose  = document.getElementById('btnSidebarClose');
    const backdrop  = document.getElementById('sidebarBackdrop');

    // Restore desktop collapsed state
    if (window.innerWidth >= 992) {
        const isCollapsed = localStorage.getItem('sidebar_collapsed') === 'true';
        if (isCollapsed && appLayout) {
            appLayout.classList.add('sidebar-collapsed');
        }
    }

    function toggleSidebar() {
        if (!appLayout) return;
        if (window.innerWidth < 992) {
            // Mobile: drawer open/close
            const isOpen = appLayout.classList.toggle('sidebar-mobile-open');
            if (backdrop) {
                backdrop.classList.toggle('show', isOpen);
            }
        } else {
            // Desktop: collapse / expand
            const isCollapsed = appLayout.classList.toggle('sidebar-collapsed');
            localStorage.setItem('sidebar_collapsed', isCollapsed ? 'true' : 'false');
        }
    }

    function closeMobileSidebar() {
        if (appLayout) {
            appLayout.classList.remove('sidebar-mobile-open');
        }
        if (backdrop) {
            backdrop.classList.remove('show');
        }
    }

    toggleButtons.forEach(function(btn) {
        btn.addEventListener('click', toggleSidebar);
    });
    if (btnClose) btnClose.addEventListener('click', closeMobileSidebar);
    if (backdrop) backdrop.addEventListener('click', closeMobileSidebar);

    // Handle window resize
    window.addEventListener('resize', function() {
        if (window.innerWidth >= 992) {
            closeMobileSidebar();
            const isCollapsed = localStorage.getItem('sidebar_collapsed') === 'true';
            if (isCollapsed && appLayout) {
                appLayout.classList.add('sidebar-collapsed');
            } else if (appLayout) {
                appLayout.classList.remove('sidebar-collapsed');
            }
        }
    });

    // Auto-initialize Bootstrap tooltips if available
    if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function(el) {
            return new bootstrap.Tooltip(el);
        });
    }
});

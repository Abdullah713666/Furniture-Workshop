<?php
/**
 * Admin Sidebar Include
 * Shared sidebar navigation for all admin pages
 */
$current_admin_page = $current_admin_page ?? 'dashboard';
?>
    <aside class="admin-sidebar">
        <div class="logo">
            <a href="dashboard.php">Antique Workshop</a>
            <small>Admin Panel</small>
        </div>
        <nav class="sidebar-nav">
            <a href="dashboard.php"<?php if ($current_admin_page === 'dashboard') echo ' class="active"'; ?>>
                <svg viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="9"/><rect x="14" y="3" width="7" height="5"/><rect x="14" y="12" width="7" height="9"/><rect x="3" y="16" width="7" height="5"/></svg>
                <span>Dashboard</span>
            </a>
            <a href="gallery.php"<?php if ($current_admin_page === 'gallery') echo ' class="active"'; ?>>
                <svg viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                <span>Gallery</span>
            </a>
            <a href="categories.php"<?php if ($current_admin_page === 'categories') echo ' class="active"'; ?>>
                <svg viewBox="0 0 24 24"><path d="M22 19a2 2 0 01-2 2H4a2 2 0 01-2-2V5a2 2 0 012-2h5l2 3h9a2 2 0 012 2z"/></svg>
                <span>Categories</span>
            </a>
            <a href="services.php"<?php if ($current_admin_page === 'services') echo ' class="active"'; ?>>
                <svg viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                <span>Services</span>
            </a>
            <a href="inventory.php"<?php if ($current_admin_page === 'inventory') echo ' class="active"'; ?>>
                <svg viewBox="0 0 24 24"><path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>
                <span>Inventory</span>
            </a>
            <a href="transactions.php"<?php if ($current_admin_page === 'transactions') echo ' class="active"'; ?>>
                <svg viewBox="0 0 24 24"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>
                <span>Sales</span>
            </a>
            <a href="reports.php"<?php if ($current_admin_page === 'reports') echo ' class="active"'; ?>>
                <svg viewBox="0 0 24 24"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
                <span>Reports</span>
            </a>
            <div class="divider"></div>
            <a href="timeline.php"<?php if ($current_admin_page === 'timeline') echo ' class="active"'; ?>>
                <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                <span>Timeline</span>
            </a>
            <a href="messages.php"<?php if ($current_admin_page === 'messages') echo ' class="active"'; ?>>
                <svg viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                <span>Messages</span>
            </a>
            <a href="faqs.php"<?php if ($current_admin_page === 'faqs') echo ' class="active"'; ?>>
                <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 015.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                <span>FAQs</span>
            </a>
            <a href="users.php"<?php if ($current_admin_page === 'users') echo ' class="active"'; ?>>
                <svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>
                <span>Users</span>
            </a>
            <div class="divider"></div>
            <a href="settings.php"<?php if ($current_admin_page === 'settings') echo ' class="active"'; ?>>
                <svg viewBox="0 0 24 24"><path d="M12 15a3 3 0 100-6 3 3 0 000 6z"/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 11-2.83 2.83l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 11-4 0v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 11-2.83-2.83l.06-.06A1.65 1.65 0 004.68 15a1.65 1.65 0 00-1.51-1H3a2 2 0 110-4h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 112.83-2.83l.06.06A1.65 1.65 0 009 4.68a1.65 1.65 0 001-1.51V3a2 2 0 114 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 112.83 2.83l-.06.06A1.65 1.65 0 0019.4 9a1.65 1.65 0 001.51 1H21a2 2 0 110 4h-.09a1.65 1.65 0 00-1.51 1z"/></svg>
                <span>Settings</span>
            </a>
            <a href="../index.php">
                <svg viewBox="0 0 24 24"><path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                <span>View Site</span>
            </a>
            <a href="logout.php">
                <svg viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                <span>Logout</span>
            </a>
        </nav>
    </aside>

    <!-- Admin tab session check (sessionStorage is tab-scoped) -->
    <script>
    (function() {
        if (!sessionStorage.getItem('adminTabToken')) {
            if (window.location.pathname.indexOf('dashboard.php') === -1) {
                window.location.href = 'logout.php';
            }
        }
    })();
    </script>

<?php
/**
 * Shared Footer — Antique Furniture Workshop
 * 
 * Include this at the bottom of every public page.
 * Uses $active_page variable set before including header.php
 */

$active_page = $active_page ?? 'home';
?>
    </main><!-- /#main-content -->

    <!-- ==================== FOOTER ==================== -->
    <footer class="footer" role="contentinfo">
        <p>&copy; <?php echo date('Y'); ?> Antique Workshop. All rights reserved. &bull; <a href="faq.php" style="color: inherit; text-decoration: none; opacity: 0.8; margin-left: 8px;">FAQ</a></p>
        <div class="footer-social">
            <a href="<?php echo getSetting('instagram_url', '#'); ?>" aria-label="Instagram">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="2" y="2" width="20" height="20" rx="5" />
                    <circle cx="12" cy="12" r="5" />
                    <circle cx="17.5" cy="6.5" r="1.5" fill="currentColor" stroke="none" />
                </svg>
            </a>
            <a href="<?php echo getSetting('twitter_url', '#'); ?>" aria-label="Twitter">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M23 3a10.9 10.9 0 01-3.14 1.53A4.48 4.48 0 0012 7.5v1A10.66 10.66 0 013 4s-4 9 5 13a11.64 11.64 0 01-7 2c9 5 20 0 20-11.5a4.5 4.5 0 00-.08-.83A7.72 7.72 0 0023 3z" />
                </svg>
            </a>
        </div>
    </footer>

    <!-- ==================== BOTTOM NAV (Mobile) ==================== -->
    <nav class="bottom-nav" aria-label="Bottom navigation">
        <a href="index.php"<?php if ($active_page === 'home') echo ' class="active"'; ?>>
            <svg viewBox="0 0 24 24">
                <path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z" />
                <polyline points="9 22 9 12 15 12 15 22" />
            </svg>
            Home
        </a>
        <a href="gallery.php"<?php if ($active_page === 'gallery') echo ' class="active"'; ?>>
            <svg viewBox="0 0 24 24">
                <rect x="3" y="3" width="7" height="7" />
                <rect x="14" y="3" width="7" height="7" />
                <rect x="14" y="14" width="7" height="7" />
                <rect x="3" y="14" width="7" height="7" />
            </svg>
            Collection
        </a>
        <a href="services.php"<?php if ($active_page === 'services') echo ' class="active"'; ?>>
            <svg viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="3" />
                <path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-4 0v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83-2.83l.06-.06A1.65 1.65 0 004.68 15a1.65 1.65 0 00-1.51-1H3a2 2 0 010-4h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 012.83-2.83l.06.06A1.65 1.65 0 009 4.68a1.65 1.65 0 001-1.51V3a2 2 0 014 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 2.83l-.06.06A1.65 1.65 0 0019.4 9a1.65 1.65 0 001.51 1H21a2 2 0 010 4h-.09a1.65 1.65 0 00-1.51 1z" />
            </svg>
            Services
        </a>
        <a href="about.php"<?php if ($active_page === 'about') echo ' class="active"'; ?>>
            <svg viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="10" />
                <line x1="12" y1="16" x2="12" y2="12" />
                <line x1="12" y1="8" x2="12.01" y2="8" />
            </svg>
            About
        </a>
    </nav>

    <!-- ==================== SCROLL CONTROLS ==================== -->
    <div class="scroll-controls" aria-label="Page scroll controls">
        <button id="btnScrollUp" aria-label="Scroll Up">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="18 15 12 9 6 15"></polyline></svg>
        </button>
        <button id="btnScrollDown" aria-label="Scroll Down">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"></polyline></svg>
        </button>
    </div>

    <script src="js/script.js" defer></script>
</body>

</html>

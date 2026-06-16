<?php
/**
 * Shared Footer â€” Antique Furniture Workshop
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

    <!-- ==================== COOKIE CONSENT BANNER ==================== -->
    <div class="cookie-consent" id="cookieConsent" role="dialog" aria-label="Cookie consent">
        <div class="cookie-settings-panel" id="cookieSettingsPanel">
            <div class="cookie-setting-item">
                <div class="cookie-setting-info">
                    <h5>Essential Cookies</h5>
                    <p>Required for the website to function. Cannot be disabled.</p>
                </div>
                <label class="cookie-toggle">
                    <input type="checkbox" checked disabled>
                    <span class="cookie-toggle-slider"></span>
                </label>
            </div>
            <div class="cookie-setting-item">
                <div class="cookie-setting-info">
                    <h5>Analytics Cookies</h5>
                    <p>Help us understand how visitors interact with our website.</p>
                </div>
                <label class="cookie-toggle">
                    <input type="checkbox" id="cookieAnalytics" checked>
                    <span class="cookie-toggle-slider"></span>
                </label>
            </div>
            <div class="cookie-setting-item">
                <div class="cookie-setting-info">
                    <h5>Marketing Cookies</h5>
                    <p>Used to deliver relevant advertisements and track campaigns.</p>
                </div>
                <label class="cookie-toggle">
                    <input type="checkbox" id="cookieMarketing">
                    <span class="cookie-toggle-slider"></span>
                </label>
            </div>
        </div>
        <div class="cookie-consent-inner">
            <div class="cookie-consent-content">
                <div class="cookie-icon">ðŸª</div>
                <div class="cookie-text">
                    <h4>We Value Your Privacy</h4>
                    <p>We use cookies to enhance your browsing experience, serve personalized content, and analyze our traffic. By clicking "Accept All", you consent to our use of cookies. <a href="contact.php">Learn more</a></p>
                </div>
            </div>
            <div class="cookie-consent-actions">
                <button class="cookie-btn cookie-btn-settings" id="cookieSettingsBtn">âš™ Preferences</button>
                <button class="cookie-btn cookie-btn-decline" id="cookieDecline">Decline</button>
                <button class="cookie-btn cookie-btn-accept" id="cookieAccept">Accept All</button>
            </div>
        </div>
    </div>

    <!-- ==================== SCROLL CONTROLS ==================== -->
    <div class="scroll-controls" aria-label="Page scroll controls">
        <button id="btnScrollUp" aria-label="Scroll Up">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="18 15 12 9 6 15"></polyline></svg>
        </button>
        <button id="btnScrollDown" aria-label="Scroll Down">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"></polyline></svg>
        </button>
    </div>

    <script src="js/particles.js?v=1.0" defer></script>
    <script src="js/hero-warp.js?v=1.0" defer></script>
    <script src="js/hero-shapes.js?v=1.0" defer></script>
    <script src="js/script.js?v=2.2" defer></script>
    <script src="js/nav-gsap.js?v=1.0" defer></script>
    <script src="js/animations-gsap.js?v=1.0" defer></script>
</body>

</html>

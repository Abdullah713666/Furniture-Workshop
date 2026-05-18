<?php
/**
 * Shared Header — Antique Furniture Workshop
 * 
 * Include this at the top of every public page.
 * Before including, set: $page_title, $page_description, $active_page
 */
require_once __DIR__ . '/../config/database.php';

$page_title = $page_title ?? 'Antique Furniture Workshop';
$page_description = $page_description ?? 'Expert antique furniture restoration, custom commissions, and conservation services.';
$active_page = $active_page ?? 'home';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($page_description); ?>">
    <meta name="keywords" content="antique furniture, restoration, woodworking, custom commissions, conservation">
    <link rel="icon" href="images/favicon.svg" type="image/svg+xml">
    <link rel="stylesheet" href="css/style.css?v=2.0">
</head>

<body>

    <!-- Skip to content link for accessibility -->
    <a href="#main-content" class="skip-link">Skip to main content</a>

    <!-- ==================== TOP HEADER ==================== -->
    <header class="top-header" role="banner">
        <?php if ($active_page !== 'home'): ?>
        <a href="index.php" class="header-icon" aria-label="Go back">
            <svg viewBox="0 0 24 24">
                <polyline points="15 18 9 12 15 6" />
            </svg>
        </a>
        <?php else: ?>
        <button class="hamburger" aria-label="Toggle menu" aria-expanded="false">
            <span></span><span></span><span></span>
        </button>
        <?php endif; ?>
        
        <div class="logo"><a href="index.php"><?php 
            if ($active_page === 'home') echo 'Antique Workshop';
            elseif ($active_page === 'about') echo 'About Us';
            elseif ($active_page === 'contact') echo 'Contact';
            else echo 'Antique Workshop';
        ?></a></div>
        
        <nav class="desktop-nav" aria-label="Main navigation">
            <a href="index.php"<?php if ($active_page === 'home') echo ' class="active"'; ?>>Home</a>
            <a href="gallery.php"<?php if ($active_page === 'gallery') echo ' class="active"'; ?>>Collection</a>
            <a href="services.php"<?php if ($active_page === 'services') echo ' class="active"'; ?>>Services</a>
            <a href="about.php"<?php if ($active_page === 'about') echo ' class="active"'; ?>>About</a>
            <a href="faq.php"<?php if ($active_page === 'faq') echo ' class="active"'; ?>>FAQ</a>
            <a href="contact.php"<?php if ($active_page === 'contact') echo ' class="active"'; ?>>Contact</a>
        </nav>
        
        <div class="header-right-actions" style="display: flex; align-items: center; justify-content: flex-end; gap: 15px;">
            <a href="gallery.php?search=focus" class="header-icon" aria-label="Search Collection" title="Search Collection" style="width: auto; height: auto;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 20px; height: 20px; opacity: 0.85; transition: opacity 0.3s;">
                    <circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
            </a>
            
            <?php if ($active_page !== 'home'): ?>
            <button class="hamburger" aria-label="Toggle menu" aria-expanded="false" style="margin: 0;">
                <span></span><span></span><span></span>
            </button>
            <?php endif; ?>
        </div>
    </header>

    <!-- Mobile Slide Menu -->
    <nav class="mobile-menu" aria-label="Mobile navigation">
        <a href="index.php"<?php if ($active_page === 'home') echo ' class="active"'; ?>>Home</a>
        <a href="gallery.php"<?php if ($active_page === 'gallery') echo ' class="active"'; ?>>Collection</a>
        <a href="services.php"<?php if ($active_page === 'services') echo ' class="active"'; ?>>Services</a>
        <a href="about.php"<?php if ($active_page === 'about') echo ' class="active"'; ?>>About</a>
        <a href="faq.php"<?php if ($active_page === 'faq') echo ' class="active"'; ?>>FAQ</a>
        <a href="contact.php"<?php if ($active_page === 'contact') echo ' class="active"'; ?>>Contact</a>
    </nav>

    <!-- Main content starts -->
    <main id="main-content">

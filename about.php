<?php
/**
 * About Page — Antique Furniture Workshop
 * Dynamic version: fetches timeline events from database
 */
require_once 'config/database.php';

$page_title = 'About Us — Antique Furniture Workshop';
$page_description = 'Learn about our legacy of antique furniture restoration since 1985. Master craftsmen preserving history one piece at a time.';
$active_page = 'about';

// Fetch timeline events
$db = getDB();
$timeline = $db->query("SELECT * FROM timeline_events ORDER BY display_order ASC")->fetchAll();

// Fetch philosophy text from settings
$philosophy = getSetting('philosophy_text', 'We believe restoration is an act of preservation.');

require_once 'includes/header.php';
?>

    <!-- ==================== ABOUT HERO IMAGE ==================== -->
    <section style="padding: 70px 20px 0;">
        <img class="about-hero-image" src="images/about-craftsman.jpg"
            alt="Master craftsman working on antique furniture" loading="lazy">
    </section>

    <!-- ==================== LEGACY & CRAFT ==================== -->
    <section class="philosophy-section fade-up">
        <h2><em>Legacy<br>& Craft</em></h2>

        <div class="philosophy-block mt-3">
            <h4>Our Philosophy</h4>
            <p><?php echo htmlspecialchars($philosophy); ?></p>
        </div>
    </section>

    <!-- ==================== TIMELINE ==================== -->
    <section class="timeline fade-up" aria-label="Our Heritage">
        <h2>Our Heritage</h2>

        <?php foreach ($timeline as $event): ?>
        <div class="timeline-item fade-up">
            <div class="timeline-dot"></div>
            <span class="timeline-year"><?php echo htmlspecialchars($event['year']); ?></span>
            <h3><?php echo htmlspecialchars($event['title']); ?></h3>
            <p><?php echo htmlspecialchars($event['description']); ?></p>
        </div>
        <?php endforeach; ?>
    </section>

    <!-- ==================== CTA BANNER ==================== -->
    <div class="cta-banner fade-up">
        <p class="italic-quote">"Preserving the past for the future."</p>
        <a href="contact.php" class="btn btn-primary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="2" y="4" width="20" height="16" rx="2" />
                <polyline points="22,4 12,13 2,4" />
            </svg>
            Inquire Restoration
        </a>
    </div>

<?php require_once 'includes/footer.php'; ?>

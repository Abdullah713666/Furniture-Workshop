<?php
/**
 * Services Page — Antique Furniture Workshop
 * Dynamic version: fetches services from database
 */
require_once 'config/database.php';

$page_title = 'Our Services — Antique Furniture Workshop';
$page_description = 'Expert antique restoration, custom commissions, and professional consultation services. Master craftsmanship preserving history.';
$active_page = 'services';

// Fetch active services
$db = getDB();
$services = $db->query("SELECT * FROM services WHERE is_active = 1 ORDER BY display_order ASC")->fetchAll();

require_once 'includes/header.php';
?>

    <!-- ==================== PAGE HERO ==================== -->
    <section class="page-hero fade-up">
        <h1>Our Expertise</h1>
        <span class="underline-accent"></span>
        <p class="section-subtitle mt-2" style="margin: 16px auto 0;">Master craftsmanship preserving history, one detail at a time.</p>
    </section>

    <!-- ==================== SERVICES LIST ==================== -->
    <div class="services-list stagger-children">
        <?php foreach ($services as $service): ?>
        <article class="service-card fade-up">
            <img class="service-image" src="<?php echo htmlspecialchars($service['image_path']); ?>" alt="<?php echo htmlspecialchars($service['alt_text']); ?>" loading="lazy">

            <div class="service-body">
                <div class="service-icon">
                    <?php echo $service['icon_svg']; ?>
                </div>
                <h3><?php echo htmlspecialchars($service['title']); ?></h3>
                <p><?php echo htmlspecialchars($service['description']); ?></p>
                <a href="<?php echo htmlspecialchars($service['cta_link']); ?>" class="cta-link"><?php echo htmlspecialchars($service['cta_text']); ?></a>
            </div>
        </article>
        <?php endforeach; ?>
    </div>

    <!-- ==================== CTA ==================== -->
    <div style="padding: 20px;" class="fade-up">
        <a href="contact.php" class="btn btn-primary btn-block" style="max-width: 500px; margin: 0 auto; display: flex;">
            Book a Consultation
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;">
                <path d="M7 17l9.2-9.2M17 17V7H7" />
            </svg>
        </a>
    </div>

<?php require_once 'includes/footer.php'; ?>

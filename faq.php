<?php
/**
 * FAQ Page â€” Antique Furniture Workshop
 * Redesigned with accordion cards, search, and polished layout
 */
require_once 'config/database.php';

$page_title = 'FAQs â€” Antique Furniture Workshop';
$page_description = 'Frequently asked questions about antique restoration, pricing, and our workshop services.';
$active_page = 'faq';

// Fetch active FAQs ordered by display_order
try {
    $db = getDB();
    $faqs = $db->query("SELECT * FROM faqs WHERE is_active = 1 ORDER BY display_order ASC")->fetchAll();
} catch (Exception $e) {
    $faqs = [];
}

require_once 'includes/header.php';
?>

    <!-- ==================== FAQ HEADER ==================== -->
    <section class="faq-page-header fade-up">
        <span class="hero-tag">Common Questions</span>
        <h1>Knowledge & <span class="accent-text">Care</span></h1>
        <p>Everything you need to know about restoring and preserving your treasured antiques.</p>
        
    </section>

    <!-- ==================== FAQ ACCORDION ==================== -->
    <section class="faq-section" aria-label="Frequently Asked Questions">
        <div class="faq-wrapper">
            <?php if (!empty($faqs)): ?>
                <?php foreach ($faqs as $index => $faq): ?>
                <div class="faq-card fade-up" data-question="<?php echo strtolower(strip_tags($faq['question'])); ?>" data-answer="<?php echo strtolower(strip_tags($faq['answer'])); ?>">
                    <button class="faq-card-question" aria-expanded="false" aria-controls="faq-answer-<?php echo $index; ?>">
                        <span class="faq-card-number"><?php echo str_pad($index + 1, 2, '0', STR_PAD_LEFT); ?></span>
                        <span style="flex:1;"><?php echo htmlspecialchars($faq['question']); ?></span>
                        <svg class="faq-card-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="6 9 12 15 18 9"/>
                        </svg>
                    </button>
                    <div class="faq-card-answer" id="faq-answer-<?php echo $index; ?>" role="region">
                        <p><?php echo nl2br(htmlspecialchars($faq['answer'])); ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="text-center fade-up" style="padding: 40px;">
                    <p>No FAQs available at the moment. Please contact us for any questions.</p>
                    <a href="contact.php" class="btn btn-primary" style="margin-top: 20px;">Get in Touch</a>
                </div>
            <?php endif; ?>
        </div>

        <!-- CTA -->
        <div class="cta-banner fade-up" style="margin-top: 60px;">
            <p class="italic-quote">"Have a specific question about your piece?"</p>
            <p style="margin-bottom: 24px; color: var(--text-secondary);">Send us a photo and description of your antique for a free restoration estimate.</p>
            <a href="contact.php" class="btn btn-primary pulse-glow">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="4" width="20" height="16" rx="2"/><polyline points="22,4 12,13 2,4"/></svg>
                Contact Our Experts
            </a>
        </div>
    </section>

    <!-- FAQ accordion + search JS -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Accordion toggle
        document.querySelectorAll('.faq-card-question').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var card = this.closest('.faq-card');
                var isActive = card.classList.contains('active');
                
                // Close all
                document.querySelectorAll('.faq-card').forEach(function(c) {
                    c.classList.remove('active');
                    c.querySelector('.faq-card-question').setAttribute('aria-expanded', 'false');
                });
                
                // Open clicked if it wasn't already open
                if (!isActive) {
                    card.classList.add('active');
                    this.setAttribute('aria-expanded', 'true');
                }
            });
        });


    });
    </script>

<?php require_once 'includes/footer.php'; ?>

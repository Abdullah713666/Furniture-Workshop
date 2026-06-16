<?php
/**
 * Homepage â€” Antique Furniture Workshop
 * Dynamic version: fetches featured pieces from database
 */
require_once 'config/database.php';

$page_title = 'Antique Furniture Workshop â€” The Art of Restoration';
$page_description = 'Expert antique furniture restoration, custom commissions, and conservation services. Preserving the past for the future since 1985.';
$active_page = 'home';

// Fetch featured gallery items
$db = getDB();
$featured = $db->query("SELECT * FROM gallery_items WHERE is_featured = 1 ORDER BY display_order ASC LIMIT 6")->fetchAll();

// Fetch featured testimonials
$testimonials = $db->query("SELECT * FROM testimonials WHERE is_featured = 1 ORDER BY id ASC LIMIT 3")->fetchAll();

require_once 'includes/header.php';
?>

    <!-- ==================== HERO SECTION ==================== -->
    <section class="hero" aria-label="Hero">
        <div class="hero-bg" aria-hidden="true"></div>
        <div class="hero-particles" aria-hidden="true"></div>
        
        <div class="hero-split" style="z-index: 4;">
            <!-- Left: Text Content -->
            <div class="hero-content fade-up">
                <span class="hero-tag">Est. 1985</span>
                <h1>The Art of<br><span class="accent-text">Restoration</span></h1>
                <p>Transforming forgotten treasures into timeless masterpieces with meticulous craftsmanship and passion.</p>
                <div style="display: flex; gap: 12px; flex-wrap: wrap;">
                    <a href="gallery.php" class="btn btn-outline">View Collection</a>
                    <a href="contact.php" class="btn btn-primary pulse-glow">Get a Quote</a>
                </div>
            </div>

            <!-- Right: Floating Stats Panel -->
            <div class="hero-stats-panel fade-up" aria-label="Workshop achievements">
                <div class="hero-stats-inner">
                    <div class="hero-stat-row">
                        <div class="hero-stat-number" data-target="35" data-suffix="+">0</div>
                        <div class="hero-stat-text">Years Experience</div>
                    </div>
                    <div class="hero-stat-divider"></div>
                    <div class="hero-stat-row">
                        <div class="hero-stat-number" data-target="1200" data-suffix="+">0</div>
                        <div class="hero-stat-text">Restored Pieces</div>
                    </div>
                    <div class="hero-stat-divider"></div>
                    <div class="hero-stat-row">
                        <div class="hero-stat-number" data-target="3" data-suffix="">0</div>
                        <div class="hero-stat-text">Generations Craftsmanship</div>
                    </div>
                    <div class="hero-stat-divider"></div>
                    <div class="hero-stat-row">
                        <div class="hero-stat-number" data-target="98" data-suffix="%">0</div>
                        <div class="hero-stat-text">Client Satisfaction</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ==================== SECTION DIVIDER ==================== -->
    <div class="section-divider"></div>

    <!-- ==================== BEFORE / AFTER SLIDER ==================== -->
    <section class="before-after-section" aria-label="Before and after restoration">
        <div class="section-header text-center fade-up" style="padding: 0 20px; margin-bottom: 30px;">
            <h2 style="text-align:center;">The <span class="accent-text">Transformation</span></h2>
            <p class="section-subtitle" style="margin: 10px auto 0; text-align:center;">Drag the slider to see the restoration magic.</p>
        </div>

        <div class="before-after-container scale-reveal">
            <img src="images/after-restoration.jpg" alt="Furniture after restoration" class="ba-before" loading="lazy">
            <img src="images/before-restoration.jpg" alt="Furniture before restoration" class="ba-after" loading="lazy">
            <div class="ba-slider-line"></div>
            <div class="ba-slider-handle">
                <svg viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/><polyline points="15 18 9 12 15 6"/></svg>
            </div>
            <span class="ba-label ba-label-before">Before</span>
            <span class="ba-label ba-label-after">After</span>
        </div>
    </section>

    <!-- ==================== SECTION DIVIDER ==================== -->
    <div class="section-divider"></div>

    <!-- ==================== FEATURED PIECES ==================== -->
    <section aria-label="Featured pieces">
        <div class="section-header slide-left" style="padding: 0 20px;">
            <h2>Featured <span class="accent-text">Craft</span></h2>
            <p class="section-subtitle">Hand-selected restorations from our latest collection.</p>
        </div>

        <div class="featured-grid stagger-children" style="padding: 20px;">
            <?php foreach ($featured as $piece): ?>
            <article class="piece-card fade-up shimmer-card tilt-card">
                <div class="card-image-wrapper">
                    <img class="card-image" src="<?php echo htmlspecialchars($piece['image_path']); ?>" alt="<?php echo htmlspecialchars($piece['alt_text']); ?>" loading="lazy">
                </div>
                <?php if (!empty($piece['tag'])): ?>
                <span class="card-tag"><?php echo htmlspecialchars($piece['tag']); ?></span>
                <?php endif; ?>
                <div class="card-body">
                    <h3 class="card-title"><?php echo htmlspecialchars($piece['title']); ?></h3>
                    <p class="card-desc"><?php echo htmlspecialchars($piece['description']); ?></p>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- ==================== SECTION DIVIDER ==================== -->
    <div class="section-divider"></div>

    <!-- ==================== OUR PROCESS ==================== -->
    <section class="process-section" aria-label="Our restoration process">
        <div class="section-header text-center fade-up" style="padding: 0 20px;">
            <h2 style="text-align:center;">Our <span class="accent-text">Process</span></h2>
            <p class="section-subtitle" style="margin: 10px auto 0; text-align:center;">From assessment to delivery â€” every step handled with care.</p>
        </div>

        <div class="process-grid" style="margin-top: 30px;">
            <div class="process-step fade-up">
                <div class="process-number">1</div>
                <div>
                    <h4>Assessment</h4>
                    <p>We examine your piece, document its condition, and create a restoration plan.</p>
                </div>
            </div>
            <div class="process-step fade-up">
                <div class="process-number">2</div>
                <div>
                    <h4>Design</h4>
                    <p>We agree on scope, materials, timeline and provide a transparent quote.</p>
                </div>
            </div>
            <div class="process-step fade-up">
                <div class="process-number">3</div>
                <div>
                    <h4>Craft</h4>
                    <p>Master craftsmen execute the restoration using traditional techniques.</p>
                </div>
            </div>
            <div class="process-step fade-up">
                <div class="process-number">4</div>
                <div>
                    <h4>Delivery</h4>
                    <p>Your restored piece is carefully delivered back to its rightful place.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- ==================== HERITAGE OF LUXURY ==================== -->
    <section class="heritage-section fade-up" aria-label="Heritage of Luxury">
        <h2>Heritage of <span class="accent-text">Luxury</span></h2>
        <p class="section-subtitle mb-3">Three decades of preserving history through master craftsmanship.</p>

        <div class="heritage-features stagger-children">
            <div class="heritage-feature fade-up shimmer-card">
                <div class="heritage-feature-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/>
                    </svg>
                </div>
                <div>
                    <h4>Expert Restoration</h4>
                    <p>Meticulous French polishing and structural repair by master craftsmen.</p>
                </div>
            </div>
            <div class="heritage-feature fade-up shimmer-card">
                <div class="heritage-feature-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                    </svg>
                </div>
                <div>
                    <h4>Timeless Quality</h4>
                    <p>Every restoration is built to endure another century of use and beauty.</p>
                </div>
            </div>
            <div class="heritage-feature fade-up shimmer-card">
                <div class="heritage-feature-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z"/>
                    </svg>
                </div>
                <div>
                    <h4>Crafted with Passion</h4>
                    <p>We treat every piece as if it were our own treasured heirloom.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- ==================== TESTIMONIALS ==================== -->
    <?php if (!empty($testimonials)): ?>
    <section class="testimonials-section fade-up" aria-label="Client Testimonials">
        <div class="section-header text-center" style="padding: 0 20px;">
            <h2>Client <span class="accent-text">Testimonials</span></h2>
            <p class="section-subtitle mx-auto">Words from those who entrusted their heritage to us.</p>
        </div>

        <div class="testimonials-grid stagger-children" style="padding: 20px;">
            <?php foreach ($testimonials as $testim): ?>
            <article class="testimonial-card fade-up shimmer-card">
                <div class="testimonial-icon text-accent mb-2">
                    <svg viewBox="0 0 24 24" fill="currentColor" stroke="none" style="width: 24px; height: 24px; color: var(--accent);">
                        <path d="M14.017 18L14.017 10.609C14.017 4.905 17.748 1.039 23 0L23.995 2.151C21.563 3.068 20 5.789 20 8H24V18H14.017ZM0 18V10.609C0 4.905 3.748 1.038 9 0L9.996 2.151C7.563 3.068 6 5.789 6 8H9.983L9.983 18L0 18Z" />
                    </svg>
                </div>
                <p class="quote">"<?php echo htmlspecialchars($testim['quote']); ?>"</p>
                <div class="client-info mt-2">
                    <h4><?php echo htmlspecialchars($testim['client_name']); ?></h4>
                    <?php if (!empty($testim['client_title'])): ?>
                    <span class="client-title"><?php echo htmlspecialchars($testim['client_title']); ?></span>
                    <?php endif; ?>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <!-- ==================== CTA BANNER ==================== -->
    <div class="cta-banner fade-up">
        <p class="italic-quote">"Preserving the past for the future."</p>
        <a href="contact.php" class="btn btn-primary pulse-glow">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="4" width="20" height="16" rx="2"/><polyline points="22,4 12,13 2,4"/></svg>
            Inquire Restoration
        </a>
    </div>

    <!-- ==================== CHATBOT WIDGET ==================== -->
    <button class="chatbot-toggle" aria-label="Open chat assistant">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/>
        </svg>
    </button>

    <div class="chatbot-panel" role="dialog" aria-label="Chat assistant">
        <div class="chatbot-header">
            <h4><span class="status-dot"></span> Workshop Assistant</h4>
            <button class="chatbot-close" aria-label="Close chat">&times;</button>
        </div>
        <div class="chatbot-messages">
            <div class="chat-msg bot">Hello! ðŸ‘‹ I'm the Workshop Assistant. Ask me about our restoration services, pricing, hours, or anything else!</div>
        </div>
        <div class="chat-suggestions">
            <button class="chat-suggestion-btn">Restoration services</button>
            <button class="chat-suggestion-btn">Pricing info</button>
            <button class="chat-suggestion-btn">How long does it take?</button>
            <button class="chat-suggestion-btn">Location & hours</button>
        </div>
        <div class="chatbot-input">
            <input type="text" placeholder="Type your question..." aria-label="Chat message">
            <button aria-label="Send message">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/>
                </svg>
            </button>
        </div>
    </div>

<?php require_once 'includes/footer.php'; ?>

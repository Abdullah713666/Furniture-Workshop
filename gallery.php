<?php
/**
 * Gallery / Collection Page — Antique Furniture Workshop
 * Dynamic version: fetches all gallery items from database
 */
require_once 'config/database.php';

$page_title = 'The Collection — Antique Furniture Workshop';
$page_description = 'Explore our curated selection of restored masterpieces and handcrafted antique furniture. Browse by category.';
$active_page = 'gallery';

// Fetch all gallery items
$db = getDB();
$items = $db->query("SELECT * FROM gallery_items ORDER BY display_order ASC")->fetchAll();

// Get unique categories for filter buttons
$categories = $db->query("SELECT DISTINCT category FROM gallery_items ORDER BY category ASC")->fetchAll(PDO::FETCH_COLUMN);

require_once 'includes/header.php';
?>

    <!-- ==================== GALLERY HEADER ==================== -->
    <section class="gallery-header fade-up">
        <h1>The<br><span class="accent-text">Collection</span></h1>
        <p class="section-subtitle mt-1">Explore our curated selection of restored masterpieces and handcrafted furniture.</p>
    </section>

    <!-- ==================== FILTER & SEARCH BAR ==================== -->
    <div class="filter-search-container fade-up" style="display: flex; flex-wrap: wrap; gap: 20px; align-items: center; padding: 10px 20px 20px; justify-content: space-between;">
        <div class="filter-bar" role="tablist" aria-label="Filter gallery" style="padding: 0; margin: 0;">
            <button class="filter-btn active" data-filter="all" role="tab" aria-selected="true">All</button>
            <?php foreach ($categories as $cat): ?>
            <button class="filter-btn" data-filter="<?php echo htmlspecialchars($cat); ?>" role="tab" aria-selected="false"><?php echo htmlspecialchars(ucfirst($cat)); ?></button>
            <?php endforeach; ?>
        </div>
        <div class="search-bar" style="position: relative; max-width: 300px; width: 100%; flex-grow: 1;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); width: 18px; color: var(--text-muted);">
                <circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line>
            </svg>
            <input type="text" id="gallerySearch" class="form-control" placeholder="Search by title or keyword..." aria-label="Search by title or keyword" style="padding-left: 44px; border-radius: 30px;">
        </div>
    </div>

    <!-- ==================== GALLERY GRID ==================== -->
    <div class="gallery-grid stagger-children" role="tabpanel">
        <?php foreach ($items as $item): ?>
        <div class="gallery-item fade-up" data-category="<?php echo htmlspecialchars($item['category']); ?>" data-title="<?php echo htmlspecialchars(strtolower($item['title'])); ?>" data-desc="<?php echo htmlspecialchars(strtolower($item['description'])); ?>">
            <img src="<?php echo htmlspecialchars($item['image_path']); ?>" alt="<?php echo htmlspecialchars($item['alt_text']); ?>" loading="lazy">
            <div class="gallery-item-info">
                <span class="gallery-item-title"><?php echo htmlspecialchars($item['title']); ?></span>
                <?php if (!empty($item['tag'])): ?>
                <span class="gallery-item-tag"><?php echo htmlspecialchars($item['tag']); ?></span>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- ==================== LIGHTBOX ==================== -->
    <div class="lightbox" role="dialog" aria-label="Image preview">
        <button class="lightbox-close" aria-label="Close preview">&times;</button>
        <img src="" alt="Gallery preview">
    </div>

<?php require_once 'includes/footer.php'; ?>

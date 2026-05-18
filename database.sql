-- ============================================================
-- Antique Furniture Workshop — Database Schema
-- MySQL 5.7+ / MariaDB 10.3+
-- ============================================================

CREATE DATABASE IF NOT EXISTS `antique_workshop` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `antique_workshop`;

-- ============================================================
-- 1. Gallery Items
-- ============================================================
CREATE TABLE IF NOT EXISTS `gallery_items` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(255) NOT NULL,
    `description` TEXT,
    `category` VARCHAR(50) NOT NULL DEFAULT 'restoration',
    `image_path` VARCHAR(255) NOT NULL,
    `alt_text` VARCHAR(255) DEFAULT '',
    `is_featured` TINYINT(1) NOT NULL DEFAULT 0,
    `tag` VARCHAR(50) DEFAULT '',
    `display_order` INT NOT NULL DEFAULT 0,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Clean existing data to prevent duplicates on re-install
TRUNCATE TABLE `gallery_items`;

-- Seed: gallery items (each with a unique image)
INSERT INTO `gallery_items` (`title`, `description`, `category`, `image_path`, `alt_text`, `is_featured`, `tag`, `display_order`) VALUES
('Victorian Armchair', 'c. 1870 — Full French polish restoration', 'restoration', 'images/featured-victorian-armchair.jpg', 'Restored Victorian armchair', 1, 'Restored', 1),
('Heritage Oak Table', 'Bespoke 12-seat dining table in English oak', 'handcrafted', 'images/featured-oak-table.jpg', 'Handcrafted oak dining table', 1, 'Handcrafted', 2),
('Gilded Rococo Mirror', '18th-century gold leaf frame restoration', 'restoration', 'images/featured-gilded-mirror.jpg', 'Gilded antique mirror frame', 1, 'Restored', 3),
('Chesterfield Sofa', 'Hand-tufted leather sofa with brass nail-head trim', 'restoration', 'images/gallery-chesterfield-sofa.png', 'Luxurious Chesterfield sofa', 1, 'Restored', 4),
('Regency Bookcase', 'Mahogany bookcase with glass-fronted doors', 'handcrafted', 'images/gallery-regency-bookcase.png', 'Elegant Regency bookcase', 1, 'Handcrafted', 5),
('Four-Poster Bed', 'Mahogany bed frame with carved acanthus posts', 'baroque', 'images/gallery-four-poster-bed.png', 'Antique four-poster bed', 1, 'Baroque', 6),
('Antique Armoire', 'Restored 19th-century wardrobe in solid oak', 'restoration', 'images/gallery-antique-armoire.jpg', 'Antique wooden armoire', 0, '', 7),
('Grandfather Clock', 'Fully serviced longcase clock, c. 1890', 'restoration', 'images/gallery-grandfather-clock.jpg', 'Grandfather clock', 0, '', 8),
('Oak Table Surface', 'Detail of hand-planed English oak tabletop', 'handcrafted', 'images/gallery-oak-surface.jpg', 'Oak table surface detail', 0, '', 9),
('Handcrafted Chair', 'Contemporary chair in traditional joinery', 'handcrafted', 'images/gallery-handcrafted-chair.jpg', 'Handcrafted wooden chair', 0, '', 10),
('Baroque Armchair', 'Upholstered armchair with cabriole legs', 'baroque', 'images/gallery-baroque-armchair.jpg', 'Baroque style armchair', 0, '', 11),
('Antique Dresser', 'Victorian-era dresser with original mirror', 'restoration', 'images/gallery-antique-dresser.jpg', 'Antique dresser with mirror', 0, '', 12),
('Baroque Writing Desk', 'Ornate desk with gilded drawer pulls', 'baroque', 'images/gallery-baroque-desk.jpg', 'Baroque writing desk', 0, '', 13),
('Console Table', 'Slim entryway table in reclaimed timber', 'handcrafted', 'images/gallery-console-table.jpg', 'Handcrafted console table', 0, '', 14),
('Carved Sideboard', 'Walnut sideboard with intricate scrollwork', 'handcrafted', 'images/gallery-carved-sideboard.png', 'Carved antique sideboard', 0, '', 15),
('Art Deco Vanity', 'Lacquered vanity with gold inlay and mirror', 'restoration', 'images/gallery-art-deco-vanity.png', 'Art Deco vanity desk', 0, '', 16),
('Antique Rocking Chair', 'Cherry wood rocker with turned spindles', 'handcrafted', 'images/gallery-rocking-chair.png', 'Antique rocking chair', 0, '', 17);

-- ============================================================
-- 2. Services
-- ============================================================
CREATE TABLE IF NOT EXISTS `services` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(255) NOT NULL,
    `description` TEXT NOT NULL,
    `image_path` VARCHAR(255) NOT NULL,
    `alt_text` VARCHAR(255) DEFAULT '',
    `icon_svg` TEXT,
    `cta_text` VARCHAR(100) NOT NULL DEFAULT 'Learn More →',
    `cta_link` VARCHAR(255) NOT NULL DEFAULT 'contact.php',
    `display_order` INT NOT NULL DEFAULT 0,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Clean existing data to prevent duplicates
TRUNCATE TABLE `services`;

-- Seed: services
INSERT INTO `services` (`title`, `description`, `image_path`, `alt_text`, `icon_svg`, `cta_text`, `cta_link`, `display_order`) VALUES
('Antique Restoration', 'Meticulous French polishing, structural repair, and veneer conservation to bring your treasured pieces back to life while respecting their history.', 'images/service-restoration.jpg', 'Antique restoration workshop', '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M14.7 6.3a1 1 0 000 1.4l1.6 1.6a1 1 0 001.4 0l3.77-3.77a6 6 0 01-7.94 7.94l-6.91 6.91a2.12 2.12 0 01-3-3l6.91-6.91a6 6 0 017.94-7.94l-3.76 3.76z" /></svg>', 'View Details →', 'contact.php', 1),
('Custom Commissions', 'Bespoke furniture designed and built to your exact specifications. From initial sketch to final lacquer, we create future heirlooms.', 'images/service-custom.jpg', 'Custom woodworking commission', '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" /></svg>', 'Start Project →', 'contact.php', 2),
('Consultation', 'Expert appraisals, historical verification, and interior styling advice. Understand the true value and story behind your collection.', 'images/service-consultation.jpg', 'Furniture consultation and appraisal', '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="11" cy="11" r="8" /><line x1="21" y1="21" x2="16.65" y2="16.65" /></svg>', 'Learn More →', 'contact.php', 3);

-- ============================================================
-- 3. Testimonials
-- ============================================================
CREATE TABLE IF NOT EXISTS `testimonials` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `client_name` VARCHAR(255) NOT NULL,
    `client_title` VARCHAR(255) DEFAULT '',
    `quote` TEXT NOT NULL,
    `is_featured` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Clean existing data
TRUNCATE TABLE `testimonials`;

INSERT INTO `testimonials` (`client_name`, `client_title`, `quote`, `is_featured`) VALUES
('Lady Margaret Ashworth', 'Antique Collector, London', 'The team restored my grandmother''s Victorian writing desk to absolute perfection. Every detail was handled with extraordinary care.', 1),
('James Whitfield', 'Interior Designer', 'Their custom commission work is unmatched. The bespoke dining table they crafted is now the centrepiece of my client''s estate.', 1),
('Dr. Eleanor Voss', 'Museum Curator', 'Professional, knowledgeable, and deeply respectful of historical integrity. I trust them with our most precious artefacts.', 1);

-- ============================================================
-- 4. Timeline Events (About page)
-- ============================================================
CREATE TABLE IF NOT EXISTS `timeline_events` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `year` VARCHAR(10) NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `description` TEXT NOT NULL,
    `display_order` INT NOT NULL DEFAULT 0,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Clean existing data
TRUNCATE TABLE `timeline_events`;

INSERT INTO `timeline_events` (`year`, `title`, `description`, `display_order`) VALUES
('1985', 'Workshop Founded', 'Started in a small garage in Tuscany with a single set of chisels.', 1),
('2005', 'Royal Commission', 'Honored to restore an 18th-century writing desk for the Royal Estate.', 2),
('2023', 'Studio Expansion', 'Opening our new flagship workshop and gallery in London.', 3);

-- ============================================================
-- 5. Contact Submissions
-- ============================================================
CREATE TABLE IF NOT EXISTS `contact_submissions` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `email` VARCHAR(255) NOT NULL,
    `phone` VARCHAR(50) DEFAULT '',
    `service_interest` VARCHAR(100) DEFAULT '',
    `message` TEXT NOT NULL,
    `is_read` TINYINT(1) NOT NULL DEFAULT 0,
    `submitted_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- 6. Site Settings
-- ============================================================
CREATE TABLE IF NOT EXISTS `site_settings` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `setting_key` VARCHAR(100) NOT NULL UNIQUE,
    `setting_value` TEXT
) ENGINE=InnoDB;

-- Clean existing data
TRUNCATE TABLE `site_settings`;

INSERT INTO `site_settings` (`setting_key`, `setting_value`) VALUES
('site_name', 'Antique Workshop'),
('site_tagline', 'The Art of Restoration'),
('site_description', 'Expert antique furniture restoration, custom commissions, and conservation services. Preserving the past for the future since 1985.'),
('phone', '+1 (555) 019-2834'),
('email', 'hello@antiqueworkshop.com'),
('address_line1', '123 Heritage Lane'),
('address_line2', 'Craftsmanship City, CA 90210'),
('working_hours', 'Mon-Fri, 9am - 6pm'),
('instagram_url', '#'),
('twitter_url', '#'),
('copyright_year', '2024'),
('philosophy_text', 'We believe restoration is an act of preservation—not just of wood and fabric, but of history itself. Every scratch tells a story, every grain holds a memory. Our mission is to honor the original artisan''s hand while breathing new life into timeless pieces.');

-- ============================================================
-- 7. Admin Users
-- ============================================================
CREATE TABLE IF NOT EXISTS `admin_users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(100) NOT NULL UNIQUE,
    `password_hash` VARCHAR(255) NOT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Default admin user (password: admin123) — CHANGE THIS after first login!
-- Hash generated with password_hash('admin123', PASSWORD_BCRYPT)
INSERT INTO `admin_users` (`username`, `password_hash`) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- ============================================================
-- 8. Site Users (Public Login / Signup)
-- ============================================================
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `full_name` VARCHAR(255) NOT NULL,
    `email` VARCHAR(255) NOT NULL UNIQUE,
    `password_hash` VARCHAR(255) NOT NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
-- ============================================================
-- 9. FAQs
-- ============================================================
CREATE TABLE IF NOT EXISTS `faqs` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `question` TEXT NOT NULL,
    `answer` TEXT NOT NULL,
    `display_order` INT NOT NULL DEFAULT 0,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Clean existing data
TRUNCATE TABLE `faqs`;

INSERT INTO `faqs` (`question`, `answer`, `display_order`) VALUES
('How long does a typical restoration take?', 'Timeline varies by piece. Small repairs take 1-2 weeks, while full French polishing and structural restoration can take 4-8 weeks.', 1),
('Do you provide pickup and delivery?', 'Yes, we offer professional transportation for oversized pieces within a 50-mile radius of our London workshop.', 2),
('Can you provide a valuation for my antique?', 'While we are primarily restorers, we can offer informal appraisals and historical context for your furniture. For formal insurance valuations, we recommend our Consultation service.', 3),
('Is restoration worth the cost?', 'In most cases, yes. Restoration not only preserves the beauty and utility of a piece but also maintains its historical and financial value for future generations.', 4),
('How should I care for my restored furniture?', 'Avoid direct sunlight and radiators. Dust regularly with a soft, lint-free cloth and avoid modern chemical sprays. We recommend high-quality beeswax once or twice a year.', 5);

# Graph Report - .  (2026-06-13)

## Corpus Check
- 62 files · ~318,425 words
- Verdict: corpus is large enough that graph structure adds value.

## Summary
- 154 nodes · 178 edges · 50 communities (32 shown, 18 thin omitted)
- Extraction: 79% EXTRACTED · 21% INFERRED · 0% AMBIGUOUS · INFERRED: 38 edges (avg confidence: 0.8)
- Token cost: 0 input · 0 output

## Community Hubs (Navigation)
- [[_COMMUNITY_Admin Backend & Data Layer|Admin Backend & Data Layer]]
- [[_COMMUNITY_Frontend Features & Interactivity|Frontend Features & Interactivity]]
- [[_COMMUNITY_Core Page Routing & CMS|Core Page Routing & CMS]]
- [[_COMMUNITY_Visual Effects & Navigation|Visual Effects & Navigation]]
- [[_COMMUNITY_Authentication & Security|Authentication & Security]]
- [[_COMMUNITY_Traditional Furniture Gallery|Traditional Furniture Gallery]]
- [[_COMMUNITY_Public Pages & Settings|Public Pages & Settings]]
- [[_COMMUNITY_Elegant Furniture Collection|Elegant Furniture Collection]]
- [[_COMMUNITY_Handcrafted Pieces|Handcrafted Pieces]]
- [[_COMMUNITY_Form Security & CSRF|Form Security & CSRF]]
- [[_COMMUNITY_BeforeAfter Restoration|Before/After Restoration]]
- [[_COMMUNITY_Database Setup|Database Setup]]
- [[_COMMUNITY_Admin Particles Include|Admin Particles Include]]
- [[_COMMUNITY_Admin Sidebar Include|Admin Sidebar Include]]
- [[_COMMUNITY_Admin Logout Handler|Admin Logout Handler]]
- [[_COMMUNITY_GetDB Function|GetDB Function]]
- [[_COMMUNITY_GetSetting Function|GetSetting Function]]
- [[_COMMUNITY_CICD Workflow|CI/CD Workflow]]
- [[_COMMUNITY_Craftsman Image|Craftsman Image]]
- [[_COMMUNITY_Favicon|Favicon]]
- [[_COMMUNITY_Featured Gilded Mirror|Featured Gilded Mirror]]
- [[_COMMUNITY_Featured Oak Table|Featured Oak Table]]
- [[_COMMUNITY_README Documentation|README Documentation]]
- [[_COMMUNITY_Admin Users Table|Admin Users Table]]
- [[_COMMUNITY_Categories Table|Categories Table]]
- [[_COMMUNITY_Site Settings Table|Site Settings Table]]
- [[_COMMUNITY_Timeline Events Table|Timeline Events Table]]

## God Nodes (most connected - your core abstractions)
1. `getDB()` - 21 edges
2. `requireLogin()` - 14 edges
3. `Contact Page & Form Handler` - 8 edges
4. `Homepage` - 8 edges
5. `Admin Dashboard` - 7 edges
6. `Admin Login` - 6 edges
7. `Categories CRUD` - 6 edges
8. `Contact Page & Form Handler` - 6 edges
9. `Gallery / Collection Page` - 6 edges
10. `Services Page` - 6 edges

## Surprising Connections (you probably didn't know these)
- `Admin Hub` --calls--> `getDB()`  [EXTRACTED]
  admin/index.php → config/database.php
- `FAQ Page` --references--> `FAQs Table`  [EXTRACTED]
  faq.php → database.sql
- `Gallery Category Filter & Search` --shares_data_with--> `Gallery Items Table`  [INFERRED]
  js/script.js → database.sql
- `afw_ensure_auth_schema()` --calls--> `getDB()`  [EXTRACTED]
  admin/auth.php → config/database.php
- `About Page` --calls--> `getDB()`  [EXTRACTED]
  about.php → config/database.php

## Import Cycles
- None detected.

## Hyperedges (group relationships)
- **Admin CRUD Page Pattern** — admin_categories_categoriesphp, admin_gallery_galleryphp, admin_faqs_faqsphp, admin_services_servicesphp, admin_timeline_timelinephp [INFERRED 0.95]
- **Admin Authentication Guard Pattern** — admin_auth_requirelogin, admin_auth_isloggedin, admin_login_loginphp, admin_logout_logoutphp [INFERRED 0.95]
- **Centralized Database Access Hub** — config_database_getdb, config_database_getsetting, config_init_initphp, admin_auth_requirelogin [INFERRED 0.85]
- **Dynamic Content Management System** — config_database, root_faq, root_gallery, root_services, root_index, pattern_dynamic_cms [EXTRACTED 1.00]
- **JavaScript Animation & Visual Layer** — js_animations_gsap, js_nav_gsap, js_particles, feature_gsap_scroll, feature_particle_engine [EXTRACTED 1.00]
- **Form Security & Submission Pattern** — root_contact, feature_csrf_recaptcha, feature_form_ajax, pattern_form_security, pattern_ajax_submission [EXTRACTED 1.00]
- **Traditional Ornate Furniture Collection** — images_featured_victorian_armchair, images_gallery_baroque_armchair, images_gallery_baroque_desk, images_gallery_carved_sideboard, images_gallery_four_poster_bed, images_gallery_grandfather_clock [INFERRED 0.85]
- **Workshop Service Pipeline (Research, Design, Restore)** — images_service_consultation, images_service_custom, images_service_restoration, images_hero_bg, images_gallery_oak_surface [INFERRED 0.95]
- **Bedroom Furniture Suite** — images_gallery_four_poster_bed, images_gallery_antique_dresser, images_gallery_art_deco_vanity, images_gallery_antique_armoire [INFERRED 0.85]

## Communities (50 total, 18 thin omitted)

### Community 0 - "Admin Backend & Data Layer"
Cohesion: 0.17
Nodes (22): requireLogin(), Categories CRUD, Admin Dashboard, FAQs CRUD, Gallery CRUD, Inventory Management, Messages Management, Reports & Analytics (+14 more)

### Community 1 - "Frontend Features & Interactivity"
Cohesion: 0.12
Nodes (14): Before/After Comparison Slider, Keyword-based Chatbot, Cookie Consent Banner, AJAX Form Submission, Gallery Category Filter & Search, GSAP ScrollTrigger Animations, addChatMsg(), deleteCookie() (+6 more)

### Community 2 - "Core Page Routing & CMS"
Cohesion: 0.24
Nodes (13): count_words() Function, sanitize_input() Function, Dynamic Content Management Pattern, Contact Page & Form Handler, FAQ Page, Gallery / Collection Page, Homepage, URL Router (+5 more)

### Community 3 - "Visual Effects & Navigation"
Cohesion: 0.21
Nodes (7): Responsive Navigation System, Canvas Particle Engine, draw(), init(), Particle, resize(), Shared Design Tokens (JS)

### Community 4 - "Authentication & Security"
Cohesion: 0.20
Nodes (10): afw_ensure_auth_schema(), isLoggedIn(), Admin Hub, Admin Login, Admin Settings, Email Verification, Brute Force Login Protection, Session Fixation Prevention (+2 more)

### Community 5 - "Traditional Furniture Gallery"
Cohesion: 0.29
Nodes (10): Victorian Wingback Armchair (Featured Image), Baroque Carved Armchair, Baroque Writing Desk with Quill, Carved Walnut Sideboard, Grandfather Clock, Oak Wood Grain Texture, Workshop Tools (Hero Background), Consultation Research Materials (+2 more)

### Community 6 - "Public Pages & Settings"
Cohesion: 0.38
Nodes (6): About Page, getSetting(), Contact Page & Form Handler, count_words(), sanitize_input(), site_settings Table

### Community 7 - "Elegant Furniture Collection"
Cohesion: 0.33
Nodes (6): Antique Carved Armoire, Antique Dresser with Oval Mirror, Art Deco Vanity with Gold Inlay, Leather Chesterfield Sofa, Four-Poster Canopy Bed, Regency Glass-Door Bookcase

### Community 8 - "Handcrafted Pieces"
Cohesion: 0.67
Nodes (3): Mid-Century Modern Console Table, Handcrafted Minimalist Chair, Traditional Wooden Rocking Chair

## Knowledge Gaps
- **39 isolated node(s):** `Application Initialization`, `Admin Logout`, `Particle Background Include`, `Admin Sidebar Navigation`, `faqs Table` (+34 more)
  These have ≤1 connection - possible missing edges or undocumented components.
- **18 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `getDB()` connect `Admin Backend & Data Layer` to `Core Page Routing & CMS`, `Authentication & Security`, `Public Pages & Settings`?**
  _High betweenness centrality (0.189) - this node is a cross-community bridge._
- **Why does `Contact Page & Form Handler` connect `Core Page Routing & CMS` to `Frontend Features & Interactivity`?**
  _High betweenness centrality (0.089) - this node is a cross-community bridge._
- **Why does `Homepage` connect `Core Page Routing & CMS` to `Frontend Features & Interactivity`?**
  _High betweenness centrality (0.038) - this node is a cross-community bridge._
- **What connects `Application Initialization`, `Admin Logout`, `Particle Background Include` to the rest of the system?**
  _47 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `Frontend Features & Interactivity` be split into smaller, more focused modules?**
  _Cohesion score 0.12280701754385964 - nodes in this community are weakly interconnected._
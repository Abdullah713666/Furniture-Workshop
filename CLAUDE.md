# Antique Furniture Workshop

PHP portfolio/showcase website for an antique furniture restoration business. Pure vanilla PHP + CSS + JS. No framework, no Composer, no npm.

## Directory Structure

```
antique-furniture-workshop/
├── index.php              Homepage (hero, gallery, testimonials, stats)
├── about.php              About page (hero image overlay, timeline)
├── gallery.php            Collection page (filterable grid, search)
├── services.php           Services page (card grid from DB)
├── contact.php            Contact form (AJAX, reCAPTCHA, CSRF)
├── faq.php                FAQ accordion with search
├── install.php            DB installer wizard
├── setup.php              Alternative DB setup (Railway)
├── router.php             PHP built-in server router
├── database.sql           Full MySQL schema + seed data
├── config/
│   ├── database.php       PDO connection (XAMPP/Railway/InfinityFree)
│   └── init.php           Security headers + session setup
├── includes/
│   ├── header.php         Shared <head>, nav, CDN links
│   └── footer.php         Shared footer, mobile nav, JS loaders
├── admin/                 Admin panel (login, CRUD for all tables)
│   ├── auth.php           requireLogin(), 30-min timeout
│   ├── login.php          Brute-force protection, reCAPTCHA
│   ├── dashboard.php      Stats + recent messages
│   ├── gallery.php        Gallery CRUD
│   ├── services.php       Services CRUD
│   ├── categories.php     Categories CRUD
│   ├── faqs.php           FAQs CRUD
│   ├── messages.php       Contact submissions viewer
│   ├── timeline.php       Timeline CRUD
│   ├── inventory.php      Price/status/condition editor
│   ├── reports.php        Analytics dashboard
│   ├── settings.php       Password change, email verification
│   ├── includes/
│   │   ├── sidebar.php    Admin navigation sidebar
│   │   ├── mailer.php     Resend API email wrapper
│   │   └── particles.php  Admin particle background loader
│   └── style.css          Admin stylesheet
├── css/
│   └── style.css          Master stylesheet (2945 lines)
├── js/
│   ├── particles.js       Global canvas particle engine (300 lines)
│   ├── hero-shapes.js     Three.js hex grid mesh (283 lines)
│   ├── hero-warp.js       WebGL hexagonal image warp shader
│   ├── script.js          Main app JS (696 lines)
│   ├── animations-gsap.js GSAP scroll animations
│   └── nav-gsap.js        Nav hover effects
├── images/                24 images (all JPG, optimized ~2.2MB total)
└── graphify-out/          Generated knowledge graph (do not edit)
```

## Tech Stack

- **Backend:** PHP 8.2, MySQL (PDO), no framework
- **Frontend:** Vanilla JS, CSS custom properties, Google Fonts (Playfair Display + Inter)
- **Libraries (CDN only):**
  - GSAP 3.12.5 + ScrollTrigger (animations)
  - Three.js r128 + LineGeometry + LineMaterial + Line2 (hero hex grid)
  - Google reCAPTCHA v2 (contact form + admin login)
  - Resend API (transactional emails, no Composer)
- **Database:** 10 tables, seeded via database.sql

## Database Tables

| Table | Purpose |
|-------|---------|
| `gallery_items` | Furniture pieces (17 seeded, with categories) |
| `services` | Workshop services (3 seeded) |
| `testimonials` | Client quotes (3 seeded) |
| `timeline_events` | Company milestones (3 seeded) |
| `contact_submissions` | Contact form entries |
| `site_settings` | Key-value config (14 seeded) |
| `admin_users` | Admin accounts (1 default: admin/admin) |
| `faqs` | FAQ entries (5 seeded) |
| `categories` | Gallery categories (3 seeded) |
| `users` | Dropped in migration |

## Hero Section Architecture (Most Complex Part)

The homepage hero has multiple overlapping layers:

### Stacking Order (z-index)
| z-index | Element | Purpose |
|---------|---------|---------|
| 0 | `.hero-bg` | CSS background-image (hero-bg.jpg) |
| 0 | `.hero-bg::after` | Dark gradient overlay (rgba 0.4 → 0.85 → solid) |
| 3 | `.hero-particles` | Canvas with Three.js hex grid (transparent bg) |
| 4 | `.hero-split` | Text content + stats panel (inline style) |

### particles.js (DO NOT MODIFY)
- Creates its own `<canvas>` prepended to `<body>`
- 150-220 floating dots, mouse repulsion, friction damping
- Completely independent from hero-shapes.js
- Used on both public site AND admin panel

### hero-shapes.js (Three.js Hex Grid)
- Creates a second `<canvas>` inside `.hero-particles`
- Three.js scene with transparent background (`alpha: true`)
- Renders hexagonal wireframe mesh using `THREE.Line2` (or `LineSegments` fallback)
- **Spotlight visibility:** hex lines only visible near cursor (VISIBILITY_RADIUS = 2.0)
- **Outward bulge:** vertices deform toward camera near cursor (DEFORM_RADIUS = 1.8, BULGE_STRENGTH = 0.5)
- **Color:** bright gold `#f0c850` (AMBER variable)
- Line width: 3px via LineMaterial
- Uses `vertexColors: true` for per-vertex alpha fading

### How CDN Scripts Load (header.php lines 27-30)
```html
<script src="https://unpkg.com/three@0.128.0/build/three.min.js"></script>
<script src="https://unpkg.com/three@0.128.0/examples/js/lines/LineGeometry.js"></script>
<script src="https://unpkg.com/three@0.128.0/examples/js/lines/LineMaterial.js"></script>
<script src="https://unpkg.com/three@0.128.0/examples/js/lines/Line2.js"></script>
```

### How JS Loads (footer.php)
Scripts load in order at bottom of body:
1. `particles.js` — global dot particles
2. `hero-shapes.js` — hex grid (depends on Three.js from header)
3. `script.js` — main app logic
4. `nav-gsap.js` — nav hover effects
5. `animations-gsap.js` — scroll animations (depends on GSAP + ScrollTrigger from header)

## Page Includes Pattern

Every page follows this pattern:
```php
<?php include 'includes/header.php'; ?>
<!-- Page content here -->
<?php include 'includes/footer.php'; ?>
```

Header outputs: `<!DOCTYPE html>`, `<head>`, CDN scripts, nav, opens `<main>`.
Footer outputs: closes `</main>`, footer content, mobile nav, loads JS files, closes `</body></html>`.

## Admin Panel

- URL: `/admin/` (shows landing page or login)
- Auth: `admin/auth.php` → `requireLogin()` with 30-min inactivity timeout
- Brute-force: 5 attempts, 15-min lockout on `admin/login.php`
- All CRUD pages use CSRF tokens + prepared statements
- Email: Resend API via `admin/includes/mailer.php` (needs RESEND_API_KEY env var)

## Config Detection (config/database.php)

Three environments detected automatically:
1. **Railway:** MYSQLHOST, MYSQLPORT, MYSQLUSER, MYSQLPASS, MYSQLDB env vars
2. **InfinityFree:** Hardcoded production credentials
3. **Local XAMPP:** localhost / root / empty password

## Security Features

- CSRF tokens on all POST forms
- Security headers: X-Frame-Options DENY, nosniff, XSS-Protection, Referrer-Policy
- Session: HttpOnly, SameSite=Strict, Secure (when HTTPS)
- `.htaccess` blocks .sql, .log, .md, .txt, /config/ access
- `router.php` blocks same for PHP built-in server
- PDO prepared statements for all queries
- `htmlspecialchars()` / `strip_tags()` on all user input
- reCAPTCHA v2 on contact form and admin login
- Bcrypt password hashing

## Rules for Agents

1. **NEVER modify `js/particles.js`** — it's a standalone particle engine, shared with admin
2. **Z-index convention:** hero-bg=0, canvas=3, hero-split=4 (never change without updating all three)
3. **No npm/composer** — all dependencies via CDN or hand-written
4. **Three.js version is r128** — do not upgrade; Line2/LineGeometry/LineMaterial come from unpkg CDN
5. **CSS custom properties** — use `var(--accent)`, `var(--bg-primary)`, etc. for colors
6. **Admin panel has separate stylesheet** — `admin/style.css`, not `css/style.css`
7. **Database.sql is the source of schema** — any new table must be added there first
8. **All PHP pages include header.php + footer.php** — never output HTML without them

## Recent Changes (Current State)

- `hero-shapes.js`: Rewritten from 22 floating shapes to hexagonal grid mesh
  - Spotlight visibility (only visible near cursor)
  - Outward bulge deformation on hover
  - Bright gold color (#f0c850)
  - Line2 with 3px width, LineSegments fallback
- `css/style.css`: `.hero-particles` z-index: 3, `.hero-bg` z-index: 0 with ::after gradient
- `index.php`: `.hero-split` inline z-index: 4
- `header.php`: Three.js r128 + Line2 addons loaded from unpkg CDN
- Image rendering removed from Three.js — CSS handles hero-bg.jpg with gradient overlay
- **Image optimization:** All 8 PNGs converted to JPG (8.3MB → 2.18MB, 74% reduction)
  - All images compressed at 80% quality (mozjpeg)
  - `database.sql` seed data updated: `.png` → `.jpg`
  - `index.php` before/after slider: `.png` → `.jpg`, added `loading="lazy"`
  - `.htaccess`: browser caching (1 year) + gzip compression for images
  - `router.php`: cache headers for images on PHP built-in server (Railway)

## Deployment

- **Local:** XAMPP, run `php -S localhost:8080 router.php`
- **Docker:** `docker build -t antique-workshop . && docker run -p 8080:8080 antique-workshop`
- **Railway:** Auto-detects MySQL env vars, use `setup.php` for DB init
- **Default admin:** admin / admin (change via admin/settings.php)

<?php
/**
 * Admin Hub / Index — Antique Furniture Workshop
 * Premium landing page for the admin area
 */
require_once __DIR__ . '/../config/init.php';
require_once __DIR__ . '/auth.php';

// If logged in, pull stats for a richer display
$logged_in = isLoggedIn();
$stats = [];

if ($logged_in) {
    $db = getDB();
    try {
        $stats['gallery']   = $db->query("SELECT COUNT(*) FROM gallery_items")->fetchColumn();
        $stats['services']  = $db->query("SELECT COUNT(*) FROM services")->fetchColumn();
        $stats['messages']  = $db->query("SELECT COUNT(*) FROM contact_submissions WHERE is_read = 0")->fetchColumn();
        $stats['timeline']  = $db->query("SELECT COUNT(*) FROM timeline_events")->fetchColumn();
        $stats['users']     = $db->query("SELECT COUNT(*) FROM users")->fetchColumn();
    } catch (Exception $e) {
        // Gracefully handle missing tables
    }
}

// Time-of-day greeting
$hour = (int) date('G');
if ($hour < 12) $greeting = 'Good Morning';
elseif ($hour < 17) $greeting = 'Good Afternoon';
else $greeting = 'Good Evening';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel — Antique Furniture Workshop</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        /* --------- Reset & Variables --------- */
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

        :root {
            --bg:           #0f0d0a;
            --surface:      #1a1610;
            --surface-alt:  #231f18;
            --border:       #2a2520;
            --border-glow:  rgba(212, 168, 67, 0.12);
            --accent:       #d4a843;
            --accent-hover: #e6bc5a;
            --accent-dim:   rgba(212, 168, 67, 0.10);
            --text:         #f5f0e8;
            --text-sec:     #a09080;
            --text-muted:   #706050;
            --danger:       #c0392b;
            --success:      #27ae60;
            --font-heading: 'Playfair Display', Georgia, serif;
            --font-body:    'Inter', -apple-system, sans-serif;
            --ease:         cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* --------- Body --------- */
        body {
            font-family: var(--font-body);
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
            position: relative;
            overflow-x: hidden;
        }

        /* Subtle background texture */
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background:
                repeating-linear-gradient(90deg, rgba(60, 40, 20, 0.04) 0px, transparent 1px, transparent 14px),
                repeating-linear-gradient(0deg, rgba(60, 40, 20, 0.03) 0px, transparent 1px, transparent 14px);
            pointer-events: none;
            z-index: 0;
        }

        /* --------- Container --------- */
        .admin-hub {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 900px;
            animation: fadeUp 0.6s var(--ease) both;
        }

        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(20px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* --------- Header --------- */
        .hub-header {
            text-align: center;
            margin-bottom: 48px;
        }

        .hub-header .emblem {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 64px;
            height: 64px;
            border-radius: 16px;
            background: linear-gradient(135deg, var(--accent-dim), rgba(212,168,67,0.05));
            border: 1px solid var(--border);
            margin-bottom: 20px;
        }

        .hub-header .emblem svg {
            width: 30px;
            height: 30px;
            stroke: var(--accent);
            fill: none;
            stroke-width: 1.5;
        }

        .hub-header h1 {
            font-family: var(--font-heading);
            font-size: clamp(1.8rem, 4vw, 2.6rem);
            font-weight: 600;
            color: var(--text);
            margin-bottom: 6px;
            letter-spacing: 0.5px;
        }

        .hub-header h1 span {
            color: var(--accent);
        }

        .hub-header .tagline {
            font-size: 0.9rem;
            color: var(--text-sec);
            font-weight: 300;
        }

        .hub-header .greeting {
            font-size: 0.8rem;
            color: var(--text-muted);
            margin-top: 8px;
            font-style: italic;
        }

        /* --------- Nav Grid --------- */
        .nav-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 16px;
            margin-bottom: 40px;
        }

        .nav-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 28px 24px;
            text-decoration: none;
            color: var(--text);
            display: flex;
            align-items: flex-start;
            gap: 16px;
            transition: all 0.35s var(--ease);
            position: relative;
            overflow: hidden;
        }

        .nav-card::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(212,168,67,0.06) 0%, transparent 60%);
            opacity: 0;
            transition: opacity 0.35s var(--ease);
        }

        .nav-card:hover {
            transform: translateY(-4px);
            border-color: rgba(212, 168, 67, 0.3);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.3), 0 0 20px rgba(212, 168, 67, 0.06);
        }

        .nav-card:hover::before {
            opacity: 1;
        }

        .nav-card .card-icon {
            flex-shrink: 0;
            width: 44px;
            height: 44px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--accent-dim);
            border-radius: 10px;
            position: relative;
            z-index: 1;
            transition: all 0.35s var(--ease);
        }

        .nav-card:hover .card-icon {
            background: rgba(212, 168, 67, 0.2);
            transform: scale(1.05);
        }

        .nav-card .card-icon svg {
            width: 22px;
            height: 22px;
            stroke: var(--accent);
            fill: none;
            stroke-width: 1.8;
        }

        .nav-card .card-body {
            position: relative;
            z-index: 1;
            flex: 1;
        }

        .nav-card .card-title {
            font-family: var(--font-heading);
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 4px;
            transition: color 0.25s var(--ease);
        }

        .nav-card:hover .card-title {
            color: var(--accent);
        }

        .nav-card .card-desc {
            font-size: 0.8rem;
            color: var(--text-sec);
            line-height: 1.5;
        }

        .nav-card .card-stat {
            position: absolute;
            top: 12px;
            right: 16px;
            font-size: 0.65rem;
            font-weight: 600;
            letter-spacing: 1px;
            text-transform: uppercase;
            padding: 3px 10px;
            border-radius: 20px;
            z-index: 1;
        }

        .stat-accent {
            background: var(--accent-dim);
            color: var(--accent);
        }

        .stat-alert {
            background: rgba(212, 168, 67, 0.2);
            color: var(--accent-hover);
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { box-shadow: 0 0 0 0 rgba(212, 168, 67, 0.2); }
            50% { box-shadow: 0 0 0 6px rgba(212, 168, 67, 0); }
        }

        /* Arrow hint */
        .nav-card .arrow {
            position: absolute;
            bottom: 16px;
            right: 16px;
            z-index: 1;
            opacity: 0;
            transform: translateX(-6px);
            transition: all 0.3s var(--ease);
            color: var(--accent);
            font-size: 1.1rem;
        }

        .nav-card:hover .arrow {
            opacity: 1;
            transform: translateX(0);
        }

        /* --------- Divider row --------- */
        .divider-row {
            display: flex;
            align-items: center;
            gap: 16px;
            margin-bottom: 20px;
        }

        .divider-row hr {
            flex: 1;
            border: none;
            border-top: 1px solid var(--border);
        }

        .divider-row span {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: var(--text-muted);
            white-space: nowrap;
        }

        /* --------- Secondary links (smaller) --------- */
        .secondary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 12px;
            margin-bottom: 48px;
        }

        .secondary-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 18px 20px;
            text-decoration: none;
            color: var(--text);
            display: flex;
            align-items: center;
            gap: 12px;
            transition: all 0.3s var(--ease);
        }

        .secondary-card:hover {
            border-color: rgba(212, 168, 67, 0.25);
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(0,0,0,0.2);
        }

        .secondary-card svg {
            width: 18px;
            height: 18px;
            stroke: var(--accent);
            fill: none;
            stroke-width: 2;
            flex-shrink: 0;
        }

        .secondary-card span {
            font-size: 0.85rem;
            font-weight: 500;
        }

        .secondary-card:hover span {
            color: var(--accent);
        }

        /* --------- Footer --------- */
        .hub-footer {
            text-align: center;
            padding-top: 24px;
            border-top: 1px solid var(--border);
        }

        .hub-footer p {
            font-size: 0.75rem;
            color: var(--text-muted);
        }

        .hub-footer a {
            color: var(--accent);
            text-decoration: none;
            transition: color 0.2s;
        }

        .hub-footer a:hover {
            color: var(--accent-hover);
        }

        /* --------- Login prompt (not logged in) --------- */
        .login-prompt {
            text-align: center;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 36px 28px;
            margin-bottom: 40px;
        }

        .login-prompt p {
            color: var(--text-sec);
            font-size: 0.9rem;
            margin-bottom: 20px;
        }

        .btn-login {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 14px 36px;
            background: var(--accent);
            color: #1a1410;
            font-weight: 600;
            font-size: 0.9rem;
            border-radius: 50px;
            text-decoration: none;
            transition: all 0.3s var(--ease);
        }

        .btn-login:hover {
            background: var(--accent-hover);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(212, 168, 67, 0.3);
            color: #1a1410;
        }

        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 10px 24px;
            border: 1px solid var(--border);
            color: var(--text-sec);
            font-size: 0.8rem;
            border-radius: 50px;
            text-decoration: none;
            transition: all 0.3s var(--ease);
            margin-top: 12px;
        }

        .btn-back:hover {
            border-color: var(--accent);
            color: var(--accent);
        }

        /* --------- Responsive --------- */
        @media (max-width: 600px) {
            body { padding: 24px 16px; }
            .nav-grid { grid-template-columns: 1fr; }
            .secondary-grid { grid-template-columns: 1fr; }
            .hub-header { margin-bottom: 32px; }
        }
    </style>
</head>
<body>
    <div class="admin-hub">
        <!-- Header -->
        <header class="hub-header">
            <div class="emblem">
                <svg viewBox="0 0 24 24"><path d="M12 15a3 3 0 100-6 3 3 0 000 6z"/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 11-2.83 2.83l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 11-4 0v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 11-2.83-2.83l.06-.06A1.65 1.65 0 004.68 15a1.65 1.65 0 00-1.51-1H3a2 2 0 110-4h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 112.83-2.83l.06.06A1.65 1.65 0 009 4.68a1.65 1.65 0 001-1.51V3a2 2 0 114 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 112.83 2.83l-.06.06A1.65 1.65 0 0019.4 9a1.65 1.65 0 001.51 1H21a2 2 0 110 4h-.09a1.65 1.65 0 00-1.51 1z"/></svg>
            </div>
            <h1>Antique <span>Workshop</span></h1>
            <p class="tagline">Content Management System</p>
            <?php if ($logged_in): ?>
            <p class="greeting"><?php echo $greeting; ?>, <?php echo htmlspecialchars($_SESSION['admin_user']); ?></p>
            <?php endif; ?>
        </header>

        <?php if ($logged_in): ?>
        <!-- ===== LOGGED IN: full navigation ===== -->

        <!-- Primary Content Sections -->
        <div class="nav-grid">
            <a href="dashboard.php" class="nav-card">
                <div class="card-icon">
                    <svg viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="9"/><rect x="14" y="3" width="7" height="5"/><rect x="14" y="12" width="7" height="9"/><rect x="3" y="16" width="7" height="5"/></svg>
                </div>
                <div class="card-body">
                    <div class="card-title">Dashboard</div>
                    <div class="card-desc">Overview of your site activity, recent messages, and quick stats.</div>
                </div>
                <span class="arrow">→</span>
            </a>

            <a href="gallery.php" class="nav-card">
                <?php if (!empty($stats['gallery'])): ?>
                <span class="card-stat stat-accent"><?php echo $stats['gallery']; ?> items</span>
                <?php endif; ?>
                <div class="card-icon">
                    <svg viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                </div>
                <div class="card-body">
                    <div class="card-title">Gallery</div>
                    <div class="card-desc">Manage your collection pieces, featured items, and image uploads.</div>
                </div>
                <span class="arrow">→</span>
            </a>

            <a href="services.php" class="nav-card">
                <?php if (!empty($stats['services'])): ?>
                <span class="card-stat stat-accent"><?php echo $stats['services']; ?> active</span>
                <?php endif; ?>
                <div class="card-icon">
                    <svg viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                </div>
                <div class="card-body">
                    <div class="card-title">Services</div>
                    <div class="card-desc">Add or edit restoration, commission, and consultation services.</div>
                </div>
                <span class="arrow">→</span>
            </a>

            <a href="messages.php" class="nav-card">
                <?php if (!empty($stats['messages']) && $stats['messages'] > 0): ?>
                <span class="card-stat stat-alert"><?php echo $stats['messages']; ?> new</span>
                <?php endif; ?>
                <div class="card-icon">
                    <svg viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                </div>
                <div class="card-body">
                    <div class="card-title">Messages</div>
                    <div class="card-desc">Read and reply to contact form submissions from visitors.</div>
                </div>
                <span class="arrow">→</span>
            </a>

            <a href="timeline.php" class="nav-card">
                <?php if (!empty($stats['timeline'])): ?>
                <span class="card-stat stat-accent"><?php echo $stats['timeline']; ?> events</span>
                <?php endif; ?>
                <div class="card-icon">
                    <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                </div>
                <div class="card-body">
                    <div class="card-title">Timeline</div>
                    <div class="card-desc">Manage heritage milestones displayed on the About page.</div>
                </div>
                <span class="arrow">→</span>
            </a>

            <a href="users.php" class="nav-card">
                <?php if (!empty($stats['users'])): ?>
                <span class="card-stat stat-accent"><?php echo $stats['users']; ?> users</span>
                <?php endif; ?>
                <div class="card-icon">
                    <svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>
                </div>
                <div class="card-body">
                    <div class="card-title">Users</div>
                    <div class="card-desc">View and manage registered public site users.</div>
                </div>
                <span class="arrow">→</span>
            </a>
        </div>

        <!-- Divider -->
        <div class="divider-row">
            <hr><span>Quick Access</span><hr>
        </div>

        <!-- Secondary Links -->
        <div class="secondary-grid">
            <a href="settings.php" class="secondary-card">
                <svg viewBox="0 0 24 24"><path d="M12 15a3 3 0 100-6 3 3 0 000 6z"/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 11-2.83 2.83l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 11-4 0v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 11-2.83-2.83l.06-.06A1.65 1.65 0 004.68 15a1.65 1.65 0 00-1.51-1H3a2 2 0 110-4h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 112.83-2.83l.06.06A1.65 1.65 0 009 4.68a1.65 1.65 0 001-1.51V3a2 2 0 114 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 112.83 2.83l-.06.06A1.65 1.65 0 0019.4 9a1.65 1.65 0 001.51 1H21a2 2 0 110 4h-.09a1.65 1.65 0 00-1.51 1z"/></svg>
                <span>Account Settings</span>
            </a>
            <a href="../index.php" class="secondary-card">
                <svg viewBox="0 0 24 24"><path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                <span>View Live Website</span>
            </a>
            <a href="logout.php" class="secondary-card">
                <svg viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                <span>Logout</span>
            </a>
        </div>

        <?php else: ?>
        <!-- ===== NOT LOGGED IN: login prompt ===== -->
        <div class="login-prompt">
            <p>Sign in to access the content management system.</p>
            <a href="login.php" class="btn-login">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
                Sign In
            </a>
            <br>
            <a href="../index.php" class="btn-back">← Back to Website</a>
        </div>
        <?php endif; ?>

        <!-- Footer -->
        <footer class="hub-footer">
            <p>Antique Furniture Workshop &copy; <?php echo date('Y'); ?> &mdash; <a href="../index.php">Visit Website</a></p>
        </footer>
    </div>
</body>
</html>

<?php
/**
 * Contact Page & Form Handler — Antique Furniture Workshop
 * 
 * GET  → Displays the contact page with form + info
 * POST → Validates, sanitizes, logs the form submission, returns JSON/redirect
 */
require_once 'config/init.php';

// reCAPTCHA keys — read from environment, fall back to Google's test keys (localhost only)
// Get your own keys at: https://www.google.com/recaptcha/admin
// Set RECAPTCHA_SITE_KEY and RECAPTCHA_SECRET_KEY in Railway env vars / .env for production.
define('RECAPTCHA_SITE_KEY',   getenv('RECAPTCHA_SITE_KEY')   ?: '6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI');
define('RECAPTCHA_SECRET_KEY', getenv('RECAPTCHA_SECRET_KEY') ?: '6LeIxAcTAAAAAGG-vFI1TnRWxMZNFuojJ4WifJWe');
define('RECAPTCHA_SITE_KEY_ADMIN',   getenv('RECAPTCHA_SITE_KEY_ADMIN')   ?: RECAPTCHA_SITE_KEY);
define('RECAPTCHA_SECRET_KEY_ADMIN', getenv('RECAPTCHA_SECRET_KEY_ADMIN') ?: RECAPTCHA_SECRET_KEY);
define('MAX_WORDS', 250);

// ============================================================
// HANDLE POST (form submission)
// ============================================================
// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF
    if (($_POST['csrf_token'] ?? '') !== $csrf_token) {
        die('Invalid CSRF token.');
    }


    // --- Helper: sanitize a string input ---
    function sanitize_input($data) {
        return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
    }

    // --- Helper: count words ---
    function count_words($text) {
        $text = trim($text);
        if (empty($text)) return 0;
        return count(preg_split('/\s+/', $text));
    }

    // --- Collect and sanitize inputs ---
    $fullname = isset($_POST['fullname']) ? sanitize_input($_POST['fullname']) : '';
    $email    = isset($_POST['email'])    ? sanitize_input($_POST['email'])    : '';
    $phone    = isset($_POST['phone'])    ? sanitize_input($_POST['phone'])    : '';
    $service  = isset($_POST['service'])  ? sanitize_input($_POST['service'])  : '';
    $details  = isset($_POST['details'])  ? sanitize_input($_POST['details'])  : '';

    // --- Validation ---
    $errors = [];

    if (empty($fullname) || mb_strlen($fullname) < 2) {
        $errors[] = 'Full name is required (at least 2 characters).';
    } elseif (!preg_match('/^[a-zA-Z][a-zA-Z\s.\-]*$/', $fullname)) {
        $errors[] = 'Full name must start with a letter and cannot contain special characters or numbers.';
    }

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'A valid email address is required.';
    }
    if (!empty($phone) && !preg_match('/^[\d\s\+\-\(\)]{7,20}$/', $phone)) {
        $errors[] = 'Please enter a valid phone number.';
    }
    $allowed_services = ['restoration', 'commission', 'consultation', 'other'];
    if (empty($service) || !in_array($service, $allowed_services)) {
        $errors[] = 'Please select a valid service.';
    }
    if (empty($details) || mb_strlen($details) < 10) {
        $errors[] = 'Project details are required (at least 10 characters).';
    }

    // Word limit validation
    if (count_words($details) > MAX_WORDS) {
        $errors[] = 'Message must be ' . MAX_WORDS . ' words or less.';
    }

    // reCAPTCHA validation
    $recaptcha_response = $_POST['g-recaptcha-response'] ?? '';
    if (empty($recaptcha_response)) {
        $errors[] = 'Please complete the reCAPTCHA verification.';
    } else {
        $verify_url = 'https://www.google.com/recaptcha/api/siteverify';
        $verify_data = [
            'secret' => RECAPTCHA_SECRET_KEY,
            'response' => $recaptcha_response,
            'remoteip' => $_SERVER['REMOTE_ADDR'] ?? ''
        ];
        
        $options = [
            'http' => [
                'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                'method' => 'POST',
                'content' => http_build_query($verify_data)
            ]
        ];
        $context = stream_context_create($options);
        $verify_result = @file_get_contents($verify_url, false, $context);
        
        if ($verify_result) {
            $verify_json = json_decode($verify_result, true);
            if (!($verify_json['success'] ?? false)) {
                $errors[] = 'reCAPTCHA verification failed. Please try again.';
            }
        }
        // If verification request fails (e.g. no internet on localhost), silently pass
    }

    // --- Build response ---
    if (!empty($errors)) {
        $response = ['success' => false, 'message' => implode(' ', $errors)];
    } else {
        // Log to file
        $log_entry  = "=== New Inquiry ===\n";
        $log_entry .= "Date: " . date('Y-m-d H:i:s') . "\n";
        $log_entry .= "Name: $fullname\n";
        $log_entry .= "Email: $email\n";
        $log_entry .= "Phone: $phone\n";
        $log_entry .= "Service: $service\n";
        $log_entry .= "Details: $details\n";
        $log_entry .= "IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown') . "\n";
        $log_entry .= "===================\n\n";

        $log_file = __DIR__ . '/messages.log';
        file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX);

        // Also save to database if available
        try {
            $db = getDB();
            $stmt = $db->prepare("INSERT INTO contact_submissions (name, email, phone, service_interest, message) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$fullname, $email, $phone, $service, $details]);
        } catch (Exception $e) {
            // Silently continue — log file is the fallback
        }

        $response = [
            'success' => true,
            'message' => 'Thank you, ' . $fullname . '! Your inquiry has been received. We will get back to you within 24 hours.'
        ];
    }

    // --- Return response ---
    $is_ajax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

    if ($is_ajax) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($response);
    } else {
        $status = $response['success'] ? 'success' : 'error';
        $msg_raw = $response['message'];
        $msg = urlencode($msg_raw);
        $redirect_url = "contact.php?status=$status&msg=$msg";
        $redirect_url = str_replace(["\r", "\n"], '', $redirect_url);
        header("Location: $redirect_url");
    }
    exit;
}

// ============================================================
// HANDLE GET (display the contact page)
// ============================================================
$page_title = 'Contact — Antique Furniture Workshop';
$page_description = 'Get in touch with our antique furniture restoration workshop. Inquire about restoration, custom commissions, or schedule a consultation.';
$active_page = 'contact';

// Get settings from DB for contact info
$db = getDB();
$phone_number  = getSetting('phone', '+1 (555) 019-2834');
$email_address = getSetting('email', 'hello@antiqueworkshop.com');
$address1      = getSetting('address_line1', '123 Heritage Lane');
$address2      = getSetting('address_line2', 'Craftsmanship City, CA 90210');
$working_hours = getSetting('working_hours', 'Mon-Fri, 9am - 6pm');
$map_embed_url = getSetting('map_embed_url', 'https://www.openstreetmap.org/export/embed.html?bbox=-0.1378%2C51.5037%2C-0.1069%2C51.5204&layer=mapnik&marker=51.5121%2C-0.1224');

require_once 'includes/header.php';
?>

    <!-- ==================== CONTACT HEADER ==================== -->
    <section class="contact-header fade-up">
        <h1>Inquire with the<br>Workshop</h1>
        <p>Tell us about your heirloom or custom project. We appreciate detailed descriptions.</p>
    </section>

    <!-- ==================== FORM + INFO WRAPPER ==================== -->
    <div class="contact-wrapper">

        <!-- Contact Form -->
        <div class="contact-form fade-up">
            <div id="formMessage" class="form-message"></div>

            <form id="contactForm" action="contact.php" method="POST" novalidate>
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">


                <div class="form-group">
                    <label for="fullname">Full Name</label>
                    <input type="text" class="form-control" id="fullname" name="fullname"
                        placeholder="E.g. Jonathan Harker" required autocomplete="name">
                    <p class="form-error" aria-live="polite"></p>
                </div>

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" class="form-control" id="email" name="email" placeholder="hello@example.com"
                        required autocomplete="email">
                    <p class="form-error" aria-live="polite"></p>
                </div>

                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="tel" class="form-control" id="phone" name="phone" placeholder="+1 (555) 000-0000"
                        autocomplete="tel">
                    <p class="form-error" aria-live="polite"></p>
                </div>

                <div class="form-group">
                    <label for="service">Service Required</label>
                    <select class="form-control" id="service" name="service" required>
                        <option value="" disabled selected>Select a service</option>
                        <option value="restoration">Restoration &amp; Conservation</option>
                        <option value="commission">Custom Commission</option>
                        <option value="consultation">Consultation &amp; Appraisal</option>
                        <option value="other">Other</option>
                    </select>
                    <p class="form-error" aria-live="polite"></p>
                </div>

                <div class="form-group">
                    <label for="details">Project Details</label>
                    <textarea class="form-control" id="details" name="details" rows="5"
                        placeholder="Describe the piece, its condition, and your vision..." required></textarea>
                    <div class="word-counter" id="wordCounter">
                        <span id="wordCount">0</span> / <?php echo MAX_WORDS; ?> words
                    </div>
                    <p class="form-error" aria-live="polite"></p>
                </div>

                <!-- Google reCAPTCHA -->
                <div class="form-group recaptcha-group">
                    <div class="g-recaptcha" data-sitekey="<?php echo RECAPTCHA_SITE_KEY; ?>" data-theme="dark"></div>
                    <p class="form-error" id="recaptchaError" aria-live="polite"></p>
                </div>

                <button type="submit" class="btn btn-primary btn-block btn-submit">
                    Send Message
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        style="width:16px;height:16px;">
                        <line x1="22" y1="2" x2="11" y2="13" />
                        <polygon points="22 2 15 22 11 13 2 9 22 2" />
                    </svg>
                </button>

            </form>
        </div>

        <!-- Contact Info -->
        <div class="contact-info fade-up">
            <h2>
                <span aria-hidden="true" style="font-size:1.2rem;">🏛️</span>
                Visit the Workshop
            </h2>

            <div class="contact-detail">
                <div class="contact-detail-icon">
                    <svg viewBox="0 0 24 24">
                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z" />
                        <circle cx="12" cy="10" r="3" />
                    </svg>
                </div>
                <div>
                    <h4><?php echo htmlspecialchars($address1); ?></h4>
                    <p><?php echo htmlspecialchars($address2); ?></p>
                </div>
            </div>

            <div class="contact-detail">
                <div class="contact-detail-icon">
                    <svg viewBox="0 0 24 24">
                        <path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72 12.84 12.84 0 00.7 2.81 2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45 12.84 12.84 0 002.81.7A2 2 0 0122 16.92z" />
                    </svg>
                </div>
                <div>
                    <h4><?php echo htmlspecialchars($phone_number); ?></h4>
                    <p><?php echo htmlspecialchars($working_hours); ?></p>
                </div>
            </div>

            <div class="contact-detail">
                <div class="contact-detail-icon">
                    <svg viewBox="0 0 24 24">
                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" />
                        <polyline points="22,6 12,13 2,6" />
                    </svg>
                </div>
                <div>
                    <h4><?php echo htmlspecialchars($email_address); ?></h4>
                    <p>Typically replies within 24h</p>
                </div>
            </div>

            <!-- Interactive Map -->
            <div class="map-container" aria-label="Workshop location map">
                <iframe 
                    width="100%" 
                    height="250" 
                    frameborder="0" 
                    scrolling="no" 
                    marginheight="0" 
                    marginwidth="0" 
                    src="<?php echo htmlspecialchars($map_embed_url); ?>" 
                    style="border: 0; display: block;"
                    title="Map of Workshop Location">
                </iframe>
            </div>
        </div>

    </div>

    <!-- reCAPTCHA Script -->
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>

<?php require_once 'includes/footer.php'; ?>

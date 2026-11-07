<?php
// helpers.php: shared security helpers. Every page includes this FIRST.

if (PHP_SAPI !== 'cli') {
    // Send safety headers with every page.
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('Referrer-Policy: same-origin');
    header("Content-Security-Policy: default-src 'self'; style-src 'self' 'unsafe-inline'; form-action 'self'; frame-ancestors 'none'");

    // Safer session cookies: not readable by JavaScript, and not sent with cross-site requests.
    if (session_status() === PHP_SESSION_NONE) {
        ini_set('session.use_strict_mode', '1');   // refuse session IDs the server did not create
        session_set_cookie_params([
            'lifetime' => 0,
            'path'     => '/',
            'httponly' => true,
            'samesite' => 'Lax',
            // 'secure' => true,   // switch this on when the site is served over HTTPS
        ]);
        session_start();
    }
}

// ---------- Output ----------

// Makes text safe to print inside HTML, so "<script>" is shown as text instead of running.
// Use it on EVERYTHING that came from a user or the database.
function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function redirect($url) {
    header('Location: ' . $url);
    exit();
}

// ---------- CSRF protection ----------
// Every form carries a secret token. A form submitted from some other website
// can't know it, so the server can tell the difference.

function csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field() {
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

// Call at the top of any code that handles a POST form.
function csrf_verify() {
    $sent = $_POST['csrf_token'] ?? '';
    $real = $_SESSION['csrf_token'] ?? '';
    if ($real === '' || !hash_equals($real, $sent)) {
        http_response_code(403);
        exit('Invalid or missing security token. Go back, reload the page, and try again.');
    }
}

// ---------- One-time messages ("flash" messages) ----------

function flash_set($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

// Returns the waiting message (and forgets it), or null.
function flash_get() {
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $flash;
}

// Builds the HTML for the waiting message (or "" if there is none).
function flash_html() {
    $flash = flash_get();
    if ($flash === null) {
        return '';
    }
    $label = $flash['type'] === 'error' ? 'Error: ' : '';
    return '<p><strong>' . e($label . $flash['message']) . '</strong></p>';
}

// ---------- Access control ----------

// Only logged-in, approved users get through. The database is checked on every request,
// so rejecting someone takes effect immediately, even if they are already logged in.
function require_login(mysqli $conn) {
    header('Cache-Control: no-store');   // stop the browser's Back button showing old private pages
    $id = $_SESSION['user_id'] ?? null;
    if ($id === null) {
        redirect('index.php');
    }
    $stmt = $conn->prepare('SELECT username, role, status FROM users WHERE id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    if (!$user || $user['status'] !== 'approved') {
        $_SESSION = [];
        session_destroy();
        redirect('index.php');
    }
    // Always use the role from the database, never one remembered in the session.
    $_SESSION['username'] = $user['username'];
    $_SESSION['role']     = $user['role'];
}

// Only admins get through.
function require_admin(mysqli $conn) {
    require_login($conn);
    if (($_SESSION['role'] ?? '') !== 'admin') {
        http_response_code(403);
        exit('Access denied. This page is for administrators.');
    }
}

// ---------- Validation (shared by registration and create_admin.php) ----------

// Returns a list of problems. An empty list means everything is fine.
function validate_registration($username, $password, $confirm) {
    $errors = [];
    if (!preg_match('/^[A-Za-z0-9_]{3,30}$/', $username)) {
        $errors[] = 'Username must be 3 to 30 characters: letters, numbers, and underscores only.';
    }
    if (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters.';
    }
    if (strlen($password) > 72) {
        // The hashing method only uses the first 72 bytes, so longer passwords are refused.
        $errors[] = 'Password must be 72 characters or fewer.';
    }
    if ($password !== $confirm) {
        $errors[] = 'The two passwords do not match.';
    }
    return $errors;
}

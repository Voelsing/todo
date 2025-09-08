<?php
/**
 * CSRF helper providing rotating synchronizer tokens.
 */

const CSRF_TTL = 900; // 15 minutes
const CSRF_MAX_TOKENS = 10;

/**
 * Issue a new CSRF token pair and store it in the session.
 *
 * @return array{id:string,token:string}
 */
function csrf_issue(): array {
    if (!isset($_SESSION['csrf']) || !is_array($_SESSION['csrf'])) {
        $_SESSION['csrf'] = [];
    }

    $now = time();

    // Remove expired or used entries
    foreach ($_SESSION['csrf'] as $key => $data) {
        if (!is_array($data) || ($data['used'] ?? true) || ($data['exp'] ?? 0) < $now) {
            unset($_SESSION['csrf'][$key]);
        }
    }

    // Enforce ring buffer of at most CSRF_MAX_TOKENS
    if (count($_SESSION['csrf']) >= CSRF_MAX_TOKENS) {
        $_SESSION['csrf'] = array_slice($_SESSION['csrf'], -CSRF_MAX_TOKENS + 1, null, true);
    }

    $id = bin2hex(random_bytes(16));
    $token = rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');

    $_SESSION['csrf'][$id] = [
        't'   => $token,
        'exp' => $now + CSRF_TTL,
        'used'=> false,
    ];

    return ['id' => $id, 'token' => $token];
}

/**
 * Verify a POST request's CSRF parameters.
 */
function csrf_verify_post(): bool {
    // New style: verify against issued token pair
    if (isset($_POST['csrf_id'])) {
        $id    = $_POST['csrf_id'];
        $token = $_POST['csrf_token'] ?? '';

        if (!isset($_SESSION['csrf'][$id])) {
            return false;
        }

        $entry = &$_SESSION['csrf'][$id];

        if ($entry['used'] || $entry['exp'] < time()) {
            unset($_SESSION['csrf'][$id]);
            return false;
        }

        if (!hash_equals($entry['t'], $token)) {
            return false;
        }

        $entry['used'] = true;
        return true;
    }

    // Legacy style: single session token
    $token = $_POST['csrf_token'] ?? '';
    if (empty($token) || empty($_SESSION['admin_csrf_token'])) {
        return false;
    }

    $valid = hash_equals($_SESSION['admin_csrf_token'], $token);

    if ($valid) {
        // rotate token after successful check
        $_SESSION['admin_csrf_token'] = bin2hex(random_bytes(32));
    }

    return $valid;
}

/**
 * Ensure a session-based CSRF token exists (legacy support).
 */
function csrf_token(): void {
    if (empty($_SESSION['admin_csrf_token'])) {
        $_SESSION['admin_csrf_token'] = bin2hex(random_bytes(32));
    }
}
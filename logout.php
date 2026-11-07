<?php
require_once 'helpers.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    // Empty the session, delete the browser's session cookie, then destroy the session.
    $_SESSION = [];
    $cookie = session_get_cookie_params();
    setcookie(session_name(), '', time() - 3600, $cookie['path'], $cookie['domain'], $cookie['secure'], $cookie['httponly']);
    session_destroy();

    // Start a fresh session just to carry the "logged out" message.
    session_start();
    flash_set('success', 'You have been logged out.');
}
redirect('index.php');

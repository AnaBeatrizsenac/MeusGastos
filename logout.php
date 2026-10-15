<?php
require __DIR__ . '/includes/bootstrap.php';
if (csrf_ok($_GET['t'] ?? null)) {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', ['expires' => time() - 3600, 'path' => $p['path'], 'httponly' => true, 'samesite' => 'Lax']);
    }
    session_destroy();
    redirect('login.php');
}
redirect(logado() ? 'dashboard.php' : 'login.php');

<?php
declare(strict_types=1);
require_once __DIR__ . '/config.php';
function session_boot(): void {
    static $booted=false; if ($booted) return; $booted=true;
    $secure = strtolower(envv('APP_ENV','dev')) !== 'dev'; // dev는 http 허용
    session_name('SID');
    session_set_cookie_params([
        'lifetime'=>0,'path'=>'/','domain'=>'',
        'secure'=>$secure,'httponly'=>true,'samesite'=>'Strict',
    ]);
    if (session_status() !== PHP_SESSION_ACTIVE) session_start();
}
function auth_login(int $user_id): void {
    session_boot();
    $_SESSION = [
        'uid'=>$user_id,
        'ua'=>substr($_SERVER['HTTP_USER_AGENT'] ?? '',0,200),
        'created_at'=>time(),
    ];
    session_regenerate_id(true);
}
function auth_logout(): void {
    session_boot();
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        setcookie(session_name(), '', time()-42000, '/');
    }
    session_destroy();
}
function auth_require(): int {
    session_boot();
    if (!isset($_SESSION['uid'])) { http_response_code(401); exit('unauth'); }
    // UA 바인딩(완화 가능)
    $ua_now = substr($_SERVER['HTTP_USER_AGENT'] ?? '',0,200);
    if (!hash_equals($_SESSION['ua'] ?? '', $ua_now)) { http_response_code(401); exit('rebind'); }
    return (int)$_SESSION['uid'];
}
function csrf_token(): string {
    session_boot();
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}
function csrf_assert(): void {
    session_boot();
    $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? ($_POST['csrf'] ?? '');
    if (!is_string($token) || $token === '') { http_response_code(403); exit('csrf_missing'); }
    if (!hash_equals($_SESSION['csrf'] ?? '', $token)) { http_response_code(403); exit('csrf_bad'); }
}

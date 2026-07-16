<?php
declare(strict_types=1);

/**
 * 환경변수 helper - 없으면 기본값 반환
 */
function envv(string $key, ?string $default = null): ?string {
    $val = getenv($key);
    if ($val === false) {
        return $default;
    }
    return $val;
}

// ── DB 접속 설정 ────────────────────────────────
// 운영 배포 시에는 실제 서버 환경변수로 주입 (DB_HOST, DB_USER, DB_PASS, DB_NAME)
define('DB_HOST', envv('DB_HOST', '127.0.0.1'));
define('DB_PORT', (int)envv('DB_PORT', '3306'));
define('DB_USER', envv('DB_USER', 'web'));
define('DB_PASS', envv('DB_PASS', 'ChangeMe!'));
define('DB_NAME', envv('DB_NAME', 'web_db'));

define('APP_ENV', envv('APP_ENV', 'dev'));
define('UPLOAD_DIR', __DIR__ . '/uploads/');
define('UPLOAD_URL', '/uploads/');

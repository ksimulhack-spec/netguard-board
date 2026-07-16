<?php
declare(strict_types=1);
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/db.php';

$uid = auth_require();
$id = (int)($_GET['id'] ?? 0);

// [취약점 #4] IDOR: 여기도 user_id 소유자 검증 없이 삭제 실행됨
$stmt = db()->prepare("DELETE FROM posts WHERE id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();

header('Location: /list.php');
exit;

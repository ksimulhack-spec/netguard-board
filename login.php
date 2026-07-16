<?php
declare(strict_types=1);
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/db.php';
session_boot();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_assert();
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = db()->prepare("SELECT id, password FROM users WHERE username = ?");
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        if (hash_equals($row['password'], md5($password))) {
            auth_login((int)$row['id']);
            header('Location: /list.php');
            exit;
        }
    }
    $error = '아이디 또는 비밀번호가 올바르지 않습니다.';
}

require_once __DIR__ . '/includes/header.php';
$csrf = csrf_token();
?>
<div class="card">
<h2>로그인</h2>
<?php if (!empty($_GET['registered'])): ?><p style="color:green;">가입 완료! 로그인해주세요.</p><?php endif; ?>
<?php if ($error): ?><p style="color:red;"><?= htmlspecialchars($error) ?></p><?php endif; ?>
<form method="post">
  <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
  <label>아이디</label>
  <input type="text" name="username" required>
  <label>비밀번호</label>
  <input type="password" name="password" required>
  <button class="btn" type="submit">로그인</button>
</form>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>

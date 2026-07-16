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
    $email    = trim($_POST['email'] ?? '');

    if ($username === '' || $password === '') {
        $error = '아이디와 비밀번호를 입력하세요.';
    } else {
        // 중복 체크 (prepared statement 사용 - 안전)
        $stmt = db()->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->bind_param('s', $username);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $error = '이미 존재하는 아이디입니다.';
        } else {
            // ── [취약점 #6] 약한 해싱: MD5, 솔트 없음 ──
            // 원래는 password_hash($password, PASSWORD_BCRYPT) 사용해야 함
            $hashed = md5($password);

            $stmt = db()->prepare("INSERT INTO users (username, password, email, role) VALUES (?, ?, ?, 'user')");
            $stmt->bind_param('sss', $username, $hashed, $email);
            $stmt->execute();

            header('Location: /login.php?registered=1');
            exit;
        }
    }
}

require_once __DIR__ . '/includes/header.php';
$csrf = csrf_token();
?>
<div class="card">
<h2>회원가입</h2>
<?php if ($error): ?><p style="color:red;"><?= htmlspecialchars($error) ?></p><?php endif; ?>
<form method="post">
  <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
  <label>아이디</label>
  <input type="text" name="username" required>
  <label>비밀번호</label>
  <input type="password" name="password" required>
  <label>이메일</label>
  <input type="email" name="email">
  <button class="btn" type="submit">가입하기</button>
</form>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>

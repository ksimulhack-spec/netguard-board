<?php
declare(strict_types=1);
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/db.php';

$uid = auth_require();

$stmt = db()->prepare("SELECT username, email, role, created_at FROM users WHERE id = ?");
$stmt->bind_param('i', $uid);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

$posts_stmt = db()->prepare("SELECT id, title, created_at FROM posts WHERE user_id = ? ORDER BY id DESC");
$posts_stmt->bind_param('i', $uid);
$posts_stmt->execute();
$posts = $posts_stmt->get_result();

require_once __DIR__ . '/includes/header.php';
?>
<div class="card">
<h2>마이페이지</h2>
<p><b>아이디:</b> <?= htmlspecialchars($user['username']) ?></p>
<p><b>이메일:</b> <?= htmlspecialchars($user['email'] ?? '') ?></p>
<p><b>권한:</b> <?= htmlspecialchars($user['role']) ?></p>
<p><b>가입일:</b> <?= htmlspecialchars($user['created_at']) ?></p>
<h3>내가 쓴 글</h3>
<table>
<tr><th>번호</th><th>제목</th><th>작성일</th></tr>
<?php while ($p = $posts->fetch_assoc()): ?>
<tr>
  <td><?= (int)$p['id'] ?></td>
  <td><a href="/view.php?id=<?= (int)$p['id'] ?>"><?= htmlspecialchars($p['title']) ?></a></td>
  <td><?= htmlspecialchars($p['created_at']) ?></td>
</tr>
<?php endwhile; ?>
</table>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>

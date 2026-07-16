<?php
declare(strict_types=1);
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../db.php';

// ── [취약점 #5] 인가 체계 우회 (Broken Access Control) ──────────────
// auth_require()로 "로그인 여부"만 확인하고, role이 'admin'인지는 검증하지 않음.
// 즉 일반 회원도 로그인만 했다면 /admin/index.php URL을 직접 입력해서 접근 가능.
// 원래는 아래처럼 role 체크가 추가로 필요함:
//   $stmt = db()->prepare("SELECT role FROM users WHERE id = ?");
//   $stmt->bind_param('i', $uid); $stmt->execute();
//   $role = $stmt->get_result()->fetch_assoc()['role'];
//   if ($role !== 'admin') { http_response_code(403); exit('forbidden'); }
$uid = auth_require();

$users = db()->query("SELECT id, username, email, role, created_at FROM users ORDER BY id DESC");
$posts = db()->query("SELECT p.id, p.title, u.username, p.created_at FROM posts p JOIN users u ON p.user_id=u.id ORDER BY p.id DESC LIMIT 50");

require_once __DIR__ . '/../includes/header.php';
?>
<div class="card">
<h2>관리자 페이지</h2>
<p style="color:#888;font-size:12px;">이 페이지는 로그인 여부만 확인하며, 관리자 권한(role) 검증 로직이 빠져 있습니다. (의도적 취약점)</p>

<h3>회원 목록</h3>
<table>
<tr><th>ID</th><th>아이디</th><th>이메일</th><th>권한</th><th>가입일</th></tr>
<?php while ($u = $users->fetch_assoc()): ?>
<tr>
  <td><?= (int)$u['id'] ?></td>
  <td><?= htmlspecialchars($u['username']) ?></td>
  <td><?= htmlspecialchars($u['email'] ?? '') ?></td>
  <td><?= htmlspecialchars($u['role']) ?></td>
  <td><?= htmlspecialchars($u['created_at']) ?></td>
</tr>
<?php endwhile; ?>
</table>

<h3>전체 게시글 (최근 50개)</h3>
<table>
<tr><th>ID</th><th>제목</th><th>작성자</th><th>작성일</th><th>관리</th></tr>
<?php while ($p = $posts->fetch_assoc()): ?>
<tr>
  <td><?= (int)$p['id'] ?></td>
  <td><?= htmlspecialchars($p['title']) ?></td>
  <td><?= htmlspecialchars($p['username']) ?></td>
  <td><?= htmlspecialchars($p['created_at']) ?></td>
  <td><a href="/delete.php?id=<?= (int)$p['id'] ?>" onclick="return confirm('삭제?')">삭제</a></td>
</tr>
<?php endwhile; ?>
</table>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>

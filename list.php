<?php
declare(strict_types=1);
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/includes/header.php';

$keyword = $_GET['q'] ?? '';

// ── [취약점 #2] SQL Injection ──────────────────────────
// 검색어를 문자열 결합으로 직접 쿼리에 삽입 (prepared statement 미사용)
// 예: q=' UNION SELECT id,username,password,role,1,NOW(),NOW() FROM users-- -
if ($keyword !== '') {
    $sql = "SELECT p.id, p.title, u.username, p.view_count, p.created_at
            FROM posts p JOIN users u ON p.user_id = u.id
            WHERE p.title LIKE '%$keyword%'
            ORDER BY p.id DESC";
} else {
    $sql = "SELECT p.id, p.title, u.username, p.view_count, p.created_at
            FROM posts p JOIN users u ON p.user_id = u.id
            ORDER BY p.id DESC";
}
$result = db()->query($sql);
?>
<div class="card">
<h2>게시판</h2>
<form method="get" style="margin-bottom:16px;">
  <input type="text" name="q" placeholder="제목 검색" value="<?= htmlspecialchars($keyword) ?>" style="width:70%;display:inline-block;">
  <button class="btn" type="submit">검색</button>
</form>
<table>
<tr><th>번호</th><th>제목</th><th>작성자</th><th>조회</th><th>작성일</th></tr>
<?php if ($result): while ($row = $result->fetch_assoc()): ?>
<tr>
  <td><?= (int)$row['id'] ?></td>
  <td><a href="/view.php?id=<?= (int)$row['id'] ?>"><?= htmlspecialchars($row['title']) ?></a></td>
  <td><?= htmlspecialchars($row['username']) ?></td>
  <td><?= (int)$row['view_count'] ?></td>
  <td><?= htmlspecialchars($row['created_at']) ?></td>
</tr>
<?php endwhile; else: ?>
<tr><td colspan="5">쿼리 오류 또는 결과 없음</td></tr>
<?php endif; ?>
</table>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>

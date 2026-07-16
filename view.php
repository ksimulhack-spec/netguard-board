<?php
declare(strict_types=1);
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/includes/header.php';

$id = (int)($_GET['id'] ?? 0);

db()->query("UPDATE posts SET view_count = view_count + 1 WHERE id = $id");

$stmt = db()->prepare("SELECT p.*, u.username FROM posts p JOIN users u ON p.user_id = u.id WHERE p.id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$post = $stmt->get_result()->fetch_assoc();

if (!$post) {
    echo "<div class='card'>존재하지 않는 게시글입니다.</div>";
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

$files_stmt = db()->prepare("SELECT * FROM post_files WHERE post_id = ?");
$files_stmt->bind_param('i', $id);
$files_stmt->execute();
$files = $files_stmt->get_result();
?>
<div class="card">
<h2><?= $post['title'] ?></h2>
<!-- ── [취약점 #3] Stored XSS ──────────────────────────
     title, content을 htmlspecialchars() 없이 그대로 출력
     write.php에서 <script>alert(1)</script> 같은 내용을 저장하면 여기서 실행됨 -->
<p style="color:#666;font-size:13px;">작성자: <?= htmlspecialchars($post['username']) ?> | 조회수: <?= (int)$post['view_count'] ?> | <?= htmlspecialchars($post['created_at']) ?></p>
<hr>
<div><?= nl2br($post['content']) ?></div>

<?php if ($files->num_rows > 0): ?>
<h4>첨부파일</h4>
<ul>
<?php while ($f = $files->fetch_assoc()): ?>
  <li><a href="/uploads/<?= htmlspecialchars($f['stored_name']) ?>" target="_blank"><?= htmlspecialchars($f['orig_name']) ?></a></li>
<?php endwhile; ?>
</ul>
<?php endif; ?>

<hr>
<a class="btn" href="/edit.php?id=<?= $id ?>">수정</a>
<a class="btn" href="/delete.php?id=<?= $id ?>" onclick="return confirm('삭제하시겠습니까?')">삭제</a>
<a class="btn" href="/list.php">목록</a>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>

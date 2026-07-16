<?php
declare(strict_types=1);
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/db.php';

$uid = auth_require();
$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);

// ── [취약점 #4] IDOR (Insecure Direct Object Reference) ──────────────
// 로그인 여부만 확인(auth_require)하고, 이 글이 실제로 로그인한 사용자(uid) 소유인지
// 검증하지 않음 -> 다른 회원의 글 id를 넣으면 누구나 수정 가능
// 원래는: WHERE id = ? AND user_id = ? 로 소유자 검증을 해야 함

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_assert();
    $title = $_POST['title'] ?? '';
    $content = $_POST['content'] ?? '';

    $stmt = db()->prepare("UPDATE posts SET title = ?, content = ? WHERE id = ?");
    $stmt->bind_param('ssi', $title, $content, $id);
    $stmt->execute();

    header('Location: /view.php?id=' . $id);
    exit;
}

$stmt = db()->prepare("SELECT * FROM posts WHERE id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$post = $stmt->get_result()->fetch_assoc();

if (!$post) { echo "존재하지 않는 게시글입니다."; exit; }

require_once __DIR__ . '/includes/header.php';
$csrf = csrf_token();
?>
<div class="card">
<h2>게시글 수정</h2>
<form method="post">
  <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
  <input type="hidden" name="id" value="<?= $id ?>">
  <label>제목</label>
  <input type="text" name="title" value="<?= htmlspecialchars($post['title']) ?>" required>
  <label>내용</label>
  <textarea name="content" rows="8" required><?= htmlspecialchars($post['content']) ?></textarea>
  <button class="btn" type="submit">수정하기</button>
</form>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>

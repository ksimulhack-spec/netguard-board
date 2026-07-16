<?php
declare(strict_types=1);
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/db.php';

$uid = auth_require();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_assert();
    $title   = $_POST['title'] ?? '';
    $content = $_POST['content'] ?? '';

    if ($title === '' || $content === '') {
        $error = '제목과 내용을 입력하세요.';
    } else {
        // 게시글 저장 (prepared statement 사용 - insert 자체는 안전)
        // 단, view.php에서 출력 시 escape 하지 않아 Stored XSS 발생 (취약점 #3)
        $stmt = db()->prepare("INSERT INTO posts (user_id, title, content) VALUES (?, ?, ?)");
        $stmt->bind_param('iss', $uid, $title, $content);
        $stmt->execute();
        $post_id = db()->insert_id;

        // ── [취약점 #1] 파일 업로드 취약점 (필수) ──────────────────
        if (!empty($_FILES['file']['name'])) {
            $orig_name = $_FILES['file']['name'];
            $tmp_path  = $_FILES['file']['tmp_name'];

            // 블랙리스트 방식 확장자 검증 - 우회 가능
            // .phtml, .pht, .php5, .phar 등이 블랙리스트에서 누락되어 있음
            // Apache 설정에 따라 이 확장자들도 PHP로 실행될 수 있음 (웹쉘 업로드 가능)
            $blacklist = ['php', 'php3', 'php4', 'exe', 'sh', 'bat'];
            $ext = strtolower(pathinfo($orig_name, PATHINFO_EXTENSION));

            // Content-Type도 검사하지만 클라이언트가 보내는 값이라 위조 가능 (참고용일 뿐)
            $mime = $_FILES['file']['type'] ?? '';

            if (in_array($ext, $blacklist, true)) {
                $error = '업로드할 수 없는 파일 형식입니다.';
            } else {
                // 원본 파일명을 거의 그대로 사용 (충돌 방지용 접두사만 추가)
                // 실행 가능한 디렉터리(uploads/)에 그대로 저장 -> 웹쉘 접근 가능
                $stored_name = uniqid('f_') . '_' . basename($orig_name);
                $dest = UPLOAD_DIR . $stored_name;

                if (move_uploaded_file($tmp_path, $dest)) {
                    $stmt = db()->prepare("INSERT INTO post_files (post_id, orig_name, stored_name) VALUES (?, ?, ?)");
                    $stmt->bind_param('iss', $post_id, $orig_name, $stored_name);
                    $stmt->execute();
                }
            }
        }

        header('Location: /view.php?id=' . $post_id);
        exit;
    }
}

require_once __DIR__ . '/includes/header.php';
$csrf = csrf_token();
?>
<div class="card">
<h2>글쓰기</h2>
<?php if ($error): ?><p style="color:red;"><?= htmlspecialchars($error) ?></p><?php endif; ?>
<form method="post" enctype="multipart/form-data">
  <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
  <label>제목</label>
  <input type="text" name="title" required>
  <label>내용</label>
  <textarea name="content" rows="8" required></textarea>
  <label>첨부파일</label>
  <input type="file" name="file">
  <button class="btn" type="submit">등록</button>
</form>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>

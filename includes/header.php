<?php
declare(strict_types=1);
require_once __DIR__ . '/../auth.php';
session_boot();
$logged_in = isset($_SESSION['uid']);
?>
<!DOCTYPE html>
<html lang="ko">
<head>
<meta charset="UTF-8">
<title>NetGuard 게시판</title>
<style>
  body{font-family:sans-serif;max-width:900px;margin:30px auto;background:#f7f7f7;color:#222;}
  nav{background:#2b2b2b;padding:12px 20px;border-radius:6px;margin-bottom:20px;}
  nav a{color:#fff;margin-right:16px;text-decoration:none;font-size:14px;}
  nav a:hover{text-decoration:underline;}
  table{width:100%;border-collapse:collapse;background:#fff;}
  th,td{border:1px solid #ddd;padding:8px 10px;text-align:left;font-size:14px;}
  th{background:#eee;}
  .btn{display:inline-block;padding:6px 14px;background:#333;color:#fff;text-decoration:none;border-radius:4px;font-size:13px;border:none;cursor:pointer;}
  input,textarea{width:100%;padding:8px;margin:6px 0;box-sizing:border-box;}
  .card{background:#fff;padding:20px;border-radius:6px;}
</style>
</head>
<body>
<nav>
  <a href="/list.php">게시판</a>
  <a href="/write.php">글쓰기</a>
  <?php if ($logged_in): ?>
    <a href="/mypage.php">마이페이지</a>
    <a href="/admin/index.php">관리자</a>
    <a href="/logout.php">로그아웃</a>
  <?php else: ?>
    <a href="/login.php">로그인</a>
    <a href="/register.php">회원가입</a>
  <?php endif; ?>
</nav>

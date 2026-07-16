<?php
// [취약점 #8] 하드코딩된 자격증명 (Hardcoded Credentials)
// DB 접속 정보(호스트, 계정, 비밀번호)가 소스코드에 평문으로 박혀 있음.
// 이 파일이 소스 유출(예: .git 노출, .bak 파일 노출)로 외부에 드러나면
// 공격자가 DB에 바로 접근 가능해짐.
// 원래는 config.php의 envv() 처럼 환경변수로 분리해야 함.
$host = "127.0.0.1";
$port = 3306;
$conn = @new mysqli($host, "web", "WebPass2025?", "web_db", $port);
if ($conn->connect_errno) {
    error_log("MySQL connect failed: ({$conn->connect_errno}) {$conn->connect_error}");
    die("ops");
}
echo "good";
?>

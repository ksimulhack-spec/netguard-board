<?php
// 디버깅용 DNS 조회 스크립트. 배포 서버에 남아있으면 내부망 구조 유추에 악용될 수 있음.
$host = "127.0.0.1";
var_dump(gethostbyname($host));
?>

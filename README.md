# NetGuard 게시판 (DMZ 웹서버용)

## 배포 환경
- Ubuntu 24.04 + Apache2 + PHP 7.4 (의도적 EOL 버전 사용)
- MySQL 8.0 (Trust 서버 또는 동일 VM)

## 설치 순서

1. **DB 생성**
   ```bash
   mysql -u root -p < schema.sql
   ```
   기본 관리자 계정: `admin` / `admin1234`

2. **DB 접속 정보 설정** (환경변수 또는 config.php 기본값 수정)
   ```bash
   export DB_HOST=127.0.0.1
   export DB_USER=web
   export DB_PASS=WebPass2025?
   export DB_NAME=web_db
   ```
   설정 안 하면 `config.php`의 기본값(`web`/`ChangeMe!`)이 사용되니, 실제 배포 전 반드시 맞는 값으로 변경.

3. **uploads 디렉토리 쓰기 권한**
   ```bash
   chmod 777 uploads/   # 데모/진단용. 운영에서는 www-data 소유로 750 권장
   ```

4. **Apache DocumentRoot를 이 폴더로 설정**
   ```apache
   <VirtualHost *:80>
       DocumentRoot /var/www/netguard_board
       <Directory /var/www/netguard_board>
           AllowOverride All
           Require all granted
       </Directory>
   </VirtualHost>
   ```

5. 브라우저에서 `http://<서버IP>/register.php`로 회원가입 후 이용

---

## 파일 구조
```
netguard_board/
├── config.php          # 환경변수 기반 설정
├── db.php              # mysqli 연결
├── auth.php            # 세션/CSRF (선배 제공 버전)
├── schema.sql           # DB 스키마
├── register.php / login.php / logout.php
├── list.php             # 목록 + 검색 (SQLi)
├── write.php            # 글쓰기 + 파일업로드 (업로드 취약점)
├── view.php              # 글보기 (XSS 출력지점)
├── edit.php / delete.php # 수정/삭제 (IDOR)
├── mypage.php
├── admin/index.php      # 관리자 페이지 (인가 우회)
├── includes/            # 공통 헤더/푸터
├── uploads/              # 업로드 파일 저장 위치
└── devTest/              # 진단용 디버그 파일 (정보노출/자격증명노출/백업파일노출)
    ├── info.php
    ├── dbtest.php
    ├── test.php
    └── auth.php.bak
```

---

## 심어진 취약점 목록 (총 9개, 파일업로드 포함)

| # | 취약점 유형 | 위치 | 설명 |
|---|---|---|---|
| 1 | **파일 업로드 (필수)** | `write.php` | 확장자 블랙리스트에 `phtml`, `pht`, `php5`, `phar` 등이 누락되어 있어 웹쉘 업로드 가능. Content-Type 검증도 클라이언트가 보내는 값(위조 가능)만 참고. |
| 2 | SQL Injection | `list.php` (검색) | 검색어(`q`)를 prepared statement 없이 문자열 결합으로 쿼리에 삽입. `' UNION SELECT ...--` 형태로 다른 테이블 데이터 추출 가능. |
| 3 | Stored XSS | `write.php`(저장) → `view.php`(출력) | 게시글 제목/본문을 `htmlspecialchars()` 없이 그대로 출력. `<script>` 삽입 시 조회한 모든 사용자에게 실행됨. |
| 4 | IDOR (권한 없는 자원 접근) | `edit.php`, `delete.php` | 로그인 여부만 확인하고 게시글 소유자(`user_id`)와 현재 로그인 사용자 일치 여부를 검증하지 않아, 글 번호만 알면 타인의 글을 수정/삭제 가능. |
| 5 | 인가 체계 우회 (Broken Access Control) | `admin/index.php` | 관리자 페이지가 로그인 여부만 확인하고 `role='admin'` 검증이 빠져있어, 일반 회원도 URL 직접 접근으로 관리자 페이지 진입 가능. |
| 6 | 약한 비밀번호 저장 | `register.php`, `login.php` | 비밀번호를 `MD5` 무솔트로 해싱. Rainbow table로 쉽게 역산 가능. (`password_hash()`/`password_verify()` 미사용) |
| 7 | 정보 노출 | `devTest/info.php` | `phpinfo()`가 그대로 노출되어 PHP 버전, 서버 경로, 모듈 등 정찰 정보 제공. |
| 8 | 하드코딩된 자격증명 | `devTest/dbtest.php` | DB 계정/비밀번호가 소스코드에 평문으로 하드코딩됨. |
| 9 | 백업 파일 노출 | `devTest/auth.php.bak` | `.bak` 확장자는 PHP로 실행되지 않고 원본 소스가 그대로 텍스트로 응답되어, 인증 로직 전체가 노출됨. |

각 취약점 위치에는 코드 내 주석으로 `[취약점 #N]` 표시를 해두었으니, 리포트 작성 시 스크린샷과 함께 그대로 인용 가능합니다.

## 참고
- `devTest/` 폴더는 실제 운영 배포 시 반드시 제거되어야 하는 디버그 산출물의 예시이며,
  선배(멘토)가 준 참고 코드의 `devTest/auth.php.bak`, `dbtest.php`, `info.php`, `test.php` 구성을 그대로 재현한 것입니다.

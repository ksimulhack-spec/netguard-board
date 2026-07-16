CREATE DATABASE IF NOT EXISTS web_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE web_db;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,       -- 취약점: MD5 무솔트 저장 (register.php 참고)
    email VARCHAR(100),
    role VARCHAR(20) NOT NULL DEFAULT 'user',  -- 'user' | 'admin'
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    content TEXT NOT NULL,
    view_count INT NOT NULL DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS post_files (
    id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT NOT NULL,
    orig_name VARCHAR(255) NOT NULL,
    stored_name VARCHAR(255) NOT NULL,
    uploaded_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE
);

-- 초기 관리자 계정 (비밀번호: admin1234, MD5 해시)
INSERT INTO users (username, password, email, role)
VALUES ('admin', MD5('admin1234'), 'admin@example.com', 'admin')
ON DUPLICATE KEY UPDATE username=username;

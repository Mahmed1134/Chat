<?php
// الجداول وتحديثاتها (بتتنفذ عند كل تشغيل زي الأصل)

mysqli_query($conn, "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL UNIQUE,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    active_chat_id INT DEFAULT NULL
)");

$chkActiveCol = mysqli_query($conn, "SHOW COLUMNS FROM users LIKE 'active_chat_id'");
if (mysqli_num_rows($chkActiveCol) == 0) {
    mysqli_query($conn, "ALTER TABLE users ADD COLUMN active_chat_id INT DEFAULT NULL");
}

mysqli_query($conn, "CREATE TABLE IF NOT EXISTS chats (
    id INT AUTO_INCREMENT PRIMARY KEY,
    participants TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

mysqli_query($conn, "CREATE TABLE IF NOT EXISTS chat_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    chat_id INT NOT NULL,
    sender_name VARCHAR(255) NOT NULL,
    body TEXT NOT NULL,
    file_path VARCHAR(500) DEFAULT NULL,
    file_type VARCHAR(100) DEFAULT NULL,
    file_name VARCHAR(255) DEFAULT NULL,
    is_edited TINYINT(1) DEFAULT 0,
    reply_to_id INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

$chkEditCol = mysqli_query($conn, "SHOW COLUMNS FROM chat_messages LIKE 'is_edited'");
if (mysqli_num_rows($chkEditCol) == 0) {
    mysqli_query($conn, "ALTER TABLE chat_messages ADD COLUMN is_edited TINYINT(1) DEFAULT 0");
}

$chkFileCol = mysqli_query($conn, "SHOW COLUMNS FROM chat_messages LIKE 'file_path'");
if (mysqli_num_rows($chkFileCol) == 0) {
    mysqli_query($conn, "ALTER TABLE chat_messages ADD COLUMN file_path VARCHAR(500) DEFAULT NULL");
    mysqli_query($conn, "ALTER TABLE chat_messages ADD COLUMN file_name VARCHAR(255) DEFAULT NULL");
    mysqli_query($conn, "ALTER TABLE chat_messages ADD COLUMN file_type VARCHAR(100) DEFAULT NULL");
}

$chkReplyCol = mysqli_query($conn, "SHOW COLUMNS FROM chat_messages LIKE 'reply_to_id'");
if (mysqli_num_rows($chkReplyCol) == 0) {
    mysqli_query($conn, "ALTER TABLE chat_messages ADD COLUMN reply_to_id INT DEFAULT NULL");
}

mysqli_query($conn, "CREATE TABLE IF NOT EXISTS chat_reads (
    chat_id INT NOT NULL,
    user_name VARCHAR(255) NOT NULL,
    last_read_at TIMESTAMP DEFAULT '1970-01-02 00:00:00',
    PRIMARY KEY (chat_id, user_name)
)");

mysqli_query($conn, "CREATE TABLE IF NOT EXISTS push_subscriptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_name VARCHAR(255) NOT NULL,
    endpoint TEXT NOT NULL,
    p256dh VARCHAR(255) NOT NULL,
    auth_token VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_endpoint (endpoint(255))
)");

mysqli_query($conn, "CREATE TABLE IF NOT EXISTS app_settings (
    setting_key VARCHAR(64) PRIMARY KEY,
    setting_value TEXT
)");

mysqli_query($conn, "CREATE TABLE IF NOT EXISTS push_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_name VARCHAR(255),
    status INT,
    response TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

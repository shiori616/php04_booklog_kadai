-- データベースを作成
CREATE DATABASE IF NOT EXISTS gs_booklog CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- データベースを使用
USE gs_booklog;

-- book_clicksテーブルを作成
CREATE TABLE IF NOT EXISTS book_clicks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title TEXT,
    authors TEXT,
    image_url TEXT,
    description TEXT,
    industry_identifiers JSON,
    isbn13 VARCHAR(20),
    isbn10 VARCHAR(15),
    button_type VARCHAR(20),
    click_datetime DATETIME,
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- テーブル構造を確認
DESCRIBE book_clicks;

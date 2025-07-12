-- ユーザーテーブルを作成
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- book_clicksテーブルにuser_idカラムを追加
ALTER TABLE book_clicks 
ADD COLUMN user_id INT,
ADD FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;

-- インデックスを追加（パフォーマンス向上）
CREATE INDEX idx_book_clicks_user_id ON book_clicks(user_id);
CREATE INDEX idx_book_clicks_button_type ON book_clicks(button_type);
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_username ON users(username);

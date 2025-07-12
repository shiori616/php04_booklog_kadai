<?php
//エラー表示
ini_set("display_errors", 1);

// 共通設定ファイルを読み込み
require_once 'config/database.php';
require_once 'config/session.php';

// JSONレスポンス用のヘッダー設定
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// POSTリクエストのみ受け付け
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

// JSONデータを受け取る
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid JSON data']);
    exit;
}

// ログインチェック - 必須にする
$userId = SessionManager::getUserId();
if (!$userId) {
    echo json_encode([
        'success' => false, 
        'error' => 'ログインが必要です。書籍を保存するにはログインしてください。',
        'redirect' => 'auth/login.php'
    ]);
    exit;
}

// データベース接続
$database = new Database();
$pdo = $database->getConnection();

// 必要なデータを取得
$industryIdentifiers = isset($input['industryIdentifiers']) ? $input['industryIdentifiers'] : [];
$clickDateTime = isset($input['clickDateTime']) ? $input['clickDateTime'] : date('Y-m-d H:i:s');
$buttonType = isset($input['buttonType']) ? $input['buttonType'] : '';
$comment = isset($input['comment']) ? $input['comment'] : '';

// その他のデータ
$title = isset($input['title']) ? $input['title'] : '';
$authors = isset($input['authors']) ? $input['authors'] : '';
$imageUrl = isset($input['imageUrl']) ? $input['imageUrl'] : '';
$description = isset($input['description']) ? $input['description'] : '';

// デバッグ用ログ
error_log("Saving book for user ID: " . $userId);
error_log("Book title: " . $title);
error_log("Button type: " . $buttonType);

// ISBNを抽出
$isbn13 = '';
$isbn10 = '';
foreach ($industryIdentifiers as $identifier) {
    if (isset($identifier['type']) && isset($identifier['identifier'])) {
        if ($identifier['type'] === 'ISBN_13') {
            $isbn13 = $identifier['identifier'];
        } elseif ($identifier['type'] === 'ISBN_10') {
            $isbn10 = $identifier['identifier'];
        }
    }
}

try {
    // テーブルが存在しない場合は作成
    $createTable = "
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
            user_id INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ";
    $pdo->exec($createTable);

    // 同じユーザーが同じ本を重複して登録しないようにチェック
    $checkStmt = $pdo->prepare("
        SELECT id FROM book_clicks 
        WHERE user_id = ? AND title = ? AND button_type = ?
    ");
    $checkStmt->execute([$userId, $title, $buttonType]);
    
    if ($checkStmt->fetch()) {
        echo json_encode([
            'success' => false, 
            'error' => 'この本は既に' . ($buttonType === 'read' ? '読了済み' : '積読') . 'リストに登録されています。'
        ]);
        exit;
    }

    // データを挿入（user_idは必須）
    $stmt = $pdo->prepare("
        INSERT INTO book_clicks 
        (title, authors, image_url, description, industry_identifiers, isbn13, isbn10, button_type, click_datetime, comment, user_id) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $result = $stmt->execute([
        $title,
        $authors,
        $imageUrl,
        $description,
        json_encode($industryIdentifiers, JSON_UNESCAPED_UNICODE),
        $isbn13,
        $isbn10,
        $buttonType,
        date('Y-m-d H:i:s', strtotime($clickDateTime)),
        $comment,
        $userId
    ]);

    if ($result) {
        $insertId = $pdo->lastInsertId();
        
        echo json_encode([
            'success' => true,
            'message' => 'Book data saved successfully',
            'insert_id' => $insertId,
            'user_id' => $userId,
            'data' => [
                'title' => $title,
                'authors' => $authors,
                'industryIdentifiers' => $industryIdentifiers,
                'clickDateTime' => $clickDateTime,
                'buttonType' => $buttonType,
                'isbn13' => $isbn13,
                'isbn10' => $isbn10,
                'comment' => $comment
            ]
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to save data']);
    }

} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    error_log("General error: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Error: ' . $e->getMessage()]);
}
?>

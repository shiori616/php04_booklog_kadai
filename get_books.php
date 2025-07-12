<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// 共通設定ファイルを読み込み
require_once 'config/database.php';
require_once 'config/session.php';

// DB接続
try {
    $database = new Database();
    $pdo = $database->getConnection();
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'DBConnection Error:'.$e->getMessage()]);
    exit;
}

// ステータスでフィルタリング（read または tsundoku）
$status_filter = isset($_GET['status']) ? $_GET['status'] : null;

// ログインユーザーのIDを取得
$userId = SessionManager::getUserId();

// ログインしていない場合はエラーを返す
if (!$userId) {
    echo json_encode([
        'success' => false, 
        'error' => 'ログインが必要です',
        'redirect' => 'auth/login.php'
    ]);
    exit;
}

try {
    if ($status_filter && in_array($status_filter, ['read', 'tsundoku'])) {
        // ログインユーザーの特定ステータスの書籍のみを取得
        $stmt = $pdo->prepare("SELECT * FROM book_clicks WHERE button_type = ? AND user_id = ? ORDER BY created_at DESC");
        $stmt->execute([$status_filter, $userId]);
        error_log("Filtering by status: " . $status_filter . " for user: " . $userId);
    } else {
        // ログインユーザーのすべての書籍を取得
        $stmt = $pdo->prepare("SELECT * FROM book_clicks WHERE user_id = ? ORDER BY created_at DESC");
        $stmt->execute([$userId]);
        error_log("Getting all books for user: " . $userId);
    }
    
    $books = $stmt->fetchAll(PDO::FETCH_ASSOC);
    error_log("Found " . count($books) . " books for user " . $userId);
    
    // industry_identifiersをJSONデコード
    foreach ($books as &$book) {
        if (isset($book['industry_identifiers'])) {
            $book['industry_identifiers'] = json_decode($book['industry_identifiers'], true);
        }
    }
    
    echo json_encode([
        'success' => true,
        'books' => $books,
        'count' => count($books),
        'filter' => $status_filter,
        'user_id' => $userId,
        'is_logged_in' => true,
        'debug' => [
            'sql_executed' => $status_filter ? 
                "SELECT * FROM book_clicks WHERE button_type = '$status_filter' AND user_id = $userId ORDER BY created_at DESC" : 
                "SELECT * FROM book_clicks WHERE user_id = $userId ORDER BY created_at DESC"
        ]
    ], JSON_UNESCAPED_UNICODE);
    
} catch (PDOException $e) {
    error_log("Database error in get_books.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
?>

<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// 共通設定ファイルを読み込み
require_once 'config/database.php';
require_once 'config/session.php';

// POSTリクエストのみ受け付け
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

// JSONデータを受け取る
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid data: ID is required']);
    exit;
}

// ログインチェック
$userId = SessionManager::getUserId();
if (!$userId) {
    echo json_encode([
        'success' => false, 
        'error' => 'ログインが必要です',
        'redirect' => 'auth/login.php'
    ]);
    exit;
}

$bookId = $input['id'];

try {
    // DB接続
    $database = new Database();
    $pdo = $database->getConnection();
    
    // 削除前にレコードが存在し、かつ現在のユーザーのものかを確認
    $checkStmt = $pdo->prepare("SELECT * FROM book_clicks WHERE id = ? AND user_id = ?");
    $checkStmt->execute([$bookId, $userId]);
    $existingRecord = $checkStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$existingRecord) {
        echo json_encode(['success' => false, 'error' => 'Record not found or access denied']);
        exit;
    }
    
    // レコードを削除（自分のレコードのみ）
    $deleteStmt = $pdo->prepare("DELETE FROM book_clicks WHERE id = ? AND user_id = ?");
    $result = $deleteStmt->execute([$bookId, $userId]);
    
    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Book deleted successfully',
            'deleted_record' => $existingRecord
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to delete record']);
    }
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Error: ' . $e->getMessage()]);
}
?>

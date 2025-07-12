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

if (!$input || !isset($input['id']) || !isset($input['comment'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid data: ID and comment are required']);
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
$comment = $input['comment'];

try {
    // DB接続
    $database = new Database();
    $pdo = $database->getConnection();
    
    // レコードが存在し、かつ現在のユーザーのものかを確認
    $checkStmt = $pdo->prepare("SELECT * FROM book_clicks WHERE id = ? AND user_id = ?");
    $checkStmt->execute([$bookId, $userId]);
    $existingRecord = $checkStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$existingRecord) {
        echo json_encode(['success' => false, 'error' => 'Record not found or access denied']);
        exit;
    }
    
    // コメントを更新（自分のレコードのみ）
    $updateStmt = $pdo->prepare("UPDATE book_clicks SET comment = ? WHERE id = ? AND user_id = ?");
    $result = $updateStmt->execute([$comment, $bookId, $userId]);
    
    if ($result) {
        // 更新後のデータを取得
        $checkStmt->execute([$bookId, $userId]);
        $updatedRecord = $checkStmt->fetch(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'message' => 'Comment updated successfully',
            'updated_record' => $updatedRecord
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to update comment']);
    }
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Error: ' . $e->getMessage()]);
}
?>

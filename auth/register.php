<?php
require_once '../config/database.php';
require_once '../config/session.php';

// 既にログインしている場合はリダイレクト
if (SessionManager::isLoggedIn()) {
    header('Location: ../index.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // バリデーション
    if (empty($username) || empty($email) || empty($password)) {
        $error = 'すべての項目を入力してください。';
    } elseif (strlen($username) < 3 || strlen($username) > 50) {
        $error = 'ユーザー名は3文字以上50文字以下で入力してください。';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = '有効なメールアドレスを入力してください。';
    } elseif (strlen($password) < 6) {
        $error = 'パスワードは6文字以上で入力してください。';
    } elseif ($password !== $confirm_password) {
        $error = 'パスワードが一致しません。';
    } else {
        try {
            $database = new Database();
            $pdo = $database->getConnection();
            
            // usersテーブルが存在しない場合は作成
            $createUsersTable = "
                CREATE TABLE IF NOT EXISTS users (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    username VARCHAR(50) UNIQUE NOT NULL,
                    email VARCHAR(100) UNIQUE NOT NULL,
                    password_hash VARCHAR(255) NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ";
            $pdo->exec($createUsersTable);
            
            // ユーザー名とメールアドレスの重複チェック
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);
            
            if ($stmt->fetch()) {
                $error = 'このユーザー名またはメールアドレスは既に使用されています。';
            } else {
                // パスワードをハッシュ化
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                
                // ユーザーを登録
                $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)");
                $result = $stmt->execute([$username, $email, $password_hash]);
                
                if ($result) {
                    $success = '会員登録が完了しました。ログインしてください。';
                } else {
                    $error = '登録に失敗しました。もう一度お試しください。';
                }
            }
        } catch (PDOException $e) {
            error_log("Registration error: " . $e->getMessage());
            $error = 'システムエラーが発生しました。詳細: ' . $e->getMessage();
        } catch (Exception $e) {
            error_log("General registration error: " . $e->getMessage());
            $error = 'システムエラーが発生しました。詳細: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>会員登録 - ブクログ</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center">
    <div class="max-w-md w-full space-y-8 p-8 bg-white rounded-lg shadow-md">
        <div class="text-center">
            <h2 class="text-3xl font-bold text-gray-900">会員登録</h2>
            <p class="mt-2 text-gray-600">アカウントを作成してブクログを始めましょう</p>
        </div>
        
        <?php if ($error): ?>
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded">
                <?php echo htmlspecialchars($success); ?>
                <div class="mt-2">
                    <a href="login.php" class="text-green-800 underline">ログインページへ</a>
                </div>
            </div>
        <?php else: ?>
            <form method="POST" class="space-y-6">
                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700">ユーザー名</label>
                    <input type="text" id="username" name="username" required 
                           value="<?php echo htmlspecialchars($username ?? ''); ?>"
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                </div>
                
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">メールアドレス</label>
                    <input type="email" id="email" name="email" required 
                           value="<?php echo htmlspecialchars($email ?? ''); ?>"
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                </div>
                
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">パスワード</label>
                    <input type="password" id="password" name="password" required 
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    <p class="mt-1 text-sm text-gray-500">6文字以上で入力してください</p>
                </div>
                
                <div>
                    <label for="confirm_password" class="block text-sm font-medium text-gray-700">パスワード確認</label>
                    <input type="password" id="confirm_password" name="confirm_password" required 
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                </div>
                
                <div>
                    <button type="submit" 
                            class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        会員登録
                    </button>
                </div>
            </form>
        <?php endif; ?>
        
        <div class="text-center">
            <p class="text-sm text-gray-600">
                既にアカウントをお持ちですか？ 
                <a href="login.php" class="text-blue-600 hover:text-blue-500">ログイン</a>
            </p>
            <p class="text-sm text-gray-600 mt-2">
                <a href="../index.php" class="text-blue-600 hover:text-blue-500">メインページに戻る</a>
            </p>
        </div>
    </div>
</body>
</html>

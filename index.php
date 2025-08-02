<?php
// セッション管理を読み込み
require_once 'config/session.php';

// ログイン状態を確認
$isLoggedIn = SessionManager::isLoggedIn();
$userInfo = SessionManager::getUserInfo();
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ブクログ初級</title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>📚</text></svg>">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <!-- ヘッダー部分 -->
    <header class="bg-blue-600 text-white p-4 sticky top-0 z-50">
        <div class="container mx-auto flex justify-between items-center">
            <h1 class="text-2xl font-bold"><a href="index.php" class="hover:text-blue-200 transition-colors font-medium">ブクログ</a></h1>
            
            <div class="flex items-center space-x-4">
                <!-- メニューバー -->
                <nav class="hidden md:flex space-x-6">
                    <a href="index.php" class="hover:text-blue-200 transition-colors font-medium">検索</a>
                    <a href="read.php" class="hover:text-blue-200 transition-colors font-medium">読了済み</a>
                    <a href="tsundoku.php" class="hover:text-blue-200 transition-colors font-medium">積読</a>
                </nav>
                
                <!-- ユーザー情報・ログインボタン -->
                <div class="hidden md:flex items-center space-x-3">
                    <?php if ($isLoggedIn): ?>
                        <span class="text-blue-200">こんにちは、<?php echo htmlspecialchars($userInfo['username']); ?>さん</span>
                        <a href="auth/logout.php" class="bg-blue-500 hover:bg-blue-700 px-3 py-1 rounded text-sm transition-colors">ログアウト</a>
                    <?php else: ?>
                        <a href="auth/login.php" class="bg-blue-500 hover:bg-blue-700 px-3 py-1 rounded text-sm transition-colors">ログイン</a>
                        <a href="auth/register.php" class="bg-green-500 hover:bg-green-700 px-3 py-1 rounded text-sm transition-colors">会員登録</a>
                    <?php endif; ?>
                </div>
                
                <!-- モバイルメニューボタン -->
                <button id="mobile-menu-btn" class="md:hidden">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
            </div>
        </div>
        
        <!-- モバイルメニュー -->
        <nav id="mobile-menu" class="md:hidden mt-4 hidden">
            <div class="flex flex-col space-y-2">
                <a href="index.php" class="hover:text-blue-200 transition-colors font-medium py-2">検索</a>
                <a href="read.php" class="hover:text-blue-200 transition-colors font-medium py-2">読了済み</a>
                <a href="tsundoku.php" class="hover:text-blue-200 transition-colors font-medium py-2">積読</a>
                <div class="border-t border-blue-500 pt-2 mt-2">
                    <?php if ($isLoggedIn): ?>
                        <span class="text-blue-200 block py-1">こんにちは、<?php echo htmlspecialchars($userInfo['username']); ?>さん</span>
                        <a href="auth/logout.php" class="bg-blue-500 hover:bg-blue-700 px-3 py-1 rounded text-sm transition-colors inline-block">ログアウト</a>
                    <?php else: ?>
                        <a href="auth/login.php" class="bg-blue-500 hover:bg-blue-700 px-3 py-1 rounded text-sm transition-colors inline-block mr-2">ログイン</a>
                        <a href="auth/register.php" class="bg-green-500 hover:bg-green-700 px-3 py-1 rounded text-sm transition-colors inline-block">会員登録</a>
                    <?php endif; ?>
                </div>
            </div>
        </nav>
    </header>

    <!-- 検索フォーム -->
    <div class="container mx-auto p-4">
        <form id="search-form" class="mb-4 flex flex-col md:flex-row items-center justify-center">
            <input 
                type="text" 
                name="keyword" 
                placeholder="検索キーワードを入力" 
                id="keyword" 
                class="shadow appearance-none border rounded w-4/5 md:w-auto py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline mb-2 md:mb-0 md:mr-2"
            >
            <button 
                type="button" 
                id="search-button" 
                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline w-4/5 md:w-auto"
            >
                検索
            </button>
        </form>
    </div>

    <!-- 検索結果 -->
    <div class="result">

    </div>

    <!-- jQueryライブラリ -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <!-- JavaScriptファイル -->
    <script src="js/script.js"></script>
    <script>
        // jQueryが読み込まれてから実行
        $(document).ready(function() {
            console.log('jQuery loaded in index.php');
            
            // モバイルメニューの切り替え
            $('#mobile-menu-btn').click(function() {
                $('#mobile-menu').toggleClass('hidden');
            });
        });
    </script>

    <!-- コメント入力モーダル -->
    <div id="comment-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 mb-4" id="modal-title">コメントを追加</h3>
                <div class="mb-4">
                    <label for="book-comment" class="block text-sm font-medium text-gray-700 mb-2">
                        コメント（任意）
                    </label>
                    <textarea 
                        id="book-comment" 
                        rows="4" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="この本についてのコメントを入力してください..."
                    ></textarea>
                </div>
                <div class="flex justify-end space-x-3">
                    <button 
                        id="cancel-comment" 
                        class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 transition-colors"
                    >
                        キャンセル
                    </button>
                    <button 
                        id="save-comment" 
                        class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 transition-colors"
                    >
                        保存
                    </button>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

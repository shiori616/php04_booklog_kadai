<?php
require_once '../config/session.php';

SessionManager::logout();
header('Location: login.php');
exit;
?>

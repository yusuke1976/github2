<?php
session_start();
include "funcs.php";

$pdo = db_conn();
sschk();

$username = $_SESSION['username'];

// 通知を取得
$stmt = $pdo->prepare("SELECT * FROM notifications WHERE recipient_username = :username ORDER BY created_at DESC LIMIT 5");
$stmt->execute([':username' => $username]);
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 未読の通知数を取得
$stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE recipient_username = :username AND is_read = FALSE");
$stmt->execute([':username' => $username]);
$unread_count = $stmt->fetchColumn();

echo json_encode([
    'notifications' => $notifications,
    'unread' => $unread_count
]);
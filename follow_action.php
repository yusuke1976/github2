<?php
session_start();
include "funcs.php";

// DB接続
$pdo = db_conn();

// セッションチェック
sschk();

$action = $_POST['action'];
$followed_username = $_POST['username'];
$follower_username = $_SESSION['username'];

if ($action === 'follow') {
    $stmt = $pdo->prepare("INSERT INTO user_follows (follower_username, followed_username) VALUES (:follower, :followed)");
    $stmt->bindValue(':follower', $follower_username, PDO::PARAM_STR);
    $stmt->bindValue(':followed', $followed_username, PDO::PARAM_STR);

    try {
        $stmt->execute();

        // フォロー通知を作成
        $notification_message = $follower_username . "さんがあなたをフォローしました。";
        $stmt = $pdo->prepare("INSERT INTO notifications (recipient_username, message, is_read) VALUES (:recipient, :message, FALSE)");
        $stmt->execute([':recipient' => $followed_username, ':message' => $notification_message]);

        echo json_encode(['success' => true, 'action' => $action]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    $stmt = $pdo->prepare("DELETE FROM user_follows WHERE follower_username = :follower AND followed_username = :followed");
    $stmt->bindValue(':follower', $follower_username, PDO::PARAM_STR);
    $stmt->bindValue(':followed', $followed_username, PDO::PARAM_STR);

    try {
        $stmt->execute();
        echo json_encode(['success' => true, 'action' => $action]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}
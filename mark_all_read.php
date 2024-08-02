<?php
session_start();
include "funcs.php";

$pdo = db_conn();

$username = $_SESSION['username'];

try {
    $stmt = $pdo->prepare("UPDATE notifications SET is_read = TRUE WHERE recipient_username = :username");
    $stmt->bindValue(':username', $username, PDO::PARAM_STR);
    $stmt->execute();

    // 通知をすべて削除
    $stmt = $pdo->prepare("DELETE FROM notifications WHERE recipient_username = :username");
    $stmt->bindValue(':username', $username, PDO::PARAM_STR);
    $stmt->execute();

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
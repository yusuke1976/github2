<?php
session_start();
include "funcs.php";

$pdo = db_conn();
sschk();

$username = $_SESSION['username'];

// すべての通知を既読にする
$stmt = $pdo->prepare("UPDATE notifications SET is_read = TRUE WHERE recipient_username = :username");
$stmt->bindValue(':username', $username, PDO::PARAM_STR);
$stmt->execute();

// select.phpにリダイレクト
header("Location: select.php");
exit();
?>
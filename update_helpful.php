<?php
session_start();
include "funcs.php";

header('Content-Type: application/json');

if(isset($_POST['helpful']) && isset($_POST['username'])) {
    $pdo = db_conn();
    $book_id = $_POST['book_id'];
    $username = $_POST['username'];
    $helpful = $_POST['helpful'] === 'true';

    // トランザクション開始
    $pdo->beginTransaction();

    try {
        // 現在の投票状況を確認
        $stmt = $pdo->prepare("SELECT voted_users, helpful_count FROM gs_bm_table WHERE id = :id");
        $stmt->bindValue(':id', $book_id, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        $voted_users = $result['voted_users'] ? explode(',', $result['voted_users']) : [];
        $helpful_count = $result['helpful_count'];
        $isFirstVote = false;

        if ($helpful && !in_array($username, $voted_users)) {
            // 新しい投票を追加
            $voted_users[] = $username;
            $helpful_count++;
            $isFirstVote = count($voted_users) == 1;
            $message = $isFirstVote ? '初めての投票嬉しいです！' : '投票ありがとうございます！';
        } elseif (!$helpful && in_array($username, $voted_users)) {
            // 投票をキャンセル
            $voted_users = array_diff($voted_users, [$username]);
            $helpful_count--;
            $message = '投票をキャンセルしました。';
        } else {
            // 不正な操作
            echo json_encode(['success' => false, 'message' => '不正な操作です。']);
            exit;
        }

        $new_voted_users = implode(',', $voted_users);

        $stmt = $pdo->prepare("UPDATE gs_bm_table SET helpful_count = :helpful_count, voted_users = :voted_users WHERE id = :id");
        $stmt->bindValue(':helpful_count', $helpful_count, PDO::PARAM_INT);
        $stmt->bindValue(':voted_users', $new_voted_users, PDO::PARAM_STR);
        $stmt->bindValue(':id', $book_id, PDO::PARAM_INT);
        $stmt->execute();

        $pdo->commit();

        echo json_encode(['success' => true, 'newCount' => $helpful_count, 'message' => $message, 'isFirstVote' => $isFirstVote]);
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => '処理中にエラーが発生しました。']);
    }
} else {
    echo json_encode(['success' => false, 'message' => '無効なリクエストです。']);
}
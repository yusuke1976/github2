<?php
session_start();
include "funcs.php";

$pdo = db_conn();
sschk();

$username = $_SESSION['username'];

// メッセージ削除処理
if (isset($_POST['delete_message'])) {
    $message_id = $_POST['message_id'];
    $stmt = $pdo->prepare("DELETE FROM gs_messages_table WHERE id = :id AND sender_username = :username");
    $stmt->bindValue(':id', $message_id, PDO::PARAM_INT);
    $stmt->bindValue(':username', $username, PDO::PARAM_STR);
    $stmt->execute();
}

// 返信処理
if (isset($_POST['reply_message'])) {
    $reply_to = $_POST['reply_to'];
    $reply_message = $_POST['reply_message'];
    $stmt = $pdo->prepare("INSERT INTO gs_messages_table (sender_username, receiver_username, message, created_at) VALUES (:sender, :receiver, :message, NOW())");
    $stmt->bindValue(':sender', $username, PDO::PARAM_STR);
    $stmt->bindValue(':receiver', $reply_to, PDO::PARAM_STR);
    $stmt->bindValue(':message', $reply_message, PDO::PARAM_STR);
    $stmt->execute();

    // 通知を作成
    $notification_message = $username . "さんからメッセージが届きました。";
    $stmt = $pdo->prepare("INSERT INTO notifications (recipient_username, message, created_at, is_read) VALUES (:recipient, :message, NOW(), FALSE)");
    $stmt->bindValue(':recipient', $reply_to, PDO::PARAM_STR);
    $stmt->bindValue(':message', $notification_message, PDO::PARAM_STR);
    $stmt->execute();
}

// ブロック処理
if (isset($_POST['block_user'])) {
    $blocked_username = $_POST['blocked_username'];
    $stmt = $pdo->prepare("INSERT IGNORE INTO user_blocks (blocker_username, blocked_username) VALUES (:blocker, :blocked)");
    $stmt->bindValue(':blocker', $username, PDO::PARAM_STR);
    $stmt->bindValue(':blocked', $blocked_username, PDO::PARAM_STR);
    $stmt->execute();
}

// ブロック解除処理
if (isset($_POST['unblock_user'])) {
    $blocked_username = $_POST['blocked_username'];
    $stmt = $pdo->prepare("DELETE FROM user_blocks WHERE blocker_username = :blocker AND blocked_username = :blocked");
    $stmt->bindValue(':blocker', $username, PDO::PARAM_STR);
    $stmt->bindValue(':blocked', $blocked_username, PDO::PARAM_STR);
    $stmt->execute();
}

// ユーザーのプロフィール画像を取得
$stmt = $pdo->prepare("SELECT profile_image FROM gs_user_table5 WHERE username = :username");
$stmt->bindValue(':username', $_SESSION['username'], PDO::PARAM_STR);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$profile_image = $user['profile_image'] ? 'uploads/' . $user['profile_image'] : 'path/to/default/image.jpg';

// 最多投稿者を取得
$stmt = $pdo->prepare("SELECT username FROM gs_bm_table GROUP BY username ORDER BY COUNT(*) DESC LIMIT 1");
$stmt->execute();
$top_poster = $stmt->fetch(PDO::FETCH_ASSOC);
$top_poster_username = $top_poster['username'];

// ブロックされているユーザーのリストを取得
$stmt = $pdo->prepare("SELECT blocked_username FROM user_blocks WHERE blocker_username = :username");
$stmt->bindValue(':username', $username, PDO::PARAM_STR);
$stmt->execute();
$blocked_users = $stmt->fetchAll(PDO::FETCH_COLUMN);

// 受信したメッセージを取得（ブロックされていないユーザーからのみ）
$stmt = $pdo->prepare("SELECT m.*, u.profile_image FROM gs_messages_table m 
                       JOIN gs_user_table5 u ON m.sender_username = u.username 
                       WHERE m.receiver_username = :username 
                       AND m.sender_username NOT IN (SELECT blocked_username FROM user_blocks WHERE blocker_username = :username)
                       ORDER BY m.created_at DESC");
$stmt->bindValue(':username', $username, PDO::PARAM_STR);
$stmt->execute();
$received_messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 送信したメッセージを取得（受信者のプロフィール画像も含める）
$stmt = $pdo->prepare("SELECT m.*, u.profile_image FROM gs_messages_table m 
                       JOIN gs_user_table5 u ON m.receiver_username = u.username 
                       WHERE m.sender_username = :username ORDER BY m.created_at DESC");
$stmt->bindValue(':username', $username, PDO::PARAM_STR);
$stmt->execute();
$sent_messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>メッセージ</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@300;400;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-image: url('./img/background.jpg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            font-family: 'Noto Sans JP', sans-serif;
            font-size: 16px;
        }

        .profile-img {
            width: 50px;
            height: 50px;
            border-radius: 50%;   /* 真円 */
            object-fit: cover;    /* 枠に合わせて切り取る */
        }

        .navbar {
            background-color: #ff9800;
            padding: 15px 15px;
        }
        
        .navbar-brand {
            color: #ffffff !important;
            font-weight: 350;
            font-size: 1.2rem;
            margin-left: 10px; 
        }

        .navbar-brand:hover {
            text-decoration: underline;
        }        
        .message-container {
            background-color: rgba(255, 255, 255, 0.8);
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 30px;
        }

        .message {
            display: flex;
            align-items: flex-start;
            margin-bottom: 15px;
        }

        .message-content {
            flex-grow: 1;
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .received .message-content {
            background-color: #e8f5e9;
            border-top-left-radius: 0;
            margin-left: 10px;
        }

        .sent .message-content {
            background-color: #e3f2fd;
            border-top-right-radius: 0;
            margin-right: 10px;
        }

        .sent {
            flex-direction: row-reverse;
            text-align: right;
        }

        .message-img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }

        .message strong {
            font-size: 0.9em;
            color: #555;
        }

        .message p {
            margin: 10px 0;
        }

        .message small {
            font-size: 0.8em;
            color: #888;
        }

        h2, h3 {
            color: #000;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        
        .delete-btn {
            background-color: #ff4444;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.8em;
        }
        .delete-btn:hover {
            background-color: #cc0000;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark mb-4">
        <div class="container">
            <a>
            <img src="<?= $profile_image ?>" alt="Profile Image" class="profile-img">
            &thinsp;
            <?=$_SESSION["username"]?>さんの悩み、解決します！
            </a>
            <a class="navbar-brand" href="select.php"><i class="fa fa-table"></i>登録データ一覧</a>
            <a class="navbar-brand" href="logout.php"><i class="fas fa-sign-out-alt"></i>ログアウト</a>
        </div>
    </nav>

    <div class="container">
        <h2 class="mb-4">メッセージ</h2>

        <div class="message-container">
            <h3>受信したメッセージ</h3>
            <?php foreach ($received_messages as $message): ?>
                <div class="message received">
                    <img src="<?= $message['profile_image'] ? 'uploads/' . $message['profile_image'] : 'path/to/default/image.jpg' ?>" alt="Profile" class="message-img">
                    <div class="message-content">
                        <strong>
                            From: <?= h($message['sender_username']) ?>
                            <?php if ($message['sender_username'] === $top_poster_username): ?>
                                <i class="fas fa-crown text-warning" title="最多投稿者"></i>
                            <?php endif; ?>
                        </strong>
                        <p><?= h($message['message']) ?></p>
                        <small><i class="far fa-clock"></i> <?= h($message['created_at']) ?></small>
                        <button class="btn btn-primary btn-sm reply-btn" data-toggle="modal" data-target="#replyModal" data-username="<?= h($message['sender_username']) ?>">返信</button>
                        <button class="btn btn-danger btn-sm block-btn" data-toggle="modal" data-target="#blockModal" data-username="<?= h($message['sender_username']) ?>">ブロック</button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="message-container">
            <h3>送信したメッセージ</h3>
            <?php foreach ($sent_messages as $message): ?>
                <div class="message sent">
                    <img src="<?= $message['profile_image'] ? 'uploads/' . $message['profile_image'] : 'path/to/default/image.jpg' ?>" alt="Profile" class="message-img">
                    <div class="message-content">
                        <strong>
                            To: <?= h($message['receiver_username']) ?>
                            <?php if ($message['receiver_username'] === $top_poster_username): ?>
                                <i class="fas fa-crown text-warning" title="最多投稿者"></i>
                            <?php endif; ?>
                        </strong>
                        <p><?= h($message['message']) ?></p>
                        <small><i class="far fa-clock"></i> <?= h($message['created_at']) ?></small>
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="message_id" value="<?= $message['id'] ?>">
                            <button type="submit" name="delete_message" class="delete-btn">削除</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="message-container">
            <h3>ブロックしたユーザー</h3>
            <?php foreach ($blocked_users as $blocked_user): ?>
                <div>
                    <?= h($blocked_user) ?>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="blocked_username" value="<?= h($blocked_user) ?>">
                        <button type="submit" name="unblock_user" class="btn btn-warning btn-sm">ブロック解除</button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- 返信モーダル -->
    <div class="modal fade" id="replyModal" tabindex="-1" role="dialog" aria-labelledby="replyModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="replyModalLabel">返信</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="reply_to" id="replyTo">
                        <div class="form-group">
                            <label for="replyMessage">メッセージ:</label>
                            <textarea class="form-control" id="replyMessage" name="reply_message" rows="3" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">キャンセル</button>
                        <button type="submit" class="btn btn-primary">送信</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ブロックモーダル -->
    <div class="modal fade" id="blockModal" tabindex="-1" role="dialog" aria-labelledby="blockModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="blockModalLabel">ユーザーをブロック</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="blocked_username" id="blockedUsername">
                        <p>このユーザーをブロックしますか？</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">キャンセル</button>
                        <button type="submit" name="block_user" class="btn btn-danger">ブロック</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.reply-btn').click(function() {
                var username = $(this).data('username');
                $('#replyTo').val(username);
            });

            $('.block-btn').click(function() {
                var username = $(this).data('username');
                $('#blockedUsername').val(username);
            });
        });
    </script>
</body>
</html>
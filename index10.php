<?php
session_start();
include "funcs.php";

// DB接続
$pdo = db_conn();

// NGワードのリスト
$ng_words = [
    // 危険な表現
    'ブス', 'チビ', '死ね', 'のろま', '強姦', 'シャブ', 'ヤーバー', '強姦',

    // 暴力的な言葉
    '殺す',

    // 差別的な言葉
    '非人', '乞食', 'ルンペン', '毛唐', '紅毛唐', 'クロンボ', 'ロスケ', 'アメ公', 'チョン', 'ブリカス', 'ユダ公', 'めくら', 'つんぼ', 'おし', 'びっこ', 'かたわ', '土人', 'オカマ',

    // 性的な言葉
    '外性器', '男性器', '女性器', '陰茎', '陰核', 'ペニス', '巨根', 'マンコ', 'クリトリス', '体位', '騎乗位', 'まんぐり返し', 'クンニ', '手淫', '手マン', '手コキ', 'フェラチオ',
    '勃起', 'ガマン汁', '愛液', '潮吹き', '射精', '中出し', '本気汁', '立ちんぼ', 'イメクラ', 'デリヘル', 'ラブホ', 'ブルセラ', 'ソープランド', 'オナホール', 'ラブドール', '電マ',
    'ディルド', 'ピンロー', 'ペニバン', '裏ビデオ', 'ロリコン', 'スカトロ', '飲尿', '輪姦', '青姦', '獣姦', '屍姦', '浣腸プレイ',

    // 不適切な表現
    'クソ', 'カス', 'きもい',

    // 特定の属性を攻撃する言葉
    'デブ', 'ハゲ',

];

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

// Ajaxリクエストの処理
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_comment') {
    $worry_id = $_POST['worry_id'];
    $comment = $_POST['comment'];
    $username = $_SESSION['username'];

    // NGワードチェック
    $contains_ng_word = false;
    foreach ($ng_words as $word) {
        if (stripos($comment, $word) !== false) {
            $contains_ng_word = true;
            break;
        }
    }

    if ($contains_ng_word) {
        echo json_encode([
            'success' => false,
            'message' => '不適切な言葉が含まれています。投稿できません。'
        ]);
        exit;
    }

    $stmt = $pdo->prepare("INSERT INTO gs_worry_comments (worry_id, username, comment) VALUES (:worry_id, :username, :comment)");
    $stmt->bindValue(':worry_id', $worry_id, PDO::PARAM_INT);
    $stmt->bindValue(':username', $username, PDO::PARAM_STR);
    $stmt->bindValue(':comment', $comment, PDO::PARAM_STR);
    $stmt->execute();

    $comment_id = $pdo->lastInsertId();
    $created_at = date('Y-m-d H:i:s');

    // 新しいコメントのHTMLを返す
    echo json_encode([
        'id' => $comment_id,
        'username' => $username,
        'comment' => $comment,
        'created_at' => $created_at
    ]);
    exit;
} elseif (isset($_POST['action']) && $_POST['action'] === 'delete_comment') {
    $comment_id = $_POST['comment_id'];
    $username = $_SESSION['username'];

    // adminユーザーチェック
    $is_admin = ($username === 'admin');

    if ($is_admin) {
        // adminの場合、IDのみで削除
        $stmt = $pdo->prepare("DELETE FROM gs_worry_comments WHERE id = :comment_id");
        $stmt->bindValue(':comment_id', $comment_id, PDO::PARAM_INT);
    } else {
        // 通常ユーザーの場合、IDとユーザー名で削除
        $stmt = $pdo->prepare("DELETE FROM gs_worry_comments WHERE id = :comment_id AND username = :username");
        $stmt->bindValue(':comment_id', $comment_id, PDO::PARAM_INT);
        $stmt->bindValue(':username', $username, PDO::PARAM_STR);
    }
    $result = $stmt->execute();

    if ($result && $stmt->rowCount() > 0) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'コメントの削除に失敗しました。']);
    }
    exit;
} elseif (isset($_POST['action']) && $_POST['action'] === 'increment_proposal_count') {
    $worry_id = $_POST['worry_id'];

    $stmt = $pdo->prepare("UPDATE gs_worry SET proposal_count = proposal_count + 1 WHERE id = :worry_id");
    $stmt->bindValue(':worry_id', $worry_id, PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result) {
        $stmt = $pdo->prepare("SELECT proposal_count FROM gs_worry WHERE id = :worry_id");
        $stmt->bindValue(':worry_id', $worry_id, PDO::PARAM_INT);
        $stmt->execute();
        $new_count = $stmt->fetchColumn();
        
        echo json_encode(['success' => true, 'new_count' => $new_count]);
    } else {
        echo json_encode(['success' => false, 'message' => '提案回数の更新に失敗しました。']);
    }
    exit;
}

// 悩みの削除処理
if (isset($_POST['action']) && $_POST['action'] === 'delete_worry') {
    $worry_id = $_POST['worry_id'];
    $username = $_SESSION['username'];

    // adminユーザーチェック
$is_admin = ($username === 'admin');

if ($is_admin) {
    // adminの場合、IDのみで削除
    $stmt = $pdo->prepare("DELETE FROM gs_worry WHERE id = :worry_id");
    $stmt->bindValue(':worry_id', $worry_id, PDO::PARAM_INT);
} else {
    // 通常ユーザーの場合、IDとユーザー名で削除
    $stmt = $pdo->prepare("DELETE FROM gs_worry WHERE id = :worry_id AND username = :username");
    $stmt->bindValue(':worry_id', $worry_id, PDO::PARAM_INT);
    $stmt->bindValue(':username', $username, PDO::PARAM_STR);
}
$result = $stmt->execute();

    if ($result && $stmt->rowCount() > 0) {
        // 関連するコメントも削除
        $stmt = $pdo->prepare("DELETE FROM gs_worry_comments WHERE worry_id = :worry_id");
        $stmt->bindValue(':worry_id', $worry_id, PDO::PARAM_INT);
        $stmt->execute();

        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => '悩みの削除に失敗しました。']);
    }
    exit;
}

// 検索キーワードを取得
$search_keyword = isset($_GET['search']) ? $_GET['search'] : '';

// 悩みデータ取得（投稿者のプロフィール画像も含める）
$sql = "
    SELECT gs_worry.*, gs_user_table5.profile_image
    FROM gs_worry 
    LEFT JOIN gs_user_table5 ON gs_worry.username = gs_user_table5.username 
    WHERE 1=1
";

if (!empty($search_keyword)) {
    $sql .= " AND gs_worry.worry LIKE :keyword";
}

$sql .= " ORDER BY gs_worry.date DESC";

$stmt = $pdo->prepare($sql);

if (!empty($search_keyword)) {
    $stmt->bindValue(':keyword', '%'.$search_keyword.'%', PDO::PARAM_STR);
}

$status = $stmt->execute();


?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>悩み一覧</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
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

        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
        }

        .card-header {
            background-color: #4a5568;
            color: #ffffff;
            border-radius: 15px 15px 0 0 !important;
            padding: 15px;
        }

        .card-header h2 {
            font-size: 1.3rem;
            margin-bottom: 0;
        }

        .card-body {
            padding: 20px;
        }

        .poster-info {
            display: flex;
            align-items: center;
        }

        .poster-info img {
            margin-right: 10px;
        }

        .comment-section {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
        }
        .comment {
            margin-bottom: 15px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        
        .comment-header {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }
        .comment-username {
            font-weight: bold;
            margin-right: 10px;
        }
        .comment-date {
            font-size: 0.8em;
            color: #777;
        }
        .comment-content {
            line-height: 1.6;
        }
        .new-comment {
            animation: fadeIn 0.5s ease-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .delete-comment {
            cursor: pointer;
            color: #dc3545;
            margin-left: 10px;
        }

        .proposal-count {
            font-size: 0.9em;
            color: #6c757d;
            margin-left: 10px;
        }

        .poster-info {
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
        }

        .poster-info > div {
            display: flex;
            align-items: center;
        }

        .btn-danger.btn-sm {
            margin-left: 10px;
        }
        .crown-icon {
            color: gold;
            margin-left: 5px;
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
        <h1 class="mb-4">悩み一覧</h1>

        <!-- 検索フォーム -->
        <form action="" method="GET" class="mb-4">
            <div class="input-group">
                <input type="text" class="form-control" placeholder="悩みを検索" name="search" value="<?= h($search_keyword) ?>">
                <div class="input-group-append">
                    <button class="btn btn-warning" type="submit"><i class="fas fa-search"></i>検索</button>
                    <button class="btn btn-secondary" type="button" id="resetSearch"><i class="fas fa-undo"></i>リセット</button>
                </div>
            </div>
        </form>

        <?php
        if($status==false) {
            sql_error($stmt);
        } else {
            while( $result = $stmt->fetch(PDO::FETCH_ASSOC)){ 
                $poster_image = $result['profile_image'] ? 'uploads/' . $result['profile_image'] : 'path/to/default/image.jpg';
        ?>
        <div class="card">
        <div class="card-header">
            <div class="poster-info d-flex justify-content-between align-items-center">
                <div>
                    <img src="<?= $poster_image ?>" alt="Poster Profile Image" class="profile-img">
                    <span>
                        投稿者: <?=$result["username"]?>
                        <?php if ($result["username"] === $top_poster['username']): ?>
                            <i class="fas fa-crown crown-icon" title="最多投稿者"></i>
                        <?php endif; ?>
                        | 日時: <?=$result["date"]?>
                    </span>
                </div>
                <?php if ($result["username"] === $_SESSION['username'] || $_SESSION['username'] === 'admin'): ?>
                    <button type="button" class="btn btn-danger btn-sm ml-2" onclick="deleteWorry(<?=$result['id']?>)">削除</button>
                <?php endif; ?>
            </div>
        </div>
            <div class="card-body">
            <p class="card-text"><?=$result["worry"]?></p>
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-primary" onclick="showCommentForm(<?=$result['id']?>)">コメントする</button>
                    <button type="button" class="btn btn-success" onclick="proposeBook(<?=$result['id']?>, '<?=htmlspecialchars($result["worry"], ENT_QUOTES)?>')">
                        解決本を提案
                        <span id="proposalCount<?=$result['id']?>" class="proposal-count">(<?=$result['proposal_count'] ?? 0?>)</span>
                    </button>
                </div>
                <div id="commentForm<?=$result['id']?>" style="display:none; margin-top: 15px;">
                    <form onsubmit="return addComment(<?=$result['id']?>)">
                        <textarea id="commentText<?=$result['id']?>" class="form-control" rows="3" required></textarea>
                        <button type="submit" class="btn btn-primary mt-2">コメントを投稿</button>
                    </form>
                </div>
                <div id="commentSection<?=$result['id']?>" class="comment-section">
                    <?php
                    $comment_stmt = $pdo->prepare("SELECT * FROM gs_worry_comments WHERE worry_id = :worry_id ORDER BY created_at DESC");
                    $comment_stmt->bindValue(':worry_id', $result['id'], PDO::PARAM_INT);
                    $comment_stmt->execute();
                    while ($comment = $comment_stmt->fetch(PDO::FETCH_ASSOC)) {
                    ?>

                    <div class="comment" id="comment<?=$comment['id']?>">
                        <div class="comment-header">
                            <span class="comment-username">
                                <?=$comment['username']?>
                                <?php if ($comment['username'] === $top_poster['username']): ?>
                                    <i class="fas fa-crown crown-icon" title="最多投稿者"></i>
                                <?php endif; ?>
                            </span>
                            <span class="comment-date"><?=date('Y/m/d H:i', strtotime($comment['created_at']))?></span>
                            <?php if ($comment['username'] === $_SESSION['username'] || $_SESSION['username'] === 'admin'): ?>
                                <span class="delete-comment" onclick="deleteComment(<?=$comment['id']?>)"><i class="fas fa-trash-alt"></i></span>
                            <?php endif; ?>
                        </div>
                        <div class="comment-content"><?=$comment['comment']?></div>
                    </div>

                    <?php
                    }
                    ?>
                </div>
            </div>
        </div>
        <?php
            }
        }
        ?>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <script>
        function showCommentForm(id) {
            var form = document.getElementById('commentForm' + id);
            form.style.display = form.style.display === 'none' ? 'block' : 'none';
        }

        function addComment(worryId) {
            var commentText = document.getElementById('commentText' + worryId).value;
            
            $.ajax({
                url: 'index10.php',
                method: 'POST',
                data: {
                    action: 'add_comment',
                    worry_id: worryId,
                    comment: commentText
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        var commentSection = document.getElementById('commentSection' + worryId);
                        var newComment = document.createElement('div');
                        newComment.className = 'comment new-comment';
                        newComment.id = 'comment' + response.id;
                        newComment.innerHTML = `
                            <div class="comment-header">
                                <span class="comment-username">${response.username}</span>
                                <span class="comment-date">${new Date(response.created_at).toLocaleString()}</span>
                                <span class="delete-comment" onclick="deleteComment(${response.id})"><i class="fas fa-trash-alt"></i></span>
                            </div>
                            <div class="comment-content">${response.comment}</div>
                        `;
                        commentSection.insertBefore(newComment, commentSection.firstChild);
                        
                        document.getElementById('commentText' + worryId).value = '';
                        showCommentForm(worryId);
                    } else {
                        alert(response.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('エラー:', error);
                    alert('コメントの投稿中にエラーが発生しました。');
                }
            });

            return false;  // フォームのデフォルトの送信を防ぐ
        }

        function deleteComment(commentId) {
            if (confirm('このコメントを削除してもよろしいですか？')) {
                $.ajax({
                    url: 'index10.php',
                    method: 'POST',
                    data: {
                        action: 'delete_comment',
                        comment_id: commentId
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            $('#comment' + commentId).remove();
                        } else {
                            alert(response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('エラー:', error);
                        alert('コメントの削除中にエラーが発生しました。');
                    }
                });
            }
        }

        function proposeBook(id, worry) {
            $.ajax({
                url: 'index10.php',
                method: 'POST',
                data: {
                    action: 'increment_proposal_count',
                    worry_id: id
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        $('#proposalCount' + id).text('(' + response.new_count + ')');
                    } else {
                        console.error('提案回数の更新に失敗しました:', response.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('エラー:', error);
                }
            });

            window.location.href = 'index2.php?worry_id=' + id + '&worry=' + encodeURIComponent(worry);
        }

        function deleteWorry(worryId) {
            if (confirm('この悩みを削除してもよろしいですか？関連するコメントもすべて削除されます。')) {
                $.ajax({
                    url: 'index10.php',
                    method: 'POST',
                    data: {
                        action: 'delete_worry',
                        worry_id: worryId
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            // 悩みのカードを削除
                            $('.card').filter(function() {
                                return $(this).find('button[onclick^="deleteWorry(' + worryId + ')"]').length > 0;
                            }).remove();
                        } else {
                            alert(response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('エラー:', error);
                        alert('悩みの削除中にエラーが発生しました。');
                    }
                });
            }
        }

        // 検索リセット機能
        document.getElementById('resetSearch').addEventListener('click', function() {
            window.location.href = 'index10.php';
        });
    </script>

</body>
</html>
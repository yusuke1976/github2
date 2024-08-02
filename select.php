<?php
session_start();

include "funcs.php";

//１. DB接続します
$pdo = db_conn();

sschk();

// ユーザーのプロフィール画像を取得
$stmt = $pdo->prepare("SELECT profile_image FROM gs_user_table5 WHERE username = :username");
$stmt->bindValue(':username', $_SESSION['username'], PDO::PARAM_STR);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$profile_image = $user['profile_image'] ? 'uploads/' . $user['profile_image'] : 'path/to/default/image.jpg';

// ユーザーのジャンルを取得
$stmt = $pdo->prepare("SELECT genre FROM gs_user_table5 WHERE username = :username");
$stmt->bindValue(':username', $_SESSION['username'], PDO::PARAM_STR);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$user_genre = $user['genre'];

// ブロックしたユーザーのリストを取得
$stmt = $pdo->prepare("SELECT blocked_username FROM user_blocks WHERE blocker_username = :username");
$stmt->bindValue(':username', $_SESSION['username'], PDO::PARAM_STR);
$stmt->execute();
$blocked_users = $stmt->fetchAll(PDO::FETCH_COLUMN);

// ブロックしたユーザーからの通知を除外して取得
if (!empty($blocked_users)) {
    $placeholders = implode(',', array_fill(0, count($blocked_users), '?'));
    $sql = "SELECT * FROM notifications 
            WHERE recipient_username = ? 
            AND (sender_username NOT IN ($placeholders) OR sender_username = '')
            ORDER BY created_at DESC LIMIT 5";
    $params = array_merge([$_SESSION['username']], $blocked_users);
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
} else {
    $sql = "SELECT * FROM notifications WHERE recipient_username = ? ORDER BY created_at DESC LIMIT 5";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$_SESSION['username']]);
}
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 未読の通知数を取得（ブロックしたユーザーからの通知を除外）
if (!empty($blocked_users)) {
    $placeholders = implode(',', array_fill(0, count($blocked_users), '?'));
    $sql = "SELECT COUNT(*) FROM notifications
            WHERE recipient_username = ? 
            AND is_read = FALSE 
            AND (sender_username NOT IN ($placeholders) OR sender_username = '')";
    $params = array_merge([$_SESSION['username']], $blocked_users);
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
} else {
    $sql = "SELECT COUNT(*) FROM notifications WHERE recipient_username = ? AND is_read = FALSE";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$_SESSION['username']]);
}
$unread_count = $stmt->fetchColumn();

// 通知ドロップダウンメニューの HTML を更新
$notification_html = '';
if (empty($notifications)) {
    $notification_html .= '<a class="dropdown-item" href="#">通知はありません</a>';
} else {
    foreach ($notifications as $notification) {
        $notification_html .= '<a class="dropdown-item ' . ($notification['is_read'] ? '' : 'font-weight-bold') . '" href="#">';
        $notification_html .= h($notification['message']);
        $notification_html .= '</a>';
    }
    $notification_html .= '<div class="dropdown-divider"></div>';
    $notification_html .= '<a class="dropdown-item" href="mark_all_read.php">すべて既読にする</a>';
}

// 検索キーワードとフィルターオプションを取得
$search_keyword = isset($_GET['search']) ? $_GET['search'] : '';
$filter_different_genre = isset($_GET['filter_different_genre']) ? $_GET['filter_different_genre'] : false;

// 「助かりました」ボタンが押された場合の処理
if(isset($_POST['helpful'])) {
  $book_id = $_POST['book_id'];
  $stmt = $pdo->prepare("UPDATE gs_bm_table SET helpful_count = helpful_count + 1 WHERE id = :id");
  $stmt->bindValue(':id', $book_id, PDO::PARAM_INT);
  if($stmt->execute()) {
      // 更新後のhelpful_countを取得
      $stmt = $pdo->prepare("SELECT helpful_count FROM gs_bm_table WHERE id = :id");
      $stmt->bindValue(':id', $book_id, PDO::PARAM_INT);
      $stmt->execute();
      $result = $stmt->fetch(PDO::FETCH_ASSOC);
      echo json_encode(['success' => true, 'newCount' => $result['helpful_count']]);
      exit;
  } else {
      echo json_encode(['success' => false]);
      exit;
  }
}

// 最多投稿者を取得
$stmt = $pdo->prepare("SELECT username, COUNT(*) as post_count FROM gs_bm_table GROUP BY username ORDER BY post_count DESC LIMIT 1");
$stmt->execute();
$top_poster = $stmt->fetch(PDO::FETCH_ASSOC);

// フォロー状態を確認する関数
function isFollowing($pdo, $follower, $followed) {
    $stmt = $pdo->prepare("SELECT * FROM user_follows WHERE follower_username = :follower AND followed_username = :followed");
    $stmt->execute([':follower' => $follower, ':followed' => $followed]);
    return $stmt->rowCount() > 0;
}

// ソート順を取得
$sort_order = isset($_GET['sort']) ? $_GET['sort'] : 'desc';

// データ取得SQL作成
$sql = "SELECT b.*, u.genre FROM gs_bm_table b JOIN gs_user_table5 u ON b.username = u.username WHERE 1=1";
$params = array();

if (!empty($search_keyword)) {
    $sql .= " AND (b.book LIKE :keyword OR b.worry LIKE :keyword OR b.coment LIKE :keyword OR b.username LIKE :keyword)";
    $params[':keyword'] = '%'.$search_keyword.'%';
}

if ($filter_different_genre) {
    $sql .= " AND u.genre != :user_genre";
    $params[':user_genre'] = $user_genre;
}

// ソート順を適用
$sql .= " ORDER BY b.date " . ($sort_order === 'asc' ? 'ASC' : 'DESC');

$stmt = $pdo->prepare($sql);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value, PDO::PARAM_STR);
}
$status = $stmt->execute();

//３．データ表示
$view = "";
if ($status == false) {
    $error = $stmt->errorInfo();
    exit("ErrorQuery:".$error[2]);
} else {
    $view .= '<div class="row">';
    while ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $view .= '<div class="col-md-4 mb-4">';
        $view .= '<div class="card h-100">';
        $view .= '<div class="card-body">';
        $view .= '<h5 class="card-title">' . h($result['book']) . '</h5>';
        $view .= '<h6 class="card-subtitle mb-2 text-muted">' . h($result['date']) . '</h6>';
        // 投稿者名の表示を変更
        $view .= '<p class="card-text"><strong>投稿者：</strong>' . h($result['username']);
        if ($result['username'] === $top_poster['username']) {
            $view .= ' <i class="fas fa-crown text-warning" title="最多投稿者"></i>';
        }
        
        $view .= '<p class="card-text"><strong>悩み：</strong>' . h($result['worry']) . '</p>';
        $view .= '<p class="card-text"><strong>コメント：</strong>' . h($result['coment']) . '</p>';
        $view .= '<a href="' . h($result['url']) . '" class="btn btn-primary btn-block mb-2" target="_blank"><i class="fas fa-external-link-alt"></i> 詳細を見る</a>';
        $view .= '<button class="btn btn-info btn-block mb-2 send-message-btn" data-username="' . h($result['username']) . '"><i class="far fa-envelope"></i> メッセージを送る</button>';

        // 「助かりました」ボタンとメッセージを追加
        $voted_users = explode(',', $result['voted_users']);
        $isVoted = in_array($_SESSION['username'], $voted_users);

        $view .= '<div id="helpfulMessage_' . h($result['id']) . '" class="alert alert-success mb-2 text-center" style="display:none;">投票ありがとう！</div>';
        $view .= '<button class="btn btn-helpful btn-block mb-2 helpful-button' . ($isVoted ? ' voted' : '') . '" data-id="' . h($result['id']) . '">';
        $view .= '<i class="' . ($isVoted ? 'fas' : 'far') . ' fa-heart mr-2"></i><span class="button-text">' . ($isVoted ? 'キャンセル' : '助かりました') . '</span> <span class="helpful-count">' . h($result['helpful_count']) . '</span>';
        $view .= '</button>';

        // フォローボタンを追加
        if ($result['username'] !== $_SESSION['username']) {
            $isFollowing = isFollowing($pdo, $_SESSION['username'], $result['username']);
            $followBtnClass = $isFollowing ? 'btn-secondary' : 'btn-primary';
            $followBtnText = $isFollowing ? 'フォロー解除' : 'フォローする';
            $view .= '<button class="btn ' . $followBtnClass . ' btn-block mb-2 follow-btn" data-username="' . h($result['username']) . '">' . $followBtnText . '</button>';
        }

        if ($result['username'] === $_SESSION['username'] || $_SESSION['username'] === 'admin') {
            $view .= '<div class="d-flex justify-content-between">';
            $view .= '<a href="detail.php?id=' . h($result['id']) . '" class="btn btn-success flex-grow-1 mr-2">更新</a>';
            $view .= '<a href="delete.php?id=' . h($result['id']) . '" class="btn btn-danger flex-grow-1" onclick="return confirm(\'本当に削除しますか？\');">削除</a>';
            $view .= '</div>';
        }
        $view .= '</div></div></div>';
    }
    $view .= '</div>';
}

?>

<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>登録データ表示</title>
<link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css" rel="stylesheet">

<style>
    body {
        background-image: url('./img/background2.jpg');
        background-size: cover;
        background-position: center;
        background-attachment: fixed;
        background-color: #f8f9fa;
    }
    .profile-img {
        width: 50px;
        height: 50px;
        border-radius: 50%;   /* 真円 */
        object-fit: cover;    /* 枠に合わせて切り取る */
    }

    .navbar { background-color: #007bff; }
    .navbar-brand { color: white !important; }
    .navbar-brand:hover { text-decoration: underline; }
    .card { box-shadow: 0 4px 8px rgba(0,0,0,0.1); transition: 0.3s; }
    .card:hover { box-shadow: 0 8px 16px rgba(0,0,0,0.2); }
    .btn-primary { background-color: #007bff; border-color: #007bff; }
    .btn-primary:hover { background-color: #0056b3; border-color: #0056b3; }

    /* 投票ありがとうメッセージのスタイル */
    .thank-you-message {
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: linear-gradient(45deg, #FF69B4, #FF1493);
        color: white;
        padding: 15px 30px;
        border-radius: 30px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        opacity: 0;
        transition: all 0.3s ease;
        z-index: 1000;
    }

    .thank-you-message.show {
        opacity: 1;
        animation: pulse 0.5s ease-in-out;
    }

    @keyframes pulse {
        0% { transform: translate(-50%, -50%) scale(0.9); }
        50% { transform: translate(-50%, -50%) scale(1.1); }
        100% { transform: translate(-50%, -50%) scale(1); }
    }

    /* 助かりましたボタンのスタイル */
    .btn-helpful {
        background-color: #FFB6C1; /* 優しいピンク */
        border-color: #FFB6C1;
        color: #fff;
    }
    .btn-helpful:hover {
        background-color: #FF69B4; /* ホバー時少し濃いピンク */
        border-color: #FF69B4;
        color: #fff;
    }

    .filter-container {
        display: flex;
        align-items: center;
        margin-top: 15px;
    }

    .filter-checkbox {
        transform: scale(2);
        margin-right: 15px;
    }

    .filter-label {
        font-size: 1.1em;
        font-weight: bold;
        color: #007bff;
        background-color: #f8f9fa;
        padding: 5px 15px;
        border-radius: 5px;
        border: 2px solid #007bff;
        display: flex;
        align-items: center;
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .filter-label:hover {
        background-color: #007bff;
        color: #fff;
    }

    .user-welcome{
        font-size: 1rem;
    }

    .firework-container {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        pointer-events: none;
        z-index: 9999;
        overflow: hidden;
    }

    .firework {
        position: absolute;
    }

    .firework-particle {
        position: absolute;
        width: 4px;
        height: 4px;
        border-radius: 50%;
        animation: firework-explode 1s ease-out forwards;
    }

    .sort-container {
        margin-top: 15px;
    }

    .btn-sort {
        background-color: #FFA500; /* オレンジ */
        border-color: #FF8C00;
        color: white;
        font-weight: bold;
        transition: all 0.3s ease;
    }

    .btn-sort:hover {
        background-color: #FF8C00; /* ダークオレンジ */
        border-color: #FF7F50;
    }

    .btn-sort.active {
        background-color: #FF4500; /* オレンジレッド */
        border-color: #FF0000;
    }

    .btn-sort.active:hover {
        background-color: #DC143C; /* クリムゾン */
        border-color: #B22222;
    }


    @keyframes firework-explode {
        0% { transform: scale(1); opacity: 1; }
        100% { transform: scale(20); opacity: 0; }
    }
    .small-nav-text {
        font-size: 0.9rem;
    }
</style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-gradient-primary">
  <div class="container">
    <a class="navbar-brand d-flex align-items-center">
      <img src="<?= $profile_image ?>" alt="Profile Image" class="profile-img mr-2">
      <span class="user-welcome text-dark">
        <?=$_SESSION["username"]?>さん<br>
        の悩み、解決します！
      </span>
    </a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ml-auto">
        <li class="nav-item">
          <a class="nav-link text-white small-nav-text" href="index3.php"><i class="fas fa-plus-circle"></i> 悩み登録</a>
        </li>
        <li class="nav-item">
          <a class="nav-link text-white small-nav-text" href="index10.php"><i class="fas fa-list-ul"></i> 悩み一覧</a>
        </li>
        <li class="nav-item">
          <a class="nav-link text-white small-nav-text" href="index2.php"><i class="fas fa-database"></i> データ登録</a>
        </li>
        <li class="nav-item">
          <a class="nav-link text-white small-nav-text" href="gpt.php"><i class="fas fa-search"></i> AI書籍検索</a>
        </li>
        <li class="nav-item">
          <a class="nav-link text-white small-nav-text" href="user_edit.php"><i class="fa fa-pen"></i> ユーザー情報編集</a>
        </li>
        <li class="nav-item">
          <a class="nav-link text-white small-nav-text" href="messages.php"><i class="far fa-envelope"></i> メッセージ</a>
        </li>

        <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle text-white" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <i class="fas fa-bell"></i>
            <?php if ($unread_count > 0): ?>
            <span class="badge badge-danger"><?= $unread_count ?></span>
            <?php endif; ?>
        </a>
        <div class="dropdown-menu" aria-labelledby="navbarDropdown">
            <?= $notification_html ?>
        </div>
        </li>

        <li class="nav-item">
          <a class="nav-link text-white small-nav-text" href="logout.php"><i class="fas fa-sign-out-alt"></i> ログアウト</a>
        </li>
      </ul>
    </div>
  </div>
</nav>

<div class="container">
  <h2 class="text-center mb-4 font-weight-bold text-warning"><i class="fas fa-book-open"></i>登録データ一覧</h2>

  <!-- 「助かりました」メッセージの表示 -->
  <?php if (!empty($helpful_message)): ?>
    <div class="alert alert-success" role="alert">
      <?= $helpful_message ?>
    </div>
  <?php endif; ?>

    <!-- 検索フォームと並び替えボタン -->
  <form action="" method="GET" class="mb-4" id="searchForm">
    <div class="input-group">
        <input type="text" class="form-control" placeholder="キーワードを入力" name="search" id="searchInput" value="<?= h($search_keyword) ?>">
        <div class="input-group-append">
            <button class="btn btn-warning" type="submit"><i class="fas fa-search"></i>検索</button>
            <button class="btn btn-secondary" type="button" id="resetSearch"><i class="fas fa-undo"></i>リセット</button>
        </div>
    </div>
    <div class="filter-container mt-2">
        <input class="filter-checkbox" type="checkbox" id="filterDifferentGenre" name="filter_different_genre" value="1" <?= $filter_different_genre ? 'checked' : '' ?>>
        <label class="filter-label" for="filterDifferentGenre">
            <i class="fas fa-filter mr-2"></i> 読書歴が違う人の投稿のみ表示
        </label>
    </div>
    <div class="sort-container mt-2">
        <button type="submit" name="sort" value="<?= $sort_order === 'asc' ? 'desc' : 'asc' ?>" class="btn btn-sort <?= $sort_order === 'desc' ? 'active' : '' ?>">
            <i class="fas fa-sort"></i> 
            <?= $sort_order === 'asc' ? '新しい順に並べ替え' : '古い順に並べ替え' ?>
        </button>
    </div>
  </form>

  <?= $view ?>

</div>

<!-- メッセージ送信用モーダル -->
<div class="modal fade" id="messageModal" tabindex="-1" role="dialog" aria-labelledby="messageModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="messageModalLabel">メッセージを送信</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form id="messageForm">
          <input type="hidden" id="receiverUsername" name="receiverUsername">
          <div class="form-group">
            <label for="messageText">メッセージ:</label>
            <textarea class="form-control" id="messageText" name="messageText" rows="3" required></textarea>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">閉じる</button>
        <button type="button" class="btn btn-primary" id="sendMessageBtn">送信</button>
      </div>
    </div>
  </div>
</div>

<div class="firework-container" style="display: none;"></div>

<script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>

<!-- 検索リセット用のJavaScript -->
<script>
document.getElementById('resetSearch').addEventListener('click', function() {
    document.getElementById('searchInput').value = '';
    document.getElementById('filterDifferentGenre').checked = false;
    // ソート順もリセット
    var sortButton = document.querySelector('button[name="sort"]');
    sortButton.value = 'desc';
    sortButton.innerHTML = '<i class="fas fa-sort"></i> 古い順に並べ替え';
    sortButton.classList.add('active');
    document.getElementById('searchForm').submit();
});

function createFirework(x, y) {
    const colors = ['#ff0000', '#00ff00', '#0000ff', '#ffff00', '#ff00ff', '#00ffff', '#ffffff'];
    const firework = document.createElement('div');
    firework.className = 'firework';
    firework.style.left = x + 'px';
    firework.style.top = y + 'px';

    const particleCount = Math.floor(Math.random() * 80) + 50; // 粒子数を増やしました
    for (let i = 0; i < particleCount; i++) {
        const particle = document.createElement('div');
        particle.className = 'firework-particle';
        particle.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
        
        const angle = Math.random() * Math.PI * 2;
        const velocity = Math.random() * 60 + 30; // 速度範囲を調整しました
        const tx = Math.cos(angle) * velocity;
        const ty = Math.sin(angle) * velocity;

        particle.style.left = '0px';
        particle.style.top = '0px';

        particle.animate([
            { transform: 'translate(0, 0) scale(1)', opacity: 1 },
            { transform: `translate(${tx}px, ${ty}px) scale(0)`, opacity: 0 }
        ], {
            duration: Math.random() * 1000 + 700, // アニメーション時間を調整しました
            easing: 'cubic-bezier(0,0,0.25,1)'
        });

        firework.appendChild(particle);
    }

    return firework;
}

function launchFireworks() {
    const container = document.querySelector('.firework-container');
    container.style.display = 'block';
    container.innerHTML = '';  // Clear previous fireworks

    const width = window.innerWidth;
    const height = window.innerHeight;

    for (let i = 0; i < 8; i++) {  // 花火の数を調整しました
        setTimeout(() => {
            const x = Math.random() * width;
            const y = height - Math.random() * height / 2;  // 打ち上げ位置を調整しました
            const firework = createFirework(x, y);
            container.appendChild(firework);

            // Remove the firework element after animation
            setTimeout(() => firework.remove(), 2000);
        }, Math.random() * 1500);  // 打ち上げタイミングを調整しました
    }

    // Hide container after all fireworks are done
    setTimeout(() => {
        container.style.display = 'none';
    }, 3500);
}

// 助かりましたボタンのAjax処理
$(document).ready(function() {
    $('.helpful-button').on('click', function(e) {
        e.preventDefault();
        var button = $(this);
        var id = button.data('id');
        var isVoted = button.hasClass('voted');
        $.ajax({
            url: 'update_helpful.php',
            type: 'POST',
            data: { 
                helpful: !isVoted, 
                book_id: id, 
                username: '<?php echo $_SESSION['username']; ?>' 
            },
            dataType: 'json',
            success: function(response) {
                if(response.success) {
                    var countElement = button.find('.helpful-count');
                    countElement.text(response.newCount);
                    $('#helpfulMessage_' + id).text(response.message).fadeIn().delay(400).fadeOut();
                    button.toggleClass('voted');
                    var icon = button.find('i');
                    var buttonText = button.find('.button-text');
                    if (isVoted) {
                        icon.removeClass('fas').addClass('far');
                        buttonText.text('助かりました');
                    } else {
                        icon.removeClass('far').addClass('fas');
                        buttonText.text('キャンセル');
                    }
                    
                    // 初めての投票の場合、花火アニメーションを表示
                    if (response.isFirstVote) {
                        launchFireworks();
                    }
                } else {
                    alert(response.message || 'エラーが発生しました。');
                }
            },
            error: function() {
                alert('通信エラーが発生しました。');
            }
        });
    });
});
</script>

<script>
// メッセージ送信ボタンのクリックイベント
$(document).on('click', '.send-message-btn', function() {
    var receiverUsername = $(this).data('username');
    $('#receiverUsername').val(receiverUsername);
    $('#messageModal').modal('show');
});

// メッセージ送信処理
$('#sendMessageBtn').on('click', function() {
    var receiverUsername = $('#receiverUsername').val();
    var messageText = $('#messageText').val();

    $.ajax({
        url: 'send_message.php',
        type: 'POST',
        data: {
            receiverUsername: receiverUsername,
            messageText: messageText
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                alert(response.message);
                $('#messageModal').modal('hide');
                $('#messageText').val('');
            } else {
                alert('エラー: ' + response.message);
            }
        },
        error: function() {
            alert('通信エラーが発生しました。');
        }
    });
});
</script>

<script>
$(document).ready(function() {
    $('.follow-btn').on('click', function() {
        var button = $(this);
        var username = button.data('username');
        var action = button.text() === 'フォローする' ? 'follow' : 'unfollow';

        $.ajax({
            url: 'follow_action.php',
            type: 'POST',
            data: {
                action: action,
                username: username
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    if (action === 'follow') {
                        $('.follow-btn[data-username="' + username + '"]').each(function() {
                            $(this).text('フォロー解除').removeClass('btn-primary').addClass('btn-secondary');
                        });
                    } else {
                        $('.follow-btn[data-username="' + username + '"]').each(function() {
                            $(this).text('フォローする').removeClass('btn-secondary').addClass('btn-primary');
                        });
                    }
                } else {
                    alert('エラー: ' + response.message);
                }
            },
            error: function() {
                alert('通信エラーが発生しました。');
            }
        });
    });
});
</script>

<script>
function loadNotifications() {
    $.ajax({
        url: 'get_notifications.php',
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            var menu = $('.dropdown-menu');
            menu.empty();
            $('.badge-danger').text(response.unread);
            
            if (response.notifications.length === 0) {
                menu.append('<a class="dropdown-item" href="#">通知はありません</a>');
            } else {
                response.notifications.forEach(function(notification) {
                    var item = $('<a class="dropdown-item" href="#"></a>');
                    item.text(notification.message);
                    if (!notification.is_read) {
                        item.addClass('font-weight-bold');
                    }
                    menu.append(item);
                });
                menu.append('<div class="dropdown-divider"></div>');
                menu.append('<a class="dropdown-item" href="mark_all_read.php">すべて既読にする</a>');
            }
        }
    });
}

$(document).ready(function() {
    loadNotifications();
    setInterval(loadNotifications, 60000); // 1分ごとに更新
});
</script>

</body>
</html>
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

// 検索キーワードを取得
$search_keyword = isset($_GET['search']) ? $_GET['search'] : '';

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

//２．データ取得SQL作成
if (!empty($search_keyword)) {
    $stmt = $pdo->prepare("SELECT * FROM gs_bm_table WHERE book LIKE :keyword OR worry LIKE :keyword OR coment LIKE :keyword");
    $stmt->bindValue(':keyword', '%'.$search_keyword.'%', PDO::PARAM_STR);
} else {
    $stmt = $pdo->prepare("SELECT * FROM gs_bm_table");
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
        $view .= '<p class="card-text"><strong>投稿者：</strong>' . h($result['username']) . '</p>';
        $view .= '<p class="card-text"><strong>悩み：</strong>' . h($result['worry']) . '</p>';
        $view .= '<p class="card-text"><strong>コメント：</strong>' . h($result['coment']) . '</p>';
        $view .= '<a href="' . h($result['url']) . '" class="btn btn-primary btn-block mb-2" target="_blank">詳細を見る</a>';

        // 「助かりました」ボタンとメッセージを追加
        $view .= '<div id="helpfulMessage_' . h($result['id']) . '" class="alert alert-success mb-2 text-center" style="display:none;">投票ありがとう！</div>';
        $view .= '<button class="btn btn-helpful btn-block mb-2 helpful-button" data-id="' . h($result['id']) . '">';
        $view .= '<i class="far fa-heart mr-2"></i>助かりました <span class="helpful-count">' . h($result['helpful_count']) . '</span>';
        $view .= '</button>';

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
    <a class="navbar-brand" href="index3.php"><i class="fas fa-plus-circle"></i>悩み登録</a>
    <a class="navbar-brand" href="index2.php"><i class="fas fa-database"></i>データ登録</a>
    <a class="navbar-brand" href="gpt.php"><i class="fas fa-search"></i>AI書籍検索</a>
    <a class="navbar-brand" href="user_edit.php"><i class="fa fa-pen"></i>ユーザー情報編集</a>
    <a class="navbar-brand" href="logout.php"><i class="fas fa-sign-out-alt"></i>ログアウト</a>
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

  <!-- 検索フォーム -->
  <form action="" method="GET" class="mb-4"  id="searchForm">
    <div class="input-group">
      <input type="text" class="form-control" placeholder="キーワードを入力" name="search" id="searchInput" value="<?= h($search_keyword) ?>">
      <div class="input-group-append">
        <button class="btn btn-warning" type="submit"><i class="fas fa-search"></i>検索</button>
        <button class="btn btn-secondary" type="button" id="resetSearch"><i class="fas fa-undo"></i>リセット</button>
      </div>
    </div>
  </form>

  <?= $view ?>
</div>

<script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>

<!-- 検索リセット用のJavaScript -->
<script>
document.getElementById('resetSearch').addEventListener('click', function() {
    document.getElementById('searchInput').value = '';
    document.getElementById('searchForm').submit();
});

// 助かりましたボタンのAjax処理
$(document).ready(function() {
    $('.helpful-button').on('click', function(e) {
        e.preventDefault();
        var button = $(this);
        var id = button.data('id');
        $.ajax({
            url: 'update_helpful.php',
            type: 'POST',
            data: { helpful: true, book_id: id },
            dataType: 'json',
            success: function(response) {
                if(response.success) {
                    var countElement = button.find('.helpful-count');
                    countElement.text(response.newCount);
                    $('#helpfulMessage_' + id).fadeIn().delay(400).fadeOut();
                } else {
                    alert('エラーが発生しました。');
                }
            },
            error: function() {
                alert('通信エラーが発生しました。');
            }
        });
    });
});
</script>

</body>
</html>
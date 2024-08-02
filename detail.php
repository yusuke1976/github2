<?php
//0. SESSION開始！！
session_start();

include "funcs.php";

$id = $_GET["id"];

//１. DB接続します
$pdo = db_conn();

//LOGINチェック → funcs.phpへ関数化しましょう！
sschk();

// ユーザーのプロフィール画像を取得
$stmt = $pdo->prepare("SELECT profile_image FROM gs_user_table5 WHERE username = :username");
$stmt->bindValue(':username', $_SESSION['username'], PDO::PARAM_STR);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$profile_image = $user['profile_image'] ? 'uploads/' . $user['profile_image'] : 'path/to/default/image.jpg';

//２．データ登録SQL作成
$sql = "SELECT * FROM gs_bm_table WHERE id=:id";
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':id', $id, PDO::PARAM_INT);  //Integer（数値の場合 PDO::PARAM_INT)
$status = $stmt->execute();

//３．データ表示
$values = "";
if($status==false) {
  sql_error($stmt);
}

//全データ取得
$v    = $stmt->fetch(); //PDO::FETCH_ASSOC[カラム名のみで取得できるモード]
// $json = json_encode($values,JSON_UNESCAPED_UNICODE);






?>
<!--
２．HTML
以下にindex.phpのHTMLをまるっと貼り付ける！
理由：入力項目は「登録/更新」はほぼ同じになるからです。
※form要素 input type="hidden" name="id" を１項目追加（非表示項目）
※form要素 action="update.php"に変更
※input要素 value="ここに変数埋め込み"
-->

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>データ更新</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@300;400;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css" rel="stylesheet">

<style>
        body {
            background-image: url('./img/background2.jpg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
        }
        .profile-img {
            width: 50px;
            height: 50px;
            border-radius: 50%;   /* 真円 */
            object-fit: cover;    /* 枠に合わせて切り取る */
        }
    </style>

    
    <style>
        /* body {
            font-family: 'Noto Sans JP', sans-serif;
            background-color: #f8f9fa;
        } */
        .navbar {
            background-color: #ff9800;
            padding: 15px 15px;
        }
        
        .navbar-brand {
            color: #ffffff !important;
            font-weight: 350;
        }
        .navbar-brand:hover {
            text-decoration: underline;
        }

        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .card-header {
            background-color: #4a5568;
            color: #ffffff;
            border-radius: 15px 15px 0 0 !important;
        }
        .form-control {
            border-radius: 10px;
        }

        .btn-primary {
            background-color: #4a5568;
            border-color: #4a5568;
            border-radius: 10px;
        }
        .btn-primary:hover {
            background-color: #2c3340;
            border-color: #2c3340;
        }
        
    </style>
</head>

<body>
<nav class="navbar navbar-expand-lg navbar-dark mb-4">
        <div class="container">
            <a>
            <img src="<?= $profile_image ?>" alt="Profile Image" class="profile-img">
            &thinsp;
            <?=$_SESSION["username"]?>さん、一緒に悩みを解決しましょう！
            </a>
            <a class="navbar-brand" href="select.php"><i class="fa fa-table"></i>登録データ一覧</a>
            <a class="navbar-brand" href="logout.php"><i class="fas fa-sign-out-alt"></i>ログアウト</a>
        </div>
    </nav>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h2 class="mb-0">データ更新</h2>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="update.php">
                            <div class="form-group">
                                <label for="worry">あなたの悩み</label>
                                <textarea class="form-control" id="worry" name="worry" rows="4" placeholder="ここに悩みを入力してください"><?=$v["worry"]?></textarea>
                            </div>
                            <div class="form-group">
                                <label for="book">書籍名</label>
                                <input type="text" class="form-control" id="book" name="book" value="<?=$v["book"]?>">>
                            </div>
                            <div class="form-group">
                                <label for="url">書籍URL</label>
                                <input type="text" class="form-control" id="url" name="url" value="<?=$v["url"]?>">>
                            </div>
                            <div class="form-group">
                                <label for="coment">ヒントとなった点</label>
                                <textarea class="form-control" id="coment" name="coment" rows="4" placeholder="どのような点がヒントとなったのか入力してください"><?=$v["coment"]?></textarea>
                            </div>
                            <input type="hidden" name="id" value="<?=$v["id"]?>">
                            <button type="submit" class="btn btn-primary btn-block">送信</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>





<!-- <!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title>データ更新</title>
  <link href="css/bootstrap.min.css" rel="stylesheet">
  <style>div{padding: 10px;font-size:16px;}</style>
</head>
<body> -->

<!-- Head[Start] -->
<!-- <header>
  <nav class="navbar navbar-default">
    <div class="container-fluid">
    <div class="navbar-header"><a class="navbar-brand" href="select.php">データ一覧</a></div>
    </div>
  </nav>
</header> -->
<!-- Head[End] -->

<!-- Main[Start] -->
<!-- <form method="POST" action="update.php">
  <div class="jumbotron">
   <fieldset>
    <legend>フリーアンケート更新</legend>
//     <label>名前：<input type="text" name="name" value="<?=$v["name"]?>"></label><br>
//     <label>Email：<input type="text" name="email" value="<?=$v["email"]?>"></label><br>
//     <label>年齢：<input type="text" name="age" value="<?=$v["age"]?>"></label><br>
//     <label><textArea name="naiyou" rows="4" cols="40"><?=$v["naiyou"]?></textArea></label><br>
//     <input type="hidden" name="id" value="<?=$v["id"]?>">
     <input type="submit" value="送信">
    </fieldset>
  </div>
</form> -->
<!-- Main[End] -->


<!-- </body>
</html> -->




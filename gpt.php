<?php

session_start();
include "funcs.php";

// DB接続
$pdo = db_conn();

sschk();

// ユーザーのプロフィール画像を取得
$stmt = $pdo->prepare("SELECT profile_image FROM gs_user_table5 WHERE username = :username");
$stmt->bindValue(':username', $_SESSION['username'], PDO::PARAM_STR);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$profile_image = $user['profile_image'] ? 'uploads/' . $user['profile_image'] : 'path/to/default/image.jpg';

?>

<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="">
        <title>AI書籍検索</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
        <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@300;400;700&display=swap" rel="stylesheet">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css" rel="stylesheet">

        <meta name="theme-color" content="#7952b3">
        <style>
            body {
                background-image: url('./img/background4.jpg');
                background-size: cover;
                background-position: center;
                background-attachment: fixed;
                font-family: 'Noto Sans JP', sans-serif;
                font-size: 16px;
            }

            h1{
                color: #FFF;
                text-shadow: 
                    0 0 0.05em #F06292,
                    0 0 0.10em #F06292,
                    0 0 0.15em #F06292,
                    0 0 0.30em #F06292;
                filter: saturate(80%);
            }

            .profile-img {
                width: 50px;
                height: 50px;
                border-radius: 50%;
                object-fit: cover;
            }

            .navbar {
                background-color: #6c5ce7;
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

            .container {
                max-width: 1200px;
            }
            textarea {
                width:100%;
                height:100px;
            }
            #outputText{
                width: 100%;
                height: 100%;
                background-color: #f8f9fa;
                border: 1px solid #ced4da;
                border-radius: 0.25rem;
                padding: 15px;
                margin-top: 15px;
                white-space: pre-wrap;
                word-wrap: break-word;
                font-size: 1rem;
                line-height: 1.5;
                text-align: left;
                display: none; /* 初期状態で非表示 */
            }

            .btn {
                border-radius: 25px;
                padding: 10px 20px;
                font-weight: 500;
                transition: all 0.3s ease;
            }

            .btn-primary {
                background-color: #6c5ce7;
                border-color: #6c5ce7;
            }

            .btn-primary:hover {
                background-color: #5b4cdb;
                border-color: #5b4cdb;
                transform: translateY(-2px);
                box-shadow: 0 4px 10px rgba(108,92,231,.3);
            }

            .btn-secondary {
                background-color: #95a5a6;
                border-color: #95a5a6;
            }

            .btn-secondary:hover {
                background-color: #7f8c8d;
                border-color: #7f8c8d;
            }

            .input-group {
                width: 100%;
                max-width: 1000px;
                margin: 0 auto;
            }

            #formText {
                flex-grow: 1;
                height: 48px; /* ボタンの高さに合わせる */
            }

            @media (max-width: 768px) {
                .container {
                    padding-left: 20px;
                    padding-right: 20px;
                }
                .input-group {
                    flex-direction: column;
                }
                #formText, #btn, #resetBtn {
                    width: 100%;
                    margin-bottom: 10px;
                }
                #formText {
                    height: 60px; /* スマホ画面での高さを増加 */
                }
                #formText::placeholder {
                    font-size: 14px;
                    white-space: normal;
                    overflow: visible;
                    position: absolute; /* プレースホルダーを絶対位置に */
                    top: 8px; /* 上部から8pxの位置に配置 */
                }
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
    
        <main>
            <section class="text-center container">
                <div class="col-lg-8 col-md-10 mx-auto">
                    <h1 class="mb-3 fw-medium">書籍検索</h1>
                    
                    <div>
                        <textarea
                        id="inputText"
                        class="mt-3 mb-3"
                        placeholder="ここに悩みを入力してください。AIがあなたにおすすめの本をご紹介します。"
                        ></textarea>
                        <button class="btn btn-primary btn-lg" onclick="submitPrompt()">
                            <i class="fas fa-magic mr-2"></i> AI選書！悩みを解決
                        </button>
                        <div id="outputText" class="mt-3 mb-3"></div>
                    </div>

                    <div class="input-group mt-4">
                        <input type="text" id="formText" name="myFormText" class="form-control" placeholder="キーワード（本のタイトルや内容、著者等）を入力して検索" aria-label="books" aria-describedby="btn">
                        <button id="btn" class="btn btn-primary"><i class="fas fa-search"></i>検索</button>
                        <button id="resetBtn" class="btn btn-secondary"><i class="fas fa-undo"></i>リセット</button>
                    </div>                            

                    <!-- 青空文庫へのリンクを追加 -->
                    <div class="mt-4">
                        <a href="https://www.aozora.gr.jp/" target="_blank" class="btn btn-info">
                            <i class="fas fa-book mr-2"></i>青空文庫で無料の書籍を探す
                        </a>
                    </div>
                </div>
            </section>

            <div id="bookItem" class="container">
                <div class="row row-cols-1 row-cols-md-3 g-4 mt-3"></div>
            </div>
        </main>
    </body>
    <script src="gpt.js"></script>
</html>
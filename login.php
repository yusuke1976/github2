<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ユーザー登録</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@300;400;700&display=swap" rel="stylesheet">


    <style>
        body {
            background-image: url('./img/background2.jpg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;

            font-family: 'Arial', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        #registration-form {
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            width: 350px;
        }
        .image-container {
            text-align: center;
        }
        .image-container img {
            width: 100%;
            max-width: 200px;
            border-radius: 50%;
        }
        .title{
            text-align: center;
            font-weight: bold;
            font-size: 1.2rem;     
        }
        h3 {
            text-align: center;
            color: #333;
            margin-bottom: 1.5rem;
        }
        form {
            display: flex;
            flex-direction: column;
        }
        input {
            margin-bottom: 1rem;
            padding: 0.5rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
        }
        button {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 0.7rem;
            border-radius: 4px;
            font-size: 1rem;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        button:hover {
            background-color: #45a049;
        }
        .hidden {
            display: none;
        }
        select{
            width:200px;
            height:40px;
        }
        #step2{
            text-align: center;
        }
        #step3{
            text-align: center;
        }
        #result{
            text-align: center;
        }
    </style>
</head>
<body>
<div id="registration-form">
        <div class="image-container">
            <img src="./img/logo.jpg" alt="">
        </div>
        <p class="title">Bookshelf Of Worries</p>
        
        <h3>ログイン</h3>
            <form action="login_act.php" method="post">
                <input type="text" name="username" id="username" placeholder="ユーザー名" required>
                <input type="password" name="password" id="password" placeholder="パスワード" required>
                <button type="submit">ログイン</button>
            </form>
        
</div>

</body>
</html>
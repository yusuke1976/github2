<?php
session_start();

include "funcs.php";

//1. POSTデータ取得
$username = filter_input( INPUT_POST, "username" );
$email    = filter_input( INPUT_POST, "email" );
$password = filter_input( INPUT_POST, "password" );
$concern  = filter_input( INPUT_POST, "concern" );
$genre    = filter_input( INPUT_POST, "genre" );
$password = password_hash($password, PASSWORD_DEFAULT);   //パスワードハッシュ化

//2. DB接続します
$pdo = db_conn();

// メールアドレスの重複チェック用SQLを準備
$stmt = $pdo->prepare("SELECT email FROM gs_user_table5 WHERE email=:email");
$stmt->bindValue(':email', $email, PDO::PARAM_STR);
$status = $stmt->execute();

// SQL実行時にエラーがある場合
if($status==false){
    $error = $stmt->errorInfo();
    exit("QueryError:".$error[2]);
}

// 重複がある場合はエラーメッセージを返して処理を中断
if($stmt->rowCount() > 0){
    echo "既に登録されているメールアドレスです。";
    exit;
}

// ユーザー名の重複チェック
$stmt = $pdo->prepare("SELECT username FROM gs_user_table5 WHERE username=:username");
$stmt->bindValue(':username', $username, PDO::PARAM_STR);
$status = $stmt->execute();

// SQL実行時にエラーがある場合
if($status==false){
    $error = $stmt->errorInfo();
    exit("QueryError:" . $error[2]);
}

// 重複がある場合はエラーメッセージを返して処理を中断
if ($stmt->rowCount() > 0) {
    echo "既に登録されているユーザー名です。";
    exit;
}

// 重複がなければ登録処理を続行
//３．データ登録SQL作成
$sql = "INSERT INTO gs_user_table5(username,email,password,concern,genre,life_flg)VALUES(:username,:email,:password,:concern,:genre,0)";
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':username', $username, PDO::PARAM_STR); //Integer（数値の場合 PDO::PARAM_INT)
$stmt->bindValue(':email', $email, PDO::PARAM_STR); //Integer（数値の場合 PDO::PARAM_INT)
$stmt->bindValue(':password', $password, PDO::PARAM_STR); //Integer（数値の場合 PDO::PARAM_INT)
$stmt->bindValue(':concern', $concern, PDO::PARAM_STR); //Integer（数値の場合 PDO::PARAM_INT)
$stmt->bindValue(':genre', $genre, PDO::PARAM_STR); //Integer（数値の場合 PDO::PARAM_INT)
$status = $stmt->execute();

//４．データ登録処理後
if ($status == false) {
    sql_error($stmt);
} else {
    redirect("login.php");
}

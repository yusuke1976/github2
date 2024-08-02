<?php
//0. SESSION開始！！
session_start();

require_once('funcs.php');

//1. POSTデータ取得
$username = $_POST['username'];
$worry = $_POST['worry'];
// $book = $_POST['book'];
// $url = $_POST['url'];
// $coment = $_POST['coment'];


//１. DB接続します
$pdo = db_conn();

//LOGINチェック → funcs.phpへ関数化しましょう！
sschk();

//３．データ登録SQL作成
$stmt = $pdo->prepare('INSERT INTO
                gs_worry( username, id, worry, date )
                VALUES( :username, NULL, :worry, now() ) ');

//  2. バインド変数を用意
// Integer 数値の場合 PDO::PARAM_INT
// String文字列の場合 PDO::PARAM_STR
$stmt->bindValue(':username', $username, PDO::PARAM_STR);
$stmt->bindValue(':worry', $worry, PDO::PARAM_STR);
// $stmt->bindValue(':book', $book, PDO::PARAM_STR);
// $stmt->bindValue(':url', $url, PDO::PARAM_STR);
// $stmt->bindValue(':coment', $coment, PDO::PARAM_STR);

//  3. 実行
$status = $stmt->execute();

//４．データ登録処理後
if($status === false) {
    //SQL実行時にエラーがある場合（エラーオブジェクト取得して表示）
    $error = $stmt->errorInfo();
    exit('ErrorMessage:'.$error[2]);
} else {
    //５．index.phpへリダイレクト
    header('Location: index3.php');

}
 ?>
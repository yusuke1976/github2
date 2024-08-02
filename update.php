<?php
//0. SESSION開始！！
session_start();

require_once('funcs.php');

$worry  = $_POST["worry"];
$book   = $_POST["book"];
$url    = $_POST["url"];
$coment = $_POST["coment"];
$id     = $_POST["id"];

//１. DB接続します
$pdo = db_conn();

//LOGINチェック → funcs.phpへ関数化しましょう！
sschk();

//３．データ登録SQL作成
$sql = "UPDATE gs_bm_table SET worry=:worry,book=:book,url=:url,coment=:coment WHERE id=:id";
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':worry',  $worry,  PDO::PARAM_STR);  //Integer（数値の場合 PDO::PARAM_INT)
$stmt->bindValue(':book',   $book,   PDO::PARAM_STR);  //Integer（数値の場合 PDO::PARAM_INT)
$stmt->bindValue(':url',    $url,    PDO::PARAM_STR);  //Integer（数値の場合 PDO::PARAM_INT)
$stmt->bindValue(':coment', $coment, PDO::PARAM_STR);  //Integer（数値の場合 PDO::PARAM_INT)
$stmt->bindValue(':id',     $id,     PDO::PARAM_INT);  //Integer（数値の場合 PDO::PARAM_INT)
$status = $stmt->execute(); //実行


//４．データ登録処理後
if($status==false){
    //*** function化する！*****************
    sql_error($stmt);
}else{
    //*** function化する！*****************
    // redirect("select.php");

    //リダイレクト関数: redirect($file_name)
    // function redirect($file_name){
    header("Location: select.php");
    exit();
//   }
  
}








?>

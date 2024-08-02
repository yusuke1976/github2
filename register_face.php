<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gs_db5";

// データベース接続
$conn = new mysqli($servername, $username, $password, $dbname);

// 接続チェック
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_POST['image'])) {
    $image = $_POST['image'];
    $image = str_replace('data:image/jpeg;base64,', '', $image);
    $image = str_replace(' ', '+', $image);
    $imageData = base64_decode($image);

    // ユニークなファイル名を生成
    $filename = uniqid() . '.jpg';
    $filepath = 'uploads/' . $filename;

    // 画像を保存
    file_put_contents($filepath, $imageData);

    // データベースに登録
    $sql = "INSERT INTO faces (filepath) VALUES (?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $filepath);

    if ($stmt->execute()) {
        echo "顔画像が正常に登録されました。";
    } else {
        echo "エラー: " . $sql . "<br>" . $conn->error;
    }

    $stmt->close();
} else {
    echo "画像データがありません。";
}

$conn->close();
?>
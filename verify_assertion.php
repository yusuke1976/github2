<?php
header('Content-Type: application/json');

$host = 'localhost';
$db   = "gs_db5";
$user = "root";
$pass = "";
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);

    $input = json_decode(file_get_contents('php://input'), true);
    $credentialId = base64_decode($input['id']);

    $stmt = $pdo->prepare("SELECT public_key FROM credentials WHERE credential_id = ?");
    $stmt->execute([$credentialId]);
    $credential = $stmt->fetch();

    if ($credential) {
        $publicKey = $credential['public_key'];

        // ここで実際の認証検証を行う
        // この例では簡略化のため、常に成功を返していますが、
        // 実際にはクライアントから送られた署名を検証する必要があります
        echo json_encode(['status' => 'success', 'message' => '認証成功']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'クレデンシャルが見つかりません']);
    }
} catch (\PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
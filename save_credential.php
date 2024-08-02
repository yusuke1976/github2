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

    $data = json_decode(file_get_contents('php://input'), true);
    $credentialId = $data['credentialId'];
    $credentialPublicKey = $data['credentialPublicKey'];

    $stmt = $pdo->prepare("INSERT INTO credentials (credential_id, public_key) VALUES (?, ?) ON DUPLICATE KEY UPDATE public_key = ?");
    $stmt->execute([$credentialId, $credentialPublicKey, $credentialPublicKey]);

    echo json_encode(['status' => 'success']);
} catch (\PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
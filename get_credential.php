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

    $stmt = $pdo->query("SELECT credential_id, public_key FROM credentials LIMIT 1");
    $credential = $stmt->fetch();

    if ($credential) {
        echo json_encode([
            'credentialId' => $credential['credential_id'],
            'credentialPublicKey' => $credential['public_key']
        ]);
    } else {
        echo json_encode(['credentialId' => null, 'credentialPublicKey' => null]);
    }
} catch (\PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
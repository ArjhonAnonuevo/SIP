<?php
require_once __DIR__ . '/../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$secretKey = $_ENV['SECRET_KEY'];
include "../configuration/interns_config.php";

$config = getDatabaseConfig();
$dbhost = $config['dbhost'];
$dbuser = $config['dbuser'];
$dbpass = $config['dbpass'];
$dbname = $config['dbname'];

$conn = new mysqli($dbhost, $dbuser, $dbpass, $dbname);

$response = [];

if ($conn->connect_error) {
    $response['error'] = "Connection failed: " . $conn->connect_error;
} else {
    $sql = "SELECT username FROM interns_account";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $qrcodes = [];

        while ($row = $result->fetch_assoc()) {
            $username = $row['username'];

            $key = $secretKey;
            $iv = random_bytes(16);
            $encryptedUsername = openssl_encrypt($username, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);

            $encodedIV = str_replace(['+', '/'], ['-', '_'], base64_encode($iv));
            $encodedEncryptedUsername = str_replace(['+', '/'], ['-', '_'], base64_encode($encryptedUsername));

            $qrcodes[] = [
                'username' => $username,
                'qrData' => "{$encodedIV}|{$encodedEncryptedUsername}",
            ];
        }

        $response['qrcodes'] = $qrcodes;
    } else {
        $response['error'] = "No data found in the tables.";
    }

    $conn->close();
}

echo json_encode($response);
?>

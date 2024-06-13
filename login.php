<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

require 'vendor/autoload.php';
use \Firebase\JWT\JWT;

$secret_key = "test123";
$issuer = "YOUR_ISSUER"; // this can be your domain name
$audience = "YOUR_AUDIENCE";
$issued_at = time();
$expiration_time = $issued_at + 3600; // JWT valid for 1 hour
$refresh_expiration_time = $issued_at + 604800; // Refresh token valid for 7 days

include("db.php");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$rawData = file_get_contents('php://input');
$data = json_decode($rawData, true);

$username = $data['username'] ?? '';
$password = $data['password'] ?? '';

header('Content-Type: application/json; charset=UTF-8');

if (mysqli_connect_errno()) {
    $output = [
        'status' => [
            'code' => "300",
            'name' => "failure",
            'description' => "database unavailable",
            'returnedIn' => (microtime(true) - $executionStartTime) / 1000 . " ms"
        ],
        'data' => []
    ];

    echo json_encode($output);
    exit;
}

if (validate_user($username, $password, $conn)) {
    $token = [
        "iss" => $issuer,
        "aud" => $audience,
        "iat" => $issued_at,
        "exp" => $expiration_time,
        "data" => [
            "username" => $username
        ]
    ];
    $jwt = JWT::encode($token, $secret_key);

    $refresh_token = [
        "iss" => $issuer,
        "aud" => $audience,
        "iat" => $issued_at,
        "exp" => $refresh_expiration_time,
        "data" => [
            "username" => $username
        ]
    ];
    $refresh_jwt = JWT::encode($refresh_token, $secret_key);

    echo json_encode([
        "access_token" => $jwt,
        "refresh_token" => $refresh_jwt
    ]);
} else {
    echo json_encode(["message" => "Invalid username or password"]);
}

function validate_user($username, $password, $conn) {
    $sql = "SELECT * FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($password, $user['password_hash'])) {
        return true;
    } else {
        return false;
    }
}

mysqli_close($conn);
?>

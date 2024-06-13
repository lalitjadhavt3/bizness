<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

require '../vendor/autoload.php';
use \Firebase\JWT\JWT;

$secret_key = "test123";
$issuer = "https://lalitjadhav.in"; // Your domain
$audience = "YOUR_AUDIENCE"; // Intended audience

$rawData = file_get_contents('php://input');
$data = json_decode($rawData, true);

$refresh_token = $data['refresh_token'] ?? '';

header('Content-Type: application/json; charset=UTF-8');

if ($refresh_token) {
    try {
        $decoded = JWT::decode($refresh_token, $secret_key, ['HS256']);
        if ($decoded->iss !== $issuer || $decoded->aud !== $audience) {
            echo json_encode(["message" => "Invalid refresh token"]);
            exit();
        }
        
        // Generate a new access token
        $issued_at = time();
        $expiration_time = $issued_at + 3600; // Access token valid for 1 hour

        $token = [
            "iss" => $issuer,
            "aud" => $audience,
            "iat" => $issued_at,
            "exp" => $expiration_time,
            "data" => [
                "username" => $decoded->data->username // You can include additional data if needed
            ]
        ];
        $new_access_token = JWT::encode($token, $secret_key, 'HS256');

        echo json_encode(["access_token" => $new_access_token]);
    } catch (Exception $e) {
        echo json_encode(["message" => "Invalid refresh token"]);
    }
} else {
    echo json_encode(["message" => "No refresh token provided"]);
}
?>

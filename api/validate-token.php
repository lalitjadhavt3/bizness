<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header('Content-Type: application/json; charset=UTF-8');
require '../vendor/autoload.php';
use \Firebase\JWT\JWT;
use \Firebase\JWT\ExpiredException;
use \Firebase\JWT\Key;
$secret_key = "test123";
$issuer = "https://lalitjadhav.in"; // Your domain
$audience = "YOUR_AUDIENCE"; // Intended audience

$authHeader = $_SERVER['HTTP_AUTHORIZATION'];
$arr = explode(" ", $authHeader);
$jwt = $arr[1];


if ($jwt) {
    try {
        $decoded = JWT::decode($jwt, new Key($secret_key, 'HS256'));;
        if ($decoded->iss !== $issuer || $decoded->aud !== $audience) {
            echo json_encode(["valid" => false, "message" => "Invalid token"]);
            exit();
        }
        echo json_encode(["valid" => true, "data" => $decoded->data]);
    } catch (ExpiredException $e) {
        echo json_encode(["valid" => false, "message" => "Token expired"]);
    } catch (Exception $e) {
        echo json_encode(["valid" => false, "message" => "Invalid token"]);
    }
} else {
    echo json_encode(["valid" => false, "message" => "No token provided"]);
}
?>

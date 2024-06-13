<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

require '../vendor/autoload.php';
use \Firebase\JWT\JWT;

$secret_key = "test123";
$issuer = "https://lalitjadhav.in"; // this can be your domain name
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

if (validate_user($username, $password, $con)) {
    $token = [
        "iss" => $issuer,
        "aud" => $audience,
        "iat" => $issued_at,
        "exp" => $expiration_time,
        "data" => [
            "username" => $username
        ]
    ];
    $jwt = JWT::encode($token, $secret_key,'HS256');

    $refresh_token = [
        "iss" => $issuer,
        "aud" => $audience,
        "iat" => $issued_at,
        "exp" => $refresh_expiration_time,
        "data" => [
            "username" => $username
        ]
    ];
    $refresh_jwt = JWT::encode($refresh_token, $secret_key,'HS256');

    echo json_encode([
        "success" => true,
        "access_token" => $jwt,
        "refresh_token" => $refresh_jwt
    ]);
}else {
    echo json_encode(["success" => false, "message" => "Invalid username or password"]);
}

function validate_user($username, $password, $con) {
    $username = stripslashes($username);
    $password = stripslashes($password);
    $username = mysqli_real_escape_string($con,$username);
    $password = mysqli_real_escape_string($con,$password);

    $sql = "SELECT * FROM users WHERE email='$username'";
       
    $query = mysqli_query($con, $sql);
    $rows = mysqli_num_rows($query);
    $usertype=NULL;

    while($row = mysqli_fetch_assoc($query))
    {
        if ($row['id'] && password_verify($password, $row["password_hash"])) {
            return true;
        } else {
            return false;
        }


    }
   
}

mysqli_close($con);
?>

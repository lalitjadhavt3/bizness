<?php

header("Access-Control-Allow-Origin: *");

// Specify which HTTP methods are allowed
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");

// Specify which headers are allowed
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Handle preflight requests (OPTIONS)

extract($_POST);
ini_set('display_errors', 'On');
error_reporting(E_ALL);

$executionStartTime = microtime(true);

include("db.php");

header('Content-Type: application/json; charset=UTF-8');

if (mysqli_connect_errno()) {
    $output['status']['code'] = "300";
    $output['status']['name'] = "failure";
    $output['status']['description'] = "database unavailable";
    $output['status']['returnedIn'] = (microtime(true) - $executionStartTime) / 1000 . " ms";
    $output['data'] = [];

    mysqli_close($conn);

    echo json_encode($output);
    exit;
}

// Get username and password from request

// Prepare SQL statement
$sql = "SELECT * FROM users WHERE username = ?";
$stmt = $conn->prepare($sql);

// Bind parameters
$stmt->bind_param("s", $username);

// Execute query
$stmt->execute();

// Get result
$result = $stmt->get_result();

// Fetch user data
$user = $result->fetch_assoc();

// Check if user exists and verify password
if ($user && password_verify($password, $user['password_hash'])) {
    // User exists and password is correct
    $output['status']['code'] = "200";
    $output['status']['name'] = "ok";
    $output['status']['description'] = "success";
    $output['status']['returnedIn'] = (microtime(true) - $executionStartTime) / 1000 . " ms";
    $output['data'] = [];
} else {
    // User does not exist or password is incorrect
    $output['status']['code'] = "400";
    $output['status']['name'] = "executed";
    $output['status']['description'] = "query failed";  
    $output['data'] = [];
}

// Close statement and connection
$stmt->close();
mysqli_close($conn);

echo json_encode($output);

?>

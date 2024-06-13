<?php
// Define the environment
define('ENVIRONMENT', 'development'); // Change this to 'staging' or 'production' as needed

// Database configurations for different environments
$config = [
    'development' => [
        'host' => 'localhost',
        'username' => 'root',
        'password' => '',
        'database' => 'bizness_logins',
    ],
    'staging' => [
        'host' => 'staging_host',
        'username' => 'staging_user',
        'password' => 'staging_password',
        'database' => 'bizness_logins',
    ],
    'production' => [
        'host' => 'production_host',
        'username' => 'production_user',
        'password' => 'production_password',
        'database' => 'bizness_logins',
    ],
];

// Get the current environment configuration
$currentConfig = $config[ENVIRONMENT];

// Connect to the database
$con = mysqli_connect($currentConfig['host'], $currentConfig['username'], $currentConfig['password'], $currentConfig['database']);

// Check the connection
if (mysqli_connect_errno()) {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
}
?>

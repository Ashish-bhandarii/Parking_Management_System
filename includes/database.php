<?php

// Database connection settings for InfinityFree
$dbhost = "sql101.infinityfree.com"; // Correct MySQL Host from InfinityFree
$dbname = "if0_39880353_pm";        // Your database name
$dbuser = "if0_39880353";           // Your InfinityFree DB username
$dbpassword = "f6AuqMQA2bWHX1S";    // Your InfinityFree DB password

// Connect to the database using MySQLi
$connect = mysqli_connect($dbhost, $dbuser, $dbpassword, $dbname);

// Check for connection errors
if (!$connect) {
    echo "Database connection failed: " . mysqli_connect_error();
    exit;
} else {
    // Optional: Uncomment for testing only
    // echo "Database connected successfully!";
}
?>

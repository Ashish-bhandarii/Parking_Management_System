<?php
session_start();
include('includes/database.php'); // Include database connection

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "Please log in to update your profile.";
    exit;
}

$userId = $_SESSION['user_id'];
$userName = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : "Guest";
$email = isset($_SESSION['email']) ? $_SESSION['email'] : "";
$phone = isset($_SESSION['phone']) ? $_SESSION['phone'] : "";
$password = isset($_SESSION['password']) ? $_SESSION['password'] : "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize inputs
    $name = mysqli_real_escape_string($connect, $_POST['username']);
    $email = mysqli_real_escape_string($connect, $_POST['email']);
    $phone = mysqli_real_escape_string($connect, $_POST['phone']);
    $password = !empty($_POST['password']) ? mysqli_real_escape_string($connect, $_POST['password']) : '';

    // Hash password if provided
    if (!empty($password)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $query = "UPDATE Users SET name='$name', email='$email', phone='$phone', password='$hashedPassword' WHERE user_id=$userId";
    } else {
        $query = "UPDATE Users SET name='$name', email='$email', phone='$phone' WHERE user_id=$userId";
    }

    if (mysqli_query($connect, $query)) {
        // Update session variables if needed
        $_SESSION['email'] = $email;
        $_SESSION['phone'] = $phone;
        echo "Profile updated successfully.";
    } else {
        echo "Error updating profile: " . mysqli_error($connect);
    }
}
?>  

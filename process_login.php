<?php
session_start();
require 'includes/database.php'; // Include the DB connection file

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get login details
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Query the database to check for the user
    $sql = "SELECT * FROM Users WHERE email = ?";

    $stmt = $connect->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    // print_r($result);
    // exit;
    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();

        // $pass =  password_hash($_POST['password'], PASSWORD_DEFAULT);
        $pass =  password_hash($_POST['password'],PASSWORD_DEFAULT);
        // die ($pass);
        // echo $_POST['password'].'<br />';
        // echo  password_hash($_POST['password'],PASSWORD_DEFAULT).'<br />';
        // die($user['password']);
        
       
        // Verify password
        if (password_verify($_POST['password'], $user['password'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_type'] = $user['user_type'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['phone'] = $user['phone'];
            $_SESSION['password'] = $user['password'];
            // Redirect to the appropriate dashboard
            if ($user['user_type'] === 'admin') {
                
                header("Location: admin/dashboard.php"); // Admin dashboard
            } else {
                header("Location: user/user_dashboard.php"); // User dashboard
            }
            exit;
        } else {
            // die('test');
            $_SESSION['error'] = "Incorrect password";
            header("Location: index.php"); // Redirect to login page with error message
            exit;
        }
    } else {
        $_SESSION['error'] = "User not found";
        header("Location: index.php"); // Redirect to login page with error message
        exit;
    }

    $stmt->close();
    $connect->close();
}
?>

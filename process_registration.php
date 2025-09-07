<?php
// process_registration.php
session_start();
require 'includes/database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    $errors = [];
    $field_errors = [];
    
    if (!preg_match("/^[a-zA-Z ]{3,}$/", $name)) {
        $field_errors['name'] = "Name must only contain letters and spaces, and be at least 3 characters long.";
    }
    
    if (!preg_match("/^(98|97)\d{8}$/", $phone)) {
        $field_errors['phone'] = "Phone number must be 10 digits and start with 98 or 97.";
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $field_errors['email'] = "Invalid email format.";
    }
    
    if ($password !== $confirm_password) {
        $field_errors['confirm_password'] = "Passwords do not match.";
    }
    
    // Check if email already exists
    $sql = "SELECT * FROM Users WHERE email = ?";
    $stmt = $connect->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $field_errors['email'] = "Email already exists.";
    }
    
    if (!empty($field_errors)) {
        $_SESSION['field_errors'] = $field_errors;
        // Store the form data to repopulate the form
        $_SESSION['form_data'] = [
            'name' => $name,
            'phone' => $phone,
            'email' => $email
        ];
        header("Location: index.php");
        exit();
    }
    
    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert the user into the database
    $sql = "INSERT INTO Users (name, phone, email, password) VALUES (?, ?, ?, ?)";
    $stmt = $connect->prepare($sql);
    $stmt->bind_param("ssss", $name, $phone, $email, $hashed_password);
    
    if ($stmt->execute()) {
        $_SESSION['register_success'] = "Registration successful. You can now Sign in.";
        header("Location: index.php");
    } else {
        $_SESSION['register_error'] = "An error occurred, please try again.";
        header("Location: index.php");
    }
    
    $stmt->close();
    $connect->close();
}
?>
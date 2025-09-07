<?php
// Include database connection and header
include('../includes/database.php');

// Initialize variables
$messageSent = false;
$error = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the values from the form
    $name = htmlspecialchars($_POST['name']);
    $email = htmlspecialchars($_POST['email']);
    $message = htmlspecialchars($_POST['message']);

    // Validate inputs
    if (!empty($name) && !empty($email) && !empty($message)) {
        // Insert the message into the database (without user_id)
        $query = "INSERT INTO ContactMessages (name, email, message) VALUES (?, ?, ?)";
        $stmt = mysqli_prepare($connect, $query);
        mysqli_stmt_bind_param($stmt, "sss", $name, $email, $message); // Bind parameters

        if (mysqli_stmt_execute($stmt)) {
            $messageSent = true;
        } else {
            $error = "Failed to send your message. Please try again.";
        }
        mysqli_stmt_close($stmt);
    } else {
        $error = "All fields are required.";
    }
}
?>
<style>
/* General Styling */
body {
    font-family: Arial, sans-serif;
    background-color: #f4f4f4;
    margin: 0;
    padding: 0;
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
}

/* Contact Container */
.contact-container {
    background-color: #fff;
    max-width: 600px;
    width: 100%;
    padding: 30px;
    margin: 20px;
    border-radius: 8px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    position: relative;
    text-align: center;
}

/* Back to Home Button */
.back-to-home {
    position: absolute;
    top: 10px;
    right: 10px;
    font-size: 0.9em;
    color: #3498db;
    text-decoration: none;
    padding: 5px 10px;
    border: 1px solid #3498db;
    border-radius: 5px;
    transition: all 0.3s ease;
}

.back-to-home:hover {
    background-color: #3498db;
    color: #fff;
}

/* Form Heading and Description */
.contact-container h1 {
    font-size: 2em;
    margin-bottom: 20px;
    color: #333;
}

.contact-container p {
    font-size: 1.1em;
    color: #555;
    margin-bottom: 20px;
}

/* Success/Error Messages */
.success {
    color: #2ecc71;
    font-weight: bold;
    margin-bottom: 15px;
}

.error {
    color: #e74c3c;
    font-weight: bold;
    margin-bottom: 15px;
}

/* Contact Form */
.contact-form {
    display: flex;
    flex-direction: column;
    gap: 15px;
    text-align: left;
}

.contact-form .form-group {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.contact-form label {
    font-size: 1em;
    color: #333;
}

.contact-form input,
.contact-form textarea {
    padding: 10px;
    font-size: 1em;
    border: 1px solid #ddd;
    border-radius: 5px;
    width: 100%;
}

.contact-form textarea {
    resize: vertical;
}

.contact-form input:focus,
.contact-form textarea:focus {
    outline: none;
    border-color: #3498db;
    box-shadow: 0 0 5px rgba(52, 152, 219, 0.3);
}

/* Button */
.contact-form .btn {
    padding: 10px 20px;
    font-size: 1em;
    color: #fff;
    background-color: #3498db;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    transition: background-color 0.3s ease;
    text-align: center;
}

.contact-form .btn:hover {
    background-color: #2980b9;
}

/* Media Query */
@media (max-width: 768px) {
    .contact-container {
        padding: 20px;
    }

    .contact-container h1 {
        font-size: 1.5em;
    }

    .contact-container p {
        font-size: 1em;
    }

    .contact-form .btn {
        font-size: 0.9em;
    }

    .back-to-home {
        font-size: 0.8em;
        padding: 4px 8px;
    }
}
</style>
<div class="contact-container">
    <a href="./user_dashboard.php" class="back-to-home">Back to Home Page</a>
    <h1>Contact Us</h1>
    <p>If you have any questions, feel free to reach out using the form below.</p>

    <?php if ($messageSent): ?>
        <p class="success">Your message has been sent successfully!</p>
    <?php elseif ($error): ?>
        <p class="error"><?php echo $error; ?></p>
    <?php endif; ?>

    <form action="" method="POST" class="contact-form">
        <div class="form-group">
            <label for="name">Your Name</label>
            <input type="text" id="name" name="name" placeholder="Enter your name" required>
        </div>

        <div class="form-group">
            <label for="email">Your Email</label>
            <input type="email" id="email" name="email" placeholder="Enter your email" required>
        </div>

        <div class="form-group">
            <label for="message">Your Message</label>
            <textarea id="message" name="message" rows="5" placeholder="Type your message" required></textarea>
        </div>

        <button type="submit" class="btn">Send Message</button>
    </form>
</div>

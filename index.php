<?php
require './includes/database.php'; // Include the DB connection file
session_start();

// print_r($_SESSION);
// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['user_type'] === 'admin') {
        header("Location: admin/dashboard.php");
        exit;
    } elseif ($_SESSION['user_type'] === 'user') {
        header("Location: user/user_dashboard.php");
        exit;
    }
}

// If there's an error message in the session, retrieve it and clear it
$error_message = '';
// if (isset($_SESSION['error'])) {
//     $error_message = $_SESSION['error'];
//     unset($_SESSION['error']);
// }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Parking Management System - Login</title>
    <link rel="stylesheet" href="css/indexstyle.css">
    <script src="https://kit.fontawesome.com/64d58efce2.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
.input-field {
    max-width: 380px;
    width: 100%;
    background-color: #f0f0f0;
    margin: 10px 0;
    height: 55px;
    border-radius: 55px;
    display: grid;
    grid-template-columns: 15% 85%;
    padding: 0 0.4rem;
    position: relative;
    transition: all 0.3s ease;
}

.input-field i {
    text-align: center;
    line-height: 55px;
    color: #acacac;
    transition: 0.5s;
    font-size: 1.1rem;
}

.input-field input {
    background: none;
    outline: none;
    border: none;
    line-height: 1;
    font-weight: 600;
    font-size: 1.1rem;
    color: #333;
}

.input-field input::placeholder {
    color: #151010;
    font-weight: 500;
}

/* Updated error styles */
.input-field.error {
    background-color: #fff0f0;
    box-shadow: 0 0 0 2px #ff3333;
    border-radius: 55px;
}

.input-field.error i {
    color: #ff3333;
}

.error-tooltip {
    display: none;
    position: absolute;
    background-color: #ff3333;
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 55px;
    font-size: 0.8rem;
    bottom: calc(100% + 10px);
    left: 50%;
    transform: translateX(-50%);
    white-space: nowrap;
    z-index: 1000;
    box-shadow: 0 2px 10px rgba(255, 51, 51, 0.2);
}

/* Add a small arrow to the tooltip */
.error-tooltip::after {
    content: '';
    position: absolute;
    bottom: -5px;
    left: 50%;
    transform: translateX(-50%);
    width: 0;
    height: 0;
    border-left: 6px solid transparent;
    border-right: 6px solid transparent;
    border-top: 6px solid #ff3333;
}

.input-field.error:hover .error-tooltip {
    display: block;
}
.success-message {
    color: green;
    text-align: center;
    margin-bottom: 1rem;
    padding: 0.5rem;
    border-radius: 25px;
    background-color: #f0fff0;
}
</style>
</head>
<body>
    <?php
    $isRegisterFormActive = isset($_SESSION['field_errors']);
    ?>
    <div class="container <?php echo $isRegisterFormActive ? 'sign-up-mode' : ''; ?>">
        <div class="forms-container">
            <div class="signin-signup">

                

                <!-- User Login Form -->
                <form action="process_login.php" method="post" class="sign-in-form">
                    <h2 class="title">User Login</h2>
                <?php
                if (isset($_SESSION['register_success'])) {
                    echo "<p style='color: green; text-align: center;'>" . htmlspecialchars($_SESSION['register_success']) . "</p>";
                    unset($_SESSION['register_success']);
                }
                    if (isset($_SESSION['error'])) {
                    echo "<p style='color: red; text-align: center;'>" . $_SESSION['error'] . "</p>";
                    unset($_SESSION['error']);
                    }
                ?>
                    <div class="input-field">
                        <i class="fas fa-user"></i>
                        <input type="email" name="email" placeholder="Email" required>
                    </div>
                    <div class="input-field">
                        <i class="fas fa-lock"></i>
                        <input type="password" name="password" placeholder="Password" required>
                    </div>
                    <input type="submit" value="Login" class="btn solid">
                </form>

               <!-- Registration Form -->

<form action="process_registration.php" method="post" class="register-form">
    <h2 class="title">Register</h2>
    
    <?php if (isset($_SESSION['register_success'])): ?>
        <p class="success-message"><?php echo htmlspecialchars($_SESSION['register_success']); ?></p>
        <?php unset($_SESSION['register_success']); ?>
    <?php endif; ?>

    <?php
    $field_errors = isset($_SESSION['field_errors']) ? $_SESSION['field_errors'] : [];
    $form_data = isset($_SESSION['form_data']) ? $_SESSION['form_data'] : [];
    ?>
    
    <div class="input-field <?php echo isset($field_errors['name']) ? 'error' : ''; ?>">
        <i class="fas fa-user"></i>
        <input type="text" name="name" placeholder="Full Name" 
               value="<?php echo isset($form_data['name']) ? htmlspecialchars($form_data['name']) : ''; ?>" 
               required>
        <?php if (isset($field_errors['name'])): ?>
            <div class="error-tooltip"><?php echo htmlspecialchars($field_errors['name']); ?></div>
        <?php endif; ?>
    </div>
    
    <div class="input-field <?php echo isset($field_errors['phone']) ? 'error' : ''; ?>">
        <i class="fas fa-phone"></i>
        <input type="tel" name="phone" placeholder="Phone" 
               value="<?php echo isset($form_data['phone']) ? htmlspecialchars($form_data['phone']) : ''; ?>" 
               required>
        <?php if (isset($field_errors['phone'])): ?>
            <div class="error-tooltip"><?php echo htmlspecialchars($field_errors['phone']); ?></div>
        <?php endif; ?>
    </div>
    
    <div class="input-field <?php echo isset($field_errors['email']) ? 'error' : ''; ?>">
        <i class="fas fa-envelope"></i>
        <input type="email" name="email" placeholder="Email" 
               value="<?php echo isset($form_data['email']) ? htmlspecialchars($form_data['email']) : ''; ?>" 
               required>
        <?php if (isset($field_errors['email'])): ?>
            <div class="error-tooltip"><?php echo htmlspecialchars($field_errors['email']); ?></div>
        <?php endif; ?>
    </div>
    
    <div class="input-field <?php echo isset($field_errors['password']) ? 'error' : ''; ?>">
        <i class="fas fa-lock"></i>
        <input type="password" name="password" placeholder="Password" required>
        <?php if (isset($field_errors['password'])): ?>
            <div class="error-tooltip"><?php echo htmlspecialchars($field_errors['password']); ?></div>
        <?php endif; ?>
    </div>
    
    <div class="input-field <?php echo isset($field_errors['confirm_password']) ? 'error' : ''; ?>">
        <i class="fas fa-lock"></i>
        <input type="password" name="confirm_password" placeholder="Confirm Password" required>
        <?php if (isset($field_errors['confirm_password'])): ?>
            <div class="error-tooltip"><?php echo htmlspecialchars($field_errors['confirm_password']); ?></div>
        <?php endif; ?>
    </div>
    
    <input type="submit" value="Register" class="btn solid">
</form>

<?php
// Clear the session variables after displaying the form
unset($_SESSION['field_errors']);
unset($_SESSION['form_data']);
?>
            </div>
        </div>
        <div class="panels-container">
            <div class="panel left-panel">
                <div class="content">
                    <h3>New here?</h3>
                    <p>Sign up and start managing your parking spaces efficiently!</p>
                    <button class="btn transparent" id="sign-up-btn">Sign up</button>
                </div>
            </div>
            <div class="panel right-panel">
                <div class="content">
                    <h3>One of us?</h3>
                    <p>Login to access your account and manage your parking spaces.</p>
                    <button class="btn transparent" id="sign-in-btn">Sign in</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        const sign_in_btn = document.querySelector("#sign-in-btn");
        const sign_up_btn = document.querySelector("#sign-up-btn");
        const container = document.querySelector(".container");

        sign_up_btn.addEventListener("click", () => {
            container.classList.add("sign-up-mode");
        });

        sign_in_btn.addEventListener("click", () => {
            container.classList.remove("sign-up-mode");
        });

 // Registration form validation
register_form.addEventListener("submit", (e) => {
    const name = register_form.querySelector('input[name="name"]').value;
    const email = register_form.querySelector('input[name="email"]').value;
    const phone = register_form.querySelector('input[name="phone"]').value;
    const password = register_form.querySelector('input[name="password"]').value;
    const confirmPassword = register_form.querySelector('input[name="confirm_password"]').value;

    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    const phonePattern = /^(98|97)\d{8}$/;

    if (name.trim() === "") {
        e.preventDefault();
        alert("Please enter your full name!");
    } else if (!emailPattern.test(email)) {
        e.preventDefault();
        alert("Please enter a valid email address!");
    } else if (!phonePattern.test(phone)) {
        e.preventDefault();
        alert("Phone number must start with 98 or 97 and be 10 digits long!");
    } else if (password.length < 6) {
        e.preventDefault();
        alert("Password must be at least 6 characters long!");
    } else if (password !== confirmPassword) {
        e.preventDefault();
        alert("Passwords do not match!");
    }
});

// Login form validation
sign_in_form.addEventListener("submit", (e) => {
    const email = sign_in_form.querySelector('input[name="email"]').value;
    const password = sign_in_form.querySelector('input[name="password"]').value;

    if (email.trim() === "") {
        e.preventDefault();
        alert("Please enter your email!");
    } else if (password.trim() === "") {
        e.preventDefault();
        alert("Please enter your password!");
    }
});

</script>
</body>
</html>
<?php
// Start the session to access session variables
include('../includes/database.php');
include('adminheader.php');

// Initialize variables
$error = "";
$success = "";
$userDetails = null;
$admin_id = $_SESSION['user_id']; // Assuming the admin's user ID is stored in the session variable

// Handle delete request
if (isset($_POST['delete_user_id'])) {
    $delete_user_id = (int)$_POST['delete_user_id'];

    // First check if the target user is an admin
    $checkAdminQuery = "SELECT user_type FROM Users WHERE user_id = ?";
    $stmt = mysqli_prepare($connect, $checkAdminQuery);
    mysqli_stmt_bind_param($stmt, "i", $delete_user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $userToDelete = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    // Check conditions for deletion
    if ($delete_user_id === $admin_id) {
        $error = "You cannot delete your own account.";
    } elseif ($userToDelete && $userToDelete['user_type'] === 'admin') {
        $error = "Admin accounts cannot be deleted.";
    } else {
        // Proceed with deletion if it's not an admin account
        $deleteQuery = "DELETE FROM Users WHERE user_id = ?";
        $stmt = mysqli_prepare($connect, $deleteQuery);
        mysqli_stmt_bind_param($stmt, "i", $delete_user_id);

        if (mysqli_stmt_execute($stmt)) {
            $success = "User deleted successfully!";
        } else {
            $error = "Error deleting user: " . mysqli_error($connect);
        }
        mysqli_stmt_close($stmt);
    }
}

// If a user ID is passed to view details
if (isset($_POST['user_id'])) {
    $user_id = (int)$_POST['user_id'];

    // Fetch user details from the database
    $query = "SELECT * FROM Users WHERE user_id = ?";
    $stmt = mysqli_prepare($connect, $query);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($result)) {
        $userDetails = $row; // Store user details for display
    } else {
        $error = "User not found.";
    }
    mysqli_stmt_close($stmt);
}

// Fetch all users from the database for the user list
$query = "SELECT * FROM Users ORDER BY created_at DESC";
$result = mysqli_query($connect, $query);
?>

<div class="admin-container">
    <h1>Manage Users</h1>

    <!-- Show success or error messages -->
    <?php if ($error): ?>
        <p id="error-message" class="message error"><?php echo $error; ?></p>
    <?php elseif ($success): ?>
        <p id="success-message" class="message success"><?php echo $success; ?></p>
    <?php endif; ?>

    <!-- If a user is being viewed, show their details -->
    <?php if ($userDetails): ?>
        <h2>User Details</h2>
        <p><strong>User ID:</strong> <?php echo htmlspecialchars($userDetails['user_id']); ?></p>
        <p><strong>Name:</strong> <?php echo htmlspecialchars($userDetails['name']); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($userDetails['email']); ?></p>
        <p><strong>Phone:</strong> <?php echo htmlspecialchars($userDetails['phone']); ?></p>
        <p><strong>User Type:</strong> <?php echo htmlspecialchars($userDetails['user_type']); ?></p>
        <p><strong>Created At:</strong> <?php echo htmlspecialchars($userDetails['created_at']); ?></p>

        <!-- Button to go back to user list -->
        <a href="manage_users.php" class="back-to-users">Back to User List</a>
    <?php else: ?>
        <!-- Display the list of users in a table -->
        <table border="1" cellpadding="10" cellspacing="0" style="width: 100%; margin-top: 20px; border-collapse: collapse;">
            <thead>
                <tr>
                    <th>User ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>User Type</th>
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                while ($row = mysqli_fetch_assoc($result)) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['user_id']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['phone']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['user_type']) . "</td>";
                    echo "<td>" . $row['created_at'] . "</td>";
                    echo "<td>
                            <form action='manage_users.php' method='POST'>
                                <input type='hidden' name='user_id' value='" . $row['user_id'] . "'>
                                <input type='submit' value='View'>
                            </form>";
                    // Only show delete button if the user is not an admin
                    if ($row['user_type'] !== 'admin') {
                        echo "<form action='manage_users.php' method='POST' onsubmit='return confirm(\"Are you sure you want to delete this user?\")'>
                                <input type='hidden' name='delete_user_id' value='" . $row['user_id'] . "'>
                                <input type='submit' value='Delete'>
                            </form>";
                    }
                    echo "</td>";
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<script>
// Function to hide messages after a timeout
function hideMessage(id) {
    setTimeout(function() {
        document.getElementById(id).style.display = 'none';
    }, 5000); // Hide after 5 seconds
}

// Hide the success message after 5 seconds
<?php if ($success): ?>
    hideMessage('success-message');
<?php endif; ?>

// Hide the error message after 5 seconds
<?php if ($error): ?>
    hideMessage('error-message');
<?php endif; ?>
</script>

<style>
/* Style for success and error messages */
.message {
    padding: 10px;
    margin: 20px 0;
    border-radius: 5px;
    transition: opacity 0.5s ease-out;
    align: center;
}

/* Success message style */
.success {
    background-color: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

/* Error message style */
.error {
    background-color: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}
</style>
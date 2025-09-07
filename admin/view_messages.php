<?php
include('../includes/database.php');
include 'adminheader.php';

// Initialize variables
$messageDetails = null;
$error = "";

// Check if a message is selected for details (via POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['contact_id'])) {
    $contact_id = (int)$_POST['contact_id'];

    // Fetch the selected message from the database
    $query = "SELECT * FROM ContactMessages WHERE contact_id = ?";
    $stmt = mysqli_prepare($connect, $query);
    mysqli_stmt_bind_param($stmt, "i", $contact_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    // Check if the message exists
    if ($row = mysqli_fetch_assoc($result)) {
        $messageDetails = $row; // Store message details for display
    } else {
        $error = "Message not found.";
    }
    mysqli_stmt_close($stmt);
}

?>

<div class="admin-container">
    <?php if ($messageDetails): ?>
        <!-- Display the message details -->
        <h1>Message Details</h1>
        <p><strong>Name:</strong> <?php echo htmlspecialchars($messageDetails['name']); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($messageDetails['email']); ?></p>
        <p><strong>Message:</strong></p>
        <p><?php echo nl2br(htmlspecialchars($messageDetails['message'])); ?></p>
        <p><strong>Date Submitted:</strong> <?php echo $messageDetails['submitted_at']; ?></p>
        
        <!-- Button to go back to message list -->
        <a href="view_messages.php" class="back-to-messages">Back to Messages</a>
    <?php else: ?>
        <!-- Display list of messages -->
        <h1>View All Messages</h1>
        <?php if ($error): ?>
            <p class="error"><?php echo $error; ?></p>
        <?php endif; ?>

        <table border="1" cellpadding="10" cellspacing="0" style="width: 100%; margin-top: 20px; border-collapse: collapse;">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Message</th>
                    <th>Date Submitted</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Fetch all messages from the database
                $query = "SELECT * FROM ContactMessages ORDER BY submitted_at DESC";
                $result = mysqli_query($connect, $query);

                if (!$result) {
                    die("Error fetching messages: " . mysqli_error($connect));
                }

                while ($row = mysqli_fetch_assoc($result)) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                    echo "<td>" . htmlspecialchars(substr($row['message'], 0, 50)) . "...</td>"; // Show only a snippet of the message
                    echo "<td>" . $row['submitted_at'] . "</td>";
                    echo "<td>
                            <form action='' method='POST'>
                                <input type='hidden' name='contact_id' value='" . $row['contact_id'] . "' />
                                <button type='submit'>View Details</button>
                            </form>
                        </td>";
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
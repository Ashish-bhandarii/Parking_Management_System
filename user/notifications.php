<?php
include('../includes/database.php');
include('../includes/header.php');
?>
<?php
// Function to check if any reservations are late and generate notifications
function checkLateReservations($connect) {
    $current_time = date('Y-m-d H:i:s'); // Current time

    // Query to find all reservations where the end time has passed and status is still 'reserved'
    $sql = "SELECT r.reservation_id, r.user_id, r.end_time, u.name 
            FROM Reservations r 
            JOIN Users u ON r.user_id = u.user_id
            WHERE r.end_time < ? AND r.status = 'reserved'";

    $stmt = $connect->prepare($sql);
    $stmt->bind_param('s', $current_time);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        // Generate the notification message
        $message = "You're late for your parking reservation. Reservation ended at " . $row['end_time'];

        // Insert the notification into the Notifications table
        $insert_sql = "INSERT INTO Notifications (user_id, reservation_id, message) 
                       VALUES (?, ?, ?)";
        $insert_stmt = $connect->prepare($insert_sql);
        $insert_stmt->bind_param('iis', $row['user_id'], $row['reservation_id'], $message);
        $insert_stmt->execute();
    }
}

// Call the function to check for late reservations and generate notifications
checkLateReservations($connect);

// Fetch unread notifications count
$user_id = $_SESSION['user_id']; // Assuming the user ID is stored in session
$sql = "SELECT COUNT(*) AS unread_notifications 
        FROM Notifications 
        WHERE user_id = ? AND read_status = 0"; // Assuming there's a 'read_status' field
$stmt = $connect->prepare($sql);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$unread_notifications = $row['unread_notifications'];
?>
<!-- Display the Notifications Page (notifications.php) -->
<?php
// Fetch notifications for the logged-in user
$sql = "SELECT * FROM Notifications WHERE user_id = ? ORDER BY sent_at DESC";
$stmt = $connect->prepare($sql);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!-- Notification Card -->
<a href="notifications.php" class="notification-card">
    <div class="notification-card-header">
        <div class="notification-card-title" data-count="<?php echo $unread_notifications; ?>">
            Notifications
        </div>
        
    </div>
    <div class="notification-card-body">
        <p>Stay updated with your parking status.</p>
    </div>
</a>


<?php while ($row = $result->fetch_assoc()) : ?>
  <div class="notification">
    <table>
      <tr>
        <td><p><?php echo htmlspecialchars($row['message']); ?></p></td>
        <td><span><?php echo date('Y-m-d H:i:s', strtotime($row['sent_at'])); ?></span></td>
      </tr>
    </table>
  </div>
<?php endwhile; ?>
<?php
// Mark all notifications as read once the user has viewed them
$update_sql = "UPDATE Notifications SET read_status = 1 WHERE user_id = ?";
$update_stmt = $connect->prepare($update_sql);
$update_stmt->bind_param('i', $user_id);
$update_stmt->execute();
?>

<?php
include('../includes/database.php');
include('adminheader.php');

// Handle reservation actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $reservation_id = intval($_POST['reservation_id']);

    // Fetch reservation details
    $reservation_query = "SELECT area_id, slot_number FROM Reservations WHERE reservation_id = ?";
    $stmt = mysqli_prepare($connect, $reservation_query);
    mysqli_stmt_bind_param($stmt, "i", $reservation_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($result)) {
        $area_id = $row['area_id'];
        $slot_number = $row['slot_number'];

        switch ($action) {
            case 'approve':
                $status = 'Approved';
                updateReservationStatus($connect, $reservation_id, $status);
                break;
            case 'cancel':
                $status = 'Cancelled';
                updateReservationStatus($connect, $reservation_id, $status);
        
                // Send notification to the user
                $user_id_query = "SELECT user_id FROM Reservations WHERE reservation_id = ?";
                $stmt_user = mysqli_prepare($connect, $user_id_query);
                mysqli_stmt_bind_param($stmt_user, "i", $reservation_id);
                mysqli_stmt_execute($stmt_user);
                $result_user = mysqli_stmt_get_result($stmt_user);
                $user_row = mysqli_fetch_assoc($result_user);
                $user_id = $user_row['user_id'];
        
                $notification_message = "Your reservation (ID: $reservation_id) has been cancelled.";
                $insert_notification = "INSERT INTO Notifications (user_id, reservation_id, message) VALUES (?, ?, ?)";
                $stmt_notification = mysqli_prepare($connect, $insert_notification);
                mysqli_stmt_bind_param($stmt_notification, "iis", $user_id, $reservation_id, $notification_message);
                mysqli_stmt_execute($stmt_notification);
                break;
        
            case 'complete':
                $status = 'Completed';
                updateReservationStatus($connect, $reservation_id, $status);
        
                // Send notification to the user
                $user_id_query = "SELECT user_id FROM Reservations WHERE reservation_id = ?";
                $stmt_user = mysqli_prepare($connect, $user_id_query);
                mysqli_stmt_bind_param($stmt_user, "i", $reservation_id);
                mysqli_stmt_execute($stmt_user);
                $result_user = mysqli_stmt_get_result($stmt_user);
                $user_row = mysqli_fetch_assoc($result_user);
                $user_id = $user_row['user_id'];
        
                $notification_message = "Your reservation (ID: $reservation_id) has been completed.";
                $insert_notification = "INSERT INTO Notifications (user_id, reservation_id, message) VALUES (?, ?, ?)";
                $stmt_notification = mysqli_prepare($connect, $insert_notification);
                mysqli_stmt_bind_param($stmt_notification, "iis", $user_id, $reservation_id, $notification_message);
                mysqli_stmt_execute($stmt_notification);
                break;
        
            case 'delete':
                deleteReservation($connect, $reservation_id);
                break;
            case 'edit':
                $start_time = $_POST['start_time'];
                $end_time = $_POST['end_time'];
                editReservation($connect, $reservation_id, $start_time, $end_time);
                break;
            case 'decline':
                $status = 'Declined';
                updateReservationStatus($connect, $reservation_id, $status);
                // updateSlotStatus($connect, $area_id, $slot_number, 1); // or 0
                break;
            case 'toggle_vehicle_status':
                $new_status = $_POST['vehicle_status'];
                $update_vehicle = "UPDATE Reservations SET vehicle_status = ? WHERE reservation_id = ?";
                $stmt = mysqli_prepare($connect, $update_vehicle);
                mysqli_stmt_bind_param($stmt, "si", $new_status, $reservation_id);
                mysqli_stmt_execute($stmt);
                break;
        }
    }
}

function updateReservationStatus($connect, $reservation_id, $new_status) {
    mysqli_begin_transaction($connect);
    
    try {
        // Get current reservation details
        $get_details = "SELECT area_id, slot_number, status FROM Reservations WHERE reservation_id = ?";
        $stmt_details = mysqli_prepare($connect, $get_details);
        mysqli_stmt_bind_param($stmt_details, "i", $reservation_id);
        mysqli_stmt_execute($stmt_details);
        $result = mysqli_stmt_get_result($stmt_details);
        $reservation = mysqli_fetch_assoc($result);
        
        if (!$reservation) {
            throw new Exception("Reservation not found");
        }
        
        $current_status = $reservation['status'];
        $area_id = $reservation['area_id'];
        $slot_number = $reservation['slot_number'];
        
        // Update reservation status
        $update_status = "UPDATE Reservations SET status = ? WHERE reservation_id = ?";
        $stmt_status = mysqli_prepare($connect, $update_status);
        mysqli_stmt_bind_param($stmt_status, "si", $new_status, $reservation_id);
        mysqli_stmt_execute($stmt_status);
        
        // Handle slot and area updates based on status change
        if ($new_status === 'Declined' || $new_status === 'Cancelled' || $new_status === 'Completed') {
            // Free up the slot
            $update_slot = "UPDATE slots SET is_reserved = 0 
                           WHERE parking_area_id = ? AND slot_id = ?";
            $stmt_slot = mysqli_prepare($connect, $update_slot);
            mysqli_stmt_bind_param($stmt_slot, "ii", $area_id, $slot_number);
            mysqli_stmt_execute($stmt_slot);
            
            // Update area counts
            $update_area = "UPDATE parking_areas 
                           SET available_slots = available_slots + 1,
                               reserved_slots = GREATEST(reserved_slots - 1, 0)
                           WHERE area_id = ?";
            $stmt_area = mysqli_prepare($connect, $update_area);
            mysqli_stmt_bind_param($stmt_area, "i", $area_id);
            mysqli_stmt_execute($stmt_area);
        }
        
        mysqli_commit($connect);
        return true;
        
    } catch (Exception $e) {
        mysqli_rollback($connect);
        throw $e;
    }
}

function updateSlotStatus($connect, $area_id, $slot_number, $is_reserved) {
    $query = "UPDATE slots SET is_reserved = ? WHERE parking_area_id = ? AND slot_name = ?";
    $stmt = mysqli_prepare($connect, $query);
    mysqli_stmt_bind_param($stmt, "iis", $is_reserved, $area_id, $slot_number);
    mysqli_stmt_execute($stmt);

    // Update parking_areas table
    if ($is_reserved == 1) {
        $update_area = "UPDATE parking_areas SET reserved_slots = reserved_slots, available_slots = available_slots WHERE area_id = ?";
    } else {
        $update_area = "UPDATE parking_areas SET reserved_slots = GREATEST(reserved_slots, 0), available_slots = available_slots WHERE area_id = ?";
    }

    $stmt_update_area = mysqli_prepare($connect, $update_area);
    mysqli_stmt_bind_param($stmt_update_area, "i", $area_id);
    mysqli_stmt_execute($stmt_update_area);
}

function deleteReservation($connect, $reservation_id) {
    // Get reservation details before deleting
    $query = "SELECT area_id, slot_number, status FROM Reservations WHERE reservation_id = ?";
    $stmt = mysqli_prepare($connect, $query);
    mysqli_stmt_bind_param($stmt, "i", $reservation_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);

    // Delete the reservation
    $delete_query = "DELETE FROM Reservations WHERE reservation_id = ?";
    $stmt_delete = mysqli_prepare($connect, $delete_query);
    mysqli_stmt_bind_param($stmt_delete, "i", $reservation_id);
    mysqli_stmt_execute($stmt_delete);

    // Update slot status and parking area if the reservation was approved
    if ($row['status'] == 'Approved') {
        updateSlotStatus($connect, $row['area_id'], $row['slot_number'], 0);
    }
}

function editReservation($connect, $reservation_id, $start_time, $end_time){
    $query = "UPDATE Reservations SET start_time = ?, end_time = ? WHERE reservation_id = ?";
    $stmt = mysqli_prepare($connect, $query);
    mysqli_stmt_bind_param($stmt, "ssi", $start_time, $end_time, $reservation_id);
    mysqli_stmt_execute($stmt);
}

// Fetch reservations for display
$reservations_query = "
   SELECT
    r.reservation_id, 
    u.name AS user_name, 
    MAX(v.vehicle_number) AS vehicle_number,
    pa.area_name, 
    r.slot_number, 
    r.start_time, 
    r.end_time, 
    r.status,
    r.created_at,
    s.slot_name,
    r.vehicle_status
FROM Reservations r
JOIN Users u ON r.user_id = u.user_id
JOIN slots s ON r.slot_number = s.slot_id
JOIN parking_areas pa ON r.area_id = pa.area_id
LEFT JOIN VehicleData v ON r.user_id = v.user_id AND r.area_id = v.area_id
GROUP BY r.reservation_id, u.name, pa.area_name, r.slot_number, r.start_time, r.end_time, r.status, s.slot_name, r.vehicle_status
ORDER BY r.start_time DESC;
";
$reservations = mysqli_query($connect, $reservations_query);

?>

    <h1>Manage Reservations</h1>
    <?php if (isset($_GET['msg'])): ?>
        <div class="success"><?= htmlspecialchars($_GET['msg']) ?></div>
    <?php elseif (isset($_GET['error'])): ?>
        <div class="error"><?= htmlspecialchars($_GET['error']) ?></div>
    <?php endif; ?>

<table>
    <thead>
        <tr>
            <th>Reservation ID</th>
            <th>User</th>
            <th>Vehicle</th>
            <th>Parking Area</th>
            <th>Slot</th>
            <th>Start Time</th>
            <th>End Time</th>
            <th>Status</th>
            <th>Vehicle Status</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($row = mysqli_fetch_assoc($reservations)): ?>
            <tr>
                <td><?= $row['reservation_id'] ?></td>
                <td><?= $row['user_name'] ?></td>
                <td><?= $row['vehicle_number'] ?></td>
                <td><?= $row['area_name'] ?></td>
                <td><?= $row['slot_name'] ?></td>
                <td><?= $row['start_time'] ?></td>
                <td><?= $row['end_time'] ?></td>
                <td><?= $row['status'] ?></td>
                <td>
                    <?php if ($row['status'] == 'Approved'): ?>
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="reservation_id" value="<?= $row['reservation_id'] ?>">
                            <input type="hidden" name="action" value="toggle_vehicle_status">
                            <?php if ($row['vehicle_status'] == 'IN'): ?>
                                <button type="submit" name="vehicle_status" value="OUT" class="btn btn-warning">Vehicle IN</button>
                            <?php else: ?>
                                <button type="submit" name="vehicle_status" value="IN" class="btn btn-info">Vehicle OUT</button>
                            <?php endif; ?>
                        </form>
                    <?php else: ?>
                        <span class="badge badge-secondary">N/A</span>
                    <?php endif; ?>
                </td>
                <td>
                    <form method="POST">
                        <input type="hidden" name="reservation_id" value="<?= $row['reservation_id'] ?>">
                        <?php if ($row['status'] == 'Pending'): ?>
                            <button type="submit" name="action" value="approve">Approve</button>
                            <button type="submit" name="action" value="decline">Decline</button>
                        <?php endif; ?>
            <?php if ($row['status'] == 'Approved'): ?>

                <!-- "Completed" button -->
                <form method="POST" onsubmit="return confirm('Are you sure you want to mark this reservation as completed?');">
                    <input type="hidden" name="reservation_id" value="<?= $row['reservation_id'] ?>">
                    <input type="hidden" name="action" value="complete">
                    <button type="submit" class="btn btn-success">Completed</button>
                </form>
            <?php endif; ?>

            <?php if (in_array($row['status'], ['Cancelled', 'Declined', 'Completed'])): ?>
        <button type="submit" name="action" value="delete" onclick="return confirm('Are you sure you want to delete this?')">Delete</button>

<?php endif; ?>
                </form>
                </td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<style>
.btn {
    padding: 5px 10px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 14px;
}

.btn-warning {
    background-color: #ffc107;
    color: #000;
}

.btn-info {
    background-color: #17a2b8;
    color: #fff;
}

.badge {
    padding: 5px 10px;
    border-radius: 4px;
    font-size: 12px;
}

.badge-secondary {
    background-color: #6c757d;
    color: #fff;
}
</style>
</body>
</html>
<!-- 

// // 1. Automatic cancellation of reservations older than 1 hour
// function checkAndCancelOldReservations($connect) {
//     // Fetch all pending reservations to check for automatic cancellation
//     $reservations_query = "
//         SELECT 
//             reservation_id, 
//             user_id, 
//             status, 
//             created_at, 
//             area_id, 
//             slot_number
//         FROM Reservations
//         WHERE status = 'Pending';
//     ";
//     $reservations_result = mysqli_query($connect, $reservations_query);

//     // Loop through each pending reservation
//     while ($reservation = mysqli_fetch_assoc($reservations_result)) {
//         // Get reservation data
//         $reservation_id = $reservation['reservation_id'];
//         $user_id = $reservation['user_id'];
//         $created_at = strtotime($reservation['created_at']); // Convert to timestamp
//         $current_time = time(); // Get current timestamp

//         // Check if reservation is older than 1 hour
//         if (($current_time - $created_at) > 300) { // 3600 seconds = 1 hour
//             // Automatically cancel the reservation
//             $new_status = 'Cancelled';
            
//             // Update reservation status to 'Cancelled'
//             $update_query = "UPDATE Reservations SET status = ? WHERE reservation_id = ?";
//             $stmt_update = mysqli_prepare($connect, $update_query);
//             mysqli_stmt_bind_param($stmt_update, "si", $new_status, $reservation_id);
//             mysqli_stmt_execute($stmt_update);

//             // Free the slot (optional)
//             $slot_update_query = "UPDATE slots SET is_reserved = 0 WHERE area_id = ? AND slot_number = ?";
//             $stmt_slot_update = mysqli_prepare($connect, $slot_update_query);
//             mysqli_stmt_bind_param($stmt_slot_update, "ii", $reservation['area_id'], $reservation['slot_number']);
//             mysqli_stmt_execute($stmt_slot_update);

//             // Send notification to user about the cancellation
//             $notification_message = "Your reservation (ID: $reservation_id) has been automatically cancelled due to inactivity.";
//             $insert_notification_query = "INSERT INTO Notifications (user_id, reservation_id, message) VALUES (?, ?, ?)";
//             $stmt_notification = mysqli_prepare($connect, $insert_notification_query);
//             mysqli_stmt_bind_param($stmt_notification, "iis", $user_id, $reservation_id, $notification_message);
//             mysqli_stmt_execute($stmt_notification);
//         }
//     }
// }

// // Call the function to check for automatic cancellation of old reservations
// checkAndCancelOldReservations($connect); -->

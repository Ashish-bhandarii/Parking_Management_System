<?php
include('../includes/database.php');
include('../includes/header.php');

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Check if reservation_id is provided
if (isset($_GET['reservation_id'])) {
    $reservation_id = $_GET['reservation_id'];

    // Fetch the reservation and vehicle details from the database
    $query = "SELECT r.reservation_id, r.slot_number, r.start_time, r.end_time, r.area_id, 
              p.area_name, p.type_id, v.vehicle_number, v.vehicle_type, v.vehicle_id
              FROM Reservations r 
              JOIN parking_areas p ON r.area_id = p.area_id 
              JOIN VehicleData v ON r.vehicle_id = v.vehicle_id
              WHERE r.user_id = ? AND r.reservation_id = ? AND r.status = 'Pending'";
    $stmt = $connect->prepare($query);
    $stmt->bind_param('ii', $user_id, $reservation_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        $_SESSION['error'] = "No editable reservation found.";
        header("Location: reservation.php");
        exit;
    }

    $reservation = $result->fetch_assoc();
} else {
    $_SESSION['error'] = "Invalid reservation.";
    header("Location: reservation.php");
    exit;
}

// Handle form submission for editing the reservation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_reservation'])) {
    $area_id = $_POST['area_id'];
    $slot_id = $_POST['slot_id'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    $vehicle_type = $_POST['vehicle_type'];
    $vehicle_number = $_POST['vehicle_number'];

    // Check slot availability
    $check_slot_query = "SELECT COUNT(*) as slot_count FROM Reservations 
                         WHERE area_id = ? AND slot_number = ? AND reservation_id != ? 
                         AND status IN ('Pending', 'Approved') 
                         AND ((start_time <= ? AND end_time >= ?) 
                         OR (start_time <= ? AND end_time >= ?) 
                         OR (start_time >= ? AND end_time <= ?))";
    $stmt = $connect->prepare($check_slot_query);
    $stmt->bind_param('iiissssss', $area_id, $slot_id, $reservation_id, $start_time, $start_time, $end_time, $end_time, $start_time, $end_time);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if ($row['slot_count'] > 0) {
        $_SESSION['error'] = "The selected slot is not available for the chosen time period. Please choose another slot or time.";
    } else {
        mysqli_begin_transaction($connect);
        try {
            // Update the reservation details
            $update_query = "UPDATE Reservations 
                           SET area_id = ?, slot_number = ?, start_time = ?, end_time = ? 
                           WHERE reservation_id = ? AND user_id = ? AND status = 'Pending'";
            $stmt_update = $connect->prepare($update_query);
            $stmt_update->bind_param('iissii', $area_id, $slot_id, $start_time, $end_time, $reservation_id, $user_id);
            $stmt_update->execute();

            // Update vehicle details
            $update_vehicle = "UPDATE VehicleData 
                             SET vehicle_number = ?, vehicle_type = ?, area_id = ?, entry_time = ? 
                             WHERE vehicle_id = ?";
            $stmt_vehicle = $connect->prepare($update_vehicle);
            $stmt_vehicle->bind_param('ssisi', $vehicle_number, $vehicle_type, $area_id, $start_time, $reservation['vehicle_id']);
            $stmt_vehicle->execute();

            mysqli_commit($connect);
            $_SESSION['message'] = "Reservation updated successfully!";
            header("Location: reservation.php");
            exit;
        } catch (Exception $e) {
            mysqli_rollback($connect);
            $_SESSION['error'] = "Failed to update reservation: " . $e->getMessage();
        }
    }
}

// Fetch all parking areas for the select dropdown
$query_areas = "SELECT area_id, area_name, type_id FROM parking_areas";
$result_areas = $connect->query($query_areas);

// Fetch vehicle categories for the current area type
$query_vehicle_types = "SELECT DISTINCT category_name 
                       FROM vehicle_categories 
                       WHERE type_id = (SELECT type_id FROM parking_areas WHERE area_id = ?)";
$stmt_vehicle_types = $connect->prepare($query_vehicle_types);
$stmt_vehicle_types->bind_param('i', $reservation['area_id']);
$stmt_vehicle_types->execute();
$result_vehicle_types = $stmt_vehicle_types->get_result();

// Fetch available slots for the selected area
$query_slots = "SELECT s.slot_id, s.slot_name 
                FROM slots s
                LEFT JOIN (
                    SELECT slot_number, area_id
                    FROM Reservations
                    WHERE status IN ('Pending', 'Approved')
                      AND reservation_id != ?
                      AND start_time <= ? AND end_time >= ?
                ) r ON s.slot_id = r.slot_number AND r.area_id = s.parking_area_id
                WHERE s.parking_area_id = ? AND (r.slot_number IS NULL OR s.slot_id = ?)";
$stmt_slots = $connect->prepare($query_slots);
$stmt_slots->bind_param('issii', $reservation_id, $reservation['start_time'], $reservation['end_time'], $reservation['area_id'], $reservation['slot_number']);
$stmt_slots->execute();
$result_slots = $stmt_slots->get_result();
?>

<div class="container">
    <div class="reservation-form">
        <h3>Edit Reservation</h3>

        <?php if (isset($_SESSION['message'])): ?>
            <div class="message success"><?= htmlspecialchars($_SESSION['message']); ?></div>
            <?php unset($_SESSION['message']); ?>
        <?php elseif (isset($_SESSION['error'])): ?>
            <div class="message error"><?= htmlspecialchars($_SESSION['error']); ?></div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <form method="POST" id="reservationForm">
            <!-- Parking Area and Slot Row -->
            <div class="form-row">
                <div class="form-group">
                    <label for="area_id">Select Parking Area:</label>
                    <select name="area_id" id="area_id" required>
                        <?php while ($row = mysqli_fetch_assoc($result_areas)): ?>
                            <option value="<?= $row['area_id'] ?>" 
                                    data-type="<?= $row['type_id'] ?>"
                                    <?= ($row['area_id'] == $reservation['area_id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($row['area_name']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="slot_id">Select Slot:</label>
                    <select name="slot_id" id="slot_id" required>
                        <?php while ($row = $result_slots->fetch_assoc()): ?>
                            <option value="<?= $row['slot_id'] ?>" 
                                    <?= ($row['slot_id'] == $reservation['slot_number']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($row['slot_name']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>

            <!-- Vehicle Type and Number Row -->
            <div class="form-row">
                <div class="form-group">
                    <label for="vehicle_type">Select Vehicle Type:</label>
                    <select name="vehicle_type" id="vehicle_type" required>
                        <?php while ($row = mysqli_fetch_assoc($result_vehicle_types)): ?>
                            <option value="<?= htmlspecialchars($row['category_name']) ?>" 
                                    <?= ($reservation['vehicle_type'] == $row['category_name']) ? 'selected' : '' ?>>
                                <?= ucfirst(htmlspecialchars($row['category_name'])) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="vehicle_number">Vehicle Number:</label>
                    <input type="text" name="vehicle_number" id="vehicle_number" required 
                           value="<?= htmlspecialchars($reservation['vehicle_number']) ?>">
                </div>
            </div>

            <!-- Time Selection Row -->
            <div class="form-row">
                <div class="form-group">
                    <label for="start_time">Start Time:</label>
                    <input type="datetime-local" name="start_time" id="start_time" 
                           value="<?= date('Y-m-d\TH:i', strtotime($reservation['start_time'])) ?>" required>
                </div>
                <div class="form-group">
                    <label for="end_time">End Time:</label>
                    <input type="datetime-local" name="end_time" id="end_time" 
                           value="<?= date('Y-m-d\TH:i', strtotime($reservation['end_time'])) ?>" required>
                </div>
            </div>

            <div class="btn-container">
                <button type="submit" name="edit_reservation" class="btn btn-primary">Save Changes</button>
                <a href="reservation.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    $('#area_id').change(function() {
        var area_id = $(this).val();
        var type_id = $(this).find(':selected').data('type');
        var start_time = $('#start_time').val();
        var end_time = $('#end_time').val();
        
        if (area_id) {
            // Get available slots
            $.ajax({
                url: 'get_available_slots.php',
                type: 'POST',
                data: {
                    area_id: area_id,
                    start_time: start_time,
                    end_time: end_time,
                    reservation_id: '<?= $reservation_id ?>'
                },
                dataType: 'json',
                success: function(data) {
                    var options = '';
                    $.each(data, function(index, slot) {
                        options += '<option value="' + slot.slot_id + '">' + slot.slot_name + '</option>';
                    });
                    $('#slot_id').html(options);
                }
            });

            // Get vehicle categories for the selected area type
            $.ajax({
                url: 'get_vehicle_categories.php',
                type: 'POST',
                data: {type_id: type_id},
                dataType: 'json',
                success: function(data) {
                    var options = '';
                    $.each(data, function(index, category) {
                        options += '<option value="' + category.category_name + '">' + 
                                  category.category_name + '</option>';
                    });
                    $('#vehicle_type').html(options);
                }
            });
        }
    });

    $('#start_time, #end_time').change(function() {
        $('#area_id').trigger('change');
    });

    // Form validation
    $('#reservationForm').submit(function(event) {
        const startTime = new Date($('#start_time').val());
        const endTime = new Date($('#end_time').val());
        const currentTime = new Date();
        const vehicleNumber = $('#vehicle_number').val();
        const vehicleNumberPattern = /^[A-Za-z]{2} \d{1,2} [A-Za-z]{2} \d{1,4}$/;

        if (startTime <= currentTime) {
            alert("Start time must be a future date and time.");
            event.preventDefault();
            return;
        }

        if (endTime <= startTime) {
            alert("End time must be after the start time.");
            event.preventDefault();
            return;
        }

        const duration = (endTime - startTime) / (1000 * 60);
        if (duration < 30) {
            alert("The duration between start time and end time must be at least 30 minutes.");
            event.preventDefault();
            return;
        }

        if (!vehicleNumberPattern.test(vehicleNumber)) {
            alert("Please enter a valid vehicle number (e.g., BA 1 PA 1234).");
            event.preventDefault();
            return;
        }
    });
});
</script>
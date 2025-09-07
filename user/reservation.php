<?php
include('../includes/database.php');
include('../includes/header.php');

$user_id = $_SESSION['user_id'];

// Add this function at the beginning of your file, after the includes
function updateParkingSlots($connect, $area_id, $slot_id, $action) {
    mysqli_begin_transaction($connect);
    
    try {
        if ($action === 'reserve') {
            // Update parking areas table
            $update_area = "UPDATE parking_areas 
                           SET available_slots = GREATEST(available_slots - 1, 0),
                               reserved_slots = reserved_slots + 1 
                           WHERE area_id = ?";
            $stmt_area = mysqli_prepare($connect, $update_area);
            mysqli_stmt_bind_param($stmt_area, "i", $area_id);
            mysqli_stmt_execute($stmt_area);
            
            // Update slots table
            $update_slot = "UPDATE slots 
                           SET is_reserved = 1 
                           WHERE parking_area_id = ? AND slot_id = ?";
            $stmt_slot = mysqli_prepare($connect, $update_slot);
            mysqli_stmt_bind_param($stmt_slot, "ii", $area_id, $slot_id);
            mysqli_stmt_execute($stmt_slot);
            
        } elseif ($action === 'cancel') {
            // Update parking areas table
            $update_area = "UPDATE parking_areas 
                           SET available_slots = available_slots + 1,
                               reserved_slots = GREATEST(reserved_slots - 1, 0) 
                           WHERE area_id = ?";
            $stmt_area = mysqli_prepare($connect, $update_area);
            mysqli_stmt_bind_param($stmt_area, "i", $area_id);
            mysqli_stmt_execute($stmt_area);
            
            // Update slots table
            $update_slot = "UPDATE slots 
                           SET is_reserved = 0 
                           WHERE parking_area_id = ? AND slot_id = ?";
            $stmt_slot = mysqli_prepare($connect, $update_slot);
            mysqli_stmt_bind_param($stmt_slot, "ii", $area_id, $slot_id);
            mysqli_stmt_execute($stmt_slot);
        }
        
        mysqli_commit($connect);
        return true;
    } catch (Exception $e) {
        mysqli_rollback($connect);
        throw new Exception("Error updating parking slots: " . $e->getMessage());
    }
}

// Fetch parking areas with available slots
$query_areas = "SELECT area_id, area_name, type_id FROM parking_areas WHERE available_slots > 0";
$result_areas = mysqli_query($connect, $query_areas);

// Replace your existing reservation submission code with this:
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reserve'])) {
    $area_id = mysqli_real_escape_string($connect, $_POST['area_id']);
    $vehicle_type = mysqli_real_escape_string($connect, $_POST['vehicle_type']);
    $slot_id = mysqli_real_escape_string($connect, $_POST['slot_id']);
    $start_time = mysqli_real_escape_string($connect, $_POST['start_time']);
    $end_time = mysqli_real_escape_string($connect, $_POST['end_time']);
    $vehicle_number = mysqli_real_escape_string($connect, $_POST['vehicle_number']);

    // Determine if the selected vehicle is a 2-wheeler
    $two_wheeler_types = ['Motorcycle', 'Scooter', 'Moped', 'Sport Bike'];
    $is_two_wheeler = in_array($vehicle_type, $two_wheeler_types);

    // Get area type
    $area_type_query = "SELECT type_id FROM parking_areas WHERE area_id = ?";
    $stmt = mysqli_prepare($connect, $area_type_query);
    mysqli_stmt_bind_param($stmt, 'i', $area_id);
    mysqli_stmt_execute($stmt);
    $area_result = mysqli_stmt_get_result($stmt);
    $area_type = mysqli_fetch_assoc($area_result)['type_id'];

    // Check slot availability
    $check_slot_query = "SELECT r.start_time, r.end_time, r.vehicle_id, v.vehicle_type, s.small_vehicles_count
                        FROM slots s
                        LEFT JOIN Reservations r ON r.slot_number = s.slot_id
                        LEFT JOIN VehicleData v ON r.vehicle_id = v.vehicle_id
                        WHERE s.slot_id = ? 
                        AND r.status IN ('Pending', 'Approved')
                        AND ((r.start_time <= ? AND r.end_time >= ?) 
                            OR (r.start_time <= ? AND r.end_time >= ?) 
                            OR (r.start_time >= ? AND r.end_time <= ?))";

    $stmt = mysqli_prepare($connect, $check_slot_query);
    mysqli_stmt_bind_param($stmt, 'issssss', $slot_id, $start_time, $start_time, $end_time, $end_time, $start_time, $end_time);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    // Check if the slot is available based on vehicle type
    if ($area_type == 2) { // 4-wheeler area
        $existing_reservations = mysqli_fetch_all($result, MYSQLI_ASSOC);
        
        if (!$is_two_wheeler && count($existing_reservations) > 0) {
            // 4-wheeler trying to park - slot must be completely empty
            $_SESSION['error'] = "This slot is already reserved for the selected time period.";
            header("Location: ./reservation.php");
            exit;
        } else if ($is_two_wheeler) {
            // 2-wheeler trying to park - check if there's space
            $two_wheeler_count = 0;
            $has_four_wheeler = false;
            
            foreach ($existing_reservations as $reservation) {
                if (in_array($reservation['vehicle_type'], $two_wheeler_types)) {
                    $two_wheeler_count++;
                } else {
                    $has_four_wheeler = true;
                }
            }

            if ($has_four_wheeler) {
                $_SESSION['error'] = "This slot is reserved for a four-wheeler.";
                header("Location: ./reservation.php");
                exit;
            }

            if ($two_wheeler_count >= 3) {
                $_SESSION['error'] = "This slot has reached its maximum capacity for two-wheelers.";
                header("Location: ./reservation.php");
                exit;
            }
        }
    } else { // 2-wheeler area
        if (!$is_two_wheeler) {
            $_SESSION['error'] = "Only two-wheelers are allowed in this area.";
            header("Location: ./reservation.php");
            exit;
        }
        
        if (mysqli_num_rows($result) > 0) {
            $_SESSION['error'] = "This slot is already reserved for the selected time period.";
            header("Location: ./reservation.php");
            exit;
        }
    }

    // Continue with the reservation process if all checks pass
    mysqli_begin_transaction($connect);
    
    try {
        // Insert vehicle data
        $insert_vehicle_query = "INSERT INTO VehicleData (user_id, vehicle_number, vehicle_type, area_id, entry_time) 
                                VALUES (?, ?, ?, ?, ?)";
        $stmt_vehicle = mysqli_prepare($connect, $insert_vehicle_query);
        mysqli_stmt_bind_param($stmt_vehicle, 'issss', $user_id, $vehicle_number, $vehicle_type, $area_id, $start_time);
        mysqli_stmt_execute($stmt_vehicle);
        $vehicle_id = mysqli_insert_id($connect);

        // Insert reservation
        $insert_query = "INSERT INTO Reservations (user_id, area_id, slot_number, start_time, end_time, status, vehicle_id) 
                        VALUES (?, ?, ?, ?, ?, 'Pending', ?)";
        $stmt_reservation = mysqli_prepare($connect, $insert_query);
        mysqli_stmt_bind_param($stmt_reservation, 'iiissi', $user_id, $area_id, $slot_id, $start_time, $end_time, $vehicle_id);
        mysqli_stmt_execute($stmt_reservation);

        // Update small_vehicles_count if it's a 2-wheeler in 4-wheeler area
        if ($area_type == 2 && $is_two_wheeler) {
            $update_slot = "UPDATE slots SET small_vehicles_count = small_vehicles_count + 1 WHERE slot_id = ?";
            $stmt_slot = mysqli_prepare($connect, $update_slot);
            mysqli_stmt_bind_param($stmt_slot, 'i', $slot_id);
            mysqli_stmt_execute($stmt_slot);
        }

        mysqli_commit($connect);
        $_SESSION['message'] = "Reservation request submitted successfully! Waiting for approval.";
        
    } catch (Exception $e) {
        mysqli_rollback($connect);
        $_SESSION['error'] = "An error occurred while processing your reservation: " . $e->getMessage();
    }
    
    header("Location: ./reservation.php");
    exit;
}

// Update your cancel reservation code
if (isset($_GET['action']) && $_GET['action'] === 'cancel' && isset($_GET['reservation_id'])) {
    $reservation_id = mysqli_real_escape_string($connect, $_GET['reservation_id']);
    
    mysqli_begin_transaction($connect);
    
    try {
        // Get reservation details before cancelling
        $get_details = "SELECT area_id, slot_number FROM Reservations WHERE reservation_id = ? AND status = 'Pending'";
        $stmt_details = mysqli_prepare($connect, $get_details);
        mysqli_stmt_bind_param($stmt_details, 'i', $reservation_id);
        mysqli_stmt_execute($stmt_details);
        $result = mysqli_stmt_get_result($stmt_details);
        
        if ($reservation = mysqli_fetch_assoc($result)) {
            // Update reservation status
            $cancel_query = "UPDATE Reservations SET status = 'Cancelled' WHERE reservation_id = ?";
            $stmt_cancel = mysqli_prepare($connect, $cancel_query);
            mysqli_stmt_bind_param($stmt_cancel, 'i', $reservation_id);
            mysqli_stmt_execute($stmt_cancel);
            
            // Update parking slots
            updateParkingSlots($connect, $reservation['area_id'], $reservation['slot_number'], 'cancel');
            
            mysqli_commit($connect);
            $_SESSION['message'] = "Reservation cancelled successfully.";
        } else {
            throw new Exception("Invalid reservation or already processed.");
        }
    } catch (Exception $e) {
        mysqli_rollback($connect);
        $_SESSION['error'] = $e->getMessage();
    }
    
    header("Location: ./reservation.php");
    exit;
}


// Fetch active reservations
$active_query = "SELECT r.reservation_id,s.slot_name, r.slot_number, p.area_name, r.start_time, r.end_time, r.status
                 FROM Reservations r 
                 JOIN parking_areas p ON r.area_id = p.area_id 
                 JOIN slots s on r.slot_number = s.slot_id
                 WHERE r.user_id = ? AND r.status IN ('Pending', 'Approved')";
$stmt_active = mysqli_prepare($connect, $active_query);
mysqli_stmt_bind_param($stmt_active, 'i', $user_id);
mysqli_stmt_execute($stmt_active);
$active_reservations = mysqli_stmt_get_result($stmt_active);

// Fetch reservation history
$history_query = "SELECT r.slot_number,s.slot_name, p.area_name, r.start_time, r.end_time, r.status 
                  FROM Reservations r 
                  JOIN parking_areas p ON r.area_id = p.area_id 
                  JOIN slots s on r.slot_number = s.slot_id
                  WHERE r.user_id = ? AND r.status NOT IN ('Pending', 'Approved')";

$stmt_history = mysqli_prepare($connect, $history_query);
mysqli_stmt_bind_param($stmt_history, 'i', $user_id);
mysqli_stmt_execute($stmt_history);
$reservation_history = mysqli_stmt_get_result($stmt_history);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Parking Reservation</title>
    <link rel="stylesheet" href="../css/style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
<div class="container">
        <div class="username-container">
            <h1>Hello, <?= htmlspecialchars($_SESSION['user_name']) ?>!</h1>
        </div>

        <div class="message-container">
            <?php if (isset($_SESSION['message'])): ?>
                <div class="message success"><?= htmlspecialchars($_SESSION['message']); ?></div>
                <?php unset($_SESSION['message']); ?>
            <?php elseif (isset($_SESSION['error'])): ?>
                <div class="message error"><?= htmlspecialchars($_SESSION['error']); ?></div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>
        </div>

        <div class="reservation-form">
            <h3>Make a Reservation</h3>
            <form method="POST" id="reservationForm">
                <!-- Parking Area and Slot Row -->
                <div class="form-row">
    <div class="form-group">
        <label for="area_id">Select Parking Area:</label>
        <select name="area_id" id="area_id" required>
            <option value="" disabled selected>Select an area</option>
            <?php while ($row = mysqli_fetch_assoc($result_areas)): ?>
                <option value="<?= $row['area_id'] ?>" data-type="<?= $row['type_id'] ?>">
                    <?= htmlspecialchars($row['area_name']) ?>
                </option>
            <?php endwhile; ?>
        </select>
    </div>
    <div class="form-group">
        <label for="slot_id">Select Slot:</label>
        <select name="slot_id" id="slot_id" required>
            <option value="" disabled selected>Select a slot</option>
        </select>
    </div>
</div>

<div class="form-row">
    <div class="form-group">
        <label for="vehicle_type">Select Vehicle Category:</label>
        <select name="vehicle_type" id="vehicle_type" required disabled>
            <option value="" disabled selected>Select parking area first</option>
        </select>
    </div>
    <div class="form-group">
        <label for="vehicle_number">Vehicle Number:</label>
        <input type="text" name="vehicle_number" id="vehicle_number" required>
    </div>
</div>

                <!-- Time Selection Row -->
                <div class="form-row">
                    <div class="form-group">
                        <label for="start_time">Start Time:</label>
                        <input type="datetime-local" name="start_time" id="start_time" required>
                    </div>
                    <div class="form-group">
                        <label for="end_time">End Time:</label>
                        <input type="datetime-local" name="end_time" id="end_time" required>
                    </div>
                </div>
                <div class="btn-container">
                    <button type="submit" name="reserve" class="btn btn-primary">Reserve Now</button>
                </div>
            </form>
        </div>
    </div>
        <div class="reservation-section">
            <h3>Active Reservations</h3>
            <?php if (mysqli_num_rows($active_reservations) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Slot Number</th>
                            <th>Parking Area</th>
                            <th>Reservation Time</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($active_reservations)): ?>
                            <tr>
                                <td><?= ($row['slot_name']) ?></td>
                                <td><?= htmlspecialchars($row['area_name']) ?></td>
                                <td><?= htmlspecialchars($row['start_time']) ?></td>
                                <td><?= htmlspecialchars($row['status']) ?></td>
                <td>
                    <?php if ($row['status'] == 'Pending'): ?>
                        <a href="./edit_reservation.php?action=edit&reservation_id=<?= $row['reservation_id'] ?>" class="btn btn-edit">Edit</a>
                        <a href="./reservation.php?action=cancel&reservation_id=<?= $row['reservation_id'] ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to cancel this reservation?');">Cancel</a>
                    <?php elseif ($row['status'] == 'Approved'): ?>
                        <a href="generate_confirmation.php?reservation_id=<?= $row['reservation_id'] ?>" class="btn btn-success" target="_blank">Download Confirmation</a>
                    <?php endif; ?>
                </td>


                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="no-data">No active reservations found.</p>
            <?php endif; ?>
        </div>

        <div class="history-section">
            <h3>Reservation History</h3>
            <?php if (mysqli_num_rows($reservation_history) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Slot Number</th>
                            <th>Parking Area</th>
                            <th>Start Time</th>
                            <th>End Time</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($reservation_history)): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['slot_name']) ?></td>
                                <td><?= htmlspecialchars($row['area_name']) ?></td>
                                <td><?= htmlspecialchars($row['start_time']) ?></td>
                                <td><?= htmlspecialchars($row['end_time']) ?></td>
                                <td><?= htmlspecialchars($row['status']) ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="no-data">No past reservations found.</p>
            <?php endif; ?>
        </div>
    </div>

    <script>
        $(document).ready(function() {
    $('#area_id').change(function() {
        var area_id = $(this).val();
        var type_id = $(this).find(':selected').data('type');
        
        if (area_id) {
            // Get available slots
            $.ajax({
                url: 'get_available_slots.php',
                type: 'POST',
                data: {area_id: area_id},
                dataType: 'json',
                success: function(data) {
                    var options = '<option value="" disabled selected>Select a slot</option>';
                    $.each(data, function(index, slot) {
                        options += '<option value="' + slot.slot_id + '">' + slot.slot_name + '</option>';
                    });
                    $('#slot_id').html(options);
                }
            });

            // Get vehicle categories for the selected area's type
            $.ajax({
                url: 'get_vehicle_categories.php',
                type: 'POST',
                data: {type_id: type_id},
                dataType: 'json',
                success: function(data) {
                    var options = '<option value="" disabled selected>Select vehicle category</option>';
                    $.each(data, function(index, category) {
                        options += '<option value="' + category.category_name + '">' + 
                                  category.category_name + '</option>';
                    });
                    $('#vehicle_type')
                        .html(options)
                        .prop('disabled', false);
                },
                error: function() {
                    $('#vehicle_type')
                        .html('<option value="" disabled selected>Error loading categories</option>')
                        .prop('disabled', true);
                }
            });
        } else {
            $('#slot_id').html('<option value="" disabled selected>Select a slot</option>');
            $('#vehicle_type')
                .html('<option value="" disabled selected>Select parking area first</option>')
                .prop('disabled', true);
        }
    });
});

        document.getElementById('reservationForm').addEventListener('submit', function(event) {
    const startTime = new Date(document.getElementById('start_time').value);
    const endTime = new Date(document.getElementById('end_time').value);
    const currentTime = new Date();
    const vehicleNumber = document.getElementById('vehicle_number').value;
    
    // Calculate minimum allowed start time (current time + 1 hour)
    const minStartTime = new Date(currentTime.getTime() + (60 * 60 * 1000));
    
    // Check if start_time is at least 1 hour in the future
    if (startTime < minStartTime) {
        alert("Start time must be at least 1 hour from now.");
        event.preventDefault(); // Prevent form submission
        return;
    }

    // Check if end_time is after start_time
    if (endTime <= startTime) {
        alert("End time must be after the start time.");
        event.preventDefault(); // Prevent form submission
        return;
    }

    // Check if the duration between start_time and end_time is at least 30 minutes
    const duration = (endTime - startTime) / (1000 * 60); // Duration in minutes
    if (duration < 30) {
        alert("The duration between start time and end time must be at least 30 minutes.");
        event.preventDefault(); // Prevent form submission
        return;
    }

    // Check if the vehicle number is in the correct format
    if (!vehicleNumberPattern.test(vehicleNumber)) {
        alert("Please enter a valid vehicle number (e.g., BA 1 PA 1234).");
        event.preventDefault();
        return;
    }
    });


    // Check for pre-selected area from URL
$(document).ready(function() {
    const urlParams = new URLSearchParams(window.location.search);
    const preSelectedArea = urlParams.get('pre_selected_area');
    
    if (preSelectedArea) {
        $('#area_id').val(preSelectedArea).trigger('change');
    }
});

        </script>
    </body>
    </html>


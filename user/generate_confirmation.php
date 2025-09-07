<?php 
include('../includes/database.php');

if (isset($_GET['reservation_id'])) {
    $reservation_id = $_GET['reservation_id'];

    // Updated query to match your database schema
    $query = "
    SELECT 
        r.reservation_id,
        r.start_time,
        r.end_time,
        r.status,
        r.created_at,
        u.name AS user_name,
        v.vehicle_number,
        v.vehicle_type,
        p.area_name,
        s.slot_name
    FROM Reservations r
    JOIN Users u ON r.user_id = u.user_id
    JOIN VehicleData v ON r.vehicle_id = v.vehicle_id
    JOIN parking_areas p ON r.area_id = p.area_id
    JOIN slots s ON (r.slot_number = s.slot_id AND s.parking_area_id = r.area_id)
    WHERE r.reservation_id = ?";

    $stmt = $connect->prepare($query);
    if (!$stmt) {
        die("Prepare failed: " . $connect->error);
    }

    $stmt->bind_param('i', $reservation_id);
    if (!$stmt->execute()) {
        die("Execute failed: " . $stmt->error);
    }

    $result = $stmt->get_result();
    $reservation = $result->fetch_assoc();

    if ($reservation) {
        // Format dates
        $start_time = new DateTime($reservation['start_time']);
        $end_time = new DateTime($reservation['end_time']);
        $formatted_start = $start_time->format('g:i A, jS F Y');
        $formatted_end = $end_time->format('g:i A, jS F Y');
        $created_at = new DateTime($reservation['created_at']);
        $formatted_created = $created_at->format('g:i A, jS F Y');
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Parking Confirmation <?= isset($reservation) ? "#" . htmlspecialchars($reservation['reservation_id']) : "" ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
        }
        .ticket-container {
            max-width: 800px;
            margin: 0 auto;
            border: 2px solid #333;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
            margin-bottom: 20px;
        }
        .company-name {
            font-size: 24px;
            color: orange;
            font-weight: bold;
            margin: 10px 0;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .table th, .table td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }
        .table th {
            background-color: #f4f4f4;
            width: 30%;
        }
        .footer {
            margin-top: 30px;
            font-size: 14px;
            text-align: left;
            border-top: 1px solid #ddd;
            padding-top: 20px;
        }
        .buttons {
            text-align: center;
            margin-top: 20px;
        }
        .buttons button {
            padding: 10px 20px;
            font-size: 16px;
            cursor: pointer;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            margin: 0 5px;
        }
        @media print {
            .buttons {
                display: none;
            }
        }
        .status {
            padding: 5px 10px;
            border-radius: 4px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <?php if (isset($reservation) && $reservation): ?>
    <div class="ticket-container">
        <div class="header">
            <div class="company-name">ParkEase</div>
            <p>Reservation Confirmation Ticket</p>
            <p style="color: #666;">Generated on: <?= htmlspecialchars($formatted_created) ?></p>
        </div>

        <table class="table">
            <tr>
                <th>Reservation ID</th>
                <td><?= htmlspecialchars($reservation['reservation_id']) ?></td>
            </tr>
            <tr>
                <th>Status</th>
                <td>
                    <span class="status" style="background-color: <?= $reservation['status'] === 'Approved' ? '#28a745' : ($reservation['status'] === 'Pending' ? '#ffc107' : '#dc3545') ?>; color: <?= $reservation['status'] === 'Pending' ? '#000' : '#fff' ?>">
                        <?= htmlspecialchars($reservation['status']) ?>
                    </span>
                </td>
            </tr>
            <tr>
                <th>Parker Name</th>
                <td><?= htmlspecialchars($reservation['user_name']) ?></td>
            </tr>
            <tr>
                <th>Vehicle Details</th>
                <td>
                    <strong>Number:</strong> <?= htmlspecialchars($reservation['vehicle_number']) ?><br>
                    <strong>Type:</strong> <?= htmlspecialchars($reservation['vehicle_type']) ?>
                </td>
            </tr>
            <tr>
                <th>Parking Location</th>
                <td>
                    <strong>Area:</strong> <?= htmlspecialchars($reservation['area_name']) ?><br>
                    <strong>Slot:</strong> <?= htmlspecialchars($reservation['slot_name']) ?>
                </td>
            </tr>
            <tr>
                <th>Duration</th>
                <td>
                    <strong>From:</strong> <?= htmlspecialchars($formatted_start) ?><br>
                    <strong>To:</strong> <?= htmlspecialchars($formatted_end) ?>
                </td>
            </tr>
        </table>

        <div class="footer">
            <p><strong>Important Instructions:</strong></p>
            <ol>
                <li>Please arrive at least 5 minutes before your scheduled time.</li>
                <li>Present this confirmation ticket at the parking entrance.</li>
                <li>Park only in your assigned slot (<?= htmlspecialchars($reservation['slot_name']) ?>).</li>
                <li>Keep your vehicle locked and secured at all times.</li>
                <li>Contact parking management for any assistance or emergencies.</li>
            </ol>
        </div>
    </div>

    <div class="buttons">
        <button onclick="window.print()">Print Ticket</button>
        <button onclick="window.location.href='./reservation.php'">Back to Reservations</button>
    </div>

    <?php else: ?>
    <div class="ticket-container">
        <p>No reservation details found. Please check the reservation ID and try again.</p>
        <div class="buttons">
            <button onclick="window.location.href='./reservation.php'">Back to Reservations</button>
        </div>
    </div>
    <?php endif; ?>

</body>
</html>
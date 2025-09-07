<?php
include('database.php'); 
session_start();
// Check if user is logged in and is a regular user
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'user') {
    header("Location: ../index.php");
    exit;
}
// print_r($_SESSION);
// exit;
?>
<?php
// setting a session variables.
$userName = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : "Guest";
$phone= $_SESSION['phone'];
$email = $_SESSION['email'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ParkEase - User Dashboard</title>
  <!-- Add Leaflet CSS and JS -->
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
  <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
  <link rel="stylesheet" href="../css/style.css">
</head>
<body>
  <header class="header">
    <div class="header-left">
      <a href="../user/user_dashboard.php" class="logo">
        <span class="sr-only">ParkEase</span>
      </a>
    </div>
    <div class="header-right">
      <nav class="nav">
        <a href="../user/user_dashboard.php">Home</a>
        <a href="../user/locations.php">Locations</a>
        <a href="../user/reservation.php">Reservations</a>
        <a href="../user/contact.php">Contact</a>
        <a href="../logout.php" class="btn">Logout</a>
      </nav>
<!-- Profile Button -->
<div class="profile">
    <a href="#" class="profile-btn" onclick="toggleProfilePopup()">
        <img src="../Images/Profile.jpg" alt="Profile" class="profile-icon">
        <span><?php echo htmlspecialchars($userName); ?></span>
    </a>
</div>

<!-- Profile Popup -->
<div id="profile-popup" class="popup">
    <div class="popup-content">
        <span class="close" onclick="toggleProfilePopup()">&times;</span>
        <h2>View Profile</h2>
        <div id="view-profile">
            <strong>Username:</strong>
            <span><?php echo htmlspecialchars($userName); ?></span>
            <strong>Email:</strong>
            <span><?php echo htmlspecialchars($email); ?></span>
            <strong>Phone:</strong>
            <span><?php echo htmlspecialchars($phone); ?></span>
        </div>
    </div>
</div>
    </div>
</header>
  <script src="../JS/userscript.js" defer></script>

<style>
    /* Keep all existing styles the same, only modify these popup-specific styles */
    .popup {
        display: none;
        position: absolute;
        top: 60px; /* Adjusted to be closer to button */
        right: 10px; /* Adjusted to align with profile button */
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        z-index: 1000;
        width: 320px;
    }

    /* Add arrow pointer to popup */
    .popup::before {
        content: '';
        position: absolute;
        top: -8px;
        right: 20px;
        width: 16px;
        height: 16px;
        background: white;
        transform: rotate(45deg);
        box-shadow: -2px -2px 5px rgba(0,0,0,0.05);
    }

    .popup-content {
        padding: 20px;
        position: relative;
    }

    .popup h2 {
        margin-bottom: 15px;
        font-size: 18px;
        color: #2c3e50;
    }

    #view-profile {
        display: grid;
        grid-template-columns: 100px 1fr;
        gap: 10px;
    }

    #view-profile p {
        margin: 8px 0;
        display: contents;
    }

    #view-profile strong {
        color: #666;
        font-weight: 600;
    }

    #view-profile span {
        color: #333;
    }

    .close {
        position: absolute;
        top: 15px;
        right: 15px;
        cursor: pointer;
        font-size: 18px;
        color: #666;
    }
</style>

<script>
    function toggleProfilePopup() {
        const popup = document.getElementById('profile-popup');
        popup.style.display = popup.style.display === 'block' ? 'none' : 'block';
    }

    // Close popup when clicking outside
    document.addEventListener('click', function(event) {
        const popup = document.getElementById('profile-popup');
        const profileBtn = document.querySelector('.profile-btn');
        
        if (!popup.contains(event.target) && !profileBtn.contains(event.target)) {
            popup.style.display = 'none';
        }
    });

    // Prevent popup from closing when clicking inside it
    document.getElementById('profile-popup').addEventListener('click', function(event) {
        event.stopPropagation();
    });
</script>
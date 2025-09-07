<?php
    session_start();
    include('../includes/database.php');
    // Check if user is logged in and is an admin
    if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
        // If not, redirect to the login page
        header("Location: ../index.php");
        exit;
    }
?>

<!-- Admin Dashboard Content -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/admin_style.css">
    <title>Admin Dashboard</title>
    
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>

<style>
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5);
}
.modal-content {
    background-color: #fefefe;
    margin: 10% auto;
    padding: 20px;
    border: 1px solid #888;
    width: 80%;
    max-width: 800px;
}

.close {
    color: #aaa;
    float: right;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
}

.btn-select-location {
    padding: 10px 15px;
    background-color: #4CAF50;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    margin: 10px 0;
}

#selectedLocationPreview {
    margin-top: 10px;
    padding: 10px;
    border: 1px solid #ddd;
}

.search-container {
    margin-bottom: 15px;
}

.btn-confirm {
    margin-top: 10px;
    padding: 10px 20px;
    background-color: #4CAF50;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}

/* Sidebar Styles */
.sidebar {
    position: fixed;
    top: 0;
    left: -250px;
    width: 250px;
    height: 100vh;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    transition: left 0.3s ease;
    z-index: 1000;
    box-shadow: 2px 0 10px rgba(0,0,0,0.1);
}

.sidebar.active {
    left: 0;
}

.sidebar-header {
    padding: 20px;
    background: rgba(255,255,255,0.1);
    border-bottom: 1px solid rgba(255,255,255,0.2);
}

.sidebar-header h3 {
    color: white;
    font-size: 1.2rem;
    margin: 0;
    text-align: center;
}

.sidebar-menu {
    padding: 20px 0;
}

.sidebar-menu ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.sidebar-menu li {
    margin: 0;
}

.sidebar-menu a {
    display: flex;
    align-items: center;
    padding: 15px 20px;
    color: white;
    text-decoration: none;
    transition: all 0.3s ease;
    border-left: 3px solid transparent;
}

.sidebar-menu a:hover {
    background: rgba(255,255,255,0.1);
    border-left-color: #ffd700;
    transform: translateX(5px);
}

.sidebar-menu a.active {
    background: rgba(255,255,255,0.2);
    border-left-color: #ffd700;
}

.sidebar-menu .icon {
    font-size: 1.2rem;
    margin-right: 12px;
    width: 20px;
    text-align: center;
}

.sidebar-menu .text {
    font-weight: 500;
}

.sidebar-toggle {
    position: fixed;
    top: 20px;
    left: 20px;
    background: #667eea;
    color: white;
    border: none;
    padding: 10px;
    border-radius: 5px;
    cursor: pointer;
    z-index: 1001;
    font-size: 1.2rem;
    transition: all 0.3s ease;
}

.sidebar-toggle:hover {
    background: #5a6fd8;
    transform: scale(1.05);
}

.sidebar-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    z-index: 999;
    display: none;
}

.sidebar-overlay.active {
    display: block;
}

/* Adjust main content when sidebar is open */
.main-content {
    transition: margin-left 0.3s ease;
}

.main-content.sidebar-open {
    margin-left: 250px;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .sidebar {
        width: 100%;
        left: -100%;
    }
    
    .main-content.sidebar-open {
        margin-left: 0;
    }
    
    .sidebar-toggle {
        top: 15px;
        left: 15px;
    }
}
</style>
</head>
<body>
    <!-- Sidebar Toggle Button -->
    <button class="sidebar-toggle" onclick="toggleSidebar()">☰</button>
    
    <!-- Sidebar Overlay -->
    <div class="sidebar-overlay" onclick="toggleSidebar()"></div>
    
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <h3>🏢 Admin Panel</h3>
        </div>
        <nav class="sidebar-menu">
            <ul>
                <li><a href="dashboard.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
                    <span class="icon">🏠</span>
                    <span class="text">Dashboard</span>
                </a></li>
                <li><a href="manage_parkingarea.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'manage_parkingarea.php' ? 'active' : ''; ?>">
                    <span class="icon">📍</span>
                    <span class="text">Parking Areas</span>
                </a></li>
                <li><a href="manage_vehicle_category.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'manage_vehicle_category.php' ? 'active' : ''; ?>">
                    <span class="icon">🚗</span>
                    <span class="text">Vehicle Categories</span>
                </a></li>
                <li><a href="manage_fare_rates.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'manage_fare_rates.php' ? 'active' : ''; ?>">
                    <span class="icon">💵</span>
                    <span class="text">Fare Rates</span>
                </a></li>
                <li><a href="manage_reservations.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'manage_reservations.php' ? 'active' : ''; ?>">
                    <span class="icon">📅</span>
                    <span class="text">Reservations</span>
                </a></li>
                <li><a href="manage_users.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'manage_users.php' ? 'active' : ''; ?>">
                    <span class="icon">👥</span>
                    <span class="text">Manage Users</span>
                </a></li>
                <li><a href="view_messages.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'view_messages.php' ? 'active' : ''; ?>">
                    <span class="icon">💬</span>
                    <span class="text">Messages</span>
                </a></li>
                <li><a href="../logout.php" class="logout-link">
                    <span class="icon">🚪</span>
                    <span class="text">Logout</span>
                </a></li>
            </ul>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="main-content" id="mainContent">
        <header class="admin-header">
            <h1>Admin Dashboard</h1>
            <nav>
                <div class="button-container">
                    <a href="dashboard.php" class="btn btn-primary">🏠 Home</a>
                    <a href="../logout.php" class="logout-btn">Logout</a>
                </div>
            </nav>
        </header>

<script>
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.querySelector('.sidebar-overlay');
    const mainContent = document.getElementById('mainContent');
    
    sidebar.classList.toggle('active');
    overlay.classList.toggle('active');
    mainContent.classList.toggle('sidebar-open');
}

// Close sidebar when clicking outside on mobile
document.addEventListener('click', function(event) {
    const sidebar = document.getElementById('sidebar');
    const toggle = document.querySelector('.sidebar-toggle');
    const overlay = document.querySelector('.sidebar-overlay');
    
    if (window.innerWidth <= 768 && 
        !sidebar.contains(event.target) && 
        !toggle.contains(event.target) && 
        sidebar.classList.contains('active')) {
        toggleSidebar();
    }
});

// Handle window resize
window.addEventListener('resize', function() {
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('mainContent');
    
    if (window.innerWidth > 768) {
        sidebar.classList.remove('active');
        document.querySelector('.sidebar-overlay').classList.remove('active');
        mainContent.classList.remove('sidebar-open');
    }
});
</script>
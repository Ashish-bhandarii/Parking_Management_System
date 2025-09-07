<?php
// die('test');
include('../includes/database.php');
include('adminheader.php');
?>
        <main class="dashboard-content">
            <h2>Welcome, Admin!</h2>
            <p>Manage your parking system efficiently using the tools below:</p>
            <div class="dashboard-grid">
                <a href="manage_parkingarea.php" class="dashboard-card">
                    <div class="card-icon">📍</div>
                    <div class="card-content">
                        <h3>Parking Areas</h3>
                        <p>Add, update, or remove parking areas.</p>
                    </div>
                </a>
                <a href="manage_vehicle_category.php" class="dashboard-card">
                    <div class="card-icon">🚗</div>
                    <div class="card-content">
                        <h3>Vehicle Categories</h3>
                        <p>Define and manage vehicle types.</p>
                    </div>
                </a>
                <a href="manage_fare_rates.php" class="dashboard-card">
                    <div class="card-icon">💵</div>
                    <div class="card-content">
                        <h3>Fare Rates</h3>
                        <p>Set and adjust parking rates.</p>
                    </div>
                </a>
                <a href="manage_reservations.php" class="dashboard-card">
                    <div class="card-icon">📅</div>
                    <div class="card-content">
                        <h3>Reservations</h3>
                        <p>View and manage reservations.</p>
                    </div>
                </a>
                <a href="manage_users.php" class="dashboard-card">
                    <div class="card-icon">👥</div>
                    <div class="card-content">
                        <h3>Manage Users</h3>
                        <p>Manage and view users</p>
                    </div>
                </a>
                <a href="view_messages.php" class="dashboard-card">
                    <div class="card-icon">💬</div>
                    <div class="card-content">
                        <h3>View Messages</h3>
                        <p>Manage and view Messages from users.</p>
                    </div>
                </a>
            </div>
        </main>
    </div>
</body>
</html>

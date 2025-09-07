<?php
include('../includes/database.php');
include('../includes/header.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['query'])) {
    $query = htmlspecialchars($_POST['query']); // Sanitize input
    $sql = "SELECT * FROM parking_areas WHERE area_name LIKE ?";
    $stmt = $connect->prepare($sql);
    $searchTerm = "%$query%";
    $stmt->bind_param("s", $searchTerm);
    $stmt->execute();
    $result = $stmt->get_result();

    echo "<div class='search-results'>";
    echo "<h1>Search Results for: " . htmlspecialchars($query) . "</h1>";
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "<div class='result-card'>";
            echo "<h3>" . htmlspecialchars($row['area_name']) . "</h3>";
            echo "<p>Total slots: " . htmlspecialchars($row['total_slots']) . "</p>";

            // Reserve Button
            echo "<form method='GET' action='reservation.php' class='reserve-form'>";
            echo "<input type='hidden' name='pre_selected_area' value='" . $row['area_id'] . "'>";
            echo "<button type='submit' class='reserve-button'>Reserve</button>";
            echo "</form>";
            echo "</div>";
        }
    } else {
        echo "<p>No parking areas found for your search.</p>";
    }
    echo "</div>";
} else {
    echo "<p>Invalid request. Please submit the form.</p>";
}
?>
<!-- <a href="../index.php" class="back-to-home">Back to Home</a> -->

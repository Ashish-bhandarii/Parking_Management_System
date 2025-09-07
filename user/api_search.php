<?php
include('../includes/database.php');

// Set content type to JSON
header('Content-Type: application/json');

// Check if this is a POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['query'])) {
    $query = htmlspecialchars($_POST['query']); // Sanitize input
    
    // Search for parking areas
    $sql = "SELECT pa.*, vt.type_name 
            FROM parking_areas pa 
            JOIN vehicle_types vt ON pa.type_id = vt.type_id 
            WHERE pa.area_name LIKE ? 
            ORDER BY pa.area_name ASC 
            LIMIT 10"; // Limit to 10 results for better performance
    
    $stmt = $connect->prepare($sql);
    $searchTerm = "%$query%";
    $stmt->bind_param("s", $searchTerm);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $results = array();
    
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $results[] = array(
                'area_id' => $row['area_id'],
                'area_name' => $row['area_name'],
                'total_slots' => $row['total_slots'],
                'available_slots' => $row['available_slots'],
                'reserved_slots' => $row['reserved_slots'],
                'type_name' => $row['type_name'],
                'map_iframe_url' => $row['map_iframe_url']
            );
        }
    }
    
    // Return JSON response
    echo json_encode($results);
} else {
    // Return error if not a valid request
    echo json_encode(array('error' => 'Invalid request'));
}
?>

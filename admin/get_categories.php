<?php
include '../includes/database.php';

if (isset($_GET['type_id'])) {
    $type_id = intval($_GET['type_id']);
    
    $query = "SELECT category_id, category_name 
              FROM vehicle_categories 
              WHERE type_id = ?";
              
    $stmt = mysqli_prepare($connect, $query);
    mysqli_stmt_bind_param($stmt, "i", $type_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $categories = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $categories[] = $row;
    }
    
    header('Content-Type: application/json');
    echo json_encode($categories);
}
?> 
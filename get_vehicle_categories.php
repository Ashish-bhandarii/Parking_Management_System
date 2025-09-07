<?php
include('../includes/database.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['type_id'])) {
    $type_id = mysqli_real_escape_string($connect, $_POST['type_id']);
    
    // Get the parking type details
    $type_query = "SELECT category_name FROM vehicle_categories WHERE type_id = ?";
    $stmt = mysqli_prepare($connect, $type_query);
    mysqli_stmt_bind_param($stmt, 'i', $type_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $categories = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $categories[] = $row;
        
        // If this is a 4-wheeler category, also allow 2-wheelers
        if (strpos(strtolower($row['category_name']), 'four wheeler') !== false) {
            $categories[] = array('category_name' => 'Two Wheeler');
        }
    }
    
    echo json_encode($categories);
}
?> 
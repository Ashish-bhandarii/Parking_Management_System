<?php
include('../includes/database.php');

header('Content-Type: application/json');

if (isset($_POST['area_id'])) {
    $area_id = mysqli_real_escape_string($connect, $_POST['area_id']);
    
    $query = "SELECT s.slot_id, s.slot_name 
              FROM slots s
              WHERE s.parking_area_id = ?";
    
    $stmt = mysqli_prepare($connect, $query);
    mysqli_stmt_bind_param($stmt, 'i', $area_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $slots = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $slots[] = array(
            'slot_id' => $row['slot_id'],
            'slot_name' => $row['slot_name']
        );
    }
    
    echo json_encode($slots);
} else {
    echo json_encode(array('error' => 'No area_id provided'));
}
?>

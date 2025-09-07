<?php
include('../includes/database.php');

if (isset($_POST['type_id'])) {
    $type_id = mysqli_real_escape_string($connect, $_POST['type_id']);
    
    // Define allowed categories
    $allowed_categories = [
        'two_wheeler' => ['Motorcycle', 'Scooter', 'Moped', 'Sport Bike'],
        'four_wheeler' => ['Car', 'Jeep', 'Van', 'SUV', 'Pickup']
    ];

    $categories = [];
    
    // If it's a 4-wheeler area (assuming type_id 2 is for 4-wheelers)
    if ($type_id == 2) {
        // Include both 2-wheelers and 4-wheelers
        $categories = array_merge(
            $allowed_categories['two_wheeler'],
            $allowed_categories['four_wheeler']
        );
    } 
    // If it's a 2-wheeler area (assuming type_id 1 is for 2-wheelers)
    else if ($type_id == 1) {
        // Include only 2-wheelers
        $categories = $allowed_categories['two_wheeler'];
    }

    // Format the response
    $response = [];
    foreach ($categories as $category) {
        $response[] = [
            'category_name' => $category
        ];
    }
    
    echo json_encode($response);
} else {
    echo json_encode(['error' => 'No type ID provided']);
}
?>
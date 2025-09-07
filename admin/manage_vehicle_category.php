<?php
include '../includes/database.php';
include 'adminheader.php';

// Initialize error message variable
$error_message = '';
$success_message = '';

// Define allowed categories for each vehicle type
$allowed_categories = [
    'two_wheeler' => ['Motorcycle', 'Scooter', 'Moped', 'Sport Bike'],
    'four_wheeler' => ['Car', 'Jeep', 'Van', 'SUV', 'Pickup']
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_category'])) {
        $category_name = trim(mysqli_real_escape_string($connect, $_POST['category_name']));
        $type_id = mysqli_real_escape_string($connect, $_POST['type_id']);
        
        // Get vehicle type name
        $type_query = "SELECT type_name FROM vehicle_types WHERE type_id = '$type_id'";
        $type_result = mysqli_query($connect, $type_query);
        $type_data = mysqli_fetch_assoc($type_result);
        $type_name = $type_data['type_name'];
        
        // Check if category exists
        $check_query = "SELECT * FROM vehicle_categories WHERE LOWER(category_name) = LOWER('$category_name')";
        $check_result = mysqli_query($connect, $check_query);
        
        if (mysqli_num_rows($check_result) > 0) {
            $error_message = "Category already exists!";
        } else {
            // Validate category against allowed lists
            $category_valid = false;
            if (isset($allowed_categories[$type_name])) {
                $category_valid = in_array(ucwords($category_name), $allowed_categories[$type_name]);
            }
            
            if ($category_valid) {
                $query = "INSERT INTO vehicle_categories (category_name, type_id) VALUES ('$category_name', '$type_id')";
                if (mysqli_query($connect, $query)) {
                    $success_message = "Category added successfully!";
                } else {
                    $error_message = "Error adding category: " . mysqli_error($connect);
                }
            } else {
                $error_message = "Invalid category for selected vehicle type!";
            }
        }
    } elseif (isset($_POST['edit_category'])) {
        try {
            $category_id = intval($_POST['category_id']);
            $category_name = mysqli_real_escape_string($connect, $_POST['category_name']);
            $type_id = mysqli_real_escape_string($connect, $_POST['type_id']);
            
            // First check if the category name already exists for a different ID
            $check_query = "SELECT * FROM vehicle_categories WHERE category_name = '$category_name' AND category_id != $category_id";
            $check_result = mysqli_query($connect, $check_query);
            
            if (mysqli_num_rows($check_result) > 0) {
                // Category name already exists
                echo "<script>alert('This category name already exists. Please choose a different name.');</script>";
            } else {
                // Safe to update
                $query = "UPDATE vehicle_categories SET category_name = '$category_name', type_id = '$type_id' WHERE category_id = $category_id";
                if (mysqli_query($connect, $query)) {
                    echo "<script>alert('Category updated successfully!');</script>";
                    echo "<script>window.location.href = window.location.href;</script>"; // Refresh the page
                }
            }
        } catch (mysqli_sql_exception $e) {
            echo "<script>alert('Error: Category name already exists!');</script>";
        }
    } elseif (isset($_POST['delete_category'])) {
        $category_id = intval($_POST['category_id']);
        $query = "DELETE FROM vehicle_categories WHERE category_id = $category_id";
        if (mysqli_query($connect, $query)) {
            $success_message = "Category deleted successfully!";
        } else {
            $error_message = "Error deleting category: " . mysqli_error($connect);
        }
    }
}

// Fetch vehicle types
$types_query = "SELECT * FROM vehicle_types";
$types_result = mysqli_query($connect, $types_query);
$vehicle_types = [];
while ($type = mysqli_fetch_assoc($types_result)) {
    $vehicle_types[$type['type_id']] = $type['type_name'];
}
?>

<a href="dashboard.php" class="back-to-home">Back to Home</a>
<div class="container">
    <h2>Manage Vehicle Categories</h2>
    
    <?php if ($error_message): ?>
        <div class="alert alert-error"><?php echo $error_message; ?></div>
    <?php endif; ?>
    
    <?php if ($success_message): ?>
        <div class="alert alert-success"><?php echo $success_message; ?></div>
    <?php endif; ?>
    
    <!-- Add Category Form -->
    <form id="addCategoryForm" method="POST" class="add-form">
        <div class="form-group">
            <select name="type_id" id="add_type_id" required>
                <option value="">Select Vehicle Type</option>
                <?php foreach ($vehicle_types as $id => $name): ?>
                    <option value="<?php echo $id; ?>"><?php echo ucwords(str_replace('_', ' ', $name)); ?></option>
                <?php endforeach; ?>
            </select>
            <select name="category_name" id="add_category_name" required disabled>
                <option value="">Select Category</option>
            </select>
            <button type="submit" name="add_category">Add Category</button>
        </div>
    </form>

    <!-- Categories Display -->
    <?php foreach ($vehicle_types as $type_id => $type_name): ?>
        <div class="vehicle-type-section">
            <h3><?php echo ucwords(str_replace('_', ' ', $type_name)); ?></h3>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Category Name</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $categories_query = "SELECT * FROM vehicle_categories WHERE type_id = $type_id";
                    $categories_result = mysqli_query($connect, $categories_query);
                    while ($row = mysqli_fetch_assoc($categories_result)) {
                        echo "<tr>
                                <td>{$row['category_id']}</td>
                                <td>{$row['category_name']}</td>
                                <td>
                                    <button class='edit-btn' data-id='{$row['category_id']}'>Edit</button>
                                    <form id='editCategoryForm-{$row['category_id']}' method='POST' style='display:none;'>
                                        <input type='hidden' name='category_id' value='{$row['category_id']}'>
                                        <select name='type_id' class='edit-type-select' data-id='{$row['category_id']}' required>";
                                        foreach ($vehicle_types as $vid => $vname) {
                                            $selected = ($vid == $type_id) ? 'selected' : '';
                                            echo "<option value='$vid' $selected>" . ucwords(str_replace('_', ' ', $vname)) . "</option>";
                                        }
                        echo "          </select>
                                        <select name='category_name' class='edit-category-select' data-id='{$row['category_id']}' required>
                                            <option value='{$row['category_name']}'>{$row['category_name']}</option>
                                        </select>
                                        <button type='submit' name='edit_category'>Save</button>
                                    </form>
                                    <form method='POST' style='display:inline-block;' onsubmit=\"return confirm('Are you sure you want to delete this category?');\">
                                        <input type='hidden' name='category_id' value='{$row['category_id']}'>
                                        <button type='submit' name='delete_category'>Delete</button>
                                    </form>
                                </td>
                              </tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    <?php endforeach; ?>
</div>

<style>
.container {
    padding: 20px;
}

.alert {
    padding: 15px;
    margin-bottom: 20px;
    border: 1px solid transparent;
    border-radius: 4px;
}

.alert-error {
    color: #721c24;
    background-color: #f8d7da;
    border-color: #f5c6cb;
}

.alert-success {
    color: #155724;
    background-color: #d4edda;
    border-color: #c3e6cb;
}
.container {
    padding: 20px;
}

.add-form {
    margin-bottom: 30px;
}

.form-group {
    display: flex;
    gap: 10px;
    margin-bottom: 20px;
}

.vehicle-type-section {
    margin-bottom: 30px;
}

.vehicle-type-section h3 {
    color: #333;
    padding: 10px 0;
    border-bottom: 2px solid #eee;
    margin-bottom: 15px;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 20px;
}

th, td {
    padding: 12px;
    text-align: left;
    border-bottom: 1px solid #ddd;
}

th {
    background-color: #f5f5f5;
}

button {
    padding: 8px 15px;
    margin: 0 5px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}

.edit-btn {
    background-color: #4CAF50;
    color: white;
}

button[name="delete_category"] {
    background-color: #f44336;
    color: white;
}

select {
    padding: 8px;
    border-radius: 4px;
    border: 1px solid #ddd;
}

input[type="text"] {
    padding: 8px;
    border-radius: 4px;
    border: 1px solid #ddd;
    width: 200px;
}
</style>

<script>
// Define allowed categories
const allowedCategories = {
    'two_wheeler': ['Motorcycle', 'Scooter', 'Moped', 'Sport Bike'],
    'four_wheeler': ['Car', 'Jeep', 'Van', 'SUV', 'Pickup']
};

// Function to populate category dropdown based on vehicle type
function populateCategoryDropdown(typeSelect, categorySelect) {
    const selectedType = typeSelect.options[typeSelect.selectedIndex].text.toLowerCase().replace(' ', '_');
    categorySelect.innerHTML = '<option value="">Select Category</option>';
    
    if (allowedCategories[selectedType]) {
        allowedCategories[selectedType].forEach(category => {
            const option = document.createElement('option');
            option.value = category;
            option.textContent = category;
            categorySelect.appendChild(option);
        });
        categorySelect.disabled = false;
    } else {
        categorySelect.disabled = true;
    }
}

// Add Category Form
const addTypeSelect = document.getElementById('add_type_id');
const addCategorySelect = document.getElementById('add_category_name');

addTypeSelect.addEventListener('change', function() {
    populateCategoryDropdown(this, addCategorySelect);
});

// Edit Category Forms
document.querySelectorAll('.edit-type-select').forEach(typeSelect => {
    typeSelect.addEventListener('change', function() {
        const categorySelect = document.querySelector(`.edit-category-select[data-id="${this.dataset.id}"]`);
        populateCategoryDropdown(this, categorySelect);
    });
});

// Show/hide edit forms
document.querySelectorAll('.edit-btn').forEach(button => {
    button.addEventListener('click', function() {
        const formId = `editCategoryForm-${this.dataset.id}`;
        document.getElementById(formId).style.display = 'inline-block';
        this.style.display = 'none';
    });
});
</script>
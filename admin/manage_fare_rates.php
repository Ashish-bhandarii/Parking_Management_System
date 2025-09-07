<?php
include '../includes/database.php';
include 'adminheader.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_rate'])) {
        $area_id = intval($_POST['area_id']);
        $category_id = intval($_POST['category_id']);
        $hourly_rate = floatval($_POST['hourly_rate']);
        
        // Get the type_id from the parking area
        $area_query = "SELECT type_id FROM parking_areas WHERE area_id = $area_id";
        $area_result = mysqli_query($connect, $area_query);
        $area_data = mysqli_fetch_assoc($area_result);
        $type_id = $area_data['type_id'];
        
        if ($hourly_rate > 0) {
            $query = "INSERT INTO FareRates (area_id, type_id, category_id, hourly_rate) 
                     VALUES ($area_id, $type_id, $category_id, $hourly_rate)";
            if (mysqli_query($connect, $query)) {
                $_SESSION['message'] = "Rate added successfully!";
                $_SESSION['message_type'] = "success";
            } else {
                $_SESSION['message'] = "Error: " . mysqli_error($connect);
                $_SESSION['message_type'] = "error";
            }
        } else {
            $_SESSION['message'] = "Hourly rate must be a positive number.";
            $_SESSION['message_type'] = "error";
        }
    }

    // Delete and edit operations remain the same
    if (isset($_POST['delete_rate'])) {
        $rate_id = intval($_POST['rate_id']);
        $query = "DELETE FROM FareRates WHERE rate_id = $rate_id";
        if (mysqli_query($connect, $query)) {
            $_SESSION['message'] = "Rate deleted successfully!";
            $_SESSION['message_type'] = "success";
        }
    }

    if (isset($_POST['edit_rate'])) {
        $rate_id = intval($_POST['rate_id']);
        $hourly_rate = floatval($_POST['hourly_rate']);
        if ($hourly_rate > 0) {
            $query = "UPDATE FareRates SET hourly_rate = $hourly_rate WHERE rate_id = $rate_id";
            if (mysqli_query($connect, $query)) {
                $_SESSION['message'] = "Rate updated successfully!";
                $_SESSION['message_type'] = "success";
            }
        } else {
            $_SESSION['message'] = "Hourly rate must be a positive number.";
            $_SESSION['message_type'] = "error";
        }
    }

    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}
?>

<a href="dashboard.php" class="back-to-home">Back to Home</a>
<div class="container">
    <?php
    if (isset($_SESSION['message'])) {
        echo "<div class='{$_SESSION['message_type']}-message'>{$_SESSION['message']}</div>";
        unset($_SESSION['message']);
        unset($_SESSION['message_type']);
    }
    ?>
    <h2>Manage Fare Rates</h2>
    <form method="POST" id="addRateForm">
        <select name="area_id" id="area_id" required>
            <option value="">Select Parking Area</option>
            <?php
            $areas = mysqli_query($connect, "SELECT pa.*, vt.type_name, vt.type_id 
                                           FROM parking_areas pa 
                                           JOIN vehicle_types vt ON pa.type_id = vt.type_id");
            while ($area = mysqli_fetch_assoc($areas)) {
                echo "<option value='{$area['area_id']}' data-type-id='{$area['type_id']}' data-type-name='{$area['type_name']}'>";
                echo "{$area['area_name']} ({$area['type_name']})";
                echo "</option>";
            }
            ?>
        </select>
        
        <select name="category_id" id="category_id" required>
            <option value="">Select Vehicle Category</option>
        </select>

        <div id="vehicle_type_display" style="margin: 10px 0; display: none;">
            Selected Vehicle Type: <span id="selected_type_name"></span>
            <input type="hidden" id="type_id" name="type_id">
        </div>

        <input type="number" name="hourly_rate" placeholder="Hourly Rate" step="0.01" required>
        <button type="submit" name="add_rate">Add Rate</button>
    </form>

    <table>
        <thead>
            <tr>
                <th>Rate ID</th>
                <th>Parking Area</th>
                <th>Vehicle Type</th>
                <th>Vehicle Category</th>
                <th>Hourly Rate</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $rates = mysqli_query($connect, "SELECT fr.rate_id, pa.area_name, vt.type_name, 
                                           vc.category_name, fr.hourly_rate
                                           FROM FareRates fr
                                           JOIN parking_areas pa ON fr.area_id = pa.area_id
                                           JOIN vehicle_types vt ON fr.type_id = vt.type_id
                                           JOIN vehicle_categories vc ON fr.category_id = vc.category_id");
            while ($row = mysqli_fetch_assoc($rates)) {
                echo "<tr>
                        <td>{$row['rate_id']}</td>
                        <td>{$row['area_name']}</td>
                        <td>{$row['type_name']}</td>
                        <td>{$row['category_name']}</td>
                        <td>
                            <span class='hourly-rate-display' id='display-{$row['rate_id']}'>{$row['hourly_rate']}</span>
                            <form method='POST' style='display:none;' id='editForm-{$row['rate_id']}'>
                                <input type='hidden' name='rate_id' value='{$row['rate_id']}'>
                                <input type='number' name='hourly_rate' value='{$row['hourly_rate']}' step='0.01' required>
                                <button type='submit' name='edit_rate'>Save</button>
                            </form>
                        </td>
                        <td>
                            <button class='edit-btn' data-id='{$row['rate_id']}'>Edit</button>
                            <form method='POST' style='display:inline-block;' onsubmit=\"return confirm('Are you sure you want to delete this rate?');\">
                                <input type='hidden' name='rate_id' value='{$row['rate_id']}'>
                                <button type='submit' name='delete_rate'>Delete</button>
                            </form>
                        </td>
                    </tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Message timeout
    setTimeout(function() {
        var messageElement = document.querySelector('.success-message, .error-message');
        if (messageElement) {
            messageElement.style.display = 'none';
        }
    }, 5000);

    // Handle parking area selection
    const areaSelect = document.getElementById('area_id');
    const categorySelect = document.getElementById('category_id');
    const vehicleTypeDisplay = document.getElementById('vehicle_type_display');
    const selectedTypeName = document.getElementById('selected_type_name');
    const typeIdInput = document.getElementById('type_id');

    areaSelect.addEventListener('change', function() {
        if (this.value) {
            const selectedOption = this.options[this.selectedIndex];
            const typeId = selectedOption.dataset.typeId;
            const typeName = selectedOption.dataset.typeName;
            
            // Update type display
            selectedTypeName.textContent = typeName;
            typeIdInput.value = typeId;
            vehicleTypeDisplay.style.display = 'block';

            // Fetch categories for the selected type
            fetch(`get_categories.php?type_id=${typeId}`)
                .then(response => response.json())
                .then(categories => {
                    categorySelect.innerHTML = '<option value="">Select Vehicle Category</option>';
                    categories.forEach(category => {
                        categorySelect.innerHTML += `<option value="${category.category_id}">${category.category_name}</option>`;
                    });
                    categorySelect.disabled = false;
                });
        } else {
            vehicleTypeDisplay.style.display = 'none';
            categorySelect.innerHTML = '<option value="">Select Vehicle Category</option>';
            categorySelect.disabled = true;
        }
    });

    // Edit button functionality
    document.querySelectorAll('.edit-btn').forEach(button => {
        button.addEventListener('click', function() {
            const rateId = this.dataset.id;
            document.getElementById(`editForm-${rateId}`).style.display = 'inline-block';
            document.getElementById(`display-${rateId}`).style.display = 'none';
            this.style.display = 'none';
        });
    });

    // Form validation
    document.getElementById('addRateForm').addEventListener('submit', function(event) {
        const hourlyRate = parseFloat(this.hourly_rate.value);
        if (isNaN(hourlyRate) || hourlyRate <= 0) {
            alert('Hourly rate must be a positive number.');
            event.preventDefault();
        }
    });
});
</script>
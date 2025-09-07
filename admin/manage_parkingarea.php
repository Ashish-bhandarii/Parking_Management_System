<?php
include('../includes/database.php');
include('adminheader.php');

// Helper function to generate and save slots
function generateAndSaveSlots($connect, $area_id, $total_slots) {
    $query = "SELECT COUNT(*) as current_slots FROM slots WHERE parking_area_id = '$area_id'";
    $result = mysqli_query($connect, $query);
    $row = mysqli_fetch_assoc($result);
    $current_slots = $row['current_slots'];

    if ($total_slots > $current_slots) {
        for ($i = $current_slots + 1; $i <= $total_slots; $i++) {
            $slot_name = "slot_" . $i;
            mysqli_query($connect, "INSERT INTO slots (parking_area_id, slot_name) VALUES ('$area_id', '$slot_name')");
        }
    } elseif ($total_slots < $current_slots) {
        mysqli_query($connect, "DELETE FROM slots WHERE parking_area_id = '$area_id' AND CAST(SUBSTRING(slot_name, 6) AS UNSIGNED) > $total_slots");
    }
}

// Handling Add Parking Area
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_parking_area'])) {
        $area_name = mysqli_real_escape_string($connect, $_POST['area_name']);
        $total_slots = mysqli_real_escape_string($connect, $_POST['total_slots']);
        $available_slots = $total_slots;
        $reserved_slots = 0;
        $map_iframe_url = mysqli_real_escape_string($connect, $_POST['map_iframe_url']);
        $type_id = mysqli_real_escape_string($connect, $_POST['type_id']);

        $query = "INSERT INTO parking_areas (area_name, total_slots, reserved_slots, available_slots, map_iframe_url, type_id) 
                  VALUES ('$area_name', '$total_slots', '$reserved_slots', '$available_slots', '$map_iframe_url', '$type_id')";
        
        if (mysqli_query($connect, $query)) {
            $area_id = mysqli_insert_id($connect);
            generateAndSaveSlots($connect, $area_id, $total_slots);
            $_SESSION['message'] = 'Parking area added successfully!';
            header('Location: manage_parkingarea.php');
            exit;
        } else {
            $_SESSION['error'] = 'Error adding parking area: ' . mysqli_error($connect);
        }
    }
}

// Handling Update Parking Area
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_parking_area'])) {
    $area_id = mysqli_real_escape_string($connect, $_POST['area_id']);
    $area_name = mysqli_real_escape_string($connect, $_POST['area_name']);
    $total_slots = mysqli_real_escape_string($connect, $_POST['total_slots']);
    $available_slots = $total_slots;
    $reserved_slots = 0;
    $map_iframe_url = mysqli_real_escape_string($connect, $_POST['map_iframe_url']);
    $type_id = mysqli_real_escape_string($connect, $_POST['type_id']);

    if (empty($area_id) || empty($area_name) || empty($total_slots) || empty($type_id)) {
        $_SESSION['error'] = 'All required fields must be filled out.';
    } else {
        $query = "UPDATE parking_areas 
                  SET area_name = '$area_name', 
                      total_slots = '$total_slots', 
                      reserved_slots = '$reserved_slots', 
                      available_slots = '$available_slots', 
                      map_iframe_url = '$map_iframe_url',
                      type_id = '$type_id'
                  WHERE area_id = '$area_id'";
        
        if (mysqli_query($connect, $query)) {
            generateAndSaveSlots($connect, $area_id, $total_slots);
            $_SESSION['message'] = 'Parking area updated successfully!';
            header('Location: manage_parkingarea.php');
            exit;
        } else {
            $_SESSION['error'] = 'Error updating parking area: ' . mysqli_error($connect);
        }
    }
}

// Handling Delete Parking Area
if (isset($_POST['delete_area_id'])) {
    $area_id = $_POST['delete_area_id'];

    mysqli_begin_transaction($connect);
    try {
        mysqli_query($connect, "DELETE FROM slots WHERE parking_area_id='$area_id'");
        mysqli_query($connect, "DELETE FROM parking_areas WHERE area_id='$area_id'");
        mysqli_commit($connect);
        $_SESSION['message'] = 'Parking area deleted successfully!';
    } catch (Exception $e) {
        mysqli_rollback($connect);
        $_SESSION['error'] = 'Error deleting parking area. Please try again.';
    }
    header('Location: manage_parkingarea.php');
    exit;
}

// Fetch all parking areas from the database
$query = "SELECT pa.*, vt.type_name FROM parking_areas pa JOIN vehicle_types vt ON pa.type_id = vt.type_id";
$parking_areas = mysqli_query($connect, $query);

// Fetch all vehicle types
$vehicle_types_query = "SELECT * FROM vehicle_types";
$vehicle_types = mysqli_query($connect, $vehicle_types_query);
?>

<!-- Add this modal HTML at the bottom of your page -->
<div id="mapModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Select Location</h2>
        <div class="search-container">
            <input type="text" id="searchLocation" placeholder="Search location...">
            <button id="searchBtn">Search</button>
        </div>
        <div id="map" style="height: 300px; width: 100%; margin-top: 1px;"></div>
        <button id="confirmLocation" class="btn-confirm">Confirm Location</button>
    </div>
</div>

<a href="dashboard.php" class="back-to-home">Back to Home</a>
<main class="dashboard-content">
    <h2>Manage Parking Areas</h2>

    <!-- Success or Error Messages -->
    <?php if (isset($_SESSION['message'])): ?>
        <div class="success-message"><?php echo $_SESSION['message']; ?></div>
        <?php unset($_SESSION['message']); ?>
    <?php elseif (isset($_SESSION['error'])): ?>
        <div class="error-message"><?php echo $_SESSION['error']; ?></div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <!-- Add Parking Area Form -->
    <?php if (!isset($_GET['edit_area_id'])): ?>
    <form method="POST" class="add-parking-form" id="parkingAreaForm">
        <input type="text" id="area_name" name="area_name" placeholder="Parking Area Name" required>
        <input type="number" id="total_slots" name="total_slots" placeholder="Total Slots" required>
        
        <!-- Vehicle Type Selection -->
        <div class="form-group">
            <label for="type_id">Allowed Vehicle Type: <span class="required">*</span></label>
            <select id="type_id" name="type_id" required>
                <option value="">Select Vehicle Type</option>
                <?php 
                mysqli_data_seek($vehicle_types, 0);
                while ($type = mysqli_fetch_assoc($vehicle_types)) {
                    echo "<option value='" . $type['type_id'] . "'>" . ucfirst($type['type_name']) . "</option>";
                }
                ?>
            </select>
        </div>
        
        <!-- Map selection container -->
        <div class="map-selection-container">
            <input type="hidden" id="map_iframe_url" name="map_iframe_url" required>
            <button type="button" id="selectLocationBtn" class="btn-select-location">Select Location</button>
            <div id="selectedLocationPreview"></div>
        </div>
        <button type="submit" name="add_parking_area">Add Parking Area</button>
    </form>
    <?php endif; ?>

    <!-- Update Parking Area Form -->
    <?php if (isset($_GET['edit_area_id'])): ?>
    <?php 
    $edit_area_id = mysqli_real_escape_string($connect, $_GET['edit_area_id']);
    $edit_query = "SELECT * FROM parking_areas WHERE area_id = '$edit_area_id'";
    $edit_result = mysqli_query($connect, $edit_query);
    $parking_area = mysqli_fetch_assoc($edit_result);
    
    if ($parking_area): ?>
        <h2>Edit Parking Area</h2>
        <form method="POST" class="add-parking-form" id="parkingAreaForm">
            <input type="hidden" name="area_id" value="<?php echo htmlspecialchars($parking_area['area_id']); ?>">
            
            <div class="form-group">
                <input type="text" id="area_name" name="area_name" 
                    value="<?php echo htmlspecialchars($parking_area['area_name']); ?>" required>
                <span class="required">*</span>
            </div>
            
            <div class="form-group">
                <input type="number" id="total_slots" name="total_slots" 
                    value="<?php echo htmlspecialchars($parking_area['total_slots']); ?>" required>
                <span class="required">*</span>
            </div>
            
            <!-- Vehicle Type Selection -->
            <div class="form-group">
                <label for="type_id">Allowed Vehicle Type: <span class="required">*</span></label>
                <select id="type_id" name="type_id" required>
                    <?php 
                    mysqli_data_seek($vehicle_types, 0);
                    while ($type = mysqli_fetch_assoc($vehicle_types)) {
                        $selected = ($type['type_id'] == $parking_area['type_id']) ? 'selected' : '';
                        echo "<option value='" . $type['type_id'] . "' $selected>" . ucfirst($type['type_name']) . "</option>";
                    }
                    ?>
                </select>
            </div>
            
            <div class="map-selection-container">
                <input type="hidden" id="map_iframe_url" name="map_iframe_url" 
                    value="<?php echo htmlspecialchars($parking_area['map_iframe_url']); ?>" required>
                <button type="button" id="selectLocationBtn" class="btn-select-location">
                    Select Location
                    <span class="required">*</span>
                </button>
                <div id="selectedLocationPreview">
                    <?php if (!empty($parking_area['map_iframe_url'])): ?>
                        <small>Current selected location:</small><br>
                        <iframe width="100%" height="200" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" 
                            src="<?php echo htmlspecialchars($parking_area['map_iframe_url']); ?>">
                        </iframe>
                    <?php endif; ?>
                </div>
            </div>
            
            <button type="submit" name="update_parking_area">Update Parking Area</button>
            <a href="manage_parkingarea.php" class="btn">Cancel</a>
        </form>
    <?php else: ?>
        <div class="error-message">Parking area not found.</div>
        <a href="manage_parkingarea.php" class="btn">Back to List</a>
    <?php endif; ?>
<?php endif; ?>

    <!-- List of Parking Areas -->
    <h2>Existing Parking Areas</h2>
    <table>
        <thead>
            <tr>
                <th>Area Name</th>
                <th>Total Slots</th>
                <th>Allowed Vehicle Types</th>
                <th>Location</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = mysqli_fetch_assoc($parking_areas)): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['area_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['total_slots']); ?></td>
                    <td><?php echo htmlspecialchars(ucfirst($row['type_name'])); ?></td>
                    <td>
                        <?php if (!empty($row['map_iframe_url'])): ?>
                            <iframe width="200" height="150" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" 
                                src="<?php echo htmlspecialchars($row['map_iframe_url']); ?>">
                            </iframe>
                        <?php else: ?>
                            No location set
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="?edit_area_id=<?php echo $row['area_id']; ?>">Edit</a>
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="delete_area_id" value="<?php echo $row['area_id']; ?>">
                            <button type="submit" onclick="return confirm('Are you sure you want to delete this parking area?')">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</main>

<script>
// Global variables for map functionality
let map;
let selectedLocation = null;
let marker = null;
const modal = document.getElementById('mapModal');
const closeBtn = document.querySelector('.close');
const selectLocationBtn = document.getElementById('selectLocationBtn');
const confirmBtn = document.getElementById('confirmLocation');
const searchBtn = document.getElementById('searchBtn');
const searchInput = document.getElementById('searchLocation');

// Function to initialize the map
function initMap() {
    map = L.map('map').setView([14.5995, 120.9842], 13); // Default view of Manila
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    // Add click event to map
    map.on('click', function(e) {
        if (marker) {
            map.removeLayer(marker);
        }
        selectedLocation = e.latlng;
        marker = L.marker(e.latlng).addTo(map);
    });
}

// Function to update location preview
function updateLocationPreview(iframeUrl) {
    const previewDiv = document.getElementById('selectedLocationPreview');
    if (previewDiv && iframeUrl) {
        previewDiv.innerHTML = `
            <small>Selected location:</small><br>
            <iframe width="100%" height="200" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" 
                src="${iframeUrl}">
            </iframe>
        `;
    }
}

// Function to handle location search
function searchLocation(searchQuery) {
    if (searchQuery) {
        fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(searchQuery)}`)
            .then(response => response.json())
            .then(data => {
                if (data.length > 0) {
                    const location = data[0];
                    const lat = parseFloat(location.lat);
                    const lon = parseFloat(location.lon);
                    
                    if (marker) {
                        map.removeLayer(marker);
                    }
                    
                    selectedLocation = { lat: lat, lng: lon };
                    marker = L.marker([lat, lon]).addTo(map);
                    map.setView([lat, lon], 16);
                } else {
                    alert('Location not found');
                }
            })
            .catch(error => {
                console.error('Error searching location:', error);
                alert('Error searching location');
            });
    }
}

// Main initialization when document is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Form validation
    const parkingForm = document.getElementById('parkingAreaForm');
    if (parkingForm) {
        parkingForm.addEventListener('submit', function(event) {
            const areaName = document.getElementById('area_name').value.trim();
            const totalSlots = parseInt(document.getElementById('total_slots').value);
            const mapIframeUrl = document.getElementById('map_iframe_url').value.trim();
            const typeId = document.getElementById('type_id').value;

            // Check if area_name is valid
            if (areaName.length > 40 || !/^[A-Za-z\s]+$/.test(areaName)) {
                alert("Parking Area Name must be less than 40 characters and contain only letters and spaces.");
                event.preventDefault();
                return;
            }

            // Check if total_slots is valid
            if (totalSlots <= 0) {
                alert("Total slots must be greater than 0.");
                event.preventDefault();
                return;
            }

            // Check if location is selected
            if (!mapIframeUrl) {
                alert("Please select a location for the parking area.");
                event.preventDefault();
                return;
            }

            // Check if vehicle type is selected
            if (!typeId) {
                alert("Please select an allowed vehicle type.");
                event.preventDefault();
                return;
            }
        });
    }

    // Style for required fields
    if (selectLocationBtn) {
        selectLocationBtn.classList.add('required-field');
        if (!selectLocationBtn.querySelector('.required')) {
            const asterisk = document.createElement('span');
            asterisk.className = 'required';
            asterisk.textContent = ' *';
            selectLocationBtn.appendChild(asterisk);
        }
    }

    // Initialize map preview if URL exists
    const existingMapUrl = document.getElementById('map_iframe_url')?.value;
    if (existingMapUrl) {
        updateLocationPreview(existingMapUrl);
    }

    // Modal open handler
    if (selectLocationBtn) {
        selectLocationBtn.onclick = function() {
            modal.style.display = "block";
            setTimeout(() => {
                if (!map) {
                    initMap();
                }
                map.invalidateSize();
            }, 100);
        };
    }

    // Modal close handlers
    if (closeBtn) {
        closeBtn.onclick = function() {
            modal.style.display = "none";
        };
    }

    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = "none";
        }
    };

    // Search functionality
    if (searchBtn && searchInput) {
        searchBtn.onclick = function() {
            searchLocation(searchInput.value);
        };

        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                searchBtn.click();
            }
        });
    }

    // Confirm location handler
    if (confirmBtn) {
        confirmBtn.onclick = function() {
            if (selectedLocation) {
                const lat = selectedLocation.lat;
                const lng = selectedLocation.lng;
                
                // Generate OpenStreetMap iframe URL
                const iframe_url = `https://www.openstreetmap.org/export/embed.html?bbox=${lng-0.01},${lat-0.01},${lng+0.01},${lat+0.01}&layer=mapnik&marker=${lat},${lng}`;
                
                document.getElementById('map_iframe_url').value = iframe_url;
                updateLocationPreview(iframe_url);
                
                modal.style.display = "none";
            } else {
                alert('Please select a location first');
            }
        };
    }
});
</script>
<style>
.form-group {
    position: relative;
    margin-bottom: 15px;
}

.required {
    color: red;
    margin-left: 5px;
}

.required-field {
    position: relative;
}

.map-selection-container {
    margin-bottom: 15px;
}

.error-message {
    color: red;
    margin-bottom: 15px;
    padding: 10px;
    background-color: #ffe6e6;
    border: 1px solid #ff9999;
    border-radius: 4px;
}

.success-message {
    color: green;
    margin-bottom: 15px;
    padding: 10px;
    background-color: #e6ffe6;
    border: 1px solid #99ff99;
    border-radius: 4px;
}

#selectedLocationPreview {
    margin-top: 10px;
}

#selectedLocationPreview iframe {
    border: 1px solid #ddd;
    border-radius: 4px;
}

.btn-select-location {
    background-color: #4CAF50;
    color: white;
    padding: 10px 15px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}

.btn-select-location:hover {
    background-color: #45a049;
}

select {
    width: 100%;
    padding: 10px;
    margin-top: 5px;
    border: 1px solid #ccc;
    border-radius: 4px;
    box-sizing: border-box;
}
</style>
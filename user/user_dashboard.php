<?php
include('../includes/database.php');
include('../includes/header.php');
?>
<style>
/* Modern CSS Reset */
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

/* Container Layout */
.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 15px;
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
}

/* Welcome Section - Compact and Clean */
.welcome-section {
  text-align: center;
  margin-bottom: 25px;
  padding: 15px 10px;
}

.welcome-section h1 {
  font-size: 1.8rem;
  margin-bottom: 8px;
  font-weight: 700;
  color: #2c3e50;
  text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.welcome-section p {
  font-size: 1rem;
  margin-bottom: 20px;
  color: #5a6c7d;
  font-weight: 500;
}

/* Elegant Search Bar - No Box Needed */
.search-section {
  margin-bottom: 25px;
  padding: 0 10px;
}

.search-form {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 10px;
  max-width: 450px;
  margin: 0 auto;
  background: white;
  border-radius: 50px;
  padding: 6px;
  box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
  border: 2px solid #f0f0f0;
  transition: all 0.3s ease;
}

.search-form:hover {
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
  border-color: #e0e0e0;
}

.search-input-field {
  flex: 1;
  padding: 12px 18px;
  border: none;
  border-radius: 50px;
  font-size: 0.95rem;
  background: transparent;
  outline: none;
  color: #333;
  font-weight: 500;
}

.search-input-field::placeholder {
  color: #999;
  font-weight: 400;
}

.search-submit-button {
  padding: 12px 20px;
  border: none;
  border-radius: 50px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  font-size: 0.9rem;
  min-width: 90px;
}

.search-submit-button:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
}

.search-submit-button:active {
  transform: translateY(0);
}

/* Real-time Search Results */
.search-results-container {
  max-width: 500px;
  margin: 15px auto 0 auto;
  background: white;
  border-radius: 15px;
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
  border: 1px solid #e0e0e0;
  overflow: hidden;
  animation: slideDown 0.3s ease-out;
}

@keyframes slideDown {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.search-results-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 15px 20px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
}

.search-results-header h3 {
  margin: 0;
  font-size: 1.1rem;
  font-weight: 600;
}

.close-results {
  background: none;
  border: none;
  color: white;
  font-size: 1.5rem;
  cursor: pointer;
  padding: 0;
  width: 25px;
  height: 25px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  transition: background-color 0.2s ease;
}

.close-results:hover {
  background-color: rgba(255, 255, 255, 0.2);
}

.search-results-list {
  max-height: 300px;
  overflow-y: auto;
}

.search-result-item {
  padding: 15px 20px;
  border-bottom: 1px solid #f0f0f0;
  transition: background-color 0.2s ease;
  cursor: pointer;
}

.search-result-item:hover {
  background-color: #f8f9fa;
}

.search-result-item:last-child {
  border-bottom: none;
}

.search-result-title {
  font-size: 1rem;
  font-weight: 600;
  color: #2c3e50;
  margin-bottom: 5px;
}

.search-result-details {
  font-size: 0.85rem;
  color: #5a6c7d;
  margin-bottom: 8px;
}

.search-result-actions {
  display: flex;
  gap: 10px;
  margin-top: 10px;
}

.reserve-btn {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border: none;
  padding: 8px 15px;
  border-radius: 20px;
  font-size: 0.85rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
  text-decoration: none;
  display: inline-block;
}

.reserve-btn:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

.no-results {
  padding: 20px;
  text-align: center;
  color: #5a6c7d;
  font-style: italic;
}

.loading-spinner {
  display: flex;
  justify-content: center;
  align-items: center;
  padding: 20px;
}

.spinner {
  width: 20px;
  height: 20px;
  border: 2px solid #f0f0f0;
  border-top: 2px solid #667eea;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}
.quick-actions {
  padding: 20px 10px;
  max-width: 1000px;
  margin: 0 auto;
}

.quick-actions h1 {
  font-size: 1.6rem;
  margin-bottom: 20px;
  color: #2c3e50;
  text-align: center;
  font-weight: 700;
}

.card-container {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
  gap: 18px;
  justify-content: center;
  margin: 0 auto;
  padding: 0 10px;
}

.card {
  background: white;
  border-radius: 15px;
  padding: 20px 18px;
  text-decoration: none;
  color: inherit;
  transition: all 0.4s ease;
  border: 1px solid #f0f0f0;
  box-shadow: 0 6px 20px rgba(0, 0, 0, 0.08);
  position: relative;
  overflow: hidden;
}

.card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 4px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  transform: scaleX(0);
  transition: transform 0.3s ease;
}

.card:hover::before {
  transform: scaleX(1);
}

.card:hover {
  transform: translateY(-8px);
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
  border-color: #e0e0e0;
}

.card-header {
  margin-bottom: 10px;
}

.card-title {
  font-size: 1.1rem;
  font-weight: 600;
  color: #2c3e50;
  margin-bottom: 8px;
}

.card-body p {
  color: #5a6c7d;
  font-size: 0.9rem;
  line-height: 1.4;
}

/* Update responsive design */
@media (max-width: 992px) {
  .card-container {
    grid-template-columns: repeat(2, 1fr);
    max-width: 700px;
    margin: 0 auto;
  }
}

@media (max-width: 768px) {
  .card-container {
    grid-template-columns: 1fr;
    gap: 15px;
    padding: 0 5px;
  }
  
  .card {
    margin: 0 auto;
    max-width: 350px;
  }
  
  .search-form {
    max-width: 90%;
    margin: 0 auto;
  }
  
  .welcome-section h1 {
    font-size: 1.5rem;
  }
  
  .welcome-section {
    padding: 10px 5px;
    margin-bottom: 20px;
  }
  
  .search-section {
    margin-bottom: 20px;
  }
}

/* Animations */
@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.card {
  animation: fadeIn 0.6s ease-out;
}

.search-form {
  animation: fadeIn 0.8s ease-out;
}
.card-title {
    position: relative;
    display: inline-block;
}

.notification-badge {
    position: absolute;
    top: -10px;
    right: -10px;
    background-color: #ff4444;
    color: white;
    border-radius: 50%;
    padding: 2px 6px;
    font-size: 12px;
    min-width: 20px;
    text-align: center;
}

/* Optional: You can add a different style for zero notifications */
.notification-badge:empty,
.notification-badge[data-count="0"] {
    background-color: #999; /* Gray color for zero notifications */
}
</style>
  <div class="container">
  <main>
    <!-- Welcome Section -->
    <section class="welcome-section">
      <h1>Welcome <?= htmlspecialchars($_SESSION['user_name']) ?>!</h1>
      <p>Find, reserve, and manage your parking spaces with ease.</p>
    </section>

    <!-- Elegant Search Section with Real-time Results -->
    <section class="search-section">
      <form class="search-form" id="searchForm">
        <input class="search-input-field" id="searchInput" placeholder="Search for parking areas..." type="text" name="query" autocomplete="off">
        <button type="submit" class="search-submit-button">🔍 Search</button>
      </form>
      
      <!-- Real-time Search Results -->
      <div class="search-results-container" id="searchResults" style="display: none;">
        <div class="search-results-header">
          <h3>Search Results</h3>
          <button class="close-results" onclick="closeSearchResults()">×</button>
        </div>
        <div class="search-results-list" id="searchResultsList">
          <!-- Results will be populated here -->
        </div>
      </div>
    </section>
    <!-- Quick Actions Section -->
<section class="quick-actions">
  <h1>Quick Actions</h1>
  <div class="card-container">

    <!-- Reserve a Spot Card -->
    <a href="reservation.php" class="card">
      <div class="card-header">
        <div class="card-title">Reserve a Spot</div>
      </div>
      <div class="card-body">
        <p>Book your parking spot in advance</p>
      </div>
    </a>

    <!-- Pay for Parking Card -->
    <a href="pay_parking.php" class="card">
      <div class="card-header">
        <div class="card-title">Check for Fares</div>
      </div>
      <div class="card-body">
        <p>Get Service In Low Budget</p>
      </div>
    </a>
<?php
// Function to check if any reservations are late and generate notifications
function checkLateReservations($connect) {
  $current_time = date('Y-m-d H:i:s'); // Current time

  // Query to find all reservations where the end time has passed and status is still 'reserved'
  $sql = "SELECT r.reservation_id, r.user_id, r.end_time, u.name 
          FROM Reservations r 
          JOIN Users u ON r.user_id = u.user_id
          WHERE r.end_time < ? AND r.status = 'reserved'";

  $stmt = $connect->prepare($sql);
  $stmt->bind_param('s', $current_time);
  $stmt->execute();
  $result = $stmt->get_result();

  while ($row = $result->fetch_assoc()) {
      // Generate the notification message
      $message = "You're late for your parking reservation. Reservation ended at " . $row['end_time'];

      // Insert the notification into the Notifications table
      $insert_sql = "INSERT INTO Notifications (user_id, reservation_id, message) 
                     VALUES (?, ?, ?)";
      $insert_stmt = $connect->prepare($insert_sql);
      $insert_stmt->bind_param('iis', $row['user_id'], $row['reservation_id'], $message);
      $insert_stmt->execute();
  }
}

// Call the function to check for late reservations and generate notifications
checkLateReservations($connect);

// Fetch unread notifications count
$user_id = $_SESSION['user_id']; // Assuming the user ID is stored in session
$sql = "SELECT COUNT(*) AS unread_notifications 
      FROM Notifications 
      WHERE user_id = ? AND read_status = 0"; // Assuming there's a 'read_status' field
$stmt = $connect->prepare($sql);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$unread_notifications = $row['unread_notifications'];
?>


<!-- Notification Card with Counter -->
<a href="notifications.php" class="card">
    <div class="card-header">
        <div class="card-title">
            Notifications
            <span class="notification-badge"><?php echo $unread_notifications; ?></span>
        </div>
    </div>
    <div class="card-body">
        <p>Stay updated with your parking status.</p>
    </div>
</a>
  </div>
</section>
  </main>
</div>

<script>
// Real-time Search Implementation
let searchTimeout;
let currentSearchRequest;

document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const searchForm = document.getElementById('searchForm');
    const searchResults = document.getElementById('searchResults');
    const searchResultsList = document.getElementById('searchResultsList');

    // Real-time search on input
    searchInput.addEventListener('input', function() {
        const query = this.value.trim();
        
        // Clear previous timeout
        clearTimeout(searchTimeout);
        
        if (query.length === 0) {
            hideSearchResults();
            return;
        }
        
        if (query.length < 2) {
            return; // Don't search for very short queries
        }
        
        // Debounce the search
        searchTimeout = setTimeout(() => {
            performSearch(query);
        }, 300); // Wait 300ms after user stops typing
    });

    // Handle form submission
    searchForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const query = searchInput.value.trim();
        if (query.length > 0) {
            // Redirect to search_areas.php for full results
            window.location.href = `search_areas.php?query=${encodeURIComponent(query)}`;
        }
    });

    // Hide results when clicking outside
    document.addEventListener('click', function(e) {
        if (!searchResults.contains(e.target) && !searchInput.contains(e.target)) {
            hideSearchResults();
        }
    });
});

function performSearch(query) {
    const searchResults = document.getElementById('searchResults');
    const searchResultsList = document.getElementById('searchResultsList');
    
    // Show loading spinner
    showLoadingSpinner();
    
    // Cancel previous request if it exists
    if (currentSearchRequest) {
        currentSearchRequest.abort();
    }
    
    // Create new request
    currentSearchRequest = new XMLHttpRequest();
    currentSearchRequest.open('POST', 'api_search.php', true);
    currentSearchRequest.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    
    currentSearchRequest.onreadystatechange = function() {
        if (this.readyState === 4) {
            if (this.status === 200) {
                try {
                    const response = JSON.parse(this.responseText);
                    if (response.error) {
                        showNoResults('Search failed. Please try again.');
                    } else {
                        displaySearchResults(response);
                    }
                } catch (e) {
                    showNoResults('Search failed. Please try again.');
                }
            } else {
                showNoResults('Search failed. Please try again.');
            }
        }
    };
    
    currentSearchRequest.send(`query=${encodeURIComponent(query)}`);
}

function showLoadingSpinner() {
    const searchResults = document.getElementById('searchResults');
    const searchResultsList = document.getElementById('searchResultsList');
    
    searchResults.style.display = 'block';
    searchResultsList.innerHTML = `
        <div class="loading-spinner">
            <div class="spinner"></div>
        </div>
    `;
}

function displaySearchResults(results) {
    const searchResults = document.getElementById('searchResults');
    const searchResultsList = document.getElementById('searchResultsList');
    
    searchResults.style.display = 'block';
    
    if (!results || results.length === 0) {
        showNoResults('No parking areas found for your search.');
        return;
    }
    
    let html = '';
    results.forEach(result => {
        html += `
            <div class="search-result-item">
                <div class="search-result-title">${result.area_name}</div>
                <div class="search-result-details">
                    Total slots: ${result.total_slots} | Available: ${result.available_slots}
                </div>
                <div class="search-result-actions">
                    <a href="reservation.php?pre_selected_area=${result.area_id}" class="reserve-btn">
                        Reserve Now
                    </a>
                </div>
            </div>
        `;
    });
    
    searchResultsList.innerHTML = html;
}


function showNoResults(message) {
    const searchResults = document.getElementById('searchResults');
    const searchResultsList = document.getElementById('searchResultsList');
    
    searchResults.style.display = 'block';
    searchResultsList.innerHTML = `
        <div class="no-results">${message}</div>
    `;
}

function hideSearchResults() {
    const searchResults = document.getElementById('searchResults');
    searchResults.style.display = 'none';
}

function closeSearchResults() {
    hideSearchResults();
    document.getElementById('searchInput').value = '';
}
</script>

  <?php include('../includes/footer.php');?>
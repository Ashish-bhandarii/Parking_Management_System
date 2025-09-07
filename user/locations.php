<?php
include('../includes/database.php');
include('../includes/header.php');
// Check if user is logged in and is a regular user
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'user') {
    header("Location: ../index.php");
    exit;
}
// Fetch all parking areas from the database
$query = "SELECT * FROM parking_areas";
$parking_areas = mysqli_query($connect, $query);
?>
<div class="locations-container">
    <h1>Parking Locations</h1>
    <div class="location-list">
        <?php while ($area = mysqli_fetch_assoc($parking_areas)): ?>
            <div class="location-item">
                <h2><?php echo htmlspecialchars($area['area_name']); ?></h2>
                <?php if (!empty($area['map_iframe_url'])): ?>
                    <div class="location-map" id="map_<?php echo $area['area_id']; ?>"></div>
                    <button class="view-larger-map" onclick="openLargeMap('<?php echo $area['area_id']; ?>', '<?php echo htmlspecialchars($area['area_name']); ?>', '<?php echo htmlspecialchars($area['map_iframe_url']); ?>')">
                        View Larger Map
                    </button>
                <?php endif; ?>
                <div class="location-stats">
                    <p><strong>Total Slots:</strong> <?php echo $area['total_slots']; ?></p>
                </div>
                <a href="reservation.php?pre_selected_area=<?php echo $area['area_id']; ?>" class="btn">Reserve Spot</a>
            </div>

            <!-- Modal for large map -->
            <div id="mapModal_<?php echo $area['area_id']; ?>" class="modal">
                <div class="modal-content">
                    <span class="close-modal" onclick="closeModal('<?php echo $area['area_id']; ?>')">&times;</span>
                    <h2><?php echo htmlspecialchars($area['area_name']); ?></h2>
                    <div id="largeMap_<?php echo $area['area_id']; ?>" class="large-map"></div>
                </div>
            </div>

            <script>
                // Initialize small map for this area
                (function() {
                    try {
                        const iframeUrl = "<?php echo htmlspecialchars($area['map_iframe_url']); ?>";
                        const markerMatch = iframeUrl.match(/marker=(-?\d+\.?\d*),(-?\d+\.?\d*)/);
                        if (markerMatch) {
                            const lat = parseFloat(markerMatch[1]);
                            const lng = parseFloat(markerMatch[2]);
                            
                            const smallMap = L.map('map_<?php echo $area['area_id']; ?>', {
                                scrollWheelZoom: false
                            }).setView([lat, lng], 16);
                            
                            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                                attribution: '© OpenStreetMap contributors'
                            }).addTo(smallMap);
                            
                            L.marker([lat, lng]).addTo(smallMap)
                                .bindPopup("<?php echo htmlspecialchars($area['area_name']); ?>");
                        }
                    } catch (e) {
                        console.error('Error initializing small map:', e);
                    }
                })();
            </script>
        <?php endwhile; ?>
    </div>
</div>

<script>
    let activeLargeMap = null;

    function openLargeMap(areaId, areaName, mapUrl) {
        const modal = document.getElementById(`mapModal_${areaId}`);
        modal.style.display = "block";

        const markerMatch = mapUrl.match(/marker=(-?\d+\.?\d*),(-?\d+\.?\d*)/);
        if (!markerMatch) return;

        const lat = parseFloat(markerMatch[1]);
        const lng = parseFloat(markerMatch[2]);

        setTimeout(() => {
            if (activeLargeMap) {
                activeLargeMap.remove();
            }

            activeLargeMap = L.map(`largeMap_${areaId}`).setView([lat, lng], 17);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            }).addTo(activeLargeMap);
            
            L.marker([lat, lng]).addTo(activeLargeMap)
                .bindPopup(areaName);

            activeLargeMap.invalidateSize();
        }, 100);
    }

    function closeModal(areaId) {
        const modal = document.getElementById(`mapModal_${areaId}`);
        modal.style.display = "none";
        
        if (activeLargeMap) {
            activeLargeMap.remove();
            activeLargeMap = null;
        }
    }

    window.onclick = function(event) {
        if (event.target.classList.contains('modal')) {
            const areaId = event.target.id.replace('mapModal_', '');
            closeModal(areaId);
        }
    }
</script>
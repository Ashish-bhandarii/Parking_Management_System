<?php
include '../includes/database.php';
include '../includes/header.php';

// Modified query to fetch area details, fare rates by vehicle type and category
$sql = "SELECT
            pa.area_name,
            pa.total_slots,
            pa.reserved_slots,
            pa.available_slots,
            vt.type_name,
            vc.category_name,
            fr.hourly_rate
        FROM
            parking_areas pa
        JOIN
            FareRates fr ON pa.area_id = fr.area_id
        JOIN
            vehicle_types vt ON fr.type_id = vt.type_id
        JOIN
            vehicle_categories vc ON fr.category_id = vc.category_id
        ORDER BY 
            pa.area_name, vt.type_name, vc.category_name";
$result = $connect->query($sql);

// Group results by parking area
$parkingAreas = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        if (!isset($parkingAreas[$row['area_name']])) {
            $parkingAreas[$row['area_name']] = [
                'total_slots' => $row['total_slots'],
                'reserved_slots' => $row['reserved_slots'],
                'available_slots' => $row['available_slots'],
                'rates' => []
            ];
        }
        $parkingAreas[$row['area_name']]['rates'][] = [
            'type_name' => $row['type_name'],
            'category_name' => $row['category_name'],
            'hourly_rate' => $row['hourly_rate']
        ];
    }
}
?>

<?php if (!empty($parkingAreas)): ?>
    <div class="container">
        <h2 class="text-center mb-4">Fare Rates and Slots for All Parking Areas</h2>
        <div class="card-container">
            <?php foreach ($parkingAreas as $areaName => $areaData): ?>
                <div class="parking-card">
                    <div class="card-header">
                        <h3><?php echo $areaName; ?></h3>
                    </div>
                    <div class="card-body">
                        <div class="info-item">
                            <span class="label">Total Slots:</span>
                            <span class="value"><?php echo $areaData['total_slots']; ?></span>
                        </div>
                        <!-- Group rates by vehicle type -->
                        <?php 
                        $groupedRates = [];
                        foreach ($areaData['rates'] as $rate) {
                            if (!isset($groupedRates[$rate['type_name']])) {
                                $groupedRates[$rate['type_name']] = [];
                            }
                            $groupedRates[$rate['type_name']][] = $rate;
                        }
                        
                        foreach ($groupedRates as $typeName => $typeRates): 
                        ?>
                            <div class="vehicle-type-section">
                                <h4 class="type-header"><?php echo ucwords(str_replace('_', ' ', $typeName)); ?></h4>
                                <?php foreach ($typeRates as $rate): ?>
                                    <div class="info-item rate-item">
                                        <span class="label"><?php echo $rate['category_name']; ?>:</span>
                                        <span class="value">NPR <?php echo number_format($rate['hourly_rate'], 2); ?>/hr</span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php else: ?>
    <div class="no-data">
        <p>No data available for parking areas.</p>
    </div>
<?php endif; ?>

<?php $connect->close(); ?>

<style>
.container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 20px;
}

.text-center {
    text-align: center;
}

.mb-4 {
    margin-bottom: 2rem;
}

.card-container {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 1rem;
    padding: 0.5rem;
}

.parking-card {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    transition: transform 0.2s ease-in-out;
    border: 1px solid #e0e0e0;
    overflow: hidden;
    min-width: 200px;
}

.parking-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
}

.card-header {
    background: #2c3e50;
    color: white;
    padding: 0.75rem;
    text-align: center;
}

.card-header h3 {
    margin: 0;
    font-size: 1rem;
    font-weight: 600;
}

.card-body {
    padding: 1rem;
}

.info-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.5rem;
    padding: 0.5rem;
    border-bottom: 1px solid #eee;
    font-size: 0.9rem;
}

.info-item:last-child {
    border-bottom: none;
}

.rate-item {
    background-color: white;
    margin: 0.5rem 0;
    padding: 0.5rem;
    border-radius: 4px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
}

.rate-item .label {
    font-size: 0.85rem;
}

.rate-item .value {
    color: #28a745;
}

.vehicle-type-section {
    margin-top: 1rem;
    padding: 0.5rem;
    background-color: #f8f9fa;
    border-radius: 6px;
}

.type-header {
    color: #2c3e50;
    font-size: 0.95rem;
    margin: 0 0 0.5rem 0;
    padding-bottom: 0.5rem;
    border-bottom: 1px solid #dee2e6;
}

.no-data {
    text-align: center;
    padding: 2rem;
    background: #f8f9fa;
    border-radius: 8px;
    margin: 1rem;
}

/* Responsive breakpoints */
@media (max-width: 1400px) {
    .card-container {
        grid-template-columns: repeat(4, 1fr);
    }
}

@media (max-width: 1200px) {
    .card-container {
        grid-template-columns: repeat(3, 1fr);
    }
}

@media (max-width: 900px) {
    .card-container {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 600px) {
    .card-container {
        grid-template-columns: 1fr;
    }
}
</style>
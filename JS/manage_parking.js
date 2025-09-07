document.addEventListener('DOMContentLoaded', function() {
    // Form validation for both add and edit forms
    const parkingForm = document.getElementById('parkingAreaForm');
    if (parkingForm) {
        parkingForm.addEventListener('submit', function(event) {
            const areaName = document.getElementById('area_name').value.trim();
            const totalSlots = parseInt(document.getElementById('total_slots').value);
            const mapIframeUrl = document.getElementById('map_iframe_url').value.trim();

            // Check if area_name is valid
            if (areaName.length > 40 || !/^[A-Za-z\s]+$/.test(areaName)) {
                alert("Parking Area Name must be less than 40 characters and contain only letters and spaces.");
                event.preventDefault();
                return;
            }

            // Check if total_slots is valid
            if (totalSlots <= 0) {
                alert("Total slots must be greater than 0. ");
                event.preventDefault();
                return;
            }

            // Check if location is selected
            if (!mapIframeUrl) {
                alert("Please select a location for the parking area.");
                event.preventDefault();
                return;
            }
        });
    }

    // Style for required fields
    const selectBtn = document.getElementById('selectLocationBtn');
    if (selectBtn) {
        selectBtn.classList.add('required-field');
        
        // Only add asterisk if it doesn't already exist
        if (!selectBtn.querySelector('.required')) {
            const asterisk = document.createElement('span');
            asterisk.className = 'required';
            asterisk.textContent = ' *';
            selectBtn.appendChild(asterisk);
        }
    }

    // Update location preview after selection (for both add and edit forms)
    const updateLocationPreview = function(iframeUrl) {
        const previewDiv = document.getElementById('selectedLocationPreview');
        if (previewDiv && iframeUrl) {
            previewDiv.innerHTML = `
                <small>Selected location:</small><br>
                <iframe width="100%" height="200" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" 
                    src="${iframeUrl}">
                </iframe>
            `;
        }
    };

    // Initialize map preview if URL exists (for edit form)
    const existingMapUrl = document.getElementById('map_iframe_url').value;
    if (existingMapUrl) {
        updateLocationPreview(existingMapUrl);
    }
});

// Update the confirmLocation function in your map modal
const confirmBtn = document.getElementById('confirmLocation');
if (confirmBtn) {
    confirmBtn.onclick = function() {
        if (selectedLocation) {
            const lat = selectedLocation.lat;
            const lng = selectedLocation.lng;
            const zoom = map.getZoom();
            
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





// Get modal elements
const modal = document.getElementById('mapModal');
const closeBtn = document.querySelector('.close');
const selectLocationBtn = document.getElementById('selectLocationBtn');
let map;
let selectedLocation = null;
let marker = null;

// Initialize map with OpenStreetMap
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

// Open modal when select location button is clicked
if (selectLocationBtn) {
    selectLocationBtn.onclick = function() {
        modal.style.display = "block";
        // Initialize map after modal is visible
        setTimeout(() => {
            if (!map) {
                initMap();
            }
            map.invalidateSize();
        }, 100);
    };
}

// Close modal when X is clicked
if (closeBtn) {
    closeBtn.onclick = function() {
        modal.style.display = "none";
    };
}

// Close modal when clicking outside
window.onclick = function(event) {
    if (event.target == modal) {
        modal.style.display = "none";
    }
};

// Search location functionality
const searchBtn = document.getElementById('searchBtn');
const searchInput = document.getElementById('searchLocation');

if (searchBtn && searchInput) {
    searchBtn.onclick = function() {
        const searchQuery = searchInput.value;
        if (searchQuery) {
            // Use OpenStreetMap Nominatim API for geocoding
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
    };

    // Add enter key support for search
    searchInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            searchBtn.click();
        }
    });
}

// Update the preview function
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




// <script>
// // Global variables for map functionality
// let map;
// let selectedLocation = null;
// let marker = null;
// const modal = document.getElementById('mapModal');
// const closeBtn = document.querySelector('.close');
// const selectLocationBtn = document.getElementById('selectLocationBtn');
// const confirmBtn = document.getElementById('confirmLocation');
// const searchBtn = document.getElementById('searchBtn');
// const searchInput = document.getElementById('searchLocation');

// // Function to initialize the map
// function initMap() {
//     map = L.map('map').setView([14.5995, 120.9842], 13); // Default view of Manila
//     L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
//         attribution: '© OpenStreetMap contributors'
//     }).addTo(map);

//     // Add click event to map
//     map.on('click', function(e) {
//         if (marker) {
//             map.removeLayer(marker);
//         }
//         selectedLocation = e.latlng;
//         marker = L.marker(e.latlng).addTo(map);
//     });
// }

// // Function to update location preview
// function updateLocationPreview(iframeUrl) {
//     const previewDiv = document.getElementById('selectedLocationPreview');
//     if (previewDiv && iframeUrl) {
//         previewDiv.innerHTML = `
//             <small>Selected location:</small><br>
//             <iframe width="100%" height="200" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" 
//                 src="${iframeUrl}">
//             </iframe>
//         `;
//     }
// }

// // Function to handle location search
// function searchLocation(searchQuery) {
//     if (searchQuery) {
//         fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(searchQuery)}`)
//             .then(response => response.json())
//             .then(data => {
//                 if (data.length > 0) {
//                     const location = data[0];
//                     const lat = parseFloat(location.lat);
//                     const lon = parseFloat(location.lon);
                    
//                     if (marker) {
//                         map.removeLayer(marker);
//                     }
                    
//                     selectedLocation = { lat: lat, lng: lon };
//                     marker = L.marker([lat, lon]).addTo(map);
//                     map.setView([lat, lon], 16);
//                 } else {
//                     alert('Location not found');
//                 }
//             })
//             .catch(error => {
//                 console.error('Error searching location:', error);
//                 alert('Error searching location');
//             });
//     }
// }

// // Main initialization when document is loaded
// document.addEventListener('DOMContentLoaded', function() {
//     // Form validation
//     const parkingForm = document.getElementById('parkingAreaForm');
//     if (parkingForm) {
//         parkingForm.addEventListener('submit', function(event) {
//             const areaName = document.getElementById('area_name').value.trim();
//             const totalSlots = parseInt(document.getElementById('total_slots').value);
//             const mapIframeUrl = document.getElementById('map_iframe_url').value.trim();

//             // Check if area_name is valid
//             if (areaName.length > 40 || !/^[A-Za-z\s]+$/.test(areaName)) {
//                 alert("Parking Area Name must be less than 40 characters and contain only letters and spaces.");
//                 event.preventDefault();
//                 return;
//             }

//             // Check if total_slots is valid
//             if (totalSlots <= 0) {
//                 alert("Total slots must be greater than 0.");
//                 event.preventDefault();
//                 return;
//             }

//             // Check if location is selected
//             if (!mapIframeUrl) {
//                 alert("Please select a location for the parking area.");
//                 event.preventDefault();
//                 return;
//             }
//         });
//     }

//     // Style for required fields
//     if (selectLocationBtn) {
//         selectLocationBtn.classList.add('required-field');
//         if (!selectLocationBtn.querySelector('.required')) {
//             const asterisk = document.createElement('span');
//             asterisk.className = 'required';
//             asterisk.textContent = ' *';
//             selectLocationBtn.appendChild(asterisk);
//         }
//     }

//     // Initialize map preview if URL exists
//     const existingMapUrl = document.getElementById('map_iframe_url')?.value;
//     if (existingMapUrl) {
//         updateLocationPreview(existingMapUrl);
//     }

//     // Modal open handler
//     if (selectLocationBtn) {
//         selectLocationBtn.onclick = function() {
//             modal.style.display = "block";
//             setTimeout(() => {
//                 if (!map) {
//                     initMap();
//                 }
//                 map.invalidateSize();
//             }, 100);
//         };
//     }

//     // Modal close handlers
//     if (closeBtn) {
//         closeBtn.onclick = function() {
//             modal.style.display = "none";
//         };
//     }

//     window.onclick = function(event) {
//         if (event.target == modal) {
//             modal.style.display = "none";
//         }
//     };

//     // Search functionality
//     if (searchBtn && searchInput) {
//         searchBtn.onclick = function() {
//             searchLocation(searchInput.value);
//         };

//         searchInput.addEventListener('keypress', function(e) {
//             if (e.key === 'Enter') {
//                 searchBtn.click();
//             }
//         });
//     }

//     // Confirm location handler
//     if (confirmBtn) {
//         confirmBtn.onclick = function() {
//             if (selectedLocation) {
//                 const lat = selectedLocation.lat;
//                 const lng = selectedLocation.lng;
                
//                 // Generate OpenStreetMap iframe URL
//                 const iframe_url = `https://www.openstreetmap.org/export/embed.html?bbox=${lng-0.01},${lat-0.01},${lng+0.01},${lat+0.01}&layer=mapnik&marker=${lat},${lng}`;
                
//                 document.getElementById('map_iframe_url').value = iframe_url;
//                 updateLocationPreview(iframe_url);
                
//                 modal.style.display = "none";
//             } else {
//                 alert('Please select a location first');
//             }
//         };
//     }
// });
// </script>
// <style>
// .form-group {
//     position: relative;
//     margin-bottom: 15px;
// }

// .required {
//     color: red;
//     margin-left: 5px;
// }

// .required-field {
//     position: relative;
// }

// .map-selection-container {
//     margin-bottom: 15px;
// }

// .error-message {
//     color: red;
//     margin-bottom: 15px;
//     padding: 10px;
//     background-color: #ffe6e6;
//     border: 1px solid #ff9999;
//     border-radius: 4px;
// }

// .success-message {
//     color: green;
//     margin-bottom: 15px;
//     padding: 10px;
//     background-color: #e6ffe6;
//     border: 1px solid #99ff99;
//     border-radius: 4px;
// }

// #selectedLocationPreview {
//     margin-top: 10px;
// }

// #selectedLocationPreview iframe {
//     border: 1px solid #ddd;
//     border-radius: 4px;
// }

// .btn-select-location {
//     background-color: #4CAF50;
//     color: white;
//     padding: 10px 15px;
//     border: none;
//     border-radius: 4px;
//     cursor: pointer;
// }

// .btn-select-location:hover {
//     background-color: #45a049;
// }
// </style>
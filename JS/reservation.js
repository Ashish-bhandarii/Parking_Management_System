$(document).ready(function() {
    // Cache frequently used elements
    const form = $('#reservationForm');
    const submitButton = $('#submitButton');
    const loadingSpinner = submitButton.find('.loading');
    const messageContainer = $('#dynamicMessageContainer');

    // Initialize date/time pickers with minimum dates
    const now = new Date();
    $('#start_time').attr('min', now.toISOString().slice(0, 16));
    $('#end_time').attr('min', now.toISOString().slice(0, 16));

    // Function to show dynamic messages
    function showMessage(message, type, duration = 5000) {
        const messageId = Date.now();
        const messageHtml = `
            <div class="message ${type}" id="message-${messageId}" style="display: block;">
                ${message}
                <button onclick="document.getElementById('message-${messageId}').remove()" 
                        style="float: right; background: none; border: none; cursor: pointer;">
                    ×
                </button>
            </div>
        `;
        messageContainer.append(messageHtml);

        if (duration) {
            setTimeout(() => {
                $(`#message-${messageId}`).fadeOut('slow', function() {
                    $(this).remove();
                });
            }, duration);
        }
    }

    // Function to format date for display
    function formatDateTime(dateString) {
        return new Date(dateString).toLocaleString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    // Function to check slot availability
    function checkSlotAvailability() {
        const area_id = $('#area_id').val();
        const slot_id = $('#slot_id').val();
        const start_time = $('#start_time').val();
        const end_time = $('#end_time').val();

        if (!area_id || !slot_id || !start_time || !end_time) {
            return Promise.resolve({ available: true });
        }

        return $.ajax({
            url: 'reservation.php',
            type: 'POST',
            data: {
                check_slot_availability: true,
                area_id: area_id,
                slot_id: slot_id,
                start_time: start_time,
                end_time: end_time
            },
            dataType: 'json'
        });
    }

    // Function to load available slots
    function loadAvailableSlots(area_id) {
        if (!area_id) {
            $('#slot_id').html('<option value="" disabled selected>Select a slot</option>');
            return;
        }

        $.ajax({
            url: 'get_available_slots.php',
            type: 'POST',
            data: { area_id: area_id },
            dataType: 'json',
            beforeSend: function() {
                $('#slot_id').html('<option value="" disabled selected>Loading slots...</option>');
            },
            success: function(data) {
                let options = '<option value="" disabled selected>Select a slot</option>';
                data.forEach(slot => {
                    options += `<option value="${slot.slot_id}">${slot.slot_name}</option>`;
                });
                $('#slot_id').html(options);
            },
            error: function(xhr, status, error) {
                console.error("AJAX error:", status, error);
                showMessage('Error fetching available slots. Please try again.', 'error');
                $('#slot_id').html('<option value="" disabled selected>Select a slot</option>');
            }
        });
    }

    // // Function to load active reservations
    // function loadActiveReservations() {
    //     $.ajax({
    //         url: 'get_active_reservations.php',
    //         type: 'GET',
    //         beforeSend: function() {
    //             $('.reservation-section').find('table tbody').html(
    //                 '<tr><td colspan="5" class="text-center">Loading...</td></tr>'
    //             );
    //         },
    //         success: function(response) {
    //             $('.reservation-section').html(response);
    //         },
    //         error: function() {
    //             showMessage("Failed to refresh reservations list.", "error");
    //         }
    //     });
    // }

    // Validate vehicle number format
    function isValidVehicleNumber(number) {
        const pattern = /^[A-Za-z]{2} \d{1,2} [A-Za-z]{2} \d{1,4}$/;
        return pattern.test(number);
    }

    // Validate reservation times
    function validateReservationTimes(startTime, endTime) {
        const now = new Date();
        const start = new Date(startTime);
        const end = new Date(endTime);
        const duration = (end - start) / (1000 * 60); // Duration in minutes

        if (start <= now) {
            showMessage("Start time must be in the future.", "error");
            return false;
        }

        if (end <= start) {
            showMessage("End time must be after the start time.", "error");
            return false;
        }

        if (duration < 30) {
            showMessage("Minimum reservation duration is 30 minutes.", "error");
            return false;
        }

        return true;
    }

    // Event Handlers

    // Handle parking area selection
    $('#area_id').change(function() {
        loadAvailableSlots($(this).val());
    });

    // Handle date/time changes
    $('#start_time').change(function() {
        const startTime = new Date($(this).val());
        const minEndTime = new Date(startTime.getTime() + 30 * 60000); // Add 30 minutes
        $('#end_time').attr('min', minEndTime.toISOString().slice(0, 16));
    });

    // Real-time slot availability check
    $('#slot_id, #start_time, #end_time').change(function() {
        if ($('#area_id').val() && $('#slot_id').val() && $('#start_time').val() && $('#end_time').val()) {
            checkSlotAvailability()
                .then(response => {
                    if (!response.available) {
                        const conflicts = response.conflicts.map(conflict => 
                            `${formatDateTime(conflict.start_time)} to ${formatDateTime(conflict.end_time)}`
                        ).join('\n');
                        showMessage(`Slot is already reserved during:\n${conflicts}`, "error");
                        $('#slot_id').val('');
                    }
                })
                .catch(error => {
                    showMessage("Error checking slot availability.", "error");
                });
        }
    });

    // Form submission handler
    form.submit(function(event) {
        event.preventDefault();
        messageContainer.empty();

        // Basic validation
        const startTime = $('#start_time').val();
        const endTime = $('#end_time').val();
        const vehicleNumber = $('#vehicle_number').val();

        if (!validateReservationTimes(startTime, endTime)) {
            return false;
        }

        if (!isValidVehicleNumber(vehicleNumber)) {
            showMessage("Please enter a valid vehicle number (e.g., BA 1 PA 1234).", "error");
            return false;
        }

        // Disable form and show loading
        submitButton.prop('disabled', true);
        loadingSpinner.show();

        // Check final availability and submit
        checkSlotAvailability()
            .then(response => {
                if (!response.available) {
                    throw new Error('Selected slot is no longer available.');
                }
                
                return $.ajax({
                    url: form.attr('action') || window.location.href,
                    type: 'POST',
                    data: form.serialize(),
                    dataType: 'json'
                });
            })
            .then(response => {
                if (response.success) {
                    showMessage("Reservation submitted successfully!", "success");
                    form[0].reset();
                    loadActiveReservations();
                } else {
                    showMessage(response.message || "Failed to submit reservation.", "error");
                }
            })
            .catch(error => {
                showMessage(error.message || "An error occurred while processing your request.", "error");
            })
            .finally(() => {
                submitButton.prop('disabled', false);
                loadingSpinner.hide();
            });
    });

    // Initialize active reservations
    // loadActiveReservations();
});
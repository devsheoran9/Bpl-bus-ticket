<?php
 
?>
<script src="assets/js/jquery-3.6.0.min.js"></script>
<script src="assets/bootstrap/js/bootstrap.min.js"></script>
<script src="assets/js/notify.js"></script>
<script src="assets/js/parsley.min.js"></script>
<script src="assets/ladda/spin.min.js"></script>
<script src="assets/ladda/ladda.min.js"></script>
<!-- REMOVED SweetAlert v1 which caused the conflict -->
<!-- <script src="assets/sweetalert/sweetalert.min.js?145"></script> --> 
<!-- ADDED SweetAlert2 (modern version) -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> 
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>
<!-- jQuery UI (REQUIRED for draggable/droppable) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.13.2/jquery-ui.min.js"></script>

<script>
    // General form submission handler (kept for other forms, if any)
    $(document).on('submit','form.data-form',function(e) {
        e.preventDefault();
        // If this is the dummy form on manage_seats.php, do nothing.
        // The Ladda button on manage_seats.php is handled separately.
        if ($(this).find('input[name="action"]').val() === 'dummy_save_action') {
             return; 
        }

        var action = $(this).attr('action');
        var m_form_data = new FormData(this);
        if($(this).parsley().isValid()){
            var submitBtn = $(this).find('.submit-btn')[0];
            var l;
            if (submitBtn) {
                l = Ladda.create(submitBtn);
                l.start();
            }

            $.ajax({
                type: "POST",
                url: action,
                data: m_form_data,
                cache: false,
                dataType:"json",
                enctype: 'multipart/form-data',
                processData: false,
                contentType: false,
                success: function(data){
                    setTimeout(function(){
                        if (l) l.stop();
                    }, 2500);
                    var goTo ='';
                    var notify_type = data.notif_type; 
                    var notify_title = data.notif_title;
                    var notify_desc = data.notif_desc;
                    var notif_popup = data.notif_popup;

                    // Use SweetAlert2 (Swal.fire)
                    if(notif_popup === 'true'){
                        Swal.fire({
                            title: notify_title,
                            text: notify_desc,
                            icon: notify_type 
                        });
                    }else{
                        $.notify({
                            title: notify_title,
                            message: notify_desc
                        },{
                            type: notify_type
                        });
                    }

                    goTo = data.goTo;
                    if(data.res === 'true' && goTo !== ''){
                        window.setTimeout(function(){
                            if(goTo === '469bba0a564235dfceede42db14f17b0'){ // This magic string implies a special 'go back' action
                                history.go(-1);
                            }else {
                                window.location.href = goTo;
                            }
                        }, 1000);
                        // Do not reset form if redirecting to seat management, as we need bus_id
                        if (!goTo.includes('manage_seats.php')) {
                            $(this)[0].reset();
                            if ($(this).parsley()) {
                                $(this).parsley().reset();
                            }
                        }
                    } else if (data.res === 'true') {
                        // If success but no redirect, just show notification and reset form
                        $(this)[0].reset();
                        if ($(this).parsley()) {
                            $(this).parsley().reset();
                        }
                    }
                },
                error: function (jqXHR, exception) {
                    var msg = '';
                    if (jqXHR.status === 0) {
                        msg = "Network Problem. Please check your internet connection.";
                    } else if (jqXHR.status == 404) {
                        msg = "Requested page not found. [404]";
                    } else if (jqXHR.status == 500) {
                        msg = "Internal Server Error. [500]";
                    } else if (exception === 'parsererror') {
                        msg = "Error parsing JSON response. The server response might be malformed.";
                    } else if (exception === 'timeout') {
                        msg = "Request timed out.";
                    } else if (exception === 'abort') {
                        msg = "Request aborted.";
                    } else {
                        msg = "Uncaught Error.\n" + jqXHR.responseText;
                    }
                    if (l) l.stop();
                    $.notify({
                        title: '(Please Retry) - ',
                        message: msg
                    },{
                        type: 'warning'
                    });
                }
            });
        }
    })
</script>

<script>
    ///sidebar
    document.addEventListener('DOMContentLoaded', () => {
        const wrapper = document.getElementById('wrapper');
        const toggle  = document.getElementById('sidebarToggle');
        const icon    = document.getElementById('sidebarToggleIcon');

        let overlay = document.getElementById('sidebarOverlay');
        if (!overlay) {
            overlay = document.createElement('div');
            overlay.id = 'sidebarOverlay';
            wrapper.appendChild(overlay);
        }

        function handleToggleFor1080() {
            if (window.innerWidth >= 1080) {
                wrapper.classList.add('toggled');
            } else {
                wrapper.classList.remove('toggled');
            }
        }

        function updateToggleIcon() {
            if (!icon) return;
            if (wrapper.classList.contains('toggled')) {
                icon.classList.remove('fa-bars');
                icon.classList.add('fa-times');
            } else {
                icon.classList.remove('fa-times');
                icon.classList.add('fa-bars');
            }
        }

        function handleResize() {
            handleToggleFor1080();
            updateToggleIcon();
        }

        handleResize(); // Call on initial load

        if (toggle) { // Ensure toggle element exists
            toggle.addEventListener('click', () => {
                wrapper.classList.toggle('toggled');
                updateToggleIcon();
            });
        }

        if (overlay) { // Ensure overlay element exists
            overlay.addEventListener('click', () => {
                if (wrapper.classList.contains('toggled')) {
                    wrapper.classList.remove('toggled');
                    updateToggleIcon();
                }
            });
        }

        window.addEventListener('resize', handleResize);
    });

    function setupFileInput(inputElement, previewContainerId, fileNameDisplayId, isMultiple = false) {
        const previewContainer = $(`#${previewContainerId}`);
        const fileNameDisplay = $(`#${fileNameDisplayId}`);

        if (!inputElement.length || !previewContainer.length || !fileNameDisplay.length) {
            console.warn(`File input setup elements not found for: ${inputElement.selector}`);
            return; // Exit if elements don't exist
        }

        if (previewContainer.children().length === 0) {
            previewContainer.addClass('empty-state');
        } else {
            previewContainer.removeClass('empty-state');
        }

        $(inputElement).on('change', function(event) {
            previewContainer.html('');
            previewContainer.removeClass('empty-state');

            if (event.target.files && event.target.files.length > 0) {
                if (!isMultiple) {
                    const file = event.target.files[0];
                    fileNameDisplay.text(file.name);

                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const img = $('<img>').attr('src', e.target.result).addClass('image-preview');
                        const clearButton = $('<button type="button" class="clear-featured-image"><i class="fas fa-times"></i></button>');
                        
                        previewContainer.append(img).append(clearButton);

                        clearButton.on('click', function() {
                            inputElement.val('');
                            fileNameDisplay.text('No file chosen');
                            previewContainer.html('').addClass('empty-state');
                        });
                    };
                    reader.readAsDataURL(file);
                } else {
                    fileNameDisplay.text(`${event.target.files.length} files chosen`);
                    Array.from(event.target.files).forEach((file, index) => {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            const itemDiv = $('<div>').addClass('image-preview-item');
                            const img = $('<img>').attr('src', e.target.result);
                            const removeButton = $('<button type="button" class="remove-image-button"><i class="fas fa-times"></i></button>');
                            
                            itemDiv.append(img).append(removeButton);
                            previewContainer.append(itemDiv);

                            removeButton.on('click', function() {
                                $(this).parent().remove();
                                if (previewContainer.children().length === 0) {
                                    fileNameDisplay.text('No files chosen');
                                    inputElement.val('');
                                    previewContainer.addClass('empty-state');
                                }
                            });
                        };
                        reader.readAsDataURL(file);
                    });
                }
            } else {
                fileNameDisplay.text(isMultiple ? 'No files chosen' : 'No file chosen');
                previewContainer.addClass('empty-state');
            }
        });
    }

    $(document).ready(function() {
        // These elements might not exist on all pages, so check if they exist before calling setupFileInput
        if ($('#featured_image').length) {
            setupFileInput($('#featured_image'), 'featured_image_preview', 'featured_image_name', false);
        }
        if ($('#multiple_images').length) {
            setupFileInput($('#multiple_images'), 'multiple_images_preview', 'multiple_images_name', true);
        }

        if ($('#long_description').length) {
            $('#long_description').summernote({
                placeholder: 'Write your blog content here...',
                tabsize: 2,
                height: 300,
                toolbar: [
                    ['style', ['style']],
                    ['font', ['bold', 'italic', 'underline', 'clear']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['table', ['table']],
                    ['insert', ['link', 'picture', 'video']],
                    ['view', ['fullscreen', 'codeview', 'help']]
                ]
            });
        }
    });

</script>

<!-- Custom Seat Management JavaScript (ONLY for manage_seats.php) -->
<script>
    // This script should only run on manage_seats.php
    if (window.location.pathname.includes('manage_seats.php')) {
        $(document).ready(function() {
            const busId = $('#bus_id_for_js').val(); // Get bus_id from dedicated hidden input
            let currentSeats = []; // Array to hold all seat objects fetched from DB
            let selectedSeatElement = null; // Stores the jQuery object of the currently selected seat DIV
            const GRID_SIZE = 20; // Must match --grid-size in CSS for visual grid

            let seatCodeCounter = { // For generating unique seat_code for new seats
                LOWER_SEATER: 1, LOWER_SLEEPER: 1, LOWER_AISLE: 1,
                UPPER_SEATER: 1, UPPER_SLEEPER: 1, UPPER_AISLE: 1
            };

            // Ladda button for dynamic save notification
            const dynamicSaveLadda = Ladda.create($('#dynamicSaveNotificationBtn')[0]);
            $('#dynamicSaveNotificationBtn').on('click', function() {
                dynamicSaveLadda.start();
                setTimeout(function() {
                    dynamicSaveLadda.stop();
                    $.notify({
                        title: 'Info',
                        message: 'All changes have been saved automatically to the database.'
                    }, {
                        type: 'info'
                    });
                }, 1000);
            });


            function initializeSeatCodeCounter() {
                seatCodeCounter = {
                    LOWER_SEATER: 1, LOWER_SLEEPER: 1, LOWER_AISLE: 1,
                    UPPER_SEATER: 1, UPPER_SLEEPER: 1, UPPER_AISLE: 1
                };
                // Ensure unique seat codes by finding the max existing number for each type/deck
                currentSeats.forEach(seat => {
                    const deckPrefix = seat.deck.substring(0,1); // L or U
                    if (seat.seat_type === 'SEATER' && seat.seat_code.startsWith(deckPrefix + 'S')) {
                        const num = parseInt(seat.seat_code.replace(deckPrefix + 'S', ''));
                        if (!isNaN(num) && num >= seatCodeCounter[seat.deck + '_SEATER']) seatCodeCounter[seat.deck + '_SEATER'] = num + 1;
                    } else if (seat.seat_type === 'SLEEPER' && seat.seat_code.startsWith(deckPrefix + 'P')) {
                        const num = parseInt(seat.seat_code.replace(deckPrefix + 'P', ''));
                        if (!isNaN(num) && num >= seatCodeCounter[seat.deck + '_SLEEPER']) seatCodeCounter[seat.deck + '_SLEEPER'] = num + 1;
                    } else if (seat.seat_type === 'AISLE' && seat.seat_code.startsWith(deckPrefix + 'G')) {
                        const num = parseInt(seat.seat_code.replace(deckPrefix + 'G', ''));
                        if (!isNaN(num) && num >= seatCodeCounter[seat.deck + '_AISLE']) seatCodeCounter[seat.deck + '_AISLE'] = num + 1;
                    }
                });
            }

            function updateSeatCounts() {
                let total = 0, seater = 0, sleeper = 0, aisle = 0, driver = 0;
                currentSeats.forEach(seat => {
                    total++;
                    switch(seat.seat_type) {
                        case 'SEATER': seater++; break;
                        case 'SLEEPER': sleeper++; break;
                        case 'AISLE': aisle++; break;
                        case 'DRIVER': driver++; break;
                    }
                });
                $('#total_seat_count').text(total);
                $('#seater_count').text(seater);
                $('#sleeper_count').text(sleeper);
                $('#aisle_count').text(aisle);
                $('#driver_count').text(driver);
            }

            function loadSeatsForBus() {
                if (!busId || busId === 'N/A') {
                    $.notify({ title: 'Error', message: 'Invalid Bus ID. Cannot load seats.' }, { type: 'danger' });
                    renderSeats();
                    updateSeatCounts();
                    return;
                }

                $.ajax({
                    url: 'function/backend/bus_actions.php', // Ensure this path is correct
                    type: 'GET',
                    dataType: 'json',
                    data: { action: 'get_bus_seats', bus_id: busId },
                    success: function(response) {
                        if (response.res === 'true' && response.seats) {
                            currentSeats = response.seats;
                        } else {
                            $.notify({ title: 'Info', message: 'No existing seats found for this bus. Starting with a blank layout.' }, { type: 'info' });
                            currentSeats = [];
                        }
                        initializeSeatCodeCounter();
                        renderSeats();
                    },
                    error: function(xhr, status, error) {
                        $.notify({ title: 'Error', message: 'Failed to load seats from server: ' + status + ' ' + error + ". Server Response: " + xhr.responseText }, { type: 'danger' });
                        console.error("AJAX error loading seats:", xhr.responseText);
                        currentSeats = [];
                        initializeSeatCodeCounter();
                        renderSeats(); // Still render to show empty decks
                    }
                });
            }

            function renderSeats() {
                $('#lower_deck_container').empty();
                $('#upper_deck_container').empty();

                currentSeats.forEach(seatData => {
                    const containerId = `${seatData.deck.toLowerCase()}_deck_container`;
                    const container = $(`#${containerId}`);
                    const seatElement = createSeatElement(seatData);
                    
                    // Ensure coordinates snap to grid
                    const snappedX = Math.round(seatData.x_coordinate / GRID_SIZE) * GRID_SIZE;
                    const snappedY = Math.round(seatData.y_coordinate / GRID_SIZE) * GRID_SIZE;

                    seatElement.css({
                        left: snappedX + 'px',
                        top: snappedY + 'px',
                        width: seatData.width + 'px',
                        height: seatData.height + 'px',
                        transform: seatData.orientation === 'HORIZONTAL' ? 'rotate(90deg)' : 'none'
                    });
                    container.append(seatElement);
                });
                makeSeatsDraggableAndEditable();
                updateSeatCounts(); // Update counts after rendering
            }

            function createSeatElement(seatData) {
                const element = $('<div>')
                    .addClass('seat')
                    .addClass(seatData.seat_type.toLowerCase())
                    .attr('id', 'seat_' + seatData.seat_id) // Use DB seat_id for DOM element ID
                    .data('seat-data', seatData);

                if (seatData.seat_type === 'DRIVER') {
                    element.html('<i class="fas fa-steering-wheel"></i>');
                } else if (seatData.seat_type === 'AISLE') {
                    element.html('<i class="fas fa-arrows-alt-h"></i> Aisle');
                } else {
                    element.text(seatData.seat_code);
                }

                if (seatData.is_bookable === false) {
                    element.addClass('non-bookable');
                }

                return element;
            }

            function makeSeatsDraggableAndEditable() {
                $('.seat').draggable({
                    containment: ".deck-container",
                    grid: [GRID_SIZE, GRID_SIZE], // Snap to grid
                    stop: function(event, ui) {
                        const seat_id = $(this).data('seat-data').seat_id;
                        const new_x = ui.position.left;
                        const new_y = ui.position.top;
                        
                        // Update in currentSeats array immediately for consistency
                        const seatIndex = currentSeats.findIndex(s => s.seat_id === seat_id);
                        if (seatIndex > -1) {
                            currentSeats[seatIndex].x_coordinate = new_x;
                            currentSeats[seatIndex].y_coordinate = new_y;
                            // Send AJAX to update position in DB
                            saveSeatToDB({seat_id: seat_id, x_coordinate: new_x, y_coordinate: new_y, bus_id: busId}, 'update_seat_position');
                        }
                    }
                }).on('click', function(e) {
                    e.stopPropagation();
                    $('.seat').removeClass('selected-for-edit');
                    $(this).addClass('selected-for-edit');
                    selectedSeatElement = $(this);
                    editSeatProperties($(this).data('seat-data'));
                });

                $('.deck-container').on('click', function() {
                    $('.seat').removeClass('selected-for-edit');
                    selectedSeatElement = null;
                });
            }

            function editSeatProperties(seatData) {
                $('#modal_display_seat_id').text(seatData.seat_code); // Display seat_code
                $('#modal_seat_db_id').val(seatData.seat_id); // Hidden field for DB ID
                $('#modal_seat_code').val(seatData.seat_code); // Original seat code (disabled for edit)
                $('#modal_seat_type').val(seatData.seat_type); // Type is generally not changed after creation
                $('#modal_base_price').val(seatData.base_price || 0);
                $('#modal_gender_preference').val(seatData.gender_preference || 'ANY');
                $('#modal_is_bookable').prop('checked', seatData.is_bookable !== false);
                $('#modal_orientation').val(seatData.orientation || 'VERTICAL');
                $('#modal_width').val(seatData.width || 40);
                $('#modal_height').val(seatData.height || 40);
                $('#modal_status').val(seatData.status || 'AVAILABLE');


                const isConfigurableBookable = (seatData.seat_type !== 'DRIVER' && seatData.seat_type !== 'AISLE');
                $('#modal_base_price, #modal_gender_preference, #modal_is_bookable, #modal_orientation, #modal_status').prop('disabled', !isConfigurableBookable);
                
                // Seat type is disabled in the HTML, width/height are editable and snap to grid
                $('#modal_width').attr('step', GRID_SIZE);
                $('#modal_height').attr('step', GRID_SIZE);

                $('#seatPropertiesModal').modal('show');
            }

            $('#saveSeatProperties').on('click', function() {
                if (selectedSeatElement) {
                    const seat_db_id = $('#modal_seat_db_id').val();
                    const currentSeatData = selectedSeatElement.data('seat-data'); // Get current data to merge
                    
                    const updatedSeatData = {
                        ...currentSeatData, // Keep existing data like coordinates if not explicitly changed
                        seat_id: seat_db_id, 
                        bus_id: busId, // Ensure bus_id is always there
                        base_price: parseFloat($('#modal_base_price').val()),
                        gender_preference: $('#modal_gender_preference').val(),
                        is_bookable: $('#modal_is_bookable').prop('checked'),
                        orientation: $('#modal_orientation').val(),
                        width: parseInt($('#modal_width').val()),
                        height: parseInt($('#modal_height').val()),
                        status: $('#modal_status').val(),
                        // Coordinates are updated by draggable, use current element position
                        x_coordinate: selectedSeatElement.position().left,
                        y_coordinate: selectedSeatElement.position().top
                    };
                    
                    // Update in database via AJAX
                    saveSeatToDB(updatedSeatData, 'update_seat', function(response) {
                        if (response.res === 'true') {
                            // Update the local currentSeats array
                            const seatIndex = currentSeats.findIndex(s => s.seat_id == seat_db_id);
                            if (seatIndex > -1) {
                                currentSeats[seatIndex] = updatedSeatData; // Replace old with updated
                            }
                            
                            // Update the DOM element
                            selectedSeatElement.data('seat-data', updatedSeatData); // Update jQuery data
                            
                            selectedSeatElement.removeClass('non-bookable'); // Remove old class
                            if (updatedSeatData.is_bookable === false) {
                                selectedSeatElement.addClass('non-bookable');
                            }
                            selectedSeatElement.css({
                                width: updatedSeatData.width + 'px',
                                height: updatedSeatData.height + 'px',
                                transform: updatedSeatData.orientation === 'HORIZONTAL' ? 'rotate(90deg)' : 'none'
                            });
                            // No need to change seat_code or type text on the element itself, as they are fixed from template
                            
                            updateSeatCounts(); // Recalculate counts 
                        }
                        $('#seatPropertiesModal').modal('hide');
                        selectedSeatElement = null; // Deselect after saving
                        $('.seat').removeClass('selected-for-edit'); // Remove highlight
                    });
                }
            });

            $('.seat-template').draggable({
                helper: 'clone',
                revert: 'invalid',
                cursor: 'grabbing',
                start: function(event, ui) {
                    $(ui.helper).data('original-type', $(this).data('seat-type'));
                    $(ui.helper).data('original-price', $(this).data('base-price'));
                    $(ui.helper).data('original-is-bookable', $(this).data('is-bookable') !== false);
                    $(ui.helper).data('original-orientation', $(this).data('orientation') || 'VERTICAL');
                    $(ui.helper).css({
                        'z-index': 1000,
                        'opacity': 0.7,
                        'background-image': 'none' // Remove grid pattern from helper
                    });
                }
            });

            $('.deck-container').droppable({
                accept: '.seat-template',
                hoverClass: 'bg-light',
                drop: function(event, ui) {
                    const newSeatType = ui.helper.data('original-type');
                    const deckId = $(this).attr('id').replace('_deck_container', '').toUpperCase();
                    const deckPrefix = deckId.substring(0, 1);

                    let newSeatCode;
                    let defaultWidth = 40;
                    let defaultHeight = 40;
                    let isBookable = ui.helper.data('original-is-bookable');

                    if (newSeatType === 'SEATER') {
                        newSeatCode = `${deckPrefix}S${seatCodeCounter[deckId + '_SEATER']++}`;
                    } else if (newSeatType === 'SLEEPER') {
                        newSeatCode = `${deckPrefix}P${seatCodeCounter[deckId + '_SLEEPER']++}`;
                        defaultHeight = 80; // Sleeper is taller
                    } else if (newSeatType === 'DRIVER') {
                        newSeatCode = 'DRIVER';
                        defaultWidth = 50; defaultHeight = 50; // Driver is a bit different size
                        isBookable = false; // Driver seat is never bookable
                        // Check if driver seat already exists for this bus
                        if (currentSeats.some(s => s.seat_code === 'DRIVER' && s.deck === deckId)) {
                            $.notify({ title: 'Warning', message: 'Driver seat already exists on this deck.' }, { type: 'warning' });
                            return; // Do not add another driver seat
                        }
                    } else if (newSeatType === 'AISLE') {
                        newSeatCode = `${deckPrefix}G${seatCodeCounter[deckId + '_AISLE']++}`;
                        isBookable = false; // Aisle is never bookable
                    } else {
                        newSeatCode = `${deckPrefix}X${Math.floor(Math.random() * 10000)}`; // Fallback for unknown types
                    }

                    // Calculate position relative to container and snap to grid
                    const dropX = event.pageX - $(this).offset().left;
                    const dropY = event.pageY - $(this).offset().top;
                    const snappedX = Math.round(dropX / GRID_SIZE) * GRID_SIZE;
                    const snappedY = Math.round(dropY / GRID_SIZE) * GRID_SIZE;

                    const newSeatData = {
                        bus_id: busId,
                        seat_code: newSeatCode,
                        deck: deckId,
                        seat_type: newSeatType,
                        x_coordinate: snappedX,
                        y_coordinate: snappedY,
                        width: defaultWidth,
                        height: defaultHeight,
                        orientation: ui.helper.data('original-orientation'),
                        base_price: ui.helper.data('original-price') || 0,
                        gender_preference: 'ANY',
                        is_bookable: isBookable,
                        status: 'AVAILABLE'
                    };

                    // Add to database via AJAX
                    saveSeatToDB(newSeatData, 'add_seat', function(response) {
                        if (response.res === 'true') {
                            newSeatData.seat_id = response.new_seat_id; // Get DB generated ID
                            currentSeats.push(newSeatData); // Add to local array
                            renderSeats(); // Re-render all seats to include the new one and update counts
                        } else {
                            // Decrement counter if DB add failed to reuse seat code
                            if (newSeatType === 'SEATER') seatCodeCounter[deckId + '_SEATER']--;
                            else if (newSeatType === 'SLEEPER') seatCodeCounter[deckId + '_SLEEPER']--;
                            else if (newSeatType === 'AISLE') seatCodeCounter[deckId + '_AISLE']--;
                        }
                    });
                }
            });

            // Generic AJAX function for seat actions
            function saveSeatToDB(seatData, actionType, callback) {
                // Ensure bus_id is always included
                seatData.bus_id = busId; // Add busId to all seatData payloads
                seatData.action = actionType;

                $.ajax({
                    url: 'function/backend/bus_actions.php', // Ensure this path is correct
                    type: 'POST',
                    dataType: 'json',
                    data: seatData,
                    success: function(response) {
                        if (response.res === 'true') {
                            $.notify({ title: 'Success', message: response.notif_desc }, { type: 'success' });
                            if (callback) callback(response);
                        } else {
                            Swal.fire({
                                title: response.notif_title,
                                text: response.notif_desc,
                                icon: response.notif_type
                            });
                            if (callback) callback(response); // Still call callback for error handling in UI
                        }
                    },
                    error: function(xhr, status, error) {
                        $.notify({ title: 'Error', message: `Failed to ${actionType.replace('_', ' ')}: ` + status + ' ' + error + ". Server Response: " + xhr.responseText }, { type: 'danger' });
                        console.error(`AJAX error on ${actionType}:`, xhr.responseText);
                        if (callback) callback({res: 'false', notif_desc: 'Network or server error.'}); // Indicate failure
                    }
                });
            }

            $('.delete-selected-seat-btn').on('click', function() {
                if (selectedSeatElement) {
                    Swal.fire({
                        title: "Are you sure?",
                        text: "You won't be able to revert this!",
                        icon: "warning",
                        showCancelButton: true,
                        confirmButtonText: "Yes, delete it!",
                        cancelButtonText: "No, cancel!" 
                    }).then((result) => { 
                        if (result.isConfirmed) { 
                            const seatIdToDelete = selectedSeatElement.data('seat-data').seat_id;
                            saveSeatToDB({ seat_id: seatIdToDelete, bus_id: busId }, 'delete_seat', function(response) {
                                if (response.res === 'true') {
                                    currentSeats = currentSeats.filter(seat => seat.seat_id !== seatIdToDelete);
                                    selectedSeatElement.remove();
                                    selectedSeatElement = null;
                                    Swal.fire("Deleted!", "Your seat has been deleted.", "success");
                                    initializeSeatCodeCounter(); // Recalculate counters after deletion
                                    updateSeatCounts(); // Update counts after deletion
                                } else {
                                    Swal.fire("Error!", "Failed to delete seat.", "error");
                                }
                            });
                        }
                    });
                } else {
                    $.notify({ title: 'Info', message: 'Please select a seat to delete.' }, { type: 'info' });
                }
            });

            // Initial load of the seat layout when the page loads
            loadSeatsForBus();
        });
    }
</script>
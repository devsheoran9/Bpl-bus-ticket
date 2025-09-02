
<script src="assets/js/jquery-3.6.0.min.js"></script>
<script src="assets/bootstrap/js/bootstrap.min.js"></script>
<script src="assets/js/notify.js"></script>
<script src="assets/js/parsley.min.js"></script>
<script src="assets/ladda/spin.min.js"></script>
<script src="assets/ladda/ladda.min.js"></script>
<script src="assets/sweetalert/sweetalert.min.js?145"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>
<script>
     
    //form submit
    $(document).on('submit','form.data-form',function(e) {
        e.preventDefault();
        var action = $(this).attr('action');
        var m_form_data = new FormData(this);
        if($(this).parsley().isValid()){
            var l = Ladda.create(document.querySelector('.submit-btn'));
            l.start();
            $.ajax({
                type: "POST",
                url: action,
                data: m_form_data,
                cache: false,
                dataType:"json",
                enctype: 'multipart/form-data',
                processData: false,  // tell jQuery not to process the data
                contentType: false,   // tell jQuery not to set contentType
                success: function(data){
                    count_add_row = 1;
                    //alert(data);
                    setTimeout(function(){
                        l.stop();
                    }, 2500);
                    var goTo ='';
                    var notify_type = data.notif_type;
                    var notify_title = data.notif_title;
                    var notify_desc = data.notif_desc;
                    var notif_popup = data.notif_popup;

                    if(notif_popup === 'true'){
                        Swal.fire(data.notif_title, data.notif_desc, data.notif_type);
                    }else{
                        $.notify({
                            // options
                            title: notify_title,
                            message: notify_desc
                        },{
                            // settings
                            type: notify_type
                        });
                    }

                    goTo = data.goTo;
                    if(data.res === 'true' && goTo !== ''){
                        window.setTimeout(function(){
                            if(goTo === '469bba0a564235dfceede42db14f17b0'){
                                history.go(-1);
                            }else {
                                window.location.href = goTo;
                            }
                        }, 1000);
                        $(this)[0].reset();
                         // Reset Parsley validation state if using it
                         if ($('form.data-form').parsley()) {
                            $('form.data-form').parsley().reset();
                        }
                    }
                },
                error: function (jqXHR, exception) {
                    var msg = '';
                    if (jqXHR.status === 0) {
                        msg = "Network Problem";
                    } else if (jqXHR.status === 404) {
                        msg = "404 error";
                    } else if (jqXHR.status === 500) {
                        msg = "505 error";
                    } else if (exception === 'parsererror') {
                        msg = "Data Error";
                    } else if (exception === 'timeout') {
                        msg = "Network Problem - Timeout";
                    } else if (exception === 'abort') {
                        msg = "Invalid Data Entery";
                    } else {
                        msg = "oops something want wrong"
                    }
                    l.stop();
                    $.notify({
                        // options
                        title: '(Please Retry) - ',
                        message: msg
                    },{
                        // settings
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

        // 1. Overlay setup
        let overlay = document.getElementById('sidebarOverlay');
        if (!overlay) {
            overlay = document.createElement('div');
            overlay.id = 'sidebarOverlay';
            wrapper.appendChild(overlay);
        }

        // 2a. Sidebar auto-toggle for min-width: 1080px
        function handleToggleFor1080() {
            if (window.innerWidth >= 1080) {
                wrapper.classList.add('toggled');
            } else {
                wrapper.classList.remove('toggled');
            }
        }

        // 2b. Update toggle icon based on sidebar state
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

        // 3. Combined handler for load and resize
        function handleResize() {
            handleToggleFor1080();
            updateToggleIcon();
        }

        // 4. Initial render
        handleResize();

        // 5. Toggle sidebar and icon
        toggle.addEventListener('click', () => {
            wrapper.classList.toggle('toggled');
            updateToggleIcon();
        });

        // 6. Hide sidebar/overlay (on overlay click)
        overlay.addEventListener('click', () => {
            if (wrapper.classList.contains('toggled')) {
                wrapper.classList.remove('toggled');
                updateToggleIcon();
            }
        });

        // 7. Also handle resize events
        window.addEventListener('resize', handleResize);
    });


     function setupFileInput(inputElement, previewContainerId, fileNameDisplayId, isMultiple = false) {
        const previewContainer = $(`#${previewContainerId}`);
        const fileNameDisplay = $(`#${fileNameDisplayId}`);

        // Initial check for empty state
        if (previewContainer.children().length === 0) {
            previewContainer.addClass('empty-state');
        } else {
            previewContainer.removeClass('empty-state');
        }

        $(inputElement).on('change', function(event) {
            previewContainer.html(''); // Clear previous previews
            previewContainer.removeClass('empty-state'); // Remove empty state on file selection

            if (event.target.files && event.target.files.length > 0) {
                if (!isMultiple) {
                    // For single featured image
                    const file = event.target.files[0];
                    fileNameDisplay.text(file.name);

                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const img = $('<img>').attr('src', e.target.result).addClass('image-preview');
                        const clearButton = $('<button type="button" class="clear-featured-image"><i class="fas fa-times"></i></button>');
                        
                        previewContainer.append(img).append(clearButton);

                        clearButton.on('click', function() {
                            inputElement.val(''); // Clear the file input
                            fileNameDisplay.text('No file chosen');
                            previewContainer.html('').addClass('empty-state'); // Clear preview and set empty state
                        });
                    };
                    reader.readAsDataURL(file);
                } else {
                    // For multiple additional images
                    fileNameDisplay.text(`${event.target.files.length} files chosen`);
                    Array.from(event.target.files).forEach((file, index) => {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            const itemDiv = $('<div>').addClass('image-preview-item');
                            const img = $('<img>').attr('src', e.target.result);
                            const removeButton = $('<button type="button" class="remove-image-button"><i class="fas fa-times"></i></button>');
                            
                            itemDiv.append(img).append(removeButton);
                            previewContainer.append(itemDiv);

                            // Individual remove for multiple inputs is complex (reconstructing FileList).
                            // This button only removes the visual preview.
                            // Re-selecting files will replace the whole set.
                            removeButton.on('click', function() {
                                $(this).parent().remove(); // Remove the preview item
                                if (previewContainer.children().length === 0) {
                                    fileNameDisplay.text('No files chosen');
                                    inputElement.val(''); // Clear the hidden file input if all previews are removed
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
        // Initialize custom file input handlers
        setupFileInput($('#featured_image'), 'featured_image_preview', 'featured_image_name', false);
        setupFileInput($('#multiple_images'), 'multiple_images_preview', 'multiple_images_name', true);

        // Initialize Summernote
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
    });

</script>
<script>
        $(document).ready(function() {
            // Use a delegated event handler for elements that might be added dynamically
            $(document).on('click', '.delete-blog-btn', function(e) {
                // Prevent the default form submission if the button is a submit type
                e.preventDefault();

                // 'this' refers to the button that was clicked
                var $button = $(this);
                var $form = $button.closest('form.delete-form');

                // --- FIX 1: Get form action and data from the form itself ---
                var url = $form.attr('action');
                var formData = new FormData($form[0]); // Use FormData for multipart/form-data

                var blogTitle = $button.data('blog-title') || 'this item'; // Fallback title

                // --- Check if we found the form and URL ---
                if (!url) {
                    console.error("Could not find the form's action URL. Make sure the button is inside a <form> with an 'action' attribute.");
                    return; // Stop execution
                }

                Swal.fire({
                    title: 'Are you sure?',
                    html: `You are about to delete the blog post: <strong>${blogTitle}</strong>.<br>This action cannot be undone!`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {

                        // --- FIX 2: Placeholder for a loading indicator ---
                        // Example: $button.prop('disabled', true).text('Deleting...');
                        // If you are using a library like Ladda.js, start it here.
                        // var l = Ladda.create($button[0]);
                        // l.start();

                        $.ajax({
                            type: "POST",
                            url: url, // Use the 'url' variable
                            data: formData, // Use the 'formData' variable
                            dataType: "json",
                            // --- FIX 3: These are necessary for FormData ---
                            processData: false, // Prevents jQuery from automatically transforming the data into a query string
                            contentType: false, // Tells the server what kind of data to expect (let the browser set it with the boundary)
                            cache: false,
                            // 'enctype' is an HTML form attribute, not a valid $.ajax option.
                            // It is handled by using FormData with contentType: false.

                            success: function(data) {
                                // --- FIX 2 (continued): Stop the loading indicator ---
                                // if (l) { l.stop(); }
                                // $button.prop('disabled', false).text('Delete');

                                var notify_type = data.notif_type || 'success';
                                var notify_title = data.notif_title || 'Success!';
                                var notify_desc = data.notif_desc || 'The item has been deleted.';
                                var notif_popup = data.notif_popup === 'true'; // Convert to boolean
                                var goTo = data.goTo;

                                if (notif_popup) {
                                    Swal.fire(notify_title, notify_desc, notify_type);
                                } else {
                                    $.notify({
                                        title: `<strong>${notify_title}</strong>`,
                                        message: notify_desc
                                    }, {
                                        type: notify_type
                                    });
                                }

                                // --- Bonus Improvement: Remove the element from the DOM ---
                                // Instead of a full page reload, you can provide a smoother UX
                                // by just removing the deleted item's container.
                                // Example: $form.closest('.blog-post-row').fadeOut(500, function() { $(this).remove(); });

                                if (data.res === 'true' && goTo) {
                                    setTimeout(function() {
                                        // --- FIX 4: Avoid "magic strings" for redirection ---
                                        // A hardcoded hash is fragile. Better to send a keyword like "reload" or "back".
                                        if (goTo === '469bba0a564235dfceede42db14f17b0' || goTo === 'back') {
                                            history.go(-1);
                                        } else if (goTo === 'reload') {
                                            window.location.reload();
                                        } else {
                                            window.location.href = goTo;
                                        }
                                    }, 1500); // Increased delay to allow user to read message
                                }

                                // --- FIX 5: Use the correct reference to the form ---
                                // 'this' inside an ajax success callback refers to the ajax settings, not the button or form.
                                $form[0].reset();
                                if (typeof $form.parsley === 'function') {
                                    $form.parsley().reset();
                                }
                            },
                            error: function(jqXHR, exception) {
                                // --- FIX 2 (continued): Always stop the loader on error ---
                                // if (l) { l.stop(); }
                                // $button.prop('disabled', false).text('Delete');

                                var msg = '';
                                if (jqXHR.status === 0) {
                                    msg = 'Not connected. Verify Network.';
                                } else if (jqXHR.status === 404) {
                                    msg = 'Requested page not found. [404]';
                                } else if (jqXHR.status === 500) {
                                    msg = 'Internal Server Error [500].';
                                } else if (exception === 'parsererror') {
                                    msg = 'Requested JSON parse failed.';
                                } else if (exception === 'timeout') {
                                    msg = 'Time out error.';
                                } else if (exception === 'abort') {
                                    msg = 'Ajax request aborted.';
                                } else {
                                    msg = 'Uncaught Error: ' + jqXHR.responseText;
                                }

                                // Use Swal for a more prominent error message
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Oops... Something went wrong!',
                                    text: msg
                                });
                            }
                        });
                    }
                });
            });
        });
    </script>
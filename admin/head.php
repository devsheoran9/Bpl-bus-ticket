<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Welcome Page</title>
<link href="assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
<link href="assets/ladda/ladda.min.css" rel="stylesheet">
<link href="assets/css/style.css?<?php echo time(); ?>" rel="stylesheet">
<link href="assets/fa-font/css/all.min.css" rel="stylesheet">
<!-- Summernote CSS - you'll need this -->
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
<!-- Boxicons CSS -->
<link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">

<!-- DataTables Buttons Extension CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css">

<style>
    /* Custom styles for DataTables buttons to match your theme */
    .dt-buttons .btn {
        background-color: #6c757d !important; /* A neutral gray */
        color: white !important;
        border-radius: 5px !important;
        margin: 0 5px 10px 0 !important; /* Add bottom margin */
        padding: 0.375rem 0.75rem !important;
        font-size: 0.9rem !important;
        border: none !important;
    }
    .dt-buttons .btn:hover {
        background-color: #5a6268 !important;
    }
    .dataTables_wrapper .dataTables_filter {
        margin-bottom: 1rem; /* Space below the search box */
    }
</style>
<style>
    h2 {
        text-align: center;
        margin-bottom: 40px;
        /* More space below heading */
        color: #212529;
        /* Darker text for heading */
        font-weight: 700;
        letter-spacing: -0.5px;
    }

    .form-group {
        margin-bottom: 25px;
        /* Consistent spacing */
    }

    label {
        font-weight: 600;
        margin-bottom: 10px;
        /* More space between label and input */
        display: block;
        color: #343a40;
        /* Darker label text */
    }

    .form-control {
        border-radius: 6px;
        /* Slightly more rounded inputs */
        padding: 12px 18px;
        /* More padding inside inputs */
        border: 1px solid #ced4da;
        /* Lighter border */
        box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.06);
        /* Subtle inner shadow */
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
        /* Smooth transitions */
    }

    .form-control:focus {
        border-color: #80bdff;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        /* Bootstrap-like focus glow */
        outline: none;
    }

    textarea.form-control {
        min-height: 150px;
        /* Taller textareas */
        resize: vertical;
    }

    .form-text {
        font-size: 0.875em;
        /* Slightly larger small text */
        color: #6c757d;
        /* Muted text color */
        margin-top: 6px;
    }

    .custom-file-input-wrapper {
        position: relative;
        overflow: hidden;
        display: flex;
        align-items: center;
        border: 1px solid #ced4da;
        border-radius: 6px;
        background-color: #f8f9fa;
        cursor: pointer;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }

    .custom-file-input-wrapper:hover {
        border-color: #a0aec0;
        box-shadow: 0 0 0 0.2rem rgba(108, 117, 125, 0.25);
    }

    .custom-file-input-wrapper input[type="file"] {
        position: absolute;
        left: 0;
        top: 0;
        opacity: 0;
        cursor: pointer;
        width: 100%;
        height: 100%;
        z-index: 2;
    }

    .custom-file-upload-button {
        flex-shrink: 0;
        padding: 12px 18px;
        background-color: #007bff;
        color: white;
        border-radius: 6px 0 0 6px;
        display: flex;
        text-align: center;
        justify-content: center;
        align-items: center;
        gap: 8px;
        width: 100%;
        font-weight: 500;
    }

    .custom-file-upload-button i {
        font-size: 1.1em;
    }

    .custom-file-upload-button:hover {
        background-color: #0056b3;
    }

    .custom-file-input-display {
        flex-grow: 1;
        /* ADD THIS LINE */
        min-width: 0;
        /* Allows the flex item to shrink below its content size */
        /* END ADD */
        padding: 12px 18px;
        color: #495057;
        overflow: hidden;
        white-space: nowrap;
        text-overflow: ellipsis;
        font-style: italic;
    }

    /* Image Preview Styling */
    .image-preview-container {
        margin-top: 20px;
        padding: 15px;
        background-color: #e9ecef;
        /* Light grey background */
        border: 1px dashed #adb5bd;
        /* Dashed border for visual separation */
        border-radius: 8px;
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
        /* More space between items */
        min-height: 80px;
        /* Taller placeholder */
        align-items: center;
        justify-content: flex-start;
        position: relative;
        /* For clear/remove button positioning */
    }

    .image-preview-container.empty-state::before {
        content: "No images selected";
        color: #868e96;
        font-style: italic;
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
    }

    /* For single featured image preview */
    .featured-image-preview img {
        max-width: 100%;
        height: auto;
        border-radius: 8px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        display: block;
    }

    .featured-image-preview {
        position: relative;
        /* For the clear button */
        padding-right: 30px;
        /* Space for the clear button */
        width: 200px;
        /* Ensure it fills container */
    }

    .featured-image-preview .clear-featured-image {
        position: absolute;
        top: 0;
        right: 0;
        background-color: #dc3545;
        /* Red for delete */
        color: white;
        border: none;
        border-radius: 50%;
        width: 28px;
        height: 28px;
        font-size: 1.2em;
        line-height: 1;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        transition: background-color 0.2s ease, transform 0.2s ease;
    }

    .featured-image-preview .clear-featured-image:hover {
        background-color: #c82333;
        transform: scale(1.1);
    }

    /* For multiple image thumbnails */
    .image-preview-item {
        position: relative;
        width: 120px;
        /* Larger fixed size for thumbnails */
        height: 120px;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        overflow: hidden;
        display: flex;
        justify-content: center;
        align-items: center;
        background-color: #f1f3f5;
        /* Lighter background for items */
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        /* Item shadow */
    }

    .image-preview-item img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        /* Crop to fit, maintain aspect ratio */
    }

    .image-preview-item .remove-image-button {
        position: absolute;
        top: 5px;
        right: 5px;
        background-color: rgba(220, 53, 69, 0.8);
        /* Red with transparency */
        color: white;
        border: none;
        border-radius: 50%;
        width: 25px;
        height: 25px;
        font-size: 14px;
        line-height: 1;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0;
        z-index: 10;
        transition: background-color 0.2s ease;
    }

    .image-preview-item .remove-image-button:hover {
        background-color: #dc3545;
        /* Solid red on hover */
    }

    /* Parsley.js Error Styling */
    .parsley-error-list {
        color: #dc3545;
        font-size: 0.875em;
        margin-top: 8px;
        list-style: none;
        /* Remove bullet points */
        padding-left: 0;
    }

    .form-control.parsley-error {
        border-color: #dc3545;
        box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
    }

    /* Summernote specific styling to match form controls */
    .note-editor.note-frame {
        border-radius: 6px;
        border: 1px solid #ced4da;
        box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.06);
    }

    .note-toolbar {
        border-bottom: 1px solid #ced4da;
    }

    /* Submit Button Styling */
    .btn-primary {
        background-color: #28a745;
        /* Green for success/submit */
        border-color: #28a745;
        padding: 12px 25px;
        font-size: 1.1em;
        font-weight: 600;
        border-radius: 6px;
        box-shadow: 0 4px 10px rgba(40, 167, 69, 0.2);
        transition: background-color 0.2s ease, box-shadow 0.2s ease;
    }

    .btn-primary:hover {
        background-color: #218838;
        border-color: #1e7e34;
        box-shadow: 0 6px 15px rgba(40, 167, 69, 0.3);
    }

    .btn-primary:active {
        background-color: #1e7e34;
        border-color: #1c7430;
        box-shadow: 0 2px 5px rgba(40, 167, 69, 0.4);
        transform: translateY(1px);
    }
</style>
<style>
    /* Optional: Basic styling for fieldsets */
    .form-label {
        margin-bottom: 0;
    }

    .form-label {
        font-size: 13px;
    }

    fieldset {
        border: 1px solid #ddd !important;
        padding: 0.25rem !important;
        margin-bottom: 2rem;
        border-radius: .25rem;
    }

    legend {
        font-size: 1.2em !important;
        font-weight: bold !important;
        text-align: left !important;
        width: auto;
        /* For auto width based on content */
        padding: 0 10px;
        /* Padding around text */
        border-bottom: none;
    }

    .text-danger {
        color: #dc3545;
        /* Bootstrap's danger color */
    }

    .form-control {
        padding: 0.25rem;
    }


    .mini {
        font-size: 10px;
    }
</style>
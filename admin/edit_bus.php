<?php
global $_conn_db;
include_once('function/_db.php');
// check_user_login();
session_security_check(); 

// 1. Get and validate the bus ID from the URL
$bus_id = isset($_GET['bus_id']) ? (int)$_GET['bus_id'] : 0;
if ($bus_id <= 0) {
    header("Location: view_all_buses.php"); // Redirect if ID is invalid
    exit();
}

try {
    // 2. Get the main bus details
    $stmt = $_conn_db->prepare("SELECT * FROM buses WHERE bus_id = ?");
    $stmt->execute([$bus_id]);
    $bus = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$bus) { 
        echo "Bus not found!";
        exit();
    }

    // 3. Get the selected categories for this bus
    $stmt_cats = $_conn_db->prepare("SELECT category_id FROM bus_category_map WHERE bus_id = ?");
    $stmt_cats->execute([$bus_id]);
    $selected_category_ids = $stmt_cats->fetchAll(PDO::FETCH_COLUMN, 0);

    // 4. Get existing images for this bus
    $stmt_images = $_conn_db->prepare("SELECT image_id, image_path FROM bus_images WHERE bus_id = ?");
    $stmt_images->execute([$bus_id]);
    $existing_images = $stmt_images->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log("Edit bus fetch error: " . $e->getMessage());
    echo "Database error. Please check logs.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php include_once('head.php');?>
    <!-- CSS for Custom Multi-Select Dropdown & Enhanced Design -->
    <style>
        body { background-color: #f8f9fa; }
        .card { box-shadow: 0 8px 30px rgba(0,0,0,0.07); border: none; border-radius: 1rem; }
        
        /* Custom Multi-Select Styling */
        .custom-select-container { position: relative; }
        .selected-categories-display { display: flex; flex-wrap: wrap; gap: 5px; padding: 0.5rem 0.75rem; border: 1px solid #ced4da; border-radius: 0.375rem; min-height: 42px; cursor: pointer; background-color: #fff; }
        .selected-category-pill { display: inline-flex; align-items: center; padding: 3px 8px; background-color: #e9ecef; border: 1px solid #dee2e6; border-radius: 12px; font-size: 0.9em; white-space: nowrap; }
        .remove-pill { margin-left: 8px; cursor: pointer; font-weight: bold; color: #6c757d; }
        .category-dropdown { display: none; position: absolute; top: 100%; left: 0; right: 0; z-index: 1050; background-color: #fff; border: 1px solid #ced4da; border-top: none; border-radius: 0 0 0.375rem 0.375rem; box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15); max-height: 200px; overflow-y: auto; }
        #categorySearch { width: 100%; padding: 8px 12px; border: none; border-bottom: 1px solid #eee; outline: none; }
        .category-options-list { list-style: none; padding: 0; margin: 0; }
        .category-option { padding: 8px 12px; cursor: pointer; }
        .category-option:hover { background-color: #f0f0f0; }
        .select-placeholder { color: #6c757d; align-self: center; }

        /* Enhanced Image Preview Styling */
        .image-preview-box {
            border: 2px dashed #ced4da; padding: 1rem; border-radius: 0.5rem;
            background-color: #f8f9fa; min-height: 130px; display: flex;
            flex-wrap: wrap; gap: 1rem; align-items: center; justify-content: center;
        }
        .preview-image {
            height: 100px; width: 100px; object-fit: cover; border-radius: 5px;
            border: 2px solid #fff; box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            transition: transform 0.2s;
        }
        .preview-image:hover { transform: scale(1.05); }
        .image-preview-placeholder { color: #6c757d; font-size: 0.9rem; }
        
        /* Styling for Existing Images */
        .existing-image-wrapper { position: relative; }
        .delete-image-btn {
            position: absolute; top: -8px; right: -8px; background-color: #dc3545; color: white;
            border: 2px solid white; border-radius: 50%; width: 26px; height: 26px;
            font-weight: bold; cursor: pointer; display: flex; align-items: center;
            justify-content: center; line-height: 1; padding-bottom: 2px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }
    </style>
</head>
<body>

<div id="wrapper">
    <?php include_once('sidebar.php');?>
    <div class="main-content">
        <?php include_once('header.php');?>
        <div class="container-fluid ">
            <div class="row justify-content-center">
                <div class="col-lg-12 col-xl-12">
                    <h2 class="mb-4 mt-4">Edit Bus</h2>
                    
                    <div class="card">
                        <div class="card-header bg-white py-3">
                            <h5 class="mb-0">Editing Details for: <span class="text-primary"><?= htmlspecialchars($bus['bus_name']) ?></span></h5>
                        </div>
                        <div class="card-body p-4">
                            <form class="data-form" id="edit-bus-form" action="function/backend/bus_actions.php" method="POST" enctype="multipart/form-data" data-parsley-validate>
                                <input type="hidden" name="action" value="update_bus">
                                <input type="hidden" name="bus_id" value="<?= $bus_id ?>">
                                <input type="hidden" id="imagesToDelete" name="images_to_delete" value="">

                                <h6 class="text-primary">Basic Information</h6>
                                <hr class="mt-2 mb-4">
                                
                                <div class="row g-3">
                                    <div class="col-md-6 mb-3">
                                        <label for="bus_name" class="form-label">Bus Name/Number <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="bus_name" name="bus_name" value="<?= htmlspecialchars($bus['bus_name']) ?>" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="registration_number" class="form-label">Registration Number <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="registration_number" name="registration_number" value="<?= htmlspecialchars($bus['registration_number']) ?>" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="engine_no" class="form-label">Engine Number <small>(Optional)</small></label>
                                        <input type="text" class="form-control" id="engine_no" name="engine_no" value="<?= htmlspecialchars($bus['engine_no'] ?? '') ?>">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="chassis_no" class="form-label">Chassis Number <small>(Optional)</small></label>
                                        <input type="text" class="form-control" id="chassis_no" name="chassis_no" value="<?= htmlspecialchars($bus['chassis_no'] ?? '') ?>">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="bus_type" class="form-label">Bus Type <span class="text-danger">*</span></label>
                                        <select class="form-select" id="bus_type" name="bus_type" required>
                                            <option value="">Select Bus Type</option>
                                            <?php
                                                $bus_types = ["AC Seater", "Non-AC Seater", "AC Sleeper", "Non-AC Sleeper", "AC Seater-Sleeper", "Non-AC Seater-Sleeper"];
                                                foreach ($bus_types as $type) {
                                                    $selected = ($bus['bus_type'] == $type) ? 'selected' : '';
                                                    echo "<option value=\"$type\" $selected>$type</option>";
                                                }
                                            ?>
                                        </select>
                                    </div>
                                </div>

                                <h6 class="text-primary mt-4">Features & Images</h6>
                                <hr class="mt-2 mb-4">

                                <div class="mb-3">
                                    <label class="form-label">Bus Amenities / Categories</label>
                                    <div class="input-group">
                                        <div class="custom-select-container flex-grow-1">
                                            <div class="selected-categories-display" id="selectedCategoriesDisplay"></div>
                                            <div class="category-dropdown" id="categoryDropdown">
                                                <input type="text" id="categorySearch" placeholder="Search categories...">
                                                <ul class="category-options-list" id="categoryOptionsList"></ul>
                                            </div>
                                        </div>
                                        <button class="btn btn-outline-success" type="button" id="addNewCategoryBtn" title="Add New Category"><i class="fas fa-plus"></i></button>
                                        <button class="btn btn-outline-primary" type="button" id="manageCategoriesBtn" title="Manage Categories"><i class="fas fa-tasks"></i></button>
                                    </div>
                                    <select name="categories[]" id="hiddenCategories" multiple style="display: none;">
                                       <?php
                                        try {
                                            $stmt_all_cat = $_conn_db->query("SELECT category_id, category_name FROM bus_categories WHERE status = 'Active' ORDER BY category_name");
                                            while ($cat = $stmt_all_cat->fetch()) {
                                                $selected = in_array($cat['category_id'], $selected_category_ids) ? 'selected' : '';
                                                echo '<option value="' . $cat['category_id'] . '" ' . $selected . '>' . htmlspecialchars($cat['category_name']) . '</option>';
                                            }
                                        } catch (PDOException $e) { /* Error Handling */ }
                                       ?>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Existing Images</label>
                                    <div id="existing-images-container" class="d-flex flex-wrap gap-3">
                                        <?php foreach ($existing_images as $image): ?>
                                            <div class="existing-image-wrapper" id="image-wrapper-<?= $image['image_id'] ?>">
                                                <img src="./function/backend/uploads/bus_images/<?= htmlspecialchars($image['image_path']) ?>" class="preview-image" alt="Bus Image">
                                                <button type="button" class="delete-image-btn" data-image-id="<?= $image['image_id'] ?>">&times;</button>
                                            </div>
                                        <?php endforeach; ?>
                                        <?php if (empty($existing_images)): ?>
                                            <p class="text-muted small">No images have been uploaded for this bus.</p>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="bus_images" class="form-label">Upload New Images</label>
                                    <input type="file" class="form-control" id="bus_images" name="bus_images[]" accept="image/*" multiple>
                                    <div id="image-preview-container" class="mt-3 image-preview-box">
                                        <span class="image-preview-placeholder">New image previews will appear here</span>
                                    </div>
                                </div>

                                <h6 class="text-primary mt-4">Additional Details</h6>
                                <hr class="mt-2 mb-4">
                                
                                <div class="mb-3">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea class="form-control" id="description" name="description" rows="3"><?= htmlspecialchars($bus['description']) ?></textarea>
                                </div>
                                <div class="mb-3">
                                    <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                    <select class="form-select" id="status" name="status" required>
                                        <option value="Active" <?= ($bus['status'] == 'Active') ? 'selected' : '' ?>>Active</option>
                                        <option value="Inactive" <?= ($bus['status'] == 'Inactive') ? 'selected' : '' ?>>Inactive</option>
                                    </select>
                                </div>
                            </form>
                        </div>
                        <div class="card-footer bg-white text-end py-3">
                            <a href="view_all_buses.php" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary ladda-button p-2 submit-btn" data-style="zoom-in" form="edit-bus-form">
                                <span class="ladda-label">Update Bus Details</span> <span class="ladda-spinner"></span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modals for Category Management (Copied from add_bus.php) -->
<div class="modal fade" id="categoryModal" tabindex="-1"> <div class="modal-dialog"> <div class="modal-content"> <div class="modal-header"> <h5 class="modal-title" id="categoryModalLabel">Add New Category</h5> <button type="button" class="btn-close" data-bs-dismiss="modal"></button> </div> <div class="modal-body"> <form id="categoryForm"> <input type="hidden" id="categoryAction" value="add_category"> <input type="hidden" id="editCategoryId" value=""> <div class="mb-3"> <label for="categoryName" class="form-label">Category Name</label> <input type="text" class="form-control" id="categoryName" required> </div> </form> </div> <div class="modal-footer"> <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button> <button type="button" class="btn btn-primary" id="saveCategoryBtn">Save Category</button> </div> </div> </div> </div>
<div class="modal fade" id="manageCategoriesModal" tabindex="-1"> <div class="modal-dialog modal-lg"> <div class="modal-content"> <div class="modal-header"> <h5 class="modal-title">Manage Categories</h5> <button type="button" class="btn-close" data-bs-dismiss="modal"></button> </div> <div class="modal-body"> <div class="table-responsive"> <table class="table table-striped"> <thead> <tr> <th>Category Name</th> <th class="text-end">Actions</th> </tr> </thead> <tbody id="manageCategoryList"></tbody> </table> </div> </div> </div> </div> </div>


<?php include_once('foot.php');?>

<script>
$(document).ready(function() {
    // --- Existing Image Deletion Logic ---
    let imagesToDelete = [];
    $('#existing-images-container').on('click', '.delete-image-btn', function() {
        const imageId = $(this).data('image-id');
        const wrapper = $('#image-wrapper-' + imageId);
        
        if (!imagesToDelete.includes(imageId)) {
            imagesToDelete.push(imageId);
            $('#imagesToDelete').val(imagesToDelete.join(','));
        }
        
        wrapper.fadeOut(300, function() { $(this).remove(); });
    });
 
    // --- New Image Preview Logic ---
    $('#bus_images').on('change', function() {
        const previewContainer = $('#image-preview-container');
        previewContainer.empty();
        if (this.files && this.files.length > 0) {
            Array.from(this.files).forEach(file => {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const img = $(`<img src="${e.target.result}" alt="Image preview" class="preview-image">`);
                    previewContainer.append(img);
                };
                reader.readAsDataURL(file);
            });
        } else {
            previewContainer.html('<span class="image-preview-placeholder">New image previews will appear here</span>');
        }
    });

    // --- Custom Multi-Select Dropdown Logic ---
    const hiddenSelect = $('#hiddenCategories');
    const displayContainer = $('#selectedCategoriesDisplay');
    const dropdown = $('#categoryDropdown');
    const optionsList = $('#categoryOptionsList');
    const searchInput = $('#categorySearch');

    function syncUIFromSelect() {
        displayContainer.empty();
        optionsList.empty();
        let hasSelection = false;
        hiddenSelect.find('option').each(function() {
            const option = $(this);
            const value = option.val();
            const text = option.text();
            const listItem = $(`<li class="category-option" data-value="${value}">${text}</li>`);
            optionsList.append(listItem);
            if (option.is(':selected')) {
                hasSelection = true;
                const pill = $(`<div class="selected-category-pill" data-value="${value}">${text}<span class="remove-pill">&times;</span></div>`);
                displayContainer.append(pill);
                listItem.addClass('hidden');
            } else {
                listItem.removeClass('hidden');
            }
        });
        if (!hasSelection) {
            displayContainer.append('<span class="select-placeholder">Select categories...</span>');
        }
    }
    displayContainer.on('click', function() { dropdown.toggle(); if (dropdown.is(':visible')) { searchInput.focus(); searchInput.val(''); optionsList.find('.category-option').show(); } });
    $(document).on('click', function(e) { if (!$(e.target).closest('.custom-select-container').length) { dropdown.hide(); } });
    optionsList.on('click', '.category-option', function() { const value = $(this).data('value'); if (!hiddenSelect.find(`option[value="${value}"]`).is(':selected')) { hiddenSelect.find(`option[value="${value}"]`).prop('selected', true); syncUIFromSelect(); } dropdown.hide(); });
    displayContainer.on('click', '.remove-pill', function(e) { e.stopPropagation(); const value = $(this).parent().data('value'); hiddenSelect.find(`option[value="${value}"]`).prop('selected', false); syncUIFromSelect(); });
    searchInput.on('keyup', function() { const filter = $(this).val().toLowerCase(); optionsList.find('.category-option').each(function() { const optionText = $(this).text().toLowerCase(); const isSelected = hiddenSelect.find(`option[value="${$(this).data('value')}"]`).is(':selected'); if (optionText.includes(filter) && !isSelected) { $(this).show(); } else { $(this).hide(); } }); });
    syncUIFromSelect();

    // --- ADDED: Modal and Category Management Logic ---
    const categoryModal = new bootstrap.Modal(document.getElementById('categoryModal'));
    const manageCategoriesModal = new bootstrap.Modal(document.getElementById('manageCategoriesModal'));
    function refreshCategories() { const currentSelection = hiddenSelect.val() || []; $.ajax({ url: 'function/backend/bus_actions.php', type: 'GET', dataType: 'json', data: { action: 'get_all_categories' }, success: function(response) { if (response.res === 'true' && Array.isArray(response.categories)) { hiddenSelect.empty(); response.categories.forEach(cat => { hiddenSelect.append(new Option(cat.category_name, cat.category_id)); }); const newSelection = currentSelection.filter(id => response.categories.some(cat => cat.category_id == id) ); hiddenSelect.val(newSelection); syncUIFromSelect(); populateManageList(response.categories); } } }); }
    function populateManageList(categories) { const manageList = $('#manageCategoryList'); manageList.empty(); if (categories.length === 0) { manageList.append('<tr><td colspan="2" class="text-center">No categories found.</td></tr>'); return; } categories.forEach(cat => { manageList.append(` <tr id="cat-row-${cat.category_id}"> <td>${cat.category_name}</td> <td class="text-end"> <button class="btn btn-sm btn-warning edit-cat-btn me-2" data-id="${cat.category_id}" data-name="${cat.category_name}"><i class="fas fa-edit"></i> Edit</button> <button class="btn btn-sm btn-danger delete-cat-btn" data-id="${cat.category_id}" data-name="${cat.category_name}"><i class="fas fa-trash"></i> Delete</button> </td> </tr> `); }); }
    $('#addNewCategoryBtn').on('click', function() { $('#categoryForm')[0].reset(); $('#categoryModalLabel').text('Add New Category'); $('#categoryAction').val('add_category'); categoryModal.show(); });
    $('#saveCategoryBtn').on('click', function() { const action = $('#categoryAction').val(); const categoryName = $('#categoryName').val().trim(); const categoryId = $('#editCategoryId').val(); if (!categoryName) { Swal.fire('Error!', 'Category name cannot be empty.', 'error'); return; } $.ajax({ url: 'function/backend/bus_actions.php', type: 'POST', dataType: 'json', data: { action: action, category_name: categoryName, category_id: categoryId }, success: function(response) { if (response.res === 'true') { categoryModal.hide(); $.notify({ title: 'Success', message: response.notif_desc }, { type: 'success' }); refreshCategories(); } else { Swal.fire('Error!', response.notif_desc, 'error'); } }, error: function() { Swal.fire('Error!', 'Failed to connect to server.', 'error'); } }); });
    $('#manageCategoriesBtn').on('click', function() { refreshCategories(); manageCategoriesModal.show(); });
    $('#manageCategoryList').on('click', '.edit-cat-btn', function() { $('#categoryForm')[0].reset(); $('#categoryModalLabel').text('Edit Category'); $('#categoryAction').val('update_category'); $('#editCategoryId').val($(this).data('id')); $('#categoryName').val($(this).data('name')); manageCategoriesModal.hide(); categoryModal.show(); });
    $('#manageCategoryList').on('click', '.delete-cat-btn', function() { const catId = $(this).data('id'); const catName = $(this).data('name'); Swal.fire({ title: `Delete "${catName}"?`, text: "This will remove the category from all buses and cannot be undone!", icon: 'warning', showCancelButton: true, confirmButtonColor: '#d33', confirmButtonText: 'Yes, delete it!' }).then((result) => { if (result.isConfirmed) { $.ajax({ url: 'function/backend/bus_actions.php', type: 'POST', dataType: 'json', data: { action: 'delete_category', category_id: catId }, success: function(response) { if (response.res === 'true') { $.notify({ title: 'Success', message: response.notif_desc }, { type: 'success' }); refreshCategories(); } else { Swal.fire('Error!', response.notif_desc, 'error'); } }, error: function() { Swal.fire('Error!', 'Failed to connect to server.', 'error'); } }); } }); });


    // Handle form submission via the button in the footer
    $('.submit-btn').on('click', function(e) {
        e.preventDefault();
        $('form.data-form').submit(); 
    });
});
</script>

</body>
</html>
<?php pdo_close_conn($_conn_db); ?>
<?php
global $_conn_db;
include_once('function/_db.php');
// check_user_login();
session_security_check(); 

// --- PHP variables ---
$name = $_SESSION['user']['name'] ?? 'Guest';
$email = $_SESSION['user']['email'] ?? '';
$mobile = $_SESSION['user']['mobile'] ?? '';
$current_page = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php include_once('head.php');?>
    <!-- CSS for Custom Multi-Select Dropdown -->
    <style>
        .custom-select-container { position: relative; }
        .selected-categories-display { display: flex; flex-wrap: wrap; gap: 5px; padding: 8px 12px; border: 1px solid #ced4da; border-radius: 0.375rem; min-height: 40px; cursor: pointer; background-color: #fff; }
        .selected-category-pill { display: inline-flex; align-items: center; padding: 3px 8px; background-color: #e9ecef; border: 1px solid #dee2e6; border-radius: 12px; font-size: 0.9em; white-space: nowrap; }
        .remove-pill { margin-left: 8px; cursor: pointer; font-weight: bold; color: #6c757d; }
        .remove-pill:hover { color: #000; }
        .category-dropdown { display: none; position: absolute; top: 100%; left: 0; right: 0; z-index: 1050; background-color: #fff; border: 1px solid #ced4da; border-top: none; border-radius: 0 0 0.375rem 0.375rem; box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15); max-height: 200px; overflow-y: auto; }
        #categorySearch { width: 100%; padding: 8px 12px; border: none; border-bottom: 1px solid #eee; outline: none; }
        .category-options-list { list-style: none; padding: 0; margin: 0; }
        .category-option { padding: 8px 12px; cursor: pointer; }
        .category-option:hover { background-color: #f8f9fa; }
        .category-option.hidden { display: none; }
        .select-placeholder { color: #6c757d; align-self: center; }
    </style>
</head>
<body>

<div id="wrapper">
    <?php include_once('sidebar.php');?>
    <div class="main-content">
        <?php include_once('header.php');?>
        <div class="container-fluid ">
            <h2 class="mb-4 mt-4 text-center">Add New Bus</h2>
            <form class="data-form" action="function/backend/bus_actions.php" method="POST" enctype="multipart/form-data" data-parsley-validate>
                <input type="hidden" name="action" value="add_bus">
                
                <!-- Bus Details -->
                <div class="row g-1">
                    <div class="col-md-6 col-6 mb-3">
                        <label for="bus_name" class="form-label">Bus Name/Number <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="bus_name" name="bus_name" required>
                    </div>
                    <div class="col-md-6 col-6 mb-3">
                        <label for="registration_number" class="form-label">Registration Number <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="registration_number" name="registration_number" required>
                    </div>
                </div>
                <div class="row g-1">
                    <div class="col-md-6 col-6 mb-3">
                        <label for="operator_id" class="form-label">Bus Operator <span class="text-danger">*</span></label>
                        <select class="form-select" id="operator_id" name="operator_id" required>
                           <option value="">Select Operator</option>
                           <?php
                            try {
                                $stmt = $_conn_db->query("SELECT operator_id, operator_name FROM operators WHERE status = 'Active'");
                                while ($row = $stmt->fetch()) {
                                    echo '<option value="' . htmlspecialchars($row['operator_id']) . '">' . htmlspecialchars($row['operator_name']) . '</option>';
                                }
                            } catch (PDOException $e) { /* Error Handling */ }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-6 col-6 mb-3">
                        <label for="bus_type" class="form-label">Bus Type <span class="text-danger">*</span></label>
                        <select class="form-select" id="bus_type" name="bus_type" required>
                            <option value="">Select Bus Type</option>
                            <option value="AC Seater">AC Seater</option>
                            <option value="Non-AC Seater">Non-AC Seater</option>
                            <option value="AC Sleeper">AC Sleeper</option>
                            <option value="Non-AC Sleeper">Non-AC Sleeper</option>
                            <option value="AC Seater-Sleeper">AC Seater-Sleeper</option>
                            <option value="Non-AC Seater-Sleeper">Non-AC Seater-Sleeper</option>
                        </select>
                    </div>
                </div>

                <!-- Custom Categories Section -->
                <div class="mb-3">
                    <label class="form-label">Categories</label>
                    <div class="input-group">
                        <div class="custom-select-container flex-grow-1">
                            <div class="selected-categories-display" id="selectedCategoriesDisplay">
                                <span class="select-placeholder">Select categories...</span>
                            </div>
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
                            $stmt_cat = $_conn_db->query("SELECT category_id, category_name FROM bus_categories WHERE status = 'Active' ORDER BY category_name");
                            while ($cat = $stmt_cat->fetch()) {
                                echo '<option value="' . $cat['category_id'] . '">' . htmlspecialchars($cat['category_name']) . '</option>';
                            }
                        } catch (PDOException $e) { /* Error Handling */ }
                       ?>
                    </select>
                </div>
                
                <!-- Other Bus Fields -->
                <!-- MODIFIED FOR MULTIPLE IMAGES -->
                <div class="mb-3">
                    <label for="bus_images" class="form-label">Bus Images (select multiple)</label>
                    <input type="file" class="form-control" id="bus_images" name="bus_images[]" accept="image/*" multiple>
                    <!-- Container for image previews -->
                    <div id="image-preview-container" class="mt-2 d-flex flex-wrap gap-2"></div>
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                </div>
                <div class="mb-3">
                    <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                    <select class="form-select" id="status" name="status" required>
                        <option value="Active">Active</option>
                        <option value="Inactive">Inactive</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary ladda-button p-1 submit-btn" data-style="zoom-in"><span class="ladda-label">Add Bus & Continue</span> <span class="ladda-spinner"></span> </button>
            </form>
        </div>
    </div>
</div>

<!-- Modals for Category Management (same as before) -->
<div class="modal fade" id="categoryModal" tabindex="-1"> <div class="modal-dialog"> <div class="modal-content"> <div class="modal-header"> <h5 class="modal-title" id="categoryModalLabel">Add New Category</h5> <button type="button" class="btn-close" data-bs-dismiss="modal"></button> </div> <div class="modal-body"> <form id="categoryForm"> <input type="hidden" id="categoryAction" value="add_category"> <input type="hidden" id="editCategoryId" value=""> <div class="mb-3"> <label for="categoryName" class="form-label">Category Name</label> <input type="text" class="form-control" id="categoryName" required> </div> </form> </div> <div class="modal-footer"> <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button> <button type="button" class="btn btn-primary" id="saveCategoryBtn">Save Category</button> </div> </div> </div> </div>
<div class="modal fade" id="manageCategoriesModal" tabindex="-1"> <div class="modal-dialog modal-lg"> <div class="modal-content"> <div class="modal-header"> <h5 class="modal-title">Manage Categories</h5> <button type="button" class="btn-close" data-bs-dismiss="modal"></button> </div> <div class="modal-body"> <div class="table-responsive"> <table class="table table-striped"> <thead> <tr> <th>Category Name</th> <th class="text-end">Actions</th> </tr> </thead> <tbody id="manageCategoryList"></tbody> </table> </div> </div> </div> </div> </div>

<?php include_once('foot.php');?>

<script>
$(document).ready(function() {
    // --- Custom Multi-Select Logic (same as before) ---
    const hiddenSelect = $('#hiddenCategories');
    const displayContainer = $('#selectedCategoriesDisplay');
    const dropdown = $('#categoryDropdown');
    const optionsList = $('#categoryOptionsList');
    const searchInput = $('#categorySearch');
    function syncUIFromSelect() { displayContainer.empty(); optionsList.empty(); let hasSelection = false; hiddenSelect.find('option').each(function() { const option = $(this); const value = option.val(); const text = option.text(); const listItem = $(`<li class="category-option" data-value="${value}">${text}</li>`); optionsList.append(listItem); if (option.is(':selected')) { hasSelection = true; const pill = $(`<div class="selected-category-pill" data-value="${value}">${text}<span class="remove-pill">&times;</span></div>`); displayContainer.append(pill); listItem.addClass('hidden'); } else { listItem.removeClass('hidden'); } }); if (!hasSelection) { displayContainer.append('<span class="select-placeholder">Select categories...</span>'); } }
    displayContainer.on('click', function() { dropdown.toggle(); if (dropdown.is(':visible')) { searchInput.focus(); searchInput.val(''); optionsList.find('.category-option').show(); } });
    $(document).on('click', function(e) { if (!$(e.target).closest('.custom-select-container').length) { dropdown.hide(); } });
    optionsList.on('click', '.category-option', function() { const value = $(this).data('value'); if (!hiddenSelect.find(`option[value="${value}"]`).is(':selected')) { hiddenSelect.find(`option[value="${value}"]`).prop('selected', true); syncUIFromSelect(); } dropdown.hide(); });
    displayContainer.on('click', '.remove-pill', function(e) { e.stopPropagation(); const value = $(this).parent().data('value'); hiddenSelect.find(`option[value="${value}"]`).prop('selected', false); syncUIFromSelect(); });
    searchInput.on('keyup', function() { const filter = $(this).val().toLowerCase(); optionsList.find('.category-option').each(function() { const optionText = $(this).text().toLowerCase(); const isSelected = hiddenSelect.find(`option[value="${$(this).data('value')}"]`).is(':selected'); if (optionText.includes(filter) && !isSelected) { $(this).show(); } else { $(this).hide(); } }); });
    syncUIFromSelect();

    // --- Modal and Category Management Logic (same as before) ---
    const categoryModal = new bootstrap.Modal(document.getElementById('categoryModal'));
    const manageCategoriesModal = new bootstrap.Modal(document.getElementById('manageCategoriesModal'));
    function refreshCategories() { const currentSelection = hiddenSelect.val(); $.ajax({ url: 'function/backend/bus_actions.php', type: 'GET', dataType: 'json', data: { action: 'get_all_categories' }, success: function(response) { if (response.res === 'true' && Array.isArray(response.categories)) { hiddenSelect.empty(); response.categories.forEach(cat => { hiddenSelect.append(new Option(cat.category_name, cat.category_id)); }); const newSelection = currentSelection.filter(id => response.categories.some(cat => cat.category_id == id) ); hiddenSelect.val(newSelection); syncUIFromSelect(); populateManageList(response.categories); } } }); }
    function populateManageList(categories) { const manageList = $('#manageCategoryList'); manageList.empty(); if (categories.length === 0) { manageList.append('<tr><td colspan="2" class="text-center">No categories found.</td></tr>'); return; } categories.forEach(cat => { manageList.append(` <tr id="cat-row-${cat.category_id}"> <td>${cat.category_name}</td> <td class="text-end"> <button class="btn btn-sm btn-warning edit-cat-btn me-2" data-id="${cat.category_id}" data-name="${cat.category_name}"><i class="fas fa-edit"></i> Edit</button> <button class="btn btn-sm btn-danger delete-cat-btn" data-id="${cat.category_id}" data-name="${cat.category_name}"><i class="fas fa-trash"></i> Delete</button> </td> </tr> `); }); }
    $('#addNewCategoryBtn').on('click', function() { $('#categoryForm')[0].reset(); $('#categoryModalLabel').text('Add New Category'); $('#categoryAction').val('add_category'); categoryModal.show(); });
    $('#saveCategoryBtn').on('click', function() { const action = $('#categoryAction').val(); const categoryName = $('#categoryName').val().trim(); const categoryId = $('#editCategoryId').val(); if (!categoryName) { Swal.fire('Error!', 'Category name cannot be empty.', 'error'); return; } $.ajax({ url: 'function/backend/bus_actions.php', type: 'POST', dataType: 'json', data: { action: action, category_name: categoryName, category_id: categoryId }, success: function(response) { if (response.res === 'true') { categoryModal.hide(); $.notify({ title: 'Success', message: response.notif_desc }, { type: 'success' }); refreshCategories(); } else { Swal.fire('Error!', response.notif_desc, 'error'); } }, error: function() { Swal.fire('Error!', 'Failed to connect to server.', 'error'); } }); });
    $('#manageCategoriesBtn').on('click', function() { refreshCategories(); manageCategoriesModal.show(); });
    $('#manageCategoryList').on('click', '.edit-cat-btn', function() { $('#categoryForm')[0].reset(); $('#categoryModalLabel').text('Edit Category'); $('#categoryAction').val('update_category'); $('#editCategoryId').val($(this).data('id')); $('#categoryName').val($(this).data('name')); manageCategoriesModal.hide(); categoryModal.show(); });
    $('#manageCategoryList').on('click', '.delete-cat-btn', function() { const catId = $(this).data('id'); const catName = $(this).data('name'); Swal.fire({ title: `Delete "${catName}"?`, text: "This will remove the category from all buses and cannot be undone!", icon: 'warning', showCancelButton: true, confirmButtonColor: '#d33', confirmButtonText: 'Yes, delete it!' }).then((result) => { if (result.isConfirmed) { $.ajax({ url: 'function/backend/bus_actions.php', type: 'POST', dataType: 'json', data: { action: 'delete_category', category_id: catId }, success: function(response) { if (response.res === 'true') { $.notify({ title: 'Success', message: response.notif_desc }, { type: 'success' }); refreshCategories(); } else { Swal.fire('Error!', response.notif_desc, 'error'); } }, error: function() { Swal.fire('Error!', 'Failed to connect to server.', 'error'); } }); } }); });

    // --- NEW: Image Preview Logic for Multiple Files ---
    $('#bus_images').on('change', function() {
        const previewContainer = $('#image-preview-container');
        previewContainer.empty(); // Clear previous previews
        if (this.files && this.files.length > 0) {
            Array.from(this.files).forEach(file => {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const img = $(`<img src="${e.target.result}" alt="Image preview" style="height: 100px; width: 100px; object-fit: cover; border-radius: 5px; border: 1px solid #ddd;">`);
                    previewContainer.append(img);
                };
                reader.readAsDataURL(file);
            });
        }
    });
});
</script>

</body>
</html>
<?php pdo_close_conn($_conn_db); ?>
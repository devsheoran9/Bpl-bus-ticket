<?php
global $_conn_db;
include_once('function/_db.php');
check_user_login();

// 1. Obtener el ID del autobús de la URL y validarlo
$bus_id = isset($_GET['bus_id']) ? (int)$_GET['bus_id'] : 0;
if ($bus_id <= 0) {
    header("Location: view_all_buses.php"); // Redirigir si el ID no es válido
    exit();
}

try {
    // 2. Obtener los detalles principales del autobús
    $stmt = $_conn_db->prepare("SELECT * FROM buses WHERE bus_id = ?");
    $stmt->execute([$bus_id]);
    $bus = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$bus) { 
        echo "Bus not found!";
        exit();
    }

    // 3. Obtener las categorías seleccionadas para este autobús
    $stmt_cats = $_conn_db->prepare("SELECT category_id FROM bus_category_map WHERE bus_id = ?");
    $stmt_cats->execute([$bus_id]);
    $selected_category_ids = $stmt_cats->fetchAll(PDO::FETCH_COLUMN, 0);

    // 4. Obtener las imágenes existentes para este autobús
    $stmt_images = $_conn_db->prepare("SELECT image_id, image_path FROM bus_images WHERE bus_id = ?");
    $stmt_images->execute([$bus_id]);
    $existing_images = $stmt_images->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    // Manejar errores de la base de datos
    error_log("Edit bus fetch error: " . $e->getMessage());
    echo "Database error. Please check logs.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php include_once('head.php');?>
    <!-- CSS para el Dropdown Multi-Select Personalizado y Previsualización de Imágenes -->
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
        /* Estilos para la previsualización de imágenes existentes */
        .existing-image-wrapper { position: relative; display: inline-block; }
        .existing-image-preview { height: 100px; width: 100px; object-fit: cover; border-radius: 5px; border: 1px solid #ddd; }
        .delete-image-btn { position: absolute; top: -5px; right: -5px; background-color: #dc3545; color: white; border: none; border-radius: 50%; width: 24px; height: 24px; font-weight: bold; cursor: pointer; display: flex; align-items: center; justify-content: center; line-height: 1; padding: 0; }
    </style>
</head>
<body>

<div id="wrapper">
    <?php include_once('sidebar.php');?>
    <div class="main-content">
        <?php include_once('header.php');?>
        <div class="container-fluid ">
            <h2 class="mb-4 mt-4 text-center">Edit Bus Details</h2>
            <form class="data-form" action="function/backend/bus_actions.php" method="POST" enctype="multipart/form-data" data-parsley-validate>
                <!-- Campos ocultos para la acción de actualización y el ID del autobús -->
                <input type="hidden" name="action" value="update_bus">
                <input type="hidden" name="bus_id" value="<?= $bus_id ?>">
                <input type="hidden" id="imagesToDelete" name="images_to_delete" value="">


                <!-- Detalles del autobús (rellenados con datos existentes) -->
                <div class="row g-1">
                    <div class="col-md-6 col-6 mb-3">
                        <label for="bus_name" class="form-label">Bus Name/Number <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="bus_name" name="bus_name" value="<?= htmlspecialchars($bus['bus_name']) ?>" required>
                    </div>
                    <div class="col-md-6 col-6 mb-3">
                        <label for="registration_number" class="form-label">Registration Number <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="registration_number" name="registration_number" value="<?= htmlspecialchars($bus['registration_number']) ?>" required>
                    </div>
                </div>
                <div class="row g-1">
                    <div class="col-md-6 col-6  mb-3">
                        <label for="operator_id" class="form-label">Bus Operator <span class="text-danger">*</span></label>
                        <select class="form-select" id="operator_id" name="operator_id" required>
                           <option value="">Select Operator</option>
                           <?php
                            try {
                                $stmt_op = $_conn_db->query("SELECT operator_id, operator_name FROM operators WHERE status = 'Active'");
                                while ($row = $stmt_op->fetch()) {
                                    $selected = ($bus['operator_id'] == $row['operator_id']) ? 'selected' : '';
                                    echo '<option value="' . htmlspecialchars($row['operator_id']) . '" ' . $selected . '>' . htmlspecialchars($row['operator_name']) . '</option>';
                                }
                            } catch (PDOException $e) { /* Manejo de errores */ }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-6 col-6 mb-3">
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

                <!-- Sección de categorías personalizadas (con selecciones existentes) -->
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
                            $stmt_all_cat = $_conn_db->query("SELECT category_id, category_name FROM bus_categories WHERE status = 'Active' ORDER BY category_name");
                            while ($cat = $stmt_all_cat->fetch()) {
                                $selected = in_array($cat['category_id'], $selected_category_ids) ? 'selected' : '';
                                echo '<option value="' . $cat['category_id'] . '" ' . $selected . '>' . htmlspecialchars($cat['category_name']) . '</option>';
                            }
                        } catch (PDOException $e) { /* Manejo de errores */ }
                       ?>
                    </select>
                </div>
                
                <!-- Gestión de imágenes múltiples -->
                <div class="mb-3">
                    <label class="form-label">Existing Images</label>
                    <div id="existing-images-container" class="d-flex flex-wrap gap-2 mb-2">
                        <?php foreach ($existing_images as $image): ?>
                            <div class="existing-image-wrapper" id="image-wrapper-<?= $image['image_id'] ?>">
                                <img src="./function/backend/uploads/bus_images/<?= htmlspecialchars($image['image_path']) ?>" class="existing-image-preview" alt="Bus Image">
                                <button type="button" class="delete-image-btn" data-image-id="<?= $image['image_id'] ?>">&times;</button>
                            </div>
                        <?php endforeach; ?>
                         <?php if (empty($existing_images)): ?>
                            <p class="text-muted">No images have been uploaded for this bus.</p>
                        <?php endif; ?>
                    </div>

                    <label for="bus_images" class="form-label">Upload New Images</label>
                    <input type="file" class="form-control" id="bus_images" name="bus_images[]" accept="image/*" multiple>
                    <div id="image-preview-container" class="mt-2 d-flex flex-wrap gap-2"></div>
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="3"><?= htmlspecialchars($bus['description']) ?></textarea>
                </div>
                <div class="mb-3">
                    <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                    <select class="form-select" id="status" name="status" required>
                        <option value="Active" <?= ($bus['status'] == 'Active') ? 'selected' : '' ?>>Active</option>
                        <option value="Inactive" <?= ($bus['status'] == 'Inactive') ? 'selected' : '' ?>>Inactive</option>
                        <!-- Agrega otros estados si los tienes en tu BBDD -->
                    </select>
                </div>

                <button type="submit" class="btn btn-primary ladda-button p-1 submit-btn" data-style="zoom-in">
                    <span class="ladda-label">Update Bus Details</span> <span class="ladda-spinner"></span>
                </button>
                 <a href="view_all_buses.php" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>

 

<?php include_once('foot.php');?>

<script>
$(document).ready(function() {
   
 
    $('#existing-images-container').on('click', '.delete-image-btn', function() {
        const imageId = $(this).data('image-id');
        const wrapper = $('#image-wrapper-' + imageId);
        const imagesToDeleteInput = $('#imagesToDelete');
         
        let currentVal = imagesToDeleteInput.val();
        let newArr = currentVal ? currentVal.split(',') : [];
        if (!newArr.includes(String(imageId))) {
            newArr.push(imageId);
            imagesToDeleteInput.val(newArr.join(','));
        }
         
        wrapper.fadeOut(300, function() { $(this).remove(); });
    });
 
    $('#bus_images').on('change', function() {
        const previewContainer = $('#image-preview-container');
        previewContainer.empty();
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
<script>
$(document).ready(function () {
    const hiddenSelect = $('#hiddenCategories');
    const selectedDisplay = $('#selectedCategoriesDisplay');
    const categoryDropdown = $('#categoryDropdown');
    const optionsList = $('#categoryOptionsList');
    const categorySearch = $('#categorySearch');

    function refreshSelectedDisplay() {
        selectedDisplay.empty();
        const selectedOptions = hiddenSelect.find('option:selected');

        if (selectedOptions.length === 0) {
            selectedDisplay.html('<span class="select-placeholder">Select categories...</span>');
        } else {
            selectedOptions.each(function () {
                const value = $(this).val();
                const text = $(this).text();
                const pill = $(`
                    <span class="selected-category-pill" data-value="${value}">
                        ${text} <span class="remove-pill">&times;</span>
                    </span>
                `);
                selectedDisplay.append(pill);
            });
        }
    }

    function populateDropdownOptions() {
        optionsList.empty();
        hiddenSelect.find('option').each(function () {
            const value = $(this).val();
            const text = $(this).text();
            const isSelected = $(this).is(':selected');
            const li = $(`<li class="category-option" data-value="${value}">${text}</li>`);
            if (isSelected) li.addClass('selected');
            optionsList.append(li);
        });
    }

    // Toggle dropdown visibility
    selectedDisplay.on('click', function () {
        categoryDropdown.toggle();
    });

    // Handle option click
    optionsList.on('click', '.category-option', function () {
        const value = $(this).data('value');
        const option = hiddenSelect.find(`option[value="${value}"]`);
        if (option.prop('selected')) {
            option.prop('selected', false);
            $(this).removeClass('selected');
        } else {
            option.prop('selected', true);
            $(this).addClass('selected');
        }
        refreshSelectedDisplay();
    });

    // Remove pill
    selectedDisplay.on('click', '.remove-pill', function (e) {
        e.stopPropagation();
        const pill = $(this).closest('.selected-category-pill');
        const value = pill.data('value');
        hiddenSelect.find(`option[value="${value}"]`).prop('selected', false);
        optionsList.find(`.category-option[data-value="${value}"]`).removeClass('selected');
        refreshSelectedDisplay();
    });

    // Filter dropdown options
    categorySearch.on('keyup', function () {
        const term = $(this).val().toLowerCase();
        optionsList.find('.category-option').each(function () {
            const text = $(this).text().toLowerCase();
            $(this).toggle(text.includes(term));
        });
    });

    // Close dropdown on outside click
    $(document).on('click', function (e) {
        if (!$(e.target).closest('.custom-select-container').length) {
            categoryDropdown.hide();
        }
    });

    // Initial setup
    populateDropdownOptions();
    refreshSelectedDisplay();
});
</script>

</body>
</html>
<?php pdo_close_conn($_conn_db); ?>
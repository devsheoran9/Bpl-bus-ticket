<?php
// view_buses.php (Fully Custom CSS Card Layout with Live Search)
global $_conn_db;
include_once('function/_db.php');
session_security_check(); 

// Fetch all buses from the database
try {
    $sql = "SELECT b.*, o.operator_name FROM buses b LEFT JOIN operators o ON b.operator_id = o.operator_id ORDER BY b.bus_id DESC";
    $stmt = $_conn_db->query($sql);
    $buses = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $buses = []; // Gracefully handle DB error
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include_once('head.php');?>
    <title>Manage Buses</title>
    
    <!-- ================== NEW CUSTOM STYLES ================== -->
    <style>
        .page-header {
            display: flex;
            flex-wrap: wrap; /* Allow wrapping on small screens */
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        .search-wrapper {
            position: relative;
            width: 100%;
            max-width: 350px; /* Limit search bar width */
        }
        .search-wrapper .form-control {
            padding-left: 2.5rem;
        }
        .search-wrapper .fa-search {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
        }
        
        /* Card Grid Layout */
        .buses-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
        }
        .bus-card {
            background-color: #fff;
            border: 1px solid #dee2e6;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            display: flex;
            flex-direction: column;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .bus-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }
        .bus-card-header {
            padding: 1rem 1.25rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #dee2e6;
        }
        .bus-card-header .bus-name { font-size: 1.1rem; font-weight: 600; color: #0d6efd; }
        .bus-card-body {
            padding: 1.25rem;
            flex-grow: 1;
        }
        .bus-card .info-item {
            display: flex;
            align-items: center;
            margin-bottom: 0.75rem;
            font-size: 0.9rem;
            color: #495057;
        }
        .bus-card .info-item i {
            color: #6c757d;
            width: 20px;
            margin-right: 12px;
            text-align: center;
        }
        .bus-card-footer {
            padding: 0.75rem 1.25rem;
            background-color: #f8f9fa;
            border-top: 1px solid #dee2e6;
            border-bottom-left-radius: 12px;
            border-bottom-right-radius: 12px;
            display: flex;
            justify-content: flex-end;
            gap: 0.5rem;
        }
        #no-results-message {
            grid-column: 1 / -1; /* Make message span full grid width */
            text-align: center;
            padding: 2rem;
            font-size: 1.2rem;
            color: #6c757d;
        }
    </style>
</head>
<body>

<div id="wrapper">
    <?php include_once('sidebar.php');?>
    <div class="main-content">
        <?php include_once('header.php');?>
        <div class="container-fluid">
            
            <div class="page-header mt-4">
                <h2 class="mb-0">Manage Buses</h2>
                <div class="d-flex align-items-center gap-2">
                    <div class="search-wrapper">
                        <i class="fas fa-search"></i>
                        <input type="text" id="bus-search-input" class="form-control" placeholder="Search buses...">
                    </div>
                    <a href="add_bus.php" class="btn btn-primary"><i class="fas fa-plus"></i> Add Bus</a>
                </div>
            </div>

            <div class="buses-grid" id="buses-grid-container">
                <?php if (empty($buses)): ?>
                    <p id="no-results-message">No buses found. Please add a new bus.</p>
                <?php else: ?>
                    <?php foreach ($buses as $bus):
                        $status_badge = 'bg-secondary';
                        if ($bus['status'] == 'Active') $status_badge = 'bg-success';
                        elseif ($bus['status'] == 'Inactive') $status_badge = 'bg-warning text-dark';
                    ?>
                        <div class="bus-card" data-bus-id="<?php echo $bus['bus_id']; ?>">
                            <div class="bus-card-header">
                                <span class="bus-name"><i class="fas fa-bus-alt me-2"></i><?php echo htmlspecialchars($bus['bus_name']); ?></span>
                                <span class="badge <?php echo $status_badge; ?>"><?php echo htmlspecialchars($bus['status']); ?></span>
                            </div>
                            <div class="bus-card-body">
                                <div class="info-item">
                                    <i class="fas fa-id-card fa-fw"></i>
                                    <span><?php echo htmlspecialchars($bus['registration_number']); ?></span>
                                </div>
                                <div class="info-item">
                                    <i class="fas fa-user-tie fa-fw"></i>
                                    <span>Operator: <strong><?php echo htmlspecialchars($bus['operator_name'] ?? 'N/A'); ?></strong></span>
                                </div>
                            </div>
                            <div class="bus-card-footer">
                                <a href="edit_bus.php?bus_id=<?php echo $bus['bus_id']; ?>" class="btn btn-sm btn-outline-warning" title="Edit Bus"><i class="fas fa-edit"></i> Edit</a>
                                <a href="manage_seats.php?bus_id=<?php echo $bus['bus_id']; ?>" class="btn btn-sm btn-outline-info" title="Manage Seats"><i class="fas fa-chair"></i> Seats</a>
                                <button type="button" class="btn btn-sm btn-outline-danger delete-bus-btn" 
                                        data-bus-id="<?php echo $bus['bus_id']; ?>" 
                                        data-bus-name="<?php echo htmlspecialchars($bus['bus_name']); ?>"
                                        title="Delete Bus">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <div id="no-results-message" style="display: none;">No buses match your search.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include_once('foot.php');?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {
    // --- Live Search Functionality ---
    const searchInput = document.getElementById('bus-search-input');
    const busCards = document.querySelectorAll('.bus-card');
    const noResultsMessage = document.getElementById('no-results-message');

    searchInput.addEventListener('keyup', function(event) {
        const searchTerm = event.target.value.toLowerCase();
        let visibleCount = 0;

        busCards.forEach(card => {
            const cardText = card.textContent.toLowerCase();
            if (cardText.includes(searchTerm)) {
                card.style.display = 'flex'; // Use 'flex' to match the original style
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });
        
        // Show/hide the "no results" message
        if (visibleCount === 0) {
            noResultsMessage.style.display = 'block';
        } else {
            noResultsMessage.style.display = 'none';
        }
    });

    // --- Delete Functionality (using delegated event listener) ---
    $('#buses-grid-container').on('click', '.delete-bus-btn', function() {
        const busId = $(this).data('bus-id');
        const busName = $(this).data('bus-name');
        const card = $(this).closest('.bus-card');

        Swal.fire({
            title: `Delete "${busName}"?`,
            text: "This action cannot be undone!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: 'function/backend/bus_actions.php', // Make sure this backend file exists
                    type: 'POST',
                    dataType: 'json',
                    data: { action: 'delete_bus', bus_id: busId },
                    success: function(response) {
                        if (response.res === 'true') {
                            $.notify({ title: response.notif_title, message: response.notif_desc }, { type: 'success' });
                            // Fade out and remove the card
                            card.fadeOut(500, function() {
                                $(this).remove();
                            });
                        } else {
                            Swal.fire('Error!', response.notif_desc, 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('Error!', 'Could not connect to the server.', 'error');
                    }
                });
            }
        });
    });
});
</script>

</body>
</html>
<?php pdo_close_conn($_conn_db); ?>
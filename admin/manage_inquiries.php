<?php
// manage_inquiries.php (Fully Custom Table & Responsive Cards)
include_once('function/_db.php');
session_security_check();
check_permission('can_manage_enqury');

try {
    $stmt = $_conn_db->query("SELECT * FROM charter_inquiries ORDER BY inquiry_date DESC");
    $inquiries = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database Error: Could not fetch inquiries. " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include "head.php"; ?>
    <title>Manage Charter Inquiries</title>
    <style>
        :root {
            --primary-color: #0d6efd;
            --success-color: #198754;
            --warning-color: #ffc107;
            --info-color: #0dcaf0;
            --danger-color: #dc3545;
            --light-color: #f8f9fa;
            --dark-color: #212529;
            --muted-color: #6c757d;
            --border-color: #dee2e6;
            --card-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }
        body { background-color: var(--light-color); }
        .page-title { font-weight: 700; }

        /* --- Search Bar --- */
        .search-container {
            margin-bottom: 1.5rem;
        }
        .search-wrapper {
            position: relative;
        }
        .search-input {
            padding-left: 2.5rem;
            border-radius: 8px;
        }
        .search-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--muted-color);
        }

        /* --- Custom Table (Desktop View) --- */
        .custom-table-wrapper {
            background-color: #fff;
            border-radius: 12px;
            box-shadow: var(--card-shadow);
            overflow: hidden;
        }
        .custom-table {
            width: 100%;
            border-collapse: collapse;
        }
        .custom-table thead {
            background-color: var(--light-color);
        }
        .custom-table th {
            padding: 1rem;
            text-align: left;
            font-weight: 600;
            color: var(--muted-color);
            text-transform: uppercase;
            font-size: 0.8rem;
            letter-spacing: 0.5px;
            border-bottom: 2px solid var(--border-color);
        }
        .custom-table tbody tr {
            border-bottom: 1px solid var(--border-color);
            transition: background-color 0.2s ease;
        }
        .custom-table tbody tr:last-child {
            border-bottom: none;
        }
        .custom-table tbody tr:hover {
            background-color: #f1f3f5;
        }
        .custom-table td {
            padding: 1rem;
            vertical-align: middle;
        }
        .message-cell {
            white-space: normal;
            min-width: 250px;
        }
        .actions-cell {
            white-space: nowrap;
            text-align: right;
        }
        .actions-cell .btn {
            margin-left: 5px;
        }
        .status-badge {
            padding: 0.4em 0.7em;
            font-size: 0.75rem;
            font-weight: 600;
            border-radius: 20px;
        }

        /* =================================================== */
        /* === CUSTOM RESPONSIVE CARD STYLES (Mobile View) === */
        /* =================================================== */
        @media (max-width: 991px) {
            .custom-table thead {
                display: none; /* Hide table header */
            }
            .custom-table, .custom-table tbody, .custom-table tr, .custom-table td {
                display: block;
                width: 100%;
            }
            .custom-table-wrapper {
                background: none;
                box-shadow: none;
            }
            .custom-table tr {
                background: #fff;
                border-radius: 12px;
                margin-bottom: 1.5rem;
                padding: 1rem;
                box-shadow: var(--card-shadow);
                border: 1px solid var(--border-color);
            }
            .custom-table td {
                padding: 0.5rem 0;
                display: flex;
                justify-content: space-between;
                align-items: center;
                border: none;
                border-bottom: 1px dashed var(--border-color);
            }
            .custom-table tr td:last-child {
                border-bottom: none;
            }
            .custom-table td:before {
                content: attr(data-label); /* Get label from data-label attribute */
                font-weight: 600;
                color: var(--muted-color);
                text-align: left;
                padding-right: 1rem;
            }
            .actions-cell {
                justify-content: center !important;
                margin-top: 1rem;
                padding-top: 1rem;
                border-top: 1px solid var(--border-color);
            }
            .actions-cell:before {
                content: none; /* No label for actions */
            }
        }
    </style>
</head>
<body>
    <div id="wrapper">
        <?php include_once('sidebar.php'); ?>
        <div class="main-content">
            <?php include_once('header.php'); ?>
            <div class="container-fluid">
                <h2 class="my-4 page-title">Manage Charter Inquiries</h2>

                <!-- Search Bar -->
                <div class="search-container">
                    <div class="search-wrapper">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" id="searchInput" class="form-control search-input" placeholder="Search by name, mobile, or location...">
                    </div>
                </div>

                <div class="custom-table-wrapper">
                    <table class="custom-table" id="inquiriesTable">
                        <thead>
                            <tr>
                                <th>Customer</th>
                                <th>Mobile</th>
                                <th>Journey</th>
                                <th>Dates</th>
                                <th class="message-cell">Message</th>
                                <th>Status</th>
                                <th class="actions-cell">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($inquiries)): ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted p-5">
                                        <h4>No Inquiries Found</h4>
                                        <p>There are currently no charter requests.</p>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($inquiries as $inquiry): ?>
                                    <tr id="inquiry-row-<?php echo $inquiry['inquiry_id']; ?>">
                                        <td data-label="Customer"><?php echo htmlspecialchars($inquiry['customer_name']); ?></td>
                                        <td data-label="Mobile"><a href="tel:<?php echo htmlspecialchars($inquiry['customer_mobile']); ?>"><?php echo htmlspecialchars($inquiry['customer_mobile']); ?></a></td>
                                        <td data-label="Journey"><?php echo htmlspecialchars($inquiry['from_location']) . ' → ' . htmlspecialchars($inquiry['to_location']); ?></td>
                                        <td data-label="Dates">
                                            <?php echo date('d M Y', strtotime($inquiry['journey_date'])); ?>
                                            <?php if ($inquiry['trip_type'] == 'Round-Trip' && !empty($inquiry['return_date'])): ?>
                                                <br><small class="text-muted">Return: <?php echo date('d M Y', strtotime($inquiry['return_date'])); ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td data-label="Message" class="message-cell"><?php echo htmlspecialchars($inquiry['message']); ?></td>
                                        <td data-label="Status">
                                            <?php
                                            $status_class = 'bg-secondary';
                                            if ($inquiry['status'] == 'Pending') $status_class = 'bg-warning text-dark';
                                            if ($inquiry['status'] == 'Contacted') $status_class = 'bg-info text-dark';
                                            if ($inquiry['status'] == 'Booked') $status_class = 'bg-success';
                                            if ($inquiry['status'] == 'Closed') $status_class = 'bg-light text-dark border';
                                            ?>
                                            <span class="badge status-badge <?php echo $status_class; ?>"><?php echo $inquiry['status']; ?></span>
                                        </td>
                                        <td class="actions-cell">
                                            <div class="btn-group">
                                                <button class="btn btn-sm btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">Status</button>
                                                <ul class="dropdown-menu">
                                                    <li><a class="dropdown-item update-status" href="#" data-id="<?php echo $inquiry['inquiry_id']; ?>" data-status="Pending">Pending</a></li>
                                                    <li><a class="dropdown-item update-status" href="#" data-id="<?php echo $inquiry['inquiry_id']; ?>" data-status="Contacted">Contacted</a></li>
                                                    <li><a class="dropdown-item update-status" href="#" data-id="<?php echo $inquiry['inquiry_id']; ?>" data-status="Booked">Booked</a></li>
                                                    <li><a class="dropdown-item update-status" href="#" data-id="<?php echo $inquiry['inquiry_id']; ?>" data-status="Closed">Closed</a></li>
                                                </ul>
                                            </div>
                                            <button class="btn btn-sm btn-outline-danger delete-inquiry" data-id="<?php echo $inquiry['inquiry_id']; ?>" title="Delete"><i class="fas fa-trash"></i></button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <?php include "foot.php"; ?>
    <!-- Popper v2 -->
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
<!-- Bootstrap 5 -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"></script>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // --- CUSTOM SEARCH FUNCTIONALITY ---
        const searchInput = document.getElementById('searchInput');
        const tableRows = document.querySelectorAll('#inquiriesTable tbody tr');

        searchInput.addEventListener('keyup', function() {
            const searchTerm = searchInput.value.toLowerCase();
            tableRows.forEach(row => {
                const rowText = row.textContent.toLowerCase();
                if (rowText.includes(searchTerm)) {
                    row.style.display = ''; // Show the row
                } else {
                    row.style.display = 'none'; // Hide the row
                }
            });
        });

        // --- ACTION BUTTONS (STATUS UPDATE & DELETE) ---
        const inquiriesTable = document.getElementById('inquiriesTable');

        inquiriesTable.addEventListener('click', function(event) {
            const target = event.target;

            // --- Handle Status Update ---
            if (target.classList.contains('update-status')) {
                event.preventDefault();
                const inquiryId = target.dataset.id;
                const newStatus = target.dataset.status;
                
                // Show loading state (optional but good UX)
                target.closest('.btn-group').querySelector('button').disabled = true;

                fetch('function/backend/inquiry_actions.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `action=update_status&inquiry_id=${inquiryId}&status=${newStatus}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire('Success!', 'Status updated successfully.', 'success').then(() => {
                            window.location.reload();
                        });
                    } else {
                        Swal.fire('Error!', data.message || 'Could not update status.', 'error');
                    }
                })
                .catch(() => Swal.fire('Error!', 'A server error occurred.', 'error'))
                .finally(() => {
                    target.closest('.btn-group').querySelector('button').disabled = false;
                });
            }

            // --- Handle Deletion ---
            if (target.closest('.delete-inquiry')) {
                event.preventDefault();
                const deleteButton = target.closest('.delete-inquiry');
                const inquiryId = deleteButton.dataset.id;
                
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        fetch('function/backend/inquiry_actions.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                            body: `action=delete_inquiry&inquiry_id=${inquiryId}`
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                document.getElementById(`inquiry-row-${inquiryId}`).remove();
                                Swal.fire('Deleted!', 'The inquiry has been deleted.', 'success');
                            } else {
                                Swal.fire('Error!', data.message || 'Could not delete the inquiry.', 'error');
                            }
                        })
                        .catch(() => Swal.fire('Error!', 'A server error occurred.', 'error'));
                    }
                });
            }
        });
    });
    </script>
</body>
</html>
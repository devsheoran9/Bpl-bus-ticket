<?php
// ================== SETUP AND DATA FETCHING ==================
include_once('function/_db.php'); // Your database connection file
session_security_check(); // Your function to check if admin is logged in
check_permission('can_manage_reviews'); // Permission check specific to this page

// --- HELPER FUNCTIONS (From your original review file) ---
function render_stars($rating)
{
    $stars_html = '';
    for ($i = 1; $i <= 5; $i++) {
        $iconClass = ($i <= $rating) ? 'bi-star-fill text-warning' : 'bi-star text-muted';
        $stars_html .= '<i class="bi ' . $iconClass . '"></i> ';
    }
    return $stars_html;
}

function get_initials($name)
{
    $words = explode(' ', trim($name));
    $initials = '';
    if (count($words) > 0 && !empty($words[0])) {
        $initials .= strtoupper(substr($words[0], 0, 1));
    }
    if (count($words) > 1 && !empty(end($words))) {
        $initials .= strtoupper(substr(end($words), 0, 1));
    }
    return $initials ?: '?';
}

// --- DATA FETCHING (From your original review file) ---
$reviews = [];
try {
    // Fetch all reviews (approved or all, depending on your needs)
    $stmt = $_conn_db->query("SELECT id, user_name, rating, review_text, created_at, status FROM reviews ORDER BY created_at DESC");
    $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Reviews Page DB Error: " . $e->getMessage());
}

// Check for admin delete permissions (can be simplified if already checked by session_security_check)
$can_delete_reviews = true; // Assuming admin has this right. You can add more complex checks.

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php include "head.php"; ?>
    <title>Manage Customer Reviews</title>
    <!-- Bootstrap Icons - This is required for the stars and trash icon -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Custom Styles for Review Cards -->
    <style>
        /* Styles from your original review file */
        .review-card {
            background-color: #ffffff;
            border: 1px solid #e9ecef;
            border-radius: 12px;
            padding: 24px;
            height: 100%;
            display: flex;
            flex-direction: column;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            position: relative;
            /* For the delete button */
        }

        .review-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
        }

        .review-header {
            display: flex;
            align-items: center;
            margin-bottom: 16px;
        }

        .author-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background-color: #0d6efd;
            /* Matching admin theme color */
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            font-weight: 600;
            margin-right: 15px;
            flex-shrink: 0;
        }

        .author-name {
            font-weight: 600;
            color: #343a40;
            font-size: 1.1em;
        }

        .review-content {
            font-size: 1em;
            color: #555;
            line-height: 1.6;
            margin-bottom: 20px;
            flex-grow: 1;
        }

        .review-footer {
            font-size: 0.85em;
            color: #888;
            text-align: right;
        }

        .btn-delete-review {
            position: absolute;
            top: 10px;
            right: 10px;
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background-color: rgba(220, 53, 69, 0.1);
            color: #dc3545;
            border: 1px solid rgba(220, 53, 69, 0.2);
            opacity: 1;
            transition: opacity 0.2s ease, background-color 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .review-card:hover .btn-delete-review {
            opacity: 1;
        }

        .btn-delete-review:hover {
            background-color: #dc3545;
            color: #fff;
        }
    </style>
</head>

<body>
    <div id="wrapper">
        <?php include_once('sidebar.php'); ?>
        <div class="main-content">
            <?php include_once('header.php'); ?>
            <div class="container-fluid">
                <!-- Page Title (from view_users structure) -->
                <h2 class="my-4">Manage Customer Reviews</h2>

                <!-- Main Content Area -->
                <?php if (empty($reviews)): ?>
                    <div class="text-center py-5 card">
                        <div class="card-body">
                            <i class="bi bi-chat-quote fs-1 text-muted"></i>
                            <h3 class="mt-3">No Reviews Found</h3>
                            <p class="text-muted">There are currently no customer reviews to display.</p>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="row g-4">
                        <?php foreach ($reviews as $review): ?>
                            <div class="col-lg-4 col-md-6 d-flex">
                                <div class="review-card w-100" id="review-card-<?php echo $review['id']; ?>">
                                    <?php if ($can_delete_reviews): ?>
                                        <button class="btn btn-delete-review" data-review-id="<?php echo $review['id']; ?>" title="Delete Review">
                                            <i class="bi bi-trash-fill"></i>
                                        </button>
                                    <?php endif; ?>

                                    <div class="review-header">
                                        <div class="author-avatar"><?php echo get_initials($review['user_name']); ?></div>
                                        <div>
                                            <div class="author-name"><?php echo htmlspecialchars($review['user_name']); ?></div>
                                            <div class="rating-stars"><?php echo render_stars($review['rating']); ?></div>
                                        </div>
                                    </div>
                                    <div class="review-content">
                                        <p>"<?php echo nl2br(htmlspecialchars($review['review_text'])); ?>"</p>
                                    </div>
                                    <div class="review-footer">
                                        Posted on <?php echo date('d M, Y', strtotime($review['created_at'])); ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </div>

    <?php include "foot.php"; ?>
    <!-- Assumes foot.php includes jQuery and SweetAlert2 -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const reviewContainer = document.querySelector('.main-content'); // Use a stable parent element

            reviewContainer.addEventListener('click', function(event) {
                const deleteButton = event.target.closest('.btn-delete-review');

                if (!deleteButton) {
                    return; // Exit if the click was not on a delete button
                }

                const reviewId = deleteButton.dataset.reviewId;
                const reviewCard = document.getElementById(`review-card-${reviewId}`);

                // Using Swal from your first file
                Swal.fire({
                    title: 'Are you sure?',
                    text: "This review will be permanently deleted!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        const formData = new FormData();
                        formData.append('action', 'delete_review');
                        formData.append('review_id', reviewId);

                        // Using fetch from your first file
                        fetch('function/backend/review_actions.php', {
                                method: 'POST',
                                body: formData
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.status === 'success') {
                                    // Animate and remove the card on success
                                    reviewCard.style.transition = 'opacity 0.5s, transform 0.5s';
                                    reviewCard.style.opacity = '0';
                                    reviewCard.style.transform = 'scale(0.9)';
                                    setTimeout(() => {
                                        reviewCard.parentElement.remove(); // Remove the parent col
                                    }, 500);

                                    Swal.fire('Deleted!', data.message, 'success');
                                } else {
                                    Swal.fire('Error!', data.message, 'error');
                                }
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                Swal.fire('Error!', 'An unexpected error occurred.', 'error');
                            });
                    }
                });
            });
        });
    </script>
</body>

</html>
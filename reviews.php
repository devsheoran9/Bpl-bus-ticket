<?php
include 'db_connect.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Helper function to display rating stars
function render_stars($rating)
{
    $stars_html = '';
    for ($i = 1; $i <= 5; $i++) {
        $iconClass = ($i <= $rating) ? 'bi-star-fill text-warning' : 'bi-star text-muted';
        $stars_html .= '<i class="bi ' . $iconClass . '"></i> ';
    }
    return $stars_html;
}

// --- UPDATED QUERY ---
// Fetch only reviews where status is 1 (Active/Approved)
$reviews_result = $conn->query("SELECT user_name, rating, review_text, created_at FROM reviews WHERE status = 1 ORDER BY created_at DESC");

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Reviews - Fouji Travels</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }

        .review-card {
            background: #fff;
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .review-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }

        .review-header .user-name {
            font-weight: bold;
            font-size: 1.1rem;
        }

        .review-header .date {
            font-size: 0.85rem;
            color: #6c757d;
        }

        .review-body {
            flex-grow: 1;
        }

        .review-body p {
            font-size: 0.95rem;
            line-height: 1.6;
        }

        .read-more-btn {
            cursor: pointer;
            color: #d32f2f;
            font-weight: bold;
            text-decoration: none;
        }

        .read-more-btn:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>

    <?php include 'includes/header.php'; ?>

    <main class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h2">What Our Customers Say</h1>
            <a href="add_review.php" class="btn btn-danger"><i class="bi bi-pencil-square"></i> Write a Review</a>
        </div>

        <?php if ($reviews_result && $reviews_result->num_rows > 0) : ?>
            <div class="row">
                <?php while ($review = $reviews_result->fetch_assoc()) : ?>
                    <div class="col-lg-4 col-md-6 mb-4 d-flex align-items-stretch">
                        <div class="review-card">
                            <div class="review-header">
                                <span class="user-name"><?php echo htmlspecialchars($review['user_name']); ?></span>
                                <span class="date"><?php echo date('d M Y', strtotime($review['created_at'])); ?></span>
                            </div>
                            <div class="rating-stars mb-2">
                                <?php echo render_stars($review['rating']); ?>
                            </div>
                            <div class="review-body">
                                <?php
                                $full_text = nl2br(htmlspecialchars($review['review_text']));
                                $char_limit = 180;

                                if (strlen($review['review_text']) > $char_limit) {
                                    $short_text = substr($full_text, 0, $char_limit);
                                    $last_space = strrpos($short_text, ' ');
                                    $short_text = substr($short_text, 0, $last_space) . '...';

                                    echo "<p class='review-text mb-2'>
                                                <span class='short-text'>{$short_text}</span>
                                                <span class='full-text' style='display: none;'>{$full_text}</span>
                                              </p>
                                              <a class='read-more-btn small'>Read More</a>";
                                } else {
                                    echo "<p class='mb-0'>{$full_text}</p>";
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else : ?>
            <div class="review-card text-center py-5">
                <p class="lead mb-0">No approved reviews have been submitted yet.</p>
                <p class="text-muted">Be the first to share your experience!</p>
            </div>
        <?php endif; ?>

    </main>

    <?php $conn->close(); ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.read-more-btn').forEach(button => {
                button.addEventListener('click', function(event) {
                    event.preventDefault();
                    const reviewBody = this.closest('.review-body');
                    const shortText = reviewBody.querySelector('.short-text');
                    const fullText = reviewBody.querySelector('.full-text');

                    if (fullText.style.display === 'none') {
                        shortText.style.display = 'none';
                        fullText.style.display = 'inline';
                        this.textContent = 'Read Less';
                    } else {
                        shortText.style.display = 'inline';
                        fullText.style.display = 'none';
                        this.textContent = 'Read More';
                    }
                });
            });
        });
    </script>
</body>

</html>
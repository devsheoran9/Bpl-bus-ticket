<?php
include 'includes/header.php';

// --- HELPER FUNCTIONS (Unchanged) ---
function render_stars($rating)
{
    $stars_html = '';
    for ($i = 1; $i <= 5; $i++) {
        $iconClass = ($i <= $rating) ? 'bi-star-fill text-warning' : 'bi-star text-muted';
        $stars_html .= '<i class="bi ' . $iconClass . '"></i> ';
    }
    return $stars_html;
}

function mask_email($email)
{
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return '';
    }
    list($first, $last) = explode('@', $email);
    $first = substr($first, 0, 2) . str_repeat('*', max(1, strlen($first) - 2));
    return $first . '@' . $last;
}

// --- PAGINATION LOGIC (Converted to PDO) ---
$reviews_per_page = 9;

// FIX: Use PDO to get the total count of reviews
$count_stmt = $pdo->query("SELECT COUNT(*) FROM reviews WHERE status = 1");
$total_reviews = $count_stmt->fetchColumn();

$total_pages = ceil($total_reviews / $reviews_per_page);
$current_page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
if ($current_page > $total_pages && $total_pages > 0) {
    $current_page = $total_pages;
}
if ($current_page < 1) {
    $current_page = 1;
}
$offset = ($current_page - 1) * $reviews_per_page;

// --- SQL QUERY (Converted to PDO) ---
// FIX: Use PDO prepare, bindValue, and execute
$stmt = $pdo->prepare("SELECT user_name, email, rating, review_text, created_at FROM reviews WHERE status = 1 ORDER BY created_at DESC LIMIT :limit OFFSET :offset");
$stmt->bindValue(':limit', $reviews_per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();

?>

<style>

    .testimonial-card {
        background-color: #fff;
        border-radius: 1rem;
        padding: 2.5rem;
        border: 1px solid #e9ecef;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.04);
        transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
        display: flex;
        flex-direction: column;
        height: 100%;
        position: relative;
        overflow: hidden;
    }

    .testimonial-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
    }

    .testimonial-icon {
        position: absolute;
        top: 1.5rem;
        left: 2.5rem; 
        color: #cc2020ff;
        z-index: 1;
    }

    .rating-line,
    .review-text,
    .author-info {
        position: relative;
        z-index: 2;
    }

    .rating-line {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
    }

    .rating-stars {
        font-size: 1.1rem;
    }

    .masked-email {
        font-size: 0.8rem;
        color: #6c757d;
        font-style: normal;
    }

    .review-text {
        font-size: 1rem;
        color: #495057;
        line-height: 1.7;
        font-style: italic;
        flex-grow: 1;
        word-wrap: break-word;
    }

    .author-info {
        margin-top: 1.5rem;
        font-style: normal;
    }

    .author-name {
        font-weight: 600;
        color: #d32f2f;
    }

    .review-date {
        font-size: 0.8rem;
        color: #6c757d;
        margin-top: 2px;
    }

    .read-more-btn {
        cursor: pointer;
        color: #0d6efd;
        font-weight: 500;
        text-decoration: none;
        font-style: normal;
        font-size: 0.9rem;
    }

    .read-more-btn:hover {
        text-decoration: underline;
    }

    .info-section {
        background-color: #fff;
        padding: 3rem;
        border-radius: 1rem;
        margin-bottom: 3rem;
        border: 1px solid #e9ecef;
    }

    .info-section .icon {
        font-size: 2.5rem;
        color: #d32f2f;
        margin-bottom: 1rem;
    }

    .pagination .page-item.active .page-link {
        background-color: #d32f2f;
        border-color: #d32f2f;
    }

    .pagination .page-link {
        color: #d32f2f;
    }

    .pagination .page-link:hover {
        color: #a02424;
    }
</style>


<body>
    <main class="container py-5 mt-5">
        <div class="text-center mb-5">
            <h1 class="h2">What Our Customers Say</h1>
            <p class="lead text-muted col-lg-8 mx-auto">At BPL Travels, we are committed to providing an exceptional travel experience. Our passengers' feedback is the cornerstone of our service, helping us improve and innovate. Here are real stories from our valued passengers.</p>
        </div>

        <?php if ($stmt->rowCount() > 0) : ?>
            <div class="row">
                <?php while ($review = $stmt->fetch()) : ?>
                    <div class="col-lg-4 col-md-6 mb-4   align-items-stretch">
                        <div class="testimonial-card">
                            <div class="testimonial-icon"><i class="bi bi-quote"></i></div>

                            <div class="rating-line">
                                <div class="rating-stars"><?php echo render_stars($review['rating']); ?></div>
                                <div class="masked-email"><?php echo mask_email($review['email']); ?></div>
                            </div>

                            <div class="review-text">
                                <?php
                                $full_text = htmlspecialchars($review['review_text']);
                                $char_limit = 100;
                                if (mb_strlen($review['review_text']) > $char_limit) {
                                    $short_text = mb_substr($full_text, 0, $char_limit);
                                    $last_space = mb_strrpos($short_text, ' ');
                                    if ($last_space !== false) {
                                        $short_text = mb_substr($short_text, 0, $last_space);
                                    }
                                    echo "<span class='short-text'>&ldquo;{$short_text}...&rdquo;</span><span class='full-text' style='display: none;'>&ldquo;{$full_text}&rdquo;</span><a class='read-more-btn d-block mt-2'>Read More</a>";
                                } else {
                                    echo "&ldquo;{$full_text}&rdquo;";
                                }
                                ?>
                            </div>
                            <div class="author-info">
                                <div class="author-name">- <?php echo htmlspecialchars($review['user_name']); ?></div>
                                <div class="review-date">Reviewed on <?php echo date('d M Y', strtotime($review['created_at'])); ?></div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>

            <?php if ($total_pages > 1) : ?>
                <nav aria-label="Page navigation">
                    <ul class="pagination justify-content-center mt-4">
                        <li class="page-item <?php if ($current_page <= 1) {
                                                    echo 'disabled';
                                                } ?>">
                            <a class="page-link" href="?page=<?php echo $current_page - 1; ?>">Previous</a>
                        </li>
                        <?php for ($i = 1; $i <= $total_pages; $i++) : ?>
                            <li class="page-item <?php if ($current_page == $i) {
                                                        echo 'active';
                                                    } ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>
                        <li class="page-item <?php if ($current_page >= $total_pages) {
                                                    echo 'disabled';
                                                } ?>">
                            <a class="page-link" href="?page=<?php echo $current_page + 1; ?>">Next</a>
                        </li>
                    </ul>
                </nav>
            <?php endif; ?>

        <?php else : ?>
            <div class="col-12">
                <div class="testimonial-card text-center py-5">
                    <p class="lead mb-0">No approved reviews have been submitted yet.</p>
                    <p class="text-muted">Why not be the first to share your experience?</p>
                    <a href="add_review" class="btn btn-danger mt-3"><i class="bi bi-pencil-square"></i> Write a Review</a>
                </div>
            </div>
        <?php endif; ?>


    </main>
    <div class="info-section text-center   container ">
        <div class="icon"><i class="bi bi-card-checklist"></i></div>
        <h2 class="h3">How to Share Your Story</h2>
        <p class="col-lg-8 mx-auto text-muted">Have you recently traveled with us? We'd love to hear about your experience! Your feedback helps other travelers make informed decisions and allows us to continually enhance our services. Simply log in to your account and click the 'Write a Review' button.</p>
        <a href="add_review" class="btn btn-outline-danger mt-3">Get Started</a>
    </div>


    <?php  
    $pdo = null;
    ?>
    <?php include "includes/footer.php" ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.read-more-btn').forEach(button => {
                button.addEventListener('click', function(event) {
                    event.preventDefault();
                    const reviewTextContainer = this.closest('.review-text');
                    const shortText = reviewTextContainer.querySelector('.short-text');
                    const fullText = reviewTextContainer.querySelector('.full-text');
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
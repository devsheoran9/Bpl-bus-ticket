<?php
include 'includes/header.php';
// This function should check if the user is logged in.
// echo user_login('page'); 

// --- SECTION 1: USER DATA FOR THE FORM ---
$userName = '';
$userEmail = '';
$userPhone = '';
$userDataAvailable = false;

if (isset($_SESSION['user_id'])) {
    try {
        $stmt = $pdo->prepare("SELECT username, email, mobile_no FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user_data = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user_data) {
            $userDataAvailable = true;
            $userName = $user_data['username'] ?? '';
            $userEmail = $user_data['email'] ?? '';
            $userPhone = $user_data['mobile_no'] ?? '';
        }
    } catch (PDOException $e) {
        error_log("Failed to fetch user data for review page: " . $e->getMessage());
    }
}

// --- SECTION 2: FETCH RECENT REVIEWS TO DISPLAY ---
// Helper functions needed for displaying reviews
function render_stars($rating) {
    $stars_html = '';
    $rating = round($rating);
    for ($i = 1; $i <= 5; $i++) {
        $stars_html .= '<i class="bi ' . (($i <= $rating) ? 'bi-star-fill text-warning' : 'bi-star text-secondary') . '"></i>';
    }
    return $stars_html;
}

function truncate_by_words($text, $word_limit) {
    $words = preg_split("/\s+/", $text);
    if (count($words) > $word_limit) {
        return implode(' ', array_slice($words, 0, $word_limit)) . '...';
    }
    return $text;
}

// Fetch the 6 most recent approved reviews
$recent_reviews_stmt = $pdo->prepare("SELECT user_name, rating, review_text, created_at FROM reviews WHERE status = 1 ORDER BY created_at DESC LIMIT 6");
$recent_reviews_stmt->execute();
$recent_reviews = $recent_reviews_stmt->fetchAll(PDO::FETCH_ASSOC);

?>
 
    <style>
        :root {
            --primary-color: #d32f2f;
            --primary-dark: #b71c1c;
            --text-dark: #2d3748;
            --text-light: #718096;
            --bg-light: #f8fafc;
            --bg-white: #ffffff;
            --border-color: #e2e8f0;
            --shadow-sm: 0 2px 8px rgba(0,0,0,0.06);
            --shadow-md: 0 4px 12px rgba(0,0,0,0.1);
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-light);
        }

        .section-title, .page-header h1 {
            font-family: 'Playfair Display', serif;
            color: var(--text-dark);
        }

        .form-container {
            padding-top: 2rem;
            padding-bottom: 4rem;
        }

        .form-card {
            background: var(--bg-white);
            border-radius: 16px;
            box-shadow: var(--shadow-md);
            border: 1px solid var(--border-color);
            position: relative;
            overflow: hidden;
            padding: 2.5rem 3rem;
        }
        
        .form-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: var(--primary-color);
        }
        
        .form-control[readonly] {
            background-color: #f1f3f5;
            cursor: not-allowed;
        }

        .star-rating { display: flex; flex-direction: row-reverse; justify-content: center; gap: 5px; }
        .star-rating input[type="radio"] { display: none; }
        .star-rating label { font-size: 2.8rem; color: #ced4da; cursor: pointer; transition: color 0.2s, transform 0.2s; }
        .star-rating label:hover { transform: scale(1.15); }
        .star-rating input[type="radio"]:checked ~ label, .star-rating label:hover, .star-rating label:hover ~ label { color: #ffc107; }
        @keyframes pulse { 0% { transform: scale(1); } 50% { transform: scale(1.2); } 100% { transform: scale(1); } }
        .star-rating input[type="radio"]:checked ~ label { animation: pulse 0.5s ease-out; }

        .char-counter {
            font-size: 0.85rem;
            color: var(--text-light);
            text-align: right;
            display: block;
            margin-top: 0.25rem;
        }

        .btn-submit { background: linear-gradient(45deg, var(--primary-dark), var(--primary-color)); border: none; padding: 0.8rem 2rem; font-weight: 600; font-size: 1.1rem; border-radius: 50px; transition: all 0.3s ease; box-shadow: 0 4px 15px rgba(211, 47, 47, 0.3); }
        .btn-submit:hover { transform: translateY(-3px); box-shadow: 0 7px 20px rgba(211, 47, 47, 0.4); }
        .btn-submit:disabled { background: #adb5bd; transform: none; box-shadow: none; }
        .form-control:focus { border-color: var(--primary-color); box-shadow: 0 0 0 0.25rem rgba(211, 47, 47, 0.25); }

        .recent-reviews-section {
            background-color: var(--bg-white);
            padding: 4rem 0;
            border-top: 1px solid var(--border-color);
        }
        .testimonial-card { padding: 1.75rem; height: 100%; display: flex; flex-direction: column; transition: transform 0.2s ease-out, box-shadow 0.2s ease-out; background: var(--bg-white); border-radius: 16px; box-shadow: var(--shadow-sm); border: 1px solid var(--border-color); position: relative; overflow: hidden;}
        .testimonial-card:hover { transform: translateY(-5px); box-shadow: var(--shadow-md); }
        .testimonial-card::before { content: ''; position: absolute; top: 0; left: 0; width: 100%; height: 5px; background: var(--primary-color); }
        .author-avatar { width: 48px; height: 48px; border-radius: 50%; background: linear-gradient(135deg, #e57373, var(--primary-color)); color: white; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 1.3rem; }
        .author-details h6 { font-weight: 700; margin-bottom: 0; }
        .review-date { font-size: 0.85rem; color: var(--text-light); }
        .review-text { color: var(--text-dark); flex-grow: 1; margin-top: 1.25rem; line-height: 1.7; }
    </style>
 

    <main>
        <div class="form-container">
            <div class="container">
                 <div class="page-header text-center mb-5">
                    <h1>Tell Us About Your Journey</h1>
                    <p class="lead text-secondary">Your feedback helps us improve and guides fellow travelers.</p>
                </div>
                <div class="row justify-content-center">
                    <div class="col-lg-8">
                        <div class="form-card">
                            <?php if ($userDataAvailable) : ?>
                                <?php if (isset($_GET['review_success'])): ?>
                                    <div class="alert alert-success text-center" role="alert">
                                        <i class="bi bi-check-circle-fill fs-3 d-block mb-2"></i>
                                        <h4 class="alert-heading">Thank You!</h4>
                                        <p>Your review has been submitted for approval. We appreciate your feedback.</p>
                                        <hr>
                                        <a href="reviews.php" class="btn btn-outline-success">View All Reviews</a>
                                    </div>
                                <?php else: ?>
                                    <?php if (isset($_GET['error'])): ?>
                                        <div class="alert alert-danger" role="alert"><?= htmlspecialchars($_GET['error']); ?></div>
                                    <?php endif; ?>

                                    <form action="submit_review.php" method="POST" id="reviewForm" novalidate>
                                        <h5 class="mb-3 text-secondary">Your Details</h5>
                                        <div class="row g-3 mb-4">
                                            <div class="col-12 form-floating">
                                                <input type="text" id="username" class="form-control" value="<?= htmlspecialchars($userName); ?>" placeholder="Your Name" readonly>
                                                <label for="username">Name</label>
                                            </div>
                                            <div class="col-md-6 form-floating">
                                                <input type="email" id="email" class="form-control" value="<?= htmlspecialchars($userEmail); ?>" placeholder="your@email.com" readonly>
                                                <label for="email">Email Address</label>
                                            </div>
                                            <div class="col-md-6 form-floating">
                                                <input type="tel" id="mobile_no" class="form-control" value="<?= htmlspecialchars($userPhone); ?>" placeholder="Mobile Number" readonly>
                                                <label for="mobile_no">Mobile Number</label>
                                            </div>
                                            <div class="col-12"><small class="form-text text-muted">Your details are from your account and cannot be changed here.</small></div>
                                        </div>
                                        <hr class="my-4">
                                        <h5 class="mb-3 text-secondary text-center">Your Feedback</h5>
                                        <div class="mb-4 text-center">
                                            <label class="form-label fs-6">Select your overall rating</label>
                                            <div class="star-rating">
                                                <input type="radio" id="rating-5" name="rating" value="5" required><label for="rating-5" title="5 stars"><i class="bi bi-star-fill"></i></label>
                                                <input type="radio" id="rating-4" name="rating" value="4"><label for="rating-4" title="4 stars"><i class="bi bi-star-fill"></i></label>
                                                <input type="radio" id="rating-3" name="rating" value="3"><label for="rating-3" title="3 stars"><i class="bi bi-star-fill"></i></label>
                                                <input type="radio" id="rating-2" name="rating" value="2"><label for="rating-2" title="2 stars"><i class="bi bi-star-fill"></i></label>
                                                <input type="radio" id="rating-1" name="rating" value="1"><label for="rating-1" title="1 star"><i class="bi bi-star-fill"></i></label>
                                            </div>
                                             <div class="invalid-feedback d-block text-center mt-2">Please select a star rating.</div>
                                        </div>
                                        <div class="mb-2 form-floating">
                                            <textarea class="form-control" id="review_text" name="review_text" rows="6" placeholder="Tell us about your trip..." required minlength="20" maxlength="1000" style="height: 150px;"></textarea>
                                            <label for="review_text">Write your review here (min 20 characters)</label>
                                             <div class="invalid-feedback">Please share some details about your experience (at least 20 characters).</div>
                                        </div>
                                        <span id="charCounter" class="char-counter">0 / 1000</span>

                                        <div class="d-grid mt-4">
                                            <button type="submit" class="btn btn-danger btn-submit" id="submitBtn">
                                                <span class="submit-text"><i class="bi bi-send-fill me-2"></i>Submit My Review</span>
                                                <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                                                <span class="loading-text d-none">Submitting...</span>
                                            </button>
                                        </div>
                                    </form>
                                <?php endif; ?>
                            <?php else: ?>
                                <div class="text-center py-5">
                                    <i class="bi bi-box-arrow-in-right display-4 text-primary mb-3"></i>
                                    <h2 class="mb-3">Login to Continue</h2>
                                    <p class="lead text-muted">You must be logged into your account to write a review.</p>
                                    <a href="login.php" class="btn btn-primary mt-3 px-4 py-2">Log In</a>
                                    <a href="register.php" class="btn btn-outline-secondary mt-3 px-4 py-2">Create Account</a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php if (!empty($recent_reviews)): ?>
        <div class="recent-reviews-section">
            <div class="container">
                <h2 class="text-center mb-5 section-title">What Other Travelers Are Saying</h2>
                <div class="row g-4">
                    <?php foreach ($recent_reviews as $review): ?>
                        <div class="col-lg-4 col-md-6">
                            <div class="testimonial-card">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="author-avatar"><?= htmlspecialchars(mb_strtoupper(mb_substr($review['user_name'], 0, 1))) ?></div>
                                        <div class="author-details">
                                            <h6><?= htmlspecialchars($review['user_name']) ?></h6>
                                            <div class="review-date"><?= date('F j, Y', strtotime($review['created_at'])) ?></div>
                                        </div>
                                    </div>
                                    <div class="rating-stars text-nowrap"><?= render_stars($review['rating']) ?></div>
                                </div>
                                <div class="review-text">
                                    "<?= truncate_by_words(htmlspecialchars($review['review_text']), 25) ?>"
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="text-center mt-5">
                    <a href="reviews.php" class="btn btn-outline-danger btn-lg px-5">View All Reviews <i class="bi bi-arrow-right ms-2"></i></a>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </main>

    <?php include "includes/footer.php"; ?> 
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const reviewForm = document.getElementById('reviewForm');
        if (!reviewForm) return;

        const reviewText = document.getElementById('review_text');
        const charCounter = document.getElementById('charCounter');
        const submitBtn = document.getElementById('submitBtn');
        const maxChars = 1000;

        // 1. Character Counter
        reviewText.addEventListener('input', () => {
            const currentLength = reviewText.value.length;
            charCounter.textContent = `${currentLength} / ${maxChars}`;
        });

        // 2. Form Validation and Submission Logic
        reviewForm.addEventListener('submit', function (event) {
            // Prevent default submission to handle it with JS
            event.preventDefault();

            // Add Bootstrap's validation classes
            if (!reviewForm.checkValidity()) {
                event.stopPropagation();
                reviewForm.classList.add('was-validated');
                return;
            }
            reviewForm.classList.add('was-validated');

            // --- If form is valid, show loading state ---
            submitBtn.disabled = true;
            submitBtn.querySelector('.submit-text').classList.add('d-none');
            submitBtn.querySelector('.spinner-border').classList.remove('d-none');
            submitBtn.querySelector('.loading-text').classList.remove('d-none');

            // Submit the form after a short delay to show the spinner
            setTimeout(() => {
                reviewForm.submit();
            }, 500); // 0.5 seconds
        });
    });
    </script>
</body>
</html>
<?php include 'includes/header.php'; 
$loggedIn = (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true);
$userName = '';
$userEmail = '';
$userPhone = '';
echo user_login('page'); 
if ($loggedIn) { 
    $userName = $_SESSION['username'] ?? '';
    $userEmail = $_SESSION['email'] ?? '';
    $userPhone = $_SESSION['mobile_no'] ?? '';
}
?>
 
    <style>
       
        .form-card {
            background: #fff;
            border-radius: 16px;
            padding: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            margin-top: 40px;
        }

        .star-rating {
            display: flex;
            flex-direction: row-reverse;
            justify-content: center;
            gap: 5px;
        }

        .star-rating input[type="radio"] {
            display: none;
        }

        .star-rating label {
            font-size: 2rem;
            color: #ddd;
            cursor: pointer;
            transition: color 0.2s;
        }

        .star-rating input[type="radio"]:checked~label,
        .star-rating label:hover,
        .star-rating label:hover~label {
            color: #ffc107;
        }
    </style>
 

<body>

    <main class="container my-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="form-card">

                    <?php if ($loggedIn) : ?>
                        <h2 class="text-center mb-4">Share Your Experience</h2>

                        <?php if (isset($_GET['review_success'])): ?>
                            <div class="alert alert-success" role="alert">
                                Thank you! Your review has been successfully submitted. You can <a href="reviews" class="alert-link">view it here</a>.
                            </div>
                        <?php endif; ?>

                        <?php if (isset($_GET['error'])): ?>
                            <div class="alert alert-danger" role="alert"><?php echo htmlspecialchars($_GET['error']); ?></div>
                        <?php endif; ?>

                        <!-- The form submits to 'submit_review' -->
                        <form action="submit_review" method="POST">

                            <!-- User Details - Pre-filled but NOT locked -->
                            <div class="row mb-3">
                                <div class="col-md-12 pb-3">
                                    <label for="username" class="form-label">Name</label>
                                    <input type="text" id="username" name="username" class="form-control" value="<?php echo htmlspecialchars($userName); ?>" required>
                                </div>
                         
                                <div class="col-md-6 pb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" id="email" name="email" class="form-control" value="<?php echo htmlspecialchars($userEmail); ?>" required>
                                </div>
                                <div class="col-md-6 pb-3">
                                    <label for="mobile_no" class="form-label">Mobile Number</label>
                                    <input type="tel" id="mobile_no" name="mobile_no" class="form-control" value="<?php echo htmlspecialchars($userPhone); ?>" required>
                                </div>
                            </div>

                            <hr>

                            <!-- Interactive Star Rating Input -->
                            <div class="mb-4 text-center">
                                <label class="form-label fs-5">Your Overall Rating</label>
                                <div class="star-rating">
                                    <input type="radio" id="rating-5" name="rating" value="5" required><label for="rating-5" title="5 stars"><i class="bi bi-star-fill"></i></label>
                                    <input type="radio" id="rating-4" name="rating" value="4"><label for="rating-4" title="4 stars"><i class="bi bi-star-fill"></i></label>
                                    <input type="radio" id="rating-3" name="rating" value="3"><label for="rating-3" title="3 stars"><i class="bi bi-star-fill"></i></label>
                                    <input type="radio" id="rating-2" name="rating" value="2"><label for="rating-2" title="2 stars"><i class="bi bi-star-fill"></i></label>
                                    <input type="radio" id="rating-1" name="rating" value="1"><label for="rating-1" title="1 star"><i class="bi bi-star-fill"></i></label>
                                </div>
                            </div>

                            <!-- Review Text Input -->
                            <div class="mb-4">
                                <label for="review_text" class="form-label fs-5">Your Review</label>
                                <textarea class="form-control" id="review_text" name="review_text" rows="5" placeholder="Tell us about your trip, the bus, and the service..." required></textarea>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-danger btn-lg">Submit Review</button>
                            </div>
                        </form>

                    <?php else: ?>
                        <div class="text-center">
                            <h2 class="mb-3">Login Required</h2>
                            <p class="lead text-muted">You must be logged in to your account to write a review.</p>
                            <a href="login" class="btn btn-primary mt-3">Log In</a>
                            <a href="register" class="btn btn-secondary mt-3">Create an Account</a>
                        </div>
                    <?php endif; ?>

                </div>
            </div>
        </div>
    </main>

    <?php if (isset($conn)) $conn->close(); ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
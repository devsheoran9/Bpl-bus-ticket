<?php
// --- Fetch the Top 10 Popular Routes ---
// This query calculates popularity by counting the number of bookings for each route.
// It is wrapped in a try-catch block to prevent errors if the query fails.
$popular_routes = [];
try {
    $routes_query = $_conn_db->query("
        SELECT r.route_name
        FROM routes r
        JOIN bookings b ON r.route_id = b.route_id
        WHERE r.status = 'Active'
        GROUP BY r.route_id, r.route_name
        ORDER BY COUNT(b.booking_id) DESC
        LIMIT 10
    ");
    $popular_routes = $routes_query->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // If there's an error (e.g., no bookings yet), the $popular_routes array will remain empty,
    // and the section will be gracefully hidden.
    error_log("Failed to fetch popular routes: " . $e->getMessage());
}
?>

    <footer class="bg-dark text-white pt-5 pb-4">
        <div class="container text-center text-md-left">
            <div class="row text-center text-md-left">

                <!-- Company Info Column -->
                <div class="col-md-3 col-lg-3 col-xl-3 mx-auto mt-3">
                    <?php if (!empty($company_name)): ?>
                        <h5 class="text-uppercase mb-4 font-weight-bold text-primary"><?php echo $company_name; ?></h5>
                    <?php endif; ?>
                    <p>Your trusted partner for booking bus tickets online. We aim to provide a seamless, safe, and happy booking experience for all our customers.</p>
                </div>

                <!-- Popular Routes Column -->
                <div class="col-md-2 col-lg-2 col-xl-2 mx-auto mt-3">
                    <h5 class="text-uppercase mb-4 font-weight-bold">Popular Routes</h5>
                    <?php if (!empty($popular_routes)): ?>
                        <?php foreach ($popular_routes as $route): ?>
                            <p><a href="#" class="text-white" style="text-decoration: none;"><?php echo $route['route_name']; ?></a></p>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-white-50">No popular routes to display yet.</p>
                    <?php endif; ?>
                </div>

                <!-- Useful Links Column -->
                <div class="col-md-3 col-lg-2 col-xl-2 mx-auto mt-3">
                    <h5 class="text-uppercase mb-4 font-weight-bold">Useful links</h5>
                    <p><a href="about_us.php" class="text-white" style="text-decoration: none;">About Us</a></p>
                    <p><a href="privacy-policy.php" class="text-white" style="text-decoration: none;">Privacy Policy</a></p>
                    <p><a href="terms-and-conditions.php" class="text-white" style="text-decoration: none;">Terms & Conditions</a></p>
                    <p><a href="contact.php" class="text-white" style="text-decoration: none;">Help</a></p>
                </div>

                <!-- Contact Column -->
                <div class="col-md-4 col-lg-3 col-xl-3 mx-auto mt-3">
                    <h5 class="text-uppercase mb-4 font-weight-bold">Contact</h5>
                    
                    <?php if (!empty($company_address)): ?>
                        <p><i class="bi bi-house-door-fill mr-3"></i> <?php echo $company_address; ?></p>
                    <?php endif; ?>
                    
                    <?php if (!empty($email_primary)): ?>
                        <p><i class="bi bi-envelope-fill mr-3"></i> <?php echo $email_primary; ?></p>
                    <?php endif; ?>
                    
                    <?php if (!empty($mobile_primary)): ?>
                        <p><i class="bi bi-telephone-fill mr-3"></i> <?php echo $mobile_primary; ?></p>
                    <?php endif; ?>

                    <?php if (!empty($mobile_optional_1)): ?>
                        <p><i class="bi bi-telephone-fill mr-3"></i> <?php echo $mobile_optional_1; ?></p>
                    <?php endif; ?>

                    <?php if (!empty($whatsapp_primary)): ?>
                        <p><i class="bi bi-whatsapp mr-3"></i> <?php echo $whatsapp_primary; ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <hr class="mb-4">

            <div class="row align-items-center">

                <!-- Copyright Column -->
                <div class="col-md-7 col-lg-8">
                    <p> © <?php echo date("Y"); ?> Copyright:
                        <a href="index.php" style="text-decoration: none;">
                            <?php if (!empty($company_name)): ?>
                                <strong class="text-primary"><?php echo $company_name; ?></strong>
                            <?php else: ?>
                                <strong class="text-primary">OurWebsite.com</strong>
                            <?php endif; ?>
                        </a>
                    </p>
                </div>

                <!-- Social Media Column -->
                <div class="col-md-5 col-lg-4">
                    <div class="text-center text-md-right">
                        <ul class="list-unstyled list-inline">
                            
                            <?php if (!empty($facebook_url)): ?>
                            <li class="list-inline-item">
                                <a href="<?php echo $facebook_url; ?>" target="_blank" class="btn-floating btn-sm text-white" style="font-size: 23px;"><i class="bi bi-facebook"></i></a>
                            </li>
                            <?php endif; ?>

                            <?php if (!empty($twitter_url)): ?>
                            <li class="list-inline-item">
                                <a href="<?php echo $twitter_url; ?>" target="_blank" class="btn-floating btn-sm text-white" style="font-size: 23px;"><i class="bi bi-twitter"></i></a>
                            </li>
                            <?php endif; ?>
                            
                            <?php if (!empty($instagram_url)): ?>
                            <li class="list-inline-item">
                                <a href="<?php echo $instagram_url; ?>" target="_blank" class="btn-floating btn-sm text-white" style="font-size: 23px;"><i class="bi bi-instagram"></i></a>
                            </li>
                            <?php endif; ?>
                            
                            <?php if (!empty($youtube_url)): ?>
                            <li class="list-inline-item">
                                <a href="<?php echo $youtube_url; ?>" target="_blank" class="btn-floating btn-sm text-white" style="font-size: 23px;"><i class="bi bi-youtube"></i></a>
                            </li>
                            <?php endif; ?>

                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </footer>


    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

 
</body>
</html>
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

<style>
.modern-footer {
    background: linear-gradient(135deg, #1a1a1a 0%, #0d0d0d 100%);
    position: relative;
    overflow: hidden;
}

.modern-footer::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 2px;
    background: linear-gradient(90deg, transparent 0%, #ffffff 50%, transparent 100%);
}

.modern-footer::after {
    content: '';
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: radial-gradient(circle, rgba(255,255,255,0.03) 0%, transparent 70%);
    animation: subtle-pulse 8s ease-in-out infinite;
}

@keyframes subtle-pulse {
    0%, 100% { opacity: 0.3; }
    50% { opacity: 0.6; }
}

.footer-section {
    position: relative;
    z-index: 2;
}

.footer-title {
    font-size: 1.1rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-bottom: 1.5rem;
    position: relative;
    display: inline-block;
}

.footer-title::after {
    content: '';
    position: absolute;
    bottom: -8px;
    left: 0;
    width: 40px;
    height: 2px;
    background: linear-gradient(90deg, #ffffff 0%, transparent 100%);
}

.company-title {
    font-size: 1.5rem;
    font-weight: 900;
    text-transform: uppercase;
    letter-spacing: 2px;
    background: linear-gradient(135deg, #ffffff 0%, #cccccc 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin-bottom: 1rem;
}

.company-description {
    color: #b3b3b3;
    line-height: 1.6;
    font-size: 0.95rem;
}

.footer-link {
    color: #cccccc !important;
    text-decoration: none !important;
    font-size: 0.9rem;
    padding: 0.3rem 0;
    display: block;
    position: relative;
    transition: all 0.3s ease;
    border-left: 2px solid transparent;
    padding-left: 0.5rem;
}

.footer-link:hover {
    color: #ffffff !important;
    border-left: 2px solid #ffffff;
    padding-left: 1rem;
    transform: translateX(5px);
}

.route-link {
    color: #999999 !important;
    font-size: 0.85rem;
    padding: 0.2rem 0;
}

.route-link:hover {
    color: #ffffff !important;
    border-left: 2px solid #666666;
}

.contact-item {
    color: #b3b3b3;
    margin-bottom: 0.8rem;
    font-size: 0.9rem;
    display: flex;
    align-items: center;
}

.contact-icon {
    width: 20px;
    font-size: 1rem;
    margin-right: 0.8rem;
    color: #ffffff;
}

.social-links {
    display: flex;
    justify-content: center;
    gap: 1rem;
}

@media (min-width: 768px) {
    .social-links {
        justify-content: flex-end;
    }
}

.social-link {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 45px;
    height: 45px;
    background: linear-gradient(135deg, #333333 0%, #1a1a1a 100%);
    color: #ffffff !important;
    border-radius: 50%;
    transition: all 0.4s ease;
    font-size: 1.2rem;
    text-decoration: none !important;
    position: relative;
    overflow: hidden;
}

.social-link::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
    transition: left 0.5s;
}

.social-link:hover {
    background: linear-gradient(135deg, #ffffff 0%, #f0f0f0 100%);
    color: #000000 !important;
    transform: translateY(-3px) scale(1.1);
    box-shadow: 0 8px 20px rgba(0,0,0,0.3);
}

.social-link:hover::before {
    left: 100%;
}

.copyright-section {
    border-top: 1px solid #333333;
    padding-top: 1.5rem;
    margin-top: 2rem;
    background: rgba(0,0,0,0.3);
}

.copyright-text {
    color: #999999;
    font-size: 0.85rem;
    margin: 0;
}

.copyright-link {
    color: #ffffff !important;
    text-decoration: none !important;
    font-weight: 600;
    transition: all 0.3s ease;
}

.copyright-link:hover {
    color: #cccccc !important;
    text-shadow: 0 0 10px rgba(255,255,255,0.3);
}

.no-routes-message {
    color: #666666;
    font-style: italic;
    font-size: 0.8rem;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .footer-title::after {
        width: 30px;
    }
    
    .company-title {
        font-size: 1.3rem;
        letter-spacing: 1px;
    }
    
    .social-links {
        gap: 0.8rem;
        margin-top: 1rem;
    }
    
    .social-link {
        width: 40px;
        height: 40px;
        font-size: 1.1rem;
    }
}
</style>

<footer class="modern-footer text-white pt-5 pb-3">
    <div class="container">
        <div class="row">

            <!-- Company Info Column -->
            <div class="col-lg-4 col-md-6 mb-4 footer-section">
                <?php if (!empty($company_name)): ?>
                    <h5 class="company-title"><?php echo $company_name; ?></h5>
                <?php endif; ?>
                <p class="company-description">Your trusted partner for booking bus tickets online. We aim to provide a seamless, safe, and happy booking experience for all our customers.</p>
            </div>

            <!-- Popular Routes Column -->
            <div class="col-lg-2 col-md-6 mb-4 footer-section">
                <h6 class="footer-title">Popular Routes</h6>
                <?php if (!empty($popular_routes)): ?>
                    <?php foreach ($popular_routes as $route): ?>
                        <a href="#" class="route-link footer-link"><?php echo $route['route_name']; ?></a>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="no-routes-message">No popular routes to display yet.</p>
                <?php endif; ?>
            </div>

            <!-- Useful Links Column -->
            <div class="col-lg-3 col-md-6 mb-4 footer-section">
                <h6 class="footer-title">Useful Links</h6>
                <a href="about_us.php" class="footer-link">About Us</a>
                <a href="privacy-policy.php" class="footer-link">Privacy Policy</a>
                <a href="terms-and-conditions.php" class="footer-link">Terms & Conditions</a>
                <a href="contact.php" class="footer-link">Help & Support</a>
            </div>

            <!-- Contact Column -->
            <div class="col-lg-3 col-md-6 mb-4 footer-section">
                <h6 class="footer-title">Contact Info</h6>
                
                <?php if (!empty($company_address)): ?>
                    <div class="contact-item">
                        <i class="bi bi-geo-alt-fill contact-icon"></i>
                        <span><?php echo $company_address; ?></span>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($email_primary)): ?>
                    <div class="contact-item">
                        <i class="bi bi-envelope-fill contact-icon"></i>
                        <span><?php echo $email_primary; ?></span>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($mobile_primary)): ?>
                    <div class="contact-item">
                        <i class="bi bi-telephone-fill contact-icon"></i>
                        <span><?php echo $mobile_primary; ?></span>
                    </div>
                <?php endif; ?>

                <?php if (!empty($mobile_optional_1)): ?>
                    <div class="contact-item">
                        <i class="bi bi-phone contact-icon"></i>
                        <span><?php echo $mobile_optional_1; ?></span>
                    </div>
                <?php endif; ?>

                <?php if (!empty($whatsapp_primary)): ?>
                    <div class="contact-item">
                        <i class="bi bi-whatsapp contact-icon"></i>
                        <span><?php echo $whatsapp_primary; ?></span>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Copyright and Social Media Section -->
        <div class="copyright-section">
            <div class="row align-items-center">
                
                <!-- Copyright Column -->
                <div class="col-md-8 col-12 mb-3 mb-md-0">
                    <p class="copyright-text">
                        © <?php echo date("Y"); ?> Copyright 
                        <a href="index.php" class="copyright-link">
                            <?php if (!empty($company_name)): ?>
                                <?php echo $company_name; ?>
                            <?php else: ?>
                                OurWebsite.com
                            <?php endif; ?>
                        </a>
                        - All Rights Reserved
                    </p>
                </div>

                <!-- Social Media Column -->
                <div class="col-md-4 col-12">
                    <div class="social-links">
                        <?php if (!empty($facebook_url)): ?>
                            <a href="<?php echo $facebook_url; ?>" target="_blank" class="social-link">
                                <i class="bi bi-facebook"></i>
                            </a>
                        <?php endif; ?>

                        <?php if (!empty($twitter_url)): ?>
                            <a href="<?php echo $twitter_url; ?>" target="_blank" class="social-link">
                                <i class="bi bi-twitter"></i>
                            </a>
                        <?php endif; ?>
                        
                        <?php if (!empty($instagram_url)): ?>
                            <a href="<?php echo $instagram_url; ?>" target="_blank" class="social-link">
                                <i class="bi bi-instagram"></i>
                            </a>
                        <?php endif; ?>
                        
                        <?php if (!empty($youtube_url)): ?>
                            <a href="<?php echo $youtube_url; ?>" target="_blank" class="social-link">
                                <i class="bi bi-youtube"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</footer>

<!-- Bootstrap JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
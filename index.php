<?php include 'includes/header.php'; ?>

<main>
    <!-- =======================
    Hero Section
    ======================== -->
    <section class="hero-section text-center">
        <div class="container">
            <h1>Safe, Reliable, and Comfortable Journeys</h1>
            <p class="lead mb-4">Your one-stop destination for hassle-free bus ticket booking.</p>
            <div class="search-form-container mt-4">
                <form action="bus_list.php" method="GET">
                    <div class="row g-3 align-items-center">
                        <div class="col-md">
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0"><i class="bi bi-bus-front"></i></span>
                                <select class="form-select border-start-0" name="from" required>
                                    <option selected disabled value="">From</option>
                                    <option value="Delhi">Delhi</option>
                                    <option value="Mumbai">Mumbai</option>
                                    <option value="Bangalore">Bangalore</option>
                                    <option value="Chennai">Chennai</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md">
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0"><i class="bi bi-geo-alt"></i></span>
                                <select class="form-select border-start-0" name="to" required>
                                    <option selected disabled value="">To</option>
                                    <option value="Lucknow">Lucknow</option>
                                    <option value="Jaipur">Jaipur</option>
                                    <option value="Pune">Pune</option>
                                    <option value="Hyderabad">Hyderabad</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md">
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0"><i class="bi bi-calendar-event"></i></span>
                                <input type="date" class="form-control border-start-0" name="date" value="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-auto">
                            <button type="submit" class="btn btn-primary w-100">Search Buses</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <!-- =======================
    Why Choose Us Section
    ======================== -->
    <section class="section">
        <div class="container text-center">
            <h2 class="section-title">Why Book With Us?</h2>
            <div class="row">
                <div class="col-md-4">
                    <div class="feature-box">
                        <div class="icon"><i class="bi bi-shield-check"></i></div>
                        <h5>Verified Buses</h5>
                        <p class="text-muted">We partner with trusted and verified bus operators to ensure your safety.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-box">
                        <div class="icon"><i class="bi bi-headset"></i></div>
                        <h5>24/7 Customer Support</h5>
                        <p class="text-muted">Our support team is available around the clock to assist you with any queries.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-box">
                        <div class="icon"><i class="bi bi-tags"></i></div>
                        <h5>Best Prices & Offers</h5>
                        <p class="text-muted">Find the best prices for your journey and enjoy exclusive offers.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- =======================
    Offers Section
    ======================== -->
    <section class="section bg-light-blue">
        <div class="container">
            <h2 class="section-title text-center">Offers For You</h2>
            <div class="row">
                <div class="col-md-3 mb-4">
                    <div class="card offer-card">
                        <div class="card-body text-center">
                            <h5 class="card-title">BUS250</h5>
                            <p class="card-text">Save up to Rs 250 on bus tickets. Use this code on checkout.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-4">
                    <div class="card offer-card">
                        <div class="card-body text-center">
                            <h5 class="card-title">FESTIVE</h5>
                            <p class="card-text">Get a flat 15% discount during the festive season.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-4">
                    <div class="card offer-card">
                        <div class="card-body text-center">
                            <h5 class="card-title">FIRSTBUS</h5>
                            <p class="card-text">New user? Get Rs 300 off on your first bus booking.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-4">
                    <div class="card offer-card">
                        <div class="card-body text-center">
                            <h5 class="card-title">AXISPAY</h5>
                            <p class="card-text">Pay with Axis Bank cards and get instant cashback.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- =======================
    Popular Routes Section
    ======================== -->
    <section class="section">
        <div class="container">
            <h2 class="section-title text-center">Popular Bus Routes</h2>
            <div class="row g-4">
                <div class="col-md-4"><a href="#" class="text-decoration-none text-dark"><div class="route-card">Delhi <i class="bi bi-arrow-left-right"></i> Lucknow</div></a></div>
                <div class="col-md-4"><a href="#" class="text-decoration-none text-dark"><div class="route-card">Mumbai <i class="bi bi-arrow-left-right"></i> Pune</div></a></div>
                <div class="col-md-4"><a href="#" class="text-decoration-none text-dark"><div class="route-card">Bangalore <i class="bi bi-arrow-left-right"></i> Chennai</div></a></div>
                <div class="col-md-4"><a href="#" class="text-decoration-none text-dark"><div class="route-card">Hyderabad <i class="bi bi-arrow-left-right"></i> Bangalore</div></a></div>
                <div class="col-md-4"><a href="#" class="text-decoration-none text-dark"><div class="route-card">Chennai <i class="bi bi-arrow-left-right"></i> Madurai</div></a></div>
                <div class="col-md-4"><a href="#" class="text-decoration-none text-dark"><div class="route-card">Kolkata <i class="bi bi-arrow-left-right"></i> Digha</div></a></div>
            </div>
        </div>
    </section>

    <!-- =======================
    App Download Section
    ======================== -->
    <section class="section app-download-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h2>Book Your Tickets Faster With Our App!</h2>
                    <p>Download our app for exclusive offers, live bus tracking, and a seamless booking experience right at your fingertips.</p>
                    <div>
                        <a href="#"><img src="https://s3.rdbuz.com/web/images/play_store.png" alt="Google Play Store" class="app-store-badge"></a>
                        <a href="#"><img src="https://s3.rdbuz.com/web/images/app_store.png" alt="Apple App Store" class="app-store-badge"></a>
                    </div>
                </div>
                <div class="col-md-6 text-center">
                    <img src="https://s3.rdbuz.com/web/images/home/secondary_web.png" alt="Phone Mockup" class="phone-mockup">
                </div>
            </div>
        </div>
    </section>

</main>

<?php include 'includes/footer.php'; ?>```

With these changes, your homepage will now be much more comprehensive, visually appealing, and professional.
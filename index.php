<?php  
include 'includes/header.php'; 
?>
<main>
    <!-- =======================
    Hero Section
    ======================== -->
    <section class="hero-section text-center">
        <div class="container">
            <h1 class="display-4 fw-bold">Safe, Reliable & Comfortable Journeys</h1>
            <p class="lead mb-4">Your one-stop destination for hassle-free bus ticket booking.</p>
            <div class="search-form-container mt-4">
                <form action="bus_list.php" method="GET">
                    <div class="row g-3 align-items-center">
                        <div class="col-md">
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-bus-front"></i></span>
                                <!-- ID added for JavaScript targeting -->
                                <select class="form-select border-start-0" name="from" id="from-city" required>
                                    <option selected disabled value="">From</option>
                                    <option value="Delhi">Delhi</option>
                                    <option value="Mumbai">Mumbai</option>
                                    <option value="Bangalore">Bangalore</option>
                                    <option value="Chennai">Chennai</option>
                                    <option value="Hyderabad">Hyderabad</option>
                                    <option value="Kolkata">Kolkata</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md">
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-geo-alt"></i></span>
                                <!-- ID added for JavaScript targeting -->
                                <select class="form-select border-start-0" name="to" id="to-city" required>
                                    <option selected disabled value="">To</option>
                                    <option value="Lucknow">Lucknow</option>
                                    <option value="Jaipur">Jaipur</option>
                                    <option value="Pune">Pune</option>
                                    <option value="Hyderabad">Hyderabad</option>
                                    <option value="Bangalore">Bangalore</option>
                                    <option value="Madurai">Madurai</option>
                                    <option value="Digha">Digha</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md">
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-calendar-event"></i></span>
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
    Popular Routes Section
    ======================== -->
    <section class="section bg-light">
        <div class="container">
            <h2 class="section-title text-center">Popular Bus Routes</h2>
            <div class="row g-4">
                <!-- Data attributes added for JavaScript functionality -->
                <div class="col-md-4 text-center"><a href="#" class="text-decoration-none text-dark popular-route-link" data-from="Delhi" data-to="Lucknow">
                        <div class="route-card py-2">Delhi <i class="bi bi-arrow-left-right"></i> Lucknow</div>
                    </a></div>
                <div class="col-md-4 text-center"><a href="#" class="text-decoration-none text-dark popular-route-link" data-from="Mumbai" data-to="Pune">
                        <div class="route-card py-2">Mumbai <i class="bi bi-arrow-left-right"></i> Pune</div>
                    </a></div>
                <div class="col-md-4 text-center"><a href="#" class="text-decoration-none text-dark popular-route-link" data-from="Bangalore" data-to="Chennai">
                        <div class="route-card py-2">Bangalore <i class="bi bi-arrow-left-right"></i> Chennai</div>
                    </a></div>
                <div class="col-md-4 text-center"><a href="#" class="text-decoration-none text-dark popular-route-link" data-from="Hyderabad" data-to="Bangalore">
                        <div class="route-card py-2">Hyderabad <i class="bi bi-arrow-left-right"></i> Bangalore</div>
                    </a></div>
                <div class="col-md-4 text-center"><a href="#" class="text-decoration-none text-dark popular-route-link" data-from="Chennai" data-to="Madurai">
                        <div class="route-card py-2">Chennai <i class="bi bi-arrow-left-right"></i> Madurai</div>
                    </a></div>
                <div class="col-md-4 text-center"><a href="#" class="text-decoration-none text-dark popular-route-link" data-from="Kolkata" data-to="Digha">
                        <div class="route-card py-2">Kolkata <i class="bi bi-arrow-left-right"></i> Digha</div>
                    </a></div>
            </div>
        </div>
    </section>

    <!-- =======================
    NEW! How It Works Section
    ======================== -->
    <section class="section">
        <div class="container">
            <h2 class="section-title text-center">Book Your Ticket in 3 Easy Steps</h2>
            <div class="row text-center">
                <div class="col-md-4">
                    <div class="step-box">
                        <div class="step-icon">1</div>
                        <h5 class="mt-3">Search</h5>
                        <p class="text-muted">Choose your origin, destination, and journey date.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="step-box">
                        <div class="step-icon">2</div>
                        <h5 class="mt-3">Select</h5>
                        <p class="text-muted">Select your preferred bus, seats, and boarding point.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="step-box">
                        <div class="step-icon">3</div>
                        <h5 class="mt-3">Pay</h5>
                        <p class="text-muted">Pay using our secure payment gateway and get your e-ticket.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>


    <!-- =======================
    NEW! Testimonials Section
    ======================== -->
    <section class="section bg-light-blue">
        <div class="container">
            <h2 class="section-title text-center">What Our Customers Say</h2>
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="testimonial-card">
                        <div class="rating">★★★★★</div>
                        <p class="fst-italic">"Booking was incredibly easy and fast. The bus was clean and arrived on time. Highly recommended!"</p>
                        <h6 class="fw-bold">- Priya Sharma, Delhi</h6>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="testimonial-card">
                        <div class="rating">★★★★★</div>
                        <p class="fst-italic">"The live tracking feature is a game-changer! I knew exactly where my bus was. Great experience."</p>
                        <h6 class="fw-bold">- Rohan Mehta, Mumbai</h6>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="testimonial-card">
                        <div class="rating">★★★★★</div>
                        <p class="fst-italic">"I got an amazing discount on my first booking. The app is user-friendly and very convenient."</p>
                        <h6 class="fw-bold">- Anjali Reddy, Bangalore</h6>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- =======================
    NEW! Top Operators Section
    ======================== -->
    <!-- <section class="section">
        <div class="container">
            <h2 class="section-title text-center">Our Top Bus Operators</h2>
            <div class="d-flex flex-wrap justify-content-center align-items-center gap-5">
                <img src="https://via.placeholder.com/150x50.png?text=TravelCo" alt="Operator 1" class="operator-logo">
                <img src="https://via.placeholder.com/150x50.png?text=GoBus" alt="Operator 2" class="operator-logo">
                <img src="https://via.placeholder.com/150x50.png?text=SafeJourney" alt="Operator 3" class="operator-logo">
                <img src="https://via.placeholder.com/150x50.png?text=ExpressLine" alt="Operator 4" class="operator-logo">
                <img src="https://via.placeholder.com/150x50.png?text=CityLink" alt="Operator 5" class="operator-logo">
            </div>
        </div>
    </section> -->

    <!-- =======================
    App Download Section
    ======================== -->
    <!-- <section class="section bg-dark text-white app-download-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6 text-center text-md-start">
                    <h2 class="text-white">Book Your Tickets Faster With Our App!</h2>
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
    </section> -->

</main>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const popularRouteLinks = document.querySelectorAll('.popular-route-link');
        const fromCitySelect = document.getElementById('from-city');
        const toCitySelect = document.getElementById('to-city');
        const heroSection = document.querySelector('.hero-section');

        popularRouteLinks.forEach(link => {
            link.addEventListener('click', function(event) {
                // Prevent the link from navigating
                event.preventDefault();

                // Get the city names from the data attributes
                const fromCity = this.dataset.from;
                const toCity = this.dataset.to;

                // Set the values in the dropdowns
                fromCitySelect.value = fromCity;
                toCitySelect.value = toCity;

                // Smoothly scroll to the top of the page (to the hero section)
                heroSection.scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });
    });
</script>


<?php include 'includes/footer.php'; ?>
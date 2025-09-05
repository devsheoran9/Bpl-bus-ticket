<?php  
include 'includes/header.php'; 
// This code assumes your database connection object `$_conn_db` is available here,
// likely because it's included in 'includes/header.php'.

$all_from_locations = [];
$all_to_locations = [];

try {
    // We need to get all unique starting points and stop names to populate the dropdowns.
    
    // 1. Get all main route starting points.
    $from_query_part1 = "SELECT DISTINCT starting_point FROM routes";
    
    // 2. Get all stop names (which can also be starting points for a journey).
    $from_query_part2 = "SELECT DISTINCT stop_name FROM route_stops";
    
    // We use UNION to efficiently get a single, combined list of unique locations.
    $from_stmt = $_conn_db->query("($from_query_part1) UNION ($from_query_part2) ORDER BY starting_point ASC");
    $all_from_locations = $from_stmt->fetchAll(PDO::FETCH_COLUMN);

    // For "To" locations, we get all main route ending points and all stop names.
    $to_query_part1 = "SELECT DISTINCT ending_point FROM routes";
    $to_query_part2 = "SELECT DISTINCT stop_name FROM route_stops";
    
    $to_stmt = $_conn_db->query("($to_query_part1) UNION ($to_query_part2) ORDER BY ending_point ASC");
    $all_to_locations = $to_stmt->fetchAll(PDO::FETCH_COLUMN);

} catch(PDOException $e) {
    // If the database query fails, the dropdowns will be empty but the page won't crash.
    // For debugging, you can log the error: error_log("Homepage DB Error: " . $e->getMessage());
}
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
                                <select class="form-select border-start-0" name="from" id="from-city" required>
                                    <option selected disabled value="">From</option>
                                    <?php foreach ($all_from_locations as $location): ?>
                                        <option value="<?php echo htmlspecialchars($location); ?>"><?php echo htmlspecialchars($location); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md">
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-geo-alt"></i></span>
                                <select class="form-select border-start-0" name="to" id="to-city" required>
                                    <option selected disabled value="">To</option>
                                    <?php foreach ($all_to_locations as $location): ?>
                                        <option value="<?php echo htmlspecialchars($location); ?>"><?php echo htmlspecialchars($location); ?></option>
                                    <?php endforeach; ?>
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
    How It Works Section
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
    Testimonials Section
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
</main>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const popularRouteLinks = document.querySelectorAll('.popular-route-link');
        const fromCitySelect = document.getElementById('from-city');
        const toCitySelect = document.getElementById('to-city');
        const heroSection = document.querySelector('.hero-section');

        popularRouteLinks.forEach(link => {
            link.addEventListener('click', function(event) {
                event.preventDefault();

                const fromCity = this.dataset.from;
                const toCity = this.dataset.to;

                // --- Helper function to check and set dropdown value ---
                const setDropdownValue = (selectElement, valueToSet) => {
                    // Check if the option exists before setting it
                    if (Array.from(selectElement.options).some(opt => opt.value === valueToSet)) {
                        selectElement.value = valueToSet;
                    } else {
                        // Optional: alert the user if a popular route is no longer available
                        console.warn(`The location "${valueToSet}" is not available in the dropdown.`);
                    }
                };

                setDropdownValue(fromCitySelect, fromCity);
                setDropdownValue(toCitySelect, toCity);

                // Smoothly scroll to the top of the page
                heroSection.scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });
    });
</script>


<?php include 'includes/footer.php'; ?>
<?php
include 'includes/header.php';
 

$all_locations = [];
$popular_routes = [];

try {
    // Fetch ALL unique locations (from, to, and stops) for dropdowns.
    $stmt_locations = $_conn_db->query("
        (SELECT DISTINCT starting_point FROM routes WHERE status = 'Active')
        UNION
        (SELECT DISTINCT ending_point FROM routes WHERE status = 'Active')
        UNION
        (SELECT DISTINCT stop_name FROM route_stops)
        ORDER BY starting_point ASC
    ");
    // Filter out any null or empty values
    $all_locations = array_filter($stmt_locations->fetchAll(PDO::FETCH_COLUMN));

    // Fetch popular routes using the 'is_popular' flag.
    $stmt_popular = $_conn_db->query("
        SELECT starting_point, ending_point FROM routes WHERE is_popular = 1 AND status = 'Active' LIMIT 6
    ");
    $popular_routes = $stmt_popular->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Homepage DB Error: " . $e->getMessage());
}
?>

 

<body>
 <main>
        <section class="hero-section">
            <div class="container">
                <h1 class="fw-bold">India's No. 1 online bus ticket booking site</h1>
                <p class="lead">Find the safest and most comfortable bus journeys across India.</p>
            </div>
        </section>

        <div class="container">
            <div class="search-form-card">
                <form action="bus_list.php" method="GET" id="bus-search-form">
                    <div class="row g-3 align-items-center">
                        <div class="col-lg-4 col-md-12">
                            <label for="from-city" class="form-label fw-semibold">From</label>
                            <div class="input-group">
                                <i class="bi bi-bus-front input-group-icon"></i>
                                <input type="text" class="form-control" name="from" id="from-city" placeholder="Leaving from" required autocomplete="off">
                                <div class="suggestions-dropdown" id="from-suggestions"></div>
                            </div>
                        </div>

                        <div class="col-lg-4 col-md-12">
                            <label for="to-city" class="form-label fw-semibold">To</label>
                            <div class="input-group">
                                <i class="bi bi-geo-alt input-group-icon"></i>
                                <input type="text" class="form-control" name="to" id="to-city" placeholder="Going to" required autocomplete="off">
                                <div class="suggestions-dropdown" id="to-suggestions"></div>
                            </div>
                        </div>

                        <div class="col-lg-2 col-md-6">
                            <label for="date" class="form-label fw-semibold">Date</label>
                            <div class="input-group">
                                <i class="bi bi-calendar-event input-group-icon"></i>
                                <input type="date" class="form-control" name="date" id="date" value="<?php echo date('Y-m-d'); ?>" required min="<?php echo date('Y-m-d'); ?>">
                            </div>
                        </div>
                        <div class="col-lg-2 col-md-6 d-flex align-self-end">
                            <button type="submit" class="btn btn-brand w-100">Search Buses</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <section class="section">
            <div class="container">
                <h2 class="section-title">Why Book With Us?</h2>
                <p class="section-subtitle">We provide a seamless and trustworthy booking experience from start to finish.</p>
                <div class="row g-4">
                    <div class="col-md-4">
                        <div class="feature-box">
                            <div class="icon"><i class="bi bi-shield-check"></i></div>
                            <h5>Verified Buses</h5>
                            <p>We partner with trusted and verified bus operators to ensure your safety and comfort on every trip.</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="feature-box">
                            <div class="icon"><i class="bi bi-headset"></i></div>
                            <h5>24/7 Customer Support</h5>
                            <p>Our dedicated support team is available around the clock to assist you with any queries or issues.</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="feature-box">
                            <div class="icon"><i class="bi bi-tags-fill"></i></div>
                            <h5>Best Prices & Offers</h5>
                            <p>Find the best prices for your journey and enjoy exclusive offers and discounts on your bookings.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="section bg-light-gray">
            <div class="container">
                <h2 class="section-title">Popular Bus Routes</h2>
                <p class="section-subtitle">Explore some of the most traveled bus routes by our satisfied customers.</p>
                <div class="row g-4">
                    <?php if (!empty($popular_routes)): ?>
                        <?php foreach ($popular_routes as $route): ?>
                            <div class="col-lg-4 col-md-6">
                                <a href="#" class="text-decoration-none popular-route-link" data-from="<?php echo htmlspecialchars($route['starting_point']); ?>" data-to="<?php echo htmlspecialchars($route['ending_point']); ?>">
                                    <div class="route-card">
                                        <?php echo htmlspecialchars($route['starting_point']); ?> <i class="bi bi-arrow-left-right"></i> <?php echo htmlspecialchars($route['ending_point']); ?>
                                    </div>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-center text-muted">Popular routes will be displayed here soon.</p>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <section class="section">
            <div class="container">
                <h2 class="section-title">What Our Customers Say</h2>
                <p class="section-subtitle">Real stories from real travelers who trust us for their journeys.</p>
                <div class="row g-4">
                    <div class="col-md-4">
                        <div class="testimonial-card">
                            <div class="rating"><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i></div>
                            <p>"Booking was incredibly easy and fast. The bus was clean and arrived on time. Highly recommended!"</p>
                            <h6 class="fw-bold mt-4">- Priya Sharma, Delhi</h6>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="testimonial-card">
                            <div class="rating"><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i></div>
                            <p>"The live tracking feature is a game-changer! I knew exactly where my bus was. Great experience."</p>
                            <h6 class="fw-bold mt-4">- Rohan Mehta, Mumbai</h6>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="testimonial-card">
                            <div class="rating"><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i></div>
                            <p>"I got an amazing discount on my first booking. The website is user-friendly and very convenient."</p>
                            <h6 class="fw-bold mt-4">- Anjali Reddy, Bangalore</h6>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>
    <?php include "includes/footer.php" ?>
    <script>
        const allLocations = <?php echo json_encode($all_locations); ?>;
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const fromInput = document.getElementById('from-city');
            const toInput = document.getElementById('to-city');
            const fromSuggestions = document.getElementById('from-suggestions');
            const toSuggestions = document.getElementById('to-suggestions');
            const searchForm = document.getElementById('bus-search-form');

            // --- Recent Search Logic ---
            const loadRecentSearch = () => {
                const lastFrom = localStorage.getItem('lastSearchFrom');
                const lastTo = localStorage.getItem('lastSearchTo');
                if (lastFrom) fromInput.value = lastFrom;
                if (lastTo) toInput.value = lastTo;
            };

            const saveSearch = (from, to) => {
                if (from && to) {
                    localStorage.setItem('lastSearchFrom', from);
                    localStorage.setItem('lastSearchTo', to);
                }
            };

            // Load recent search on page load
            loadRecentSearch();

            // --- Core Autocomplete Logic ---
            const setupAutocomplete = (input, suggestionsContainer) => {
                const showSuggestions = (filter = '') => {
                    suggestionsContainer.innerHTML = '';
                    const filterLower = filter.toLowerCase().trim();

                    // FIX: Show all locations if filter is empty, otherwise filter
                    const locationsToShow = filterLower === '' ?
                        allLocations :
                        allLocations.filter(loc => loc.toLowerCase().includes(filterLower));

                    if (locationsToShow.length > 0) {
                        // Add a title only when showing all locations on empty input
                        if (filterLower === '') {
                            suggestionsContainer.innerHTML += `<div class="suggestions-title">All Destinations</div>`;
                        }
                        // Limit to showing 10 suggestions for better performance and UX
                        locationsToShow.slice(0, 10).forEach(loc => createSuggestionItem(loc, filterLower, input, suggestionsContainer));
                        suggestionsContainer.classList.add('show');
                    } else {
                        suggestionsContainer.classList.remove('show');
                    }
                };

                input.addEventListener('input', () => showSuggestions(input.value));
                input.addEventListener('focus', () => showSuggestions(input.value));
            };

            const createSuggestionItem = (loc, filter, input, container) => {
                const item = document.createElement('div');
                item.className = 'suggestion-item';
                let highlightedLoc = loc;
                // Only highlight if there's a filter
                if (filter) {
                    const regex = new RegExp(filter, 'gi');
                    highlightedLoc = loc.replace(regex, `<strong>$&</strong>`);
                }
                item.innerHTML = `<i class="bi bi-geo-alt-fill"></i> ${highlightedLoc}`;
                item.addEventListener('click', () => {
                    input.value = loc;
                    closeAllSuggestions();
                });
                container.appendChild(item);
            };

            // --- Event Handlers ---
            setupAutocomplete(fromInput, fromSuggestions);
            setupAutocomplete(toInput, toSuggestions);

            const closeAllSuggestions = () => {
                fromSuggestions.classList.remove('show');
                toSuggestions.classList.remove('show');
            };
            document.addEventListener('click', e => {
                if (!e.target.closest('.input-group')) {
                    closeAllSuggestions();
                }
            });

            searchForm.addEventListener('submit', (e) => {
                const fromValue = fromInput.value.trim();
                const toValue = toInput.value.trim();
                if (fromValue && toValue && fromValue.toLowerCase() === toValue.toLowerCase()) {
                    e.preventDefault();
                    alert('Origin and destination cannot be the same. Please choose a different destination.');
                    return;
                }
                saveSearch(fromValue, toValue);
            });

            // --- Popular Route Link Logic ---
            const popularRouteLinks = document.querySelectorAll('.popular-route-link');
            popularRouteLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    fromInput.value = this.dataset.from;
                    toInput.value = this.dataset.to;

                    const searchCard = document.querySelector('.search-form-card');
                    if (searchCard) {
                        searchCard.scrollIntoView({
                            behavior: 'smooth',
                            block: 'center'
                        });
                    }
                });
            });
        });
    </script>

</body>

</html>
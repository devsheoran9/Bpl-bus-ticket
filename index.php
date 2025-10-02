<?php
include 'includes/header.php';

// --- HELPER FUNCTIONS ---
function get_initials($name)
{
    $words = explode(' ', trim($name));
    $initials = '';
    if (count($words) >= 2) {
        $initials = strtoupper(substr($words[0], 0, 1) . substr(end($words), 0, 1));
    } else if (count($words) == 1 && strlen($words[0]) > 0) {
        $initials = strtoupper(substr($words[0], 0, 1));
    }
    return $initials;
}
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

// --- INITIALIZE VARIABLES ---
$all_locations = [];
$popular_routes = [];
$latest_reviews = [];

try {
    // --- 1. Fetch ALL unique locations ---
    $stmt_locations = $pdo->query("
        (SELECT DISTINCT starting_point FROM routes WHERE status = 'Active')
        UNION
        (SELECT DISTINCT ending_point FROM routes WHERE status = 'Active')
        UNION
        (SELECT DISTINCT stop_name FROM route_stops)
        ORDER BY starting_point ASC
    ");
    $all_locations = array_filter($stmt_locations->fetchAll(PDO::FETCH_COLUMN));

    // --- 2. Fetch popular routes ---
    $stmt_popular = $pdo->query("
        SELECT starting_point, ending_point FROM routes WHERE is_popular = 1 AND status = 'Active' LIMIT 6
    ");
    $popular_routes = $stmt_popular->fetchAll(PDO::FETCH_ASSOC);

    // --- 3. Fetch latest reviews ---
    $stmt_reviews = $pdo->query("
        SELECT user_name, email, rating, review_text, created_at FROM reviews WHERE status = 1 ORDER BY created_at DESC LIMIT 6
    ");
    $latest_reviews = $stmt_reviews->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Homepage DB Error: " . $e->getMessage());
}
?>

<!-- <style>
    .testimonial-section-awesome {
        background-color: #ffffffff;
        padding: 80px 0;
    }

    .carousel-item .row {
        display: flex;
    }

    .carousel-item .col-lg-4,
    .carousel-item .col-md-6 {
        display: flex;
        flex-direction: column;
    }

    .testimonial-card {
        display: flex;
        flex-direction: column;
        height: 100%;
    }

    .review-text {
        flex-grow: 1;
    }

    .review-carousel .carousel-control-prev,
    .review-carousel .carousel-control-next {
        width: 45px;
        height: 45px;
        background-color: rgba(44, 62, 80, 0.4);
        border-radius: 50%;
        top: 50%;
        transform: translateY(-50%);
        opacity: 0.7;
        transition: opacity 0.2s ease;
    }

    .review-carousel .carousel-control-prev:hover,
    .review-carousel .carousel-control-next:hover {
        opacity: 1;
        background-color: var(--primary-color);
    }

    .review-carousel .carousel-control-prev {
        left: 0px;
    }

    .review-carousel .carousel-control-next {
        right: 0px;
    }

    @media (max-width: 576px) {

        .review-carousel .carousel-control-prev,
        .review-carousel .carousel-control-next {
            display: none;
        }
    }
</style> -->
<style>
    /* ==============================================
       NEW MODERN REVIEW SECTION STYLES
    ============================================== */
    .testimonial-section-new {
        background-color: #f8f9fa;
        /* A very light off-white for contrast */
        padding: 80px 0;
    }

    .review-card-new {
        background-color: #ffffff;
        border: 1px solid #e9ecef;
        border-radius: 12px;
        padding: 24px;
        height: 100%;
        display: flex;
        flex-direction: column;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .review-card-new:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
    }

    /* Faint background quote icon for elegance */
    .review-card-new::before {
        content: '\F517';
        /* Bootstrap Icons quote unicode */
        font-family: 'bootstrap-icons';
        position: absolute;
        top: 10px;
        right: 10px;
        font-size: 80px;
        color: #000;
        opacity: 0.04;
        line-height: 1;
        z-index: 1;
    }

    .review-header {
        display: flex;
        align-items: center;
        margin-bottom: 16px;
        position: relative;
        z-index: 2;
    }

    .author-avatar {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background-color: #7b003a;
        /* Your brand color */
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        font-weight: 600;
        margin-right: 15px;
        flex-shrink: 0;
    }

    .author-details {
        display: flex;
        flex-direction: column;
    }

    .author-name-new {
        font-weight: 600;
        color: #343a40;
        font-size: 1.1em;
    }

    .rating-stars .bi {
        font-size: 0.9em;
    }

    .review-content {
        font-size: 1em;
        color: #555;
        line-height: 1.6;
        margin-bottom: 20px;
        flex-grow: 1;
        /* Pushes footer to the bottom */
        position: relative;
        z-index: 2;
    }

    .review-footer {
        font-size: 0.85em;
        color: #888;
        text-align: right;
        position: relative;
        z-index: 2;
    }

    /* Carousel Controls Styling */
    .review-carousel .carousel-control-prev,
    .review-carousel .carousel-control-next {
        width: 45px;
        height: 45px;
        background-color: #ffffff;
        border-radius: 50%;
        top: 50%;
        transform: translateY(-50%);
        opacity: 1;
        transition: all 0.2s ease;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        color: #7b003a;
        /* Your brand color */
    }

    .review-carousel .carousel-control-prev:hover,
    .review-carousel .carousel-control-next:hover {
        background-color: #7b003a;
        color: #fff;
    }

    .review-carousel .carousel-control-prev-icon,
    .review-carousel .carousel-control-next-icon {
        filter: invert(1);
        /* Invert to see against white background */
    }

    .review-carousel .carousel-control-prev {
        left: -20px;
    }

    .review-carousel .carousel-control-next {
        right: -20px;
    }

    @media (max-width: 768px) {

        .review-carousel .carousel-control-prev,
        .review-carousel .carousel-control-next {
            display: none;
            /* Hide controls on mobile */
        }
    }
</style>

<body>
    <main>
        <section class="hero-section">
            <div class="container">
                <h1 class="fw-bold" style="color:#7b003a"><?php echo $company_name?> → India’s Trusted Online Bus Booking Service</h1>
                <p class="lead" style="color:#7b003a">Book safe, reliable, and comfortable bus rides to destinations across India.</p>
            </div>
        </section>

        <div class="container">
            <div class="search-form-card">
                <form action="bus_list" method="GET" id="bus-search-form">
                    <div class="row g-1 align-items-center">
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

        <div class="container">
            <div style="width: 100%; margin: 40px auto; padding: 0 15px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;">
                <h2 style="font-size: 24px; font-weight: 700; color: #1a1a1a; margin-bottom: 20px;">What's New</h2>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
                    <div style="background-color: #7b003a; border-radius: 16px; padding: 24px; color: white; display: flex; flex-direction: column; justify-content: space-between; position: relative; overflow: hidden;">
                        <div style="position: absolute; right: -20px; bottom: -20px; font-size: 120px; opacity: 0.1; color: white; transform: rotate(-15deg);"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24">
                                <path fill="currentColor" d="M12 2a10 10 0 1 0 0 20a10 10 0 0 0 0-20Zm4.3 13.3l-1.4 1.4L12 13.4l-2.9 2.9l-1.4-1.4l2.9-2.9l-2.9-2.9l1.4-1.4l2.9 2.9l2.9-2.9l1.4 1.4L13.4 12l2.9 2.9Z" />
                            </svg></div>
                        <div style="position: relative; z-index: 2;">
                            <h3 style="font-size: 20px; font-weight: 700; margin: 0 0 8px 0;">Hassle-Free Cancellation</h3>
                            <p style="font-size: 16px; margin: 0 0 24px 0; opacity: 0.9;">Cancel anytime and get a 100% refund instantly.</p>
                        </div>
                    </div>
                    <div style="background-color: #ffffff; border-radius: 16px; padding: 24px; color: #1a1a1a; display: flex; flex-direction: column; justify-content: space-between; box-shadow: 0 4px 12px rgba(0,0,0,0.05); position: relative; overflow: hidden;">
                        <div style="position: absolute; right: -15px; bottom: -25px; font-size: 120px; opacity: 0.08; color: black; transform: rotate(-15deg);"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24">
                                <path fill="currentColor" d="M12 2a10 10 0 1 0 0 20a10 10 0 0 0 0-20Zm.5 5v5.25l4.5 2.67l-.75 1.23L11 13V7h1.5Z" />
                            </svg></div>
                        <div style="position: relative; z-index: 2;">
                            <h3 style="font-size: 18px; font-weight: 700; margin: 0 0 8px 0;">Real-Time Bus Timetable</h3>
                            <p style="font-size: 16px; margin: 0 0 16px 0; color: #555;">Check live bus timings for routes across your state.</p>
                        </div>
                    </div>
                    <div style="background: linear-gradient(135deg, #e0f7fa 0%, #b2ebf2 100%); border: 1px solid #b2ebf2; border-radius: 16px; padding: 24px; color: #00796b; display: flex; flex-direction: column; justify-content: space-between; position: relative; overflow: hidden;">
                        <div style="position: absolute; right: -20px; bottom: -20px; font-size: 120px; opacity: 0.2; color: #004d40; transform: rotate(-15deg);"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24">
                                <path fill="currentColor" d="M20 4H4v4a2 2 0 0 1 0 4v4h16v-4a2 2 0 0 1 0-4V4Zm0 2v2.59l.71.7l.29.71l-.29.71l-.71.7V16H4v-2.59l-.71-.7l-.29-.71l.29-.71l.71-.7V6h16Z" />
                            </svg></div>
                        <div style="position: relative; z-index: 2;">
                            <h3 style="font-size: 18px; font-weight: 700; margin: 0 0 8px 0; color: #004d40;">FlexiTicket Options</h3>
                            <p style="font-size: 16px; margin: 0 0 16px 0; color: #00695c;">Easily reschedule or cancel with special benefits.</p>
                        </div>
                    </div>
                    <div style="background: linear-gradient(135deg, #fff3e0 0%, #ffe0b2 100%); border: 1px solid #ffe0b2; border-radius: 16px; padding: 24px; color: #e65100; display: flex; flex-direction: column; justify-content: space-between; position: relative; overflow: hidden;">
                        <div style="position: absolute; right: -10px; bottom: -20px; font-size: 120px; opacity: 0.2; color: #bf360c; transform: rotate(-15deg);"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24">
                                <path fill="currentColor" d="M7 2v11h3v9l7-12h-4l4-8Z" />
                            </svg></div>
                        <div style="position: relative; z-index: 2;">
                            <h3 style="font-size: 18px; font-weight: 700; margin: 0 0 8px 0; color: #bf360c;">Instant Refunds</h3>
                            <p style="font-size: 16px; margin: 0 0 16px 0; color: #d84315;">Get your money back within minutes of cancellation.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <section class="section">
            <div class="container why-choose-container">
                <h2 class="section-title" style="text-align: left;"><?php echo $company_name?>: India’s Trusted Online Bus Booking Platform</h2>
                <p><?php echo $company_name?> has been simplifying bus travel in India for over 2 years, serving more than 5 million happy travelers. We are committed to providing a smooth, fast, and reliable online ticket booking experience.</p>
                <p>With 100+ bus operators and 700+ routes across the country, <?php echo $company_name?> makes it easy to reach your destination. Enjoy affordable fares when you book your bus tickets online with us.</p>

                <div class="mb-2">
                    <h2 class="section-titlee" style="text-align: left; ">Why Choose <?php echo $company_name?> for Bus Booking?</h2>
                    <p style="text-align: left;">Below are some of the reasons why you should choose <?php echo $company_name?> for booking bus tickets.</p>
                </div>

                <ul style="list-style-type: disc; padding-left: 20px; font-size: 1em; color: #555;">
                    <li style="margin-bottom: 1.2rem;"><strong>Free Cancellation</strong> - Cancel bus tickets without paying cancellation charges.</li>
                    <li style="margin-bottom: 1.2rem;"><strong>Flexi Ticket</strong> - Select a Flexi ticket to modify your travel date at least 8 hours before departure.</li>
                    <!-- <li style="margin-bottom: 1.2rem;"><strong>Earn Rewards</strong> - Refer your friend and get rewards in your <?php echo $company_name?> wallet after they complete their first trip.</li> -->
                    <li style="margin-bottom: 1.2rem;"><strong>Booking for Women</strong> - Access exclusive deals for women travellers and find buses preferred by women.</li>
                    <li style="margin-bottom: 1.2rem;"><strong>Primo Services</strong> - Select top-rated bus operators that offer timely and customer-friendly Primo services.</li>
                    <li style="margin-bottom: 1.2rem;"><strong>24/7 Customer Support</strong> - Receive 24/7 customer service for any assistance related to bookings.</li>
                    <li style="margin-bottom: 1.2rem;"><strong>Instant Refund</strong> - Get an instant refund for cancellation or booking-related issues.</li>
                    <li style="margin-bottom: 1.2rem;"><strong>Live Bus Tracking</strong> - Track your bus in real-time and plan your journey more efficiently.</li>
                </ul>
            </div>
        </section>

        <section class="section bg-light-gray">
            <div class="container">
                <h2 class="section-title">Popular Bus Routes</h2>
                <p class="section-subtitle">Explore some of the most traveled bus routes by our satisfied customers.</p>
                <div class="row g-2">
                    <?php if (!empty($popular_routes)) : ?>
                        <?php foreach ($popular_routes as $route) : ?>
                            <div class="col-lg-4 col-md-6">
                                <a href="#" class="text-decoration-none popular-route-link" data-from="<?php echo htmlspecialchars($route['starting_point']); ?>" data-to="<?php echo htmlspecialchars($route['ending_point']); ?>">
                                    <div class="route-card">
                                        <?php echo htmlspecialchars($route['starting_point']); ?> <i class="bi bi-arrow-left-right"></i> <?php echo htmlspecialchars($route['ending_point']); ?>
                                    </div>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <p class="text-center text-muted">Popular routes will be displayed here soon.</p>
                    <?php endif; ?>
                </div>
            </div>
        </section>
        <section class="section testimonial-section-new">
            <div class="container">
                <h2 class="section-title">What Our Customers Say</h2>
                <p class="section-subtitle">Real stories from real travelers who trust us for their journeys.</p>

                <?php if (!empty($latest_reviews)) : ?>
                    <div id="reviewCarousel" class="carousel slide review-carousel" data-bs-ride="carousel">
                        <div class="carousel-inner">
                            <?php
                            // Determine how many items per slide based on a hypothetical screen size (server-side)
                            // This part will be primarily handled by JavaScript for true responsiveness,
                            // but we can set up the structure here.
                            $itemsPerSlide = 3; // Let's assume desktop default
                            $total_reviews = count($latest_reviews);
                            $is_first_slide = true;

                            for ($i = 0; $i < $total_reviews; $i += $itemsPerSlide) {
                                // This loop structure is now mainly a placeholder;
                                // the dynamic content will be built by JavaScript.
                            }
                            ?>
                            <!-- Carousel items will be dynamically inserted here by JavaScript -->
                        </div>

                        <button class="carousel-control-prev" type="button" data-bs-target="#reviewCarousel" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Previous</span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#reviewCarousel" data-bs-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Next</span>
                        </button>
                    </div>

                    <div class="text-center mt-5">
                        <a href="reviews.php" class="btn btn-brand">View All Reviews</a>
                    </div>

                <?php else : ?>
                    <div class="text-center py-5">
                        <p class="lead">No customer reviews yet.</p>
                        <p class="text-muted">Be the first to share your experience!</p>
                    </div>
                <?php endif; ?>
            </div>
        </section>


        <section class="section bg-light-gray">
            <div class="container why-choose-container">
                <div class="mb-2">
                    <h2 class="section-title" style="text-align: left;  ">How to Book Bus Tickets Online on <?php echo $company_name?>?</h2>
                    <p style="text-align: left;">Below are some simple steps that you can follow when booking bus tickets online on <?php echo $company_name?>.</p>
                </div>
                <ul style="list-style-type: disc; padding-left: 20px; font-size: 1em; color: #555;">
                    <li style="margin-bottom: 1.2rem;"><strong>Step 1:</strong> Visit the <?php echo $company_name?> website.</li>
                    <li style="margin-bottom: 1.2rem;"><strong>Step 2:</strong> Select your travel date and journey details (From and To).</li>
                    <li style="margin-bottom: 1.2rem;"><strong>Step 3:</strong> Search for your preferred bus available on your chosen travel date and route.</li>
                    <li style="margin-bottom: 1.2rem;"><strong>Step 4:</strong> Select your preferred boarding or dropping points and enter your contact details.</li>
                    <li style="margin-bottom: 1.2rem;"><strong>Step 5:</strong> Choose from multiple payment options to proceed with the payment process.</li>
                    <li style="margin-bottom: 1.2rem;"><strong>Step 6:</strong> After the successful payment, you will receive a confirmation of your bus booking on your registered email ID. </li>
                </ul>
            </div>
        </section>
    </main>

    <?php include "includes/footer.php" ?>

    <script>
        const allLocations = <?php echo json_encode($all_locations); ?>;

        document.addEventListener('DOMContentLoaded', function() {
            // --- FULL SEARCH FORM SCRIPT ---
            const fromInput = document.getElementById('from-city');
            const toInput = document.getElementById('to-city');
            const fromSuggestions = document.getElementById('from-suggestions');
            const toSuggestions = document.getElementById('to-suggestions');
            const searchForm = document.getElementById('bus-search-form');

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
            loadRecentSearch();

            const setupAutocomplete = (input, suggestionsContainer) => {
                const showSuggestions = (filter = '') => {
                    suggestionsContainer.innerHTML = '';
                    const filterLower = filter.toLowerCase().trim();
                    const locationsToShow = allLocations.filter(loc => loc.toLowerCase().includes(filterLower));

                    if (locationsToShow.length > 0) {
                        if (filterLower === '') {
                            suggestionsContainer.innerHTML += `<div class="suggestions-title">All Destinations</div>`;
                        }
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
                if (filter) {
                    const regex = new RegExp(filter.replace(/[-\/\\^$*+?.()|[\]{}]/g, '\\$&'), 'gi');
                    highlightedLoc = loc.replace(regex, `<strong>$&</strong>`);
                }
                item.innerHTML = `<i class="bi bi-geo-alt-fill"></i> ${highlightedLoc}`;
                item.addEventListener('click', () => {
                    input.value = loc;
                    closeAllSuggestions();
                });
                container.appendChild(item);
            };

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

            document.querySelectorAll('.popular-route-link').forEach(link => {
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


            // --- NEW, CORRECTED MULTI-ITEM SLIDER SCRIPT ---
            const reviewsData = <?php echo json_encode($latest_reviews); ?>;
if (reviewsData.length > 0) {
    const carouselInner = document.querySelector('#reviewCarousel .carousel-inner');
    carouselInner.innerHTML = ''; // Clear any server-side placeholders

    let itemsPerSlide = 3; // Default for desktop
    if (window.innerWidth < 992) { itemsPerSlide = 2; } // Tablet
    if (window.innerWidth < 768) { itemsPerSlide = 1; } // Mobile

    let activeClass = 'active';
    for (let i = 0; i < reviewsData.length; i += itemsPerSlide) {
        const slide = document.createElement('div');
        slide.className = 'carousel-item ' + activeClass;
        activeClass = ''; // Only the first slide is active

        const row = document.createElement('div');
        row.className = 'row g-4 justify-content-center';

        const chunk = reviewsData.slice(i, i + itemsPerSlide);

        chunk.forEach(review => {
            const col = document.createElement('div');
            // Use d-flex to make sure cards in the same row are equal height
            col.className = 'col-12 col-md-6 col-lg-4 d-flex';

            col.innerHTML = `
                <div class="review-card-new w-100">
                    <div class="review-header">
                        <div class="author-avatar">${getInitialsJS(review.user_name)}</div>
                        <div class="author-details">
                            <div class="author-name-new">${escapeHTML(review.user_name)}</div>
                            <div class="rating-stars">${renderStarsJS(review.rating)}</div>
                        </div>
                    </div>
                    <div class="review-content">
                        ${escapeHTML(review.review_text.substring(0, 180))}${review.review_text.length > 180 ? '...' : ''}
                    </div>
                    <div class="review-footer">
                        ${formatDateJS(review.created_at)}
                    </div>
                </div>
            `;
            row.appendChild(col);
        });
        slide.appendChild(row);
        carouselInner.appendChild(slide);
    }
}

// Helper function to get initials from a name in JS
function getInitialsJS(name) {
    if (!name) return '';
    const words = name.trim().split(' ');
    let initials = '';
    if (words.length >= 2) {
        initials = (words[0][0] || '') + (words[words.length - 1][0] || '');
    } else if (words.length === 1 && words[0].length > 0) {
        initials = words[0][0];
    }
    return initials.toUpperCase();
}

            // Helper functions for JavaScript to render the HTML
            function renderStarsJS(rating) {
                let stars_html = '';
                for (let i = 1; i <= 5; i++) {
                    const iconClass = (i <= rating) ? 'bi-star-fill text-warning' : 'bi-star text-muted';
                    stars_html += `<i class="bi ${iconClass}"></i> `;
                }
                return stars_html;
            }

            function maskEmailJS(email) {
                if (!email) return '';
                const parts = email.split('@');
                if (parts.length !== 2) return email;
                const [first, last] = parts;
                const maskedFirst = first.substring(0, 2) + '*'.repeat(Math.max(1, first.length - 2));
                return maskedFirst + '@' + last;
            }

            function formatDateJS(dateString) {
                const options = {
                    year: 'numeric',
                    month: 'short',
                    day: 'numeric'
                };
                return new Date(dateString).toLocaleDateString('en-GB', options);
            }

            function escapeHTML(str) {
                if (str === null || str === undefined) return '';
                return str.replace(/[&<>"']/g, function(match) {
                    return {
                        '&': '&amp;',
                        '<': '&lt;',
                        '>': '&gt;',
                        '"': '&quot;',
                        "'": '&#39;'
                    } [match];
                });
            }
        });
    </script>

</body>

</html>
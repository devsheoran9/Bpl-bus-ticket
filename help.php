<?php include 'includes/header.php'; ?>
<body style="background-color: #f8f9fa;">

    <!-- ======= Help Center Hero Section ======= -->
    <section class="text-center mt-5 py-5" style="background-color: #fffafb; border-bottom: 1px solid #f0e4e6;">
        <div class="container mt-4">
            <h1 class="display-5" style="font-weight: 700; color: #7b003a;">Help Center</h1>
            <p class="lead mt-3" style="color: #555;">Welcome to the  <?php echo $company_name?> Help Center. Find quick solutions, manage your bookings, and get answers to your travel questions.</p>
        </div>
    </section>

    <main class="container  ">
        <!-- Main Help Topics Section -->
        <div class="card" style="border: none; border-radius: 15px; box-shadow: 0 8px 30px rgba(0,0,0,0.07); overflow: hidden; position: relative;">
            <div style="position: absolute; right: -50px; top: -40px; font-size: 250px; color: #e9ecef; opacity: 0.5; z-index: 1; transform: rotate(-15deg);"><i class="bi bi-life-preserver"></i></div>
            <div class="card-body p-4 p-md-5" style="position: relative; z-index: 2;">
                <h2 class="mb-4" style="font-weight: 600; color: #343a40;">How can we help you today?</h2>
                <div class="row g-2">

                    <!-- Topic 1: Booking & Payments -->
                    <div class="col-12">
                        <h4 class="mb-3" style="font-weight: 600; color: #555; border-bottom: 2px solid #f0e4e6; padding-bottom: 10px;"><i class="bi bi-bus-front-fill me-2" style="color: #7b003a;"></i>Booking & Payments</h4>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="card h-100 text-center" style="border: 1px solid #eee; transition: all 0.2s ease;">
                            <div class="card-body">
                                <i class="bi bi-search-heart fs-1 mb-3" style="color: #7b003a !important;"></i>
                                <h5 class="card-title fw-bold">How to Book a Ticket</h5>
                                <p class="card-text text-muted small">A step-by-step guide to finding your route and booking your bus ticket online quickly and easily.</p>
                                <a href="index" class="btn btn-sm btn-outline-danger mt-3">Book Now</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="card h-100 text-center" style="border: 1px solid #eee; transition: all 0.2s ease;">
                            <div class="card-body">
                                <i class="bi bi-credit-card-2-front-fill fs-1 mb-3" style="color: #7b003a !important;"></i>
                                <h5 class="card-title fw-bold">Payment Options</h5>
                                <p class="card-text text-muted small">We accept secure payment methods, including UPI, credit/debit cards, and net banking. All options are available at checkout.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="card h-100 text-center" style="border: 1px solid #eee; transition: all 0.2s ease;">
                            <div class="card-body">
                                <i class="bi bi-shield-check fs-1 mb-3" style="color: #7b003a !important;"></i>
                                <h5 class="card-title fw-bold">Secure Transactions</h5>
                                <p class="card-text text-muted small">Your financial security is our top priority. All transactions are processed through a secure, certified payment gateway.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Topic 2: Managing Your Trip -->
                    <div class="col-12 mt-5">
                        <h4 class="mb-3" style="font-weight: 600; color: #555; border-bottom: 2px solid #f0e4e6; padding-bottom: 10px;"><i class="bi bi-gear-fill me-2" style="color: #7b003a;"></i>Managing Your Trip</h4>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="card h-100 text-center" style="border: 1px solid #eee; transition: all 0.2s ease;">
                            <div class="card-body">
                                <i class="bi bi-x-circle-fill fs-1 mb-3" style="color: #7b003a !important;"></i>
                                <h5 class="card-title fw-bold">Cancel a Ticket</h5>
                                <p class="card-text text-muted small">Need to cancel? Find your booking and process your cancellation request in just a few clicks.</p>
                                <a href="cancel_ticket" class="btn btn-sm btn-danger mt-3">Cancel My Ticket</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="card h-100 text-center" style="border: 1px solid #eee; transition: all 0.2s ease;">
                            <div class="card-body">
                                <i class="bi bi-pin-map-fill fs-1 mb-3" style="color: #7b003a !important;"></i>
                                <h5 class="card-title fw-bold">Track My Bus</h5>
                                <p class="card-text text-muted small">Get real-time updates on your bus's location from the link provided in your booking confirmation email.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="card h-100 text-center" style="border: 1px solid #eee; transition: all 0.2s ease;">
                            <div class="card-body">
                                <i class="bi bi-printer-fill fs-1 mb-3" style="color: #7b003a !important;"></i>
                                <h5 class="card-title fw-bold">Print My Ticket</h5>
                                <p class="card-text text-muted small">Access your "My Bookings" page to view or print a copy of your ticket anytime.</p>
                                <a href="bookings" class="btn btn-sm btn-outline-danger mt-3">My Bookings</a>
                            </div>
                        </div>
                    </div>

                    <!-- Topic 3: Policies & Information -->
                    <div class="col-12 mt-5">
                        <h4 class="mb-3" style="font-weight: 600; color: #555; border-bottom: 2px solid #f0e4e6; padding-bottom: 10px;"><i class="bi bi-info-circle-fill me-2" style="color: #7b003a;"></i>Policies & Information</h4>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="card h-100 text-center" style="border: 1px solid #eee; transition: all 0.2s ease;">
                            <div class="card-body">
                                <i class="bi bi-arrow-counterclockwise fs-1 mb-3" style="color: #7b003a !important;"></i>
                                <h5 class="card-title fw-bold">Refund Policy</h5>
                                <p class="card-text text-muted small">Refunds are processed instantly after cancellation and reflect in your source account within 24-48 working hours.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="card h-100 text-center" style="border: 1px solid #eee; transition: all 0.2s ease;">
                            <div class="card-body">
                                <i class="bi bi-suitcase-lg-fill fs-1 mb-3" style="color: #7b003a !important;"></i>
                                <h5 class="card-title fw-bold">Luggage Information</h5>
                                <p class="card-text text-muted small">Passengers are typically allowed one piece of luggage (up to 20 kg) and a small handbag. Policies may vary by operator.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="card h-100 text-center" style="border: 1px solid #eee; transition: all 0.2s ease;">
                            <div class="card-body">
                                <i class="bi bi-patch-question-fill fs-1 mb-3" style="color: #7b003a !important;"></i>
                                <h5 class="card-title fw-bold">More Questions?</h5>
                                <p class="card-text text-muted small">Visit our detailed FAQ page for answers on booking, tickets, payments, cancellations, and more.</p>
                                <a href="faq" class="btn btn-sm btn-danger mt-3">Visit FAQs</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Still Need Help Section -->
        <div class="container text-center mt-5">
            <hr class="my-5">
            <h2 style="font-weight: 600; color: #343a40;">Still can't find your answer?</h2>
            <p class="mb-4 text-muted">Our support team is here for you 24/7. Get in touch with us directly.</p>
            <div class="row justify-content-center g-4">
                <div class="col-md-5">
                    <div class="card h-100" style="border-radius: 15px; border: 1px solid #dee2e6; transition: all 0.2s ease-in-out;">
                        <div class="card-body p-4 text-center">
                            <div style="font-size: 2.5rem; color: #7b003a; margin-bottom: 1rem;"><i class="bi bi-envelope-fill"></i></div>
                            <h5 style="font-weight: 600;">Email Support</h5>
                            <p class="text-muted">Get a detailed response from our team.</p>
                            <a href="<?php echo $email_primary?>" class="text-decoration-none" style="font-weight: 500; color: #7b003a;"><?php echo $email_primary?></a>
                        </div>
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="card h-100" style="border-radius: 15px; border: 1px solid #dee2e6; transition: all 0.2s ease-in-out;">
                        <div class="card-body p-4 text-center">
                            <div style="font-size: 2.5rem; color: #7b003a; margin-bottom: 1rem;"><i class="bi bi-telephone-fill"></i></div>
                            <h5 style="font-weight: 600;">Call Us 24/7</h5>
                            <p class="text-muted">For urgent help with your booking.</p>
                            <a href="tel:+911234567890" class="text-decoration-none" style="font-weight: 500; color: #7b003a;">+91 123 456 7890</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <br><br><br><br>

    <?php include 'includes/footer.php'; ?>
</body>

</html>
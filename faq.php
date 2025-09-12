<?php include 'includes/header.php'; ?>

<!-- ======= FAQ Hero Section ======= -->
<section class="faq-hero-section text-center mt-5 ">
    <div class="container mt-4">
        <h1 class="display-5">Help & Support</h1>
        <p class="lead mt-3">Your questions, answered. Find everything you need to know about booking and managing your bus journey with us.</p>
    </div>
</section>
<main class="container">
    <div class="faq-section">
        <h2 class="faq-title">FAQs related to Bus Tickets Booking</h2>

        <!-- Tab Navigation -->
        <div class="faq-tabs">
            <ul class="nav nav-tabs" id="faqTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="general-tab" data-bs-toggle="tab" data-bs-target="#general" type="button" role="tab">General</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="ticket-tab" data-bs-toggle="tab" data-bs-target="#ticket" type="button" role="tab">Ticket-related</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="payment-tab" data-bs-toggle="tab" data-bs-target="#payment" type="button" role="tab">Payment</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="cancellation-tab" data-bs-toggle="tab" data-bs-target="#cancellation" type="button" role="tab">Cancellation & Refund</button>
                </li>
            </ul>
        </div>

        <!-- Tab Content -->
        <div class="tab-content">
            <!-- General FAQs -->
            <div class="tab-pane fade show active" id="general" role="tabpanel">
                <div class="accordion accordion-flush faq-accordion" id="generalFaqAccordion">

                    <div class="accordion-item">
                        <h2 class="accordion-header"><button class="accordion-button collapsed ps-2" type="button" data-bs-toggle="collapse" data-bs-target="#faq-general-1">Can I track the location of my booked bus online?</button></h2>
                        <div id="faq-general-1" class="accordion-collapse collapse" data-bs-parent="#generalFaqAccordion">
                            <div class="accordion-body ps-2">Yes, for many of our bus operators, we offer a live tracking feature. You can find the "Track My Bus" link in your booking confirmation email or in the "My Bookings" section of your account. This allows you to see the real-time location of your bus.</div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header"><button class="accordion-button collapsed ps-2" type="button" data-bs-toggle="collapse" data-bs-target="#faq-general-2">What are the advantages of purchasing a bus ticket with PBL Bus?</button></h2>
                        <div id="faq-general-2" class="accordion-collapse collapse" data-bs-parent="#generalFaqAccordion">
                            <div class="accordion-body ps-2">With PBL Bus, you get access to a wide network of trusted bus operators, transparent pricing, exclusive discounts, 24/7 customer support, and innovative features like live tracking and M-tickets, ensuring a seamless and reliable booking experience.</div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header"><button class="accordion-button collapsed ps-2" type="button" data-bs-toggle="collapse" data-bs-target="#faq-general-3">Do I need to create an account to book my bus ticket?</button></h2>
                        <div id="faq-general-3" class="accordion-collapse collapse" data-bs-parent="#generalFaqAccordion">
                            <div class="accordion-body ps-2">While you can book as a guest, creating an account is highly recommended. An account allows you to easily manage your bookings, view your travel history, save passenger details for faster checkout, and access exclusive member-only offers.</div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header"><button class="accordion-button collapsed ps-2" type="button" data-bs-toggle="collapse" data-bs-target="#faq-general-4">Does bus booking online cost me more?</button></h2>
                        <div id="faq-general-4" class="accordion-collapse collapse" data-bs-parent="#generalFaqAccordion">
                            <div class="accordion-body ps-2">Not at all. In fact, booking online with PBL Bus is often cheaper! You get access to special online-only discounts and offers that are not available at physical counters. The price you see is inclusive of all standard taxes.</div>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Ticket-related FAQs -->
            <div class="tab-pane fade" id="ticket" role="tabpanel">
                <div class="accordion accordion-flush faq-accordion" id="ticketFaqAccordion">
                    <div class="accordion-item">
                        <h2 class="accordion-header"><button class="accordion-button collapsed ps-2" type="button" data-bs-toggle="collapse" data-bs-target="#faq-ticket-1">Do I need to print my bus ticket?</button></h2>
                        <div id="faq-ticket-1" class="accordion-collapse collapse" data-bs-parent="#ticketFaqAccordion">
                            <div class="accordion-body ps-2">Most bus operators on our platform accept M-tickets (the ticket confirmation you receive via SMS or email on your phone). You can show this on your mobile device along with a valid photo ID. Please check the specific operator's policy mentioned on your ticket for confirmation.</div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header"><button class="accordion-button collapsed ps-2" type="button" data-bs-toggle="collapse" data-bs-target="#faq-ticket-2">What is the luggage policy?</button></h2>
                        <div id="faq-ticket-2" class="accordion-collapse collapse" data-bs-parent="#ticketFaqAccordion">
                            <div class="accordion-body ps-2">Luggage policies vary by bus operator. Typically, passengers are allowed one piece of luggage (up to 15-20 kg) and a small handbag. Any extra or oversized luggage is subject to the operator's discretion and may incur additional charges.</div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header"><button class="accordion-button collapsed ps-2" type="button" data-bs-toggle="collapse" data-bs-target="#faq-ticket-3">What documents do I need to carry while traveling?</button></h2>
                        <div id="faq-ticket-3" class="accordion-collapse collapse" data-bs-parent="#ticketFaqAccordion">
                            <div class="accordion-body ps-2">You must carry a valid government-issued photo ID (like an Aadhar Card, Driver's License, or Passport) along with your M-ticket or a printout of the e-ticket. The name on the ID should match the name of the passenger on the ticket.</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment FAQs -->
            <div class="tab-pane fade" id="payment" role="tabpanel">
                <div class="accordion accordion-flush faq-accordion" id="paymentFaqAccordion">
                    <div class="accordion-item">
                        <h2 class="accordion-header"><button class="accordion-button collapsed ps-2" type="button" data-bs-toggle="collapse" data-bs-target="#faq-payment-1">What payment options are available?</button></h2>
                        <div id="faq-payment-1" class="accordion-collapse collapse" data-bs-parent="#paymentFaqAccordion">
                            <div class="accordion-body ps-2">We accept a wide variety of payment methods, including Credit Cards, Debit Cards, Net Banking, UPI (Google Pay, PhonePe, etc.), and popular digital wallets.</div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header"><button class="accordion-button collapsed ps-2" type="button" data-bs-toggle="collapse" data-bs-target="#faq-payment-2">My payment failed but money was deducted. What should I do?</button></h2>
                        <div id="faq-payment-2" class="accordion-collapse collapse" data-bs-parent="#paymentFaqAccordion">
                            <div class="accordion-body ps-2">In such rare cases, the deducted amount is usually reversed back to your account by your bank within 5-7 working days. Please wait for the stipulated time. If you do not receive the refund, contact our customer support with your transaction details.</div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header"><button class="accordion-button collapsed ps-2" type="button" data-bs-toggle="collapse" data-bs-target="#faq-payment-3">Are my payment details secure?</button></h2>
                        <div id="faq-payment-3" class="accordion-collapse collapse" data-bs-parent="#paymentFaqAccordion">
                            <div class="accordion-body ps-2">Absolutely. We use industry-standard SSL encryption for all transactions, and we do not store your card or bank details on our servers. Your payment is processed through a secure, certified payment gateway.</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Cancellation & Refund FAQs -->
            <div class="tab-pane fade" id="cancellation" role="tabpanel">
                <div class="accordion accordion-flush faq-accordion" id="cancellationFaqAccordion">
                    <div class="accordion-item">
                        <h2 class="accordion-header"><button class="accordion-button collapsed ps-2" type="button" data-bs-toggle="collapse" data-bs-target="#faq-cancel-1">How can I cancel my bus ticket?</button></h2>
                        <div id="faq-cancel-1" class="accordion-collapse collapse" data-bs-parent="#cancellationFaqAccordion">
                            <div class="accordion-body ps-2">You can cancel your ticket by logging into your PBL Bus account and visiting the "My Bookings" section. Select the booking you wish to cancel and click the 'Cancel Ticket' button. The applicable refund amount will be shown to you before you confirm the cancellation.</div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header"><button class="accordion-button collapsed ps-2" type="button" data-bs-toggle="collapse" data-bs-target="#faq-cancel-2">How long does it take to get a refund?</button></h2>
                        <div id="faq-cancel-2" class="accordion-collapse collapse" data-bs-parent="#cancellationFaqAccordion">
                            <div class="accordion-body">The refund is processed instantly from our end. Depending on your bank's processing time, it usually reflects in your source account within 5-7 working days.</div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq-cancel-3">Can I partially cancel my booking?</button></h2>
                        <div id="faq-cancel-3" class="accordion-collapse collapse" data-bs-parent="#cancellationFaqAccordion">
                            <div class="accordion-body ps-2">Partial cancellation (canceling only some seats in a multi-seat booking) is subject to the policies of the bus operator. If allowed, you will see the option during the cancellation process. If the option is not available, you would need to cancel the entire booking and re-book.</div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- Still Need Help Section -->
        <div class="still-need-help-section text-center">
            <hr class="my-3">
            <h3 class="faq-title">Still can't find your answer?</h3>
            <p class="mb-4">Our support team is here for you 24/7. Get in touch with us.</p>
            <div class="row justify-content-center">
                <div class="col-md-5 mb-3">
                    <div class="contact-card">
                        <i class="bi bi-envelope-fill contact-card-icon mb-3"></i>
                        <h5>Email Support</h5>
                        <p>Get a detailed response.</p>
                        <a href="mailto:support@pblbus.com">support@pblbus.com</a>
                    </div>
                </div>
                <div class="col-md-5 mb-3">
                    <div class="contact-card">
                        <i class="bi bi-telephone-fill contact-card-icon mb-3"></i>
                        <h5>Call Us</h5>
                        <p>For urgent help.</p>
                        <a href="tel:+911234567890">+91 123 456 7890</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>
<?php include 'includes/header.php'; ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Help & Support - BPL Tickets</title>
    <!-- Your existing CSS from header.php -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>

<body style="background-color: #f8f9fa;">

    <!-- ======= FAQ Hero Section ======= -->
    <section class="text-center mt-5 py-5" style="background-color: #fffafb; border-bottom: 1px solid #f0e4e6;">
        <div class="container mt-4">
            <h1 class="display-5" style="font-weight: 700; color: #7b003a;">Help & Support</h1>
            <p class="lead mt-3" style="color: #555;">Your questions, answered. Find everything you need to know about booking and managing your bus journey with us.</p>
        </div>
    </section>

    <main class="container my-5">
        <div class="card" style="border: none; border-radius: 15px; box-shadow: 0 8px 30px rgba(0,0,0,0.07); overflow: hidden; position: relative;">
            <div style="position: absolute; right: -50px; top: -40px; font-size: 250px; color: #e9ecef; opacity: 0.5; z-index: 1; transform: rotate(-15deg);"><i class="bi bi-patch-question"></i></div>
            <div class="card-body p-4 p-md-5" style="position: relative; z-index: 2;">
                <h2 class="mb-4" style="font-weight: 600; color: #343a40;">Frequently Asked Questions</h2>

                <!-- Tab Navigation -->
                <ul class="nav nav-pills nav-fill mb-4" id="faqTab" role="tablist" style="background-color: #f8f9fa; border-radius: 10px; padding: 5px;">
                    <li class="nav-item" role="presentation"><button class="nav-link active" id="general-tab" data-bs-toggle="tab" data-bs-target="#general" type="button" role="tab">General</button></li>
                    <li class="nav-item" role="presentation"><button class="nav-link" id="ticket-tab" data-bs-toggle="tab" data-bs-target="#ticket" type="button" role="tab">Ticket-related</button></li>
                    <li class="nav-item" role="presentation"><button class="nav-link" id="payment-tab" data-bs-toggle="tab" data-bs-target="#payment" type="button" role="tab">Payment</button></li>
                    <li class="nav-item" role="presentation"><button class="nav-link" id="cancellation-tab" data-bs-toggle="tab" data-bs-target="#cancellation" type="button" role="tab">Cancellation</button></li>
                </ul>

                <!-- Tab Content -->
                <div class="tab-content" id="faqTabContent">
                    <!-- General FAQs -->
                    <div class="tab-pane fade show active" id="general" role="tabpanel">
                        <div class="accordion accordion-flush" id="generalFaqAccordion">
                            <div class="accordion-item" style="border-bottom: 1px solid #dee2e6;">
                                <h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq-gen-1"><i class="bi bi-pin-map-fill me-3"></i>Can I track the location of my booked bus online?</button></h2>
                                <div id="faq-gen-1" class="accordion-collapse collapse" data-bs-parent="#generalFaqAccordion">
                                    <div class="accordion-body">Yes, for many of our bus operators, we offer a live tracking feature. You can find the "Track My Bus" link in your booking confirmation email or in the "My Bookings" section of your account. This allows you to see the real-time location of your bus.</div>
                                </div>
                            </div>
                            <div class="accordion-item" style="border-bottom: 1px solid #dee2e6;">
                                <h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq-gen-2"><i class="bi bi-hand-thumbs-up-fill me-3"></i>What are the advantages of purchasing a bus ticket with BPL Bus?</button></h2>
                                <div id="faq-gen-2" class="accordion-collapse collapse" data-bs-parent="#generalFaqAccordion">
                                    <div class="accordion-body">With BPL Bus, you get access to a wide network of trusted bus operators, transparent pricing, exclusive discounts, 24/7 customer support, and innovative features like live tracking and M-tickets, ensuring a seamless and reliable booking experience.</div>
                                </div>
                            </div>
                            <div class="accordion-item" style="border-bottom: 1px solid #dee2e6;">
                                <h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq-gen-3"><i class="bi bi-person-plus-fill me-3"></i>Do I need to create an account to book my bus ticket?</button></h2>
                                <div id="faq-gen-3" class="accordion-collapse collapse" data-bs-parent="#generalFaqAccordion">
                                    <div class="accordion-body">While you can book as a guest, creating an account is highly recommended. An account allows you to easily manage your bookings, view your travel history, save passenger details for faster checkout, and access exclusive member-only offers.</div>
                                </div>
                            </div>
                            <div class="accordion-item">
                                <h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq-gen-4"><i class="bi bi-tags-fill me-3"></i>Does bus booking online cost me more?</button></h2>
                                <div id="faq-gen-4" class="accordion-collapse collapse" data-bs-parent="#generalFaqAccordion">
                                    <div class="accordion-body">Not at all. In fact, booking online with BPL Bus is often cheaper! You get access to special online-only discounts and offers that are not available at physical counters. The price you see is inclusive of all standard taxes.</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Ticket-related FAQs -->
                    <div class="tab-pane fade" id="ticket" role="tabpanel">
                        <div class="accordion accordion-flush" id="ticketFaqAccordion">
                            <div class="accordion-item" style="border-bottom: 1px solid #dee2e6;">
                                <h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq-tkt-1"><i class="bi bi-phone-fill me-3"></i>Do I need to print my bus ticket?</button></h2>
                                <div id="faq-tkt-1" class="accordion-collapse collapse" data-bs-parent="#ticketFaqAccordion">
                                    <div class="accordion-body">Most bus operators on our platform accept M-tickets (the ticket confirmation you receive via SMS or email on your phone). You can show this on your mobile device along with a valid photo ID. Please check the specific operator's policy mentioned on your ticket for confirmation.</div>
                                </div>
                            </div>
                            <div class="accordion-item" style="border-bottom: 1px solid #dee2e6;">
                                <h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq-tkt-2"><i class="bi bi-suitcase-lg-fill me-3"></i>What is the luggage policy?</button></h2>
                                <div id="faq-tkt-2" class="accordion-collapse collapse" data-bs-parent="#ticketFaqAccordion">
                                    <div class="accordion-body">Luggage policies vary by bus operator. Typically, passengers are allowed one piece of luggage (up to 15-20 kg) and a small handbag. Any extra or oversized luggage is subject to the operator's discretion and may incur additional charges.</div>
                                </div>
                            </div>
                            <div class="accordion-item">
                                <h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq-tkt-3"><i class="bi bi-person-vcard-fill me-3"></i>What documents do I need to carry while traveling?</button></h2>
                                <div id="faq-tkt-3" class="accordion-collapse collapse" data-bs-parent="#ticketFaqAccordion">
                                    <div class="accordion-body">You must carry a valid government-issued photo ID (like an Aadhar Card, Driver's License, or Passport) along with your M-ticket or a printout of the e-ticket. The name on the ID should match the name of the passenger on the ticket.</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payment FAQs -->
                    <div class="tab-pane fade" id="payment" role="tabpanel">
                        <div class="accordion accordion-flush" id="paymentFaqAccordion">
                            <div class="accordion-item" style="border-bottom: 1px solid #dee2e6;">
                                <h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq-pay-1"><i class="bi bi-credit-card-2-front-fill me-3"></i>What payment options are available?</button></h2>
                                <div id="faq-pay-1" class="accordion-collapse collapse" data-bs-parent="#paymentFaqAccordion">
                                    <div class="accordion-body">We accept a wide variety of payment methods, including Credit Cards, Debit Cards, Net Banking, UPI (Google Pay, PhonePe, etc.), and popular digital wallets.</div>
                                </div>
                            </div>
                            <div class="accordion-item" style="border-bottom: 1px solid #dee2e6;">
                                <h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq-pay-2"><i class="bi bi-exclamation-triangle-fill me-3"></i>My payment failed but money was deducted. What should I do?</button></h2>
                                <div id="faq-pay-2" class="accordion-collapse collapse" data-bs-parent="#paymentFaqAccordion">
                                    <div class="accordion-body">In such rare cases, the deducted amount is usually reversed back to your account by your bank within 5-7 working days. Please wait for the stipulated time. If you do not receive the refund, contact our customer support with your transaction details.</div>
                                </div>
                            </div>
                            <div class="accordion-item">
                                <h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq-pay-3"><i class="bi bi-shield-lock-fill me-3"></i>Are my payment details secure?</button></h2>
                                <div id="faq-pay-3" class="accordion-collapse collapse" data-bs-parent="#paymentFaqAccordion">
                                    <div class="accordion-body">Absolutely. We use industry-standard SSL encryption for all transactions, and we do not store your card or bank details on our servers. Your payment is processed through a secure, certified payment gateway.</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Cancellation & Refund FAQs -->
                    <div class="tab-pane fade" id="cancellation" role="tabpanel">
                        <div class="accordion accordion-flush" id="cancellationFaqAccordion">
                            <div class="accordion-item" style="border-bottom: 1px solid #dee2e6;">
                                <h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq-can-1"><i class="bi bi-x-circle-fill me-3"></i>How can I cancel my bus ticket?</button></h2>
                                <div id="faq-can-1" class="accordion-collapse collapse" data-bs-parent="#cancellationFaqAccordion">
                                    <div class="accordion-body">You can request to cancel your ticket by logging into your BPL Bus account and visiting the "My Bookings" section. Select the booking and follow the steps. Alternatively, you can use the "Cancel Ticket" link in the website menu.</div>
                                </div>
                            </div>
                            <div class="accordion-item" style="border-bottom: 1px solid #dee2e6;">
                                <h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq-can-2"><i class="bi bi-arrow-clockwise me-3"></i>How long does it take to get a refund?</button></h2>
                                <div id="faq-can-2" class="accordion-collapse collapse" data-bs-parent="#cancellationFaqAccordion">
                                    <div class="accordion-body">Once your cancellation request is approved, the refund is processed instantly from our end. Depending on your bank's processing time, it usually reflects in your source account within 24-48 working hours.</div>
                                </div>
                            </div>
                            <div class="accordion-item">
                                <h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq-can-3"><i class="bi bi-people-fill me-3"></i>Can I partially cancel my booking?</button></h2>
                                <div id="faq-can-3" class="accordion-collapse collapse" data-bs-parent="#cancellationFaqAccordion">
                                    <div class="accordion-body">Yes, our platform supports partial cancellation. You can select individual passengers to cancel from a booking, subject to the cancellation deadline. The refund will be calculated for the selected passengers only.</div>
                                </div>
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
            <p class="mb-4 text-muted">Our support team is here for you 24/7. Get in touch with us.</p>
            <div class="row justify-content-center">
                <div class="col-md-5 mb-3">
                    <div class="card h-100" style="border-radius: 15px; border: 1px solid #dee2e6; transition: all 0.2s ease-in-out;">
                        <div class="card-body p-4 text-center">
                            <div style="font-size: 2.5rem; color: #7b003a; margin-bottom: 1rem;"><i class="bi bi-envelope-fill"></i></div>
                            <h5 style="font-weight: 600;">Email Support</h5>
                            <p class="text-muted">Get a detailed response.</p>
                            <a href="mailto:support@bplbus.com" style="font-weight: 500;">support@bplbus.com</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-5 mb-3">
                    <div class="card h-100" style="border-radius: 15px; border: 1px solid #dee2e6; transition: all 0.2s ease-in-out;">
                        <div class="card-body p-4 text-center">
                            <div style="font-size: 2.5rem; color: #7b003a; margin-bottom: 1rem;"><i class="bi bi-telephone-fill"></i></div>
                            <h5 style="font-weight: 600;">Call Us</h5>
                            <p class="text-muted">For urgent help.</p>
                            <a href="tel:+911234567890" style="font-weight: 500;">+91 123 456 7890</a>
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
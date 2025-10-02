<?php
// cancellation_policy.php
include 'includes/header.php'; // Includes your database connection and header elements
?>
<style>
    /* This CSS is identical to the terms_and_conditions.php page for consistent styling */
    .hero-section {
        background: linear-gradient(rgba(248, 249, 250, 0.8), rgba(248, 249, 250, 0.9)), url('path/to/your/background-image.jpg') no-repeat center center;
        background-size: cover;
    }

    .terms-container {
        /* padding: 60px 0; */
    }

    .terms-card {
        background-color: transparent;
        /* border: 1px solid #e9ecef; */
        border-radius: 12px;
        padding: 40px;
        /* box-shadow: 0 8px 30px rgba(0, 0, 0, 0.07); */
    }

    .terms-card h1, .terms-card h2, .terms-card h3 {
        color: #7b003a; /* Your brand's primary color */
        font-weight: 700;
        margin-top: 2rem;
        margin-bottom: 1rem;
        padding-bottom: 0.5rem;
        border-bottom: 2px solid #f0f0f0;
    }
    
    .terms-card h1 {
        font-size: 2.5rem;
        text-align: center;
        border-bottom: 2px solid #7b003a;
        margin-top: 0;
    }

    .terms-card h2 {
        font-size: 1.8rem;
    }

    .terms-card h3 {
        font-size: 1.4rem;
        border-bottom: none;
        color: #343a40;
    }

    .terms-card p, .terms-card li {
        color: #555;
        line-height: 1.8;
        font-size: 1.1rem;
        margin-bottom: 1rem;
    }

    .terms-card ol { /* Changed from ul to ol for numbered steps */
        list-style-type: decimal;
        padding-left: 25px;
    }

    .terms-card a {
        color: #7b003a;
        text-decoration: none;
        font-weight: 600;
    }

    .terms-card a:hover {
        text-decoration: underline;
    }

    .last-updated {
        text-align: center;
        color: #888;
        margin-top: -1rem;
        margin-bottom: 2rem;
        font-style: italic;
    }
    /* Style for the refund table */
    .refund-table {
        width: 100%;
        margin: 2rem 0;
        border-collapse: collapse;
    }
    .refund-table th, .refund-table td {
        border: 1px solid #dee2e6;
        padding: 12px 15px;
        text-align: center;
    }
    .refund-table th {
        background-color: #f8f9fa;
        font-weight: 600;
    }
    .refund-table td:first-child {
        text-align: left;
    }
    .no-refund {
        color: #dc3545;
        font-weight: 700;
    }
</style>

<body>
    <main>
        <section class="hero-section">
            <div class="container">
                <h1 class="fw-bold" style="color:#7b003a">Cancellation & Refund Policy</h1>
                <p class="lead" style="color:#7b003a">Understand our policies before you book or cancel.</p>
            </div>
        </section>

        <div class="container terms-container">
            <div class="terms-card">
                <h1>Cancellation & Refund Policy for <?php echo htmlspecialchars($company_name); ?></h1>
                <p class="last-updated">Last Updated: <?php echo date('F d, Y'); ?></p>

                <p>At <?php echo htmlspecialchars($company_name); ?>, we strive to make our policies as transparent and fair as possible. This policy outlines the rules and procedures for cancelling a bus ticket booked through our platform. Please note that while we provide this framework, the final cancellation charges are determined by the bus operator.</p>

                <h2>1. Cancellation Time Frame and Refund Slabs</h2>
                <p>Our cancellation policy is based on the time of cancellation relative to the **scheduled departure time of the bus from its starting point**. The refund amount will be calculated based on the following slabs:</p>
                
                <table class="refund-table">
                    <thead>
                        <tr>
                            <th>Time of Cancellation</th>
                            <th>Refund Percentage</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Before 24 hours of departure</td>
                            <td>90% of the ticket fare</td>
                        </tr>
                        <tr>
                            <td>Between 12 hours and 24 hours of departure</td>
                            <td>75% of the ticket fare</td>
                        </tr>
                        <tr>
                            <td class="no-refund"><strong>Less than 12 hours before departure</strong></td>
                            <td class="no-refund"><strong>0% (No Refund)</strong></td>
                        </tr>
                    </tbody>
                </table>
                <p><strong>Important Note:</strong> The departure time is considered from the route's official starting point, not your specific boarding point. No cancellation requests will be accepted or processed within 12 hours of the bus's departure time.</p>

                <!-- ========================================================= -->
                <!-- ========== THIS IS THE CORRECTED SECTION ========== -->
                <!-- ========================================================= -->
                <h2>2. How to Cancel a Ticket</h2>
                <p>Cancellations can only be initiated online through our official website. Please follow these steps:</p>
                <ol>
                    <li><strong>Step 1:</strong> Click on "My Account" or "Cancel Ticket" in the website's top menu.</li>
                    <li><strong>Step 2:</strong> Enter your unique Booking Number (sent to you via email/SMS after booking) and the contact mobile number used for the booking.</li>
                    <li><strong>Step 3:</strong> Click "Find Ticket" to retrieve your booking details.</li>
                    <li><strong>Step 4:</strong> On the booking details page, use the checkboxes to select the specific passenger(s) you wish to cancel.</li>
                    <li><strong>Step 5:</strong> Click the "Cancel Selected Ticket(s)" button.</li>
                    <li><strong>Step 6:</strong> Provide a reason for your cancellation from the options provided and submit your request.</li>
                </ol>
                <p><strong>Please Note:</strong> Cancellation requests made via phone call, email, or directly to the bus operator will not be considered valid for a refund from <?php echo htmlspecialchars($company_name); ?>. You must use the online portal.</p>
                <!-- ========================================================= -->
                <!-- ========== END OF THE CORRECTED SECTION ========== -->
                <!-- ========================================================= -->

                <h2>3. Key Conditions</h2>
                <h3>3.1. Partial Cancellation</h3>
                <p>Partial cancellation of a booking (i.e., for some passengers but not all) is allowed, subject to the refund slabs mentioned above. The refund will be calculated only for the passengers whose tickets have been cancelled.</p>

                <h3>3.2. Non-Refundable Tickets</h3>
                <p>Some tickets, especially those booked under special promotional offers, may be marked as "non-refundable". In such cases, no refund will be issued regardless of when the cancellation is made. This will be explicitly mentioned at the time of booking.</p>

                <h3>3.3. Refund Processing</h3>
                <p>Refunds are credited back to the original source of payment (e.g., credit card, UPI, wallet). Please allow 5-7 business days for the refund amount to reflect in your account. This timeline is dependent on the bank and payment gateway processing cycles.</p>
                
                <h2>4. Cancellation by Bus Operator</h2>
                <p>In the unfortunate event that a bus service is cancelled by the operator for any reason (e.g., breakdown, weather, etc.), you will be entitled to a **100% full refund** of your ticket fare.</p>
                <p>We will do our best to notify you of such cancellations via SMS or email as soon as we receive the information from the operator. Our liability is limited to providing the refund for the ticket amount; we are not responsible for any other consequential losses.</p>

                <h2>5. Contact Us</h2>
                <p>For any queries regarding our cancellation policy, please feel free to contact our customer support team.</p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($email_primary); ?></p>
                <p><strong>Phone:</strong> <?php echo htmlspecialchars($mobile_primary); ?></p>
            </div>
        </div>
    </main>
    <?php include "includes/footer.php" ?>
</body>
</html>
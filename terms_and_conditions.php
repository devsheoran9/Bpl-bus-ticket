<?php
// terms_and_conditions.php
include 'includes/header.php'; // Includes your database connection and header elements
?>
<style>
    /* Inherit styles from your main page for consistency */
    .hero-section {
        background: linear-gradient(rgba(248, 249, 250, 0.8), rgba(248, 249, 250, 0.9)), url('path/to/your/background-image.jpg') no-repeat center center;
        background-size: cover;
    }

    .terms-container { 
    }

    .terms-card {
        background-color: transparent;
        /* border: 1px solid #e9ecef; */
        border-radius: 12px;
        padding: 40px;
        /* box-shadow: 0 8px 30px rgba(0, 0, 0, 0.07);   */
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

    .terms-card ul {
        list-style-type: disc;
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
</style>

<body>
    <main>
        <section class="hero-section">
            <div class="container">
                <h1 class="fw-bold" style="color:#7b003a">Terms and Conditions</h1>
                <p class="lead" style="color:#7b003a">Please read these terms carefully before using our services.</p>
            </div>
        </section>

        <div class="container terms-container">
            <div class="terms-card">
                <h1>Terms of Service for <?php echo htmlspecialchars($company_name); ?></h1>
                <p class="last-updated">Last Updated: <?php echo date('F d, Y'); ?></p>

                <p>Welcome to <?php echo htmlspecialchars($company_name); ?> ("BPL Bus Ticket", "we", "us", or "our"). These Terms and Conditions govern your use of our services. By using our platform, you agree to these terms in full. If you disagree with any part of these terms, you must not use our services.</p>

                <h2>A. BUS TICKET BOOKING</h2>
                
                <h3>1. Role of <?php echo htmlspecialchars($company_name); ?></h3>
                <p><?php echo htmlspecialchars($company_name); ?> only provides a technology platform that connects intending travelers with bus operators. It does not operate any bus or offer the service of transportation to the User. <?php echo htmlspecialchars($company_name); ?> also does not act as an agent of any bus operator in the process of providing the above-mentioned technology platform services.</p>
                <p>The bus ticket booking voucher which <?php echo htmlspecialchars($company_name); ?> issues to a User is solely based on the information provided or updated by the bus operator regarding seat availability.</p>
                <p>The amenities, services, routes, fares, schedule, bus type, seat availability, and any other details pertaining to the bus service are provided by the respective bus operator, and <?php echo htmlspecialchars($company_name); ?> has no control over such information.</p>

                <h3>2. Limitation of Liability of <?php echo htmlspecialchars($company_name); ?></h3>
                <p>In its role as a technology platform, <?php echo htmlspecialchars($company_name); ?> shall not be responsible for the operations of the bus operator, including, but not limited to, the following:</p>
                <ul>
                    <li>Timely departure or arrival of the bus.</li>
                    <li>The conduct of the bus operator's employees, representatives, or agents.</li>
                    <li>The condition of the bus, seats, etc., not being up to the customer's expectation or as per the description provided by the bus operator.</li>
                    <li>Cancellation of the trip due to any reason by the bus operator.</li>
                    <li>Loss or damage of the baggage of the customer.</li>
                    <li>The bus operator changing a customer's seat for any reason whatsoever.</li>
                    <li>The bus operator informing a wrong boarding point for the issuance of the booking confirmation voucher or changing such a boarding point without notification.</li>
                    <li>Bus operator using a separate pick-up vehicle to transport the User from the designated boarding point to the actual place of departure of the bus.</li>
                </ul>

                <h3>3. Responsibilities of the Users</h3>
                <ul>
                    <li>Users are advised to call the bus operator to find out the exact boarding point or any other information they may need for the purpose of boarding or travel.</li>
                    <li>At the time of boarding the bus, Users shall furnish a copy of the ticket and a valid government-issued identity proof like an Aadhar card, passport, PAN card, or voter ID card.</li>
                    <li>Users are required to reach the boarding place at least 30 minutes before the scheduled departure time.</li>
                    <li>All tickets issued shall be non-transferable.</li>
                </ul>

                <h3>4. Cancellation and Rescheduling of Tickets</h3>
                <ul>
                    <li>Cancellation of tickets can be done through the User's login on the <?php echo htmlspecialchars($company_name); ?> website or by finding the booking via the "Cancel Ticket" link.</li>
                    <li>Any cancellation is subject to the cancellation charges mentioned on the ticket and in our <a href="cancellation_policy.php">Cancellation Policy</a>.</li>
                    <li>Rescheduling (change of travel date) is an option provided only by select bus operators. The policy for the same shall be available on the e-ticket.</li>
                    <li>Rescheduling a ticket is subject to charges as mentioned on the e-ticket. Any fare difference shall be borne by the customer.</li>
                    <li>Tickets are non-transferable, and the originally booked passengers must travel upon rescheduling.</li>
                </ul>

                <h2>B. BUS CHARTER (RYDE)</h2>

                <h3>1. Role of <?php echo htmlspecialchars($company_name); ?></h3>
                <p><?php echo htmlspecialchars($company_name); ?> provides a technology platform to connect intending travelers with vehicle operators to hire an entire vehicle ("Charter" or "Ryde"). We do not operate any vehicles ourselves. The fulfillment of these bookings is done by third-party operators empanelled with us.</p>
                <p>The vehicle booking details are based on information provided by the vehicle operator. Amenities, services, fares, and other details are provided by the respective operator, and <?php echo htmlspecialchars($company_name); ?> has no control over this information or its fulfillment.</p>
                
                <h3>2. Limitation of Liability for Charter Services</h3>
                <p>As a technology platform, <?php echo htmlspecialchars($company_name); ?> shall not be liable for any failure on the part of the service provider, including but not limited to:</p>
                <ul>
                    <li>Timely departure or arrival of the vehicle.</li>
                    <li>The conduct of the operator's employees or agents.</li>
                    <li>The condition of the vehicle not meeting expectations.</li>
                    <li>Cancellation of the trip by the vehicle operator.</li>
                    <li>Loss or damage to baggage.</li>
                    <li>In cases of vehicle unavailability or breakdown, we will make a best effort to arrange an alternate vehicle of a similar standard, but this is not guaranteed.</li>
                </ul>

                <h3>3. Responsibilities of Users for Charter Services</h3>
                <ul>
                    <li>Users must call the vehicle operator to confirm the exact boarding point and other details.</li>
                    <li>Users shall furnish a copy of the confirmation voucher and a valid ID at the time of boarding.</li>
                    <li>Users are advised to check the booking confirmation for correctness and report any errors immediately.</li>
                </ul>

                <h3>4. Payment for Charter Booking</h3>
                <ul>
                    <li><strong>Full Payment:</strong> Payment may be made in full during the booking, including base fare and applicable taxes.</li>
                    <li><strong>Partial Payment:</strong> The User may be required to pay a partial amount at booking and the balance within a specified time. Failure to pay the balance may lead to cancellation.</li>
                    <li>Expenses like toll charges, permit charges, parking fees, and other government taxes are to be borne by the User and paid directly to the driver unless specified otherwise.</li>
                </ul>
                
                <h2>C. MISCELLANEOUS</h2>
                <p>The bus operator shall be solely liable for compliance with all applicable laws, including the Motor Vehicle Act and its Rules. Any prosecution arising from the contravention of such laws shall be borne by the bus operator.</p>
                <p>The terms and conditions shall be governed by the laws of India. Any dispute arising out of or in relation to our services shall be subject to the exclusive jurisdiction of competent courts in [Your City, e.g., Jaipur, Rajasthan], India.</p>
                <p>The maximum liability of <?php echo htmlspecialchars($company_name); ?> in the event of any claim shall not exceed the amount paid by the User for the service in question.</p>
                
                <h2>Contact Us</h2>
                <p>If you have any questions, please contact us at:</p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($email_primary); ?></p>
                <p><strong>Phone:</strong> <?php echo htmlspecialchars($mobile_primary); ?></p>
            </div>
        </div>
    </main>
    <?php include "includes/footer.php" ?>
</body>
</html>
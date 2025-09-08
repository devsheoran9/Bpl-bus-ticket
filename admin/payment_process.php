<?php
// payment_process.php
include_once('function/_db.php');
session_security_check();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include "head.php"; ?>
    <title>Processing Payment</title>
</head>
<body>
<div id="wrapper">
    <?php include_once('sidebar.php'); ?>
    <div class="main-content">
        <?php include_once('header.php'); ?>
        <div class="container-fluid">
            <div class="d-flex justify-content-center align-items-center" style="min-height: 70vh;">
                <div class="card text-center" style="max-width: 500px;">
                    <div class="card-header"><h4 class="mb-0">Online Payment</h4></div>
                    <div class="card-body" id="payment-status-area">
                        <p>Retrieving booking details...</p>
                        <div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include "foot.php"; ?>
<script>
$(document).ready(function() {
    const statusArea = $('#payment-status-area');
    const bookingDataStr = sessionStorage.getItem('pendingBookingData');

    if (!bookingDataStr) {
        statusArea.html('<div class="alert alert-danger">Error: No booking data found. Please start over.</div>');
        return;
    }

    const bookingData = JSON.parse(bookingDataStr);

    statusArea.html(`
        <p>You are about to pay <strong>₹${bookingData.total_fare}</strong> for your booking.</p>
        <p class="text-muted">Real Razorpay integration would be here. We are simulating a successful payment.</p>
        <button id="simulate-payment-btn" class="btn btn-primary btn-lg">Simulate Successful Payment</button>
    `);

    $('#simulate-payment-btn').on('click', function() {
        const btn = $(this);
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Processing...');

        // Step 1: Create the booking in the database first. It will be in PENDING state.
        $.post('function/backend/booking_actions.php', { action: 'create_pending_booking', ...bookingData }, null, 'json')
        .done(response => {
            if (response.status === 'success') {
                const bookingId = response.booking_id;
                
                // Step 2: Simulate a successful Razorpay payment response.
                // In a real scenario, this data comes from Razorpay's success callback.
                const fakePaymentDetails = {
                    gateway_payment_id: 'pay_' + Math.random().toString(36).substr(2, 9),
                    gateway_order_id: 'order_' + Math.random().toString(36).substr(2, 9),
                    gateway_signature: 'dummy_signature_' + Math.random().toString(36).substr(2, 12),
                    method: 'upi',
                    status: 'CAPTURED' // Razorpay status for successful payment
                };

                // Step 3: Send this payment data to a new backend handler to log the transaction.
                $.post('function/backend/payment_handler.php', {
                    action: 'log_successful_payment',
                    booking_id: bookingId,
                    amount: bookingData.total_fare,
                    payment_details: JSON.stringify(fakePaymentDetails)
                }, null, 'json')
                .done(updateResponse => {
                    if (updateResponse.status === 'success') {
                        Swal.fire('Payment Successful!', 'Your booking is confirmed. Booking ID: ' + bookingId, 'success')
                        .then(() => {
                            sessionStorage.removeItem('pendingBookingData');
                            // Redirect to a page where user can see their booking, e.g., view_bookings.php
                            window.location.href = 'view_bookings.php'; 
                        });
                    } else {
                        Swal.fire('Error', 'Payment was successful, but booking could not be updated. Please contact support. Booking ID: ' + bookingId, 'error');
                    }
                });

            } else {
                Swal.fire('Booking Failed', response.message, 'error');
                btn.prop('disabled', false).html('Simulate Successful Payment');
            }
        })
        .fail(() => {
            Swal.fire('Error', 'Could not initiate booking.', 'error');
            btn.prop('disabled', false).html('Simulate Successful Payment');
        });
    });
});
</script>
</body>
</html>
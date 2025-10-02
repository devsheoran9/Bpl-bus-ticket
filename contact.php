<?php
include 'includes/header.php';
$company_phone = $mobile_primary;
$company_email = $email_primary; 
?>
<link href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" rel="stylesheet">
<style>
    :root {
        --primary-color: #d32f2f;
        --primary-dark: #b71c1c;
        --text-dark: #2d3748;
        --text-light: #718096;
        --bg-light: #f8fafc;
        --bg-white: #ffffff;
        --border-color: #e2e8f0;
        --shadow-sm: 0 2px 8px rgba(0, 0, 0, 0.06);
        --shadow-md: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    body {
        font-family: 'Inter', sans-serif;
        background-color: var(--bg-light);
    }

    .page-header h1 {
        font-family: 'Playfair Display', serif;
        color: var(--text-dark);
    }

    .contact-section {
        padding-top: 4rem;
        padding-bottom: 5rem;
    }

    .info-card {
        text-align: center;
        padding: 2rem;
        background: var(--bg-white);
        border-radius: 16px;
        box-shadow: var(--shadow-sm);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        height: 100%;
        border-top: 4px solid var(--primary-color);
    }

    .info-card:hover {
        transform: translateY(-8px);
        box-shadow: var(--shadow-md);
    }

    .info-card .icon {
        font-size: 3rem;
        color: var(--primary-color);
        margin-bottom: 1.5rem;
    }

    .info-card h4 {
        font-weight: 700;
        color: var(--text-dark);
    }

    .info-card a,
    .info-card p {
        font-size: 1.1rem;
        color: var(--text-light);
        text-decoration: none;
    }

    .form-card {
        background: var(--bg-white);
        border-radius: 16px;
        box-shadow: var(--shadow-md);
        border: 1px solid var(--border-color);
        padding: 2.5rem;
    }

    .form-control:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 0.25rem rgba(211, 47, 47, 0.25);
    }

    .btn-submit {
        background: linear-gradient(45deg, var(--primary-dark), var(--primary-color));
        border: none;
        padding: 0.8rem 2rem;
        font-weight: 600;
        font-size: 1.1rem;
        border-radius: 50px;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(211, 47, 47, 0.3);
    }

    .btn-submit:hover {
        transform: translateY(-3px);
        box-shadow: 0 7px 20px rgba(211, 47, 47, 0.4);
    }

    .btn-submit:disabled {
        background: #6c757d;
        transform: none;
        box-shadow: none;
        cursor: not-allowed;
    }

    .trip-type-group .form-check-label {
        font-weight: 500;
    }

    #form-messages {
        display: none;
    }
</style>

<main>
    <div class="contact-section">
        <div class="container">
            <div class="page-header text-center mb-5">
                <h1>We're Here to Help</h1>
                <p class="lead text-secondary">Whether you have a question about a ticket or need to plan a group trip, we're ready to assist.</p>
            </div>
            <div class="row g-4 mb-5">
                <div class="col-lg-4 col-6">
                    <div class="info-card">
                        <i class="bi bi-telephone-fill icon"></i>
                        <h4>Talk to Us</h4>
                        <p class="text-muted small mb-2">For immediate assistance with bookings.</p>
                        <a href="tel:<?= htmlspecialchars($company_phone) ?>"><?= htmlspecialchars($company_phone) ?></a>
                    </div>
                </div>
                <div class="col-lg-4 col-6">
                    <div class="info-card">
                        <i class="bi bi-envelope-fill icon"></i>
                        <h4>Email Support</h4>
                        <p class="text-muted small mb-2">For inquiries, feedback, and support.</p>
                        <a href="mailto:<?= htmlspecialchars($company_email) ?>"><?= htmlspecialchars($company_email) ?></a>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="info-card">
                        <i class="bi bi-geo-alt-fill icon"></i>
                        <h4>Our Office</h4>
                        <p class="text-muted small mb-2">Visit us at our head office for in-person support.</p>
                        <p><?= htmlspecialchars($company_address) ?></p>
                    </div>
                </div>
            </div>

            <div class="row justify-content-center">
                <div class="col-lg-12">
                    <div class="form-card" id="form-card">
                        <div class="text-center mb-4">
                            <h2 class="font-family-serif">Plan Your Group Journey</h2>
                            <p class="text-secondary">Perfect for weddings, corporate outings, or pilgrimage tours. Get a personalized quote for your group by filling out the form below.</p>
                        </div>

                        <!-- Single message area for AJAX responses -->
                        <div id="form-messages" class="alert mb-4"></div>

                        <form id="charter-inquiry-form" action="submit_charter_request.php" method="POST">
                            <div class="row g-3">
                                <div class="col-md-6 form-floating"><input type="text" class="form-control" id="name" name="name" placeholder="Full Name" required><label for="name">Your Full Name</label></div>
                                <div class="col-md-6 form-floating"><input type="tel" class="form-control" id="mobile" name="mobile" placeholder="Mobile Number" required><label for="mobile">Your Mobile Number</label></div>
                                <div class="col-md-6 form-floating"><input type="text" class="form-control" id="from_location" name="from_location" placeholder="Pickup Location" required><label for="from_location">From (City or specific address)</label></div>
                                <div class="col-md-6 form-floating"><input type="text" class="form-control" id="to_location" name="to_location" placeholder="Destination" required><label for="to_location">To (Destination City)</label></div>
                                <div class="col-12"><label class="form-label fw-bold">Trip Type</label>
                                    <div class="trip-type-group d-flex gap-4">
                                        <div class="form-check"><input class="form-check-input" type="radio" name="trip_type" id="one-way" value="One-Way" checked><label class="form-check-label" for="one-way">One-Way Trip</label></div>
                                        <div class="form-check"><input class="form-check-input" type="radio" name="trip_type" id="round-trip" value="Round-Trip"><label class="form-check-label" for="round-trip">Round-Trip</label></div>
                                    </div>
                                </div>
                                <div class="col-md-6 form-floating"><input type="text" class="form-control" id="journey_date" name="journey_date" placeholder="Date of Departure" required><label for="journey_date">Departure Date</label></div>
                                <div class="col-md-6 form-floating" id="return-date-wrapper" style="display: none;"><input type="text" class="form-control" id="return_date" name="return_date" placeholder="Date of Return"><label for="return_date">Return Date</label></div>
                                <div class="col-12 form-floating"><textarea class="form-control" id="message" name="message" placeholder="Message" style="height: 120px"></textarea><label for="message">Additional Details (e.g., number of passengers, specific needs)</label></div>
                                <div class="col-12 d-grid">
                                    <button type="submit" class="btn btn-danger btn-submit" id="submit-btn">
                                        <span class="submit-text"><i class="bi bi-send-fill me-2"></i>Request a Quote</span>
                                        <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include "includes/footer.php"; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const departurePicker = flatpickr("#journey_date", {
            minDate: "today",
            dateFormat: "Y-m-d",
            onChange: function(selectedDates, dateStr) {
                if (returnPicker) returnPicker.set('minDate', dateStr);
            }
        });

        const returnPicker = flatpickr("#return_date", {
            minDate: "today",
            dateFormat: "Y-m-d"
        });

        const returnDateWrapper = document.getElementById('return-date-wrapper');
        const returnDateField = document.getElementById('return_date');

        document.querySelectorAll('input[name="trip_type"]').forEach(radio => {
            radio.addEventListener('change', function() {
                if (this.value === 'Round-Trip') {
                    returnDateWrapper.style.display = 'block';
                    returnDateField.required = true;
                } else {
                    returnDateWrapper.style.display = 'none';
                    returnDateField.required = false;
                    returnDateField.value = '';
                }
            });
        });

        const inquiryForm = document.getElementById('charter-inquiry-form');
        const submitBtn = document.getElementById('submit-btn');
        const formMessages = document.getElementById('form-messages');

        inquiryForm.addEventListener('submit', function(event) {
            event.preventDefault();

            submitBtn.disabled = true;
            submitBtn.querySelector('.submit-text').classList.add('d-none');
            submitBtn.querySelector('.spinner-border').classList.remove('d-none');

            const formData = new FormData(inquiryForm);

            fetch(inquiryForm.action, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    formMessages.style.display = 'block';
                    formMessages.className = `alert alert-${data.status}`;
                    formMessages.textContent = data.message;

                    if (data.status === 'success') {
                        inquiryForm.reset();
                        departurePicker.clear();
                        returnPicker.clear();
                        returnDateWrapper.style.display = 'none';
                        document.getElementById('one-way').checked = true;
                    }
                })
                .catch(error => {
                    formMessages.style.display = 'block';
                    formMessages.className = 'alert alert-danger';
                    formMessages.textContent = 'An unexpected error occurred. Please try again.';
                    console.error('Error:', error);
                })
                .finally(() => {
                    submitBtn.disabled = false;
                    submitBtn.querySelector('.submit-text').classList.remove('d-none');
                    submitBtn.querySelector('.spinner-border').classList.add('d-none');
                });
        });
    });
</script>
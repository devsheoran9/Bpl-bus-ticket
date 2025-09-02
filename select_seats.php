<?php
// You can include a different header file if needed
// include 'includes/header.php'; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Seats - Bus Booking</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="css/custom.css">
</head>
<body>

<header class="page-header">
    <div class="container-fluid d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-3">
            <a href="#" class="text-dark"><i class="bi bi-x fs-3"></i></a>
            <span class="route-info">Delhi → Lucknow</span>
        </div>
        <div class="progress-steps d-flex gap-4">
            <div class="step active">1. Select Seats</div>
            <div class="step">2. Board/Drop point</div>
            <div class="step">3. Passenger Info</div>
        </div>
    </div>
</header>

<main class="container-fluid my-4">
    <div class="row">
        <!-- Left Side: Seat Layout & Legend -->
        <div class="col-lg-8">
            <div class="seat-layout-section">
                <div class="row w-100">
                    <!-- Lower Deck -->
                    <div class="col-md-6 mb-4 mb-md-0">
                        <div class="seat-deck">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="deck-label">Lower deck</span>
                                <img src="https://i.ibb.co/9v0rf6G/steering-wheel.png" alt="steering wheel" class="steering-wheel"/>
                            </div>
                            <!-- SEATS WILL BE DYNAMICALLY GENERATED HERE BY JS -->
                            <div id="lower-deck-seats"></div>
                        </div>
                    </div>
                    <!-- Upper Deck -->
                    <div class="col-md-6">
                        <div class="seat-deck">
                            <span class="deck-label">Upper deck</span>
                            <!-- SEATS WILL BE DYNAMICALLY GENERATED HERE BY JS -->
                            <div id="upper-deck-seats"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="legend-section">
                <h5>Know your seat types</h5>
                <div class="legend-row">
                    <span>Available</span>
                    <div class="seat-visual seater available"></div>
                    <div class="seat-visual sleeper available"></div>
                </div>
                <div class="legend-row">
                    <span>Booked</span>
                    <div class="seat-visual seater sold"></div>
                    <div class="seat-visual sleeper sold"></div>
                </div>
                <div class="legend-row">
                    <span>Selected</span>
                    <div class="seat-visual seater selected"></div>
                    <div class="seat-visual sleeper selected"></div>
                </div>
                <div class="legend-row">
                    <span>Booked by Female</span>
                    <div class="seat-visual seater female"></div>
                    <div class="seat-visual sleeper female"></div>
                </div>
                <div class="legend-row">
                    <span>Booked by Male</span>
                    <div class="seat-visual seater male"></div>
                    <div class="seat-visual sleeper male"></div>
                </div>
            </div>

        </div>

        <!-- Right Side: Bus Details -->
        <div class="col-lg-4">
            <div class="bus-details-sidebar">
                <div class="bus-details-card position-relative">
                    <span class="rating-badge">4.8 <i class="bi bi-star-fill"></i></span>
                    <h5>Gola Bus Service</h5>
                    <p class="mb-2">Bharat Benz A/C Seater / Sleeper (2+1)</p>
                    <p>22:30 - 06:30 Sun 28 Sep</p>
                    <div class="row bus-image-gallery mt-3 g-2">
                        <div class="col-4"><img src="https://i.ibb.co/rfnL5j6/bus1.png" alt="bus view"></div>
                        <div class="col-4"><img src="https://i.ibb.co/gR57Xm1/bus2.png" alt="bus view"></div>
                        <div class="col-4"><img src="https://i.ibb.co/mHxhRhg/bus3.png" alt="bus view"></div>
                    </div>
                </div>

                <div class="bus-details-card bus-features-card">
                    <ul class="nav">
                        <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#why-book">Why book?</a></li>
                        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#bus-route">Bus route</a></li>
                        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#boarding">Boarding</a></li>
                        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#amenities">Amenities</a></li>
                    </ul>
                    <div class="tab-content mt-3">
                        <div class="tab-pane fade show active" id="why-book">
                            <div class="feature-item">
                                <div class="d-flex align-items-center gap-3">
                                    <i class="bi bi-person-standing"></i>
                                    <h6>Highly rated by women</h6>
                                </div>
                            </div>
                            <div class="feature-item">
                                <div class="d-flex align-items-center gap-3">
                                    <i class="bi bi-geo-alt"></i>
                                    <div><h6>Live Tracking</h6><p class="mb-0">You can now track your bus and plan your commute to the boarding point.</p></div>
                                </div>
                            </div>
                            <div class="feature-item">
                                <div class="d-flex align-items-center gap-3">
                                    <i class="bi bi-ticket-perforated"></i>
                                    <div><h6>Flexi Ticket</h6><p class="mb-0">Change your travel date for free up to 8 hours before the departure.</p></div>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="bus-route">
                            <p><strong>Delhi → Mathura → Agra → Lucknow</strong></p>
                        </div>
                        <div class="tab-pane fade" id="boarding">
                            <h6>Boarding point</h6>
                            <ul class="timeline">
                                <li class="timeline-item">
                                    <div class="timeline-dot"></div>
                                    <p class="fw-bold mb-0">22:30 <small class="fw-normal">28 Sep</small></p>
                                    <p>Kashmiri gate Metro station</p>
                                </li>
                                <li class="timeline-item">
                                    <div class="timeline-dot"></div>
                                    <p class="fw-bold mb-0">23:00 <small class="fw-normal">28 Sep</small></p>
                                    <p>Gautam buddha gate Chilla border</p>
                                </li>
                            </ul>
                            <h6>Dropping point</h6>
                             <ul class="timeline">
                                <li class="timeline-item">
                                    <div class="timeline-dot"></div>
                                    <p class="fw-bold mb-0">06:30 <small class="fw-normal">29 Sep</small></p>
                                    <p>Neharia Circle</p>
                                </li>
                            </ul>
                             <h6 class="mt-4">Rest stop</h6>
                             <div class="rest-stop-notice">This bus has no rest stop</div>
                        </div>
                        <div class="tab-pane fade" id="amenities">
                            <h6>6 amenities</h6>
                            <div class="row">
                                <div class="col-6 amenity-item"><i class="bi bi-droplet me-2"></i> Water Bottle</div>
                                <div class="col-6 amenity-item"><i class="bi bi-snow me-2"></i> Blankets</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- Fixed Bottom Summary Footer -->
<div class="summary-footer" id="summary-footer">
    <div>
        <span class="summary-price" id="summary-total-price"></span>
        <div class="summary-seats" id="summary-seat-count">No seats selected</div>
    </div>
    <button class="btn btn-primary btn-lg btn-proceed">PROCEED</button>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    // --- Data for Seat Layout (This should come from your server/database) ---
    const seatData = {
        seatPrice: 1599,
        sleeperPrice: 2399,
        lowerDeck: [
            [{type: 'sleeper', status: 'available'}, {type: 'gangway'}, {type: 'seater', status: 'available'}, {type: 'seater', status: 'available'}],
            [{type: 'sleeper', status: 'available'}, {type: 'gangway'}, {type: 'seater', status: 'sold'}, {type: 'seater', status: 'sold'}],
            [{type: 'sleeper', status: 'female'}, {type: 'gangway'}, {type: 'seater', status: 'available'}, {type: 'seater', status: 'available'}],
            [{type: 'sleeper', status: 'available'}, {type: 'gangway'}, {type: 'seater', status: 'available'}, {type: 'seater', status: 'available'}],
        ],
        upperDeck: [
            [{type: 'sleeper', status: 'available'}, {type: 'gangway'}, {type: 'sleeper', status: 'available'}],
            [{type: 'sleeper', status: 'male'}, {type: 'gangway'}, {type: 'sleeper', status: 'available'}],
            [{type: 'sleeper', status: 'available'}, {type: 'gangway'}, {type: 'sleeper', status: 'available'}],
            [{type: 'sleeper', status: 'available'}, {type: 'gangway'}, {type: 'sleeper', status: 'sold'}],
        ]
    };

    function renderSeatLayout(containerId, layout) {
        const container = document.getElementById(containerId);
        container.innerHTML = ''; // Clear previous content

        layout.forEach(row => {
            const rowDiv = document.createElement('div');
            rowDiv.className = 'seat-row';
            row.forEach(seat => {
                if (seat.type === 'gangway') {
                    rowDiv.innerHTML += '<div class="gangway"></div>';
                    return;
                }
                
                const price = seat.type === 'sleeper' ? seatData.sleeperPrice : seatData.seatPrice;
                const isClickable = seat.status === 'available';

                const seatWrapper = document.createElement('div');
                seatWrapper.className = 'seat-wrapper';

                const seatVisual = document.createElement('div');
                seatVisual.className = `seat-visual ${seat.type} ${seat.status}`;
                if (isClickable) {
                    seatVisual.dataset.price = price;
                    seatVisual.addEventListener('click', () => {
                        seatVisual.classList.toggle('selected');
                        updateSummary();
                    });
                }
                
                seatWrapper.appendChild(seatVisual);

                const priceText = document.createElement('span');
                priceText.className = seat.status === 'sold' ? 'sold-text' : 'seat-price';
                priceText.textContent = seat.status === 'sold' ? 'Sold' : `₹${price}`;
                seatWrapper.appendChild(priceText);
                
                rowDiv.appendChild(seatWrapper);
            });
            container.appendChild(rowDiv);
        });
    }

    function updateSummary() {
        const selectedSeats = document.querySelectorAll('.seat-visual.selected');
        const summaryFooter = document.getElementById('summary-footer');
        let totalPrice = 0;

        selectedSeats.forEach(seat => {
            totalPrice += parseFloat(seat.dataset.price);
        });

        if (selectedSeats.length > 0) {
            document.getElementById('summary-total-price').textContent = `₹ ${totalPrice.toLocaleString('en-IN')}`;
            document.getElementById('summary-seat-count').textContent = `${selectedSeats.length} Seat${selectedSeats.length > 1 ? 's' : ''} selected`;
            summaryFooter.classList.add('visible');
        } else {
            document.getElementById('summary-total-price').textContent = '';
            document.getElementById('summary-seat-count').textContent = 'No seats selected';
            summaryFooter.classList.remove('visible');
        }
    }

    // Initial Render
    renderSeatLayout('lower-deck-seats', seatData.lowerDeck);
    renderSeatLayout('upper-deck-seats', seatData.upperDeck);
});
</script>
</body>
</html>
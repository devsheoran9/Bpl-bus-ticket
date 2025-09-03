<?php include 'includes/header.php'; ?>
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        /* Process Header */
        .process-header {
            background: white;
            padding: 1rem 2rem;
            border-bottom: 1px solid #e5e5e5;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08);
        }

        .process-steps {
            display: flex;
            gap: 2rem;
        }

        .process-steps .step {
            padding: 0.5rem 1rem;
            color: #666;
            position: relative;
            font-size: 0.9rem;
            white-space: nowrap;
        }

        .process-steps .step.active {
            color: #dc3545;
            font-weight: 600;
        }

        .process-steps .step.active::after {
            content: '';
            position: absolute;
            bottom: -1rem;
            left: 0;
            right: 0;
            height: 2px;
            background: #dc3545;
        }

        /* Main Container */
        .main-container {
            display: flex;
            gap: 2rem;
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        /* Left Side - Seat Selection */
        .seat-selection-panel {
            flex: 1;
            background: white;
            border-radius: 8px;
            padding: 2rem;
            height: fit-content;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        /* Deck Headers */
        .deck-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #e5e5e5;
        }

        .deck-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #333;
        }

        .steering-wheel {
            font-size: 1.5rem;
            color: #666;
        }

        /* Seat Grid Layout */
        .seat-grid {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            align-items: center;
            margin-bottom: 3rem;
        }

        .seat-row {
            display: flex;
            gap: 0.5rem;
            align-items: center;
        }

        .seat-group {
            display: flex;
            gap: 0.5rem;
        }

        .aisle {
            width: 3rem;
        }

        /* Individual Seats */
        .seat-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.25rem;
        }

        .seat {
            width: 35px;
            height: 45px;
            border: 2px solid #28a745;
            border-radius: 4px;
            background: white;
            cursor: pointer;
            transition: all 0.2s ease;
            position: relative;
        }

        .sleeper {
            width: 35px;
            height: 70px;
            border: 2px solid #28a745;
            border-radius: 6px;
            background: white;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        /* Seat States */
        .seat.available,
        .sleeper.available {
            border-color: #28a745;
            background: white;
        }

        .seat.booked,
        .sleeper.booked {
            border-color: #ddd;
            background: #f0f0f0;
            cursor: not-allowed;
        }

        .seat.male,
        .sleeper.male {
            border-color: #007bff;
            background: #e3f2fd;
        }

        .seat.female,
        .sleeper.female {
            border-color: #e91e63;
            background: #fce4ec;
        }

        .seat.selected,
        .sleeper.selected {
            border-color: #28a745;
            background: #28a745;
        }

        /* Seat Icons */
        .seat-icon {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 0.8rem;
            color: inherit;
        }

        .seat.male .seat-icon {
            color: #007bff;
        }

        .seat.female .seat-icon {
            color: #e91e63;
        }

        .seat.selected .seat-icon {
            color: white;
        }

        /* Seat Prices */
        .seat-price {
            font-size: 0.7rem;
            font-weight: 600;
            color: #28a745;
            text-align: center;
        }

        .seat.booked+.seat-price {
            color: #999;
        }

        /* Seat Legend */
        .seat-legend {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 1.5rem;
            margin-top: 2rem;
        }

        .legend-title {
            font-weight: 600;
            font-size: 1rem;
            margin-bottom: 1rem;
            color: #333;
        }

        .legend-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 0.85rem;
        }

        .legend-seat {
            width: 20px;
            height: 25px;
            border: 2px solid;
            border-radius: 3px;
            flex-shrink: 0;
        }

        .legend-seat.available {
            border-color: #28a745;
            background: white;
        }

        .legend-seat.booked {
            border-color: #ddd;
            background: #f0f0f0;
        }

        .legend-seat.male {
            border-color: #007bff;
            background: #e3f2fd;
        }

        .legend-seat.female {
            border-color: #e91e63;
            background: #fce4ec;
        }

        .legend-seat.selected {
            border-color: #28a745;
            background: #28a745;
        }

        /* Right Side - Bus Details */
        .bus-details-panel {
            width: 400px;
            flex-shrink: 0;
        }

        .bus-info-card {
            background: white;
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .bus-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1rem;
        }

        .bus-name {
            font-size: 1.1rem;
            font-weight: 600;
            color: #333;
            margin: 0;
        }

        .bus-type {
            color: #666;
            font-size: 0.85rem;
            margin-top: 0.25rem;
        }

        .rating-badge {
            background: #28a745;
            color: white;
            padding: 0.4rem 0.8rem;
            border-radius: 6px;
            font-size: 0.8rem;
            font-weight: 600;
            text-align: center;
        }

        .rating-count {
            font-size: 0.7rem;
            opacity: 0.9;
            font-weight: normal;
        }

        /* Tabs */
        .custom-tabs {
            border-bottom: 2px solid #f0f0f0;
            margin-bottom: 1rem;
        }

        .custom-tabs .nav-link {
            border: none;
            color: #666;
            padding: 0.75rem 1rem;
            font-weight: 500;
            border-bottom: 2px solid transparent;
            font-size: 0.9rem;
            background: none;
        }

        .custom-tabs .nav-link.active {
            color: #dc3545;
            border-bottom-color: #dc3545;
        }

        /* Policies Section */
        .policies-section h6 {
            font-weight: 600;
            color: #333;
            margin-bottom: 1rem;
        }

        .policy-item {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            margin-bottom: 1.5rem;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 8px;
        }

        .policy-icon {
            color: #28a745;
            font-size: 1.2rem;
            margin-top: 0.25rem;
        }

        .policy-content h6 {
            font-size: 0.9rem;
            font-weight: 600;
            color: #333;
            margin: 0 0 0.5rem 0;
        }

        .policy-content p {
            font-size: 0.8rem;
            color: #666;
            margin: 0;
            line-height: 1.4;
        }

        /* Bottom Summary Bar */
        .summary-bar {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: white;
            border-top: 1px solid #e5e5e5;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transform: translateY(100%);
            transition: transform 0.3s ease;
            z-index: 1000;
            box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.1);
        }

        .summary-bar.visible {
            transform: translateY(0);
        }

        .summary-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .total-price {
            font-size: 1.5rem;
            font-weight: 700;
            color: #333;
        }

        .seat-count {
            color: #666;
            font-size: 0.9rem;
        }

        .proceed-btn {
            background: #dc3545;
            border-color: #dc3545;
            padding: 0.75rem 2rem;
            font-weight: 600;
            border-radius: 6px;
            font-size: 0.9rem;
            color: white;
            border: none;
            cursor: pointer;
            transition: background 0.2s;
        }

        .proceed-btn:hover:not(:disabled) {
            background: #c82333;
        }

        .proceed-btn:disabled {
            background: #ccc;
            cursor: not-allowed;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .main-container {
                flex-direction: column;
                padding: 0 0.5rem;
            }

            .bus-details-panel {
                width: 100%;
            }

            .seat-selection-panel {
                padding: 1rem;
            }
        }
    </style>
 

<body>
    <!-- Process Header -->
    <div class="process-header">
        <div class="d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <a href="bus_list.php" class="text-dark me-3"><i class="bi bi-arrow-left"></i></a>
                <strong>Delhi to Lucknow</strong>
            </div>
            <div class="process-steps">
                <div class="step active">1. Select seats</div>
                <div class="step">2. Board/Drop point</div>
                <div class="step">3. Passenger info</div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-container">
        <!-- Left Side - Seat Selection -->
        <div class="seat-selection-panel">
            <!-- Lower Deck -->
            <div class="deck-header">
                <span class="deck-title">Lower deck</span>
                <i class="bi bi-circle steering-wheel"></i>
            </div>

            <div class="seat-grid">
                <div class="seat-row">
                    <div class="seat-group">
                        <div class="seat-item">
                            <div class="seat available" data-price="1299">
                                <i class="bi bi-person-fill seat-icon"></i>
                            </div>
                            <div class="seat-price">₹1299</div>
                        </div>
                        <div class="seat-item">
                            <div class="seat available" data-price="1299">
                                <i class="bi bi-person-fill seat-icon"></i>
                            </div>
                            <div class="seat-price">₹1299</div>
                        </div>
                    </div>
                    <div class="aisle"></div>
                    <div class="seat-group">
                        <div class="seat-item">
                            <div class="sleeper available" data-price="1899">
                                <i class="bi bi-person-fill seat-icon"></i>
                            </div>
                            <div class="seat-price">₹1899</div>
                        </div>
                        <div class="seat-item">
                            <div class="seat booked">
                                <i class="bi bi-person-fill seat-icon"></i>
                            </div>
                            <div class="seat-price"></div>
                        </div>
                    </div>
                </div>

                <div class="seat-row">
                    <div class="seat-group">
                        <div class="seat-item">
                            <div class="seat available" data-price="1299">
                                <i class="bi bi-person-fill seat-icon"></i>
                            </div>
                            <div class="seat-price">₹1299</div>
                        </div>
                        <div class="seat-item">
                            <div class="seat available" data-price="1299">
                                <i class="bi bi-person-fill seat-icon"></i>
                            </div>
                            <div class="seat-price">₹1299</div>
                        </div>
                    </div>
                    <div class="aisle"></div>
                    <div class="seat-group">
                        <div class="seat-item">
                            <div class="sleeper available" data-price="1899">
                                <i class="bi bi-person-fill seat-icon"></i>
                            </div>
                            <div class="seat-price">₹1899</div>
                        </div>
                        <div class="seat-item">
                            <div class="sleeper available" data-price="1899">
                                <i class="bi bi-person-fill seat-icon"></i>
                            </div>
                            <div class="seat-price">₹1899</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Upper Deck -->
            <div class="deck-header">
                <span class="deck-title">Upper deck</span>
            </div>

            <div class="seat-grid">
                <div class="seat-row">
                    <div class="seat-group">
                        <div class="seat-item">
                            <div class="seat female" data-price="1299">
                                <i class="bi bi-person-dress seat-icon"></i>
                            </div>
                            <div class="seat-price">₹1299</div>
                        </div>
                        <div class="seat-item">
                            <div class="seat female" data-price="1299">
                                <i class="bi bi-person-dress seat-icon"></i>
                            </div>
                            <div class="seat-price">₹1299</div>
                        </div>
                    </div>
                    <div class="aisle"></div>
                    <div class="seat-group">
                        <div class="seat-item">
                            <div class="sleeper available" data-price="1899">
                                <i class="bi bi-person-fill seat-icon"></i>
                            </div>
                            <div class="seat-price">₹1899</div>
                        </div>
                        <div class="seat-item">
                            <div class="sleeper male" data-price="1899">
                                <i class="bi bi-person-standing seat-icon"></i>
                            </div>
                            <div class="seat-price">₹1899</div>
                        </div>
                    </div>
                </div>

                <div class="seat-row">
                    <div class="seat-group">
                        <div class="seat-item">
                            <div class="seat available" data-price="1299">
                                <i class="bi bi-person-fill seat-icon"></i>
                            </div>
                            <div class="seat-price">₹1299</div>
                        </div>
                        <div class="seat-item">
                            <div class="seat booked">
                                <i class="bi bi-person-fill seat-icon"></i>
                            </div>
                            <div class="seat-price"></div>
                        </div>
                    </div>
                    <div class="aisle"></div>
                    <div class="seat-group">
                        <div class="seat-item">
                            <div class="seat booked">
                                <i class="bi bi-person-fill seat-icon"></i>
                            </div>
                            <div class="seat-price"></div>
                        </div>
                        <div class="seat-item">
                            <div class="seat available" data-price="1299">
                                <i class="bi bi-person-fill seat-icon"></i>
                            </div>
                            <div class="seat-price">₹1299</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Seat Legend -->
            <div class="seat-legend">
                <div class="legend-title">Know your seat types</div>
                <div class="legend-grid">
                    <div class="legend-item">
                        <div class="legend-seat available"></div>
                        <span>Available only for male passenger</span>
                    </div>
                    <div class="legend-item">
                        <div class="legend-seat booked"></div>
                        <span>Already booked</span>
                    </div>
                    <div class="legend-item">
                        <div class="legend-seat selected"></div>
                        <span>Selected by you</span>
                    </div>
                    <div class="legend-item">
                        <div class="legend-seat female"></div>
                        <span>Available only for female passenger</span>
                    </div>
                    <div class="legend-item">
                        <div class="legend-seat booked"></div>
                        <span>Booked by female passenger</span>
                    </div>
                    <div class="legend-item">
                        <div class="legend-seat booked"></div>
                        <span>Booked by male passenger</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Side - Bus Details -->
        <div class="bus-details-panel">
            <div class="bus-info-card">
                <div class="bus-header">
                    <div>
                        <div class="bus-name">Gola Bus Service</div>
                        <div class="bus-type">22:30 - 06:30 • Wed 03 Sep<br>Bharat Benz A/C Seater/Sleeper (2+1)</div>
                    </div>
                    <div class="rating-badge">
                        <div><i class="bi bi-star-fill"></i> 4.8</div>
                        <div class="rating-count">1851</div>
                    </div>
                </div>

                <!-- Tabs -->
                <ul class="nav custom-tabs" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#why-book">Why book this bus?</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#bus-route">Bus route</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#boarding-point">Boarding point</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#dropping-point">Dropping point</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#rest-stop">Rest stop</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#amenities">Amenities</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#booking-policy">Booking policy</button>
                    </li>
                </ul>

                <div class="tab-content">
                    <div class="tab-pane fade show active" id="why-book">
                        <div class="policies-section">
                            <div class="policy-item">
                                <i class="bi bi-people policy-icon"></i>
                                <div class="policy-content">
                                    <h6>Women Travelling</h6>
                                </div>
                            </div>
                            <div class="policy-item">
                                <i class="bi bi-person-check policy-icon"></i>
                                <div class="policy-content">
                                    <h6>Highly rated by women</h6>
                                </div>
                            </div>
                            <div class="policy-item">
                                <i class="bi bi-geo-alt policy-icon"></i>
                                <div class="policy-content">
                                    <h6>Live Tracking</h6>
                                    <p>You can now track your bus and plan your commute to the boarding point.</p>
                                </div>
                            </div>
                            <div class="policy-item">
                                <i class="bi bi-ticket policy-icon"></i>
                                <div class="policy-content">
                                    <h6>Flexi Ticket</h6>
                                    <p>Change your travel date for free up to 8 hours before the departure. Get...</p>
                                </div>
                            </div>
                        </div>

                        <h6>Bus route</h6>
                        <p class="text-muted small">5 hr 0 min</p>
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <span class="fw-bold">Delhi</span>
                            <i class="bi bi-arrow-right"></i>
                            <span>Mathura</span>
                            <i class="bi bi-arrow-right"></i>
                            <span>Agra</span>
                            <i class="bi bi-arrow-right"></i>
                            <span class="fw-bold">Lucknow</span>
                        </div>

                        <div class="policies-section">
                            <h6>Other Policies</h6>
                            <div class="policy-item">
                                <i class="bi bi-person-plus policy-icon"></i>
                                <div class="policy-content">
                                    <h6>Child passenger policy</h6>
                                    <p>Children above the age of 6 will need a ticket</p>
                                </div>
                            </div>
                            <div class="policy-item">
                                <i class="bi bi-bag policy-icon"></i>
                                <div class="policy-content">
                                    <h6>Luggage policy</h6>
                                    <p>2 pieces of luggage will be accepted free of charge per passenger. Excess items will be chargeable</p>
                                    <p>Excess baggage over 10 kgs per passenger will be chargeable</p>
                                </div>
                            </div>
                            <div class="policy-item">
                                <i class="bi bi-heart policy-icon"></i>
                                <div class="policy-content">
                                    <h6>Pets Policy</h6>
                                    <p>Pets are not allowed</p>
                                </div>
                            </div>
                            <div class="policy-item">
                                <i class="bi bi-cup policy-icon"></i>
                                <div class="policy-content">
                                    <h6>Liquor Policy</h6>
                                    <p>Carrying or consuming liquor inside the bus is prohibited. Bus operator reserves the right to deboard drunk passengers.</p>
                                </div>
                            </div>
                            <div class="policy-item">
                                <i class="bi bi-clock policy-icon"></i>
                                <div class="policy-content">
                                    <h6>Pick up time policy</h6>
                                    <p>Bus operator is not obligated to wait beyond the scheduled departure time of the bus. No refund request will be entertained for late arriving passengers.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bottom Summary Bar -->
    <div class="summary-bar" id="summaryBar">
        <div class="summary-info">
            <span class="total-price" id="totalPrice">₹0</span>
            <span class="seat-count" id="seatCount">0 seats</span>
        </div>
        <button class="proceed-btn" id="proceedBtn" disabled>Select boarding & dropping points</button>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const availableSeats = document.querySelectorAll('.seat.available, .sleeper.available, .seat.female, .sleeper.female, .seat.male, .sleeper.male');
            const summaryBar = document.getElementById('summaryBar');
            const totalPriceEl = document.getElementById('totalPrice');
            const seatCountEl = document.getElementById('seatCount');
            const proceedBtn = document.getElementById('proceedBtn');

            availableSeats.forEach(seat => {
                seat.addEventListener('click', function() {
                    if (!seat.classList.contains('booked')) {
                        seat.classList.toggle('selected');
                        updateSummary();
                    }
                });
            });

            function updateSummary() {
                const selectedSeats = document.querySelectorAll('.seat.selected, .sleeper.selected');
                const numSeats = selectedSeats.length;

                const totalPrice = Array.from(selectedSeats).reduce((total, seat) => {
                    return total + parseInt(seat.dataset.price || 0, 10);
                }, 0);

                if (numSeats > 0) {
                    totalPriceEl.textContent = `₹${totalPrice.toLocaleString()}`;
                    seatCountEl.textContent = `${numSeats} seats`;
                    proceedBtn.disabled = false;
                    summaryBar.classList.add('visible');
                } else {
                    proceedBtn.disabled = true;
                    summaryBar.classList.remove('visible');
                }
            }

            // Tab functionality
            document.querySelectorAll('[data-bs-toggle="tab"]').forEach(tab => {
                tab.addEventListener('click', function(e) {
                    e.preventDefault();

                    // Remove active from all tabs and content
                    document.querySelectorAll('.nav-link').forEach(link => link.classList.remove('active'));
                    document.querySelectorAll('.tab-pane').forEach(pane => {
                        pane.classList.remove('show', 'active');
                    });

                    // Add active to clicked tab
                    this.classList.add('active');

                    // Show corresponding content
                    const targetId = this.getAttribute('data-bs-target');
                    const targetPane = document.querySelector(targetId);
                    if (targetPane) {
                        targetPane.classList.add('show', 'active');
                    }
                });
            });
        });
    </script>
</body>

</html>
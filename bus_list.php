<?php include 'includes/header.php'; ?>

<main class="container my-5">
    <div class="row">
        <!-- Filter Section -->
        <aside class="col-lg-3">
            <div class="filter-section">
                <h4>Filter by</h4>
                <div>
                    <h6>Bus Type</h6>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="" id="acCheck" checked>
                        <label class="form-check-label" for="acCheck">AC (313)</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="" id="sleeperCheck" checked>
                        <label class="form-check-label" for="sleeperCheck">Sleeper (283)</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="" id="volvoCheck">
                        <label class="form-check-label" for="volvoCheck">Volvo Buses (12)</label>
                    </div>
                </div>

                <div>
                    <h6>Departure Time</h6>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="" id="earlyMorningCheck">
                        <label class="form-check-label" for="earlyMorningCheck">06:00 - 12:00 (28)</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="" id="nightCheck" checked>
                        <label class="form-check-label" for="nightCheck">18:00 - 24:00 (244)</label>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Bus Listings -->
        <section class="col-lg-9">
            <h3 class="mb-4">Showing buses from <strong><?php echo htmlspecialchars($_GET['from'] ?? 'Delhi'); ?></strong> to <strong><?php echo htmlspecialchars($_GET['to'] ?? 'Lucknow'); ?></strong></h3>

            <!-- Bus Item 1 -->
            <div class="bus-item">
                <div class="bus-item-summary">
                    <div class="bus-info">
                        <h5>Gola Bus Service</h5>
                        <p>Bharat Benz A/C Seater / Sleeper (2+1)</p>
                        <div class="rating-badge">4.8 <i class="bi bi-star-fill"></i></div>
                    </div>
                    <div class="bus-timing">
                        <div class="time">22:30 &rarr; 06:30</div>
                        <div class="duration">8h 40m</div>
                    </div>
                    <div class="price-section">
                        <div class="price-label">Starts from</div>
                        <div class="price">₹1,599</div>
                        <a href="select_seats.php?from=Delhi&to=Lucknow" class="btn btn-primary btn-sm">View Seats</a>
                    </div>
                </div>
            </div>

            <!-- Bus Item 2 -->
            <div class="bus-item">
                <div class="bus-item-summary">
                    <div class="bus-info">
                        <h5>Laxmi Holidays</h5>
                        <p>Bharat Benz A/C Sleeper (2+1)</p>
                        <div class="rating-badge">4.7 <i class="bi bi-star-fill"></i></div>
                    </div>
                    <div class="bus-timing">
                        <div class="time">23:30 &rarr; 07:30</div>
                        <div class="duration">8h 48m</div>
                    </div>
                    <div class="price-section">
                        <div class="price-label">Starts from</div>
                        <div class="price">₹735</div>
                        <a href="select_seats.php?from=Delhi&to=Lucknow" class="btn btn-primary btn-sm">View Seats</a>
                    </div>
                </div>
            </div>

            <!-- Bus Item 3 -->
            <div class="bus-item">
                <div class="bus-item-summary">
                    <div class="bus-info">
                        <h5>PTC-SkyBus</h5>
                        <p>Volvo Multi-Axle A/C Sleeper (2+1)</p>
                        <div class="rating-badge">4.7 <i class="bi bi-star-fill"></i></div>
                    </div>
                    <div class="bus-timing">
                        <div class="time">23:15 &rarr; 07:30</div>
                        <div class="duration">8h 15m</div>
                    </div>
                    <div class="price-section">
                        <div class="price-label">Starts from</div>
                        <div class="price">₹1,869</div>
                        <a href="select_seats.php?from=Delhi&to=Lucknow" class="btn btn-primary btn-sm">View Seats</a>
                    </div>
                </div>
            </div>

            <!-- Bus Item 4 -->
            <div class="bus-item">
                <div class="bus-item-summary">
                    <div class="bus-info">
                        <h5>IntrCity SmartBus</h5>
                        <p>A/C Seater / Sleeper (2+1)</p>
                        <div class="rating-badge">4.5 <i class="bi bi-star-fill"></i></div>
                    </div>
                    <div class="bus-timing">
                        <div class="time">21:00 &rarr; 05:45</div>
                        <div class="duration">8h 45m</div>
                    </div>
                    <div class="price-section">
                        <div class="price-label">Starts from</div>
                        <div class="price">₹1,250</div>
                        <a href="select_seats.php?from=Delhi&to=Lucknow" class="btn btn-primary btn-sm">View Seats</a>
                    </div>
                </div>
            </div>

        </section>
    </div>
</main>

<?php include 'includes/footer.php'; ?>
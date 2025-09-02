<?php include 'includes/header.php'; ?>

<main class="container my-5">
    <div class="row">
        <!-- Filter Section -->
        <aside class="col-lg-3">
            <div class="filter-section">
                <h4>Filter buses</h4>
                <hr>
                <div>
                    <h6>Bus Type</h6>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="" id="acCheck">
                        <label class="form-check-label" for="acCheck">AC (313)</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="" id="sleeperCheck">
                        <label class="form-check-label" for="sleeperCheck">Sleeper (283)</label>
                    </div>
                     <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="" id="volvoCheck">
                        <label class="form-check-label" for="volvoCheck">Volvo Buses (12)</label>
                    </div>
                </div>
                <hr>
                <div>
                    <h6>Departure Time</h6>
                     <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="" id="earlyMorningCheck">
                        <label class="form-check-label" for="earlyMorningCheck">06:00 - 12:00 (28)</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="" id="nightCheck">
                        <label class="form-check-label" for="nightCheck">18:00 - 24:00 (244)</label>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Bus Listings -->
        <section class="col-lg-9">
            <h3 class="mb-4">Showing buses from <?php echo htmlspecialchars($_GET['from'] ?? 'Delhi'); ?> to <?php echo htmlspecialchars($_GET['to'] ?? 'Lucknow'); ?></h3>
            
            <!-- Dummy Bus Item 1 -->
            <div class="bus-item">
                <div class="bus-item-summary">
                    <div class="bus-info">
                        <h5>Laxmi Holidays <span class="rating-badge">4.7 <i class="bi bi-star-fill"></i></span></h5>
                        <p class="text-muted mb-2">Bharat Benz A/C Seater / Sleeper (2+1) | 32 Seats Left</p>
                        <p class="mb-1"><strong>23:30, Delhi</strong> <i class="bi bi-arrow-right"></i> <strong>07:30, Lucknow</strong> <small class="text-muted">(8h 0m)</small></p>
                        <ul class="amenities-list">
                            <li><i class="bi bi-camera-video"></i> CCTV</li>
                            <li><i class="bi bi-geo-alt"></i> Live Tracking</li>
                            <li><i class="bi bi-plug"></i> Charging Point</li>
                        </ul>
                    </div>
                    <div class="price-section">
                        <h4>₹ 1,599</h4>
                        <p>per seat</p>
                        <a class="btn btn-primary" data-bs-toggle="collapse" href="#bus-details-1" role="button" aria-expanded="false" aria-controls="bus-details-1">
                            View Seats
                        </a>
                    </div>
                </div>

                <!-- Collapsible Seat Selection Details -->
                <div class="collapse" id="bus-details-1">
                    <?php include('seat_selection_template.php'); ?>
                </div>
            </div>
            
            <!-- Dummy Bus Item 2 (You can copy and paste the above block and change IDs like 'bus-details-2' for more buses) -->
            <!-- ... more bus items ... -->

        </section>
    </div>
</main>

<!-- Bottom Summary Bar (Initially hidden) -->
<div id="summary-bar">
    <div class="summary-info">
        <span id="seat-count">0</span> Seats selected | Total: <span id="total-price">₹0</span>
    </div>
    <button class="btn btn-primary">Select Boarding & Dropping points</button>
</div>


<?php include 'includes/footer.php'; ?>

<!-- Add JavaScript at the end of the body -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const seatPrice = 1599; // Dummy price per seat
    const summaryBar = document.getElementById('summary-bar');
    const seatCountEl = document.getElementById('seat-count');
    const totalPriceEl = document.getElementById('total-price');
    
    // Use event delegation to handle clicks on all seats
    document.body.addEventListener('click', function(e) {
        if (e.target.matches('.seat.available, .sleeper.available')) {
            e.target.classList.toggle('selected');
            updateSummary();
        }
    });

    function updateSummary() {
        // Find selected seats within the currently expanded bus item
        const activeCollapse = document.querySelector('.collapse.show');
        if (!activeCollapse) return; // Do nothing if no seat section is open

        const selectedSeats = activeCollapse.querySelectorAll('.selected');
        const count = selectedSeats.length;
        const totalPrice = count * seatPrice;

        seatCountEl.textContent = count;
        totalPriceEl.textContent = `₹${totalPrice.toLocaleString('en-IN')}`;

        if (count > 0) {
            summaryBar.classList.add('visible');
        } else {
            summaryBar.classList.remove('visible');
        }
    }

    // Reset summary when a new bus item is expanded
    const collapseElements = document.querySelectorAll('.collapse');
    collapseElements.forEach(el => {
        el.addEventListener('show.bs.collapse', () => {
            // Unselect all seats everywhere
            document.querySelectorAll('.seat.selected, .sleeper.selected').forEach(seat => {
                seat.classList.remove('selected');
            });
            // Reset and hide the summary bar
            updateSummary();
        });
    });
});
</script>
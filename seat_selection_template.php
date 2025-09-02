<div class="seat-selection-details">
    <div class="row">
        <!-- Left Side: Seat Layout -->
        <div class="col-lg-5">
            <div class="seat-layout-container">
                <!-- Lower Deck -->
                <div class="seat-deck">
                    <div class="deck-label">Lower deck</div>
                    <i class="bi bi-steering-wheel steering-wheel"></i>
                    <!-- Dummy Rows -->
                    <div class="seat-row">
                        <div class="seat-visual sleeper available"></div>
                        <div class="gangway"></div>
                        <div class="seat-visual seater available"></div>
                        <div class="seat-visual seater available"></div>
                    </div>
                    <div class="seat-row">
                        <div class="seat-visual sleeper booked"></div>
                        <div class="gangway"></div>
                        <div class="seat-visual seater available"></div>
                        <div class="seat-visual seater booked"></div>
                    </div>
                    <div class="seat-row">
                        <div class="seat-visual sleeper available"></div>
                        <div class="gangway"></div>
                        <div class="seat-visual seater available"></div>
                        <div class="seat-visual seater available"></div>
                    </div>
                </div>
                <!-- Upper Deck -->
                <div class="seat-deck">
                    <div class="deck-label">Upper deck</div>
                    <div class="seat-row">
                        <div class="seat-visual sleeper available"></div>
                        <div class="gangway"></div>
                        <div class="seat-visual sleeper booked"></div>
                    </div>
                    <div class="seat-row">
                        <div class="seat-visual sleeper booked"></div>
                        <div class="gangway"></div>
                        <div class="seat-visual sleeper available"></div>
                    </div>
                    <div class="seat-row">
                        <div class="seat-visual sleeper available"></div>
                        <div class="gangway"></div>
                        <div class="seat-visual sleeper available"></div>
                    </div>
                </div>
            </div>
            <div class="seat-legend mt-3">
                <div>
                    <div class="seat-box seater"></div> Available
                </div>
                <div>
                    <div class="seat-box" style="background-color: #e5e7eb;"></div> Booked
                </div>
                <div>
                    <div class="seat-box" style="background-color: var(--success-color);"></div> Selected
                </div>
            </div>
        </div>

        <!-- Right Side: Bus Details Tabs -->
        <div class="col-lg-7 mt-4 mt-lg-0">
            <ul class="nav nav-tabs bus-details-tabs" id="busTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#reviews-tab-content" type="button">Rating & reviews</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#amenities-tab-content" type="button">Amenities</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#rest-stop-tab-content" type="button">Rest stop</button>
                </li>
            </ul>
            <div class="tab-content pt-3" id="busTabContent">
                <div class="tab-pane fade show active" id="reviews-tab-content" role="tabpanel">
                    <span class="rating-badge fs-5">4.7 <i class="bi bi-star-fill"></i></span>
                    <p class="d-inline-block ms-2 text-muted">Based on 1599 Ratings</p>
                    <hr>
                    <p>Reviews content would go here.</p>
                </div>
                <div class="tab-pane fade" id="amenities-tab-content" role="tabpanel">
                    <h6>5 amenities</h6>
                    <div class="row">
                        <div class="col-md-6 amenity-item"><i class="bi bi-droplet-fill"></i> Water Bottle</div>
                        <div class="col-md-6 amenity-item"><i class="bi bi-snow"></i> Blankets</div>
                        <div class="col-md-6 amenity-item"><i class="bi bi-plug-fill"></i> Charging Point</div>
                        <div class="col-md-6 amenity-item"><i class="bi bi-lightbulb-fill"></i> Reading Light</div>
                        <div class="col-md-6 amenity-item"><i class="bi bi-camera-video-fill"></i> CCTV</div>
                    </div>
                </div>
                <div class="tab-pane fade" id="rest-stop-tab-content" role="tabpanel">
                    <p><strong>Rest Stop Info</strong></p>
                    <div class="no-rest-stop">
                        This bus has no rest stop
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
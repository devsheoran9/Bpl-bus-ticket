document.addEventListener("DOMContentLoaded", function () {
  // Views
  const seatSelectionView = document.getElementById("seat-selection-view");
  const boardingPointView = document.getElementById("boarding-point-view");
  const passengerInfoView = document.getElementById("passenger-info-view");

  // Process Steps
  const processSteps = document.querySelectorAll(".process-steps-v2 li");

  // Buttons
  const proceedToBoardingBtn = document.getElementById("proceedToBoardingBtn");
  const proceedToPassengerBtn = document.getElementById(
    "proceedToPassengerBtn"
  );

  // Step 1 Elements
  const selectableSeats = document.querySelectorAll(
    ".seat:not(.sold), .sleeper:not(.sold)"
  );
  const totalPriceEl = document.getElementById("totalPrice");
  const seatCountEl = document.getElementById("seatCount");

  // Step 2 Elements
  const boardingRadios = document.querySelectorAll(
    'input[name="boarding_point"]'
  );
  const fixedDroppingPointLocationEl = document.getElementById(
    "fixed-dropping-point-location"
  );
  const boardingSummaryPrice = document.getElementById(
    "boarding-summary-price"
  );
  const boardingSummarySeats = document.getElementById(
    "boarding-summary-seats"
  );

  // Step 3 Elements
  const passengerContainer = document.getElementById(
    "passenger-details-container"
  );
  const summaryBoardingLocation = document.getElementById(
    "summary-boarding-location"
  );
  const summaryDroppingLocation = document.getElementById(
    "summary-dropping-location"
  );
  const summarySeatNumbers = document.getElementById("summary-seat-numbers");

  // --- State Management ---
  let selectedSeatsData = [];
  let totalPrice = 0;
  let selectedBoardingPoint = null;
  let selectedDroppingPoint = fixedDroppingPointLocationEl.innerText; // Get the fixed value once

  // --- View Controller ---
  function updateView(stepNumber) {
    seatSelectionView.classList.add("d-none");
    boardingPointView.classList.add("d-none");
    passengerInfoView.classList.add("d-none");

    if (stepNumber === 1) seatSelectionView.classList.remove("d-none");
    if (stepNumber === 2) boardingPointView.classList.remove("d-none");
    if (stepNumber === 3) passengerInfoView.classList.remove("d-none");

    processSteps.forEach((step) => {
      step.classList.toggle(
        "active",
        parseInt(step.dataset.step) === stepNumber
      );
    });
  }

  // --- STEP 1 LOGIC: Seat Selection ---
  function updateSeatSummary() {
    selectedSeatsData = [];
    totalPrice = 0;
    const selectedElements = document.querySelectorAll(
      ".seat.selected, .sleeper.selected"
    );

    selectedElements.forEach((seat) => {
      selectedSeatsData.push({
        name: seat.dataset.seatName || "N/A",
        price: parseInt(seat.dataset.price || 0, 10),
      });
      totalPrice += parseInt(seat.dataset.price || 0, 10);
    });

    const numSeats = selectedSeatsData.length;
    totalPriceEl.textContent = `₹${totalPrice.toLocaleString()}`;
    seatCountEl.textContent = `${numSeats} seat${
      numSeats !== 1 ? "s" : ""
    } selected`;
    proceedToBoardingBtn.disabled = numSeats === 0;
  }

  selectableSeats.forEach((seat) => {
    seat.addEventListener("click", () => {
      seat.classList.toggle("selected");
      updateSeatSummary();
    });
  });

  proceedToBoardingBtn.addEventListener("click", () => {
    boardingSummaryPrice.textContent = `₹${totalPrice.toLocaleString()}`;
    boardingSummarySeats.textContent = `${selectedSeatsData.length} seat${
      selectedSeatsData.length !== 1 ? "s" : ""
    }`;
    updateView(2);
  });

  // --- STEP 2 LOGIC: Boarding Point ---
  function checkBoardingSelection() {
    const boardingSelected = document.querySelector(
      'input[name="boarding_point"]:checked'
    );
    if (boardingSelected) {
      proceedToPassengerBtn.disabled = false;
      selectedBoardingPoint = boardingSelected.value;
    } else {
      proceedToPassengerBtn.disabled = true;
    }
  }

  boardingRadios.forEach((radio) =>
    radio.addEventListener("change", checkBoardingSelection)
  );

  // --- STEP 3 LOGIC: Passenger Info ---
  function generatePassengerForms() {
    passengerContainer.innerHTML = ""; // Clear previous forms
    selectedSeatsData.forEach((seat, index) => {
      const passengerIndex = index + 1;
      const formHTML = `
                <div class="passenger-item mb-3">
                    <h6>Passenger ${passengerIndex} <span class="badge bg-secondary">${seat.name}</span></h6>
                    <div class="row g-3">
                        <div class="col-md-6"><input type="text" class="form-control" placeholder="Name" required></div>
                        <div class="col-md-6"><input type="number" class="form-control" placeholder="Age" required></div>
                        <div class="col-12">
                            <div class="gender-selection">
                                <input type="radio" id="male${passengerIndex}" name="gender${passengerIndex}" class="btn-check" required><label class="btn btn-outline-secondary" for="male${passengerIndex}">Male</label>
                                <input type="radio" id="female${passengerIndex}" name="gender${passengerIndex}" class="btn-check"><label class="btn btn-outline-secondary" for="female${passengerIndex}">Female</label>
                            </div>
                        </div>
                    </div>
                </div>
            `;
      passengerContainer.insertAdjacentHTML("beforeend", formHTML);
    });
  }

  proceedToPassengerBtn.addEventListener("click", () => {
    summaryBoardingLocation.textContent = selectedBoardingPoint;
    summaryDroppingLocation.textContent = selectedDroppingPoint;
    summarySeatNumbers.textContent = selectedSeatsData
      .map((s) => s.name)
      .join(", ");

    generatePassengerForms();
    updateView(3);
  });

  // --- CLICKABLE STEP NAVIGATION ---
  processSteps.forEach((step) => {
    step.addEventListener("click", () => {
      const targetStep = parseInt(step.dataset.step);

      // Allow navigation only if the step is accessible
      if (targetStep === 1) {
        updateView(1);
      } else if (targetStep === 2 && selectedSeatsData.length > 0) {
        updateView(2);
      } else if (
        targetStep === 3 &&
        selectedSeatsData.length > 0 &&
        selectedBoardingPoint
      ) {
        // Regenerate forms in case seat selection changed
        generatePassengerForms();
        updateView(3);
      }
    });
  });

  // --- Initialize ---
  updateView(1); // Start at step 1
});

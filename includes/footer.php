 


    <footer class="bg-dark text-white pt-5 pb-4">
        <div class="container text-center text-md-left">
            <div class="row text-center text-md-left">
                <div class="col-md-3 col-lg-3 col-xl-3 mx-auto mt-3">
                    <h5 class="text-uppercase mb-4 font-weight-bold text-primary">BusGo</h5>
                    <p>Your trusted partner for booking bus tickets online. We aim to provide a seamless, safe, and happy booking experience for all our customers.</p>
                </div>

                <div class="col-md-2 col-lg-2 col-xl-2 mx-auto mt-3">
                    <h5 class="text-uppercase mb-4 font-weight-bold">Routes</h5>
                    <p><a href="#" class="text-white" style="text-decoration: none;">Delhi - Lucknow</a></p>
                    <p><a href="#" class="text-white" style="text-decoration: none;">Mumbai - Pune</a></p>
                    <p><a href="#" class="text-white" style="text-decoration: none;">Bangalore - Chennai</a></p>
                </div>

                <div class="col-md-3 col-lg-2 col-xl-2 mx-auto mt-3">
                    <h5 class="text-uppercase mb-4 font-weight-bold">Useful links</h5>
                    <p><a href="#" class="text-white" style="text-decoration: none;">Offers</a></p>
                    <p><a href="#" class="text-white" style="text-decoration: none;">My Bookings</a></p>
                    <p><a href="#" class="text-white" style="text-decoration: none;">Help</a></p>
                </div>

                <div class="col-md-4 col-lg-3 col-xl-3 mx-auto mt-3">
                    <h5 class="text-uppercase mb-4 font-weight-bold">Contact</h5>
                    <p><i class="bi bi-house-door-fill mr-3"></i> New Delhi, India</p>
                    <p><i class="bi bi-envelope-fill mr-3"></i> support@busgo.com</p>
                    <p><i class="bi bi-telephone-fill mr-3"></i> + 01 234 567 88</p>
                </div>
            </div>

            <hr class="mb-4">

            <div class="row align-items-center">
                <div class="col-md-7 col-lg-8">
                    <p> © 2024 Copyright:
                        <a href="#" style="text-decoration: none;">
                            <strong class="text-primary">BusGo.com</strong>
                        </a>
                    </p>
                </div>
                <div class="col-md-5 col-lg-4">
                    <div class="text-center text-md-right">
                        <ul class="list-unstyled list-inline">
                            <li class="list-inline-item">
                                <a href="#" class="btn-floating btn-sm text-white" style="font-size: 23px;"><i class="bi bi-facebook"></i></a>
                            </li>
                            <li class="list-inline-item">
                                <a href="#" class="btn-floating btn-sm text-white" style="font-size: 23px;"><i class="bi bi-twitter"></i></a>
                            </li>
                            <li class="list-inline-item">
                                <a href="#" class="btn-floating btn-sm text-white" style="font-size: 23px;"><i class="bi bi-instagram"></i></a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Custom Script for Navbar -->
    <script>
        // Change navbar style on scroll
        const nav = document.querySelector('.navbar');
        window.addEventListener('scroll', () => {
            if (window.scrollY > 50) {
                // When scrolled, make it solid white
                nav.classList.add('bg-white', 'navbar-light');
                nav.classList.remove('navbar-dark');
            } else {
                // At the top, make it transparent
                nav.classList.remove('bg-white', 'navbar-light');
                nav.classList.add('navbar-dark');
            }
        });
    </script>
</body>
</html>





 
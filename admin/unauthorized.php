<?php
// unauthorized.php
// It's good practice to ensure the user is at least logged in to see this page.
// The session_start() is often in _db.php, but including a check is safe.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// If somehow a non-logged-in user lands here, send them to the login page.
if (!isset($_SESSION['user']['login'])) {
    header('Location: index.php');
    exit();
}

$dashboard_page = 'dashboard.php';
$previous_page = $_SERVER['HTTP_REFERER'] ?? $dashboard_page;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Access Denied</title>
    <link href="assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/fa-font/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    
    <!-- ========================================================= -->
    <!-- ==========      CUSTOM CSS FOR THIS PAGE       ========== -->
    <!-- ========================================================= -->
    <style>
        :root {
            --danger-color: #e74c3c;
            --primary-color: #3498db;
            --text-dark: #2c3e50;
            --text-light: #7f8c8d;
            --bg-light: #f1f2f6;
        }

        html, body {
            height: 100%;
            margin: 0;
            font-family: 'Poppins', sans-serif;
        }

        body {
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: var(--bg-light);
            text-align: center;
        }

        .container {
            max-width: 600px;
        }

        .denied-container {
            background-color: #fff;
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 15px 40px -15px rgba(44, 62, 80, 0.2);
            position: relative;
            overflow: hidden;
            border-top: 5px solid var(--danger-color);
        }
        
        .denied-container::before {
            content: "\f023"; /* Font Awesome lock icon */
            font-family: "Font Awesome 5 Free";
            font-weight: 900;
            font-size: 15rem;
            color: var(--bg-light);
            position: absolute;
            bottom: -50px;
            right: -30px;
            opacity: 0.8;
            transform: rotate(-15deg);
            z-index: 1;
        }
        
        .content {
            position: relative;
            z-index: 2;
        }

        .icon-circle {
            width: 80px;
            height: 80px;
            background-color: #fceeee; /* Light red */
            color: var(--danger-color);
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            margin-bottom: 20px;
        }
        
        h1 {
            color: var(--text-dark);
            font-size: 2.5rem;
            font-weight: 700;
            margin: 0;
        }
        
        .subtitle {
            color: var(--danger-color);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 0.5rem;
            margin-bottom: 1.5rem;
        }

        p {
            color: var(--text-light);
            font-size: 1.1rem;
            line-height: 1.6;
        }
        
        .btn-custom {
            margin: 0.5rem;
            padding: 12px 30px;
            font-weight: 600;
            border-radius: 50px;
            border-width: 2px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        .btn-go-back {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            color: #fff;
        }
        
        .btn-go-back:hover {
            background-color: #2980b9;
            border-color: #2980b9;
            transform: translateY(-3px);
            box-shadow: 0 8px 15px rgba(52, 152, 219, 0.2);
        }
        
        .btn-dashboard {
            background-color: transparent;
            border-color: var(--border-color);
            color: var(--text-light);
        }
        .btn-dashboard:hover {
            background-color: var(--bg-light);
            border-color: var(--text-light);
            color: var(--text-dark);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="denied-container">
            <div class="content">
                <div class="icon-circle">
                    <i class="fas fa-hand-paper"></i>
                </div>
                <h1>Access Denied</h1>
                <p class="subtitle">You do not have permission to view this page.</p>
                <p>Your current role does not grant you access to this section. If you believe this is an error, please contact your system administrator to request access.</p>
                <div class="mt-4">
                    <a href="javascript:history.back()" class="btn btn-custom btn-go-back">
                        <i class="fas fa-arrow-left me-2"></i> Go Back
                    </a>
                    <a href="<?php echo $dashboard_page; ?>" class="btn btn-custom btn-dashboard">
                        Go to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
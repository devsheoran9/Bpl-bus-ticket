    <?php
    session_start();
    require 'db_connect.php';

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $username = $_POST['username'];
        $mobile_no = $_POST['mobile_no'];
        $email = $_POST['email'] ?? null; // Opsyonal ang email
        $password = $_POST['password'];

        // I-hash ang password para sa seguridad
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Suriin kung mayroon nang mobile number
        $stmt = $conn->prepare("SELECT id FROM users WHERE mobile_no = ?");
        $stmt->bind_param("s", $mobile_no);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            // Mayroon nang mobile number
            echo "Error: This mobile number is already registered.";
            // Maaari kang mag-redirect pabalik na may mensahe ng error
            header("refresh:2;url=register.php");
            exit();
        }
        $stmt->close();

        // Ipasok ang bagong user
        $stmt = $conn->prepare("INSERT INTO users (username, mobile_no, email, password) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $username, $mobile_no, $email, $hashed_password);

        if ($stmt->execute()) {
            // Matagumpay ang pagpaparehistro
            header("Location: login.php");
            exit();
        } else {
            // Nagkaroon ng error
            echo "Error: " . $stmt->error;
        }

        $stmt->close();
        $conn->close();
    }
    ?>
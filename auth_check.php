<?php
// No need to start session here, as your db_connect.php (or header.php) should already do it.
// No need for a separate db_connect.php here if it's already in your header.

/**
 * Checks if the current user's session token is valid and active in the database.
 * This function should be called on pages that require a user to be logged in.
 *
 * @param string $type Determines the return value. 'page' for a JS redirect, 'header' for a string signal.
 * @return string Either a JavaScript redirect script, the string 'logout_user', or an empty string if login is valid.
 */
function user_login($type = 'page')
{
    // Use the global PDO connection object defined in your db_connect.php
    global $_conn_db;

    $data_get = '';

    // Get user_id and token from the current session. Use null coalescing for safety.
    $user_id = $_SESSION['user_id'] ?? null;
    $session_token = $_SESSION['login_token'] ?? null;

    // If either the user ID or the token is missing from the session, the user is not properly logged in.
    if (!$user_id || !$session_token) {
        if ($type == 'page') {
            return "<script>window.location.href = 'logout';</script>"; // It's safer to use logout.php
        } else if ($type == 'header') {
            return 'logout_user';
        }
    }

    try {
        // --- PDO CONVERSION ---
        // 1. Prepare the statement using the PDO object. Use named placeholders for clarity.
        $stmt = $_conn_db->prepare("SELECT status FROM users_login_token WHERE user_id = :user_id AND token = :token");

        // 2. Execute the statement with an associative array of parameters.
        $stmt->execute([
            ':user_id' => $user_id,
            ':token'   => $session_token
        ]);

        // 3. Check if a row was found using rowCount().
        if ($stmt->rowCount() > 0) {
            // 4. Fetch the result as an associative array.
            $result = $stmt->fetch();

            // Check if the token's status is active (1).
            if ($result['status'] != 1) {
                // Token exists but is disabled/logged out. Force a logout.
                if ($type == 'page') {
                    $data_get = "<script>window.location.href = 'logout';</script>";
                } else if ($type == 'header') {
                    $data_get = 'logout_user';
                }
            }
            // If status is 1, the login is valid. $data_get remains empty.

        } else {
            // No matching token was found in the database. This session is invalid. Force a logout.
            if ($type == 'page') {
                $data_get = "<script>window.location.href = 'logout';</script>";
            } else if ($type == 'header') {
                $data_get = 'logout_user';
            }
        }
    } catch (PDOException $e) {
        // In case of a database error, it's safest to log out the user.
        error_log("Token validation failed: " . $e->getMessage());
        if ($type == 'page') {
            $data_get = "<script>window.location.href = 'logout';</script>";
        } else if ($type == 'header') {
            $data_get = 'logout_user';
        }
    }

    return $data_get;
}

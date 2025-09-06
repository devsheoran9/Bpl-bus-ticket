<?php
global $_conn_db;
include_once('function/_db.php');
// check_user_login();
session_security_check(); 

$route_id = filter_input(INPUT_GET, 'route_id', FILTER_VALIDATE_INT);
if (!$route_id) {
    header("Location: add_route.php");
    exit();
}

// --- FORM SUBMISSION FOR PRICES ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $origins = $_POST['origin'];
    $destinations = $_POST['destination'];
    $prices_sl = $_POST['price_sl'];
    $prices_su = $_POST['price_su'];
    $prices_ll = $_POST['price_ll'];
    $prices_lu = $_POST['price_lu'];

    $_conn_db->beginTransaction();
    try {
        // Easiest way to update: Delete all old sub-routes and insert the new ones from the form
        $stmt_delete = $_conn_db->prepare("DELETE FROM sub_routes WHERE route_id = ?");
        $stmt_delete->execute([$route_id]);

        $sql_insert = "INSERT INTO sub_routes (route_id, origin, destination, price_seater_lower, price_seater_upper, price_sleeper_lower, price_sleeper_upper) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt_insert = $_conn_db->prepare($sql_insert);

        foreach ($origins as $index => $origin) {
            // Only save if at least one price is entered for this segment
            $has_price = !empty($prices_sl[$index]) || !empty($prices_su[$index]) || !empty($prices_ll[$index]) || !empty($prices_lu[$index]);
            if ($has_price) {
                $stmt_insert->execute([
                    $route_id,
                    $origin,
                    $destinations[$index],
                    !empty($prices_sl[$index]) ? $prices_sl[$index] : null,
                    !empty($prices_su[$index]) ? $prices_su[$index] : null,
                    !empty($prices_ll[$index]) ? $prices_ll[$index] : null,
                    !empty($prices_lu[$index]) ? $prices_lu[$index] : null,
                ]);
            }
        }
        $_conn_db->commit();
        $_SESSION['notif_type'] = 'success';
        $_SESSION['notif_title'] = 'Success';
        $_SESSION['notif_desc'] = 'Pricing has been saved successfully.';
    } catch (Exception $e) {
        $_conn_db->rollBack();
        $_SESSION['notif_type'] = 'error';
        $_SESSION['notif_title'] = 'Error';
        $_SESSION['notif_desc'] = 'Could not save prices. ' . $e->getMessage();
    }
    header("Location: manage_pricing.php?route_id=" . $route_id);
    exit();
}


// --- DATA FETCHING FOR DISPLAY ---
try {
    $stmt_main = $_conn_db->prepare("SELECT * FROM routes WHERE route_id = ?");
    $stmt_main->execute([$route_id]);
    $main_route = $stmt_main->fetch(PDO::FETCH_ASSOC);

    $stmt_stops = $_conn_db->prepare("SELECT stop_name FROM route_stops WHERE route_id = ? ORDER BY stop_order ASC");
    $stmt_stops->execute([$route_id]);
    $stops = $stmt_stops->fetchAll(PDO::FETCH_COLUMN);

    $all_locations = array_merge([$main_route['starting_point']], $stops);

    // Fetch existing prices to pre-fill the form
    $stmt_prices = $_conn_db->prepare("SELECT * FROM sub_routes WHERE route_id = ?");
    $stmt_prices->execute([$route_id]);
    $existing_prices_raw = $stmt_prices->fetchAll(PDO::FETCH_ASSOC);
    $existing_prices = [];
    foreach ($existing_prices_raw as $price_row) {
        $key = $price_row['origin'] . '|' . $price_row['destination'];
        $existing_prices[$key] = $price_row;
    }

} catch (PDOException $e) {
    die("Database error.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php include "head.php"; ?>
    <style>
        .price-table th { font-size: 0.8em; text-align: center; }
        .price-table td { vertical-align: middle; }
        .price-table .form-control { text-align: center; padding: 0.25rem; }
    </style>
</head>
<body>
<div id="wrapper">
    <?php include_once('sidebar.php'); ?>
    <div class="main-content">
        <?php include_once('header.php'); ?>
        <div class="container-fluid">
            <h2 class="my-4">Manage Pricing for "<?php echo htmlspecialchars($main_route['route_name']); ?>" (Step 2)</h2>
            
            <div class="card">
                <div class="card-header">Auto-Generated Sub-Routes & Pricing</div>
                <div class="card-body">
                    <form action="manage_pricing.php?route_id=<?php echo $route_id; ?>" method="POST">
                        <div class="table-responsive">
                            <table class="table table-bordered price-table">
                                <thead>
                                    <tr>
                                        <th rowspan="2" class="align-middle">Sub-Route (From → To)</th>
                                        <th colspan="2">Seater Price</th>
                                        <th colspan="2">Sleeper Price</th>
                                    </tr>
                                    <tr>
                                        <th>Lower Deck</th>
                                        <th>Upper Deck</th>
                                        <th>Lower Deck</th>
                                        <th>Upper Deck</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $location_count = count($all_locations);
                                    for ($i = 0; $i < $location_count; $i++) {
                                        for ($j = $i + 1; $j < $location_count; $j++) {
                                            $origin = $all_locations[$i];
                                            $destination = $all_locations[$j];
                                            $price_key = $origin . '|' . $destination;
                                            $prices = $existing_prices[$price_key] ?? [];
                                    ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($origin); ?></strong> → <strong><?php echo htmlspecialchars($destination); ?></strong>
                                            <input type="hidden" name="origin[]" value="<?php echo htmlspecialchars($origin); ?>">
                                            <input type="hidden" name="destination[]" value="<?php echo htmlspecialchars($destination); ?>">
                                        </td>
                                        <td><input type="number" class="form-control" name="price_sl[]" value="<?php echo $prices['price_seater_lower'] ?? ''; ?>"></td>
                                        <td><input type="number" class="form-control" name="price_su[]" value="<?php echo $prices['price_seater_upper'] ?? ''; ?>"></td>
                                        <td><input type="number" class="form-control" name="price_ll[]" value="<?php echo $prices['price_sleeper_lower'] ?? ''; ?>"></td>
                                        <td><input type="number" class="form-control" name="price_lu[]" value="<?php echo $prices['price_sleeper_upper'] ?? ''; ?>"></td>
                                    </tr>
                                    <?php
                                        }
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3">
                            <button type="submit" class="btn btn-success">Save All Prices</button>
                            <a href="add_route.php" class="btn btn-secondary">Back to Main Routes</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include "foot.php"; ?>
</body>
</html>
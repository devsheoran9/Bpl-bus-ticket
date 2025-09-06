<?php
header('Content-Type: application/json');

if (!isset($_GET['from']) || empty($_GET['from'])) {
    echo json_encode([]);
    exit();
}

include '../db_connect.php';

$from_location = $_GET['from'];
$destinations = [];

try {
    // THIS IS THE CORRECTED QUERY THAT UNDERSTANDS STOP ORDER.
    // It finds the route(s) and order of the selected origin and then finds all subsequent stops.
    $stmt = $_conn_db->prepare("
        WITH OriginData AS (
            -- This subquery finds all routes and the stop_order for the selected origin location.
            -- A main starting_point is treated as having stop_order = 0.
            -- An intermediate stop gets its order from the route_stops table.
            
            -- Case 1: The selected location is a main starting_point
            SELECT
                r.route_id,
                0 AS origin_order
            FROM routes r
            WHERE r.starting_point = :from_location

            UNION ALL

            -- Case 2: The selected location is an intermediate stop
            SELECT
                rs.route_id,
                rs.stop_order
            FROM route_stops rs
            WHERE rs.stop_name = :from_location_stop
        )
        -- The main query uses this information to find all valid future destinations.
        SELECT DISTINCT destination FROM (
            -- Part 1: Select all stops on the same route(s) that have a HIGHER stop_order.
            SELECT
                rs.stop_name AS destination
            FROM route_stops rs
            JOIN OriginData od ON rs.route_id = od.route_id
            WHERE rs.stop_order > od.origin_order

            UNION

            -- Part 2: Select the final ending_point of the same route(s).
            SELECT
                r.ending_point AS destination
            FROM routes r
            JOIN OriginData od ON r.route_id = od.route_id
        ) AS destinations
        WHERE destination IS NOT NULL AND destination != '' AND destination != :from_location_exclude
        ORDER BY destination ASC
    ");

    $stmt->bindParam(':from_location', $from_location, PDO::PARAM_STR);
    $stmt->bindParam(':from_location_stop', $from_location, PDO::PARAM_STR);
    $stmt->bindParam(':from_location_exclude', $from_location, PDO::PARAM_STR);

    $stmt->execute();
    $destinations = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    error_log("get_destinations.php DB Error: " . $e->getMessage());
    $destinations = [];
}

echo json_encode($destinations);

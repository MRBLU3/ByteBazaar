<?php
include 'conn.php';

// Fetch sales data from the database
$query = "SELECT MONTH(placed_on) as month, SUM(total_price) as sales FROM orders WHERE payment_status = 'Delivered' GROUP BY MONTH(placed_on)";
$result = mysqli_query($conn, $query);

$labels = [];
$sales = [];

while ($row = mysqli_fetch_assoc($result)) {
    $labels[] = date('F', mktime(0, 0, 0, $row['month'], 1)); // Convert month number to month name
    $sales[] = $row['sales'];
}

// Return data as JSON
echo json_encode(['labels' => $labels, 'sales' => $sales]);
?>

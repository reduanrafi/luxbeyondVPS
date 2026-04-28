<?php
header('Content-Type: application/json');
$env = parse_ini_file(__DIR__ . '/../.env');
$conn = new mysqli($env['DB_HOST'], $env['DB_USERNAME'], $env['DB_PASSWORD'], $env['DB_DATABASE'], $env['DB_PORT']);
$result = $conn->query("SELECT id, name, value, currency_id, calculation_type, min_value FROM charges WHERE is_active = 1");
$charges = [];
while($row = $result->fetch_assoc()) {
    $charges[] = $row;
}
echo json_encode($charges, JSON_PRETTY_PRINT);

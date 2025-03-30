<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

function readJsonFile($filePath) {
    $jsonData = file_get_contents($filePath);
    return json_decode($jsonData, true);
}

function writeJsonFile($filePath, $data) {
    $jsonData = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    file_put_contents($filePath, $jsonData);
}

$rentalId = $_GET['id'] ?? null;
$carId = $_GET['car_id'] ?? null;
$quantity = isset($_GET['quantity']) ? (int) $_GET['quantity'] : 1;

if ($rentalId && $carId) {
    $conn = new mysqli("awseb-e-3pqvxqgps3-stack-awsebrdsdatabase-kfrmj4ibp4zb.cekz9x3omu0o.us-east-1.rds.amazonaws.com", "uts", "12312347", "assignment2");
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $stmt = $conn->prepare("UPDATE rental_records SET status = 'confirmed' WHERE id = ? AND car_id = ?");
    $stmt->bind_param("ii", $rentalId, $carId);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        $cars = json_decode(file_get_contents('cars.json'), true);
        foreach ($cars['cars'] as &$car) {
            if ($car['id'] == $carId && $car['quantity'] >= $quantity) {
                $car['quantity'] -= $quantity;
                file_put_contents('cars.json', json_encode($cars));
            }
        }
        $stmt = $conn->prepare("SELECT * FROM rental_records WHERE id = ?");
        $stmt->bind_param("i", $rentalId);
        $stmt->execute();
        $result = $stmt->get_result();
        $rental = $result->fetch_assoc();

        echo "<div style='font-family: Arial, sans-serif; padding: 40px; background-color: #f0f0f9; border: 2px solid #c2e1c2; border-radius: 10px; width: 60%; margin: 40px auto; text-align: center; font-size: 18px;'>
        <h1 style='color: #007bff; font-size: 32px;'>Order Confirmed!</h1>
        <p>Thank you for your reservation. We are excited to have you on board!</p>
        <p><strong>Reservation Details:</strong></p>
        <p><strong>Car Model:</strong> {$rental['car_model']}</p>
        <p><strong>Start Date:</strong> {$rental['start_date']}</p>
        <p><strong>End Date:</strong> {$rental['end_date']}</p>
        <p><strong>Quantity:</strong> {$rental['quantity']}</p>
        <p><strong>Total Cost:</strong> \${$rental['total_cost']}</p>
        <a href='index.php' style='display: inline-block; padding: 10px 20px; background-color: 007bff; color: white; text-decoration: none; border-radius: 5px; font-size: 18px;'>Return to Home</a>
      </div>";
    } else {
        echo "<p style='font-family: Arial, sans-serif; font-size: 18px; text-align: center;'>Invalid rental ID or car ID. Please verify your information and try again.</p>";
    }

    $stmt->close();
    $conn->close();
} else {
    echo "<p style='font-family: Arial, sans-serif; font-size: 18px; text-align: center;'>Invalid rental ID. Please check your booking details and try again.</p>";
}


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

function calculateSubtotal($pricePerDay, $days, $quantity) {
    return $pricePerDay * $days * $quantity;
}

// Handle empty cart request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['empty_cart'])) {
    unset($_SESSION['selected_car']);
    echo json_encode(['success' => true]);
    exit;
}

// Retrieve the car details from the session if available
$selectedCar = isset($_SESSION['selected_car']) ? $_SESSION['selected_car'] : null;

if (!$selectedCar) {
    $selectedCarId = $_GET['car_id'] ?? '';

    if (!empty($selectedCarId)) {
        $cars = json_decode(file_get_contents('cars.json'), true)['cars'];
        $selectedCar = current(array_filter($cars, function ($car) use ($selectedCarId) {
            return $car['id'] == $selectedCarId;
        }));

        if (!$selectedCar) {
            die('Car not found. Please select a valid car.');
        } else {
            $_SESSION['selected_car'] = $selectedCar;
        }
    } else {
        die('No car selected. Please select a car to proceed.');
    }
}

$initialQuantity = 1;
$initialStartDate = date('Y-m-d');
$initialEndDate = date('Y-m-d', strtotime('+1 day'));
$initialDays = (strtotime($initialEndDate) - strtotime($initialStartDate)) / 86400;
$initialCost = calculateSubtotal($selectedCar['pricePerDay'], $initialDays, $initialQuantity);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $quantity = $_POST['quantity'];
    $startDate = $_POST['start_date'];
    $endDate = $_POST['end_date'];
    $days = (strtotime($endDate) - strtotime($startDate)) / 86400;
    $rentalCost = calculateSubtotal($selectedCar['pricePerDay'], $days, $quantity);

    $conn = new mysqli("awseb-e-3pqvxqgps3-stack-awsebrdsdatabase-kfrmj4ibp4zb.cekz9x3omu0o.us-east-1.rds.amazonaws.com", "uts", "12312347", "assignment2");

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $stmt = $conn->prepare("INSERT INTO rental_records (car_id, car_model, start_date, end_date, quantity, total_cost, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $status = 'unconfirmed';
    $stmt->bind_param("isssids", $selectedCar['id'], $selectedCar['model'], $startDate, $endDate, $quantity, $rentalCost, $status);

    if ($stmt->execute()) {
        $reservation_id = $stmt->insert_id;
        unset($_SESSION['selected_car']);
        echo "<div style='font-family: Arial, sans-serif; padding: 20px; background-color: #f9f9f9; border: 1px solid #ccc; border-radius: 5px; width: 80%; margin: 20px auto; text-align: center;'>
                <h2 style='color: 007bff;'>Reservation successful!</h2>
                <p>Reservation successful for <strong>{$quantity} x {$selectedCar['model']}</strong> from <strong>{$startDate}</strong> to <strong>{$endDate}</strong>.</p>
                <p>Total cost: <strong>$${rentalCost}</strong></p>
                <p><a href='confirm.php?id=$reservation_id&car_id={$selectedCar['id']}' style='color: #fff; background-color: #007bff; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Click here to confirm your order</a></p>
              </div>";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservation - Daun</title>
    <link rel="stylesheet" href="./assets/css/style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .disabled-button {
            background-color: grey !important;
            cursor: not-allowed !important;
        }
    </style>
</head>
<body>
    <main class="main-content">
        <h1>Car Reservation</h1>
        <section class="cart-display">
            <?php if ($selectedCar): ?>
                <div class='item'>
                    <img src='<?= htmlspecialchars($selectedCar['image']) ?>' alt='<?= htmlspecialchars($selectedCar['brand']) ?> <?= htmlspecialchars($selectedCar['model']) ?>' style='width:100px; height:auto;'>
                    <table class='car-details'>
                        <tr><th>Model:</th><td><?= htmlspecialchars($selectedCar['brand']) ?> <?= htmlspecialchars($selectedCar['model']) ?></td></tr>
                        <tr><th>Price per Day:</th><td>$<?= htmlspecialchars($selectedCar['pricePerDay']) ?></td></tr>
                        <tr><th>Quantity:</th><td>1</td></tr>
                        <tr><th>Total Cost:</th><td><span id='cost-display-0'>$<?= number_format($initialCost, 2) ?></span></td></tr>
                    </table>
                </div>
            <?php else: ?>
                <p>No car selected. Please select a car to proceed.</p>
            <?php endif; ?>
        </section>
        <form class="reservation-form" action="reservation.php" method="post">
            <div class="form-group">
                <label>Start Date:</label>
                <input class="input-field" type="date" name="start_date" id="start_date" value="<?= htmlspecialchars($initialStartDate) ?>" required>
            </div>
            <div class="form-group">
                <label>End Date:</label>
                <input class="input-field" type="date" name="end_date" id="end_date" value="<?= htmlspecialchars($initialEndDate) ?>" required>
            </div>
            <div class="form-group">
                <label>Quantity:</label>
                <input class="input-field" type="number" name="quantity" id="quantity" value="1" min="1" required>
            </div>
            <div class="form-group">
                <label>Name:</label>
                <input class="input-field" type="text" name="name" id="name" required>
            </div>
            <div class="form-group">
                <label>Mobile Number:</label>
                <input class="input-field" type="text" name="mobile" id="mobile" required>
            </div>
            <div class="form-group">
                <label>Email Address:</label>
                <input class="input-field" type="email" name="email" id="email" required>
            </div>
            <div class="form-group">
                <label>Valid Driver's License:</label>
                <select class="input-select" name="has_license" id="has_license" required>
                    <option value="yes">Yes</option>
                    <option value="no">No</option>
                </select>
                <p id="license-warning" style="color: red; display: none;">You must have a valid driver's license to proceed with the reservation.</p>
            </div>
            <button class="submit-btn" type="submit" id="submit-button">Submit Reservation</button>
            <button class="cancel-btn" type="button" id="cancel-button">Cancel</button>
        </form>
    </main>

</body>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const startDate = document.querySelector('input[name="start_date"]');
    const endDate = document.querySelector('input[name="end_date"]');
    const quantity = document.querySelector('input[name="quantity"]');
    const name = document.querySelector('input[name="name"]');
    const mobile = document.querySelector('input[name="mobile"]');
    const email = document.querySelector('input[name="email"]');
    const hasLicense = document.getElementById('has_license');
    const submitButton = document.getElementById('submit-button');
    const licenseWarning = document.getElementById('license-warning');
    const pricePerDay = <?= json_encode($selectedCar['pricePerDay'] ?? 0) ?>;

    function validateForm() {
        if (name.value.trim() === '' || mobile.value.trim() === '' || email.value.trim() === '' ||
            startDate.value === '' || endDate.value === '' || quantity.value <= 0 ||
            hasLicense.value === 'no') {
            submitButton.disabled = true;
            submitButton.classList.add('disabled-button');
            if (hasLicense.value === 'no') {
                licenseWarning.style.display = 'block';
            }
        } else {
            submitButton.disabled = false;
            submitButton.classList.remove('disabled-button');
            licenseWarning.style.display = 'none';
        }
    }

    startDate.addEventListener('change', validateForm);
    endDate.addEventListener('change', validateForm);
    quantity.addEventListener('input', validateForm);
    name.addEventListener('input', validateForm);
    mobile.addEventListener('input', validateForm);
    email.addEventListener('input', validateForm);
    hasLicense.addEventListener('change', validateForm);

    document.querySelectorAll('[id^="cost-display-"]').forEach(function(costDisplay, index) {
        function calculateCost() {
            const start = new Date(startDate.value);
            const end = new Date(endDate.value);
            const days = Math.max((end - start) / (1000 * 60 * 60 * 24), 1);
            const cost = quantity.value * pricePerDay * days;
            costDisplay.innerText = '$' + cost.toFixed(2);
        }

        startDate.addEventListener('change', calculateCost);
        endDate.addEventListener('change', calculateCost);
        quantity.addEventListener('input', calculateCost);

        calculateCost();
    });

    validateForm(); // Initial validation check
});

$(document).ready(function() {
    $("#cancel-button").on("click", function() {
        $.ajax({
            url: "reservation.php",
            method: "POST",
            data: { empty_cart: true },
            success: function(response) {
                window.location.href = 'index.php';
            }
        });
    });
});
</script>
</html>

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

// Function to add items to the cart
function addToCart($id, $model, $brand, $pricePerDay, $image) {
  $_SESSION['cart'] = [];
  $_SESSION['cart'][$id] = [
      'id' => $id,
      'model' => $model,
      'brand' => $brand,
      'price' => $pricePerDay,
      'quantity' => 1,
      'image' => $image
  ];
}

if (isset($_POST['action']) && $_POST['action'] == 'add_to_cart') {
    $id = $_POST['id'];
    $model = $_POST['model'];
    $brand = $_POST['brand'];
    $pricePerDay = $_POST['pricePerDay'];
    $image = $_POST['image'];
    addToCart($id, $model, $brand, $pricePerDay, $image);
    echo json_encode(['status' => 'success', 'message' => 'The car has been added to the cart.', 'id' => $id]);
    exit;
}

$type = $_GET['type'] ?? 'All';
$brand = $_GET['brand'] ?? 'All';

$cars = json_decode(file_get_contents('cars.json'), true)['cars'];
$filteredCars = array_filter($cars, function ($car) use ($type, $brand) {
    if ($brand === 'Others') {
        $otherBrands = ['Mitsubishi', 'Volkswagen', 'Mercedes-Benz', 'Audi', 'Cupra', 'Nissan'];
        return ($type === 'All' || strtolower($car['type']) === strtolower($type))
            && (in_array($car['brand'], $otherBrands));
    }
    return ($type === 'All' || strtolower($car['type']) === strtolower($type))
        && ($brand === 'All' || strtolower($car['brand']) === strtolower($brand));
});
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daun - Category</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="shortcut icon" href="./favicon.svg" type="image/svg+xml">
    <link rel="stylesheet" href="./assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600&family=Open+Sans&display=swap" rel="stylesheet">
</head>
<body>
<header class="header" data-header>
    <div class="container">
        <a href="index.php" class="logo">
            <img src="./assets/images/logo.png" alt="logo">
        </a>
        <nav class="navbar" data-navbar>
            <ul class="navbar-list">
                <li class="subnav">
                    <button class="subnavbtn">Types <i class="fa fa-caret-down"></i></button>
                    <div class="subnav-content" id="cars-dropdown">
                        <a href="category.php?type=All">All Cars</a>
                        <a href="category.php?type=Sedan">Sedan</a>
                        <a href="category.php?type=SUV">SUV</a>
                        <a href="category.php?type=Hatchback">Hatchback</a>
                    </div>
                </li>
                <li class="subnav">
                    <button class="subnavbtn">Brands <i class="fa fa-caret-down"></i></button>
                    <div class="subnav-content" id="brands-dropdown">
                        <a href="category.php?brand=Kia">Kia</a>
                        <a href="category.php?brand=Hyundai">Hyundai</a>
                        <a href="category.php?brand=Toyota">Toyota</a>
                        <a href="category.php?brand=Ford">Ford</a>
                        <a href="category.php?brand=Mazda">Mazda</a>
                        <a href="category.php?brand=Subaru">Subaru</a>
                        <a href="category.php?brand=Others">Others</a>
                    </div>
                </li>
            </ul>
        </nav>
        <div class="searchBar">
            <input type="search" id="searchInput" placeholder="Search cars..." aria-label="Search cars">
            <div id="suggestionsPanel" class="suggestions-panel"></div>
            <button onclick="performSearch()">Search</button>
        </div>
        <a href="reservation.php" class="reservation-btn" aria-labelledby="aria-label-txt">
            <span id="aria-label-txt">Reservation</span>
        </a>
        <button class="nav-toggle-btn" data-nav-toggle-btn aria-label="Toggle Menu">
            <span class="one"></span>
            <span class="two"></span>
            <span class="three"></span>
        </button>
    </div>
</header>

<main>
    <section class="section featured-car" id="featured-car">
        <div class="container">
            <div class="title-wrapper">
                <h2 class="h2 section-title">Filtered Cars</h2>
            </div>
            <ul class="featured-car-list">
                <?php foreach ($filteredCars as $car): ?>
                    <li class="featured-car-card">
                        <figure class="card-banner">
                            <img src="<?= htmlspecialchars($car['image']) ?>" alt="<?= htmlspecialchars($car['model']) ?>" class="w-100">
                        </figure>
                        <div class="card-content">
                            <div class="card-title-wrapper">
                                <h3 class="h3 card-title"><a href="#"><?= htmlspecialchars($car['brand']) ?> <?= htmlspecialchars($car['model']) ?></a></h3>
                            </div>
                            <ul class="card-list">
                                <li class="card-list-item"><ion-icon name="people-outline"></ion-icon><span class="card-item-text"><?= htmlspecialchars($car['seats']) ?> People</span></li>
                                <li class="card-list-item"><ion-icon name="flash-outline"></ion-icon><span class="card-item-text"><?= htmlspecialchars($car['fuelType']) ?></span></li>
                                <li class="card-list-item"><ion-icon name="hardware-chip-outline"></ion-icon><span class="card-item-text"><?= htmlspecialchars($car['transmission']) ?></span></li>
                                <li class="card-list-item"><ion-icon name="checkmark-circle-outline"></ion-icon><span class="card-item-text"><?= ($car['quantity'] > 0) ? 'available' : 'unavailable' ?></span></li>
                            </ul>
                            <div class="card-price-wrapper">
                                <?php if ($car['quantity'] > 0): ?>
                                    <button class="btn rent-btn" onclick="addToCart('<?= htmlspecialchars($car['model']) ?>', '<?= htmlspecialchars($car['brand']) ?>', <?= htmlspecialchars($car['pricePerDay']) ?>, '<?= htmlspecialchars($car['image']) ?>', <?= htmlspecialchars($car['id']) ?>, event);">Rent now</button>
                                <?php else: ?>
                                    <button class="btn rent-btn disabled" style="background-color: grey; cursor: not-allowed;" disabled>Rent now</button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </section>
</main>
<!-- Footer -->
<footer class="footer">
    <div class="container">
        <div class="footer-bottom">
            <ul class="social-list">
                <li><a href="#" class="social-link"><ion-icon name="logo-facebook"></ion-icon></a></li>
                <li><a href="#" class="social-link"><ion-icon name="logo-instagram"></ion-icon></a></li>
                <li><a href="#" class="social-link"><ion-icon name="logo-twitter"></ion-icon></a></li>
                <li><a href="#" class="social-link"><ion-icon name="logo-linkedin"></ion-icon></a></li>
                <li><a href="#" class="social-link"><ion-icon name="logo-skype"></ion-icon></a></li>
                <li><a href="#" class="social-link"><ion-icon name="mail-outline"></ion-icon></a></li>
            </ul>
            <p class="copyright">&copy; 2024 <a href="#">14434498 Daun Jeong</a>. All Rights Reserved</p>
        </div>
    </div>
</footer>
<script src="./assets/js/script.js"></script>
<script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
<script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
  var subnavButtons = document.querySelectorAll('.subnavbtn');
  subnavButtons.forEach(function (btn) {
    btn.onclick = function () {
      var dropdown = btn.nextElementSibling;
      dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
    };
  });

  window.onclick = function (event) {
    if (!event.target.matches('.subnavbtn')) {
      var dropdowns = document.getElementsByClassName('subnav-content');
      Array.from(dropdowns).forEach(function (dropdown) {
        if (dropdown.style.display === 'block') {
          dropdown.style.display = 'none';
        }
      });
    }
  };

  document.querySelector('.searchBar button').addEventListener('click', function () {
    performSearch();
  });

  document.getElementById('searchInput').addEventListener('keypress', function (e) {
    if (e.key === 'Enter') {
      performSearch();
    }
  });

  function performSearch() {
    const searchQuery = document.getElementById('searchInput').value;
    window.location.href = `search.php?search=${encodeURIComponent(searchQuery)}`;
  }
});

function addToCart(model, brand, pricePerDay, image, id, event) {
  event.preventDefault();

  fetch('category.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/x-www-form-urlencoded',
    },
    body: `action=add_to_cart&model=${encodeURIComponent(model)}&brand=${encodeURIComponent(brand)}&pricePerDay=${pricePerDay}&image=${encodeURIComponent(image)}&id=${id}`
  })
  .then(response => response.json())
  .then(data => {
    if (data.status === 'success') {
      alert(data.message);
      window.location.href = `reservation.php?car_id=${data.id}`;
    } else {
      alert('Failed to add to cart.');
    }
  })
  .catch(error => console.error('Error:', error));
}
</script>
</body>
</html>
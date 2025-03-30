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
    echo json_encode(['status' => 'success', 'message' => 'The car has been added to the cart.']);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daun - Rent your favourite car</title>
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
    <article>
        <section class="section hero" id="home">
            <div class="container">
                <div class="hero-content">
                    <h2 class="h1 hero-title">Find Your Perfect Rental Car</h2>
                    <p class="hero-text">Quick and Easy Booking!</p>
                </div>
            </div>
        </section>

        <!-- Featured Car Section -->
        <section class="section featured-car" id="featured-car">
            <div class="container">
                <div class="title-wrapper">
                    <h2 class="h2 section-title">Featured cars</h2>
                </div>
                <ul class="featured-car-list">
                    <?php
                    $cars = json_decode(file_get_contents('cars.json'), true)['cars'];
                    foreach ($cars as $car) {
                        $availabilityText = $car['quantity'] > 0 ? "available" : "unavailable";
                        $buttonClass = $car['quantity'] > 0 ? "btn rent-btn" : "btn rent-btn disabled";
                        $buttonStyle = $car['quantity'] > 0 ? "" : "style='background-color: grey; cursor: not-allowed;'";
                        echo "<li>";
                        echo "<div class='featured-car-card'>";
                        echo "<figure class='card-banner'>";
                        echo "<img src='{$car['image']}' alt='{$car['model']}' loading='lazy' class='w-100'>";
                        echo "</figure>";
                        echo "<div class='card-content'>";
                        echo "<div class='card-title-wrapper'>";
                        echo "<h3 class='h3 card-title'><a href='#'>{$car['brand']} {$car['model']}</a></h3>";
                        echo "</div>";
                        echo "<ul class='card-list'>";
                        echo "<li class='card-list-item'><ion-icon name='people-outline'></ion-icon><span class='card-item-text'>{$car['seats']} People</span></li>";
                        echo "<li class='card-list-item'><ion-icon name='flash-outline'></ion-icon><span class='card-item-text'>{$car['fuelType']}</span></li>";
                        echo "<li class='card-list-item'><ion-icon name='hardware-chip-outline'></ion-icon><span class='card-item-text'>{$car['transmission']}</span></li>";
                        echo "<li class='card-list-item'><ion-icon name='checkmark-circle-outline'></ion-icon><span class='card-item-text'>{$availabilityText}</span></li>";
                        echo "</ul>";
                        echo "<div class='card-price-wrapper'>";
                        if ($car['quantity'] > 0) {
                            echo "<button class='$buttonClass' onclick=\"addToCart('{$car['model']}', '{$car['brand']}', {$car['pricePerDay']}, '{$car['image']}', {$car['id']}, event);\" data-id='{$car['id']}' data-model='{$car['model']}' data-brand='{$car['brand']}' data-price='{$car['pricePerDay']}' data-image='{$car['image']}'>Rent now</button>";
                        } else {
                            echo "<button class='$buttonClass' disabled $buttonStyle>Rent now</button>";
                        }
                        echo "</div>";
                        echo "</div>";
                        echo "</div>";
                        echo "</li>";
                    }
                    ?>
                </ul>
            </div>
        </section>
    </article>
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

  fetch('index.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/x-www-form-urlencoded',
    },
    body: `action=add_to_cart&model=${encodeURIComponent(model)}&brand=${encodeURIComponent(brand)}&pricePerDay=${pricePerDay}&image=${encodeURIComponent(image)}&id=${id}`
  })
  .then(response => response.json())
  .then(data => {
    alert(data.message);
    window.location.href = `reservation.php?car_id=${id}`;
  })
  .catch(error => console.error('Error:', error));
}
</script>
<script>
document.addEventListener('DOMContentLoaded', function () {
  const searchInput = document.getElementById('searchInput');
  const suggestionsPanel = document.getElementById('suggestionsPanel');
  let timeoutId;

  searchInput.addEventListener('input', function() {
    clearTimeout(timeoutId);
    const query = this.value;
    timeoutId = setTimeout(() => {
      if (query.length > 0) {
        fetchSuggestions(query);
      } else {
        suggestionsPanel.innerHTML = '';
        suggestionsPanel.style.display = 'none';
      }
    }, 300); // 300ms의 디바운싱 시간
  });

  searchInput.addEventListener('focus', function() {
    if (this.value.length > 0) {
      suggestionsPanel.style.display = 'block';
    }
  });

  function fetchSuggestions(query) {
    const url = `search.php?search=${encodeURIComponent(query)}&ajax=1`;
    fetch(url)
        .then(response => response.json())
        .then(data => {
            suggestionsPanel.innerHTML = ''; // Reset the suggestions panel
            const suggestions = new Set(); // Use a Set to avoid duplicate entries

            data.forEach(item => {
                // Add each type of suggestion to the Set
                suggestions.add(item.brand);
                suggestions.add(item.model);
                suggestions.add(item.type);
            });

            suggestions.forEach(suggestion => {
                const div = document.createElement('div');
                div.textContent = suggestion;
                div.classList.add('suggestion-item');
                div.onclick = () => {
                    searchInput.value = suggestion;
                    performSearch();
                };
                suggestionsPanel.appendChild(div);
            });

            if (suggestions.size > 0) {
                suggestionsPanel.style.display = 'block';
            } else {
                suggestionsPanel.style.display = 'none';
            }
        })
        .catch(error => {
            console.error('Error fetching suggestions:', error);
            suggestionsPanel.style.display = 'none'; // Hide the panel if an error occurs
        });
}
});
</script>
</body>
</html>

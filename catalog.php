<?php 
require_once 'php_scripts/config.php'; 
require 'header.php'; 
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="src/css/catalog.css">
    <title>Каталог продуктов</title>
    <script>
        let allProducts = []; // Массив для хранения всех продуктов

        // Поиск продуктов по наименованию
        function searchProducts() {
            const input = document.getElementById("search").value.trim();
            const xhr = new XMLHttpRequest();
            xhr.open("GET", "php_scripts/search.php?query=" + encodeURIComponent(input), true);
            xhr.onreadystatechange = function () {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    allProducts = JSON.parse(xhr.responseText);
                    displayResults(allProducts);
                }
            };
            xhr.send();
        }

        // Фильтрация продуктов по цене
        function filterProducts() {
            const minPrice = parseFloat(document.getElementById("min-price").value) || 0;
            const maxPrice = parseFloat(document.getElementById("max-price").value) || Infinity;
            
            const filteredProducts = allProducts.filter(product => {
                const price = parseFloat(product.price);
                return price >= minPrice && price <= maxPrice;
            });

            displayResults(filteredProducts);
        }

        // Отображение продуктов на странице
        function displayResults(products) {
            const container = document.getElementById('product-results');
            container.innerHTML = ''; // Очистка предыдущих результатов

            if (products.length === 0) {
                container.innerHTML = '<p class="empty">Товары не найдены.</p>';
                return;
            }

            products.forEach(product => {
                const card = document.createElement('a');
                card.href = `full_product.php?id=${product.id}`;
                card.className = 'product-card';
                card.innerHTML = `
                    <div>
                        <img loading="lazy" src="${product.photo_path}" alt="${product.name}">
                        <div class = "product_name">
                        <h3>${product.name}</h3>
                        </div>
                        <p>Категория: ${product.category_name}</p>
                        
                        <br><p class="price_product">${Math.round(product.price)} руб.</p>
                        <button class="add-to-cart-btn" onclick="event.stopPropagation(); addToCart(${product.id}, '${product.name}', ${product.price}, '${product.photo_path}')">Добавить</button>
                    </div>
                `;
                container.appendChild(card);
            });
        }

        function applyPriceFilter() {
            filterProducts();
        }

        function addToCart(id, name, price, photo) {
            const userId = <?php echo isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'null'; ?>;

            if (userId === null) {
                window.location.href = "login_and_register_page.php";
                return;
            }

            const xhr = new XMLHttpRequest();
            xhr.open("POST", "php_scripts/add_to_cart.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.onreadystatechange = function () {
                if (xhr.readyState === XMLHttpRequest.DONE) {
                    if (xhr.status === 200) {
                        alert("Товар " + name + " добавлен в корзину!");
                    } else {
                        alert("Ошибка добавления товара в корзину. Попробуйте снова.");
                    }
                }
            };

            xhr.send("user_id=" + userId + "&product_id=" + id + "&price=" + price);
        }
    </script>
</head>
<body>
    <div class="container_catalog">
        <div class="category_filtr">
            <div class="left_category">
                <?php
                $is_active = !isset($_GET['category_id']) || $_GET['category_id'] == 0; // Проверяем, выбрана ли категория
                echo '<a href="catalog.php" class="category_link' . ($is_active ? ' active-category' : '') . '">Все</a>';
                $category_sql = "SELECT id, category_name FROM categories";
                $category_result = $conn->query($category_sql);
                if ($category_result->num_rows > 0) {
                    while ($category = $category_result->fetch_assoc()) {
                        $is_active = (isset($_GET['category_id']) && $_GET['category_id'] == $category['id']);
                        echo '<a href="catalog.php?category_id=' . $category['id'] . '" class="category_link' . ($is_active ? ' active-category' : '') . '">' . $category['category_name'] . '</a>';
                    }
                } else {
                    echo '<p>Категории не найдены.</p>';
                }
                ?>
            </div>
            <div class="right_category">
                <input type="text" id="search" class="search-input" onkeyup="searchProducts()" placeholder="Поиск по наименованию товара">
                <img src="src/icon/search.svg">
            </div>
        </div>
        <div class="display_catalog">
            <div class="left_catalog_filtr">
                <div class="price-filter-container">
                    <label for="min-price">Минимальная цена:</label>
                    <input type="number" id="min-price" placeholder="0">
                    <label for="max-price">Максимальная цена:</label>
                    <input type="number" id="max-price" placeholder="10000">
                    <button onclick="applyPriceFilter()">Применить</button>
                </div>
            </div>
<div id="product-results" class="catalog">
    <?php
    $selected_category = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;

    $sql = "SELECT products.id, products.name, products.description, products.photo_path, products.price, categories.category_name 
            FROM products 
            INNER JOIN categories ON products.category_id = categories.id";

    if ($selected_category > 0) {
        $sql .= " WHERE categories.id = $selected_category";
    }

    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo '<div class="product-card">';
            echo '<a href="full_product.php?id=' . $row['id'] . '">';
            echo '<img src="' . $row['photo_path'] . '" alt="' . $row['name'] . '">';
            echo "<div class = 'product_name'>";
            echo '<h3>' . $row['name'] . '</h3>';
            echo "</div>";
            echo '<p>Категория: ' . $row['category_name'] . '</p>';
            
            echo '<br><p class="price_product">' . intval($row['price']) . ' руб.</p>';
            echo '</a>';
            echo '<button class="add-to-cart-btn" onclick="addToCart(' . $row['id'] . ', \'' . addslashes($row['name']) . '\', ' . $row['price'] . ', \'' . $row['photo_path'] . '\')">Добавить</button>';
            echo '</div>';
            echo '<script>allProducts.push(' . json_encode($row) . ');</script>';
        }
    } else {
        echo '<p>Товары не найдены.</p>';
    }
    ?>
</div>

        </div>
    </div>
</body>
</html>

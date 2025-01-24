<?php
require 'header.php';
include 'php_scripts/config.php';

// Получаем ID продукта из URL
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Проверка, существует ли продукт с таким ID
if ($product_id > 0) {
    // Запрос на получение информации о продукте вместе с категорией
    $sql = "
        SELECT p.*, c.category_name
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();

    // Проверяем, найден ли продукт
    if ($result->num_rows > 0) {
        $product = $result->fetch_assoc();
    } else {
        echo "<div class='empty'>Продукт не найден.</div>";
        exit();
    }
} else {
    echo "<div class='empty'>Некорректный ID продукта.</div>";
    exit();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="src/css/full_product.css"> 
    <title><?php echo htmlspecialchars($product['name']); ?></title>
</head>
<body>
    <div class="container_catalog">
        <div class="product-details">
            <div class="product-image">
                <a href="javascript:window.history.back();">
                    <img class="arrow_back" src="src/icon/back.svg" alt="Назад">
                </a>

                <img class="product_image_bd" src="<?php echo htmlspecialchars($product['photo_path']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
            </div>
            <div class="product-info">
                <div class="main_info">
                    <span><?php echo htmlspecialchars($product['name']); ?></span>
                    <div class="hr"></div>
                    <p class="category">Категория: <?php echo htmlspecialchars($product['category_name']); ?></p>
                    <p class="description">Описание: <?php echo htmlspecialchars($product['description']); ?></p>
                   <p class="price_product">Цена: <?php echo intval($product['price']); ?> руб.</p>
                </div>
                <button class="add-to-cart-btn" onclick="addToCart(<?php echo $product['id']; ?>, '<?php echo addslashes($product['name']); ?>', <?php echo $product['price']; ?>)">Добавить в корзину</button>

            </div>
        </div>
    </div>
<script>
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

</body>
</html>

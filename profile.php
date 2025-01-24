<?php 
session_start();
require 'php_scripts/config.php';

// Проверка, авторизован ли пользователь
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

// Получаем ID текущего пользователя
$user_id = $_SESSION['user_id'];

// Проверяем, является ли пользователь администратором
$admin_check_query = "SELECT admin FROM users WHERE id = ?";
$admin_stmt = $conn->prepare($admin_check_query);
$admin_stmt->bind_param("i", $user_id);
$admin_stmt->execute();
$admin_result = $admin_stmt->get_result();
$admin_row = $admin_result->fetch_assoc();

// Если пользователь является администратором, перенаправляем его на admin.php
if ($admin_row && $admin_row['admin'] == 1) {
    header('Location: admin.php');
    exit();
}

$status = isset($_GET['status']) ? $_GET['status'] : 'all';

$query = "SELECT orders.id AS order_id, orders.total_price, orders.status, orders.order_date, 
                 GROUP_CONCAT(products.id SEPARATOR ', ') AS product_ids,
                 GROUP_CONCAT(products.name SEPARATOR ', ') AS product_names, 
                 SUM(order_details.quantity) AS total_quantity,
                 GROUP_CONCAT(products.photo_path SEPARATOR ', ') AS product_images
          FROM orders
          JOIN order_details ON orders.id = order_details.order_id
          JOIN products ON order_details.product_id = products.id
          WHERE orders.user_id = ?";

if ($status !== 'all') {
    $query .= " AND orders.status = ?";
}

$query .= " GROUP BY orders.id ORDER BY orders.order_date DESC";
$stmt = $conn->prepare($query);

if ($status !== 'all') {
    $stmt->bind_param("is", $user_id, $status);
} else {
    $stmt->bind_param("i", $user_id);
}

$stmt->execute();
$result = $stmt->get_result();
require 'header.php';
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" type="text/css" href="src/css/profile.css">
    <title>Ваш профиль</title>
</head>
<body>

<div class="profile-container">
    <h1>Добро пожаловать, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</h1>

    <div class="profile-info">
        <p>Email: <?php echo htmlspecialchars($_SESSION['user_email']); ?></p>
    </div>
    <div class="hr2"></div>
    <div class="order_complete">
        <div class="filter-container">
            <label for="status-filter">Фильтр по статусу:</label><br>
            <select id="status-filter" name="status">
                <option value="all" <?php if ($status == 'all') echo 'selected'; ?>>Все</option>
                <option value="В обработке" <?php if ($status == 'В обработке') echo 'selected'; ?>>В обработке</option>
                <option value="Завершен" <?php if ($status == 'Завершен') echo 'selected'; ?>>Завершен</option>
                <option value="Отменен" <?php if ($status == 'Отменен') echo 'selected'; ?>>Отменен</option>
            </select>
        </div>
        <div class="table_order_complete">
            <?php if ($result->num_rows > 0) { ?>
                <?php while ($row = $result->fetch_assoc()) { ?>
                    <div class="card_order_complete">
                        <div class="image_order_complete">
                            <?php 
                            $images = explode(', ', $row['product_images']);
                            foreach ($images as $index => $image) {
                                if ($index < 4) {
                                    echo "<img src='$image' alt='Product Image'>";
                                }
                            }
                            $missingImagesCount = 4 - count($images);
                            for ($i = 0; $i < $missingImagesCount; $i++) {
                                echo "<img src='src/product_image/plug.png' alt='Placeholder Image'>";
                            }
                            ?>
                        </div>
                        <div class="right_info_order_complete">
                            <div class="product_id">
                                <span>Заказ №<?php echo $row['order_id']; ?></span>
                            </div>
                            <div class="hr"></div>
                            <div class="structure_order">
                                <span>Состав заказа: 
                                    <?php 
                                    $product_ids = explode(', ', $row['product_ids']);
                                    $product_names = explode(', ', $row['product_names']);
                                    
                                    $links = [];
                                    foreach ($product_ids as $index => $id) {
                                        $name = htmlspecialchars($product_names[$index]);
                                        $links[] = "<a href='full_product.php?id=" . $id . "'>$name</a>";
                                    }
                                    echo implode(', ', $links);
                                    ?>
                                </span>
                            </div>
                            <div class="bottom">
                                <div class="product_quantity">
                                    <span>Количество товаров: <?php echo $row['total_quantity']; ?></span>
                                </div>
                                <div class="order_status">
                                    <span>Статус заказа: <?php echo htmlspecialchars($row['status']); ?></span>
                                </div>
                                <div class="total_price">
                                    <div>
                                        <span>Итоговая стоимость: <?php echo number_format($row['total_price'], 0, ',', ' '); ?> руб.</span>
                                    </div>
                                    <div>
                                        <a href="#" class="cancel-btn" data-order-id="<?php echo $row['order_id']; ?>">Отменить</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            <?php } else { ?>
                <div class="no-orders">
                    <img src="src/icon/no_order.svg">
                    <p>У вас пока нет заказов.</p>
                </div>
            <?php } ?>
        </div>
    </div>
    <form action="php_scripts/logout.php" method="POST">
        <button type="submit" class="logout-btn">Выйти</button>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const statusFilter = document.getElementById('status-filter');
        statusFilter.addEventListener('change', function() {
            const selectedStatus = this.value;
            window.location.href = '?status=' + encodeURIComponent(selectedStatus);
        });

        const cancelButtons = document.querySelectorAll('.cancel-btn');
        cancelButtons.forEach(button => {
            button.addEventListener('click', function(event) {
                event.preventDefault();
                const orderId = this.getAttribute('data-order-id');

                fetch('php_scripts/cancel_order.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: 'order_id=' + encodeURIComponent(orderId)
                })
                .then(response => {
                    if (response.ok) {
                        alert('Заказ отменён');
                        location.reload();
                    } else {
                        alert('Ошибка отмены заказа');
                    }
                })
                .catch(error => {
                    console.error('Ошибка:', error);
                    alert('Произошла ошибка. Попробуйте снова.');
                });
            });
        });
    });
</script>
</body>
</html>

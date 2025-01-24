<?php
require 'config.php';

session_start();
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Пользователь не авторизован']);
    exit();
}

// Получаем данные из POST запроса
$orderData = json_decode(file_get_contents('php://input'), true);

if (!$orderData || empty($orderData['address']) || empty($orderData['products']) || !is_array($orderData['products'])) {
    echo json_encode(['error' => 'Некорректные данные заказа']);
    exit();
}

$userId = $_SESSION['user_id'];
$address = htmlspecialchars(trim($orderData['address']));

// Создаем новую запись в таблице orders с временным total_price = 0
$insertOrderSql = "INSERT INTO orders (user_id, total_price, delivery_address) VALUES (?, 0, ?)";
$insertOrderStmt = $conn->prepare($insertOrderSql);
$insertOrderStmt->bind_param("is", $userId, $address);
$insertOrderStmt->execute();

if ($insertOrderStmt->affected_rows <= 0) {
    echo json_encode(['error' => 'Ошибка при создании заказа']);
    exit();
}

$order_id = $insertOrderStmt->insert_id;
$insertOrderStmt->close();

$totalPrice = 0; // Инициализируем переменную для хранения общей стоимости

// Создаем записи в таблице order_details для каждого товара
$insertOrderDetailsSql = "INSERT INTO order_details (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)";
$insertOrderDetailsStmt = $conn->prepare($insertOrderDetailsSql);

foreach ($orderData['products'] as $product) {
    $productId = $product['id'];
    $quantity = $product['quantity'];
    $price = (float) $product['price']; // Преобразуем цену товара к числу с плавающей точкой

    $totalPrice += $price * $quantity; // Увеличиваем общую стоимость

    $insertOrderDetailsStmt->bind_param("iiid", $order_id, $productId, $quantity, $price);
    $insertOrderDetailsStmt->execute();

    if ($insertOrderDetailsStmt->affected_rows <= 0) {
        echo json_encode(['error' => 'Ошибка при добавлении товара в заказ']);
        exit();
    }
}

$insertOrderDetailsStmt->close();

// Обновляем значение total_price в таблице orders
$updateOrderSql = "UPDATE orders SET total_price = ? WHERE id = ?";
$updateOrderStmt = $conn->prepare($updateOrderSql);
$updateOrderStmt->bind_param("di", $totalPrice, $order_id);
$updateOrderStmt->execute();
$updateOrderStmt->close();

// Очистка корзины для текущего пользователя после успешного создания заказа
$clearCartSql = "DELETE FROM cart WHERE user_id = ?";
$clearCartStmt = $conn->prepare($clearCartSql);
$clearCartStmt->bind_param("i", $userId);
$clearCartStmt->execute();
$clearCartStmt->close();

echo json_encode(['success' => true]);
exit();
?>

<?php
require 'config.php';
session_start();

// Получение ID пользователя из сессии
$userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

// Проверяем, авторизован ли пользователь
if ($userId) {
    // Инициализация переменной для хранения общего количества и общей стоимости
    $totalQuantity = 0;
    $totalPrice = 0;

    // Запрос к базе данных для получения товаров из корзины
    $cartSql = "SELECT c.quantity, c.price, p.id, p.name, p.description, p.photo_path 
                FROM cart c 
                JOIN products p ON c.product_id = p.id 
                WHERE c.user_id = ?";
    
    $stmt = $conn->prepare($cartSql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Формируем HTML для блока catalog
    $catalogHtml = '';
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $totalQuantity += $row['quantity'];
            $totalPrice += $row['price'] * $row['quantity'];
            $catalogHtml .= '<div class="product-card" data-product-id="' . $row['id'] . '">';
            $catalogHtml .= '<img src="' . $row['photo_path'] . '" alt="' . $row['name'] . '">';
            $catalogHtml .= '<h3>' . $row['name'] . '</h3>';
            $catalogHtml .= '<p class="price">Цена: ' . number_format($row['price'], 0, ',', ' ') . ' руб.</p>';
            $catalogHtml .= '<div class="quantity-wrapper" data-product-id="' . $row['id'] . '">';
            $catalogHtml .= '<button class="quantity-btn minus">−</button>';
            $catalogHtml .= '<input type="number" class="quantity-input" value="' . $row['quantity'] . '" min="0" readonly>';
            $catalogHtml .= '<button class="quantity-btn plus">+</button>';
            $catalogHtml .= '</div>';
            $catalogHtml .= '</div>';
        }
    } else {
        $catalogHtml .= '<span class = "empty_cart">Ваша корзина пуста.</span>';
    }
    $stmt->close();

    $totalContainerHtml = '';
   
        $totalContainerHtml .= '
            <div class="client_service">
                <div class="image_client_service">
                    <img src="src/icon/client_service.svg">
                </div>
                <div class="title_client_service">
                    <span>Если у вас возникли трудности, можете <a href="">связаться с нами</a></span>
                </div>
            </div>';

        $totalContainerHtml .= '
            <div class="add_to_cart_info">
                <div class="flex-container">
                    <div class="label">Товары:</div>
                    <div class="value total-quantity">' . $totalQuantity . '</div>
                </div>
                <div class="flex-container">
                    <div class="label">Цена:</div>
                    <div class="value total-price">' . number_format($totalPrice, 0, ',', ' ') . ' руб.</div>
                </div>
            </div>
            <div class="order_btn">
                <a href="#" id="order-button">Оформить заказ</a>
            </div>';


    // Возвращаем HTML в формате JSON
    echo json_encode([
        'catalog' => $catalogHtml,
        'totalContainer' => $totalContainerHtml
    ]);
} else {
    echo json_encode(['error' => 'Пользователь не авторизован']);
}
?>

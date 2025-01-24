<?php 
require 'config.php';
session_start();

// Получение ID пользователя из сессии
$userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

// Обработка обновления количества товаров в корзине через AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Получение данных из JSON
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (isset($input['product_id'])) {
        $productId = intval($input['product_id']);
        $quantity = max(0, intval($input['quantity']));

        // Проверяем, авторизован ли пользователь
        if ($userId) {
            if ($quantity > 0) {
                // Обновляем количество товара в корзине
                $updateSql = "UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?";
                $updateStmt = $conn->prepare($updateSql);
                $updateStmt->bind_param("iii", $quantity, $userId, $productId);
                $updateStmt->execute();
                $updateStmt->close();
            } else {
                // Если количество равно 0, удаляем товар из корзины
                $deleteSql = "DELETE FROM cart WHERE user_id = ? AND product_id = ?";
                $deleteStmt = $conn->prepare($deleteSql);
                $deleteStmt->bind_param("ii", $userId, $productId);
                $deleteStmt->execute();
                $deleteStmt->close();
            }

            // Получаем обновлённую стоимость товара
            $priceSql = "SELECT price FROM products WHERE id = ?";
            $priceStmt = $conn->prepare($priceSql);
            $priceStmt->bind_param("i", $productId);
            $priceStmt->execute();
            $priceStmt->bind_result($price);
            $priceStmt->fetch();
            $priceStmt->close();

            // Возвращаем обновлённые данные
            echo json_encode(['quantity' => $quantity, 'total' => $price * $quantity]);
        } else {
            // Если пользователь не авторизован
            echo json_encode(['error' => 'Пользователь не авторизован']);
        }
    } else {
        // Если параметр product_id не передан
        echo json_encode(['error' => 'Неверный запрос']);
    }
    exit();
} else {
    // Если запрос не является POST
    echo json_encode(['error' => 'Неверный запрос']);
    exit();
}
?>

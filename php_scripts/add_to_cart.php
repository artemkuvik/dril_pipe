<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Получаем данные из POST-запроса
    $user_id = $_POST['user_id'];
    $product_id = $_POST['product_id'];
    $price = $_POST['price'];

    // Проверка на существование товара в корзине
    $check_sql = "SELECT * FROM cart WHERE user_id = ? AND product_id = ?";
    $stmt = $conn->prepare($check_sql);
    $stmt->bind_param("ii", $user_id, $product_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Товар уже в корзине, увеличиваем количество
        $row = $result->fetch_assoc();
        $new_quantity = $row['quantity'] + 1;

        $update_sql = "UPDATE cart SET quantity = ?, price = ? WHERE user_id = ? AND product_id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("idii", $new_quantity, $price, $user_id, $product_id);
        $update_stmt->execute();
    } else {
        // Товар не в корзине, добавляем его
        $insert_sql = "INSERT INTO cart (user_id, product_id, quantity, price) VALUES (?, ?, ?, ?)";
        $insert_stmt = $conn->prepare($insert_sql);
        $quantity = 1; // Начальное количество
        $insert_stmt->bind_param("iiid", $user_id, $product_id, $quantity, $price);
        $insert_stmt->execute();
    }

    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, "message" => "Invalid request"]);
}
?>

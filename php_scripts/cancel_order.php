<?php
require 'config.php';
session_start();

// Проверка, авторизован ли пользователь
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

// Проверка, передан ли order_id
if (isset($_POST['order_id'])) {
    $order_id = intval($_POST['order_id']);
    $user_id = $_SESSION['user_id'];

    // Подготовка запроса для отмены заказа
    $query = "UPDATE orders SET status = 'Отменен' WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $order_id, $user_id);

    if ($stmt->execute()) {
        // Успешная отмена
        header('Location: ../profile.php?message=Заказ отменен');
    } else {
        // Ошибка при отмене
        header('Location: ../profile.php?message=Ошибка отмены заказа');
    }
} else {
    // Если order_id не передан
    header('Location: ../profile.php?message=Некорректный запрос');
}
?>

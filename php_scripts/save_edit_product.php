<?php
// Подключение к базе данных
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Получаем данные из формы
    $productId = $_POST['product_id'];
    $productName = mysqli_real_escape_string($conn, $_POST['name']);
    $productPrice = (float)$_POST['price'];
    $productDescription = mysqli_real_escape_string($conn, $_POST['description']);
    $categoryId = (int)$_POST['category_id'];

    // Проверяем, что все необходимые данные получены
    if ($productId && $productName && $productPrice && $productDescription && $categoryId) {
        // Обновляем данные товара в базе данных
        $updateQuery = "UPDATE products 
                        SET name = '$productName', price = $productPrice, description = '$productDescription', category_id = $categoryId 
                        WHERE id = $productId";

        if (mysqli_query($conn, $updateQuery)) {
            echo "<script>alert('Изменения успешно сохранены.'); window.location.href = '../admin.php';</script>";
        } else {
            echo "<script>alert('Ошибка при сохранении изменений: " . mysqli_error($conn) . "'); window.location.href = '../admin.php';</script>";
        }
    } else {
        echo "<script>alert('Необходимо заполнить все поля.'); window.location.href = '../admin.php';</script>";
    }
} else {
    echo "<script>alert('Некорректный запрос.'); window.location.href = '../admin.php';</script>";
}

// Закрытие подключения к базе данных
mysqli_close($conn);
?>

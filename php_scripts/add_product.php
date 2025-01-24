<?php
require 'config.php';
session_start(); // Не забудьте начать сессию

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

// Если пользователь не является администратором, перенаправляем его на index.php
if (!$admin_row || $admin_row['admin'] != 1) {
    echo "У вас нет прав для добавления товара."; // Сообщение для неадминистраторов
    exit; // Прекращаем выполнение скрипта
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $product_name = $_POST['product_name'];
    $category_id = $_POST['category_id'];
    $price = $_POST['price'];
    $description = $_POST['description']; // Получаем значение поля для 

    // Обработка загрузки файла
    $target_dir = "../src/product_image/"; // Путь для перемещения файла
    $db_path = "src/product_image/"; // Путь для записи в базу данных
    $file_name = basename($_FILES["photo_path"]["name"]);
    $target_file = $target_dir . $file_name;
    $db_file_path = $db_path . $file_name;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Загружаем файл без проверок
    if (move_uploaded_file($_FILES["photo_path"]["tmp_name"], $target_file)) {
        // Подготовка запроса на добавление продукта (в БД путь без ../)
        $add_product_query = "INSERT INTO products (name, category_id, description, photo_path, price) VALUES (?, ?, ?, ?, ?)";
        $add_product_stmt = $conn->prepare($add_product_query);
        $add_product_stmt->bind_param("sissi", $product_name, $category_id, $description, $db_file_path, $price);

        // Выполнение запроса
        if ($add_product_stmt->execute()) {
            echo "Продукт успешно добавлен.";
        } else {
            echo "Ошибка: " . $conn->error;
        }

        // Закрываем подготовленный запрос
        $add_product_stmt->close();
    } else {
        echo "Извините, возникла ошибка при загрузке вашего файла.";
    }

    // Закрываем соединение
    $conn->close();
}
?>

<?php
session_start(); // Стартуем сессию
require 'config.php';

// Получение данных из формы входа
$email = $_POST['email'];
$password = $_POST['password'];

// Подготовка SQL запроса для поиска пользователя
$sql = "SELECT * FROM users WHERE email = '$email'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Если пользователь найден, проверяем пароль
    $row = $result->fetch_assoc();
    if (password_verify($password, $row['password'])) {
        // Сохраняем данные пользователя в сессию
        $_SESSION['user_id'] = $row['id'];
        $_SESSION['user_name'] = $row['name'];
        $_SESSION['user_email'] = $row['email'];

        header('Location: ../profile.php');
        exit(); // Прекращаем выполнение скрипта после перенаправления
    } else {
        echo "Неверный пароль. Попробуйте еще раз.";
    }
} else {
    echo "Пользователь с таким email не найден.";
}

// Закрытие подключения
$conn->close();
?>

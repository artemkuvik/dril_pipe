<?php
session_start(); // Стартуем сессию
require 'config.php';

// Получение данных из формы регистрации
$name = $_POST['name'];
$email = $_POST['email'];
$password = $_POST['password'];

// Хэширование пароля
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Подготовка SQL запроса для добавления нового пользователя
$sql = "INSERT INTO users (name, email, password) VALUES ('$name', '$email', '$hashed_password')";

// Выполнение SQL запроса
if ($conn->query($sql) === TRUE) {
    // Получаем ID последнего вставленного пользователя
    $user_id = $conn->insert_id;

    // Сохраняем данные пользователя в сессию
    $_SESSION['user_id'] = $user_id;
    $_SESSION['user_name'] = $name;
    $_SESSION['user_email'] = $email;

    header('Location: ../profile.php');
    exit(); // Прекращаем выполнение скрипта после перенаправления
} else {
    echo "Ошибка: " . $sql . "<br>" . $conn->error;
}

// Закрытие подключения
$conn->close();
?>

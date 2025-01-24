<?php
require 'config.php';
$conn = new mysqli($servername, $username, $password, $dbname);

// Проверка соединения
if ($conn->connect_error) {
    die("Ошибка соединения: " . $conn->connect_error);
}

// Получение данных из формы
$user_id = $_POST['user_id'];
$service_id = $_POST['service_id'];
$date = date('Y-m-d'); // Установка сегодняшней даты
$issue = $_POST['issue'];
$status = 'В обработке'; // Начальный статус

// SQL-запрос для вставки данных в таблицу applications
$sql = "INSERT INTO applications (user_id, service_id, date, status, issue) VALUES (?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iisss", $user_id, $service_id, $date, $status, $issue);

if ($stmt->execute()) {
    // Редирект на страницу ../index.php при успешной отправке
    header("Location: ../index.php");
    exit(); // Завершаем скрипт после редиректа
} else {
    echo "Ошибка: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>

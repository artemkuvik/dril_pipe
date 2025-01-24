<?php
session_start();
require_once 'config.php'; // Подключение к базе данных

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Проверяем, авторизован ли пользователь и существует ли в сессии user_id
    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
        $feedback_text = isset($_POST['message']) ? trim($_POST['message']) : '';

        // Проверка на наличие текста сообщения
        if (!empty($feedback_text)) {
            // Подготовка SQL-запроса для вставки данных в таблицу feedback
            $stmt = $conn->prepare("INSERT INTO feedback (user_id, feedback_text, feedback_date) VALUES (?, ?, NOW())");

            if ($stmt) {
                // Привязываем параметры (user_id и feedback_text) к запросу
                $stmt->bind_param("is", $user_id, $feedback_text);

                // Выполняем запрос
                if ($stmt->execute()) {
                    echo "Ваше сообщение успешно отправлено!";
                } else {
                    echo "Не удалось отправить отзыв. Попробуйте позже.";
                }

                // Закрываем запрос
                $stmt->close();
            } else {
                echo "Ошибка подготовки запроса: " . $conn->error;
            }
        } else {
            echo "Сообщение не может быть пустым.";
        }
    } else {
        echo "Вы не авторизованы. Пожалуйста, войдите в систему.";
    }
} else {
    echo "Неправильный метод запроса.";
}

// Закрываем соединение с базой данных
$conn->close();
?>

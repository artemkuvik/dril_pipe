<?php
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Получаем данные из формы
    $service_name = htmlspecialchars(trim($_POST['service_name']));
    $service_description = htmlspecialchars(trim($_POST['description']));
    $service_cost = $_POST['cost'];

    // Обработка загружаемого файла (Фото услуги)
    if (isset($_FILES['photo_path']) && $_FILES['photo_path']['error'] == 0) {
        $file_tmp_name = $_FILES['photo_path']['tmp_name'];
        $file_name = basename($_FILES['photo_path']['name']);
        $file_extension = pathinfo($file_name, PATHINFO_EXTENSION);

        // Генерация уникального имени для файла
        $new_file_name = uniqid('service_', true) . '.' . $file_extension;

        // Путь для записи файла в папку на сервере
        $upload_dir = '../src/service_image/'; // Путь для загрузки файла на сервере

        // Проверяем, что файл является изображением
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array(strtolower($file_extension), $allowed_extensions)) {
            // Создаем папку, если ее нет
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            // Перемещаем файл в нужную папку
            if (move_uploaded_file($file_tmp_name, $upload_dir . $new_file_name)) {
                $photo_path = 'src/service_image/' . $new_file_name; // Путь для записи в базу данных
            } else {
                $error_message = "Ошибка при загрузке файла.";
            }
        } else {
            $error_message = "Недопустимый формат файла. Разрешены только изображения.";
        }
    } else {
        $photo_path = null; // Если фото не было загружено, то оставляем поле пустым
    }

    // Подключение к базе данных и добавление записи
    if (!isset($error_message)) {
        try {
            // Используем переменную $conn для выполнения запроса
            $stmt = $conn->prepare("INSERT INTO services (name, description, photo, cost) VALUES (?, ?, ?, ?)");
            
            // Привязываем параметры
            $stmt->bind_param("sssd", $service_name, $service_description, $photo_path, $service_cost);

            // Выполняем запрос
            $stmt->execute();

            // Проверка, добавлена ли услуга в базу данных
            if ($stmt->affected_rows > 0) {
                echo "Услуга успешно добавлена!";
            } else {
                echo "Ошибка при добавлении услуги.";
            }

            // Закрытие подготовленного запроса
            $stmt->close();

        } catch (mysqli_sql_exception $e) {
            // Обработка ошибок
            echo "Ошибка: " . $e->getMessage();
        }
    } else {
        // Если есть ошибка с загрузкой файла
        echo $error_message;
    }
} else {
    echo "Неверный запрос!";
}
?>

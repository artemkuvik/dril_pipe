<?php
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Получаем данные из формы
    $service_id = $_POST['service_id'];
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $cost = floatval($_POST['cost']); // Преобразуем стоимость в число с плавающей запятой

    // Обработка фото
    $photoUpdated = false;
    $photoPath = ''; // Начальная пустая переменная для пути к фото

    // Проверка на наличие загруженного файла
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] == UPLOAD_ERR_OK) {
        $photoTmpName = $_FILES['photo']['tmp_name'];
        $photoName = uniqid() . '-' . basename($_FILES['photo']['name']); // Уникальное имя для фото
        $photoDir = '../src/service_image/'; // Директория на сервере, куда будет загружаться фото

        // Проверка существования папки и создание, если она не существует
        if (!is_dir($photoDir)) {
            if (!mkdir($photoDir, 0755, true)) {
                die("Ошибка: не удалось создать директорию $photoDir. Проверьте права на запись.");
            }
        }

        $photoPath = $photoDir . $photoName;

        // Перемещаем загруженное изображение в папку
        if (move_uploaded_file($photoTmpName, $photoPath)) {
            $photoUpdated = true; // Фото успешно загружено
        } else {
            die("Ошибка: не удалось переместить файл. Проверьте права доступа к папке $photoDir.");
        }
    } else {
//        echo "Ошибка загрузки файла: " . $_FILES['photo']['error'];
    }

    // Формируем запрос для обновления записи
    $sql = "UPDATE services SET name = '$name', description = '$description', cost = $cost";

    // Если фото обновлено, добавляем путь к фото в запрос
    if ($photoUpdated) {
        // В базе данных сохраняем путь без ../
        $photoPathForDB = 'src/service_image/' . $photoName; 
        $sql .= ", photo = '$photoPathForDB'";
    }

    $sql .= " WHERE id = $service_id";

    // Выполняем запрос
    if ($conn->query($sql) === TRUE) {
        header("Location: ../admin.php?success=1"); // Перенаправление после успешного обновления
        exit();
    } else {
        echo "Ошибка при обновлении услуги: " . $conn->error;
    }

    $conn->close();
} else {
    header("Location: ../admin.php");
    exit();
}
?>

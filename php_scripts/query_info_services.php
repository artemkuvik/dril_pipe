<?php
include 'config.php';

// SQL запрос для получения всех записей из таблицы `services`
$query = "SELECT id, name AS title, description, photo AS image, cost FROM services";
$result = mysqli_query($conn, $query);

$services = [];

if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        // Округляем стоимость до целого числа
        $row['cost'] = round($row['cost']);
        $services[] = $row;
    }
}

// Возвращаем данные в формате JSON
echo json_encode($services);
?>

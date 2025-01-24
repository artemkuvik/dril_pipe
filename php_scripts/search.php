<?php
require_once 'config.php';

// Получаем поисковый запрос
$query = isset($_GET['query']) ? $_GET['query'] : '';

// Подготавливаем запрос к базе данных
$sql = "SELECT products.id, products.name, products.description, products.photo_path, products.price, categories.category_name 
        FROM products 
        INNER JOIN categories ON products.category_id = categories.id 
        WHERE products.name LIKE ?";

// Подготавливаем и выполняем запрос
$stmt = $conn->prepare($sql);
$searchTerm = '%' . $query . '%';
$stmt->bind_param("s", $searchTerm);
$stmt->execute();
$result = $stmt->get_result();

// Проверяем, есть ли результаты
$products = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
}

// Возвращаем результаты в формате JSON
header('Content-Type: application/json');
echo json_encode($products);

// Закрываем соединение с базой данных
$stmt->close();
$conn->close();
?>

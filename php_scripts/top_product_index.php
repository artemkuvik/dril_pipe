<?php
include 'config.php';

$sql = "SELECT p.id, p.name, p.photo_path, p.price, c.category_name AS category, SUM(od.quantity) as total_quantity
        FROM products p
        JOIN order_details od ON p.id = od.product_id
        JOIN categories c ON p.category_id = c.id
        GROUP BY p.id
        ORDER BY total_quantity DESC
        LIMIT 4";

$result = $conn->query($sql);
$popular_products = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Форматируем цену, убирая .00, если они есть
        $formatted_price = number_format($row['price'], 2, '.', ''); // Форматируем цену с двумя знаками после запятой
        $formatted_price = rtrim($formatted_price, '0'); // Убираем лишние нули

        if (substr($formatted_price, -1) == '.') {
            $formatted_price = rtrim($formatted_price, '.'); // Убираем точку, если она в конце
        }

        $popular_products[] = [
            'id' => $row['id'],
            'name' => $row['name'],
            'photo_path' => $row['photo_path'],
            'price' => $formatted_price, // Используем отформатированную цену
            'category' => $row['category'] // Добавляем категорию
        ];
    }
}

header('Content-Type: application/json');
echo json_encode($popular_products);
?>

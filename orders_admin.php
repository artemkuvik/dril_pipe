<?php 
require 'php_scripts/config.php';

// Получаем все заказы
$orders_query = "SELECT o.id, o.user_id, o.order_date, o.total_price, o.status, o.delivery_address, u.name AS user_name 
                 FROM orders o
                 JOIN users u ON o.user_id = u.id";
$orders_result = $conn->query($orders_query);

// Проверка, есть ли заказы
if ($orders_result->num_rows > 0) {
    echo '<div class="table_order"><table border="1" cellpadding="10" cellspacing="0">';
    echo '<tr>
            <th>ID Заказа</th>
            <th>Пользователь</th>
            <th>Дата заказа</th>
            <th>Сумма</th>
            <th>Статус</th>
            <th>Адрес доставки</th>
            <th>Изменить статус</th>
            <th>Состав заказа</th>
          </tr>';
    
    // Выводим каждый заказ
    while ($order = $orders_result->fetch_assoc()) {
        echo '<tr>';
        echo '<td>' . htmlspecialchars($order['id']) . '</td>';
        echo '<td>' . htmlspecialchars($order['user_name']) . '</td>';
        echo '<td>' . htmlspecialchars($order['order_date']) . '</td>';
        echo '<td>' . htmlspecialchars($order['total_price']) . '</td>';
        echo '<td>' . htmlspecialchars($order['status']) . '</td>';
        echo '<td>' . htmlspecialchars($order['delivery_address']) . '</td>';
        echo '<td>
                <form method="POST" action="">
                    <select name="status">
                        <option value="В обработке"' . ($order['status'] == 'В обработке' ? ' selected' : '') . '>В обработке</option>
                        <option value="Отменен"' . ($order['status'] == 'Отменен' ? ' selected' : '') . '>Отменен</option>
                        <option value="Доставлен"' . ($order['status'] == 'Доставлен' ? ' selected' : '') . '>Доставлен</option>
                    </select>
                    <input type="hidden" name="order_id" value="' . htmlspecialchars($order['id']) . '">
                    <input type="submit" value="Обновить">
                </form>
              </td>';
        
        // Получаем состав заказа
        $order_details_query = "SELECT p.name, od.quantity, od.price 
                                FROM order_details od
                                JOIN products p ON od.product_id = p.id
                                WHERE od.order_id = ?";
        $details_stmt = $conn->prepare($order_details_query);
        $details_stmt->bind_param("i", $order['id']);
        $details_stmt->execute();
        $order_details_result = $details_stmt->get_result();

        echo '<td><table border="1" cellpadding="5" cellspacing="0">';
        echo '<tr><th>Товар</th><th>Количество</th><th>Цена</th></tr>';

        // Выводим каждую позицию в заказе
        while ($detail = $order_details_result->fetch_assoc()) {
            echo '<tr>';
            echo '<td>' . htmlspecialchars($detail['name']) . '</td>';
            echo '<td>' . htmlspecialchars($detail['quantity']) . '</td>';
            echo '<td>' . htmlspecialchars($detail['price']) . '</td>';
            echo '</tr>';
        }
        echo '</table></td>';

        echo '</tr>';
    }
    echo '</table></div>';
} else {
    echo '<p>Заказы не найдены.</p>';
}

// Обработка изменения статуса заказа
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['order_id'])) {
    $order_id = $_POST['order_id'];
    $new_status = $_POST['status'];

    $update_query = "UPDATE orders SET status = ? WHERE id = ?";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bind_param("si", $new_status, $order_id);
    
    if ($update_stmt->execute()) {
        echo '<p>Статус заказа обновлен успешно!</p>';
        echo '<meta http-equiv="refresh" content="0">'; // Обновление страницы
    } else {
        echo '<p>Ошибка обновления статуса заказа: ' . $conn->error . '</p>';
    }
}
?>

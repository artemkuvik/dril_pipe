<?php
require 'php_scripts/config.php';

// Обрабатываем AJAX-запрос на изменение статуса
if (isset($_POST['update_status']) && isset($_POST['application_id']) && isset($_POST['new_status'])) {
    $application_id = $_POST['application_id'];
    $new_status = $_POST['new_status'];
    
    $sql = "UPDATE applications SET status = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $new_status, $application_id);
    
    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Статус заявки обновлен."]);
    } else {
        echo json_encode(["success" => false, "message" => "Ошибка обновления статуса."]);
    }
    $stmt->close();
    exit;
}

// Получаем все заявки с данными пользователя и услуги
$sql = "
    SELECT applications.id, users.email, services.name AS service_name, applications.date, applications.status, applications.issue
    FROM applications
    JOIN users ON applications.user_id = users.id
    JOIN services ON applications.service_id = services.id
";
$result = $conn->query($sql);
?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        function updateStatus(applicationId) {
            const newStatus = document.querySelector(`#status-${applicationId}`).value;
            
            $.ajax({
                url: 'service_inquiries_admin.php',
                type: 'POST',
                data: {
                    update_status: true,
                    application_id: applicationId,
                    new_status: newStatus
                },
                success: function(response) {
                    const data = JSON.parse(response);
                    alert(data.message);
                },
                error: function() {
                    alert('Ошибка при обновлении статуса.');
                }
            });
        }
    </script>


<table class="table_order">
    <thead>
        <tr>
            <th>ID</th>
            <th>Пользователь (Email)</th>
            <th>Услуга</th>
            <th>Дата</th>
            <th>Статус</th>
            <th>Вопрос</th>
            <th>Изменить статус</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo $row['email']; ?></td>
                    <td><?php echo $row['service_name']; ?></td>
                    <td><?php echo $row['date']; ?></td>
                    <td><?php echo $row['status']; ?></td>
                    <td><?php echo $row['issue']; ?></td>
                    <td>
                        <select id="status-<?php echo $row['id']; ?>" onchange="updateStatus(<?php echo $row['id']; ?>)">
                            <option value="В обработке" <?php if($row['status'] == 'В обработке') echo 'selected'; ?>>В обработке</option>
                            <option value="Завершено" <?php if($row['status'] == 'Завершено') echo 'selected'; ?>>Завершено</option>
                            <option value="Отменено" <?php if($row['status'] == 'Отменено') echo 'selected'; ?>>Отменено</option>
                        </select>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="7">Нет заявок</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>


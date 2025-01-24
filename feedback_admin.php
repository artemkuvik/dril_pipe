<?php
require 'php_scripts/config.php';

// Проверка, авторизован ли пользователь
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

// Получаем ID текущего пользователя
$user_id = $_SESSION['user_id'];

// Проверяем, является ли пользователь администратором
$admin_check_query = "SELECT admin FROM users WHERE id = ?";
$admin_stmt = $conn->prepare($admin_check_query);
$admin_stmt->bind_param("i", $user_id);
$admin_stmt->execute();
$admin_result = $admin_stmt->get_result();
$admin_row = $admin_result->fetch_assoc();

// Если пользователь не является администратором, перенаправляем его на index.php
if (!$admin_row || $admin_row['admin'] != 1) {
    header('Location: index.php');
    exit();
}

// SQL-запрос для получения обращений вместе с email пользователей
$feedback_query = "
    SELECT feedback.feedback_text, feedback.feedback_date, users.email 
    FROM feedback 
    JOIN users ON feedback.user_id = users.id 
    ORDER BY feedback.feedback_date DESC
";
$feedback_result = $conn->query($feedback_query);
?>


    <div class="feedback-list">
        <?php if ($feedback_result->num_rows > 0): ?>
            <table class="table_order">
                <thead>
                    <tr>
                        <th>Email пользователя</th>
                        <th>Сообщение</th>
                        <th>Дата обращения</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($feedback = $feedback_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($feedback['email']); ?></td>
                            <td><?php echo htmlspecialchars($feedback['feedback_text']); ?></td>
                            <td><?php echo htmlspecialchars($feedback['feedback_date']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>Обращений пока нет.</p>
        <?php endif; ?>
    </div>

</body>
</html>

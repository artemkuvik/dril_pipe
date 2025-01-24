<?php
require 'php_scripts/config.php';

// Получаем все услуги из таблицы services
$sql = "SELECT * FROM services";
$result = $conn->query($sql);
?>

<div class="table_order">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Название</th>
                <th>Описание</th>
                <th>Фото</th>
                <th>Стоимость</th>
                <th>Действия</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                        <td><?php echo htmlspecialchars($row['description']); ?></td>
                        <td>
                            <?php if ($row['photo']): ?>
                                <img loading="lazy" src="<?php echo htmlspecialchars($row['photo']); ?>" alt="Фото услуги" class="service-photo" style="width: 200px; height: 200px; object-fit: cover;">
                            <?php else: ?>
                                Нет фото
                            <?php endif; ?>
                        </td>
                        <td><?php echo number_format($row['cost'], 2, ',', ' '); ?> ₽</td>
                        <td>
                            <a href="#" class="edit-service-btn" data-service-id="<?php echo $row['id']; ?>">Изменить</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6">Нет доступных услуг</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<!-- Модальное окно для редактирования услуги -->
<div id="editServiceModal" class="modal" style="display: none;">
    <div class="modal-content">
        <span class="close-btn" id="close_services">&times;</span>
        <h2>Редактирование услуги</h2>
        <form id="editServiceForm" method="POST" action="php_scripts/save_edit_service.php" enctype="multipart/form-data">
            <input type="hidden" name="service_id" id="modal_service_id">
            <div>
                <label for="modal_service_name">Название:</label>
                <input type="text" name="name" id="modal_service_name" required>
            </div>
            <div>
                <label for="modal_service_description">Описание:</label>
                <textarea name="description" id="modal_service_description" required></textarea>
            </div>
            <div>
                <label for="modal_service_cost">Стоимость:</label>
                <input type="number" name="cost" id="modal_service_cost" step="0.01" required>
            </div>
            <div>
                <label for="modal_service_photo">Фото:</label>
                <input type="file" name="photo" id="modal_service_photo" accept="image/*">
            </div>
            <button type="submit">Сохранить изменения</button>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('editServiceModal');
    const closeBtn = document.getElementById('close_services'); // Выбор по id

    // Открытие модального окна при клике на "Изменить"
    document.querySelectorAll('.edit-service-btn').forEach(button => {
        button.addEventListener('click', function(event) {
            event.preventDefault(); // Предотвращаем переход по ссылке
            const serviceId = this.getAttribute('data-service-id');
            loadServiceData(serviceId);

            // Открываем модальное окно
            modal.style.display = 'block';
        });
    });

    // Закрытие модального окна
    closeBtn.onclick = function() {
        modal.style.display = 'none';
    };

    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = 'none';
        }
    };

    function loadServiceData(serviceId) {
        // Ищем строку с данными услуги
        const serviceRow = document.querySelector(`a[data-service-id="${serviceId}"]`).closest('tr');
        document.getElementById('modal_service_id').value = serviceId;
        document.getElementById('modal_service_name').value = serviceRow.children[1].textContent;
        document.getElementById('modal_service_description').value = serviceRow.children[2].textContent;

        // Корректное преобразование стоимости
        const costText = serviceRow.children[4].textContent.replace(' ₽', '').replace(/\s/g, '').replace(',', '.');
        document.getElementById('modal_service_cost').value = parseFloat(costText).toFixed(2);
    }
});
</script>


<?php
// Закрытие подключения к базе данных
$conn->close();
?>

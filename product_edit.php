<?php
// Подключение к базе данных
require 'php_scripts/config.php';

// Получение списка категорий для селектора
$categoryQuery = "SELECT id, category_name FROM categories";
$categoryResult = mysqli_query($conn, $categoryQuery);

// Обработка загрузки файла
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['product_image'])) {
    $productId = $_POST['product_id'];
    $targetDir = "src/product_image/";
    $targetFile = $targetDir . $productId . ".png"; // Путь к файлу (имя файла = ID товара)
    
    // Проверка на ошибки при загрузке файла
    if ($_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
        if (move_uploaded_file($_FILES['product_image']['tmp_name'], $targetFile)) {
            // Обновляем путь в базе данных
            $updateQuery = "UPDATE products SET photo_path = '$targetFile' WHERE id = $productId";
            mysqli_query($conn, $updateQuery);
            echo "<script>alert('Фотография успешно обновлена.');</script>";
        } else {
            echo "<script>alert('Ошибка при загрузке файла.');</script>";
        }
    } else {
        echo "<script>alert('Ошибка при загрузке файла. Код ошибки: " . $_FILES['product_image']['error'] . "');</script>";
    }
}

// Запрос для получения всех товаров
$query = "
    SELECT p.id, p.name, p.price, p.photo_path, p.description, c.category_name, c.id AS category_id 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id"; 
$result = mysqli_query($conn, $query);

if ($result && mysqli_num_rows($result) > 0): ?>
    <div class="table_product">
        <table class="table_order">
            <thead>
                <tr>
                    <th>Фото</th>
                    <th>Название</th>
                    <th>Цена</th>
                    <th>Сплав</th>
                    <th>Категория</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                    <td class="product-image-cell">
                        <img src="<?php echo htmlspecialchars($row['photo_path']); ?>" alt="<?php echo htmlspecialchars($row['name']); ?>" class="product-image" loading="lazy">
                        <form action="" method="POST" enctype="multipart/form-data" class="product-image-form">
                            <input type="hidden" name="product_id" value="<?php echo $row['id']; ?>">
                            <input type="file" name="product_image" accept="image/png" class="file-input" required>
                            <button type="submit" class="submit-button">Сохранить</button>
                        </form>
                    </td>
                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                        <td><?php echo htmlspecialchars($row['price']); ?> руб.</td>
                        <td><?php echo htmlspecialchars($row['description']); ?></td>
                        <td><?php echo htmlspecialchars($row['category_name']); ?></td>
                        <td>
                            <a href="?modal_edit=<?php echo $row['id']; ?>" class="edit-btn" data-product-id="<?php echo $row['id']; ?>">Изменить</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
<?php else: ?>
    <p>Нет доступных товаров.</p>
<?php endif; ?>

<!-- Модальное окно для редактирования товара -->
<div id="editModal" class="modal" style="display: none;">
    <div class="modal-content">
        <span class="close-btn">&times;</span>
        <h2>Редактирование товара</h2>
        <form id="editForm" method="POST" action="php_scripts/save_edit_product.php">
            <input type="hidden" name="product_id" id="modal_product_id">
            <div>
                <label for="modal_product_name">Название:</label>
                <input type="text" name="name" id="modal_product_name" required>
            </div>
            <div>
                <label for="modal_product_price">Цена:</label>
                <input type="number" name="price" id="modal_product_price" required>
            </div>
            <div>
                <label for="modal_product_description">Описание:</label>
                <input type="text" name="description" id="modal_product_description" required>
            </div>
            <div>
                <label for="modal_product_category">Категория:</label>
                <select name="category_id" id="modal_product_category" required>
                    <?php while ($category = mysqli_fetch_assoc($categoryResult)): ?>
                        <option value="<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['category_name']); ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <button type="submit">Сохранить изменения</button>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('editModal');
    const closeBtn = document.querySelector('.close-btn');

    // Открытие модального окна при клике на "Изменить"
    document.querySelectorAll('.edit-btn').forEach(button => {
        button.addEventListener('click', function(event) {
            event.preventDefault(); // Предотвращаем переход по ссылке
            const productId = this.getAttribute('data-product-id');

            // Меняем URL, добавляя параметр modal_edit
            const newUrl = new URL(window.location);
            newUrl.searchParams.set('modal_edit', productId);
            window.history.pushState({}, '', newUrl);

            // Загружаем данные товара в модальное окно
            loadProductData(productId);

            // Открываем модальное окно
            modal.style.display = 'block';
        });
    });

    // Закрытие модального окна
    closeBtn.onclick = function() {
        modal.style.display = 'none';
        clearUrl();
    };

    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = 'none';
            clearUrl();
        }
    };

    // Функция для очистки URL от параметра modal_edit
    function clearUrl() {
        const newUrl = new URL(window.location);
        newUrl.searchParams.delete('modal_edit');
        window.history.pushState({}, '', newUrl);
    }

    // Функция для загрузки данных товара
    function loadProductData(productId) {
        const productRow = document.querySelector(`a[data-product-id="${productId}"]`).closest('tr');
        document.getElementById('modal_product_id').value = productId;
        document.getElementById('modal_product_name').value = productRow.children[1].textContent;
        document.getElementById('modal_product_price').value = productRow.children[2].textContent.replace(' руб.', '');
        document.getElementById('modal_product_description').value = productRow.children[3].textContent;

        // Загрузка текущей категории товара в селектор
        const currentCategory = productRow.children[4].textContent.trim();
        const categorySelect = document.getElementById('modal_product_category');
        for (let option of categorySelect.options) {
            if (option.text === currentCategory) {
                option.selected = true;
                break;
            }
        }
    }

    // Проверка URL на наличие параметра modal_edit
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('modal_edit')) {
        const productId = urlParams.get('modal_edit');
        loadProductData(productId);
        modal.style.display = 'block';
    }
});

</script>

<?php
// Закрытие подключения к базе данных
mysqli_close($conn);
?>

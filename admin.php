<?php 
require 'php_scripts/config.php';
require 'header.php';

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
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" type="text/css" href="src/css/admin.css">
    <title>Админская панель</title>
</head>
<body>
    <div class="start_block">
        <div class="title"><span>Добро пожаловать в панель администратора!</span></div>
    </div>
    <div class="main_block">
        <div class="left_nav">
            <a href="#" class="tab-link active" data-tab="products">Товары</a><br>
            <a href="#" class="tab-link" data-tab="orders">Заказы</a><br>
            <a href="#" class="tab-link" data-tab="inquiries">Обращения</a><br>
            <a href="#" class="tab-link" data-tab="services">Услуги</a><br>
            <a href="#" class="tab-link" data-tab="service_inquiries">Заявки по услугам</a>
            <div class="logout">
                <a href="php_scripts/logout.php">Выход</a>
            </div>
        </div>
        <div class="right_main_info" id="main-info">
            <div class="tab-content" id="products" style="display: block;">
                <div class="display_product">
                    <div class="title_left"><h2>Список товаров</h2></div>
                    <div class="right_btn">
                        <a href="#" class="add-product-button">Добавить товар</a>
                    </div>
                </div>
                <div class="hr"></div>
                <?php 
                require 'product_edit.php'; ?>
            </div>

            <div class="tab-content" id="orders" style="display: none;">
                <h2>Список заказов</h2>
                <div class="hr"></div>
                <?php 
                require 'orders_admin.php'; ?>
            </div>

            <div class="tab-content" id="inquiries" style="display: none;">
                <h2>Обращения</h2>
                <div class="hr"></div>
                <?php 
                require 'feedback_admin.php'; ?>
            </div>

            <div class="tab-content" id="services" style="display: none;">
                <div class="display_product">
                    <div class="title_left"><h2>Список услуг</h2></div>
                    <div class="right_btn">
                        <a href="#" class="add-services-button">Добавить услугу</a>
                    </div>
                </div>
                <div class="hr"></div>
                <?php 
                require 'services_admin.php'; ?>
            </div>

            <div class="tab-content" id="service_inquiries" style="display: none;">
                <h2>Заявки по услугам</h2>
                <div class="hr"></div>
                <?php 
                require 'service_inquiries_admin.php'; ?>
            </div>
        </div>
    </div>


<!-- Модальное окно для добавления услуги -->
<div id="addServiceModal" class="modal" style="display: none;">
    <div class="modal-content-two">
        <span class="close" id="closeServiceModal">&times;</span>
        <h2>Добавить услугу</h2>
        <form id="addServiceForm" action="php_scripts/add_service.php" method="POST" enctype="multipart/form-data">
            <label for="serviceName">Название услуги:</label>
            <input type="text" id="serviceName" name="service_name" required>

            <label for="serviceDescription">Описание:</label>
            <input type="text" id="serviceDescription" name="description" required>

            <label for="serviceCost">Стоимость:</label>
            <input type="number" id="serviceCost" name="cost" required step="0.01">

            <label for="servicePhoto">Фото:</label>
            <input type="file" id="servicePhoto" name="photo_path" accept="image/*" required>

            <button type="submit">Добавить услугу</button>
        </form>
    </div>
</div>

<script>
// Получаем модальное окно для добавления услуги
var serviceModal = document.getElementById("addServiceModal");

// Получаем кнопку, которая открывает модальное окно
var addServiceButtons = document.querySelectorAll('.add-services-button');

// Получаем элемент <span>, который закрывает модальное окно
var serviceCloseBtn = document.getElementById("closeServiceModal");

// При нажатии на кнопку "Добавить услугу"
addServiceButtons.forEach(button => {
    button.onclick = function() {
        // Изменяем URL
        window.history.pushState({}, '', 'admin.php?modal=add_service');
        // Показываем модальное окно
        serviceModal.style.display = "block";
    }
});

// При нажатии на <span> (x), закрываем модальное окно
serviceCloseBtn.onclick = function() {
    serviceModal.style.display = "none";
    // Возвращаем URL в исходное состояние
    window.history.pushState({}, '', 'admin.php');
}

// При нажатии в любом месте вне модального окна закрываем его
window.onclick = function(event) {
    if (event.target == serviceModal) {
        serviceModal.style.display = "none";
        window.history.pushState({}, '', 'admin.php');
    }
}
</script>
<div id="addProductModal" class="modal" style="display: none;">
    <div class="modal-content-two">
        <span class="close" id="closeModal">&times;</span>
        <h2>Добавить товар</h2>
        <form id="addProductForm" action="php_scripts/add_product.php" method="POST" enctype="multipart/form-data">
            <label for="productName">Название товара:</label>
            <input type="text" id="productName" name="product_name" required>

            <label for="productCategory">Категория:</label>
            <select id="productCategory" name="category_id" required>
                <option value="">Выберите категорию</option>
               
                <?php
                $categories_query = "SELECT id, category_name FROM categories";
                $categories_result = $conn->query($categories_query);
                while ($category = $categories_result->fetch_assoc()) {
                    echo "<option value='{$category['id']}'>{$category['category_name']}</option>";
                }
                ?>
            </select>

            <label for="productPrice">Цена:</label>
            <input type="number" id="productPrice" name="price" required step="0.01">

            <label for="productDescription">Описание:</label>
            <input type="text" id="productDescription" name="description" required> <!-- Новое поле для сплава -->

            <label for="productPhoto">Фото:</label>
            <input type="file" id="productPhoto" name="photo_path" accept="image/*" required>

            <button type="submit">Добавить товар</button>
        </form>
    </div>
</div>

<script>
// Получаем модальное окно
var modal = document.getElementById("addProductModal");

// Получаем кнопку, которая открывает модальное окно
var addProductButtons = document.querySelectorAll('.add-product-button');

// Получаем элемент <span>, который закрывает модальное окно
var span = document.getElementById("closeModal");

// При нажатии на кнопку "Добавить товар"
addProductButtons.forEach(button => {
    button.onclick = function() {
        // Изменяем URL
        window.history.pushState({}, '', 'admin.php?modal=add_product');
        // Показываем модальное окно
        modal.style.display = "block";
    }
});

// При нажатии на <span> (x), закрываем модальное окно
span.onclick = function() {
    modal.style.display = "none";
    // Возвращаем URL в исходное состояние
    window.history.pushState({}, '', 'admin.php');
}

// При нажатии в любом месте вне модального окна закрываем его
window.onclick = function(event) {
    if (event.target == modal) {
        modal.style.display = "none";
        window.history.pushState({}, '', 'admin.php');
    }
}
</script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const tabLinks = document.querySelectorAll(".tab-link");
            const tabContents = document.querySelectorAll(".tab-content");

            // Получаем сохраненную вкладку из localStorage
            const activeTab = localStorage.getItem('activeTab') || 'products'; // Задаем вкладку по умолчанию

            // Показываем сохраненную вкладку
            tabContents.forEach(content => {
                content.style.display = 'none';
            });
            document.getElementById(activeTab).style.display = 'block';

            // Устанавливаем активный класс для сохраненной вкладки
            tabLinks.forEach(link => {
                link.classList.remove("active");
                if (link.getAttribute("data-tab") === activeTab) {
                    link.classList.add("active");
                }

                // Добавляем обработчик клика
                link.addEventListener("click", function(event) {
                    event.preventDefault();

                    // Удаляем активный класс у всех ссылок и скрываем все табы
                    tabLinks.forEach(l => l.classList.remove("active"));
                    tabContents.forEach(c => c.style.display = "none");

                    // Добавляем активный класс текущей ссылке и показываем соответствующий таб
                    this.classList.add("active");
                    const tabId = this.getAttribute("data-tab");
                    document.getElementById(tabId).style.display = "block";

                    // Сохраняем выбранную вкладку в localStorage
                    localStorage.setItem('activeTab', tabId);
                });
            });
        });
    </script>
</body>
</html>

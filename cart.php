<?php 
require 'header.php';
require 'php_scripts/config.php';

// Получение ID пользователя из сессии
$userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

// Инициализация переменной для хранения общего количества и общей стоимости
$totalQuantity = 0;
$totalPrice = 0;

// Обработка обновления количества товаров в корзине через AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    $productId = intval($_POST['product_id']);
    $quantity = max(0, min(10, intval($_POST['quantity']))); // Ограничиваем максимальное значение до 10

    // Обновляем количество в базе данных
    $updateSql = "UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?";
    $updateStmt = $conn->prepare($updateSql);
    $updateStmt->bind_param("iii", $quantity, $userId, $productId);
    $updateStmt->execute();
    $updateStmt->close();

    // Получаем обновлённую стоимость
    $priceSql = "SELECT price FROM products WHERE id = ?";
    $priceStmt = $conn->prepare($priceSql);
    $priceStmt->bind_param("i", $productId);
    $priceStmt->execute();
    $priceStmt->bind_result($price);
    $priceStmt->fetch();
    $priceStmt->close();

    // Возвращаем обновлённые данные
    echo json_encode(['quantity' => $quantity, 'total' => $price * $quantity]);
    exit();
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="src/css/cart.css">
    <title>Ваша корзина</title>
</head>
<body>
    <div class="container_cart">
        <div class="catalog">
            <?php
            if ($userId) {
                $cartSql = "SELECT c.quantity, c.price, p.id, p.name, p.description, p.photo_path 
                            FROM cart c 
                            JOIN products p ON c.product_id = p.id 
                            WHERE c.user_id = ?";
                
                $stmt = $conn->prepare($cartSql);
                $stmt->bind_param("i", $userId);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $totalQuantity += $row['quantity'];
                        $totalPrice += $row['price'] * $row['quantity'];
                        echo '<div class="product-card" data-product-id="' . $row['id'] . '">';
                        echo '<img src="' . $row['photo_path'] . '" alt="' . $row['name'] . '">';
                        echo '<h3>' . $row['name'] . '</h3>';
                        echo '<p class="price">Цена: ' . number_format($row['price'], 0, ',', ' ') . ' руб.</p>';
                        echo '<div class="quantity-wrapper" data-product-id="' . $row['id'] . '">';
                        echo '<button class="quantity-btn minus">−</button>';
                        echo '<input type="number" class="quantity-input" value="' . $row['quantity'] . '" min="0" readonly>';
                        echo '<button class="quantity-btn plus">+</button>';
                        echo '</div>';
                        echo '</div>';
                    }
                } else {
                    echo '<span class = "empty_cart">Ваша корзина пуста.</span>';
                }
                
                $stmt->close();
            } else {
                echo '<span class = "non_user">Вы не авторизованы. Пожалуйста, войдите в систему.</span>';
            }
            ?>
        </div>
        
        <div class="total-container">
            <div class="client_service">
                <div class="image_client_service">
                    <img src="src/icon/client_service.svg">
                </div>
                <div class="title_client_service">
                   <span>Если у вас возникли трудности, можете <a href="index.php?openModal=true">связаться с нами</a></span>
                </div>
            </div>
            <div class="add_to_cart_info">
                <?php
                
                    echo '<div class="flex-container">';
                    echo '<div class="label">Товары:</div>';
                    echo '<div class="value total-quantity">' . $totalQuantity . '</div>';
                    echo '</div>';
                    echo '<div class="flex-container">';
                    echo '<div class="label">Цена:</div>';
                    echo '<div class="value total-price">' . number_format($totalPrice, 0, ',', ' ') . ' руб.</div>';
                    echo '</div>';
            
                ?>
            </div>
            <div class="order_btn">
                <a href="#" id="order-button">Оформить заказ</a>
            </div>
        </div>
    </div>

    <!-- Модальное окно -->
    <div id="order-modal" class="modal" style="display: none;">
        <div class="modal-content">
            <span class="close-button" onclick="closeModal()">&times;</span>
            <h2>Оформление заказа</h2>
            <div class="hr"></div>
<form id="order-form">
    <div class="title_delivery">
        <img src="src/icon/delivery.svg">
        <label for="address">Адрес доставки:</label>
    </div>
    <div class="center_input">
        <input type="text" id="address" name="address" placeholder="Г. Москва Ул. Ленина 162" required>
    </div>
    <br>
    <!-- Скрытые поля для передачи данных о товарах -->
    <input type="hidden" id="products-json" name="products" value="">
    <input type="hidden" id="total-quantity" name="total_quantity" value="">
    <input type="hidden" id="total-price" name="total_price" value="">
    
    <button type="submit">Оформить</button>
</form>

            <p>Пожалуйста, заполните необходимые данные для оформления заказа.</p>
            
        </div>
    </div>
<script type="text/javascript">
    document.addEventListener('DOMContentLoaded', function() {
    const orderForm = document.getElementById('order-form');

    orderForm.addEventListener('submit', function(event) {
        event.preventDefault();
        
        const address = document.getElementById('address').value;
        const totalQuantity = document.querySelector('.total-quantity').textContent.trim();
        const totalPrice = document.querySelector('.total-price').textContent.trim();
        
        // Получаем данные о товарах из корзины
        const productsData = [];
        const productCards = document.querySelectorAll('.product-card');
        productCards.forEach(card => {
            const productId = card.getAttribute('data-product-id');
            const productName = card.querySelector('h3').textContent.trim();
            const productQuantity = card.querySelector('.quantity-input').value.trim();
            const productPrice = card.querySelector('.price').textContent.trim().replace(/\D/g, ''); // Убираем все нецифровые символы

            productsData.push({
                id: productId,
                name: productName,
                quantity: productQuantity,
                price: productPrice
            });
        });

        // Формируем JSON строку для передачи данных о товарах
        const productsJson = JSON.stringify(productsData);

        // Заполняем скрытые поля формы
        document.getElementById('products-json').value = productsJson;
        document.getElementById('total-quantity').value = totalQuantity;
        document.getElementById('total-price').value = totalPrice;

        // Отправляем AJAX запрос на создание заказа
        fetch('php_scripts/order_create.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                address: address,
                products: productsData,
                total_quantity: totalQuantity,
                total_price: totalPrice
            }),
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Очистка корзины и отображение сообщения об успешном оформлении заказа
                document.querySelector('.catalog').innerHTML = '<span class="empty_cart">Ваша корзина пуста.</span>';
                document.querySelector('.add_to_cart_info').innerHTML = '<div class="order_success">Заказ успешно оформлен!</div>';
               document.querySelector('.add_to_cart_info').innerHTML = '<div class="order_success">Заказ успешно оформлен!</div>';

                closeModal(); // Закрываем модальное окно после успешного оформления заказа
            } else {
                console.error('Ошибка при создании заказа:', data.error);
                alert('Произошла ошибка при оформлении заказа. Пожалуйста, попробуйте ещё раз или свяжитесь с поддержкой.');
            }
        })
        .catch(error => {
            console.error('Ошибка при создании заказа:', error);
            alert('Произошла ошибка при оформлении заказа. Пожалуйста, попробуйте ещё раз или свяжитесь с поддержкой.');
        });
    });
});

</script>
    <script>
// Обработка изменения количества товара в корзине
document.querySelectorAll('.quantity-wrapper').forEach(wrapper => {
    const minusBtn = wrapper.querySelector('.minus');
    const plusBtn = wrapper.querySelector('.plus');
    const quantityInput = wrapper.querySelector('.quantity-input');
    const productId = wrapper.dataset.productId;

    minusBtn.addEventListener('click', () => {
        let quantity = parseInt(quantityInput.value);
        if (quantity > 0) {
            quantityInput.value = quantity - 1;
            updateCart(productId, quantity - 1);
        }
    });

    plusBtn.addEventListener('click', () => {
        let quantity = parseInt(quantityInput.value);
        if (quantity < 10) { // Проверка на максимальное значение
            quantityInput.value = quantity + 1;
            updateCart(productId, quantity + 1);
        }
    });
});

function updateCart(productId, quantity) {
    fetch('php_scripts/change_cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ product_id: productId, quantity: quantity }),
    })
    .then(response => response.json())
    .then(data => {
        if (data.error) {
            console.error('Ошибка:', data.error);
            return;
        }
        refreshCart();
    })
    .catch(error => console.error('Ошибка:', error));

}
function refreshCart() {
    fetch('php_scripts/refresh_cart.php')
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                console.error('Ошибка:', data.error);
                return;
            }

            // Обновляем только блоки catalog и total-container
            document.querySelector('.catalog').innerHTML = data.catalog;
            document.querySelector('.total-container').innerHTML = data.totalContainer;

            // Повторно добавляем обработчики событий для новых элементов
            document.querySelectorAll('.quantity-wrapper').forEach(wrapper => {
                const minusBtn = wrapper.querySelector('.minus');
                const plusBtn = wrapper.querySelector('.plus');
                const quantityInput = wrapper.querySelector('.quantity-input');
                const productId = wrapper.dataset.productId;

                minusBtn.addEventListener('click', () => {
                    let quantity = parseInt(quantityInput.value);
                    if (quantity > 0) {
                        quantityInput.value = quantity - 1;
                        updateCart(productId, quantity - 1);
                    }
                });

                plusBtn.addEventListener('click', () => {
                    let quantity = parseInt(quantityInput.value);
                    if (quantity < 10) { // Проверка на максимальное значение
                        quantityInput.value = quantity + 1;
                        updateCart(productId, quantity + 1);
                    }
                });
            });

            // Повторно добавляем обработчик события для кнопки "Оформить заказ" после обновления корзины
            const orderButton = document.getElementById('order-button');
            if (orderButton) {
                orderButton.addEventListener('click', function(event) {
                    event.preventDefault();
                    const url = new URL(window.location.href);
                    url.searchParams.set('modal', 'order');
                    window.history.pushState({}, '', url);
                    showModal();
                });
            }
        })
        .catch(error => console.error('Ошибка при обновлении корзины:', error));
}


</script>
 <script>
// Обработка нажатия на кнопку "Оформить заказ"
document.getElementById('order-button').addEventListener('click', function(event) {
    event.preventDefault();
    const url = new URL(window.location.href);
    url.searchParams.set('modal', 'order');
    window.history.pushState({}, '', url);
    showModal();
});

// Функция для отображения модального окна
function showModal() {
    document.getElementById('order-modal').style.display = 'block';
}

// Функция для закрытия модального окна
function closeModal() {
    document.getElementById('order-modal').style.display = 'none';
    const url = new URL(window.location.href);
    url.searchParams.delete('modal');
    window.history.pushState({}, '', url);
}

// Проверка URL при загрузке страницы
window.addEventListener('load', function() {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('modal') === 'order') {
        showModal();
    }
});
</script>
</body>
</html>

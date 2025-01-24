<?php 
session_start(); ?>
<link rel="stylesheet" type="text/css" href="src/css/main.css">
<link rel="stylesheet" type="text/css" href="src/css/header.css">

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Lato:ital,wght@0,100;0,300;0,400;0,700;0,900;1,100;1,300;1,400;1,700;1,900&display=swap" rel="stylesheet">

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&display=swap" rel="stylesheet">
<header>
    <div class="header_left">
        <img src="src/icon/logo.svg">
        <span>DRIL PIPE SERVICE</span>
    </div>
    <div class="header_centr">
        <a href="index.php">Главная</a>
        <a href="catalog.php">Каталог</a>
        <a href="index.php#contact">Контакты</a>
        <a href="index.php#sale">Акции</a>
    </div>
	<div class="header_right">
	    <?php if (isset($_SESSION['user_id'])): ?>
	        <img src="src/icon/cart.svg" alt="Корзина" title="Корзина" id="cartIcon">
	        <img src="src/icon/profile.svg" alt="Профиль" title="Профиль" id="profileIcon">
	        
	    <?php else: ?>
	       
	        <a class="log" href="login_and_register_page.php">Регистрация / Авторизация</a>
	    <?php endif; ?>
	</div>

	<script>
	// Проверка существования и добавление обработчиков событий для иконок
	document.addEventListener('DOMContentLoaded', function() {
	    const profileIcon = document.getElementById('profileIcon');
	    const cartIcon = document.getElementById('cartIcon');

	    if (profileIcon) {
	        profileIcon.addEventListener('click', function() {
	            window.location.href = 'profile.php';
	        });
	    }

	    if (cartIcon) {
	        cartIcon.addEventListener('click', function() {
	            window.location.href = 'cart.php';
	        });
	    }
	});
	</script>

</header>

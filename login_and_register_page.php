<?php 
require 'header.php'; ?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" type="text/css" href="src/css/login_and_register.css">
    <title>Авторизация и Регистрация</title>

</head>
<body>

    <div class="container">
        <!-- Форма авторизации -->
        <div id="login-form" class="form-content">
            <h2>Авторизация</h2>
            <form action="php_scripts/login_user.php" method="POST">
                <div class="form-group">
                    <input type="email" id="login-email" name="email" required
                    placeholder="Email">
                </div>
                <div class="form-group">
                    <input type="password" id="login-password" name="password" required placeholder="Пароль">
                </div>
                <button type="submit" class="btn">Войти</button>
            </form>
            <div class="toggle-text">
                Нет аккаунта? <span onclick="toggleForms()">Зарегистрируйтесь</span>
            </div>
        </div>

        <!-- Форма регистрации -->
        <div id="register-form" class="form-content hidden">
            <h2>Регистрация</h2>
            <form action="php_scripts/register_user.php" method="POST">
                <div class="form-group">
                    <input type="text" id="register-name" name="name" required placeholder="Имя">
                </div>
                <div class="form-group">
                    <input type="email" id="register-email" name="email" required placeholder="Email">
                </div>
                <div class="form-group">
                    <input type="password" id="register-password" name="password" required placeholder="Пароль">
                </div>
                <button type="submit" class="btn">Зарегистрироваться</button>
            </form>
            <div class="toggle-text">
                Уже есть аккаунт? <span onclick="toggleForms()">Войдите</span>
            </div>
        </div>
    </div>

    <script>
        // Функция для переключения между формами
        function toggleForms() {
            const loginForm = document.getElementById('login-form');
            const registerForm = document.getElementById('register-form');

            loginForm.classList.toggle('hidden');
            registerForm.classList.toggle('hidden');
        }
    </script>

</body>
</html>

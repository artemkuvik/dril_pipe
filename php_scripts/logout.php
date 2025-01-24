<?php
session_start();

// Удаляем все данные сессии
session_unset();

// Уничтожаем сессию
session_destroy();

// Перенаправляем пользователя на главную страницу
header('Location: ../index.php');
exit();
?>

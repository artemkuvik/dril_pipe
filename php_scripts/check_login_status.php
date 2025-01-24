<?php
session_start();

// Проверяем, авторизован ли пользователь
$response = ['loggedIn' => false];
if (isset($_SESSION['user_id'])) {
    $response['loggedIn'] = true;
}

// Возвращаем результат в формате JSON
echo json_encode($response);
?>

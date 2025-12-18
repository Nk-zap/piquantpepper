<?php
// db.php - Подключение к базе данных piquantpepper
$con = mysqli_connect('MySQL-5.7', 'root', '', 'piquantpepper');
if (!$con) die('Ошибка подключения: ' . mysqli_connect_error());
mysqli_set_charset($con, 'utf8');
?>

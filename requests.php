<?php
session_start();
if (!isset($_SESSION['userid'])) {
    die('Чтобы посмотреть историю заявок, надо войти в аккаунт.');
}

include('db.php');

// Отзыв
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['feedback'], $_POST['request_id'])) {
        $feedback = trim($_POST['feedback']);
        $request_id = (int)$_POST['request_id'];
        
        $check = $con->prepare("SELECT id FROM requests WHERE id = ? AND user_id = ? AND status = 'Посещение состоялось'");
        $check->bind_param("ii", $request_id, $_SESSION['userid']);
        $check->execute();
        $check_result = $check->get_result();
        
        if ($check_result->num_rows > 0 && !empty($feedback)) {
            $update = $con->prepare("UPDATE requests SET feedback = ? WHERE id = ?");
            $update->bind_param("si", $feedback, $request_id);
            $update->execute();
            $update->close();
        }
        $check->close();
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
    
    // Удаление отзыва
    if (isset($_POST['delete_feedback'], $_POST['request_id'])) {
        $request_id = (int)$_POST['request_id'];
        
        $check = $con->prepare("SELECT id FROM requests WHERE id = ? AND user_id = ? AND status = 'Посещение состоялось'");
        $check->bind_param("ii", $request_id, $_SESSION['userid']);
        $check->execute();
        $check_result = $check->get_result();
        
        if ($check_result->num_rows > 0) {
            $update = $con->prepare("UPDATE requests SET feedback = NULL WHERE id = ?");
            $update->bind_param("i", $request_id);
            $update->execute();
            $update->close();
        }
        $check->close();
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
}

// Получение заявок - СОРТИРОВКА ОТ НОВЫХ К СТАРЫМ
$stmt = $con->prepare("SELECT * FROM requests WHERE user_id = ? ORDER BY date DESC");
$stmt->bind_param("i", $_SESSION['userid']);
$stmt->execute();
$result = $stmt->get_result();

if (!$result) die('Ошибка запроса');

// Считаем общее количество заявок пользователя для обратной нумерации
$count_stmt = $con->prepare("SELECT COUNT(*) as total FROM requests WHERE user_id = ?");
$count_stmt->bind_param("i", $_SESSION['userid']);
$count_stmt->execute();
$count_result = $count_stmt->get_result();
$total_row = $count_result->fetch_assoc();
$total_bookings = $total_row['total'];
$count_stmt->close();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>История бронирования - Пряный Перец</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
        }

        body {
            background-color: #FFEDE1;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            color: #000000;
        }

        /* Хедер */
        .header {
            background-color: #F0AD73;
            color: white;
            border-bottom: 4px solid white;
            padding: 15px 5%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: 70px;
        }

        .logo-container {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .logo {
            width: 3px;
            height: 30px;
            background-color: #FF7600;
            display: inline-block;
        }

        .restaurant-name {
            font-size: 1.5rem;
            font-weight: bold;
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 0;
        }

        .nav-separator {
            width: 2px;
            height: 70px;
            background-color: white;
        }

        .nav-link {
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            font-size: 1rem;
            transition: background-color 0.3s;
        }

        .nav-link:hover {
            opacity: 0.8;
        }

        /* Основной контент */
        .main-container {
            flex: 1;
            padding: 20px 20px;
            max-width: 1400px;
            margin: 0 auto;
            width: 100%;
        }

        .page-title {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
            margin-bottom: 10px;
            text-align: center;
        }

        .page-title h1 {
            font-size: 2rem;
            color: #000000;
        }

        .notebook-icon {
            width: 40px;
            height: 40px;
            background-image: url('images/1.svg');
            background-repeat: no-repeat;
            background-size: contain;
        }

        /* Контейнер карточек */
        .bookings-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 20px;
        }

        /* Карточка бронирования */
        .booking-card {
            background-color: #F0B684;
            border-radius: 12px;
            padding: 25px;
            width: 100%;
            max-width: 900px;
            min-height: 125px;
            box-shadow: 0 4px 4px rgba(0, 0, 0, 0.25);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .booking-number {
            font-size: 1.2rem;
            font-weight: bold;
        }

        .status-badge {
            padding: 6px 18px;
            border-radius: 50px;
            color: white;
            font-size: 0.9rem;
            font-weight: bold;
        }

        .status-pending {
            background-color: #4A90E2; /* Голубой */
        }

        .status-completed {
            background-color: #4CAF50; /* Зеленый */
        }

        .status-cancelled {
            background-color: #F44336; /* Красный */
        }

        .booking-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            flex-wrap: nowrap;
            gap: 10px;
        }

        .info-item {
            flex: 1;
            min-width: 200px;
            display: flex; /* НОВОЕ: лейбл и значение в ряд */
            align-items: center; /* НОВОЕ: выравнивание по центру */
            gap: 10px; /* НОВОЕ: отступ между лейблом и значением */
        }

        .info-label {
            font-weight: bold;
            white-space: nowrap;
            font-size: 0.9rem; /* НОВОЕ: уменьшенный шрифт */
        }

        .info-value {
            font-size: 0.9rem; /* НОВОЕ: уменьшенный шрифт */
        }

        .feedback-label {
            font-weight: bold;
            margin-bottom: 10px;
            display: block;
        }

        .feedback-container {
            display: flex;
            gap: 20px;
            align-items: flex-start;
        }

        .feedback-textarea {
            flex: 1;
            min-width: 60%;
            height: 75px;
            padding: 12px;
            border-radius: 10px;
            border: none;
            background-color: #FFDDC1;
            font-size: 1rem;
            resize: none;
            color: #000;
        }

        .feedback-textarea::placeholder {
            color: #666;
        }

        .feedback-buttons {
            display: flex;
            flex-direction: column;
            gap: 10px;
            width: 200px;
        }

        .save-btn, .delete-btn {
            padding: 7px 8px;
            border: none;
            border-radius: 10px;
            font-weight: bold;
            cursor: pointer;
            font-size: 0.9rem;
            transition: opacity 0.3s;
        }

        .save-btn {
            background-color: #73BACF;
            color: white;
        }

        .delete-btn {
            background-color: #eb4a3e;
            color: white;
        }

        .save-btn:hover, .delete-btn:hover {
            opacity: 0.8;
        }

        .feedback-title {
            font-weight: bold;
            margin-bottom: 10px;
        }

        .feedback-content {
            background-color: #FFDDC1;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 10px;
        }

        /* Админ-панель */
        .admin-panel-link {
            display: inline-block;
            margin-top: 30px;
            padding: 10px 20px;
            background-color: #73BACF;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
        }

        /* Сообщение администратора */
        .admin-message {
            background-color: #FFF;
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
            border-left: 4px solid #73BACF;
        }

        /* Адаптивность */
        @media (max-width: 768px) {
            .header {
                padding: 10px 15px;
                height: 60px;
            }

            .restaurant-name {
                font-size: 1.2rem;
            }

            .nav-link {
                padding: 8px 12px;
                font-size: 0.9rem;
            }

            .nav-separator {
                height: 60px;
            }

            .page-title h1 {
                font-size: 1.5rem;
            }

            .main-container {
                padding: 20px 15px;
            }

            .booking-card {
                padding: 20px;
            }

            .booking-info {
                flex-direction: column;
                gap: 15px;
            }

            .info-item {
                min-width: 100%;
                /* display: flex; align-items: center; gap: 10px; остаются, так что на мобильных лейбл и значение в ряд */
            }

            .card-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }

            .status-badge {
                align-self: flex-start;
            }

            .feedback-container {
                flex-direction: column;
            }

            .feedback-textarea {
                width: 100%;
            }

            .feedback-buttons {
                width: 100%;
                flex-direction: row;
                justify-content: flex-end;
            }
        }

        @media (max-width: 390px) {
            .header {
                flex-direction: column;
                height: auto;
                gap: 10px;
                padding: 10px;
            }

            .nav-links {
                width: 100%;
                justify-content: space-between;
            }

            .nav-separator {
                display: none;
            }

            .page-title {
                flex-direction: column;
                gap: 10px;
            }

            .booking-card {
                padding: 15px;
            }
        }
    </style>
</head>
<body>
    <!-- Хедер -->
    <header class="header">
        <div class="logo-container">
            <div class="logo"></div>
            <div class="restaurant-name">Пряный Перец</div>
        </div>
        <nav class="nav-links">
            <div class="nav-separator"></div>
            <a href="index.php" class="nav-link">Главная</a>
            <div class="nav-separator"></div>
            <a href="create.php?source=requests" class="nav-link">Забронировать стол</a>
        </nav>
    </header>

    <!-- Основной контент -->
    <main class="main-container">
        <div class="page-title">
            <h1>История бронирования</h1>
            <div class="notebook-icon"></div>
        </div>

        <div class="bookings-container">
            <?php
            // Сначала собираем все заявки в массив
            $requests = [];
            while ($request = $result->fetch_assoc()) {
                $requests[] = $request;
            }

            // Общее количество заявок
            $total = count($requests);

            // Перебираем в обычном порядке (свежие сверху)
            foreach ($requests as $index => $request) {
                $status = $request['status'];
                $status_class = '';
                $display_status = $status;

                // Рассчитываем обратную нумерацию:
                // Снизу (последняя в списке) будет №1
                // Сверху (первая в списке) будет самый большой номер
                $booking_number = $total - $index;

                // Маппинг и определение класса статуса
                if ($status === 'Новое') {
                    $status_class = 'status-pending';
                    $display_status = 'на рассмотрение';
                } elseif ($status === 'Посещение состоялось') {
                    $status_class = 'status-completed';
                    $display_status = 'Посещение состоялось';
                } elseif ($status === 'Отменено') {
                    $status_class = 'status-cancelled';
                    $display_status = 'Посещение не состоялось';
                } else {
                    $status_class = 'status-cancelled';
                    $display_status = $status;
                }
            ?>
            <div class="booking-card">
                <div class="card-header">
                    <div class="booking-number">Бронирование <?php echo $booking_number; ?></div>
                    <div class="status-badge <?php echo $status_class; ?>">
                        <?php echo htmlspecialchars($display_status); ?>
                    </div>
                </div>

                <div class="booking-info">
                    <div class="info-item">
                        <div class="info-label">Дата и время:</div>
                        <div class="info-value"><?php echo htmlspecialchars($request['date']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Количество гостей:</div>
                        <div class="info-value"><?php echo htmlspecialchars($request['bronir']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Телефон:</div>
                        <div class="info-value"><?php echo htmlspecialchars($request['contacts']); ?></div>
                    </div>
                </div>

                <?php if (!empty($request['admin_message'])): ?>
                <div class="admin-message">
                    <strong>Сообщение администратора:</strong><br>
                    <?php echo htmlspecialchars($request['admin_message']); ?>
                </div>
                <?php endif; ?>

                <?php if (!empty($request['feedback'])): ?>
                <div class="existing-feedback">
                    <div class="feedback-title">Ваш отзыв:</div>
                    <div class="feedback-content"><?php echo htmlspecialchars($request['feedback']); ?></div>
                    <?php if ($status === 'Посещение состоялось'): ?>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="request_id" value="<?php echo $request['id']; ?>">
                        <input type="hidden" name="delete_feedback" value="1">
                        <button type="submit" class="delete-btn">Удалить отзыв</button>
                    </form>
                    <?php endif; ?>
                </div>
                <?php elseif ($status === 'Посещение состоялось'): ?>
                <div class="feedback-form">
                    <form method="POST">
                        <input type="hidden" name="request_id" value="<?php echo $request['id']; ?>">
                        <label class="feedback-label">Оставьте ваш отзыв:</label>
                        <div class="feedback-container">
                            <textarea name="feedback" class="feedback-textarea" placeholder="Поделитесь вашими впечатлениями..." required></textarea>
                            <div class="feedback-buttons">
                                <button type="submit" class="save-btn">Сохранить отзыв</button>
                                <button type="button" class="delete-btn" onclick="this.form.reset()">Очистить</button>
                            </div>
                        </div>
                    </form>
                </div>
                <?php endif; ?>
            </div>
            <?php } ?>

            <?php if ($total == 0): ?>
            <div class="booking-card" style="text-align: center; padding: 40px;">
                <p style="font-size: 1.2rem;">У вас пока нет заявок.</p>
            </div>
            <?php endif; ?>
        </div>
        
        <?php if (isset($_SESSION['admin']) && $_SESSION['admin']): ?>
        <a href="admin.php" class="admin-panel-link">Панель администратора</a>
        <?php endif; ?>
        
    </main>
</body>
</html>
<?php
$stmt->close();
?>
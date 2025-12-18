<?php
session_start();
include('db.php');

// Проверяем авторизацию администратора
if (!isset($_SESSION['admin']) || !$_SESSION['admin']) {
    header('Location: login.php');
    exit();
}

// Обработка выхода из системы
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit;
}

// Получаем список всех пользователей для фильтра
$users_sql = "SELECT id, fullname FROM users ORDER BY fullname";
$users_result = $con->query($users_sql);
$users = [];
while ($user_row = $users_result->fetch_assoc()) {
    $users[$user_row['id']] = $user_row['fullname'];
}

// Определяем выбранного пользователя для фильтрации
$selected_user_id = $_GET['user_id'] ?? '';
$where_clause = '';
$filter_message = '';

if (!empty($selected_user_id) && is_numeric($selected_user_id)) {
    $selected_user_id = (int)$selected_user_id;
    $where_clause = "WHERE r.user_id = $selected_user_id";
    
    // Простое сообщение об успешном применении фильтра
    $filter_message = 'Успех';
}

// Обработка изменения статуса
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['request_id'], $_POST['status'])) {
    $request_id = (int)$_POST['request_id'];
    $status = $_POST['status'];
    
    if (in_array($status, ['Новое', 'Посещение состоялось', 'Отменено'])) {
        $stmt = $con->prepare("UPDATE requests SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $request_id);
        
        if ($stmt->execute()) {
            $status_change_message = "Статус бронирования #$request_id изменен на \"$status\"";
            $_SESSION['last_changed_request'] = $request_id;
        }
        
        $stmt->close();
    }
}

// Получаем список бронирований с учетом фильтра
$sql = "SELECT r.*, u.fullname, u.email, u.phone 
        FROM requests r 
        JOIN users u ON r.user_id = u.id 
        $where_clause
        ORDER BY r.id DESC";
$result = $con->query($sql);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Панель администратора - Пряный Перец</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles/style_admin.css">
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            <?php if (!empty($status_change_message) && isset($_SESSION['last_changed_request'])): ?>
                const changedRequestId = <?php echo $_SESSION['last_changed_request']; ?>;
                const message = "<?php echo addslashes($status_change_message); ?>";
                
                const statusForms = document.querySelectorAll('.status-form');
                statusForms.forEach(form => {
                    const requestIdInput = form.querySelector('input[name="request_id"]');
                    if (requestIdInput && parseInt(requestIdInput.value) === changedRequestId) {
                        const messageDiv = document.createElement('div');
                        messageDiv.className = 'status-change-message';
                        messageDiv.textContent = message;
                        messageDiv.style.display = 'block';
                        
                        form.parentNode.querySelector('.status-area').appendChild(messageDiv);
                        
                        setTimeout(() => {
                            messageDiv.style.display = 'none';
                        }, );
                    }
                });
                
                <?php unset($_SESSION['last_changed_request']); ?>
            <?php endif; ?>
            
            // Открытие/закрытие бокового меню
            const menuToggle = document.querySelector('.menu-toggle');
            const sidebar = document.querySelector('.admin-sidebar');
            const menuItems = document.querySelectorAll('.menu-item');
            
            menuToggle.addEventListener('click', function() {
                sidebar.classList.toggle('open');
                
                // Анимация пунктов меню с задержкой
                if (sidebar.classList.contains('open')) {
                    menuItems.forEach((item, index) => {
                        setTimeout(() => {
                            item.style.opacity = '1';
                            item.style.transform = 'translateX(0)';
                        }, index * 100 + 100);
                    });
                    menuToggle.innerHTML = '✕';
                } else {
                    menuItems.forEach(item => {
                        item.style.opacity = '0';
                        item.style.transform = 'translateX(-10px)';
                    });
                    menuToggle.innerHTML = '☰';
                }
            });
        });
    </script>
</head>
<body>
    <!-- Хедер с кнопкой меню -->
    <header class="admin-header">
        <button class="menu-toggle">☰</button>
        
        <div class="logo-container">
            <span class="orange-slash">|</span>
            <a href="index.php" class="logo-text">Пряный Перец</a>
        </div>
        
        <div class="admin-title-container">
            <h1 class="admin-title">Панель администратора</h1>
            <div class="admin-icon"></div>
        </div>
        
        <nav class="header-nav">
            <div class="nav-separator"></div>
            <a href="index.php" class="nav-link">Главная</a>
            <div class="nav-separator"></div>
            <a href="?logout=1" class="nav-link">Выйти</a>
        </nav>
    </header>

    <!-- Боковое меню -->
    <aside class="admin-sidebar">
        <nav class="sidebar-nav">
            <a href="admin.php" class="menu-item">
                <span class="menu-text">Бронирования</span>
            </a>
            <a href="#" class="menu-item">
                <span class="menu-text">Пользователи</span>
            </a>
            <a href="#" class="menu-item">
                <span class="menu-text">Настройки</span>
            </a>
            <a href="?logout=1" class="menu-item">
                <span class="menu-text">Выход</span>
            </a>
        </nav>
    </aside>

    <!-- Основной контент -->
    <main class="main-container">
        <!-- Фильтр по пользователям -->
        <div class="filter-container">
            <form method="GET" class="filter-form">
                <div class="filter-section">
                    <label class="filter-label">Фильтр по пользователю:</label>
                    <select name="user_id" class="filter-select">
                        <option value="">Все пользователи</option>
                        <?php foreach ($users as $id => $name): ?>
                            <option value="<?php echo $id; ?>" <?php echo $selected_user_id == $id ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($name); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="filter-btn">Применить</button>
                <?php if (!empty($filter_message)): ?>
                <div class="filter-message">
                    <?php echo $filter_message; ?>
                </div>
                <?php endif; ?>
            </form>
        </div>

        <div class="bookings-container">
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <?php
                    $status = $row['status'];
                    $status_class = '';
                    $status_text = '';
                    
                    if ($status === 'Новое') {
                        $status_class = 'status-pending';
                        $status_text = 'На рассмотрении';
                    } elseif ($status === 'Посещение состоялось') {
                        $status_class = 'status-completed';
                        $status_text = 'Посещение состоялось';
                    } elseif ($status === 'Отменено') {
                        $status_class = 'status-cancelled';
                        $status_text = 'Посещение не состоялось';
                    }
                    ?>
                    
                    <div class="booking-card">
                        <div class="card-header">
                            <div class="booking-number">Бронирование <?php echo $row['id']; ?></div>
                            <div class="status-area">
                                <div class="status-badge <?php echo $status_class; ?>">
                                    <?php echo htmlspecialchars($status_text); ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="booking-info">
                            <div class="info-item">
                                <div class="info-label">Дата и время:</div>
                                <div class="info-value"><?php echo htmlspecialchars($row['date']); ?></div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Количество гостей:</div>
                                <div class="info-value"><?php echo htmlspecialchars($row['bronir']); ?></div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Телефон:</div>
                                <div class="info-value"><?php echo htmlspecialchars($row['contacts']); ?></div>
                            </div>
                        </div>
                        
                        <?php if ($status === 'Посещение состоялось' && !empty($row['feedback'])): ?>
                        <div class="feedback-section">
                            <div class="feedback-label">Отзыв:</div>
                            <div class="feedback-content"><?php echo nl2br(htmlspecialchars($row['feedback'])); ?></div>
                        </div>
                        <?php endif; ?>
                        
                        <form method="POST" class="status-form">
                            <input type="hidden" name="request_id" value="<?php echo $row['id']; ?>">
                            <div class="status-label">Статус:</div>
                            <select name="status" class="status-select">
                                <option value="Новое" <?php echo $row['status'] == 'Новое' ? 'selected' : ''; ?>>На рассмотрении</option>
                                <option value="Посещение состоялось" <?php echo $row['status'] == 'Посещение состоялось' ? 'selected' : ''; ?>>Посещение состоялось</option>
                                <option value="Отменено" <?php echo $row['status'] == 'Отменено' ? 'selected' : ''; ?>>Посещение не состоялось</option>
                            </select>
                            <button type="submit" class="save-btn">Сохранить</button>
                        </form>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="empty-state">
                    <h3>
                        <?php if (!empty($selected_user_id)): ?>
                            У выбранного пользователя нет бронирований
                        <?php else: ?>
                            Бронирований не найдено
                        <?php endif; ?>
                    </h3>
                </div>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>
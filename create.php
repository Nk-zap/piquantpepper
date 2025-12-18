<?php
session_start();
include('db.php');

if (!isset($_SESSION['userid'])) {
    header('Location: login.php');
    exit;
}

$error = '';
$success = '';

// Определяем источник перехода
$source = 'index'; // по умолчанию
if (isset($_GET['source']) && $_GET['source'] === 'requests') {
    $source = 'requests';
} elseif (isset($_POST['source'])) {
    $source = $_POST['source'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['userid'];
    $datetime = $_POST['datetime'] ?? '';
    $guests = $_POST['guests'] ?? '';
    $contacts = $_POST['contacts'] ?? '';
    
    if (empty($datetime) || empty($guests) || empty($contacts)) {
        $error = 'Все поля обязательны для заполнения';
    } elseif (!preg_match('/^\+7\(\d{3}\)-\d{3}-\d{2}-\d{2}$/', $contacts)) {
        $error = 'Телефон должен быть в формате +7(XXX)-XXX-XX-XX';
    } else {
        // Разделяем дату и время для сохранения в формате БД
        $date = date('Y-m-d', strtotime($datetime));
        $time = date('H:i:s', strtotime($datetime));
        $datetime_db = $date . ' ' . $time;
        
        $payment = 'наличные';
        $status = 'Новое';
        $feedback = '';
        
        $stmt = $con->prepare("INSERT INTO requests (user_id, contacts, date, bronir, payment, status, feedback) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issssss", $user_id, $contacts, $datetime_db, $guests, $payment, $status, $feedback);
        
        if ($stmt->execute()) {
            $success = 'Заявка успешно отправлена на рассмотрение!';
            $_POST = array();
            
            // Перенаправляем в зависимости от источника
            if ($source === 'requests') {
                header('Location: requests.php');
            } else {
                header('Location: index.php');
            }
            exit;
        } else {
            $error = 'Ошибка при сохранении заявки';
        }
        $stmt->close();
    }
}

// Получаем текущий год для ограничения выбора даты
$current_year = date('Y');
$min_date = date('Y-m-d\TH:i');
$max_date = $current_year . '-12-31T23:59';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Бронирование столика - Пряный Перец</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles/style_create.css">
</head>
<body>
    <!-- Фоновая картинка -->
    <div class="background-image"></div>

    <!-- Основной контент -->
    <div class="main-content">
        <div class="form-container">
            <!-- Иконка -->
            <div class="booking-icon"></div>

            <!-- Заголовок -->
            <h1 class="form-title">Бронирование столика</h1>
            <div class="title-line"></div>

            <!-- Сообщения об ошибках/успехе -->
            <?php if ($error): ?>
                <div class="message error-message"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="message success-message"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <!-- Форма -->
            <form method="POST" class="booking-form">
                <!-- Скрытое поле для хранения источника -->
                <input type="hidden" name="source" value="<?= htmlspecialchars($source) ?>">
                
                <!-- Дата и время -->
                <div class="form-group">
                    <label class="form-label">Дата и Время</label>
                    <input type="datetime-local" 
                           name="datetime" 
                           class="form-input" 
                           placeholder="дд.мм.гггг __:__"
                           value="<?= htmlspecialchars($_POST['datetime'] ?? '') ?>"
                           min="<?= $min_date ?>"
                           max="<?= $max_date ?>"
                           required>
                </div>

                <!-- Количество гостей -->
                <div class="form-group">
                    <label class="form-label">Количество гостей</label>
                    <select name="guests" class="form-input" required>
                        <option value="" disabled selected hidden>от 1 до 10 человека</option>
                        <?php for ($i = 1; $i <= 10; $i++): ?>
                            <option value="<?= $i ?>" <?= ($_POST['guests'] ?? '') == $i ? 'selected' : '' ?>>
                                <?= $i ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>

                <!-- Телефон -->
                <div class="form-group">
                    <label class="form-label">Телефон</label>
                    <input type="tel" 
                           name="contacts" 
                           class="form-input" 
                           placeholder="+7(XXX)-XXX-XX-XX"
                           value="<?= htmlspecialchars($_POST['contacts'] ?? '') ?>"
                           pattern="\+7\(\d{3}\)-\d{3}-\d{2}-\d{2}"
                           required>
                </div>

                <!-- Кнопка отправки -->
                <button type="submit" class="submit-button">Забронировать</button>
            </form>

            <!-- Ссылки для навигации -->
            <div class="form-links">
                <a href="index.php" class="form-link">На главную</a>
                <a href="requests.php?source=requests" class="form-link">Мои бронирования</a>
            </div>
        </div>
    </div>

    <script>
        // Маска для телефона с возможностью полного удаления
        document.querySelector('input[name="contacts"]').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            
            // Если пользователь стирает символы, позволяем стирать полностью
            if (e.inputType === 'deleteContentBackward' || e.inputType === 'deleteContentForward') {
                e.target.value = value;
                return;
            }
            
            if (value.length > 11) value = value.substring(0, 11);
            
            if (value.length > 0) {
                if (!value.startsWith('7')) value = '7' + value;
                e.target.value = '+7(' + value.substring(1,4) + ')-' + value.substring(4,7) + '-' + value.substring(7,9) + '-' + value.substring(9,11);
            } else {
                e.target.value = '';
            }
        });

        // Обработка клавиш для телефона
        document.querySelector('input[name="contacts"]').addEventListener('keydown', function(e) {
            // Разрешаем удаление, backspace, tab, escape, enter
            if ([46, 8, 9, 27, 13].includes(e.keyCode) || 
                // Разрешаем Ctrl+A, Ctrl+C, Ctrl+V, Ctrl+X
                (e.keyCode === 65 && e.ctrlKey === true) || 
                (e.keyCode === 67 && e.ctrlKey === true) || 
                (e.keyCode === 86 && e.ctrlKey === true) || 
                (e.keyCode === 88 && e.ctrlKey === true) ||
                // Разрешаем навигационные клавиши: стрелки, home, end
                (e.keyCode >= 35 && e.keyCode <= 39)) {
                return;
            }
            
            // Запрещаем ввод нецифровых символов
            if ((e.keyCode < 48 || e.keyCode > 57) && (e.keyCode < 96 || e.keyCode > 105)) {
                e.preventDefault();
            }
        });

        // Установка минимального и максимального времени
        const now = new Date();
        const minDateTime = now.toISOString().slice(0, 16);
        const maxDateTime = new Date().getFullYear() + '-12-31T23:59';
        document.querySelector('input[type="datetime-local"]').min = minDateTime;
        document.querySelector('input[type="datetime-local"]').max = maxDateTime;

        // Автоматическое форматирование даты при потере фокуса
        document.querySelector('input[type="datetime-local"]').addEventListener('change', function(e) {
            if (e.target.value) {
                const date = new Date(e.target.value);
                const formattedDate = date.toLocaleDateString('ru-RU') + ' ' + date.toLocaleTimeString('ru-RU', {hour: '2-digit', minute:'2-digit'});
                // Можно добавить отображение форматированной даты, если нужно
            }
        });

        // Проверка формы перед отправкой
        document.querySelector('.booking-form').addEventListener('submit', function(e) {
            const phoneInput = document.querySelector('input[name="contacts"]');
            const phonePattern = /^\+7\(\d{3}\)-\d{3}-\d{2}-\d{2}$/;
            
            if (!phonePattern.test(phoneInput.value)) {
                e.preventDefault();
                alert('Пожалуйста, введите телефон в формате +7(XXX)-XXX-XX-XX');
                phoneInput.focus();
                return false;
            }

            const datetimeInput = document.querySelector('input[type="datetime-local"]');
            const selectedDate = new Date(datetimeInput.value);
            const currentDate = new Date();
            
            if (selectedDate < currentDate) {
                e.preventDefault();
                alert('Пожалуйста, выберите дату и время в будущем');
                datetimeInput.focus();
                return false;
            }
            
            // Проверка времени работы (12:00-23:00)
            const hours = selectedDate.getHours();
            if (hours < 9 || hours >= 22) {
                e.preventDefault();
                alert('Бронирование возможно только с 9:00 до 22:00');
                datetimeInput.focus();
                return false;
            }
        });
    </script>
</body>
</html>

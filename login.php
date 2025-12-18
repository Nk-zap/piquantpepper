<?php
session_start();
include 'db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $login = trim($_POST['login']);
    $password = $_POST['password'];
    
    if (empty($login) || empty($password)) {
        $error = 'Все поля обязательны для заполнения';
    } else {
        // Проверяем пользователя в базе данных
        $sql = "SELECT id, login, password, fullname FROM users WHERE login = ?";
        $stmt = mysqli_prepare($con, $sql);
        mysqli_stmt_bind_param($stmt, "s", $login);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($user = mysqli_fetch_assoc($result)) {
            // Проверяем пароль
            if ($password === $user['password']) {
                // Устанавливаем данные сессии
                $_SESSION['userid'] = $user['id'];
                $_SESSION['login'] = $user['login'];
                $_SESSION['fullname'] = $user['fullname'];
                // Определяем, является ли пользователь администратором (id=1 или логин admin)
                $_SESSION['admin'] = ($user['id'] == 1 || $user['login'] === 'admin');
                
                // Перенаправляем в зависимости от роли
                if ($_SESSION['admin']) {
                    header('Location: admin.php');
                } else {
                    header('Location: index.php');
                }
                exit();
            } else {
                $error = 'Неверный пароль';
            }
        } else {
            $error = 'Пользователь с таким логином не найден';
        }
        
        mysqli_stmt_close($stmt);
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <title>Авторизация</title>
    <link rel="stylesheet" href="styles/style_login.css">
    <style> body { background-image: url('images/авторизация.jpg'); } </style>
    <script>
        // Адаптация для мобильных устройств
        document.addEventListener('DOMContentLoaded', function() {
            // Увеличиваем область тапа для мобильных
            const adjustForMobile = () => {
                const isMobile = window.innerWidth <= 768;
                const inputs = document.querySelectorAll('.form-input');
                const buttons = document.querySelectorAll('.submit-btn, .link');
                
                inputs.forEach(input => {
                    if (isMobile) {
                        input.style.fontSize = '17px';
                        input.style.minHeight = '44px';
                    } else {
                        input.style.fontSize = '';
                        input.style.minHeight = '';
                    }
                });
                
                buttons.forEach(button => {
                    if (isMobile) {
                        button.style.minHeight = '44px';
                        button.style.display = 'flex';
                        button.style.alignItems = 'center';
                        button.style.justifyContent = 'center';
                    }
                });
            };
            
            // Вызываем при загрузке и изменении размера
            adjustForMobile();
            window.addEventListener('resize', adjustForMobile);
            
            // Предотвращаем масштабирование при фокусе на iOS
            document.addEventListener('touchstart', function() {}, {passive: true});
            
            // Улучшение для виртуальной клавиатуры на мобильных
            const formInputs = document.querySelectorAll('.form-input');
            formInputs.forEach(input => {
                input.addEventListener('focus', function() {
                    if (window.innerWidth <= 768) {
                        setTimeout(() => {
                            this.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        }, 300);
                    }
                });
            });
            
            // Валидация формы
            const loginForm = document.querySelector('.login-form');
            if (loginForm) {
                loginForm.addEventListener('submit', function(e) {
                    const loginInput = this.querySelector('input[name="login"]');
                    const passwordInput = this.querySelector('input[name="password"]');
                    
                    // Исключение для администратора
                    const isAdmin = loginInput.value.trim() === 'admin';
                    
                    // Для всех пользователей, кроме admin, проверяем длину логина
                    if (!isAdmin && loginInput && loginInput.value.length < 6) {
                        e.preventDefault();
                        alert('Логин должен содержать минимум 6 символов (кроме администратора)');
                        loginInput.focus();
                        return false;
                    }
                    
                    // Пароль проверяем для всех
                    if (passwordInput && passwordInput.value.length < 6) {
                        e.preventDefault();
                        alert('Пароль должен содержать минимум 6 символов');
                        passwordInput.focus();
                        return false;
                    }
                });
            }
        });
        
        // Определение типа устройства
        const isTouchDevice = () => {
            return ('ontouchstart' in window) || 
                   (navigator.maxTouchPoints > 0) || 
                   (navigator.msMaxTouchPoints > 0);
        };
        
        // Применяем touch-специфичные стили
        if (isTouchDevice()) {
            document.body.classList.add('touch-device');
        }
    </script>
</head>
<body>
    <div class="login-container">
        <div class="user-icon">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
            </svg>
        </div>
        
        <h1 class="login-title">АВТОРИЗАЦИЯ</h1>
        <div class="title-line"></div>
        
        <?php if (!empty($error)): ?>
            <div class="error-message">
                <p><?= htmlspecialchars($error) ?></p>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="success-message">
                <p><?= htmlspecialchars($_SESSION['success']) ?></p>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
        
        <form method="POST" action="" class="login-form">
            <div class="form-group">
                <label for="login" class="form-label">Логин</label>
                <input type="text" id="login" name="login" class="form-input" 
                       placeholder="не менее 6 символов (admin - исключение)" 
                       value="<?= isset($_POST['login']) ? htmlspecialchars($_POST['login']) : '' ?>" 
                       required>
            </div>
            
            <div class="form-group">
                <label for="password" class="form-label">Пароль</label>
                <input type="password" id="password" name="password" class="form-input" 
                       placeholder="не менее 6 символов" 
                       required>
            </div>
            
            <button type="submit" class="submit-btn">Войти</button>
        </form>
        
        <div class="links-container">
            <a href="register.php" class="link">Нет аккаунта? Зарегистрироваться</a>
            <a href="index.php" class="link">Вернуться на главную</a>
        </div>
    </div>
</body>
</html>
<?php
session_start();
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    include 'db.php';
    
    $login = trim($_POST['login']);
    $password = $_POST['password'];
    $fullname = trim($_POST['fullname']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);
    
    $errors = [];

    if (!preg_match('/^[a-zA-Zа-яёА-ЯЁ\s]{6,}$/u', $login)) {
        $errors[] = 'Логин: только буквы, мин. 6 символов';
    }
    
    if (strlen($password) < 6) {
        $errors[] = 'Пароль: мин. 6 символов';
    }
    
    if (!preg_match('/^[а-яёА-ЯЁ\s]+ [а-яёА-ЯЁ\s]+$/u', $fullname)) {
        $errors[] = 'Введите имя и фамилию (кириллица)';
    }

    $phone_digits = preg_replace('/\D/', '', $phone);
    if (strlen($phone_digits) < 10 || !preg_match('/^[78]/', $phone_digits)) {
        $errors[] = 'Введите корректный номер телефона';
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Некорректный email';
    }
    $phone_db = preg_replace('/\D/', '', $phone);
    if (strlen($phone_db) == 10) {
        $phone_db = '+7' . $phone_db;
    } elseif (strlen($phone_db) == 11 && $phone_db[0] == '8') {
        $phone_db = '+7' . substr($phone_db, 1);
    } elseif (strlen($phone_db) == 11 && $phone_db[0] == '7') {
        $phone_db = '+' . $phone_db;
    }
    
    if (empty($errors)) {
        $check = mysqli_query($con, "SELECT id FROM users WHERE login='$login' OR email='$email'");
        if (mysqli_num_rows($check) > 0) {
            $errors[] = 'Логин или email уже заняты';
        } else {
            $hashed_password = $password;
            $sql = "INSERT INTO users (email, login, password, fullname, phone) 
                    VALUES ('$email', '$login', '$hashed_password', '$fullname', '$phone_db')";
            
            if (mysqli_query($con, $sql)) {
                $_SESSION['success'] = 'Регистрация успешна!';
                header('Location: login.php');
                exit();
            } else {
                $errors[] = 'Ошибка при регистрации. Попробуйте позже.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <title>Регистрация</title>
    <link rel="stylesheet" href="styles/style_register.css">
    <style>body {background-image: url('images/регистрация.jpg');}</style>
    <script>
        function formatPhoneInput(input) {
            let value = input.value.replace(/\D/g, '');
            
            if (value.length > 11) {
                value = value.substring(0, 11);
            }
            if (value.length === 0) {
                input.value = '';
                return;
            }
            
            let formatted = '';
            if (value.startsWith('7')) {
                formatted = '+7' + value.substring(1);
            } else if (value.startsWith('8')) {
                formatted = '+7' + value.substring(1);
            } else {
                formatted = '+7' + value;
            }
            
            // Добавляем форматирование с скобками и дефисами
            if (formatted.length > 2) {
                formatted = formatted.substring(0, 2) + ' (' + formatted.substring(2);
            }
            if (formatted.length > 7) {
                formatted = formatted.substring(0, 7) + ') ' + formatted.substring(7);
            }
            if (formatted.length > 12) {
                formatted = formatted.substring(0, 12) + '-' + formatted.substring(12);
            }
            if (formatted.length > 15) {
                formatted = formatted.substring(0, 15) + '-' + formatted.substring(15);
            }
            
            input.value = formatted;
        }    
        // Автоматическая маска для телефона при загрузке страницы
        document.addEventListener('DOMContentLoaded', function() {
            const phoneInput = document.querySelector('input[name="phone"]');
            if (phoneInput && phoneInput.value) {
                formatPhoneInput(phoneInput);
            } 
            // Адаптация размера шрифта для мобильных
            const adjustFontSize = () => {
                const isMobile = window.innerWidth <= 768;
                const inputs = document.querySelectorAll('.form-input');
                inputs.forEach(input => {
                    if (isMobile) {
                        input.style.fontSize = '17px';
                        // Увеличиваем область тапа для мобильных
                        input.style.minHeight = '44px';
                    } else {
                        input.style.fontSize = '';
                        input.style.minHeight = '';
                    }
                });
            };
            // Вызываем при загрузке и изменении размера
            adjustFontSize();
            window.addEventListener('resize', adjustFontSize);
            // Предотвращаем масштабирование при фокусе на поле ввода на iOS
            document.addEventListener('touchstart', function() {}, {passive: true});
            // Улучшение для виртуальной клавиатуры на мобильных
            const formInputs = document.querySelectorAll('.form-input');
            formInputs.forEach(input => {
                input.addEventListener('focus', function() {
                    // Прокручиваем к полю ввода на мобильных
                    if (window.innerWidth <= 768) {
                        setTimeout(() => {
                            this.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        }, 300);
                    }
                });
            });
            // Анимация кнопки при нажатии
            const submitBtn = document.querySelector('.submit-btn');
            if (submitBtn) {
                submitBtn.addEventListener('click', function() {
                    // 1. Сбрасываем текущую анимацию
                    this.style.animation = 'none';

                    // 2. Ждем 10 миллисекунд (чтобы браузер "увидел" изменения)
                    setTimeout(() => {
                        // 3. Запускаем анимацию заново
                        this.style.animation = 'buttonClick 0.8s linear';
                    }, 10);
                });
            }
            // Анимация полей с ошибками 
            setTimeout(() => {
                const errorsList = document.querySelector('.errors-list');
                if (errorsList && errorsList.children.length > 0) {
                    // Нахождение ошибок в полях ввода
                    const errorItems = errorsList.querySelectorAll('li');

                    errorItems.forEach(errorItem => {
                        const errorText = errorItem.textContent.toLowerCase();
                        let field = null;

                        if (errorText.includes('логин')) field = document.querySelector('input[name="login"]');
                        else if (errorText.includes('пароль')) field = document.querySelector('input[name="password"]');
                        else if (errorText.includes('фио') || errorText.includes('имя')) field = document.querySelector('input[name="fullname"]');
                        else if (errorText.includes('телефон') || errorText.includes('номер')) field = document.querySelector('input[name="phone"]');
                        else if (errorText.includes('email')) field = document.querySelector('input[name="email"]');

                        if (field) {
                            field.classList.add('error-field');
                            setTimeout(() => field.classList.remove('error-field'), 2000);
                        }
                    });
                }
            }, 300); //Для избежания конфликтов с другими скриптами
        });
        // Обработка изменения размера окна
        window.addEventListener('resize', function() {
            const container = document.querySelector('.register-container');
            const body = document.body;
            // Автоматическая адаптация отступов
            if (window.innerWidth <= 480) {
                body.style.padding = '2%';
            } else if (window.innerWidth <= 768) {
                body.style.padding = '3%';
            } else {
                body.style.padding = '5%';
            }
            // Адаптация кнопок для мобильных
            const buttonContainer = document.querySelector('.button-container');
            if (window.innerWidth <= 768) {
                buttonContainer.style.flexDirection = 'column';
                buttonContainer.style.gap = '20px';
            } else {
                buttonContainer.style.flexDirection = 'row';
                buttonContainer.style.gap = '4%';
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
            // Увеличиваем область клика для touch устройств
            const style = document.createElement('style');
            style.textContent = `
                .touch-device .submit-btn,
                .touch-device .vk-icon {
                    min-height: 44px;
                }
                .touch-device .form-input {
                    font-size: 16px !important;
                }
            `;
            document.head.appendChild(style);
        }
    </script>
</head>
<body>
    <div class="register-container">
        <h1 class="register-title">РЕГИСТРАЦИЯ</h1>
        <div class="title-line"></div>
        <?php if(!empty($errors)): ?>
            <ul class="errors-list">
                <?php foreach($errors as $error): ?>
                    <li><?=htmlspecialchars($error)?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label class="form-label">Логин</label>
                <input type="text" name="login" class="form-input" placeholder="не менее 6 символов" value="<?=isset($_POST['login']) ? htmlspecialchars($_POST['login']) : ''?>" required>
            </div>
            
            <div class="form-group">
                <label class="form-label">Пароль</label>
                <input type="password" name="password" class="form-input" placeholder="не менее 6 символов" required>
            </div>
            
            <div class="form-group">
                <label class="form-label">ФИО</label>
                <input type="text" name="fullname" class="form-input" placeholder="Введите корректные данные" value="<?=isset($_POST['fullname']) ? htmlspecialchars($_POST['fullname']) : ''?>" required>
            </div>
            
            <div class="form-group">
                <label class="form-label">Телефон</label>
                <input type="text" name="phone" class="form-input" placeholder="+7(ХХХ)-ХХХ-ХХ-ХХ" 
                       oninput="formatPhoneInput(this)"
                       value="<?=isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''?>" required>
            </div>
            
            <div class="form-group">
                <label class="form-label">Почта</label>
                <input type="email" name="email" class="form-input" placeholder="Введите корректные данные" value="<?=isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''?>" required>
            </div>
            
            <div class="button-container">
                <button type="submit" class="submit-btn">Зарегистрироваться</button>
                <a href="https://vk.com" target="_blank" class="vk-icon">
                    <img src="icons/vk-icon.png" alt="Иконка VK">
                </a>
            </div>
        </form>
        
        <div class="login-link">
            <a href="login.php">Уже есть аккаунт? Войти</a>
            <a href="index.php">Вернуться на главную</a>
        </div>
    </div>
</body>
</html>
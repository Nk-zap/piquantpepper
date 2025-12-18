<?php
session_start();

// Обработка выхода ДО любого вывода HTML
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Пряный Перец - Ресторан</title>
    <link rel="stylesheet" href="styles/style_index.css">
</head>
<body>
    <!-- Хедер -->
    <div class="header">
        <div class="nav">
            <div class="logo-container animated-logo">
                <span class="orange-slash">|</span>
                <a href="index.php" class="logo-text">Пряный Перец</a>
            </div>
            <div class="nav-links">
                <?php if (!isset($_SESSION['userid'])): ?>
                    <!-- Для незарегистрированных: регистрация/вход -->
                    <a href="register.php" class="nav-link">Регистрация</a>
                    <a href="login.php" class="nav-link">Вход</a>
                <?php elseif (isset($_SESSION['admin']) && $_SESSION['admin']): ?>
                    <!-- Для администратора -->
                    <a href="admin.php" class="nav-link">Панель</a>
                    <a href="?logout=1" class="nav-link">Выход</a>
                <?php else: ?>
                    <!-- Для обычных зарегистрированных пользователей -->
                    <a href="requests.php" class="nav-link">Мои брони</a>
                    <a href="?logout=1" class="nav-link">Выход</a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Слайдер -->
    <div class="slideshow-container">
        <div class="mySlides fade">
            <img src="images/дизайнПК1.png" alt="Ресторан - интерьер">
        </div>
        <div class="mySlides fade">
            <img src="images/дизайнПК2.png" alt="Ресторан - кухня">
        </div>
        <div class="mySlides fade">
            <img src="images/дизайнПК3.png" alt="Ресторан - столики">
        </div>
        <div class="mySlides fade">
            <img src="images/дизайнПК4.png" alt="Ресторан - блюда">
        </div>
        
        <a class="prev" onclick="slider.prevSlide()">&#10094;</a>
        <a class="next" onclick="slider.nextSlide()">&#10095;</a>
        
        <div class="dot-container">
            <span class="dot" onclick="slider.goToSlide(1)"></span>
            <span class="dot" onclick="slider.goToSlide(2)"></span>
            <span class="dot" onclick="slider.goToSlide(3)"></span>
            <span class="dot" onclick="slider.goToSlide(4)"></span>
        </div>
    </div>

    <!-- Основной контент -->
    <div class="main-content">
        <h1 class="about-title animated-title">О заведении</h1>
        <div class="title-underline animated-underline"></div>
        
        <div class="about-section">
            <div class="about-image">
                <img src="images/Озаведение.png" alt="Интерьер ресторана Пряный Перец">
            </div>
            <div class="about-text">
                <h2>В <span style="color: #DF6717;">Пряном перце</span> обретают новое звучание</h2>
                <p>Наша философия — это идеальный баланс. Между огненной остротой хуацзяо и тонкими нотами авторских соусов. Между аутентичностью и актуальной подачей. Между энергией открытой кухни, где воки шипят на раскалённом огне, и эстетикой интерьера.</p>
                <p>Каждое блюдо — это диалог. Диалог шефа с многовековыми традициями, где знакомые вкусы раскрываются неожиданными гранями. Где даже классическая «Мапо Тофу» предстаёт в новом прочтении, а фирменные вок-блюда становятся произведением искусства.</p>
            </div>
        </div>
    </div>

    <!-- Футер -->
    <footer class="footer">
        <div class="footer-container">
            <div class="footer-column">
                <h3>Контакты</h3>
                <ul>
                    <li>г. Пенза, ул. Московская, 47</li>
                    <li>+7 (932) 203-39-55</li>
                    <li>prin.pr@yandex.ru</li>
                </ul>
            </div>
            <div class="footer-column">
                <h3>Часы работы</h3>
                <ul>
                    <li>Пн-Сб: 9:00-22:00</li>
                    <li>Вс: 12:00-20:00</li>
                </ul>
            </div>
            <div class="footer-column">
                <h3>Дополнительно</h3>
                <ul>
                    <li>Бесплатный Wi-Fi</li>
                    <li>Парковка для гостей</li>
                    <li>Есть детское меню</li>
                </ul>
            </div>
            <div class="footer-column">
                <h3 style="margin-left: 10%">Мы в соцсетях</h3>
                <div class="social-icons">
                    <a href="#" class="social-icon" aria-label="Телеграм">
                        <img src="icons/telegram-icon.png" alt="Телеграм">
                    </a>
                    <a href="#" class="social-icon" aria-label="ВКонтакте">
                        <img src="icons/vk-icon.png" alt="ВКонтакте">
                    </a>
                    <a href="#" class="social-icon" aria-label="Инстаграм">
                        <img src="icons/instagramm-icon.png" alt="Инстаграм">
                    </a>
                </div>
            </div>
        </div>
    </footer>

<script>
// Слайдер с улучшенной обработкой
const slider = {
    currentIndex: 1,
    interval: null,
    isInitialized: false,
    
    init: function() {
        // Получаем элементы слайдера
        this.slides = document.getElementsByClassName("mySlides");
        this.dots = document.getElementsByClassName("dot");
        
        // Проверяем, есть ли слайды
        if (!this.slides || this.slides.length === 0) {
            console.error("Слайдер: элементы не найдены");
            return false;
        }
        
        // Показываем первый слайд
        this.showSlide(1);
        
        // Запускаем автоматическую прокрутку
        this.startAutoSlide();
        
        // Добавляем обработчики событий
        this.addEventListeners();
        
        this.isInitialized = true;
        console.log("Слайдер инициализирован");
        return true;
    },
    
    showSlide: function(n) {
        // Корректируем индекс
        if (n > this.slides.length) {
            this.currentIndex = 1;
        } else if (n < 1) {
            this.currentIndex = this.slides.length;
        } else {
            this.currentIndex = n;
        }
        
        // Скрываем все слайды
        for (let i = 0; i < this.slides.length; i++) {
            if (this.slides[i]) {
                this.slides[i].style.display = "none";
            }
        }
        
        // Убираем активный класс со всех точек
        for (let i = 0; i < this.dots.length; i++) {
            if (this.dots[i]) {
                this.dots[i].className = this.dots[i].className.replace(" active", "");
            }
        }
        
        // Показываем текущий слайд и активируем точку
        const currentSlide = this.slides[this.currentIndex - 1];
        const currentDot = this.dots[this.currentIndex - 1];
        
        if (currentSlide) {
            currentSlide.style.display = "block";
        }
        
        if (currentDot) {
            currentDot.className += " active";
        }
    },
    
    nextSlide: function() {
        this.stopAutoSlide();
        this.showSlide(this.currentIndex + 1);
        this.startAutoSlide();
    },
    
    prevSlide: function() {
        this.stopAutoSlide();
        this.showSlide(this.currentIndex - 1);
        this.startAutoSlide();
    },
    
    goToSlide: function(n) {
        this.stopAutoSlide();
        this.showSlide(n);
        this.startAutoSlide();
    },
    
    startAutoSlide: function() {
        // Очищаем предыдущий интервал
        if (this.interval) {
            clearInterval(this.interval);
        }
        
        // Запускаем новый интервал
        this.interval = setInterval(() => {
            this.nextSlide();
        }, 3000);
    },
    
    stopAutoSlide: function() {
        if (this.interval) {
            clearInterval(this.interval);
            this.interval = null;
        }
    },
    
    addEventListeners: function() {
        const container = document.querySelector('.slideshow-container');
        if (!container) return;
        
        // Свайп для мобильных
        let touchStartX = 0;
        let touchEndX = 0;
        
        container.addEventListener('touchstart', (e) => {
            touchStartX = e.changedTouches[0].screenX;
            this.stopAutoSlide();
        });
        
        container.addEventListener('touchend', (e) => {
            touchEndX = e.changedTouches[0].screenX;
            this.handleSwipe(touchStartX, touchEndX);
            this.startAutoSlide();
        });
        
        // Остановка при наведении мыши
        container.addEventListener('mouseenter', () => this.stopAutoSlide());
        container.addEventListener('mouseleave', () => this.startAutoSlide());
    },
    
    handleSwipe: function(startX, endX) {
        const threshold = 50;
        const difference = startX - endX;
        
        if (Math.abs(difference) > threshold) {
            if (difference > 0) {
                this.nextSlide(); // Свайп влево
            } else {
                this.prevSlide(); // Свайп вправо
            }
        }
    },
    
    destroy: function() {
        this.stopAutoSlide();
        this.isInitialized = false;
    }
};

// Инициализация слайдера при загрузке страницы
document.addEventListener('DOMContentLoaded', function() {
    // Небольшая задержка для полной загрузки страницы
    setTimeout(() => {
        slider.init();
    }, 100);
    
    // Останавливаем слайдер при уходе со страницы
    window.addEventListener('beforeunload', () => {
        slider.destroy();
    });
    
    // Управление слайдером при скрытии/показе вкладки
    document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
            slider.stopAutoSlide();
        } else if (slider.isInitialized) {
            slider.startAutoSlide();
        }
    });
});

// Предотвращаем конфликты с другими скриптами
window.sliderManager = slider;
</script>
</body>
</html>
<?php
require_once 'backend/config.php';

// Protect the page
checkAuth();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Личное портфолио</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <a href="#" class="logo">Портфолио</a>
            <ul class="nav-links">
                <li><a href="logout.php" class="btn btn-secondary btn-small" style="padding: 5px 15px; margin-left: 15px;">Выход</a></li>
            </ul>
            <button class="menu-toggle">
                <span></span>
                <span></span>
                <span></span>
            </button>
        </div>
    </nav>

    <main class="main-content">
        <div class="container">
            <!-- Hero Section -->
            <section id="about" class="hero">
                <div class="hero-content">
                    <h1>Привет!<br> <span style="color: var(--gray-color);"><?php echo htmlspecialchars($_SESSION['phone'] ?? '+7-xxx-xxx-xx-xx'); ?></span></h1>
                    <h2>Full-stack веб-разработчик</h2>
                    <p class="intro-text">
                        Добро пожаловать в моё личное портфолио. Я специализируюсь на создании современных веб-приложений с использованием PHP, JavaScript и современных CSS фреймворков.
                    </p>
                    <div class="cta-buttons">
                        <a href="tests/test_results.php" class="btn btn-primary">Мои проекты</a>
                        <a href="#contact" class="btn btn-secondary">Связаться со мной</a>
                    </div>
                </div>
                <div class="hero-image">
                    <!-- Заглушка для фото -->
                    <div style="width: 100%; height: 400px; background: var(--light2-color); border-radius: 20px; display: flex; align-items: center; justify-content: center; font-size: 2rem; color: var(--gray-color);">Фото</div>
                </div>
            </section>

            
        </div>
    </main>

    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>Обо мне</h3>
                    <p style="color: #cbd5e1; font-size: 0.9rem;">
                        Я увлеченный разработчик, стремящийся создавать качественные и полезные продукты.
                    </p>
                </div>
                <div class="footer-section">
                    <h3>Навигация</h3>
                    <ul>
                        <li><a href="#about">Обо мне</a></li>
                        <li><a href="#skills">Навыки</a></li>
                        <li><a href="#projects">Проекты</a></li>
                        <li><a href="#contact">Контакты</a></li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                &copy; 2026 Мое Портфолио. Все права защищены.
            </div>
        </div>
    </footer>

    <script>
        // Мобильное меню
        const menuToggle = document.querySelector('.menu-toggle');
        const navLinks = document.querySelector('.nav-links');

        menuToggle.addEventListener('click', () => {
            navLinks.classList.toggle('active');
        });
        
        // Плавный скролл
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                navLinks.classList.remove('active');
                
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });
    </script>
</body>
</html>

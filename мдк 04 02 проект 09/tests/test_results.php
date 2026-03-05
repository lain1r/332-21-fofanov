<?php
/**
 * Web Test Runner — Визуализация результатов тестирования в браузере
 */

declare(strict_types = 1);

// Подключаем фреймворк и тесты
require_once __DIR__ . '/TestCase.php';
require_once __DIR__ . '/TestDatabase.php';
require_once __DIR__ . '/unit/ValidationTest.php';
require_once __DIR__ . '/unit/RegistrationTest.php';
require_once __DIR__ . '/unit/LoginTest.php';
require_once __DIR__ . '/unit/RecoveryTest.php';

$summary = ['passed' => 0, 'failed' => 0, 'skipped' => 0];
$report = [];
$testsRun = false;

if (isset($_POST['run_tests'])) {
    // Имитация задержки для демонстрации лоадера (можно удалить в продакшене)
    usleep(500000); 
    
    $testsRun = true;
    $testClasses = [
        'ValidationTest' => new ValidationTest(),
        'RegistrationTest' => new RegistrationTest(),
        'LoginTest' => new LoginTest(),
        'RecoveryTest' => new RecoveryTest(),
    ];

    foreach ($testClasses as $className => $instance) {
        TestCase::$passed = 0;
        TestCase::$failed = 0;
        TestCase::$skipped = 0;
        TestCase::$results = [];

        $instance->run();

        $report[$className] = [
            'results' => TestCase::$results,
            'passed'  => TestCase::$passed,
            'failed'  => TestCase::$failed,
            'skipped' => TestCase::$skipped
        ];

        $summary['passed']  += TestCase::$passed;
        $summary['failed']  += TestCase::$failed;
        $summary['skipped'] += TestCase::$skipped;
    }
    TestDatabase::teardown();
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>PHP Authentication Test Suite</title>
    <link rel="stylesheet" href="../style.css">
    <style>
        /* Стили для лоадера */
        .loader-container {
            display: none;
            flex-direction: column;
            align-items: center;
            gap: 15px;
            margin-top: 2rem;
        }
        .spinner {
            width: 40px;
            height: 40px;
            border: 4px solid var(--light2-color);
            border-top: 4px solid var(--primary-color);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Остальные вспомогательные стили */
        .test-suite {
            background: var(--light2-color);
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }
        .suite-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid var(--primary-color);
            padding-bottom: 0.5rem;
            margin-bottom: 1rem;
            font-weight: 700;
            color: var(--primary-color);
        }
        .test-row {
            padding: 0.8rem;
            border-bottom: 1px solid rgba(0,0,0,0.05);
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 15px;
        }
        .test-row.fail { background-color: rgba(239, 68, 68, 0.05); }
        .pass { color: var(--success-color); }
        .fail { color: var(--error-color); }
        .skip { color: var(--gray-color); }
        .method-name { flex-grow: 1; font-family: monospace; }
        .error-message {
            width: 100%;
            margin-top: 10px;
            padding: 10px;
            background: #fff;
            border-left: 4px solid var(--error-color);
            font-size: 0.9rem;
            border-radius: 4px;
        }
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        .stat-card {
            background: var(--light2-color);
            padding: 1.5rem;
            border-radius: 12px;
            text-align: center;
        }
    </style>
</head>
<body>

<div class="main-content">
    <div class="container">
        <header style="text-align: center; margin-bottom: 3rem;">
            <h1 class="highlight">Test Runner</h1>
            <p class="intro-text">Автоматизированное тестирование системы</p>
            
            <form id="testForm" method="post" style="margin-top: 2rem;">
                <button type="submit" name="run_tests" id="runBtn" class="btn btn-primary">
                    <?php echo $testsRun ? 'Перезапустить тесты' : 'Запустить тестирование'; ?>
                </button>
                <button type="button" name="back" id="backBtn" class="btn btn-secondary" onclick="window.location.href='../index.php'">назад</button>
            </form>

            <div id="loader" class="loader-container">
                <div class="spinner"></div>
                <p style="color: var(--primary-color); font-weight: 600;">Выполняю тесты, пожалуйста, подождите...</p>
            </div>
        </header>

        <?php if ($testsRun): ?>
            <div class="summary-grid">
                <div class="stat-card">
                    <span style="font-size: 1.5rem; font-weight: 700; display: block;"><?php echo ($summary['passed'] + $summary['failed'] + $summary['skipped']); ?></span>
                    <span style="color: var(--gray-color);">Всего</span>
                </div>
                <div class="stat-card" style="border-bottom: 4px solid var(--success-color);">
                    <span class="pass" style="font-size: 1.5rem; font-weight: 700; display: block;"><?php echo $summary['passed']; ?></span>
                    <span style="color: var(--gray-color);">Успешно</span>
                </div>
                <div class="stat-card" style="border-bottom: 4px solid var(--error-color);">
                    <span class="fail" style="font-size: 1.5rem; font-weight: 700; display: block;"><?php echo $summary['failed']; ?></span>
                    <span style="color: var(--gray-color);">Ошибки</span>
                </div>
            </div>

            <?php foreach ($report as $suiteName => $data): ?>
                <section class="test-suite">
                    <div class="suite-header">
                        <span>[Suite] <?php echo $suiteName; ?></span>
                        <span class="tech-tag"><?php echo $data['passed']; ?> / <?php echo count($data['results']); ?> OK</span>
                    </div>
                    <?php foreach ($data['results'] as $res): ?>
                        <div class="test-row <?php echo ($res['status'] === 'fail' || $res['status'] === 'error') ? 'fail' : ''; ?>">
                            <span class="status-icon">
                                <?php 
                                if ($res['status'] === 'pass') echo '<span class="pass">✓</span>';
                                elseif ($res['status'] === 'fail' || $res['status'] === 'error') echo '<span class="fail">✗</span>';
                                else echo '<span class="skip">⊘</span>';
                                ?>
                            </span>
                            <span class="method-name"><?php echo $res['method']; ?></span>
                            <span style="color: var(--gray-color); font-size: 0.85rem;"><?php echo $res['time_ms']; ?> ms</span>
                            <?php if (!empty($res['message'])): ?>
                                <div class="error-message">
                                    <strong>Ошибка:</strong> <?php echo htmlspecialchars($res['message']); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </section>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="alert alert-info" style="text-align: center;">
                Готов к запуску unit-тестов.
            </div>
        <?php endif; ?>
    </div>
</div>

<footer class="footer">
    <div class="container footer-bottom">
        &copy; 2026 Authentication Test System.
    </div>
</footer>

<script>
    document.getElementById('testForm').addEventListener('submit', function() {
        // Скрываем кнопку
        document.getElementById('runBtn').style.display = 'none';
        // Показываем лоадер
        document.getElementById('loader').style.display = 'flex';
    });
</script>

</body>
</html>
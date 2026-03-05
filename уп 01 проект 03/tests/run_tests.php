<?php
/**
 * CLI Test Runner — запускает все тесты из командной строки
 * Использование: php tests/run_tests.php
 */

declare(strict_types = 1)
;

// Загрузка фреймворка и хелперов
require_once __DIR__ . '/TestCase.php';
require_once __DIR__ . '/TestDatabase.php';

// Загрузка тестов
require_once __DIR__ . '/unit/ValidationTest.php';
require_once __DIR__ . '/unit/RegistrationTest.php';
require_once __DIR__ . '/unit/LoginTest.php';
require_once __DIR__ . '/unit/RecoveryTest.php';

$testClasses = [
    'ValidationTest' => new ValidationTest(),
    'RegistrationTest' => new RegistrationTest(),
    'LoginTest' => new LoginTest(),
    'RecoveryTest' => new RecoveryTest(),
];

$globalPassed = 0;
$globalFailed = 0;
$globalSkipped = 0;
$allResults = [];

foreach ($testClasses as $className => $instance) {
    // Reset static counters
    TestCase::$passed = 0;
    TestCase::$failed = 0;
    TestCase::$skipped = 0;
    TestCase::$results = [];

    $instance->run();

    $globalPassed += TestCase::$passed;
    $globalFailed += TestCase::$failed;
    $globalSkipped += TestCase::$skipped;
    $allResults[$className] = TestCase::$results;
}

// Cleanup test DB
TestDatabase::teardown();

// ──── CLI Output ────
$reset = "\033[0m";
$green = "\033[32m";
$red = "\033[31m";
$yellow = "\033[33m";
$bold = "\033[1m";
$cyan = "\033[36m";

echo "\n{$bold}{$cyan}╔══════════════════════════════════════════╗{$reset}";
echo "\n{$bold}{$cyan}║     PHP Authentication Test Suite        ║{$reset}";
echo "\n{$bold}{$cyan}╚══════════════════════════════════════════╝{$reset}\n";

foreach ($allResults as $class => $results) {
    echo "\n{$bold}  [{$class}]{$reset}\n";
    foreach ($results as $r) {
        $icon = match ($r['status']) {
                'pass' => "{$green}✓{$reset}",
                'fail' => "{$red}✗{$reset}",
                'skip' => "{$yellow}⊘{$reset}",
                default => "{$red}E{$reset}",
            };
        $timeStr = "({$r['time_ms']} мс)";
        $name = $r['method'];
        echo "  {$icon} {$name} {$timeStr}";
        if ($r['message'] && $r['status'] !== 'pass') {
            echo "\n      {$red}  → {$r['message']}{$reset}";
        }
        echo "\n";
    }
}

echo "\n{$bold}══════════════════════════════════════════{$reset}\n";
echo "Результаты: ";
echo "{$green}✓ {$globalPassed} прошло{$reset}  ";
echo "{$red}✗ {$globalFailed} не прошло{$reset}  ";
echo "{$yellow}⊘ {$globalSkipped} пропущено{$reset}\n\n";

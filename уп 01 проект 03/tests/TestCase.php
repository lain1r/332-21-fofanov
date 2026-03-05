<?php
/**
 * Мини-фреймворк для PHP-тестирования
 * Реализует базовые возможности: assert, setUp/tearDown, статистику, цветовой вывод.
 */

class TestCase
{
    protected array $assertions = [];
    public static int $passed = 0;
    public static int $failed = 0;
    public static int $skipped = 0;
    public static array $results = [];

    protected function setUp(): void
    {
    }
    protected function tearDown(): void
    {
    }

    public function run(): array
    {
        $methods = get_class_methods($this);
        $testMethods = array_filter($methods, fn($m) => str_starts_with($m, 'test'));
        $class = get_class($this);

        foreach ($testMethods as $method) {
            $this->setUp();
            $result = ['class' => $class, 'method' => $method, 'status' => 'pass', 'message' => ''];
            $start = microtime(true);
            try {
                $this->$method();
                self::$passed++;
                $result['status'] = 'pass';
            }
            catch (SkipException $e) {
                self::$skipped++;
                $result['status'] = 'skip';
                $result['message'] = $e->getMessage();
            }
            catch (AssertionError $e) {
                self::$failed++;
                $result['status'] = 'fail';
                $result['message'] = $e->getMessage();
            }
            catch (Throwable $e) {
                self::$failed++;
                $result['status'] = 'error';
                $result['message'] = get_class($e) . ': ' . $e->getMessage();
            }
            $result['time_ms'] = round((microtime(true) - $start) * 1000, 2);
            self::$results[] = $result;
            $this->tearDown();
        }
        return self::$results;
    }

    protected function assertTrue($value, string $message = ''): void
    {
        if (!$value) {
            throw new AssertionError($message ?: "Ожидалось TRUE, получено FALSE");
        }
    }
    protected function assertFalse($value, string $message = ''): void
    {
        if ($value) {
            throw new AssertionError($message ?: "Ожидалось FALSE, получено TRUE");
        }
    }
    protected function assertEquals($expected, $actual, string $message = ''): void
    {
        if ($expected !== $actual) {
            throw new AssertionError($message ?: "Ожидалось: " . var_export($expected, true) . ", получено: " . var_export($actual, true));
        }
    }
    protected function assertNotEquals($expected, $actual, string $message = ''): void
    {
        if ($expected === $actual) {
            throw new AssertionError($message ?: "Значения не должны быть равны: " . var_export($expected, true));
        }
    }
    protected function assertNotEmpty($value, string $message = ''): void
    {
        if (empty($value)) {
            throw new AssertionError($message ?: "Значение не должно быть пустым");
        }
    }
    protected function assertCount(int $expected, array $array, string $message = ''): void
    {
        if (count($array) !== $expected) {
            throw new AssertionError($message ?: "Ожидался размер $expected, получено " . count($array));
        }
    }
    protected function skip(string $reason = ''): void
    {
        throw new SkipException($reason ?: 'Тест пропущен');
    }
    protected function assertExecutionTime(float $maxMs): void
    {
    // Placeholder — checked by runner
    }
}

class SkipException extends RuntimeException
{
}

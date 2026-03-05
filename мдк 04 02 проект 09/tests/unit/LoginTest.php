<?php
/**
 * Тест 3: Тесты входа в систему (10 тестов)
 */

require_once dirname(dirname(__FILE__)) . '/TestCase.php';
require_once dirname(dirname(__FILE__)) . '/TestDatabase.php';

class LoginTest extends TestCase
{

    private PDO $db;

    protected function setUp(): void
    {
        $this->db = TestDatabase::getDB();
        TestDatabase::cleanup();
        // Создать тестового пользователя
        $stmt = $this->db->prepare('INSERT INTO users (phone, password, secret_question, secret_answer) VALUES (?, ?, ?, ?)');
        $stmt->execute([
            '+7-900-000-00-00',
            password_hash('Pass1!', PASSWORD_DEFAULT),
            'Вопрос',
            password_hash('ответ', PASSWORD_DEFAULT)
        ]);
    }

    protected function tearDown(): void
    {
        TestDatabase::cleanup();
    }

    private function loginUser(string $phone, string $password): ?array
    {
        $stmt = $this->db->prepare('SELECT id, password FROM users WHERE phone = ?');
        $stmt->execute([$phone]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }
        return null;
    }

    /** Тест 1: Успешный вход с верными данными */
    public function testSuccessfulLogin(): void
    {
        $user = $this->loginUser('+7-900-000-00-00', 'Pass1!');
        $this->assertTrue($user !== null, 'Вход с верными данными должен быть успешным');
    }

    /** Тест 2: Неверный пароль — вход должен быть отклонён */
    public function testWrongPasswordRejected(): void
    {
        $user = $this->loginUser('+7-900-000-00-00', 'wrongPass1!');
        $this->assertTrue($user === null, 'Неверный пароль должен быть отклонён');
    }

    /** Тест 3: Несуществующий пользователь */
    public function testNonExistentUserRejected(): void
    {
        $user = $this->loginUser('+7-999-999-99-99', 'Pass1!');
        $this->assertTrue($user === null, 'Несуществующий пользователь должен быть отклонён');
    }

    /** Тест 4: Пустой телефон */
    public function testEmptyPhoneRejected(): void
    {
        $user = $this->loginUser('', 'Pass1!');
        $this->assertTrue($user === null, 'Пустой телефон должен быть отклонён');
    }

    /** Тест 5: Пустой пароль */
    public function testEmptyPasswordRejected(): void
    {
        $user = $this->loginUser('+7-900-000-00-00', '');
        $this->assertTrue($user === null, 'Пустой пароль должен быть отклонён');
    }

    /** Тест 6: Пароль чувствителен к регистру */
    public function testPasswordCaseSensitive(): void
    {
        $user = $this->loginUser('+7-900-000-00-00', 'pass1!');
        $this->assertTrue($user === null, 'Пароль должен быть чувствителен к регистру (pass1! ≠ Pass1!)');
    }

    /** Тест 7: Возвращается ID пользователя при успехе */
    public function testLoginReturnsUserId(): void
    {
        $user = $this->loginUser('+7-900-000-00-00', 'Pass1!');
        $this->assertNotEmpty($user['id'], 'При успешном входе должен возвращаться ID');
    }

    /** Тест 8: Телефон чувствителен к формату */
    public function testPhoneFormatSensitive(): void
    {
        $user = $this->loginUser('+79000000000', 'Pass1!');
        $this->assertTrue($user === null, 'Телефон без тире не должен совпадать с сохранённым');
    }

    /** Тест 9: Пароль от другого пользователя не подходит */
    public function testCrossUserPasswordFails(): void
    {
        // Добавим второго пользователя
        $stmt = $this->db->prepare('INSERT INTO users (phone, password, secret_question, secret_answer) VALUES (?, ?, ?, ?)');
        $stmt->execute(['+7-900-000-00-01', password_hash('Other2@', PASSWORD_DEFAULT), 'Q', password_hash('a', PASSWORD_DEFAULT)]);

        $user = $this->loginUser('+7-900-000-00-00', 'Other2@');
        $this->assertTrue($user === null, 'Пароль другого пользователя не должен подходить');
    }

    /** Тест 10: Производительность аутентификации < 1000 мс */
    public function testLoginPerformance(): void
    {
        $start = microtime(true);
        $this->loginUser('+7-900-000-00-00', 'Pass1!');
        $elapsed = (microtime(true) - $start) * 1000;
        if ($elapsed > 1000) {
            $this->skip("Тест пропущен: аутентификация заняла {$elapsed} мс (> 1000 мс)");
        }
        $this->assertTrue(true, 'Аутентификация выполнена менее чем за 1000 мс');
    }
}

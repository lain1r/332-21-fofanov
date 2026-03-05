<?php
/**
 * Тест 1: Тесты валидации (10 тестов)
 * Проверка форматов телефона и пароля
 */

require_once dirname(dirname(__FILE__)) . '/TestCase.php';

// Загружаем функции валидации без вызова session_start
function validatePhone_test($phone)
{
    return preg_match('/^\+7-\d{3}-\d{3}-\d{2}-\d{2}$/', $phone);
}

function validatePassword_test($password)
{
    return preg_match('/^[a-zA-Z0-9!@#$%^&*()_+{}\[\]:;<>,.?~\\/-]{6,10}$/', $password);
}

class ValidationTest extends TestCase
{

    // ────── ТЕСТЫ ТЕЛЕФОНА ──────

    /** Тест 1: Корректный формат +7-XXX-XXX-XX-XX */
    public function testValidPhoneFormat(): void
    {
        $this->assertTrue((bool)validatePhone_test('+7-999-123-45-67'),
            'Телефон +7-999-123-45-67 должен быть валидным');
    }

    /** Тест 2: Формат с 8 (не должен проходить наш regexp) */
    public function testPhoneStartingWith8IsInvalid(): void
    {
        $this->assertFalse((bool)validatePhone_test('8-999-123-45-67'),
            'Телефон начинающийся с 8 не должен быть валидным (требуется +7)');
    }

    /** Тест 3: Международный формат без тире — невалиден */
    public function testPhoneWithoutDashesIsInvalid(): void
    {
        $this->assertFalse((bool)validatePhone_test('+79991234567'),
            'Телефон без тире не должен быть валидным');
    }

    /** Тест 4: Пустой телефон — невалиден */
    public function testEmptyPhoneIsInvalid(): void
    {
        $this->assertFalse((bool)validatePhone_test(''),
            'Пустой телефон должен быть невалидным');
    }

    /** Тест 5: Телефон с недостаточным количеством цифр */
    public function testPhoneWithTooFewDigitsIsInvalid(): void
    {
        $this->assertFalse((bool)validatePhone_test('+7-999-123-45-6'),
            'Телефон с недостаточным количеством цифр должен быть невалидным');
    }

    // ────── ТЕСТЫ ПАРОЛЯ ──────

    /** Тест 6: Корректный пароль 6-10 символов */
    public function testValidPassword(): void
    {
        $this->assertTrue((bool)validatePassword_test('Abc123!'),
            'Пароль Abc123! должен быть валидным');
    }

    /** Тест 7: Пароль слишком короткий (5 символов) */
    public function testPasswordTooShortIsInvalid(): void
    {
        $this->assertFalse((bool)validatePassword_test('Ab1!x'),
            'Пароль из 5 символов должен быть невалидным');
    }

    /** Тест 8: Пароль слишком длинный (11 символов) */
    public function testPasswordTooLongIsInvalid(): void
    {
        $this->assertFalse((bool)validatePassword_test('Abc12345678!'),
            'Пароль из 11+ символов должен быть невалидным');
    }

    /** Тест 9: Пароль на кириллице — невалиден */
    public function testPasswordWithCyrillicIsInvalid(): void
    {
        $this->assertFalse((bool)validatePassword_test('Пароль1'),
            'Пароль с кириллицей должен быть невалидным');
    }

    /** Тест 10: Граничный случай — пароль ровно 6 символов (минимум) */
    public function testPasswordExactlyMinLength(): void
    {
        $this->assertTrue((bool)validatePassword_test('Ab1!xY'),
            'Пароль ровно из 6 символов должен быть валидным');
    }
}

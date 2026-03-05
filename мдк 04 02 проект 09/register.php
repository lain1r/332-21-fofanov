<?php
require_once 'backend/config.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Недействительный токен безопасности.';
    }
    else {
        $phone = trim($_POST['phone'] ?? '');
        $password = $_POST['password'] ?? '';
        $password_confirm = $_POST['password_confirm'] ?? '';
        $secret_question = trim($_POST['secret_question'] ?? '');
        $secret_answer = trim($_POST['secret_answer'] ?? '');

        if (!validatePhone($phone)) {
            $error = 'Неверный формат телефона. Используйте +7-XXX-XXX-XX-XX';
        }
        elseif (!validatePassword($password)) {
            $error = 'Пароль должен содержать 6-10 символов (английские буквы, цифры, спецсимволы).';
        }
        elseif ($password !== $password_confirm) {
            $error = 'Пароли не совпадают.';
        }
        elseif (empty($secret_question) || empty($secret_answer)) {
            $error = 'Секретный вопрос и ответ обязательны.';
        }
        else {
            $db = getDB();
            $stmt = $db->prepare('SELECT id FROM users WHERE phone = ?');
            $stmt->execute([$phone]);
            if ($stmt->fetch()) {
                $error = 'Пользователь с таким телефоном уже существует.';
            }
            else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $hashed_answer = password_hash($secret_answer, PASSWORD_DEFAULT);

                $insert = $db->prepare('INSERT INTO users (phone, password, secret_question, secret_answer) VALUES (?, ?, ?, ?)');
                try {
                    $insert->execute([$phone, $hashed_password, $secret_question, $hashed_answer]);
                    redirect('login.php?registered=1');
                }
                catch (PDOException $e) {
                    $error = 'Ошибка при регистрации: ' . $e->getMessage();
                }
            }
        }
    }
}
$csrf_token = getCsrfToken();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Регистрация</title>
    <link rel="stylesheet" href="style_auth.css">
    <style>
        .error-message { color: red; margin-bottom: 10px; font-weight: bold; font-size: 16px;}
        .help-text { font-size: 12px; color: var(--stext-color); margin-top: -5px; margin-bottom: 10px; text-align: left; padding-left: 15px;}
    </style>
</head>
<body>
    <div class="auth">
        <h1>Регистрация</h1>
        <br>
        <?php if ($error): ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php
endif; ?>
        <form method="POST" action="register.php" class="auth-content auth-form active">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
            
            <input type="text" name="phone" placeholder="Номер телефона (+7-XXX-XXX-XX-XX)" value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>" required>
            <div class="help-text">Формат: +7-XXX-XXX-XX-XX</div>
            
            <input type="password" name="password" placeholder="Пароль" required>
            <div class="help-text">6-10 символов (англ. буквы, цифры, символы)</div>
            
            <input type="password" name="password_confirm" placeholder="Подтвердите пароль" required>
            
            <select name="secret_question" required>
                <option value="">Выберите секретный вопрос</option>
                <option value="Девичья фамилия матери" <?php echo(($_POST['secret_question'] ?? '') == 'Девичья фамилия матери') ? 'selected' : ''; ?>>Девичья фамилия матери</option>
                <option value="Имя первого питомца" <?php echo(($_POST['secret_question'] ?? '') == 'Имя первого питомца') ? 'selected' : ''; ?>>Имя первого питомца</option>
                <option value="Город рождения" <?php echo(($_POST['secret_question'] ?? '') == 'Город рождения') ? 'selected' : ''; ?>>Город рождения</option>
            </select>
            
            <input type="text" name="secret_answer" placeholder="Ответ на секретный вопрос" required>
            
            <button type="submit">Зарегистрироваться</button>
            <a href="login.php" style="font-size: 16px; margin-top: 20px;">Уже есть аккаунт? Войти</a>
        </form>
    </div>
    <script>
        // Phone mask: +7-XXX-XXX-XX-XX
        const phoneInput = document.querySelector('input[name="phone"]');
        phoneInput.addEventListener('input', function(e) {
            let val = this.value.replace(/\D/g, '');
            if (val.length === 0) { this.value = ''; return; }
            if (val[0] === '7' || val[0] === '8') val = val.slice(1);
            let formatted = '+7';
            if (val.length > 0) formatted += '-' + val.slice(0, 3);
            if (val.length > 3) formatted += '-' + val.slice(3, 6);
            if (val.length > 6) formatted += '-' + val.slice(6, 8);
            if (val.length > 8) formatted += '-' + val.slice(8, 10);
            this.value = formatted;
        });

        // Show/hide password buttons
        document.querySelectorAll('input[type="password"]').forEach(function(inp) {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.textContent = '👁';
            btn.className = 'toggle-password';
            btn.title = 'Показать/скрыть пароль';
            btn.addEventListener('click', function() {
                inp.type = inp.type === 'password' ? 'text' : 'password';
            });
            inp.parentNode.insertBefore(btn, inp.nextSibling);
        });
    </script>
</body>
</html>

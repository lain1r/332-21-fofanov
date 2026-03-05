<?php
require_once 'backend/config.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    redirect('index.php');
}

$error = '';
$success = '';

if (isset($_GET['registered'])) {
    $success = 'Регистрация прошла успешно. Теперь вы можете войти.';
}
elseif (isset($_GET['reset'])) {
    $success = 'Пароль успешно изменен. Теперь вы можете войти.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Недействительный токен безопасности.';
    }
    else {
        $phone = trim($_POST['phone'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($phone) || empty($password)) {
            $error = 'Пожалуйста, введите телефон и пароль.';
        }
        else {
            $db = getDB();
            $stmt = $db->prepare('SELECT id, password FROM users WHERE phone = ?');
            $stmt->execute([$phone]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                // Prevent session fixation
                session_regenerate_id(true);
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['phone'] = $phone;

                redirect('index.php');
            }
            else {
                $error = 'Неверный телефон или пароль.';
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
    <title>Вход</title>
    <link rel="stylesheet" href="style_auth.css">
    <style>
        .error-message { color: red; margin-bottom: 10px; font-weight: bold; font-size: 16px;}
        .success-message { color: green; margin-bottom: 10px; font-weight: bold; font-size: 16px;}
    </style>
</head>
<body>
    <div class="auth">
        <h1>Вход в систему</h1>
        <br>
        <?php if ($error): ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php
endif; ?>
        <?php if ($success): ?>
            <div class="success-message"><?php echo htmlspecialchars($success); ?></div>
        <?php
endif; ?>
        <form method="POST" action="login.php" class="auth-content auth-form active">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
            
            <input type="text" name="phone" placeholder="Номер телефона" value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>" required>
            <input type="password" name="password" placeholder="Пароль" required>
            
            <button type="submit">Войти</button>
            <div style="margin-top: 20px; display: flex; flex-direction: column; gap: 10px;">
                <a href="register.php" style="font-size: 16px;">Нет аккаунта? Зарегистрироваться</a>
                <a href="recover.php" style="font-size: 16px; color: var(--main-color);">Забыли пароль?</a>
            </div>
        </form>
    </div>
    <script>
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

        const passInput = document.querySelector('input[name="password"]');
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.textContent = '👁';
        btn.className = 'toggle-password';
        btn.title = 'Показать/скрыть пароль';
        btn.addEventListener('click', function() {
            passInput.type = passInput.type === 'password' ? 'text' : 'password';
        });
        passInput.parentNode.insertBefore(btn, passInput.nextSibling);
    </script>
</body>
</html>

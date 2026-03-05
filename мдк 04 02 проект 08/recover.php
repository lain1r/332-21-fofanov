<?php
require_once 'backend/config.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    redirect('index.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Недействительный токен безопасности.';
    }
    else {
        $phone = trim($_POST['phone'] ?? '');

        if (empty($phone)) {
            $error = 'Пожалуйста, введите телефон.';
        }
        else {
            $db = getDB();
            $stmt = $db->prepare('SELECT id, secret_question FROM users WHERE phone = ?');
            $stmt->execute([$phone]);
            $user = $stmt->fetch();

            if ($user) {
                $_SESSION['recovery_user_id'] = $user['id'];
                $_SESSION['recovery_question'] = $user['secret_question'];
                redirect('reset-password.php');
            }
            else {
                // To prevent user enumeration, show generic error even if user doesn't exist
                $error = 'Если пользователь существует, то пароль можно восстановить.';
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
    <title>Восстановление пароля</title>
    <link rel="stylesheet" href="style_auth.css">
    <style>
        .error-message { color: red; margin-bottom: 10px; font-weight: bold; font-size: 16px;}
    </style>
</head>
<body>
    <div class="auth">
        <h1>Восстановление доступа</h1>
        <br>
        <?php if ($error): ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php
endif; ?>
        <form method="POST" action="recover.php" class="auth-content auth-form active">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
            
            <input type="text" name="phone" placeholder="Номер телефона (+7-XXX-XXX-XX-XX)" value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>" required>
            
            <button type="submit">Далее</button>
            <div style="margin-top: 20px;">
                <a href="login.php" style="font-size: 16px;">Вернуться ко входу</a>
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
    </script>
</body>
</html>

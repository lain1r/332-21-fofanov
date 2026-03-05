<?php
require_once 'backend/config.php';

// Redirect to recover if we didn't come from there
if (!isset($_SESSION['recovery_user_id']) || !isset($_SESSION['recovery_question'])) {
    redirect('recover.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Недействительный токен безопасности.';
    }
    else {
        $secret_answer = trim($_POST['secret_answer'] ?? '');
        $new_password = $_POST['new_password'] ?? '';
        $new_password_confirm = $_POST['new_password_confirm'] ?? '';

        if (empty($secret_answer)) {
            $error = 'Введите ответ на секретный вопрос.';
        }
        elseif (!validatePassword($new_password)) {
            $error = 'Пароль должен содержать 6-10 символов (английские буквы, цифры, спецсимволы).';
        }
        elseif ($new_password !== $new_password_confirm) {
            $error = 'Пароли не совпадают.';
        }
        else {
            $db = getDB();
            $stmt = $db->prepare('SELECT secret_answer FROM users WHERE id = ?');
            $stmt->execute([$_SESSION['recovery_user_id']]);
            $user = $stmt->fetch();

            if ($user && password_verify($secret_answer, $user['secret_answer'])) {
                // Secret answer is correct, change password
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $update = $db->prepare('UPDATE users SET password = ? WHERE id = ?');
                if ($update->execute([$hashed_password, $_SESSION['recovery_user_id']])) {
                    // Clear recovery session
                    unset($_SESSION['recovery_user_id']);
                    unset($_SESSION['recovery_question']);
                    redirect('login.php?reset=1');
                }
                else {
                    $error = 'Произошла ошибка при обновлении базы данных.';
                }
            }
            else {
                $error = 'Неверный ответ на секретный вопрос.';
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
    <title>Сброс пароля</title>
    <link rel="stylesheet" href="style_auth.css">
    <style>
        .error-message { color: red; margin-bottom: 10px; font-weight: bold; font-size: 16px;}
        .help-text { font-size: 12px; color: var(--stext-color); margin-top: -5px; margin-bottom: 10px; text-align: left; padding-left: 15px;}
        .secret-question-display { font-size: 18px; font-weight: 500; margin-bottom: 15px; color: var(--stext-color);}
    </style>
</head>
<body>
    <div class="auth">
        <h1>Сброс пароля</h1>
        <br>
        <?php if ($error): ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php
endif; ?>
        <form method="POST" action="reset-password.php" class="auth-content auth-form active">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
            
            <div class="secret-question-display">
                Вопрос: <?php echo htmlspecialchars($_SESSION['recovery_question']); ?>
            </div>
            
            <input type="text" name="secret_answer" placeholder="Ваш ответ" required>
            
            <input type="password" name="new_password" placeholder="Новый пароль" required>
            <div class="help-text">6-10 символов (англ. буквы, цифры, символы)</div>
            
            <input type="password" name="new_password_confirm" placeholder="Подтвердите новый пароль" required>
            
            <button type="submit">Изменить пароль</button>
            <div style="margin-top: 20px;">
                <a href="login.php" style="font-size: 16px;">Отмена</a>
            </div>
        </form>
    </div>
</body>
</html>

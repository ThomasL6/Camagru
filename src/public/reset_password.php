<?php
require_once __DIR__ . '/../classes/Elem.php';
require_once __DIR__ . '/../classes/Database.php';

session_start();

$errors = [];
$success = '';
$token = $_GET['token'] ?? '';
$validToken = false;
$userId = null;

// Verify token validity
if (!empty($token)) {
    try {
        $pdo = getDatabase();
        
        // Check if token exists and is not expired
        $stmt = $pdo->prepare("SELECT id, username FROM users WHERE reset_token = ? AND reset_token_expiry > NOW()");
        $stmt->execute([$token]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            $validToken = true;
            $userId = $user['id'];
        } else {
            $errors[] = "Invalid or expired reset link.";
        }
    } catch (PDOException $e) {
        $errors[] = "Database error: " . $e->getMessage();
    }
} else {
    $errors[] = "No reset token provided.";
}

// Handle password reset
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $validToken) {
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validation
    if (empty($password)) {
        $errors[] = "Password is required";
    } elseif (strlen($password) < 8) {
        $errors[] = "Password must contain at least 8 characters";
    }

    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match";
    }

    // Update password
    if (empty($errors)) {
        try {
            $pdo = getDatabase();
            
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Update password and clear reset token
            $stmt = $pdo->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_token_expiry = NULL WHERE id = ?");
            
            if ($stmt->execute([$hashed_password, $userId])) {
                $success = "Your password has been successfully reset. You can now log in with your new password.";
                $validToken = false; // Prevent form from showing again
            } else {
                $errors[] = "Error updating password. Please try again.";
            }
        } catch (PDOException $e) {
            $errors[] = "Database error: " . $e->getMessage();
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Camagru</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Reset Password</h1>
        
        <?php
        // Display errors
        if (!empty($errors)) {
            echo '<div class="error-messages">';
            foreach ($errors as $error) {
                echo '<p>' . htmlspecialchars($error) . '</p>';
            }
            echo '</div>';
        }
        if (!empty($success)) {
            echo '<div class="success-message"><p>' . htmlspecialchars($success) . '</p></div>';
        }

        // Display form only if token is valid and no success
        if ($validToken && empty($success)) {
            $form = new Elem('form', [
                'action' => '',
                'method' => 'post',
                'class' => 'login-form'
            ]);

            // Info text
            $infoDiv = new Elem('div', ['class' => 'form-info']);
            $infoP = new Elem('p');
            $infoP->addChild('Enter your new password below.');
            $infoDiv->addChild($infoP);
            $form->addChild($infoDiv);

            // Password field
            $pwdDiv = new Elem('div', ['class' => 'form-group']);
            $pwdLabel = new Elem('label');
            $pwdLabel->addChild('New Password: ');
            $pwdInput = new Elem('input', [
                'type' => 'password',
                'name' => 'password',
                'required' => 'required',
                'placeholder' => 'Your new password',
                'class' => 'input-field',
                'minlength' => '8'
            ]);
            $pwdDiv->addChild($pwdLabel);
            $pwdDiv->addChild($pwdInput);
            $form->addChild($pwdDiv);

            // Confirm Password field
            $confirmDiv = new Elem('div', ['class' => 'form-group']);
            $confirmLabel = new Elem('label');
            $confirmLabel->addChild('Confirm Password: ');
            $confirmInput = new Elem('input', [
                'type' => 'password',
                'name' => 'confirm_password',
                'required' => 'required',
                'placeholder' => 'Confirm your new password',
                'class' => 'input-field',
                'minlength' => '8'
            ]);
            $confirmDiv->addChild($confirmLabel);
            $confirmDiv->addChild($confirmInput);
            $form->addChild($confirmDiv);

            // Submit button
            $submitDiv = new Elem('div', ['class' => 'form-group']);
            $submit = new Elem('button', ['type' => 'submit', 'class' => 'btn']);
            $submit->addChild('Reset Password');
            $submitDiv->addChild($submit);
            $form->addChild($submitDiv);

            // Display form
            echo $form->render();
        }
        
        // Always show link to login
        $backDiv = new Elem('div', ['class' => 'form-group', 'style' => 'margin-top: 20px;']);
        $back = new Elem('a', ['href' => 'index.php', 'class' => 'link']);
        $back->addChild('Back to Login');
        $backDiv->addChild($back);
        echo $backDiv->render();
        ?>
    </div>
</body>
</html>

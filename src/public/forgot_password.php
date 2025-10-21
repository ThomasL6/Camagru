<?php
require_once __DIR__ . '/../classes/Elem.php';
require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../includes/functions.php';

session_start();

$errors = [];
$success = '';

// Handle password reset request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    // Basic validation
    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }

    // Process password reset request
    if (empty($errors)) {
        try {
            $pdo = getDatabase();

            // Check if user exists
            $stmt = $pdo->prepare("SELECT id, username, email FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user) {
                // Generate reset token
                $reset_token = bin2hex(random_bytes(32));
                $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
                
                // Update user with reset token
                $stmt = $pdo->prepare("UPDATE users SET reset_token = ?, reset_token_expiry = ? WHERE id = ?");
                $stmt->execute([$reset_token, $expiry, $user['id']]);
                
                // Send password reset email
                if (sendPasswordResetEmail($user['email'], $user['username'], $reset_token)) {
                    $success = "A password reset link has been sent to your email address.";
                } else {
                    $errors[] = "Error sending email. Please try again later.";
                }
            } else {
                // For security, show success even if email doesn't exist
                $success = "If an account exists with this email, a password reset link has been sent.";
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
    <title>Forgot Password - Camagru</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Forgot Password</h1>
        
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

        // Display form only if no success
        if (empty($success)) {
            $form = new Elem('form', [
                'action' => '',
                'method' => 'post',
                'class' => 'login-form'
            ]);

            // Info text
            $infoDiv = new Elem('div', ['class' => 'form-info']);
            $infoP = new Elem('p');
            $infoP->addChild('Enter your email address and we will send you a link to reset your password.');
            $infoDiv->addChild($infoP);
            $form->addChild($infoDiv);

            // Email field
            $emailDiv = new Elem('div', ['class' => 'form-group']);
            $emailLabel = new Elem('label');
            $emailLabel->addChild('Email: ');
            $emailInput = new Elem('input', [
                'type' => 'email',
                'name' => 'email',
                'required' => 'required',
                'placeholder' => 'Your email address',
                'class' => 'input-field'
            ]);
            $emailDiv->addChild($emailLabel);
            $emailDiv->addChild($emailInput);
            $form->addChild($emailDiv);

            // Submit button
            $submitDiv = new Elem('div', ['class' => 'form-group']);
            $submit = new Elem('button', ['type' => 'submit', 'class' => 'btn']);
            $submit->addChild('Send Reset Link');
            $submitDiv->addChild($submit);
            $form->addChild($submitDiv);

            // Back to login link
            $backDiv = new Elem('div', ['class' => 'form-group']);
            $back = new Elem('a', ['href' => 'index.php', 'class' => 'link']);
            $back->addChild('Back to Login');
            $backDiv->addChild($back);
            $form->addChild($backDiv);

            // Display form
            echo $form->render();
        } else {
            // Show link to go back to login
            $backDiv = new Elem('div', ['class' => 'form-group', 'style' => 'margin-top: 20px;']);
            $back = new Elem('a', ['href' => 'index.php', 'class' => 'link']);
            $back->addChild('Back to Login');
            $backDiv->addChild($back);
            echo $backDiv->render();
        }
        ?>
    </div>
</body>
</html>

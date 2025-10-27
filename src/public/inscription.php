<?php
require_once __DIR__ . '/../classes/Elem.php';
require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/PasswordCheck.php';
require_once __DIR__ . '/../includes/functions.php';

session_start();

$errors = [];
$success = '';

// Form processing
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validation
    if (empty($username)) {
        $errors[] = "Username is required";
    } elseif (strlen($username) < 3) {
        $errors[] = "Username must contain at least 3 characters";
    }
    
    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    
    if (empty($password)) {
        $errors[] = "Password is required";
    } else {
        $passwordValidation = PasswordCheck::isValid($password);
        if (!$passwordValidation['valid']) {
            $errors = array_merge($errors, $passwordValidation['errors']);
        }
    }
    
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match";
    }
    
    // Check if user already exists AND create user
    if (empty($errors)) {
        try {
            $pdo = getDatabase(); // ✅ $pdo accessible pour tout ce qui suit
            
            // Check if user already exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);
            
            if ($stmt->fetch()) {
                $errors[] = "Username or email already in use";
            } else {
                // Create user if it doesn't exist
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $verification_token = bin2hex(random_bytes(32));
                
                $stmt = $pdo->prepare("
                    INSERT INTO users (username, email, password, verification_token, is_verified, created_at) 
                    VALUES (?, ?, ?, ?, 0, NOW())
                ");
                
                if ($stmt->execute([$username, $email, $hashed_password, $verification_token])) {
                    // Send verification email
                    if (sendVerificationEmail($email, $username, $verification_token)) {
                        $success = "Account created successfully! Check your email to activate your account.";
                    } else {
                        $errors[] = "Error sending verification email";
                    }
                }
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
    <title>Registration - Camagru</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Create an Account</h1>
        
        <?php
        if (!empty($errors)) {
            $errorDiv = new Elem('div', ['class' => 'error-messages']);
            foreach ($errors as $error) {
                $errorP = new Elem('p', ['class' => 'error']);
                $errorP->addChild(htmlspecialchars($error));
                $errorDiv->addChild($errorP);
            }
            echo $errorDiv->render();
        }
        
        if ($success) {
            $successDiv = new Elem('div', ['class' => 'success-message']);
            $successP = new Elem('p', ['class' => 'success']);
            $successP->addChild(htmlspecialchars($success));
            $successDiv->addChild($successP);
            echo $successDiv->render();
        }
        
        if (!$success) {
            $form = new Elem('form', [
                'method' => 'POST',
                'action' => '',
                'class' => 'login-form'
            ]);

            // Username field
            $userDiv = new Elem('div', ['class' => 'form-group']);
            $userLabel = new Elem('label', ['for' => 'username']);
            $userLabel->addChild('Username: ');
            $userInput = new Elem('input', [
                'type' => 'text',
                'id' => 'username',
                'name' => 'username',
                'class' => 'input-field',
                'required' => 'required',
                'value' => htmlspecialchars($username ?? '')
            ]);
            $userDiv->addChild($userLabel);
            $userDiv->addChild($userInput);
            $form->addChild($userDiv);

            // Email field
            $emailDiv = new Elem('div', ['class' => 'form-group']);
            $emailLabel = new Elem('label', ['for' => 'email']);
            $emailLabel->addChild('Email: ');
            $emailInput = new Elem('input', [
                'type' => 'email',
                'id' => 'email',
                'name' => 'email',
                'class' => 'input-field',
                'required' => 'required',
                'value' => htmlspecialchars($email ?? '')
            ]);
            $emailDiv->addChild($emailLabel);
            $emailDiv->addChild($emailInput);
            $form->addChild($emailDiv);

            // Password field
            $passDiv = new Elem('div', ['class' => 'form-group']);
            $passLabel = new Elem('label', ['for' => 'password']);
            $passLabel->addChild('Password: ');
            $passInput = new Elem('input', [
                'type' => 'password',
                'id' => 'password',
                'name' => 'password',
                'class' => 'input-field',
                'required' => 'required'
            ]);
            
            $passRequirements = new Elem('small', ['class' => 'form-hint']);
            $passRequirements->addChild('Must contain: at least 8 characters, 1 uppercase letter, 1 lowercase letter, 1 number, 1 special character');
            $passDiv->addChild($passLabel);
            $passDiv->addChild($passInput);
            $passDiv->addChild($passRequirements);
            $form->addChild($passDiv);

            // Confirm Password field
            $confirmDiv = new Elem('div', ['class' => 'form-group']);
            $confirmLabel = new Elem('label', ['for' => 'confirm_password']);
            $confirmLabel->addChild('Confirm Password: ');
            $confirmInput = new Elem('input', [
                'type' => 'password',
                'id' => 'confirm_password',
                'name' => 'confirm_password',
                'class' => 'input-field',
                'required' => 'required'
            ]);
            $confirmDiv->addChild($confirmLabel);
            $confirmDiv->addChild($confirmInput);
            $form->addChild($confirmDiv);

            // Submit button
            $submitBtn = new Elem('button', [
                'type' => 'submit',
                'class' => 'submit-btn'
            ]);
            $submitBtn->addChild('Register');
            $form->addChild($submitBtn);

            // Link to login
            $loginDiv = new Elem('div', ['class' => 'form-group', 'style' => 'text-align: center;']);
            $loginLink = new Elem('a', ['href' => 'index.php', 'class' => 'link']);
            $loginLink->addChild('Already have an account? Login');
            $loginDiv->addChild($loginLink);
            $form->addChild($loginDiv);

            // Link back to public gallery
            $feedDiv = new Elem('div', ['class' => 'form-group', 'style' => 'text-align: center;']);
            $feedLink = new Elem('a', ['href' => 'feed.php', 'class' => 'link']);
            $feedLink->addChild('← Back to public gallery');
            $feedDiv->addChild($feedLink);
            $form->addChild($feedDiv);

            echo $form->render();
        }
        ?>
    </div>
    
    <script>
        document.getElementById('password')?.addEventListener('input', function(e) {
            const password = e.target.value;
            const requirements = {
                length: password.length >= 8,
                uppercase: /[A-Z]/.test(password),
                lowercase: /[a-z]/.test(password),
                number: /[0-9]/.test(password),
                special: /[^a-zA-Z0-9]/.test(password)
            };
            
            const allValid = Object.values(requirements).every(v => v);
            e.target.style.borderColor = allValid ? 'green' : '#ddd';
        });
    </script>
</body>
</html>

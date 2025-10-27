<?php
require_once __DIR__ . '/../classes/Elem.php';
require_once __DIR__ . '/../classes/Database.php';

session_start();

// Si déjà connecté, rediriger vers feed
if (isset($_SESSION['user_id'])) {
    header('Location: feed.php');
    exit;
}

$errors = [];
$success = '';

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    // Basic validation
    if (empty($login)) {
        $errors[] = "Username or email is required";
    }
    if (empty($password)) {
        $errors[] = "Password is required";
    }

    // Database verification
    if (empty($errors)) {
        try {
            $pdo = getDatabase();

            // Search user by username OR email
            $stmt = $pdo->prepare("SELECT id, username, email, password, is_verified FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$login, $login]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user && password_verify($password, $user['password'])) {
                // Check if account is verified
                if ($user['is_verified'] == 1) {
                    // Successful login
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['email'] = $user['email'];
                    
                    // Redirect to main menu
                    header('Location: feed.php');
                    exit;
                } else {
                    $errors[] = "Your account is not yet activated. Please check your emails.";
                }
            } else {
                $errors[] = "Incorrect username/email or password";
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
    <title>Login - Camagru</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Camagru</h1>
        
        <?php
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

        $form = new Elem('form', [
            'action' => '',
            'method' => 'post',
            'class' => 'login-form'
        ]);

        // Username or Email field
        $usernameDiv = new Elem('div', ['class' => 'form-group']);
        $usernameLabel = new Elem('label');
        $usernameLabel->addChild('Username or Email: ');
        $usernameInput = new Elem('input', [
            'type' => 'text',
            'name' => 'username',
            'required' => 'required',
            'placeholder' => 'Your username or email',
            'class' => 'input-field'
        ]);
        $usernameDiv->addChild($usernameLabel);
        $usernameDiv->addChild($usernameInput);
        $form->addChild($usernameDiv);

        // Password field
        $pwdDiv = new Elem('div', ['class' => 'form-group']);
        $pwdLabel = new Elem('label');
        $pwdLabel->addChild('Password: ');
        $pwdInput = new Elem('input', [
            'type' => 'password',
            'name' => 'password',
            'required' => 'required',
            'placeholder' => 'Your password',
            'class' => 'input-field'
        ]);
        $pwdDiv->addChild($pwdLabel);
        $pwdDiv->addChild($pwdInput);
        $form->addChild($pwdDiv);

        // Login button
        $submitDiv = new Elem('div', ['class' => 'form-group']);
        $submit = new Elem('button', ['type' => 'submit', 'class' => 'btn']);
        $submit->addChild('Login');
        $submitDiv->addChild($submit);
        $form->addChild($submitDiv);

        // Forgot password link
        $forgotDiv = new Elem('div', ['class' => 'form-group']);
        $forgot = new Elem('a', ['href' => 'forgot_password.php', 'class' => 'link']);
        $forgot->addChild('Forgot password?');
        $forgotDiv->addChild($forgot);
        $form->addChild($forgotDiv);

        // Create account link
        $createDiv = new Elem('div', ['class' => 'form-group']);
        $create = new Elem('a', ['href' => 'inscription.php', 'class' => 'link']);
        $create->addChild('Create an account');
        $createDiv->addChild($create);
        $form->addChild($createDiv);

        // Back to public feed link
        $feedDiv = new Elem('div', ['class' => 'form-group']);
        $feedLink = new Elem('a', ['href' => 'feed.php', 'class' => 'link']);
        $feedLink->addChild('← Back to public gallery');
        $feedDiv->addChild($feedLink);
        $form->addChild($feedDiv);

        // Affichage du formulaire
        echo $form->render();
        ?>
    </div>
</body>
</html>

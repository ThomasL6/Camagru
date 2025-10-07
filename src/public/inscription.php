<?php
require_once __DIR__ . '/../classes/Elem.php';
require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../includes/functions.php';

session_start();

$errors = [];
$success = '';

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validation
    if (empty($username)) {
        $errors[] = "Le nom d'utilisateur est requis";
    } elseif (strlen($username) < 3) {
        $errors[] = "Le nom d'utilisateur doit contenir au moins 3 caractères";
    }
    
    if (empty($email)) {
        $errors[] = "L'email est requis";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Format d'email invalide";
    }
    
    if (empty($password)) {
        $errors[] = "Le mot de passe est requis";
    } elseif (strlen($password) < 8) {
        $errors[] = "Le mot de passe doit contenir au moins 8 caractères";
    }
    
    if ($password !== $confirm_password) {
        $errors[] = "Les mots de passe ne correspondent pas";
    }
    
    // Vérifier si l'utilisateur existe déjà ET créer l'utilisateur
    if (empty($errors)) {
        try {
            $pdo = getDatabase(); // ✅ $pdo accessible pour tout ce qui suit
            
            // Vérifier si l'utilisateur existe déjà
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);
            
            if ($stmt->fetch()) {
                $errors[] = "Nom d'utilisateur ou email déjà utilisé";
            } else {
                // Créer l'utilisateur si il n'existe pas
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $verification_token = bin2hex(random_bytes(32));
                
                $stmt = $pdo->prepare("
                    INSERT INTO users (username, email, password, verification_token, is_verified, created_at) 
                    VALUES (?, ?, ?, ?, 0, NOW())
                ");
                
                if ($stmt->execute([$username, $email, $hashed_password, $verification_token])) {
                    // Envoyer l'email de vérification
                    if (sendVerificationEmail($email, $username, $verification_token)) {
                        $success = "Compte créé avec succès ! Vérifiez votre email pour activer votre compte.";
                    } else {
                        $errors[] = "Erreur lors de l'envoi de l'email de vérification";
                    }
                }
            }
        } catch (PDOException $e) {
            $errors[] = "Erreur de base de données: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - Camagru</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Créer un compte</h1>
        
        <?php
        // Afficher les erreurs
        if (!empty($errors)) {
            $errorDiv = new Elem('div', ['class' => 'error-messages']);
            foreach ($errors as $error) {
                $errorP = new Elem('p', ['class' => 'error']);
                $errorP->addChild(htmlspecialchars($error));
                $errorDiv->addChild($errorP);
            }
            echo $errorDiv->render();
        }
        
        // Afficher le message de succès
        if ($success) {
            $successDiv = new Elem('div', ['class' => 'success-message']);
            $successP = new Elem('p', ['class' => 'success']);
            $successP->addChild(htmlspecialchars($success));
            $successDiv->addChild($successP);
            echo $successDiv->render();
        }
        
        // Afficher le formulaire seulement si pas de succès
        if (!$success) {
            $form = new Elem('form', [
                'method' => 'POST',
                'action' => '',
                'class' => 'login-form'
            ]);

            // Champ Username
            $userDiv = new Elem('div', ['class' => 'form-group']);
            $userLabel = new Elem('label', ['for' => 'username']);
            $userLabel->addChild('Nom d\'utilisateur: ');
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

            // Champ Email
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

            // Champ Password
            $passDiv = new Elem('div', ['class' => 'form-group']);
            $passLabel = new Elem('label', ['for' => 'password']);
            $passLabel->addChild('Mot de passe: ');
            $passInput = new Elem('input', [
                'type' => 'password',
                'id' => 'password',
                'name' => 'password',
                'class' => 'input-field',
                'required' => 'required'
            ]);
            $passDiv->addChild($passLabel);
            $passDiv->addChild($passInput);
            $form->addChild($passDiv);

            // Champ Confirm Password
            $confirmDiv = new Elem('div', ['class' => 'form-group']);
            $confirmLabel = new Elem('label', ['for' => 'confirm_password']);
            $confirmLabel->addChild('Confirmer le mot de passe: ');
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

            // Bouton Submit
            $submitBtn = new Elem('button', [
                'type' => 'submit',
                'class' => 'submit-btn'
            ]);
            $submitBtn->addChild('S\'inscrire');
            $form->addChild($submitBtn);

            echo $form->render();
        }
        ?>
    </div>
</body>
</html>

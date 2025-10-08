<?php
require_once __DIR__ . '/../classes/Elem.php';
require_once __DIR__ . '/../classes/Database.php';

session_start();

$errors = [];
$success = '';

// Traitement de la connexion
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Validation basique
    if (empty($email)) {
        $errors[] = "L'email est requis";
    }
    if (empty($password)) {
        $errors[] = "Le mot de passe est requis";
    }

    // Vérification en base de données
    if (empty($errors)) {
        try {
            $pdo = getDatabase();
            
            // Chercher l'utilisateur par email
            $stmt = $pdo->prepare("SELECT id, username, email, password, is_verified FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user && password_verify($password, $user['password'])) {
                // Vérifier si le compte est activé
                if ($user['is_verified'] == 1) {
                    // Connexion réussie
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['email'] = $user['email'];
                    
                    // Redirection vers le menu principal
                    header('Location: menu.php');
                    exit;
                } else {
                    $errors[] = "Votre compte n'est pas encore activé. Vérifiez vos emails.";
                }
            } else {
                $errors[] = "Email ou mot de passe incorrect";
            }
        } catch (PDOException $e) {
            $errors[] = "Erreur de base de données : " . $e->getMessage();
        }
    }
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Camagru</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Camagru</h1>
        
        <?php
        // Affichage des erreurs
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

        // Formulaire principal
        $form = new Elem('form', [
            'action' => '',
            'method' => 'post',
            'class' => 'login-form'
        ]);

        // Champ Email
        $emailDiv = new Elem('div', ['class' => 'form-group']);
        $emailLabel = new Elem('label');
        $emailLabel->addChild('Email: ');
        $emailInput = new Elem('input', [
            'type' => 'email',
            'name' => 'email',
            'required' => 'required',
            'placeholder' => 'Votre email',
            'class' => 'input-field'
        ]);
        $emailDiv->addChild($emailLabel);
        $emailDiv->addChild($emailInput);
        $form->addChild($emailDiv);

        // Champ Mot de passe
        $pwdDiv = new Elem('div', ['class' => 'form-group']);
        $pwdLabel = new Elem('label');
        $pwdLabel->addChild('Mot de passe: ');
        $pwdInput = new Elem('input', [
            'type' => 'password',
            'name' => 'password',
            'required' => 'required',
            'placeholder' => 'Votre mot de passe',
            'class' => 'input-field'
        ]);
        $pwdDiv->addChild($pwdLabel);
        $pwdDiv->addChild($pwdInput);
        $form->addChild($pwdDiv);

        // Bouton connexion
        $submitDiv = new Elem('div', ['class' => 'form-group']);
        $submit = new Elem('button', ['type' => 'submit', 'class' => 'btn']);
        $submit->addChild('Se connecter');
        $submitDiv->addChild($submit);
        $form->addChild($submitDiv);

        // Lien créer un compte
        $createDiv = new Elem('div', ['class' => 'form-group']);
        $create = new Elem('a', ['href' => 'inscription.php', 'class' => 'link']);
        $create->addChild('Créer un compte');
        $createDiv->addChild($create);
        $form->addChild($createDiv);

        // Affichage du formulaire
        echo $form->render();
        ?>
    </div>
</body>
</html>

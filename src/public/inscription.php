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
        require_once __DIR__ . '/../classes/Elem.php';

        $form = new Elem('form', [
            'action' => '#',
            'method' => 'post',
            'class' => 'login-form'
        ]);

        // Champ Username
        $userDiv = new Elem('div', ['class' => 'form-group']);
        $userLabel = new Elem('label');
        $userLabel->addChild('Nom d\'utilisateur: ');
        $userInput = new Elem('input', [
            'type' => 'text',
            'name' => 'username',
            'required' => 'required',
            'placeholder' => 'Votre nom d\'utilisateur',
            'class' => 'input-field'
        ]);
        $userDiv->addChild($userLabel);
        $userDiv->addChild($userInput);
        $form->addChild($userDiv);

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

        // Champ Confirmer mot de passe
        $pwdDiv2 = new Elem('div', ['class' => 'form-group']);
        $pwdLabel2 = new Elem('label');
        $pwdLabel2->addChild('Confirmer mot de passe: ');
        $pwdInput2 = new Elem('input', [
            'type' => 'password',
            'name' => 'confirm_password',
            'required' => 'required',
            'placeholder' => 'Confirmez votre mot de passe',
            'class' => 'input-field'
        ]);
        $pwdDiv2->addChild($pwdLabel2);
        $pwdDiv2->addChild($pwdInput2);
        $form->addChild($pwdDiv2);

        // Bouton inscription
        $submitDiv = new Elem('div', ['class' => 'form-group']);
        $submit = new Elem('button', ['type' => 'submit', 'class' => 'btn']);
        $submit->addChild('Créer le compte');
        $submitDiv->addChild($submit);
        $form->addChild($submitDiv);

        // Lien retour à la connexion
        $loginDiv = new Elem('div', ['class' => 'form-group']);
        $login = new Elem('a', ['href' => 'index.php', 'class' => 'link']);
        $login->addChild('Retour à la connexion');
        $loginDiv->addChild($login);
        $form->addChild($loginDiv);

        // Affichage du formulaire
        echo $form->render();
        ?>
    </div>
</body>
</html>

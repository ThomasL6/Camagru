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
        require_once __DIR__ . '/../classes/Elem.php';

        // Formulaire principal
        $form = new Elem('form', [
            'action' => '#',
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
        $submit = new Elem('button', ['type' => 'submit', 'class' => 'btn', 'href'=> 'menu.php']);
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

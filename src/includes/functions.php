<?php

function sendVerificationEmail($email, $username, $token) {
    $subject = "Vérification de votre compte Camagru";
    
    // Message HTML avec le bon URL
    $message = "
    <html>
    <head>
        <title>Vérification de votre compte</title>
    </head>
    <body>
        <h2>Bonjour $username,</h2>
        <p>Merci de vous être inscrit sur Camagru !</p>
        <p>Pour activer votre compte, veuillez cliquer sur le lien ci-dessous :</p>
        <p><a href='https://localhost:8443/verify.php?token=$token' style='background-color: #4CAF50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Activer mon compte</a></p>
        <p>Si le lien ne fonctionne pas, copiez et collez cette URL dans votre navigateur :</p>
        <p>https://localhost:8443/verify.php?token=$token</p>
        <br>
        <p>Si vous n'avez pas créé de compte, vous pouvez ignorer cet email.</p>
        <p>Cordialement,<br>L'équipe Camagru</p>
    </body>
    </html>
    ";
    
    // Headers pour HTML
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8\r\n";
    $headers .= "From: Camagru <" . ($_ENV['SMTP_FROM'] ?? 'noreply@camagru.com') . ">\r\n";
    $headers .= "Reply-To: " . ($_ENV['SMTP_FROM'] ?? 'noreply@camagru.com') . "\r\n";
    
    // Debug pour voir les variables d'environnement
    error_log("Envoi email à: $email avec SMTP: " . ($_ENV['SMTP_HOST'] ?? 'non défini'));
    
    return mail($email, $subject, $message, $headers);
}
?>
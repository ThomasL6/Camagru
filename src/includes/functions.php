<?php

function sendVerificationEmail($email, $username, $token) {
    $subject = "Verify your Camagru account";
    
    // HTML message with the correct URL
    $message = "
    <html>
    <head>
        <title>Verify your account</title>
    </head>
    <body>
        <h2>Hello $username,</h2>
        <p>Thank you for signing up for Camagru!</p>
        <p>To activate your account, please click on the link below:</p>
        <p><a href='https://localhost:8443/verify.php?token=$token' style='background-color: #4CAF50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Activate my account</a></p>
        <p>If the link doesn't work, copy and paste this URL into your browser:</p>
        <p>https://localhost:8443/verify.php?token=$token</p>
        <br>
        <p>If you did not create an account, you can ignore this email.</p>
        <p>Best regards,<br>The Camagru Team</p>
    </body>
    </html>
    ";
    
    // Headers for HTML
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8\r\n";
    $headers .= "From: Camagru <" . ($_ENV['SMTP_FROM'] ?? 'noreply@camagru.com') . ">\r\n";
    $headers .= "Reply-To: " . ($_ENV['SMTP_FROM'] ?? 'noreply@camagru.com') . "\r\n";
    
    // Debug to see environment variables
    error_log("Sending email to: $email with SMTP: " . ($_ENV['SMTP_HOST'] ?? 'not defined'));
    
    return mail($email, $subject, $message, $headers);
}
?>
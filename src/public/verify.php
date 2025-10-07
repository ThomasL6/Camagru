<?php
require_once __DIR__ . '/../classes/Database.php';

$message = '';
$success = false;

if (isset($_GET['token']) && !empty($_GET['token'])) {
    $token = $_GET['token'];
    
    try {
        $pdo = getDatabase();
        
        // Chercher l'utilisateur avec ce token
        $stmt = $pdo->prepare("SELECT id, username FROM users WHERE verification_token = ? AND is_verified = 0");
        $stmt->execute([$token]);
        $user = $stmt->fetch();
        
        if ($user) {
            // Activer le compte
            $stmt = $pdo->prepare("UPDATE users SET is_verified = 1, verification_token = NULL WHERE id = ?");
            if ($stmt->execute([$user['id']])) {
                $message = "Félicitations " . htmlspecialchars($user['username']) . " ! Votre compte a été activé avec succès.";
                $success = true;
            } else {
                $message = "Erreur lors de l'activation du compte.";
            }
        } else {
            $message = "Token de vérification invalide ou compte déjà activé.";
        }
    } catch (PDOException $e) {
        $message = "Erreur de base de données : " . $e->getMessage();
    }
} else {
    $message = "Token de vérification manquant.";
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vérification du compte - Camagru</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Vérification du compte</h1>
        
        <div class="<?php echo $success ? 'success-message' : 'error-messages'; ?>">
            <p><?php echo htmlspecialchars($message); ?></p>
        </div>
        
        <?php if ($success): ?>
            <p><a href="menu.php" class="btn">Se connecter</a></p>
        <?php else: ?>
            <p><a href="inscription.php" class="btn">Retour à l'inscription</a></p>
        <?php endif; ?>
    </div>
</body>
</html>
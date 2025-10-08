<?php
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

// Gestion de la déconnexion
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit;
}

$username = $_SESSION['username'] ?? 'Utilisateur';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu - Camagru</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Bienvenue sur Camagru</h1>
        <div class="user-info">
            <p>Connecté en tant que : <strong><?php echo htmlspecialchars($username); ?></strong></p>
        </div>
        
        <div class="menu-section">
            <h2>Menu Principal</h2>
            <ul class="menu-list">
                <li><a href="#" class="btn">📸 Prendre une photo</a></li>
                <li><a href="#" class="btn">🖼️ Ma galerie</a></li>
                <li><a href="#" class="btn">👤 Mon profil</a></li>
                <li><a href="?logout=1" class="btn logout-btn">🚪 Se déconnecter</a></li>
            </ul>
        </div>
    </div>
</body>
</html>
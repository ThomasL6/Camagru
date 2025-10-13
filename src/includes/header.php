<?php
// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$username = $_SESSION['username'] ?? 'Utilisateur';
$current_page = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'Camagru'; ?></title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="css/layout.css">
    <link rel="stylesheet" href="css/responsive.css">
    <?php if (isset($page_css)): ?>
        <link rel="stylesheet" href="css/<?php echo $page_css; ?>.css">
    <?php endif; ?>
</head>
<body>
    <header class="main-header">
        <div class="header-container">
            <div class="logo">
                <h1><a href="menu.php">📸 Camagru</a></h1>
            </div>
            
            <nav class="main-nav">
                <ul>
                    <li><a href="menu.php" class="<?php echo $current_page == 'menu' ? 'active' : ''; ?>">🏠 Accueil</a></li>
                    <li><a href="gallery.php" class="<?php echo $current_page == 'gallery' ? 'active' : ''; ?>">🖼️ Galerie</a></li>
                    <li><a href="camera.php" class="<?php echo $current_page == 'camera' ? 'active' : ''; ?>">📸 Caméra</a></li>
                    <li><a href="profile.php" class="<?php echo $current_page == 'profile' ? 'active' : ''; ?>">💽 Profil</a></li>
                </ul>
            </nav>
            
            <div class="user-menu">
                <div class="user-info">
                    <span class="username">👋 <?php echo htmlspecialchars($username); ?></span>
                </div>
                <div class="logout-menu">
                    <a href="?logout=1" class="logout-btn" onclick="return confirm('Êtes-vous sûr de vouloir vous déconnecter ?')">
                        🚪 Déconnexion
                    </a>
                </div>
            </div>
        </div>
    </header>
    
    <main class="main-content">

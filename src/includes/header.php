<?php
// Déterminer si l'utilisateur est connecté
$isLoggedIn = isset($_SESSION['user_id']);
$username = $_SESSION['username'] ?? 'Guest';
$current_page = basename($_SERVER['PHP_SELF'], '.php');

// Pages publiques accessibles sans connexion
$publicPages = ['feed', 'index', 'inscription', 'verify', 'forgot_password', 'reset_password'];

// Si pas connecté ET pas sur une page publique, rediriger vers index
if (!$isLoggedIn && !in_array($current_page, $publicPages)) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
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
    <?php if ($isLoggedIn): ?>
    <header class="main-header">
        <div class="header-container">
            <div class="logo">
                <h1><a href="menu.php">📸 Camagru</a></h1>
            </div>
            
            <nav class="main-nav">
                <ul>
                    <li><a href="menu.php" class="<?php echo $current_page == 'menu' ? 'active' : ''; ?>">🏠 Home</a></li>
                    <li><a href="feed.php" class="<?php echo $current_page == 'feed' ? 'active' : ''; ?>">🎨Feed</a></li>
                    <li><a href="gallery.php" class="<?php echo $current_page == 'gallery' ? 'active' : ''; ?>">🖼️ Gallery</a></li>
                    <li><a href="camera.php" class="<?php echo $current_page == 'camera' ? 'active' : ''; ?>">📸 Camera</a></li>
                    <li><a href="profile.php" class="<?php echo $current_page == 'profile' ? 'active' : ''; ?>">💽 Profile</a></li>
                </ul>
            </nav>
            
            <div class="user-menu">
                <div class="user-info">
                    <span class="username">👋 <?php echo htmlspecialchars($username); ?></span>
                </div>
                <div class="logout-menu">
                    <a href="logout.php" class="logout-btn" onclick="return confirm('Are you sure you want to log out?')">
                        🚪 Logout
                    </a>
                </div>
            </div>
        </div>
    </header>
    <?php else: ?>
    <!-- Header simple pour visiteurs non connectés -->
    <header class="main-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 15px 0;">
        <div class="header-container" style="display: flex; justify-content: space-between; align-items: center; max-width: 1200px; margin: 0 auto; padding: 0 20px;">
            <div class="logo">
                <h1 style="margin: 0;"><a href="feed.php" style="color: white; text-decoration: none;">📸 Camagru</a></h1>
            </div>
            <div style="display: flex; gap: 15px;">
                <a href="index.php" class="btn" style="padding: 8px 20px; background: white; color: #667eea; text-decoration: none; border-radius: 5px;">🔐 Login</a>
                <a href="inscription.php" class="btn" style="padding: 8px 20px; background: #4CAF50; color: white; text-decoration: none; border-radius: 5px;">✨ Sign Up</a>
            </div>
        </div>
    </header>
    <?php endif; ?>
    
    <main class="main-content">

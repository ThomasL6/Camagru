<?php
require_once __DIR__ . '/../includes/auth_check.php';

$page_title = "Galerie - Camagru";
$page_css = "gallery";
include __DIR__ . '/../includes/header.php';
echo '<link rel="stylesheet" href="css/placeholder.css">';
?>

<div class="container">
    <section class="gallery-header">
        <h2>🖼️ Ma Galerie</h2>
        <p>Découvrez vos créations et celles de la communauté</p>
    </section>
    
    <div class="gallery-placeholder">
        <div class="placeholder-content">
            <div class="placeholder-icon">🎨</div>
            <h3>Bientôt disponible !</h3>
            <p>La galerie sera bientôt implémentée.</p>
            <p>Vous pourrez voir toutes vos photos et celles des autres utilisateurs.</p>
            <a href="menu.php" class="btn">← Retour au menu</a>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

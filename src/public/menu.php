<?php
require_once __DIR__ . '/../includes/auth_check.php';

$page_title = "Menu - Camagru";
$page_css = "menu";
include __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <section class="welcome-section">
        <h2>Bienvenue sur Camagru ! 🎉</h2>
        <p class="welcome-text">Créez, partagez et découvrez des moments uniques avec notre communauté.</p>
    </section>
    
    <section class="quick-actions">
        <h3>Actions rapides</h3>
        <div class="action-grid">
            <a href="camera.php" class="action-card">
                <div class="card-icon">📸</div>
                <h4>Prendre une photo</h4>
                <p>Capturez un moment et ajoutez des effets créatifs</p>
            </a>
            
            <a href="gallery.php" class="action-card">
                <div class="card-icon">🖼️</div>
                <h4>Ma galerie</h4>
                <p>Parcourez vos créations et celles de la communauté</p>
            </a>
            
            <a href="profile.php" class="action-card">
                <div class="card-icon">👤</div>
                <h4>Mon profil</h4>
                <p>Gérez vos informations et préférences</p>
            </a>
            
            <a href="#" class="action-card">
                <div class="card-icon">🎨</div>
                <h4>Créations populaires</h4>
                <p>Découvrez les tendances du moment</p>
            </a>
        </div>
    </section>
    
    <section class="recent-activity">
        <h3>Activité récente</h3>
        <div class="activity-placeholder">
            <p>🚀 Bientôt disponible : Vos dernières activités apparaîtront ici !</p>
        </div>
    </section>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
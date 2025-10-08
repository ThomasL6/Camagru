<?php
require_once __DIR__ . '/../includes/auth_check.php';

$page_title = "Caméra - Camagru";
$page_css = "camera";
include __DIR__ . '/../includes/header.php';
echo '<link rel="stylesheet" href="css/placeholder.css">';
?>

<div class="container">
    <section class="camera-header">
        <h2>📸 Studio Photo</h2>
        <p>Capturez vos moments et ajoutez des effets créatifs</p>
    </section>
    
    <div class="camera-placeholder">
        <div class="placeholder-content">
            <div class="placeholder-icon">📷</div>
            <h3>Fonctionnalité en développement</h3>
            <p>Le studio photo sera bientôt disponible !</p>
            <p>Vous pourrez prendre des photos avec votre webcam et ajouter des filtres sympas.</p>
            <a href="menu.php" class="btn">← Retour au menu</a>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

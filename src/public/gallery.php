<?php
require_once __DIR__ . '/../includes/auth_check.php';

$page_title = "Gallery - Camagru";
$page_css = "gallery";
include __DIR__ . '/../includes/header.php';
echo '<link rel="stylesheet" href="css/placeholder.css">';
?>

<div class="container">
    <section class="gallery-header">
        <h2>🖼️ My Gallery</h2>
        <p>Discover your creations and those of the community</p>
    </section>
    
    <div class="gallery-placeholder">
        <div class="placeholder-content">
            <div class="placeholder-icon">🎨</div>
            <h3>Coming Soon!</h3>
            <p>The gallery will be implemented soon.</p>
            <p>You will be able to see all your photos and those of other users.</p>
            <a href="menu.php" class="btn">← Back to menu</a>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

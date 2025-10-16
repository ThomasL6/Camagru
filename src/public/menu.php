<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../classes/Database.php';

$pdo = Database::getInstance()->getConnection();
$stmt = $pdo->prepare("SELECT id, user_id, image_path, is_public, created_at FROM images WHERE user_id = ? ORDER BY created_at DESC LIMIT 4");
$stmt->execute([$_SESSION['user_id']]);
$recent_photos = $stmt->fetchAll(PDO::FETCH_ASSOC);


$page_title = "Menu - Camagru";
$page_css = "menu";
include __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <section class="welcome-section">
        <h2>Welcome to Camagru! 🎉</h2>
        <p class="welcome-text">Create, share and discover unique moments with our community.</p>
    </section>
    
    <section class="quick-actions">
        <h3>Quick Actions</h3>
        <div class="action-grid">
            <a href="camera.php" class="action-card">
                <div class="card-icon">📸</div>
                <h4>Take a Photo</h4>
                <p>Capture a moment and add creative effects</p>
            </a>
            
            <a href="gallery.php" class="action-card">
                <div class="card-icon">🖼️</div>
                <h4>My Gallery</h4>
                <p>Browse your creations and those of the community</p>
            </a>
            
            <a href="profile.php" class="action-card">
                <div class="card-icon">💽</div>
                <h4>My Profile</h4>
                <p>Manage your information and preferences</p>
            </a>
            
            <a href="feed.php" class="action-card">
                <div class="card-icon">🎨</div>
                <h4>Popular Creations</h4>
                <p>Discover trending content</p>
            </a>
        </div>
    </section>
    
    <section class="recent-activity">
        <h3>Recent Photos</h3>
        <div class="recent-photos">
            <?php if (!empty($recent_photos)): ?>
                <?php foreach($recent_photos as $photo): ?>
                    <div class="photo-item">
                        <a href="gallery.php?id=<?= $photo['id'] ?>">
                            <img src="../uploads/images/<?= htmlspecialchars($photo['image_path']) ?>" 
                                    alt="Photo du <?= date('d/m/Y', strtotime($photo['created_at'])) ?>"
                                    class="photo-thumbnail"
                                    onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                            
                            <div style="display: none; padding: 2rem; background: #f8d7da; color: #721c24; text-align: center; border-radius: 8px;">
                                <p>❌ Image introuvable</p>
                                <small><?= htmlspecialchars($photo['image_path']) ?></small>
                            </div>
                            
                            <div class="photo-info">
                                <span class="photo-date"><?= date('d/m/Y H:i', strtotime($photo['created_at'])) ?></span>
                                <span class="photo-status">
                                    <?= $photo['is_public'] ? '🌐 Public' : '🔒 Privé' ?>
                                </span>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
                <div class="view-all-photos">
                    <a href="gallery.php" class="view-all-link">Voir toutes mes photos →</a>
                </div>
            <?php else: ?>
                <div class="no-photos">
                    <p>Vous n'avez pas encore publié de photos.</p>
                    <a href="camera.php" class="btn-primary">Prendre ma première photo 📸</a>
                </div>
            <?php endif; ?>
        </div>
    </section>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
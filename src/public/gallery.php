<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../classes/Database.php';

$page_title = "Gallery - Camagru";
$page_css = "gallery";

try {
    $pdo = Database::getInstance()->getConnection();
    
    $stmt = $pdo->prepare("
        SELECT id, image_path, is_public, created_at 
        FROM images 
        WHERE user_id = ? 
        ORDER BY created_at DESC
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $photos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (Exception $e) {
    $error_message = "Failed to load photos: " . $e->getMessage();
    $photos = [];
}

include __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <section class="gallery-header">
        <h2>📸 My Photo Gallery</h2>
        <p>Browse through your captured moments</p>
        <a href="camera.php" class="btn btn-primary">📷 Take New Photo</a>
    </section>

    <?php if (isset($error_message)): ?>
        <div class="alert alert-error">
            <?= htmlspecialchars($error_message) ?>
        </div>
    <?php endif; ?>

    <div class="gallery-grid">
        <?php if (empty($photos)): ?>
            <div class="no-photos">
                <h3>No photos yet</h3>
            </div>
        <?php else: ?>
            <?php foreach ($photos as $photo): ?>
                <div class="photo-card" data-photo-id="<?= $photo['id'] ?>">
                    <div class="photo-container">
                        <img src="uploads/images/<?= htmlspecialchars($photo['image_path']) ?>" 
                            alt="Photo taken on <?= date('M j, Y', strtotime($photo['created_at'])) ?>"
                            loading="lazy">
                    </div>
                    <div class="photo-info">
                        <span class="photo-date">
                            <?= date('M j, Y - g:i A', strtotime($photo['created_at'])) ?>
                        </span>
                        <div class="photo-visibility">
                            <?php if ($photo['is_public']): ?>
                                <span class="visibility-badge public">🌍 Public</span>
                            <?php else: ?>
                                <span class="visibility-badge private">🔒 Private</span>
                            <?php endif; ?>
                        </div>
                        <div class="photo-actions">
                            <?php if ($photo['is_public']): ?>
                                <button class="btn btn-sm btn-warning" onclick="toggleVisibility(<?= $photo['id'] ?>, 0)">
                                    🔒 Make Private
                                </button>
                            <?php else: ?>
                                <button class="btn btn-sm btn-success" onclick="toggleVisibility(<?= $photo['id'] ?>, 1)">
                                    🌍 Publish
                                </button>
                            <?php endif; ?>
                            <button class="btn btn-sm btn-danger" onclick="deletePhoto(<?= $photo['id'] ?>)">
                                🗑️ Delete
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script src="js/gallery.js"></script>
<?php include __DIR__ . '/../includes/footer.php'; ?>

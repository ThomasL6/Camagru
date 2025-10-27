<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../classes/Database.php';

if (isset($_GET['ajax']) && $_GET['ajax'] === 'load_more') {
    header('Content-Type: application/json');
    
    try {
        $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
        
        if ($limit > 50) {
            $limit = 50;
        }
        
        $pdo = Database::getInstance()->getConnection();
        $user_id = $_SESSION['user_id'];
        
        $stmt = $pdo->prepare("
            SELECT id, image_path, is_public, created_at 
            FROM images 
            WHERE user_id = :user_id 
            ORDER BY created_at DESC
            LIMIT :limit OFFSET :offset
        ");
        $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $photos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $stmtCount = $pdo->prepare("SELECT COUNT(*) FROM images WHERE user_id = :user_id");
        $stmtCount->bindValue(':user_id', $user_id, PDO::PARAM_INT);
        $stmtCount->execute();
        $totalPhotos = $stmtCount->fetchColumn();
        
        $hasMore = ($offset + $limit) < $totalPhotos;
        
        echo json_encode([
            'success' => true,
            'photos' => $photos,
            'hasMore' => $hasMore,
            'total' => $totalPhotos
        ]);
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'error' => 'Failed to load photos: ' . $e->getMessage()
        ]);
    }
    exit;
}

$page_title = "Gallery - Camagru";
$page_css = "gallery";

try {
    $pdo = Database::getInstance()->getConnection();
    
    $initialLimit = 20;
    $stmt = $pdo->prepare("
        SELECT id, image_path, is_public, created_at 
        FROM images 
        WHERE user_id = :user_id 
        ORDER BY created_at DESC
        LIMIT :limit
    ");
    $stmt->bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
    $stmt->bindValue(':limit', $initialLimit, PDO::PARAM_INT);
    $stmt->execute();
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
    
    <div id="loading-spinner" style="display: none; text-align: center; padding: 20px;">
        <div class="spinner">⏳ Loading more photos...</div>
    </div>
    
    <div id="no-more-photos" style="display: none; text-align: center; padding: 20px; color: #666;">
        <p>📷 You've reached the end of your gallery!</p>
    </div>
</div>

<script src="js/gallery.js"></script>
<?php include __DIR__ . '/../includes/footer.php'; ?>

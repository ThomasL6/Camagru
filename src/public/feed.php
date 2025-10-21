<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../classes/Database.php';

$page_title = "Popular Creations - Camagru";
$page_css = "feed";

try {
    $pdo = Database::getInstance()->getConnection();
    
    $stmt = $pdo->prepare("
        SELECT 
            i.id, 
            i.image_path, 
            i.created_at,
            u.username,
            COUNT(l.id) as likes_count
        FROM images i
        JOIN users u ON i.user_id = u.id
        LEFT JOIN likes l ON i.id = l.image_id
        WHERE i.is_public = 1
        GROUP BY i.id, u.username
        ORDER BY likes_count DESC, i.created_at DESC
        LIMIT 50
    ");
    $stmt->execute();
    $photos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (Exception $e) {
    $error_message = "Failed to load popular creations: " . $e->getMessage();
    $photos = [];
}

include __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <section class="feed-header">
        <h2>🎨 Popular Creations</h2>
        <p>Discover trending photos from our community</p>
    </section>

    <?php if (isset($error_message)): ?>
        <div class="alert alert-error">
            <?= htmlspecialchars($error_message) ?>
        </div>
    <?php endif; ?>

    <div class="feed-grid">
        <?php if (empty($photos)): ?>
            <div class="no-photos">
                <h3>No photos yet</h3>
                <a href="camera.php" class="btn btn-primary">📷 Take Photo</a>
            </div>
        <?php else: ?>
            <?php foreach ($photos as $photo): ?>
                <div class="photo-card feed-card" data-photo-id="<?= $photo['id'] ?>">
                    <div class="photo-header">
                        <div class="author-info">
                            <span class="author-name">📸 <?= htmlspecialchars($photo['username']) ?></span>
                            <span class="photo-date"><?= date('M j, Y', strtotime($photo['created_at'])) ?></span>
                        </div>
                    </div>
                    
                    <div class="photo-container">
                        <img src="uploads/images/<?= htmlspecialchars($photo['image_path']) ?>" 
                            alt="Photo by <?= htmlspecialchars($photo['username']) ?>"
                            loading="lazy"
                            onclick="openLightbox(this.src, '<?= htmlspecialchars($photo['username']) ?>', <?= $photo['id'] ?>)">
                    </div>
                    
                    <div class="photo-footer">
                        <div class="photo-stats">
                            <div class="likes-section">
                                <button class="like-btn" onclick="toggleLike(<?= $photo['id'] ?>, event)" 
                                        data-photo-id="<?= $photo['id'] ?>">
                                    ❤️ <span class="likes-count" id="likes-<?= $photo['id'] ?>"><?= $photo['likes_count'] ?></span>
                                </button>
                            </div>
                            <div class="comment-section">
                                <input type="text" placeholder="Add a comment..." class="comment-input" 
                                    data-photo-id="<?= $photo['id'] ?>"
                                    onkeypress="handleCommentSubmit(event, <?= $photo['id'] ?>)">
                            </div>
                            <span class="time-ago">
                                <?= timeAgo($photo['created_at']) ?>
                            </span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script src="js/feed.js"></script>
<?php include __DIR__ . '/../includes/footer.php'; ?>

<?php
function timeAgo($datetime) {
    $time = time() - strtotime($datetime);
    
    if ($time < 60) return 'just now';
    if ($time < 3600) return floor($time/60) . 'm ago';
    if ($time < 86400) return floor($time/3600) . 'h ago';
    if ($time < 2592000) return floor($time/86400) . 'd ago';
    
    return date('M j, Y', strtotime($datetime));
}
?>

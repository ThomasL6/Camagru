<?php
session_start();
require_once __DIR__ . '/../../classes/Database.php';

header('Content-Type: application/json');

// Check authentication
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

// Check method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

// Get data
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['photo_id'])) {
    echo json_encode(['success' => false, 'error' => 'Photo ID missing']);
    exit;
}

try {
    $pdo = Database::getInstance()->getConnection();
    
    $userId = $_SESSION['user_id'];
    $photoId = $input['photo_id'];
    
    // Check if user already liked this photo
    $stmt = $pdo->prepare("SELECT id FROM likes WHERE user_id = ? AND image_id = ?");
    $stmt->execute([$userId, $photoId]);
    $existingLike = $stmt->fetch();
    
    if ($existingLike) {
        // Unlike - remove the like
        $stmt = $pdo->prepare("DELETE FROM likes WHERE user_id = ? AND image_id = ?");
        $stmt->execute([$userId, $photoId]);
        $liked = false;
    } else {
        // Like - add the like
        $stmt = $pdo->prepare("INSERT INTO likes (user_id, image_id, created_at) VALUES (?, ?, NOW())");
        $stmt->execute([$userId, $photoId]);
        $liked = true;
    }
    
    // Get updated likes count
    $stmt = $pdo->prepare("SELECT COUNT(*) as likes_count FROM likes WHERE image_id = ?");
    $stmt->execute([$photoId]);
    $result = $stmt->fetch();
    $likesCount = $result['likes_count'];
    
    echo json_encode([
        'success' => true,
        'liked' => $liked,
        'likes_count' => $likesCount
    ]);
    
} catch (Exception $e) {
    error_log("Error toggling like: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Failed to toggle like']);
}
?>

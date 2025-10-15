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

if (!isset($input['photo_ids']) || !is_array($input['photo_ids'])) {
    echo json_encode(['success' => false, 'error' => 'Photo IDs missing']);
    exit;
}

try {
    $pdo = Database::getInstance()->getConnection();
    $userId = $_SESSION['user_id'];
    $photoIds = $input['photo_ids'];
    
    if (empty($photoIds)) {
        echo json_encode(['success' => true, 'liked_photos' => []]);
        exit;
    }
    
    // Create placeholders for IN clause
    $placeholders = str_repeat('?,', count($photoIds) - 1) . '?';
    
    // Get photos that user has liked
    $stmt = $pdo->prepare("
        SELECT image_id 
        FROM likes 
        WHERE user_id = ? AND image_id IN ($placeholders)
    ");
    
    $params = array_merge([$userId], $photoIds);
    $stmt->execute($params);
    
    $likedPhotos = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo json_encode([
        'success' => true,
        'liked_photos' => $likedPhotos
    ]);
    
} catch (Exception $e) {
    error_log("Error getting user likes: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Failed to get user likes']);
}
?>

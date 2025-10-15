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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['photo_id']) || !isset($input['is_public'])) {
    echo json_encode(['success' => false, 'error' => 'Missing required parameters']);
    exit;
}

try {
    $photoId = (int)$input['photo_id'];
    $isPublic = (bool)$input['is_public'];
    
    $pdo = Database::getInstance()->getConnection();
    
    // Verify the photo belongs to the current user
    $checkStmt = $pdo->prepare("SELECT user_id FROM images WHERE id = ?");
    $checkStmt->execute([$photoId]);
    $photo = $checkStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$photo) {
        echo json_encode(['success' => false, 'error' => 'Photo not found']);
        exit;
    }
    
    if ($photo['user_id'] != $_SESSION['user_id']) {
        echo json_encode(['success' => false, 'error' => 'Permission denied']);
        exit;
    }
    
    // Update visibility
    $updateStmt = $pdo->prepare("UPDATE images SET is_public = ? WHERE id = ?");
    $updateStmt->execute([$isPublic ? 1 : 0, $photoId]);
    
    $status = $isPublic ? 'published' : 'made private';
    echo json_encode([
        'success' => true, 
        'message' => "Photo successfully {$status}",
        'is_public' => $isPublic
    ]);
    
} catch (Exception $e) {
    error_log("Error toggling photo visibility: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Failed to update photo visibility']);
}
?>

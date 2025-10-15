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
    $pdo = getDatabase();
    
    // First, get the photo info to delete the file
    $stmt = $pdo->prepare("SELECT image_path FROM images WHERE id = ? AND user_id = ?");
    $stmt->execute([$input['photo_id'], $_SESSION['user_id']]);
    $photo = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$photo) {
        throw new Exception('Photo not found or unauthorized');
    }
    
    // Delete the file
    $filePath = __DIR__ . '/../uploads/images/' . $photo['image_path'];
    if (file_exists($filePath)) {
        unlink($filePath);
    }
    
    // Delete from database
    $stmt = $pdo->prepare("DELETE FROM images WHERE id = ? AND user_id = ?");
    $stmt->execute([$input['photo_id'], $_SESSION['user_id']]);
    
    if ($stmt->rowCount() === 0) {
        throw new Exception('Photo not found or unauthorized');
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Photo deleted successfully'
    ]);
    
} catch (Exception $e) {
    error_log("Error deleting photo: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Failed to delete photo']);
}
?>

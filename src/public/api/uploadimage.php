<?php
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display errors in HTML
ini_set('log_errors', 1);

session_start();
require_once __DIR__ . '/../../classes/Database.php';

header('Content-Type: application/json');

// Check if user is authenticated
if(!isset($_SESSION['user_id'])){
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

if($_SERVER['REQUEST_METHOD'] !== 'POST'){
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true); // Supprimé le _

if(!isset($input['image'])) {
    echo json_encode(['success' => false, 'error' => 'No image data provided']);
    exit;
}

try {
    $imageData = $input['image'];
    $imageData = str_replace('data:image/png;base64,', '', $imageData);
    $imageData = base64_decode($imageData);

    if($imageData === false) {
        throw new Exception("Invalid image data");
    }

    $fileName = 'photo_' . $_SESSION['user_id'] . '_' . time() . '_' . uniqid() . '.png';
    $uploadDir = __DIR__ . '/../uploads/images/';

    if(!is_dir($uploadDir)) {
        if(!mkdir($uploadDir, 0755, true)) {
            throw new Exception("Failed to create upload directory");
        }
    }

    $filePath = $uploadDir . $fileName;
    if(file_put_contents($filePath, $imageData) === false) {
        throw new Exception("Failed to save image file");
    }

    // Get visibility setting (default to private if not specified)
    $isPublic = isset($input['is_public']) ? (bool)$input['is_public'] : false;

    $pdo = getDatabase();
    $stmt = $pdo->prepare("INSERT INTO images (user_id, image_path, is_public, created_at) VALUES (?, ?, ?, NOW())");
    $stmt->execute([$_SESSION['user_id'], $fileName, $isPublic ? 1 : 0]);

    echo json_encode([
        'success' => true, 
        'message' => 'Photo saved successfully',
        'image_id' => $pdo->lastInsertId(),
        'filename' => $fileName
    ]);
} catch (Exception $e) {
    error_log("Error saving image: " . $e->getMessage());
    
    // Specific error messages without revealing technical details
    $userMessage = 'Failed to save image';
    
    if (strpos($e->getMessage(), 'directory') !== false) {
        $userMessage = 'Upload directory error';
    } elseif (strpos($e->getMessage(), 'database') !== false || strpos($e->getMessage(), 'SQL') !== false) {
        $userMessage = 'Database error';
    } elseif (strpos($e->getMessage(), 'Invalid image') !== false) {
        $userMessage = 'Invalid image format';
    }
    
    echo json_encode(['success' => false, 'error' => $userMessage]);
}
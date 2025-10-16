<?php
// Essayer différents chemins pour les includes
$possible_paths = [
    __DIR__ . '/../../includes/auth_check.php',
    __DIR__ . '/../includes/auth_check.php',
    __DIR__ . '/../../../includes/auth_check.php'
];

$auth_path = null;
foreach ($possible_paths as $path) {
    if (file_exists($path)) {
        $auth_path = $path;
        break;
    }
}

if (!$auth_path) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Auth check file not found']);
    exit;
}

require_once $auth_path;

// Même chose pour Database.php
$db_possible_paths = [
    __DIR__ . '/../../classes/Database.php',
    __DIR__ . '/../classes/Database.php',
    __DIR__ . '/../../../classes/Database.php'
];

$db_path = null;
foreach ($db_possible_paths as $path) {
    if (file_exists($path)) {
        $db_path = $path;
        break;
    }
}

if (!$db_path) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database class file not found']);
    exit;
}

require_once $db_path;

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'User not authenticated']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['photo_id']) || !isset($input['comment'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

$photoId = (int)$input['photo_id'];
$comment = trim($input['comment']);
$userId = $_SESSION['user_id'];

if (empty($comment)) {
    echo json_encode(['success' => false, 'message' => 'Comment cannot be empty']);
    exit;
}

if (strlen($comment) > 500) {
    echo json_encode(['success' => false, 'message' => 'Comment too long (max 500 characters)']);
    exit;
}

try {
    $pdo = Database::getInstance()->getConnection();
    
    // Vérifier que la photo existe et est publique
    $stmt = $pdo->prepare("SELECT id FROM images WHERE id = ? AND is_public = 1");
    $stmt->execute([$photoId]);
    
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Photo not found or not public']);
        exit;
    }
    
    // Ajouter le commentaire (noter que la colonne s'appelle comment_text dans la DB)
    $stmt = $pdo->prepare("
        INSERT INTO comments (image_id, user_id, comment_text, created_at) 
        VALUES (?, ?, ?, NOW())
    ");
    
    $stmt->execute([$photoId, $userId, $comment]);
    
    echo json_encode([
        'success' => true, 
        'message' => 'Comment added successfully',
        'comment_id' => $pdo->lastInsertId()
    ]);
    
} catch (Exception $e) {
    error_log("Error adding comment: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>

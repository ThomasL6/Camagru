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

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

if (!isset($_GET['photo_id'])) {
    echo json_encode(['success' => false, 'message' => 'Missing photo_id parameter']);
    exit;
}

$photoId = (int)$_GET['photo_id'];

try {
    $pdo = Database::getInstance()->getConnection();
    
    // Vérifier que la photo existe et est publique
    $stmt = $pdo->prepare("SELECT id FROM images WHERE id = ? AND is_public = 1");
    $stmt->execute([$photoId]);
    
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Photo not found or not public']);
        exit;
    }
    
    // Récupérer les commentaires avec les informations de l'utilisateur
    $stmt = $pdo->prepare("
        SELECT 
            c.id,
            c.comment_text,
            c.created_at,
            u.username
        FROM comments c
        JOIN users u ON c.user_id = u.id
        WHERE c.image_id = ?
        ORDER BY c.created_at ASC
    ");
    $stmt->execute([$photoId]);
    $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'comments' => $comments
    ]);
    
} catch (Exception $e) {
    error_log("Error fetching comments: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>

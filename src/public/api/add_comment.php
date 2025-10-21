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

// Charger functions.php pour sendCommentNotificationEmail
$functions_possible_paths = [
    __DIR__ . '/../../includes/functions.php',
    __DIR__ . '/../includes/functions.php',
    __DIR__ . '/../../../includes/functions.php'
];

$functions_path = null;
foreach ($functions_possible_paths as $path) {
    if (file_exists($path)) {
        $functions_path = $path;
        break;
    }
}

if ($functions_path) {
    require_once $functions_path;
}

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
    
    // Vérifier que la photo existe et est publique, et récupérer les infos du propriétaire
    $stmt = $pdo->prepare("
        SELECT i.id, i.user_id, u.username, u.email, u.notify_comments 
        FROM images i
        JOIN users u ON i.user_id = u.id
        WHERE i.id = ? AND i.is_public = 1
    ");
    $stmt->execute([$photoId]);
    $photo = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$photo) {
        echo json_encode(['success' => false, 'message' => 'Photo not found or not public']);
        exit;
    }
    
    // Ajouter le commentaire (noter que la colonne s'appelle comment_text dans la DB)
    $stmt = $pdo->prepare("
        INSERT INTO comments (image_id, user_id, comment_text, created_at) 
        VALUES (?, ?, ?, NOW())
    ");
    
    $stmt->execute([$photoId, $userId, $comment]);
    
    // Envoyer une notification email au propriétaire de la photo
    // Seulement si ce n'est pas le propriétaire qui commente sa propre photo
    // et si le propriétaire a activé les notifications
    if ($photo['user_id'] != $userId && isset($photo['notify_comments']) && $photo['notify_comments'] == 1) {
        // Récupérer le nom d'utilisateur du commentateur
        $stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $commenter = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($commenter && function_exists('sendCommentNotificationEmail')) {
            $emailSent = sendCommentNotificationEmail(
                $photo['email'], 
                $photo['username'], 
                $commenter['username'],
                $photoId
            );
            
            if (!$emailSent) {
                error_log("Failed to send comment notification email to: " . $photo['email']);
            }
        }
    }
    
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

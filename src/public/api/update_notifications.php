<?php
session_start();

// Vérifier l'authentification
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

// Vérifier la méthode
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Charger Database
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
    echo json_encode(['success' => false, 'message' => 'Database class not found']);
    exit;
}

require_once $db_path;

header('Content-Type: application/json');

// Récupérer les données JSON
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['notify_comments'])) {
    echo json_encode(['success' => false, 'message' => 'Missing notify_comments parameter']);
    exit;
}

$notify_comments = (int)$input['notify_comments'];
$user_id = $_SESSION['user_id'];

// Valider la valeur (0 ou 1 uniquement)
if ($notify_comments !== 0 && $notify_comments !== 1) {
    echo json_encode(['success' => false, 'message' => 'Invalid value for notify_comments']);
    exit;
}

try {
    $pdo = Database::getInstance()->getConnection();
    
    // Mettre à jour la préférence
    $stmt = $pdo->prepare("UPDATE users SET notify_comments = ? WHERE id = ?");
    $result = $stmt->execute([$notify_comments, $user_id]);
    
    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Notification preference updated successfully',
            'notify_comments' => $notify_comments
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update preference']);
    }
    
} catch (Exception $e) {
    error_log("Error updating notification preference: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>

<?php
require_once __DIR__ . '/../classes/Database.php';

$message = '';
$success = false;

if (isset($_GET['token']) && !empty($_GET['token'])) {
    $token = $_GET['token'];
    
    try {
        $pdo = getDatabase();
        
        // Find user with this token
        $stmt = $pdo->prepare("SELECT id, username FROM users WHERE verification_token = ? AND is_verified = 0");
        $stmt->execute([$token]);
        $user = $stmt->fetch();
        
        if ($user) {
            // Activate account
            $stmt = $pdo->prepare("UPDATE users SET is_verified = 1, verification_token = NULL WHERE id = ?");
            if ($stmt->execute([$user['id']])) {
                $message = "Congratulations " . htmlspecialchars($user['username']) . "! Your account has been successfully activated.";
                $success = true;
            } else {
                $message = "Error activating account.";
            }
        } else {
            $message = "Invalid verification token or account already activated.";
        }
    } catch (PDOException $e) {
        $message = "Database error: " . $e->getMessage();
    }
} else {
    $message = "Missing verification token.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Verification - Camagru</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Account Verification</h1>
        
        <div class="<?php echo $success ? 'success-message' : 'error-messages'; ?>">
            <p><?php echo htmlspecialchars($message); ?></p>
        </div>
        
        <?php if ($success): ?>
            <p><a href="index.php" class="btn">Login</a></p>
        <?php else: ?>
            <p><a href="inscription.php" class="btn">Back to registration</a></p>
        <?php endif; ?>
    </div>
</body>
</html>
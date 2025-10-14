<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../classes/Database.php';

$page_title = "My Profile - Camagru";
$page_css = "profile";
$user_id = $_SESSION['user_id'];
$success = '';
$errors = [];

// Get user information
try {
    $pdo = getDatabase();
    $stmt = $pdo->prepare("SELECT username, email, created_at FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $errors[] = "Error loading profile: " . $e->getMessage();
}

// Handle profile update form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_username = trim($_POST['username'] ?? '');
    $new_email = trim($_POST['email'] ?? '');
    $new_password = trim($_POST['password'] ?? '');
    
    // Field validation
    if (empty($new_username)) {
        $errors[] = "Username is required";
    } elseif (strlen($new_username) < 3) {
        $errors[] = "Username must contain at least 3 characters";
    }
    
    if (empty($new_email)) {
        $errors[] = "Email address is required";
    } elseif (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Email address is not valid";
    }
    
    // Password validation (optional)
    if (!empty($new_password) && strlen($new_password) < 6) {
        $errors[] = "Password must contain at least 6 characters";
    }
    
    if (empty($errors)) {
        try {
            // Check if username already exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
            $stmt->execute([$new_username, $user_id]);
            
            if ($stmt->fetch()) {
                $errors[] = "This username is already taken";
            }
            
            // Check if email already exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $stmt->execute([$new_email, $user_id]);
            
            if ($stmt->fetch()) {
                $errors[] = "This email address is already in use";
            }
            
            if (empty($errors)) {
                // Prepare update query
                if (!empty($new_password)) {
                    // Update with new password
                    $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
                    $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ?, password = ? WHERE id = ?");
                    $result = $stmt->execute([$new_username, $new_email, $hashed_password, $user_id]);
                } else {
                    // Update without changing password
                    $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
                    $result = $stmt->execute([$new_username, $new_email, $user_id]);
                }
                
                if ($result) {
                    $_SESSION['username'] = $new_username;
                    $user['username'] = $new_username;
                    $user['email'] = $new_email;
                    $success = "Profile updated successfully!";
                } else {
                    $errors[] = "Error during update";
                }
            }
        } catch (PDOException $e) {
            $errors[] = "Database error: " . $e->getMessage();
        }
    }
}

include __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <section class="profile-header">
        <h2>💽 My Profile</h2>
        <p>Manage your personal information</p>
    </section>
    
    <?php if (!empty($errors)): ?>
        <div class="error-messages">
            <?php foreach ($errors as $error): ?>
                <p><?php echo htmlspecialchars($error); ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    
    <?php if (!empty($success)): ?>
        <div class="success-message">
            <p><?php echo htmlspecialchars($success); ?></p>
        </div>
    <?php endif; ?>
    
    <div class="profile-grid">
        <section class="profile-info">
            <h3>Account Information</h3>
            <div class="info-card">
                <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email'] ?? 'Not defined'); ?></p>
                <p><strong>Member since:</strong> <?php echo isset($user['created_at']) ? date('m/d/Y', strtotime($user['created_at'])) : 'Unknown date'; ?></p>
            </div>
        </section>
        
        <section class="profile-edit">
            <h3>Edit Profile</h3>
            <form method="POST" class="profile-form">
                <div class="form-group">
                    <label for="username">Username:</label>
                    <input 
                        type="text" 
                        id="username" 
                        name="username" 
                        value="<?php echo htmlspecialchars($user['username'] ?? ''); ?>"
                        class="input-field"
                        required
                        minlength="3"
                        maxlength="50"
                    >
                </div>
                <div class="form-group">
                    <label for="email">Email Address:</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>"
                        class="input-field"
                        required
                    >
                </div>
                <div class="form-group">
                    <label for="password">New Password (optional):</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        class="input-field"
                        placeholder="New password"
                    >
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn">Save</button>
                </div>
            </form>
        </section>
        
        <section class="profile-stats">
            <h3>Statistics</h3>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number">0</div>
                    <div class="stat-label">Photos published</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">0</div>
                    <div class="stat-label">Likes received</div>
                </div>
            </div>
        </section>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

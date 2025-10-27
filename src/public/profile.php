<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/PasswordCheck.php'; // ✅ Correction du chemin

$page_title = "My Profile - Camagru";
$page_css = "profile";
$user_id = $_SESSION['user_id'];
$success = '';
$errors = [];

// Get user information
try {
    $pdo = getDatabase();
    $stmt = $pdo->prepare("SELECT username, email, created_at, notify_comments FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Get number of published photos
    $stmt = $pdo->prepare("SELECT COUNT(*) as photo_count FROM images WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $photo_count = $stmt->fetch(PDO::FETCH_ASSOC)['photo_count'];
    
    // Get total number of likes received on all user's photos
    $stmt = $pdo->prepare("
        SELECT COUNT(l.id) as total_likes 
        FROM likes l 
        INNER JOIN images i ON l.image_id = i.id 
        WHERE i.user_id = ?
    ");
    $stmt->execute([$user_id]);
    $total_likes = $stmt->fetch(PDO::FETCH_ASSOC)['total_likes'];
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
    
    if (!empty($new_password)) {
        $passwordValidation = PasswordCheck::isValid($new_password);
        if (!$passwordValidation['valid']) {
            $errors = array_merge($errors, $passwordValidation['errors']);
        }
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
            <div class="info-card">
                <div class="notification-option">
                    <label style="display: flex; align-items: center; cursor: pointer;">
                        <input 
                        type="checkbox" 
                        id="notify_comments" 
                            <?php echo (isset($user['notify_comments']) && $user['notify_comments'] == 1) ? 'checked' : ''; ?>
                            style="margin-right: 10px; width: 20px; height: 20px; cursor: pointer;"
                            onchange="updateNotificationPreference(this.checked)"
                        >
                        <span>Receive email when someone comments on my photos</span>
                    </label>
                    <small class="form-hint" style="margin-left: 30px; display: block; margin-top: 5px;">
                        You will receive an email each time someone comments on one of your public photos
                    </small>
                    <div id="notification-status" style="margin-left: 30px; margin-top: 10px; font-size: 0.9em;"></div>
                </div>
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
                        placeholder="Leave blank to keep current password"
                    >
                    <!-- ✅ Ajout des exigences -->
                    <small class="form-hint">
                        If changing: 8+ chars, uppercase, lowercase, number, special character
                    </small>
                </div>

                
                <div class="form-group">
                    <button type="submit" class="btn">Save Changes</button>
                </div>
            </form>
        </section>
        
        <section class="profile-stats">
            <h3>Statistics</h3>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number"><?php echo htmlspecialchars($photo_count ?? 0); ?></div>
                    <div class="stat-label">Photos published</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo htmlspecialchars($total_likes ?? 0); ?></div>
                    <div class="stat-label">Likes received</div>
                </div>
            </div>
        </section>
    </div>
</div>

<script>
async function updateNotificationPreference(isEnabled) {
    const statusDiv = document.getElementById('notification-status');
    const checkbox = document.getElementById('notify_comments');
    
    // Afficher un message de chargement
    statusDiv.innerHTML = '<span style="color: #666;">⏳ Saving...</span>';
    
    try {
        const response = await fetch('api/update_notifications.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                notify_comments: isEnabled ? 1 : 0
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            statusDiv.innerHTML = '<span style="color: #4CAF50;">✅ Saved successfully!</span>';
            setTimeout(() => {
                statusDiv.innerHTML = '';
            }, 3000);
        } else {
            statusDiv.innerHTML = '<span style="color: #f44336;">❌ Error: ' + (data.message || 'Failed to save') + '</span>';
            // Revert checkbox state on error
            checkbox.checked = !isEnabled;
        }
    } catch (error) {
        console.error('Error updating notification preference:', error);
        statusDiv.innerHTML = '<span style="color: #f44336;">❌ Network error</span>';
        // Revert checkbox state on error
        checkbox.checked = !isEnabled;
    }
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>

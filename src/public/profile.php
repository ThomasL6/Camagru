<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../classes/Database.php';

$page_title = "Mon Profil - Camagru";
$page_css = "profile";
$user_id = $_SESSION['user_id'];
$success = '';
$errors = [];

// Récupérer les infos utilisateur
try {
    $pdo = getDatabase();
    $stmt = $pdo->prepare("SELECT username, email, created_at FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $errors[] = "Erreur lors du chargement du profil";
}

// Traitement du formulaire de modification
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_username = trim($_POST['username'] ?? '');
    
    if (empty($new_username)) {
        $errors[] = "Le nom d'utilisateur est requis";
    } elseif (strlen($new_username) < 3) {
        $errors[] = "Le nom d'utilisateur doit contenir au moins 3 caractères";
    } else {
        try {
            // Vérifier si le nom d'utilisateur existe déjà
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
            $stmt->execute([$new_username, $user_id]);
            
            if ($stmt->fetch()) {
                $errors[] = "Ce nom d'utilisateur est déjà utilisé";
            } else {
                // Mettre à jour le nom d'utilisateur
                $stmt = $pdo->prepare("UPDATE users SET username = ? WHERE id = ?");
                if ($stmt->execute([$new_username, $user_id])) {
                    $_SESSION['username'] = $new_username;
                    $user['username'] = $new_username;
                    $success = "Profil mis à jour avec succès !";
                } else {
                    $errors[] = "Erreur lors de la mise à jour";
                }
            }
        } catch (PDOException $e) {
            $errors[] = "Erreur de base de données";
        }
    }
}

include __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <section class="profile-header">
        <h2>👤 Mon Profil</h2>
        <p>Gérez vos informations personnelles</p>
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
            <h3>Informations du compte</h3>
            <div class="info-card">
                <p><strong>Email :</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                <p><strong>Membre depuis :</strong> <?php echo date('d/m/Y', strtotime($user['created_at'])); ?></p>
            </div>
        </section>
        
        <section class="profile-edit">
            <h3>Modifier le profil</h3>
            <form method="POST" class="profile-form">
                <div class="form-group">
                    <label for="username">Nom d'utilisateur :</label>
                    <input 
                        type="text" 
                        id="username" 
                        name="username" 
                        value="<?php echo htmlspecialchars($user['username']); ?>"
                        class="input-field"
                        required
                        minlength="3"
                        maxlength="50"
                    >
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn">💾 Enregistrer</button>
                </div>
            </form>
        </section>
        
        <section class="profile-stats">
            <h3>Statistiques</h3>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number">0</div>
                    <div class="stat-label">Photos publiées</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">0</div>
                    <div class="stat-label">J'aime reçus</div>
                </div>
            </div>
        </section>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

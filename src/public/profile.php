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
    $errors[] = "Erreur lors du chargement du profil : " . $e->getMessage();
}

// Traitement du formulaire de modification
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_username = trim($_POST['username'] ?? '');
    $new_email = trim($_POST['email'] ?? '');
    $new_password = trim($_POST['password'] ?? '');
    
    // Validation des champs
    if (empty($new_username)) {
        $errors[] = "Le nom d'utilisateur est requis";
    } elseif (strlen($new_username) < 3) {
        $errors[] = "Le nom d'utilisateur doit contenir au moins 3 caractères";
    }
    
    if (empty($new_email)) {
        $errors[] = "L'adresse email est requise";
    } elseif (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "L'adresse email n'est pas valide";
    }
    
    // Validation du mot de passe (optionnel)
    if (!empty($new_password) && strlen($new_password) < 6) {
        $errors[] = "Le mot de passe doit contenir au moins 6 caractères";
    }
    
    if (empty($errors)) {
        try {
            // Vérifier si le nom d'utilisateur existe déjà
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
            $stmt->execute([$new_username, $user_id]);
            
            if ($stmt->fetch()) {
                $errors[] = "Ce nom d'utilisateur est déjà utilisé";
            }
            
            // Vérifier si l'email existe déjà
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $stmt->execute([$new_email, $user_id]);
            
            if ($stmt->fetch()) {
                $errors[] = "Cette adresse email est déjà utilisée";
            }
            
            if (empty($errors)) {
                // Préparer la requête de mise à jour
                if (!empty($new_password)) {
                    // Mettre à jour avec le nouveau mot de passe
                    $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
                    $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ?, password = ? WHERE id = ?");
                    $result = $stmt->execute([$new_username, $new_email, $hashed_password, $user_id]);
                } else {
                    // Mettre à jour sans changer le mot de passe
                    $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
                    $result = $stmt->execute([$new_username, $new_email, $user_id]);
                }
                
                if ($result) {
                    $_SESSION['username'] = $new_username;
                    $user['username'] = $new_username;
                    $user['email'] = $new_email;
                    $success = "Profil mis à jour avec succès !";
                } else {
                    $errors[] = "Erreur lors de la mise à jour";
                }
            }
        } catch (PDOException $e) {
            $errors[] = "Erreur de base de données : " . $e->getMessage();
        }
    }
}

include __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <section class="profile-header">
        <h2>💽 Mon Profil</h2>
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
                <p><strong>Email :</strong> <?php echo htmlspecialchars($user['email'] ?? 'Non défini'); ?></p>
                <p><strong>Membre depuis :</strong> <?php echo isset($user['created_at']) ? date('d/m/Y', strtotime($user['created_at'])) : 'Date inconnue'; ?></p>
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
                        value="<?php echo htmlspecialchars($user['username'] ?? ''); ?>"
                        class="input-field"
                        required
                        minlength="3"
                        maxlength="50"
                    >
                </div>
                <div class="form-group">
                    <label for="email">Adresse mail :</label>
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
                    <label for="password">Nouveau mot de passe (optionnel) :</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        class="input-field"
                        placeholder="New password"
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

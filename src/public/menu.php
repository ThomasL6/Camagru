<?php
require_once __DIR__ . '/../includes/auth_check.php';

$page_title = "Menu - Camagru";
$page_css = "menu";
include __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <section class="welcome-section">
        <h2>Welcome to Camagru! 🎉</h2>
        <p class="welcome-text">Create, share and discover unique moments with our community.</p>
    </section>
    
    <section class="quick-actions">
        <h3>Quick Actions</h3>
        <div class="action-grid">
            <a href="camera.php" class="action-card">
                <div class="card-icon">📸</div>
                <h4>Take a Photo</h4>
                <p>Capture a moment and add creative effects</p>
            </a>
            
            <a href="gallery.php" class="action-card">
                <div class="card-icon">🖼️</div>
                <h4>My Gallery</h4>
                <p>Browse your creations and those of the community</p>
            </a>
            
            <a href="profile.php" class="action-card">
                <div class="card-icon">💽</div>
                <h4>My Profile</h4>
                <p>Manage your information and preferences</p>
            </a>
            
            <a href="#" class="action-card">
                <div class="card-icon">🎨</div>
                <h4>Popular Creations</h4>
                <p>Discover trending content</p>
            </a>
        </div>
    </section>
    
    <section class="recent-activity">
        <h3>Recent Activity</h3>
        <div class="activity-placeholder">
            <p>🚀 Coming soon: Your latest activities will appear here!</p>
        </div>
    </section>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
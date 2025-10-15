<?php
require_once __DIR__ . '/../includes/auth_check.php';

$page_title = "Camera - Camagru";
$page_css = "camera";
include __DIR__ . '/../includes/header.php';
echo '<link rel="stylesheet" href="css/placeholder.css">';
?>

<div class="container">
    <section class="camera-header">
        <h2>📸 Photo Studio</h2>
        <p>Capture your moments and add creative effects</p>
    </section>
    
    <div class="camera-container">
        <div class="camera-section">
            <h3>📹 Live Preview</h3>
            <div class="video-container">
                <video id="video">Video stream not available.</video>
            </div>
            <div class="camera-controls">
                <button id="start-button" class="btn capture-btn">📸 Take Photo</button>
            </div>
        </div>
        <div class="photo-section">
            <h3>🖼️ Captured Photo</h3>
            <div class="photo-container">
                <img id="photo" alt="The captured image will be displayed here" />
            </div>
            <div class="photo-controls">
                <div class="visibility-options" id="visibility-options" style="display: none;">
                    <label>
                        <input type="radio" name="visibility" value="private" checked>
                        🔒 Private (Only visible to you)
                    </label>
                    <label>
                        <input type="radio" name="visibility" value="public">
                        🌍 Public (Visible in community feed)
                    </label>
                </div>
                <button id="save-button" class="btn save-btn" disabled>💾 Save Photo</button>
            </div>            
            <canvas id="canvas" style="display: none;"></canvas>
        </div>
    </div>
</div>

<script src="js/camera.js"></script>
<?php include __DIR__ . '/../includes/footer.php'; ?>

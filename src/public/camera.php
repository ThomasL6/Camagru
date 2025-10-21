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
                
                <div class="video-filters-overlay">
                    <button id="filters-toggle" class="filter-overlay-btn main-filter-btn" title="Filtres">
                        <span class="filter-icon">🎨</span>
                    </button>
                    <div class="filters-menu" id="filters-menu">
                        <button id="filter-none" class="filter-overlay-btn active" title="Aucun filtre">
                            <span class="filter-icon">⭕</span>
                        </button>
                        <button id="filter-grayscale" class="filter-overlay-btn" title="Noir et Blanc">
                            <span class="filter-icon">⚫</span>
                        </button>
                        <button id="filter-sepia" class="filter-overlay-btn" title="Sépia">
                            <span class="filter-icon">🟤</span>
                        </button>
                        <button id="filter-invert" class="filter-overlay-btn" title="Inverser">
                            <span class="filter-icon">🔄</span>
                        </button>
                    </div>
                </div>
            </div>
            <div class="camera-controls">
                <button id="start-button" class="btn capture-btn">📸 Take Photo</button>
                <div class="upload-section">
                    <label for="upload" class="btn upload-btn">
                        📁 Upload Photo
                        <input type="file" id="upload" accept="image/*" style="display: none;">
                    </label>
                </div>
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
                <button id="open-sticker-modal" class="btn" style="margin-bottom: 10px;">🎨 Ajouter des stickers</button>
                <button id="save-button" class="btn save-btn" disabled>💾 Save Photo</button>
            </div>            
            <canvas id="canvas" style="display: none;"></canvas>
        </div>
    </div>

    <div id="sticker-modal" class="modal">
        <div class="modal-content">
            <span class="close-button">&times;</span>
            <h3>Ajoutez des stickers à votre photo</h3>
            
            <div class="modal-workspace">
                <div class="stickers-palette">
                    <h4>Choisissez un sticker :</h4>
                    <div id="stickers-list">
                        <img src="stickers/CatMoney.png" class="sticker" alt="Sticker Money Cat">
                        <img src="stickers/Racoon1.png" class="sticker" alt="Racoon Sticker">
                        <img src="stickers/Hippo.png" class="sticker" alt="Hippo Sticker">
                        <img src="stickers/MoneyPenguin.png" class="sticker" alt="Money Penguin Sticker">
                        <img src="stickers/Coffee.png" class="sticker" alt="Coffee Sticker">
                        <img src="stickers/Grumpy.png" class="sticker" alt="Grumpy Sticker">
                    </div>
                </div>
                
                <div class="image-editor">
                    <h4>Cliquez sur l'image pour placer le sticker :</h4>
                    <div class="canvas-wrapper">
                        <canvas id="edit-canvas"></canvas>
                    </div>
                </div>
            </div>
            
            <div class="modal-actions">
                <button id="validate-stickers" class="btn">✓ Valider</button>
                <button id="cancel-stickers" class="btn">✕ Annuler</button>
            </div>
        </div>
    </div>
</div>

<script src="js/camera.js"></script>
<?php include __DIR__ . '/../includes/footer.php'; ?>

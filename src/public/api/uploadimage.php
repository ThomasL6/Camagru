<?php
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display errors in HTML
ini_set('log_errors', 1);

session_start();
require_once __DIR__ . '/../../classes/Database.php';

header('Content-Type: application/json');

// Check if user is authenticated
if(!isset($_SESSION['user_id'])){
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

if($_SERVER['REQUEST_METHOD'] !== 'POST'){
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if(!isset($input['image'])) {
    echo json_encode(['success' => false, 'error' => 'No image data provided']);
    exit;
}

try {
    // Decode base64 image
    $imageData = $input['image'];
    $imageData = str_replace('data:image/png;base64,', '', $imageData);
    $imageData = base64_decode($imageData);

    if($imageData === false) {
        throw new Exception("Invalid image data");
    }

    // Create GD image resource from decoded data
    $img = imagecreatefromstring($imageData);
    if($img === false) {
        throw new Exception("Failed to create image from data");
    }

    // Get image dimensions
    $width = imagesx($img);
    $height = imagesy($img);

    //Apply CSS filter server-side with GD
    $filter = isset($input['filter']) ? $input['filter'] : 'none';
    
    if ($filter === 'grayscale(1)') {
        imagefilter($img, IMG_FILTER_GRAYSCALE);
    } elseif ($filter === 'sepia(1)') {
        // Sepia effect (grayscale + colorize)
        imagefilter($img, IMG_FILTER_GRAYSCALE);
        imagefilter($img, IMG_FILTER_COLORIZE, 100, 50, 0);
    } elseif ($filter === 'invert(1)') {
        imagefilter($img, IMG_FILTER_NEGATE);
    }

    //Apply stickers server-side
    if (isset($input['stickers']) && is_array($input['stickers'])) {
        foreach ($input['stickers'] as $stickerData) {
            if (!isset($stickerData['path']) || !isset($stickerData['x']) || !isset($stickerData['y']) || !isset($stickerData['size'])) {
                continue;
            }

            // Extract just the filename from the path (e.g., "stickers/CatMoney.png" -> "CatMoney.png")
            $pathParts = explode('/', $stickerData['path']);
            $stickerFilename = end($pathParts);
            
            // Security: validate sticker path (must be in stickers directory)
            $stickerPath = __DIR__ . '/../stickers/' . $stickerFilename;
            if (!file_exists($stickerPath)) {
                error_log("Sticker not found: " . $stickerPath);
                continue;
            }

            // Load sticker image
            $stickerImg = @imagecreatefrompng($stickerPath);
            if ($stickerImg === false) {
                error_log("Failed to load sticker: " . $stickerPath);
                continue;
            }

            // Calculate position (center the sticker at given coordinates)
            $stickerSize = (int)$stickerData['size'];
            $x = (int)$stickerData['x'] - ($stickerSize / 2);
            $y = (int)$stickerData['y'] - ($stickerSize / 2);

            // Resize sticker to desired size
            $resizedSticker = imagecreatetruecolor($stickerSize, $stickerSize);
            imagealphablending($resizedSticker, false);
            imagesavealpha($resizedSticker, true);
            
            imagecopyresampled(
                $resizedSticker, 
                $stickerImg, 
                0, 0, 0, 0,
                $stickerSize, $stickerSize,
                imagesx($stickerImg), imagesy($stickerImg)
            );

            // Merge sticker onto main image with alpha blending
            imagealphablending($img, true);
            imagecopy($img, $resizedSticker, $x, $y, 0, 0, $stickerSize, $stickerSize);

            imagedestroy($stickerImg);
            imagedestroy($resizedSticker);
        }
    }

    //  Save processed image
    $fileName = 'photo_' . $_SESSION['user_id'] . '_' . time() . '_' . uniqid() . '.png';
    $uploadDir = __DIR__ . '/../uploads/images/';

    if(!is_dir($uploadDir)) {
        if(!mkdir($uploadDir, 0755, true)) {
            throw new Exception("Failed to create upload directory");
        }
    }

    $filePath = $uploadDir . $fileName;
    
    // Save as PNG with alpha channel support
    imagesavealpha($img, true);
    if(!imagepng($img, $filePath)) {
        throw new Exception("Failed to save processed image");
    }

    // Free memory
    imagedestroy($img);

    //Save to database
    $isPublic = isset($input['is_public']) ? (bool)$input['is_public'] : false;

    $pdo = getDatabase();
    $stmt = $pdo->prepare("INSERT INTO images (user_id, image_path, is_public, created_at) VALUES (?, ?, ?, NOW())");
    $stmt->execute([$_SESSION['user_id'], $fileName, $isPublic ? 1 : 0]);

    echo json_encode([
        'success' => true, 
        'message' => 'Photo saved successfully',
        'image_id' => $pdo->lastInsertId(),
        'filename' => $fileName
    ]);
} catch (Exception $e) {
    error_log("Error saving image: " . $e->getMessage());
    
    // Specific error messages without revealing technical details
    $userMessage = 'Failed to save image';
    
    if (strpos($e->getMessage(), 'directory') !== false) {
        $userMessage = 'Upload directory error';
    } elseif (strpos($e->getMessage(), 'database') !== false || strpos($e->getMessage(), 'SQL') !== false) {
        $userMessage = 'Database error';
    } elseif (strpos($e->getMessage(), 'Invalid image') !== false) {
        $userMessage = 'Invalid image format';
    }
    
    echo json_encode(['success' => false, 'error' => $userMessage]);
}
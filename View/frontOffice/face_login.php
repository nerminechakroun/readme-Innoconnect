<?php
session_start();
require_once __DIR__ . '/../../config.php';

$logFile = __DIR__ . '/../../debug.log';
function logMessage($message) {
    global $logFile;
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - " . $message . "\n", FILE_APPEND);
}

logMessage("Starting face login process");

if (!isset($_FILES['webcam_image']) || $_FILES['webcam_image']['error'] !== UPLOAD_ERR_OK) {
    $errorMsg = isset($_FILES['webcam_image']) ? "Error code: " . $_FILES['webcam_image']['error'] : 'No file uploaded';
    logMessage("No webcam image received. $errorMsg");
    echo json_encode(['status' => 'error', 'message' => 'Aucune image webcam reçue.']);
    exit;
}

$uploadDir = __DIR__ . '/../../uploads/temp/';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
$fileExt = strtolower(pathinfo($_FILES['webcam_image']['name'], PATHINFO_EXTENSION));
$webcamImagePath = $uploadDir . 'webcam_' . time() . '.' . $fileExt;

if (!move_uploaded_file($_FILES['webcam_image']['tmp_name'], $webcamImagePath)) {
    logMessage("Failed to move uploaded file to $webcamImagePath");
    echo json_encode(['status' => 'error', 'message' => 'Échec de l\'enregistrement de l\'image.']);
    exit;
}
logMessage("Webcam image saved at: $webcamImagePath");

$brightness_script = __DIR__ . '/../../scripts/check_brightness.py';
$brightness_command = escapeshellcmd('python ' . $brightness_script . ' ' . $webcamImagePath);
$brightness_output = shell_exec($brightness_command);
logMessage("Brightness check result: $brightness_output");

if (strpos($brightness_output, "Image trop sombre") !== false || strpos($brightness_output, "Trop de reflets") !== false) {
    logMessage("Image rejected: $brightness_output");
    unlink($webcamImagePath);
    echo json_encode(['status' => 'error', 'message' => trim($brightness_output)]);
    exit;
}

$python_script = __DIR__ . '/../../scripts/generate_temp_encoding.py';
$tempEncodingPath = __DIR__ . '/../../face_encodings/temp_encoding.npy';
$command = escapeshellcmd('python ' . $python_script . ' ' . $webcamImagePath . ' ' . $tempEncodingPath);
$output = shell_exec($command);
logMessage("Generate temp encoding result: $output");

if (strpos($output, "Aucun visage détecté") !== false || !file_exists($tempEncodingPath)) {
    logMessage("No face detected in webcam image");
    unlink($webcamImagePath);
    echo json_encode(['status' => 'error', 'message' => 'Aucun visage détecté.']);
    exit;
}

$webcamEncoding = unserialize(file_get_contents($tempEncodingPath));
logMessage("Webcam encoding loaded, size: " . count($webcamEncoding));

$db = config::getConnexion();
$stmt = $db->query("SELECT id_utilisateur FROM utilisateur");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

$matchFound = false;
$matchedUserId = null;

foreach ($users as $user) {
    $userId = $user['id_utilisateur'];
    $encodingFile = __DIR__ . "/../../face_encodings/user_{$userId}.npy";

    if (!file_exists($encodingFile)) {
        logMessage("Encoding file not found for user $userId");
        continue;
    }

    $storedEncoding = unserialize(file_get_contents($encodingFile));
    logMessage("Stored encoding loaded for user $userId, size: " . count($storedEncoding));

    $python_compare_script = __DIR__ . '/../../scripts/compare_encodings.py';
    $command = escapeshellcmd('python ' . $python_compare_script . ' ' . $tempEncodingPath . ' ' . $encodingFile);
    $compareOutput = shell_exec($command);
    logMessage("Comparison result for user $userId: $compareOutput");

    if (trim($compareOutput) === "True") {
        $matchFound = true;
        $matchedUserId = $userId;
        break;
    }
}

// After finding a match, add additional verification
if ($matchFound) {
    logMessage("Face matched for user ID: $matchedUserId");
    
    // Add additional verification if needed
    // For example, you could check if the user has recently logged in from this device
    // or require a secondary verification for high-security actions
    
    $_SESSION['user_id'] = $matchedUserId;
    echo json_encode(['status' => 'success', 'message' => 'Visage reconnu avec succès !']);
} else {
    logMessage("No matching face found");
    echo json_encode(['status' => 'error', 'message' => 'Face recognition failed. Visage non reconnu.']);
}
unlink($webcamImagePath);
unlink($tempEncodingPath);
?>

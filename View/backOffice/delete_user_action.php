<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../Controller/utilisateurC.php';

session_start();

// Log file
$logFile = __DIR__ . '/../../debug.log';
function logMessage($message) {
    global $logFile;
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - " . $message . "\n", FILE_APPEND);
}

// Check if the user is logged in and is an admin
if (!isset($_SESSION['id_utilisateur'])) {
    logMessage("Delete attempt failed: Not logged in");
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$userId = $_SESSION['id_utilisateur'];
$userC = new userC();
$userType = $userC->getUserType($userId);

if ($userType !== 'administrateur') {
    logMessage("Delete attempt failed: Not authorized for user ID $userId");
    echo json_encode(['success' => false, 'message' => 'Not authorized']);
    exit;
}

// Handle deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_utilisateur'])) {
    $idToDelete = (int)$_POST['id_utilisateur'];
    logMessage("Delete request received for ID: $idToDelete");
    $userToDelete = $userC->getUserById($idToDelete);
    if (!$userToDelete) {
        logMessage("Delete failed: User ID $idToDelete not found");
        echo json_encode(['success' => false, 'message' => 'User not found']);
        exit;
    }
    $success = $userC->deleteUser($idToDelete);
    if ($success) {
        logMessage("User deleted successfully: ID $idToDelete");
        echo json_encode(['success' => true, 'message' => 'User deleted successfully']);
    } else {
        logMessage("Delete failed for ID $idToDelete");
        echo json_encode(['success' => false, 'message' => 'Failed to delete user']);
    }
    exit;
}

logMessage("Invalid delete request");
echo json_encode(['success' => false, 'message' => 'Invalid request']);
exit;
?>
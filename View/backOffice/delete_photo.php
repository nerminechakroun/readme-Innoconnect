<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../Controller/utilisateurC.php';
session_start();

// Vérifier si l'utilisateur est connecté et est un admin
if (!isset($_SESSION['id_utilisateur'])) {
    header("Location: ../frontOffice/login.php");
    exit;
}

$userC = new userC();
$userId = $_SESSION['id_utilisateur'];
$userType = $userC->getUserType($userId);

if ($userType !== 'administrateur') {
    header("Location: ../frontOffice/login.php");
    exit;
}

// Vérifier si un ID utilisateur est fourni
if (!isset($_GET['id_utilisateur']) || !is_numeric($_GET['id_utilisateur'])) {
    header("Location: listeUser.php?error=Invalid user ID");
    exit;
}

$id_utilisateur = (int)$_GET['id_utilisateur'];

// Récupérer l'utilisateur pour obtenir le chemin de la photo
$user = $userC->getUserById($id_utilisateur);
if (!$user) {
    header("Location: listeUser.php?error=User not found");
    exit;
}

// Vérifier si l'utilisateur a une photo
if (empty($user['photo_profil'])) {
    header("Location: listeUser.php?error=User has no profile photo");
    exit;
}

// Supprimer le fichier de la photo du serveur
$photoPath = __DIR__ . '/../../uploads/' . $user['photo_profil'];
if (file_exists($photoPath)) {
    unlink($photoPath); // Supprime le fichier
}

// Mettre à jour la base de données pour supprimer la référence à la photo
$conn = config::getConnexion();
$stmt = $conn->prepare("UPDATE utilisateur SET photo_profil = NULL WHERE id_utilisateur = ?");
$stmt->execute([$id_utilisateur]);

// Rediriger vers listeUser.php avec un message de succès
header("Location: listeUser.php?success=Profile photo deleted successfully&highlight=$id_utilisateur");
exit;
?>
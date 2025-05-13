<?php
require_once '../../Model/Contrat.php';
require_once '../../Controller/ContratController.php';
session_start();

// Check if the user is logged in
if (!isset($_SESSION['id_utilisateur'])) {
    header("Location: login.php");
    exit;
}

// Check if user is an investisseur
if ($_SESSION['user_type'] !== 'investisseur') {
    header("Location: profile.php?error=Access denied. This page is for investisseurs only.");
    exit;
}

$successMessages = [];
$errorMessages = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $innovateurSignaturePath = null;
        if (!empty($_POST['investisseur_signature'])) {
            $data = str_replace('data:image/png;base64,', '', $_POST['investisseur_signature']);
            $data = str_replace(' ', '+', $data);
            $binaryData = base64_decode($data);

            if (!is_dir('uploads')) {
                mkdir('uploads', 0777, true);
            }

            $innovateurSignaturePath = 'uploads/investisseur_' . uniqid() . '.png';
            file_put_contents($innovateurSignaturePath, $binaryData);
        }

        $contrat = new Contrat();
        $contrat->setInvestisseurNom($_POST['nom_investisseur'] ?? '');
        $contrat->setInvestisseurId($_POST['id_investisseur'] ?? '');
        $contrat->setInvestisseurEmail($_POST['email_investisseur'] ?? '');
        $contrat->setMontant($_POST['montant_investi'] ?? 0);
        $contrat->setDateSignature($_POST['date_signature'] ?? date('Y-m-d'));
        $contrat->setStatut($_POST['statut'] ?? 'En attente');
        $contrat->setInvestisseurSignature($_POST['investisseur_signature'] ?? '');

        $contrat->setProjetNom('Projet Inconnu');
        $contrat->setTypeFinancement('Financement Inconnu');
        $contrat->setInnovateurId(null);
        $contrat->setInnovateurNom(null);
        $contrat->setInnovateurEmail(null);
        $contrat->setInnovateurSignature(null);

        $contratC = new ContratController();
        $id = $contratC->addContrat($contrat);
        
        // Success message
        $successMessages[] = "Contrat successfully added with ID: " . htmlspecialchars($id);
        
    } catch (Exception $e) {
        // Error message
        $errorMessages[] = "An error occurred: " . htmlspecialchars($e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Contrat d'Investissement - Signature</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="../../styles.css" rel="stylesheet">
  <style>
        /* Override any background colors */
        html, body {
            background-color: #ffffff !important;
            background: #ffffff !important;
            background-image: none !important;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #333;
            line-height: 1.6;
        }

        .container {
            background-color: #fff;
            padding: 30px 40px;
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
            max-width: 800px;
            margin: 120px auto 60px;
        }

        h1, h2 {
            color: #6610f2;
            margin-bottom: 15px;
        }

        h1 {
            font-size: 2rem;
            margin-bottom: 20px;
        }

        h2 {
            font-size: 1.5rem;
            margin-top: 30px;
        }

        p {
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #495057;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 12px;
            border-radius: 8px;
            border: 1px solid #ced4da;
            font-size: 16px;
            transition: border-color 0.3s;
            color: #333;
            background-color: #ffffff;
        }

        .form-group input:focus,
        .form-group select:focus {
            border-color: #6610f2;
            outline: none;
            box-shadow: 0 0 0 3px rgba(102, 16, 242, 0.2);
        }

        .form-actions {
            margin-top: 30px;
        }

        .form-actions button {
            background-color: #6610f2;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
            font-size: 16px;
            transition: all 0.3s;
            width: 100%;
        }

        .form-actions button:hover {
            background-color: #5a0dd9;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .signature-container {
            margin-top: 40px;
            padding-top: 30px;
            border-top: 1px solid #eee;
        }

        .signature-pad {
            border: 2px solid #6610f2;
            border-radius: 12px;
            width: 100%;
            height: 200px;
            background-color: #ffffff;
            box-shadow: 0 4px 12px rgba(102, 16, 242, 0.2);
            touch-action: none;
        }

        .signature-buttons {
            margin-top: 15px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .signature-buttons button {
            padding: 12px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
            font-size: 15px;
            transition: all 0.3s;
            width: auto;
        }

        .clear-btn {
            background-color: #f5365c;
            color: white;
        }

        .clear-btn:hover {
            background-color: #e03154;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .download-btn {
            background-color: #11cdef;
            color: white;
        }

        .download-btn:hover {
            background-color: #0fb8d4;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        small {
            display: block;
            margin-top: 5px;
            color: #6c757d;
            font-size: 0.85rem;
        }

        /* Footer styles */
        .footer {
            background: linear-gradient(135deg, #6f42c1, #6610f2);
            color: white;
            padding: 60px 0 30px;
            width: 100%;
        }

        .footer-container {
            width: 100%;
            margin: 0 auto;
            padding: 0 3rem;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
        }

        .footer-about .logo {
            font-size: 24px;
            margin-bottom: 15px;
            display: block;
            color: white;
        }

        .footer-about p {
            margin-bottom: 20px;
            opacity: 0.9;
        }

        .footer-links h4 {
            margin-bottom: 20px;
            font-size: 1.2rem;
            color: white;
        }

        .footer-links ul {
            list-style: none;
        }

        .footer-links li {
            margin-bottom: 10px;
        }

        .footer-links a {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: color 0.3s;
        }

        .footer-links a:hover {
            color: white;
        }

        .social-links {
            display: flex;
            gap: 10px;
        }

        .social-links a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            background: rgba(255, 255, 255, 0.1);
            color: white;
            border-radius: 50%;
            transition: all 0.3s;
        }

        .social-links a:hover {
            background: white;
            color: #6610f2;
        }

        .copyright {
            text-align: center;
            padding-top: 30px;
            margin-top: 30px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.9rem;
        }

        /* Message container styles */
        #message-container {
            position: fixed;
            top: 100px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 1001;
            width: 90%;
            max-width: 500px;
            transition: all 0.5s ease;
            opacity: 0;
            visibility: hidden;
        }

        #message-container.show {
            opacity: 1;
            visibility: visible;
        }

        .success-message {
            background-color: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 10px;
            border: 1px solid #c3e6cb;
        }

        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 10px;
            border: 1px solid #f5c6cb;
        }

        @media (max-width: 768px) {
            .container {
                margin: 150px auto 60px;
                padding: 20px;
            }

            .signature-buttons {
                flex-direction: column;
            }

            .signature-buttons button {
                width: 100%;
            }
        }

        @media (max-width: 480px) {
            .container {
                margin: 180px 15px 60px;
                padding: 15px;
            }

            h1 {
                font-size: 1.5rem;
            }

            h2 {
                font-size: 1.2rem;
            }
        }

        .message {
            padding: 15px;
            margin: 10px 0;
            border-radius: 5px;
            display: flex;
            align-items: center;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .icon {
            margin-right: 10px;
        }
        @media (max-width: 600px) {
            .message {
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <header style="background-color: #6f42c1; padding: 15px 20px; width: 100%; position: fixed; top: 0; z-index: 1000;">
        <div style="display: flex; align-items: center; justify-content: space-between; max-width: 1200px; margin: 0 auto;">
            <div style="display: flex; align-items: center;">
                <img src="../../innoconnect.jpeg" alt="InnoConnect Logo" style="width: 40px; height: 40px;">
                <h1 style="color: white; margin: 0 0 0 15px; font-size: 24px;">InnoConnect</h1>
            </div>
            <nav style="display: flex;">
                <ul style="display: flex; list-style: none; margin: 0; padding: 0;">
                    <li style="margin: 0 15px;"><a href="../../index.html" style="color: white; text-decoration: none; font-weight: 500;">Home</a></li>
                    <li style="margin: 0 15px;"><a href="profile.php" style="color: white; text-decoration: none; font-weight: 500;">My Profile</a></li>
                    <li style="margin: 0 15px;"><a href="ContratInvestisseur.php" style="color: white; text-decoration: none; font-weight: 500; text-decoration: underline;">My Contracts</a></li>
                    <li style="margin: 0 15px;"><a href="Investisseur.php" style="color: white; text-decoration: none; font-weight: 500;">My Investments</a></li>
                    <li style="margin: 0 15px;"><a href="investisseurProjet.php" style="color: white; text-decoration: none; font-weight: 500;">Available Projects</a></li>
                    <li style="margin: 0 15px;"><a href="logout.php" style="color: white; text-decoration: none; font-weight: 500;">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="container">
    <div class="message-container">
            <?php
                // Display success messages
                foreach ($successMessages as $message) {
                    echo '<div class="message success"><span class="icon">✅</span>' . $message . '</div>';
                }

                // Display error messages
                foreach ($errorMessages as $message) {
                    echo '<div class="message error"><span class="icon">❌</span>' . $message . '</div>';
                }
            ?>
        </div>
      <h1>Contrat d'Investissement</h1>
      <p>
        Ce contrat certifie que l'investisseur accepte les termes de l'investissement accordé à InnoConnect.
      </p>

      <form class="contract-info" action="ContratInvestisseur.php" method="POST" id="contractForm">
        <h2>Informations du contrat</h2>

        <div class="form-group">
          <label for="nom_investisseur">Nom de l'Investisseur :</label>
          <input type="text" id="nom_investisseur" name="nom_investisseur" placeholder="Entrer le nom" required>
        </div>

        <div class="form-group">
          <label for="id_investisseur">ID de l'Investisseur :</label>
          <input type="number" id="id_investisseur" name="id_investisseur" placeholder="Entrer l'ID" required>
        </div>

        <div class="form-group">
          <label for="email_investisseur">Email de l'Investisseur :</label>
          <input type="email" id="email_investisseur" name="email_investisseur" placeholder="Entrer l'email" required>
        </div>

        <div class="form-group">
          <label for="montant_investi">Montant Investi (€) :</label>
          <input type="number" id="montant_investi" name="montant_investi" placeholder="Entrer le montant" step="0.01" min="0" required>
        </div>

        <div class="form-group">
          <label for="date_signature">Date de Signature :</label>
          <input type="date" id="date_signature" name="date_signature" required>
        </div>

        <div class="form-group">
          <label for="statut">Statut du Contrat :</label>
          <select id="statut" name="statut" required>
            <option value="">-- Sélectionnez un statut --</option>
            <option value="en attente">En attente</option>
            <option value="validé">Validé</option>
            <option value="rejeté">Rejeté</option>
          </select>
        </div>




      <div class="signature-container">
        <h2>Signature de l'Investisseur</h2>
        <canvas id="signatureCanvas" class="signature-pad"></canvas>
        <small>Signature électronique ayant valeur contractuelle</small>
        <input type="hidden" id="investisseur_signature" name="investisseur_signature" required>

        <div class="signature-buttons">
          <button type="button" class="clear-btn" onclick="clearSignature()">Effacer la Signature</button>
          <button type="submit" class="download-btn" onclick="saveSignature()">Valider la signature</button>
        </div>
        </form>

      </div>
    </div>

    <footer class="footer">
      <div class="footer-container">
        <div class="footer-about">
          <a href="#" class="logo">InnoConnect</a>
          <p>Fournisseur d'investissements innovants et solutions financières adaptées.</p>
        </div>

        <div class="footer-section">
            <div class="footer-links">
                <h3>Quick Links</h3>
                <ul>
                    <li><a href="../../index.html">Home</a></li>
                    <li><a href="profile.php">My Profile</a></li>
                    <li><a href="Investisseur.php">My Investments</a></li>
                    <li><a href="ContratInvestisseur.php">My Contracts</a></li>
                    <li><a href="investisseurProjet.php">Available Projects</a></li>
                </ul>
            </div>
        </div>

        <div class="footer-links">
          <h4>Réseaux Sociaux</h4>
          <div class="social-links">
            <a href="#" target="_blank" aria-label="Facebook">Fb</a>
            <a href="#" target="_blank" aria-label="Twitter">Tw</a>
            <a href="#" target="_blank" aria-label="LinkedIn">Li</a>
          </div>
        </div>
      </div>
      <div class="copyright">
        &copy; 2025 InnoConnect. Tous droits réservés.
      </div>
    </footer>

    <!-- Signature Pad Script -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/signature_pad/2.3.2/signature_pad.min.js"></script>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
      const form = document.getElementById('contractForm');
      const signatureCanvas = document.getElementById('signatureCanvas');
      const signatureDataInput = document.getElementById('investisseur_signature');

      form.addEventListener('submit', function(event) {
        if (!validateForm()) {
          event.preventDefault(); // Empêche l'envoi du formulaire si erreur
        }
      });

      function validateForm() {
        let isValid = true;
        let errorMessages = [];

        // Vérifier le nom
        const nom = form.elements['nom_investisseur'].value.trim();
        if (nom.length < 2 || !/^[A-Za-zÀ-ÿ\s\-']+$/.test(nom)) {
          isValid = false;
          errorMessages.push("Le nom de l'investisseur est invalide.");
        }

        // Vérifier l'ID
        const id = form.elements['id_investisseur'].value.trim();
        if (id === "" || isNaN(id) || id <= 0 || id > 999999) {
          isValid = false;
          errorMessages.push("L'ID de l'investisseur doit être un nombre positif inférieur à 999999.");
        }

        // Vérifier l'email
        const email = form.elements['email_investisseur'].value.trim();
        if (email === "" || !email.includes('@')) {
          isValid = false;
          errorMessages.push("L'email de l'investisseur est invalide.");
        }

        // Vérifier le montant
        const montant = form.elements['montant_investi'].value.trim();
        if (montant === "" || isNaN(montant) || montant <= 0) {
          isValid = false;
          errorMessages.push("Le montant investi doit être un nombre positif.");
        }

        // Vérifier la date
        const dateSignature = form.elements['date_signature'].value.trim();
        const today = new Date().toISOString().split('T')[0];
        if (dateSignature === "" || dateSignature > today) {
          isValid = false;
          errorMessages.push("La date de signature est invalide ou future.");
        }

        // Vérifier le statut
        const statut = form.elements['statut'].value;
        if (statut === "") {
          isValid = false;
          errorMessages.push("Veuillez sélectionner un statut pour le contrat.");
        }

        // Vérifier la signature
        if (isCanvasBlank(signatureCanvas)) {
          isValid = false;
          errorMessages.push("Veuillez signer avant de valider le formulaire.");
        } else {
          // Préparer la signature (convertir en image base64)
          signatureDataInput.value = signatureCanvas.toDataURL();
        }

        // Afficher les erreurs si besoin
        if (!isValid) {
          alert(errorMessages.join("\n"));
        }

        return isValid;
      }

      // Fonction pour vérifier si le canvas est vide
      function isCanvasBlank(canvas) {
        const context = canvas.getContext('2d');
        const pixelBuffer = new Uint32Array(
          context.getImageData(0, 0, canvas.width, canvas.height).data.buffer
        );
        return !pixelBuffer.some(color => color !== 0);
      }

    });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
        <script>
      const canvas = document.getElementById('signatureCanvas');
      const signaturePad = new SignaturePad(canvas, {
        penColor: '#000000',
        minWidth: 1.5,
        maxWidth: 2.5,
        backgroundColor: '#ffffff'
      });

      function resizeCanvas() {
        const ratio = Math.max(window.devicePixelRatio || 1, 1);
        canvas.width = canvas.offsetWidth * ratio;
        canvas.height = canvas.offsetHeight * ratio;
        canvas.getContext("2d").scale(ratio, ratio);
        signaturePad.clear();
      }

      window.addEventListener("resize", resizeCanvas);
      resizeCanvas();

      function clearSignature() {
        signaturePad.clear();
      }

      function validateSignature() {
        if (signaturePad.isEmpty()) {
          alert("Veuillez signer le contrat avant validation");
          return;
        }
        alert("Signature capturée ! Vous pouvez maintenant enregistrer le contrat.");
        const dataUrl = signaturePad.toDataURL();
        document.getElementById('investisseur_signature').value = dataUrl;
      }

      function prepareSignature() {
        if (signaturePad.isEmpty()) {
          alert("Veuillez signer le contrat avant de soumettre !");
          return false;
        }
        const dataUrl = signaturePad.toDataURL();
        document.getElementById('investisseur_signature').value = dataUrl;
        return true;
      }
      function saveSignature() {
        const canvas = document.getElementById('signatureCanvas');
        const signatureInput = document.getElementById('investisseur_signature');
        signatureInput.value = canvas.toDataURL('image/png');
    }
    function clearSignature() {
        const canvas = document.getElementById('signatureCanvas');
        const context = canvas.getContext('2d');
        context.clearRect(0, 0, canvas.width, canvas.height);
        document.getElementById('investisseur_signature').value = '';
    }
    </script>
</body>
</html>

<?php
require_once '../../Model/Contrat.php';
require_once '../../Controller/ContratController.php';
session_start();

// Check if the user is logged in
if (!isset($_SESSION['id_utilisateur'])) {
    header("Location: login.php");
    exit;
}

// Check if user is an innovateur
if ($_SESSION['user_type'] !== 'innovateur') {
    header("Location: profile.php?error=Access denied. This page is for innovateurs only.");
    exit;
}

$successMessages = [];
$errorMessages = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $innovateurSignaturePath = null;
        if (!empty($_POST['signature_innovateur'])) {
            $data = str_replace('data:image/png;base64,', '', $_POST['signature_innovateur']);
            $data = str_replace(' ', '+', $data);
            $binaryData = base64_decode($data);

            if (!is_dir('uploads')) {
                mkdir('uploads', 0777, true);
            }

            $innovateurSignaturePath = 'uploads/innovateur_' . uniqid() . '.png';
            file_put_contents($innovateurSignaturePath, $binaryData);
        }

        // No investisseur signature handling here unless needed

        $contrat = new Contrat();
        $contrat->setInnovateurNom($_POST['nom_innovateur']);
        $contrat->setInnovateurId($_POST['id_innovateur']);
        $contrat->setInnovateurEmail($_POST['email_innovateur']);
        $contrat->setInnovateurSignature($innovateurSignaturePath);

        $contrat->setMontant($_POST['montant_innovateur']);
        $contrat->setDateSignature($_POST['date_signature']);
        $contrat->setStatut($_POST['statut']);
        $contrat->setProjetNom('Projet Inconnu');
        $contrat->setTypeFinancement('Financement Inconnu'); 
        $contrat->setInvestisseurId(null);
        $contrat->setInvestisseurNom(null);
        $contrat->setInvestisseurEmail(null);
        $contrat->setInvestisseurSignature(null);

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
    <meta charset="UTF-8" />
    <title>Contrat d'Investissement - Signature Innovateur</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="../../styles.css" rel="stylesheet">
    <style>
        /* Override any background colors */
        html, body {
            background-color: #ffffff !important;
            background: #ffffff !important;
            background-image: none !important;
        }
        
        :root {
            --primary-color: #6f42c1;
            --secondary-color: #6610f2;
            --accent-blue: #094f88;
            --text-dark: #2c3e50;
            --text-light: #6c757d;
            --background-light: #e3f2fd;
            --white: #ffffff;
            --gradient-purple: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            --shadow-sm: 0 2px 10px rgba(0, 0, 0, 0.1);
            --shadow-md: 0 5px 15px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            color: var(--text-dark);
            line-height: 1.6;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* Header and Navigation Styles */
        header {
            background: var(--gradient-purple);
            padding: 1rem 0;
            width: 100%;
            box-shadow: var(--shadow-sm);
            position: fixed;
            top: 0;
            z-index: 1000;
        }

        header .logo {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        header .logo img {
            height: 50px;
        }

        header nav {
            display: flex;
            justify-content: center;
        }

        header nav ul {
            display: flex;
            list-style: none;
            margin: 0;
            padding: 0;
        }

        header nav ul li {
            margin: 0 10px;
        }

        header nav ul li a {
            color: var(--white);
            text-decoration: none;
            padding: 0.5rem 1rem;
            border-radius: 2rem;
            transition: all 0.3s ease;
        }

        header nav ul li a:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        header nav ul li a.active {
            background: var(--white);
            color: var(--primary-color);
            font-weight: bold;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .container {
            max-width: 800px;
            margin: 120px auto 60px;
            background-color: var(--white);
            padding: 30px 40px;
            border-radius: 12px;
            box-shadow: var(--shadow-md);
        }

        h1, h2 {
            color: var(--primary-color);
            margin-bottom: 1.5rem;
        }

        .info {
            background: var(--white);
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 4px 12px rgba(75, 0, 130, 0.2);
            margin-bottom: 2rem;
        }

        .info h2 {
            text-align: center;
            margin-bottom: 20px;
            color: var(--primary-color);
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--text-dark);
        }

        input, select {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 8px;
            background: #f3f0ff;
            color: #333;
        }

        input:focus, select:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(111, 66, 193, 0.1);
        }

        button {
            width: 100%;
            padding: 12px;
            background: var(--gradient-purple);
            color: var(--white);
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
        }

        button:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .signature-container {
            margin-top: 2rem;
        }

        .signature-pad {
            border: 2px solid var(--primary-color);
            border-radius: 12px;
            width: 100%;
            height: 200px;
            background-color: var(--white);
            box-shadow: var(--shadow-sm);
            margin-bottom: 1rem;
        }

        .signature-buttons {
            display: flex;
            gap: 1rem;
            margin-top: 1rem;
        }

        .clear-btn {
            background: #f5365c;
        }

        .download-btn {
            background: #11cdef;
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

        .footer {
            background: var(--gradient-purple);
            color: var(--white);
            padding: 4rem 0 2rem;
            margin-top: auto;
            width: 100%;
        }

        .footer-container {
            display: flex;
            justify-content: space-between;
            width: 100%;
            margin: 0 auto;
            padding: 0 3rem;
            flex-wrap: wrap;
            gap: 2rem;
        }

        .footer-about {
            max-width: 300px;
        }

        .footer-about p {
            margin-top: 1rem;
        }

        .social-links {
            margin-top: 1rem;
            display: flex;
            gap: 1rem;
        }

        .social-link {
            color: var(--white);
            text-decoration: none;
            transition: opacity 0.3s ease;
        }

        .social-link:hover {
            opacity: 0.8;
        }

        .footer-links {
            min-width: 200px;
        }

        .footer-links h4 {
            margin-bottom: 1rem;
            font-size: 1.1rem;
        }

        .footer-links ul {
            list-style: none;
            padding: 0;
        }

        .footer-links li {
            margin: 0.5rem 0;
        }

        .footer-links a {
            color: var(--white);
            text-decoration: none;
            transition: opacity 0.3s ease;
        }

        .footer-links a:hover {
            opacity: 0.8;
        }

        .copyright {
            text-align: center;
            margin-top: 3rem;
            padding-top: 2rem;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            font-size: 0.9rem;
        }

        @media (max-width: 768px) {
            header {
                padding: 0.5rem 0;
            }
            
            header nav ul {
                flex-direction: column;
                align-items: center;
            }
            
            header nav ul li {
                margin: 5px 0;
            }

            .container {
                margin: 150px auto 60px;
                padding: 20px;
            }

            .footer-container {
                flex-direction: column;
                align-items: center;
                text-align: center;
            }
            
            .footer-links {
                text-align: center;
            }
        }
    </style>
    <style>
              .info {
            background: #fff;
            padding: 30px 40px;
            border-radius: 15px;
            box-shadow: 0 4px 12px rgba(75, 0, 130, 0.2);
        }
        .info h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #4b0082;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #4b0082;
        }
        input, select {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 8px;
            background: #f3f0ff;
        }
        select {
            background: #f3f0ff;
        }
        button {
            width: 100%;
            padding: 12px;
            background-color: #800080;
            color: white;
            font-size: 16px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        button:hover {
            background-color: #a020f0;
        }
        #message-container {
    transition: opacity 0.5s ease-in-out;
    opacity: 0;
}

#message-container div {
    opacity: 1;
    padding: 10px;
    border-radius: 5px;
    margin-bottom: 10px;
}

.success-message {
    color: green;
}

.error-message {
    color: red;
}

#message-container.show {
    opacity: 1;
}

.message-container {
            max-width: 600px;
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
                    <?php if ($_SESSION['user_type'] === 'innovateur'): ?>
                        <!-- Innovateur-specific navigation -->
                        <li style="margin: 0 15px;"><a href="ContratInnovateur.php" style="color: white; text-decoration: none; font-weight: 500; text-decoration: underline;">My Contracts</a></li>
                        <li style="margin: 0 15px;"><a href="InnovateurProjet.php" style="color: white; text-decoration: none; font-weight: 500;">My Projects</a></li>
                        <li style="margin: 0 15px;"><a href="InnovateurQuiz.html" style="color: white; text-decoration: none; font-weight: 500;">Innovator Quiz</a></li>
                    <?php endif; ?>
                    <li style="margin: 0 15px;"><a href="logout.php" style="color: white; text-decoration: none; font-weight: 500;">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <style>
        /* Adjust main content to account for fixed header */
        .container {
            margin-top: 120px !important;
        }
        
        @media (max-width: 768px) {
            header div {
                flex-direction: column;
            }
            
            nav ul {
                flex-direction: column;
                align-items: center;
            }
            
            nav li {
                margin: 10px 0 !important;
            }
        }
    </style>

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
        Connecter les innovateurs avec les ressources dont ils ont besoin
        pour transformer leurs idées en réalité
      </p>
      <div id="message-container" style="margin-top: 20px;"></div> 

      <form action="" method="POST" onsubmit="return prepareSignature()">
        <div class="info">
          <h2>Informations du contrat</h2>
          <label>Nom de l'Innovateur :</label>
          <input type="text" name="nom_innovateur" placeholder="Entrer le nom" required>
          <label>ID de l'Innovateur :</label>
          <input type="number" name="id_innovateur" placeholder="Entrer l'ID" required>
          <label>Email de l'Innovateur :</label>
          <input type="email" name="email_innovateur" placeholder="Entrer l'email" required>
          <label>Montant Investi (€) :</label>
          <input type="number" name="montant_innovateur" placeholder="Entrer le montant" step="0.01" required>
          <label>Date de Signature :</label>
          <input type="date" name="date_signature" required>
          <label>Statut du Contrat :</label>
          <select name="statut" required>
            <option value="">-- Sélectionnez un statut --</option>
            <option value="en attente">En attente</option>
            <option value="validé">Validé</option>
            <option value="rejeté">Rejeté</option>
          </select>
          <input type="hidden" id="innovateur_signature" name="signature_innovateur" required>
          <div class="signature-container">
            <h2>Signature de l'Innovateur</h2>
            <canvas id="signatureCanvas" class="signature-pad"></canvas>
            <small>Signature électronique ayant valeur contractuelle</small>
            <div class="signature-buttons">
              <button class="clear-btn" type="button" id="clearSignature">Effacer la Signature</button>
              <button class="download-btn" type="submit" id="validateSignature">Valider la signature</button>
            </div>
          </div>
        </div>
      </form>
    </div>

    <footer class="footer">
        <div class="footer-container">
            <div class="footer-about">
                <a href="index.html" class="logo">InnoConnect</a>
                <p>
                    Connecter les investisseurs avec des projets innovants à fort potentiel pour transformer les idées en réussites concrètes.
                </p>
                <div class="social-links">
                    <a href="#" class="social-link">Twitter</a>
                    <a href="#" class="social-link">Facebook</a>
                    <a href="#" class="social-link">Instagram</a>
                    <a href="#" class="social-link">LinkedIn</a>
                </div>
            </div>

            <div class="footer-links">
                <h4>Liens utiles</h4>
                <ul>
                    <li><a href="index.html">Accueil</a></li>
                    <li><a href="#about">À propos</a></li>
                    <li><a href="#services">Services</a></li>
                    <li><a href="#financement">Financement</a></li>
                    <li><a href="#contact">Contact</a></li>
                </ul>
            </div>

            <div class="footer-links">
                <h4>Nos services</h4>
                <ul>
                    <li><a href="#">Conseil en innovation</a></li>
                    <li><a href="#">Accès au financement</a></li>
                    <li><a href="#">Réseau d'experts</a></li>
                    <li><a href="#">Partenariats stratégiques</a></li>
                </ul>
            </div>
        </div>

        <div class="copyright">
            <p>&copy; 2025 InnoConnect. Tous droits réservés.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
    <script>
      document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('contractForm');
        const signatureCanvas = document.getElementById('signatureCanvas');
        const signatureDataInput = document.getElementById('innovateur_signature');
        const clearButton = document.getElementById('clearSignature');
        const validateButton = document.getElementById('validateSignature');
        const ctx = signatureCanvas.getContext('2d');
        
        // Set canvas dimensions to match container
        signatureCanvas.width = signatureCanvas.offsetWidth;
        signatureCanvas.height = signatureCanvas.offsetHeight;
        
        // Initialize drawing state
        let isDrawing = false;
        let lastX = 0;
        let lastY = 0;
        
        // Set up drawing styles
        ctx.lineWidth = 2;
        ctx.lineCap = 'round';
        ctx.lineJoin = 'round';
        ctx.strokeStyle = '#000000'; // Use black color for signature
        
        // Start drawing
        function startDrawing(e) {
          isDrawing = true;
          [lastX, lastY] = [
            e.offsetX || e.touches[0].clientX - signatureCanvas.getBoundingClientRect().left,
            e.offsetY || e.touches[0].clientY - signatureCanvas.getBoundingClientRect().top
          ];
        }
        
        // Draw
        function draw(e) {
          if (!isDrawing) return;
          
          // Prevent scrolling on touch devices
          e.preventDefault();
          
          const x = e.offsetX || e.touches[0].clientX - signatureCanvas.getBoundingClientRect().left;
          const y = e.offsetY || e.touches[0].clientY - signatureCanvas.getBoundingClientRect().top;
          
          ctx.beginPath();
          ctx.moveTo(lastX, lastY);
          ctx.lineTo(x, y);
          ctx.stroke();
          
          [lastX, lastY] = [x, y];
        }
        
        // Stop drawing
        function stopDrawing() {
          isDrawing = false;
        }
        
        // Clear the signature
        function clearSignature() {
          ctx.clearRect(0, 0, signatureCanvas.width, signatureCanvas.height);
          signatureDataInput.value = '';
        }
        
        // Save the signature
        function saveSignature() {
          signatureDataInput.value = signatureCanvas.toDataURL();
          validateButton.classList.add('btn-success');
          validateButton.textContent = 'Signature validée ✓';
          setTimeout(() => {
            validateButton.classList.remove('btn-success');
            validateButton.textContent = 'Valider la signature';
          }, 2000);
        }
        
        // Setup event listeners for mouse
        signatureCanvas.addEventListener('mousedown', startDrawing);
        signatureCanvas.addEventListener('mousemove', draw);
        signatureCanvas.addEventListener('mouseup', stopDrawing);
        signatureCanvas.addEventListener('mouseout', stopDrawing);
        
        // Setup event listeners for touch
        signatureCanvas.addEventListener('touchstart', startDrawing);
        signatureCanvas.addEventListener('touchmove', draw);
        signatureCanvas.addEventListener('touchend', stopDrawing);
        signatureCanvas.addEventListener('touchcancel', stopDrawing);
        
        // Clear and save buttons
        clearButton.addEventListener('click', clearSignature);
        validateButton.addEventListener('click', saveSignature);
        
        // Fonction pour vérifier si le canvas est vide
        function isCanvasBlank() {
          const pixelBuffer = new Uint32Array(
            ctx.getImageData(0, 0, signatureCanvas.width, signatureCanvas.height).data.buffer
          );
          return !pixelBuffer.some(color => color !== 0);
        }
        
        // Form validation
        form.addEventListener('submit', function(event) {
          let isValid = true;
          let errorMessages = [];

          // Vérifier le nom
          const nom = form.elements['nom_innovateur'].value.trim();
          if (nom.length < 2 || !/^[A-Za-zÀ-ÿ\s\-']+$/.test(nom)) {
            isValid = false;
            errorMessages.push("Le nom de l'innovateur est invalide.");
          }

          // Vérifier l'ID
          const id = form.elements['id_innovateur'].value.trim();
          if (id === "" || isNaN(id) || id <= 0 || id > 999999) {
            isValid = false;
            errorMessages.push("L'ID de l'innovateur doit être un nombre positif inférieur à 999999.");
          }

          // Vérifier l'email
          const email = form.elements['email_innovateur'].value.trim();
          if (email === "" || !email.includes('@')) {
            isValid = false;
            errorMessages.push("L'email de l'innovateur est invalide.");
          }

          // Vérifier le montant
          const montant = form.elements['montant_innovateur'].value.trim();
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
          if (isCanvasBlank()) {
            isValid = false;
            errorMessages.push("Veuillez signer avant de valider le formulaire.");
          }
          
          if (!isValid) {
            event.preventDefault();
            alert(errorMessages.join("\n"));
          }
        });
      });
    </script>
  </body>
</html>
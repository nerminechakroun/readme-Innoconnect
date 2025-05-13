<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../Controller/utilisateurC.php';
session_start();

// Check if the user is logged in
if (!isset($_SESSION['id_utilisateur'])) {
    header("Location: login.php");
    exit;
}

// Check if the user is an investor
if ($_SESSION['user_type'] !== 'investisseur') {
    header("Location: profile.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>InnoConnect - Plateforme Investisseurs</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
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

        /* Main section styles */
        .main {
            padding: 2rem 1rem;
            flex-grow: 1;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        /* Styles du simulateur */
        #simulateur-financement {
            max-width: 600px;
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            text-align: center;
            margin: 2rem auto;
        }

        #simulateur-financement h2 {
            color: #6f42c1;
            margin-bottom: 30px;
        }

        #simulateur-financement input {
            width: 100%;
            padding: 12px 15px;
            border-radius: 8px;
            border: 1px solid #ccc;
            font-size: 1rem;
            margin-bottom: 20px;
            color: #333;
        }

        #simulateur-financement button {
            width: 100%;
            background-color: #6f42c1;
            color: white;
            padding: 12px;
            font-size: 1rem;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.3s ease;
        }

        #simulateur-financement button:hover {
            opacity: 0.9;
        }

        #resultat {
            margin-top: 20px;
            padding: 15px;
            border-radius: 8px;
            font-size: 1.1rem;
            text-align: center;
            display: none;
        }

        /* Nouveaux styles pour les filtres */
        .filter-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-bottom: 3rem;
        }

        .filter-box {
            background: var(--white);
            padding: 1.5rem;
            border-radius: 1rem;
            border: 2px solid var(--primary-color);
            box-shadow: var(--shadow-md);
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }

        .filter-box::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: var(--gradient-purple);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .filter-box:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(111, 66, 193, 0.15);
        }

        .filter-box:hover::before {
            opacity: 1;
        }

        .filter-title {
            font-size: 1.1rem;
            margin-bottom: 1.2rem;
            color: var(--primary-color);
            position: relative;
            padding-bottom: 0.5rem;
        }

        .filter-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 40px;
            height: 2px;
            background: var(--gradient-purple);
        }

        select {
            width: 100%;
            padding: 0.8rem 1rem;
            border-radius: 0.7rem;
            border: 1px solid #e0e0e0;
            background: var(--white);
            font-size: 0.95rem;
            color: var(--text-dark);
            appearance: none;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%236f42c1' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 1rem center;
            background-size: 1em;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        select:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(111, 66, 193, 0.1);
        }

        /* Section des statistiques */
        .numbers-section {
            background: var(--white);
            padding: 2rem;
            margin-top: 4rem;
            box-shadow: var(--shadow-sm);
        }

        .metrics {
            display: flex;
            justify-content: space-between;
            gap: 2rem;
        }

        .metric-card {
            background: var(--gradient-purple);
            padding: 2rem;
            border-radius: 0.75rem;
            text-align: center;
            color: var(--white);
            box-shadow: var(--shadow-md);
            flex: 1;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .metric-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .metric-value {
            font-size: 2.5rem;
            font-weight: bold;
        }

        .metric-label {
            font-size: 1.125rem;
            margin-top: 0.75rem;
            font-weight: 500;
        }

        /* Footer */
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
            .filter-container {
                grid-template-columns: 1fr;
            }
            
            .metrics {
                flex-direction: column;
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

        /* Styles pour le chatbot */
        .chatbot-button {
            position: fixed;
            bottom: 20px;
            right: 20px;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: var(--gradient-purple);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            transition: transform 0.3s ease;
        }

        .chatbot-button:hover {
            transform: scale(1.1);
        }

        .chatbot-icon {
            color: white;
            font-size: 24px;
        }

        .chatbot-window {
            position: fixed;
            bottom: 90px;
            right: 20px;
            width: 350px;
            height: 500px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 25px rgba(0, 0, 0, 0.15);
            display: none;
            flex-direction: column;
            z-index: 1000;
            overflow: hidden;
        }

        .chatbot-header {
            background: var(--gradient-purple);
            color: white;
            padding: 15px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .chatbot-messages {
            flex-grow: 1;
            padding: 15px;
            overflow-y: auto;
        }

        .message {
            margin-bottom: 10px;
            max-width: 80%;
            padding: 10px 15px;
            border-radius: 15px;
            line-height: 1.4;
        }

        .bot-message {
            background: #f0f2f5;
            margin-right: auto;
            border-bottom-left-radius: 5px;
        }

        .user-message {
            background: var(--primary-color);
            color: white;
            margin-left: auto;
            border-bottom-right-radius: 5px;
        }

        .chatbot-input {
            padding: 15px;
            border-top: 1px solid #eee;
            display: flex;
            gap: 10px;
        }

        .chatbot-input input {
            flex-grow: 1;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 20px;
            outline: none;
            color: #333;
        }

        .chatbot-input button {
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 20px;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .chatbot-input button:hover {
            background: var(--secondary-color);
        }

        /* Profile section */
        .profile-section {
            background-color: var(--white);
            border-radius: 15px;
            padding: 30px;
            margin-top: 20px;
            box-shadow: var(--shadow-md);
        }

        /* Adjust main content to account for fixed header */
        main {
            padding-top: 100px !important;
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
</head>
<body>
    <div class="loader" id="loader"></div>
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
                    <li style="margin: 0 15px;"><a href="ContratInvestisseur.php" style="color: white; text-decoration: none; font-weight: 500;">My Contracts</a></li>
                    <li style="margin: 0 15px;"><a href="Investisseur.php" style="color: white; text-decoration: none; font-weight: 500; text-decoration: underline;">My Investments</a></li>
                    <li style="margin: 0 15px;"><a href="investisseurProjet.php" style="color: white; text-decoration: none; font-weight: 500;">Available Projects</a></li>
                    <li style="margin: 0 15px;"><a href="logout.php" style="color: white; text-decoration: none; font-weight: 500;">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main>
        <section class="profile-section">
            <div class="container">
                <section id="simulateur-financement">
                   
                    <h2>investors welcome</h2>
                    <h2>Simulez un financement</h2>
                    <div>
                        <label for="montant" style="display:block; margin-bottom: 10px; font-weight: bold;">Entrez un montant :</label>
                        <div style="display: flex; gap: 10px; margin-bottom: 15px;">
                            <input type="number" id="montant" placeholder="Ex: 250" style="flex: 2; color: #333;">
                            <select id="devise" style="flex: 1; min-width: 100px; color: #333;" onchange="convertirMontant()">
                                <option value="EUR">EUR (€)</option>
                                <option value="TND">TND (د.ت)</option>
                                <option value="USD">USD ($)</option>
                                <option value="GBP">GBP (£)</option>
                                <option value="JPY">JPY (¥)</option>
                                <option value="CHF">CHF</option>
                                <option value="CAD">CAD</option>
                            </select>
                        </div>
                        <div id="montantConverti" style="margin-bottom: 15px; color: var(--primary-color); font-weight: bold;"></div>
                        <button onclick="afficherCategorie()" class="btn-primary">Vérifier la catégorie</button>
                        <div id="resultat"></div>
                    </div>
                </section>

                <section class="hero-banner">
                    <h1 class="hero-title">Investissez dans des projets innovants à fort potentiel</h1>
                    <p class="hero-text">
                        En tant qu'<strong>investisseur</strong>, découvrez des opportunités
                        soigneusement sélectionnées et alignées avec vos critères.
                    </p>
                </section>

                <section class="filter-container">
                    <div class="filter-box">
                        <h3 class="filter-title">Montant</h3>
                        <select aria-label="Montant à investir">
                            <option value="">Sélectionner un montant</option>
                            <option value="200">Moins de 200€</option>
                            <option value="500">De 200€ à 500€</option>
                            <option value="500plus">Plus de 500€</option>
                        </select>
                    </div>
                    <div class="filter-box">
                        <h3 class="filter-title">Secteur</h3>
                        <select aria-label="Secteur d'activité">
                            <option value="">Secteur</option>
                            <option value="greentech">GreenTech</option>
                            <option value="sante">Santé</option>
                            <option value="ia">Intelligence Artificielle</option>
                        </select>
                    </div>
                    <div class="filter-box">
                        <h3 class="filter-title">Type de Financement</h3>
                        <select aria-label="Type d'investissement">
                            <option value="">Type d'investissement</option>
                            <option value="equity">Equity</option>
                            <option value="pret">Prêt</option>
                            <option value="dons">Dons avec contreparties</option>
                        </select>
                    </div>
                </section>

                <div class="numbers-section">
                    <section class="metrics">
                        <div class="metric-card">
                            <div class="metric-value">127M€</div>
                            <div class="metric-label">Levés sur la plateforme</div>
                        </div>
                        <div class="metric-card">
                            <div class="metric-value">89%</div>
                            <div class="metric-label">Taux de réussite</div>
                        </div>
                        <div class="metric-card">
                            <div class="metric-value">420+</div>
                            <div class="metric-label">Projets financés</div>
                        </div>
                    </section>
                </div>
            </div>
        </section>
    </main>

    <footer class="footer">
        <div class="footer-container">
            <div class="footer-about">
                <a href="../../index.html" class="logo">InnoConnect</a>
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
                    <li><a href="../../index.html">Accueil</a></li>
                    <li><a href="profile.php">Mon Profil</a></li>
                    <li><a href="Investisseur.php">Mes Investissements</a></li>
                    <li><a href="ContratInvestisseur.php">Mes Contrats</a></li>
                    <li><a href="investisseurProjet.php">Projets Disponibles</a></li>
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

    <!-- Chatbot Button -->
    <div class="chatbot-button" onclick="toggleChatbot()">
        <i class="fas fa-comments chatbot-icon"></i>
    </div>

    <!-- Chatbot Window -->
    <div class="chatbot-window" id="chatbot">
        <div class="chatbot-header">
            <h3>Assistant InnoConnect</h3>
            <button onclick="toggleChatbot()" style="background: none; border: none; color: white; cursor: pointer;">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="chatbot-messages" id="chatMessages">
            <div class="message bot-message">
                Bonjour ! Je suis votre assistant InnoConnect. Comment puis-je vous aider avec vos investissements aujourd'hui ?
            </div>
        </div>
        <div class="chatbot-input">
            <input type="text" id="userInput" placeholder="Tapez votre message..." onkeypress="handleKeyPress(event)">
            <button onclick="sendMessage()">
                <i class="fas fa-paper-plane"></i>
            </button>
        </div>
    </div>

    <script>
        let tauxChange = {};
        
        // Loader functionality
        function showLoader() {
            document.getElementById('loader').style.display = 'block';
        }

        function hideLoader() {
            document.getElementById('loader').style.display = 'none';
        }

        document.addEventListener('DOMContentLoaded', function() {
            hideLoader();
            // Make sure chatbot is hidden by default
            document.getElementById('chatbot').style.display = 'none';
        });
        
        // Fonction pour charger les taux de change
        async function chargerTauxChange() {
            try {
                const response = await fetch('https://api.exchangerate-api.com/v4/latest/EUR');
                const data = await response.json();
                tauxChange = data.rates;
                console.log('Taux de change chargés:', tauxChange);
            } catch (error) {
                console.error('Erreur lors du chargement des taux de change:', error);
            }
        }

        // Charger les taux au démarrage
        chargerTauxChange();

        // Fonction pour convertir le montant
        async function convertirMontant() {
            const montant = parseFloat(document.getElementById("montant").value);
            const deviseSelectionnee = document.getElementById("devise").value;
            const montantConvertiElement = document.getElementById("montantConverti");

            if (isNaN(montant) || montant <= 0) {
                montantConvertiElement.textContent = "";
                return;
            }

            try {
                if (deviseSelectionnee === "EUR") {
                    montantConvertiElement.textContent = "";
                    return;
                }

                if (!tauxChange[deviseSelectionnee]) {
                    await chargerTauxChange();
                }

                const taux = tauxChange[deviseSelectionnee];
                const montantEUR = montant / taux;
                
                montantConvertiElement.textContent = `${montant} ${deviseSelectionnee} = ${montantEUR.toFixed(2)} EUR`;
            } catch (error) {
                console.error('Erreur lors de la conversion:', error);
                montantConvertiElement.textContent = "Erreur de conversion";
            }
        }

        // Ajouter l'événement input pour la conversion en temps réel
        document.getElementById("montant").addEventListener("input", convertirMontant);

        function afficherCategorie() {
            const montant = parseFloat(document.getElementById("montant").value);
            const devise = document.getElementById("devise").value;
            const resultat = document.getElementById("resultat");

            if (isNaN(montant) || montant <= 0) {
                resultat.style.display = "block";
                resultat.style.backgroundColor = "#ffcdd2";
                resultat.style.color = "#c62828";
                resultat.innerText = "Veuillez entrer un montant valide supérieur à 0.";
                return;
            }

            let montantEUR = montant;
            if (devise !== "EUR") {
                montantEUR = montant / tauxChange[devise];
            }

            let message = "";
            let bgColor = "";
            
            if (montantEUR < 200) {
                message = "Catégorie : Financement inférieur à 200€";
                bgColor = "#28a745";
            } else if (montantEUR <= 500) {
                message = "Catégorie : Financement entre 200€ et 500€";
                bgColor = "#fd7e14";
            } else {
                message = "Catégorie : Financement supérieur à 500€";
                bgColor = "#6f42c1";
            }

            resultat.style.display = "block";
            resultat.style.backgroundColor = bgColor;
            resultat.style.color = "white";
            resultat.innerText = message;
        }

        // Chatbot functions
        function toggleChatbot() {
            const chatbot = document.getElementById('chatbot');
            chatbot.style.display = chatbot.style.display === 'none' || chatbot.style.display === '' ? 'flex' : 'none';
        }

        function handleKeyPress(event) {
            if (event.key === 'Enter') {
                sendMessage();
            }
        }

        function sendMessage() {
            const input = document.getElementById('userInput');
            const message = input.value.trim();
            
            if (message === '') return;
            
            // Ajouter le message de l'utilisateur
            addMessage(message, 'user-message');
            
            // Réinitialiser l'input
            input.value = '';
            
            // Simuler la réponse du bot
            setTimeout(() => {
                const response = getBotResponse(message);
                addMessage(response, 'bot-message');
            }, 500);
        }

        function addMessage(text, className) {
            const messages = document.getElementById('chatMessages');
            const messageDiv = document.createElement('div');
            messageDiv.className = `message ${className}`;
            messageDiv.textContent = text;
            messages.appendChild(messageDiv);
            
            // Scroll to bottom
            messages.scrollTop = messages.scrollHeight;
        }

        function getBotResponse(message) {
            message = message.toLowerCase();
            
            if (message.includes('bonjour') || message.includes('salut') || message.includes('hello')) {
                return "Bonjour ! Comment puis-je vous aider aujourd'hui ?";
            } else if (message.includes('projet') || message.includes('investir')) {
                return "Nous avons plusieurs projets qui pourraient vous intéresser. Consultez la page 'Available Projects' pour voir les opportunités du moment.";
            } else if (message.includes('contrat') || message.includes('signature')) {
                return "Pour signer un contrat, rendez-vous sur la page 'My Contracts'. Vous pourrez y compléter les informations nécessaires et signer électroniquement.";
            } else if (message.includes('montant') || message.includes('investissement') || message.includes('somme')) {
                return "Nous recommandons un investissement minimum de 200€. Utilisez notre simulateur pour estimer votre catégorie d'investissement.";
            } else if (message.includes('merci') || message.includes('thanks')) {
                return "Je vous en prie ! N'hésitez pas si vous avez d'autres questions.";
            } else {
                return "Je ne suis pas sûr de comprendre votre demande. Pouvez-vous reformuler ou préciser ce dont vous avez besoin concernant vos investissements ?";
            }
        }
    </script>
</body>
</html>
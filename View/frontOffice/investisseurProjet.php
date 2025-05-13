<?php
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
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>InnoConnect - Plateforme Investisseurs</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="../../styles.css" rel="stylesheet">
    <style>
        /* Override any background colors */
        html, body {
            background-color: #ffffff !important;
            background: #ffffff !important;
            background-image: none !important;
        }
        
        /* Variables CSS */
        :root {
            --primary-color: #6f42c1;
            --secondary-color: #6610f2;
            --accent-blue: #094f88;
            --text-dark: #2c3e50;
            --white: #ffffff;
            --gradient-purple: linear-gradient(135deg, #6f42c1, #6610f2);
            --shadow-sm: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        /* Reset */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Roboto', sans-serif;
            color: var(--text-dark);
            line-height: 1.6;
        }

        /* Contenu principal */
        .main {
            padding-top: 100px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            background-color: var(--white);
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        /* Bannière Hero */
        .hero-banner {
            background: #e3f2fd;
            padding: 3rem 2rem;
            border-radius: 1rem;
            text-align: center;
            margin-bottom: 2.5rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }

        .hero-title {
            font-size: 2.5rem;
            margin-bottom: 1.25rem;
            color: var(--text-dark);
        }

        .hero-text {
            font-size: 1.125rem;
            color: var(--accent-blue);
            max-width: 800px;
            margin: 0 auto 2rem;
        }

        .cta-button {
            display: inline-block;
            background: var(--gradient-purple);
            color: var(--white);
            padding: 12px 30px;
            border-radius: 50px;
            font-weight: bold;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .cta-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(74, 108, 247, 0.3);
        }

        /* Grille des projets */
        .options-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 25px;
            margin-top: 30px;
        }

        .option-card {
            background: var(--white);
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            border: 1px solid #eee;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .option-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        .option-card h3 {
            font-size: 1.5rem;
            margin-bottom: 15px;
            color: var(--text-dark);
        }

        .option-card p {
            color: #6c757d;
            margin-bottom: 20px;
        }

        /* Barre de progression */
        .progress-container {
            margin: 15px 0;
        }

        .progress-bar {
            height: 6px;
            background: #eee;
            border-radius: 3px;
            margin-bottom: 5px;
        }

        .progress-fill {
            height: 100%;
            border-radius: 3px;
        }

        .progress-info {
            display: flex;
            justify-content: space-between;
            color: #6c757d;
            font-size: 0.9rem;
        }

        .roi-badge {
            color: var(--secondary-color);
            font-weight: bold;
            margin: 15px 0;
        }

        .view-button {
            background: var(--primary-color);
            color: var(--white);
            border: none;
            padding: 8px 20px;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .view-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(74, 108, 247, 0.2);
        }

        /* Footer */
        .footer {
            background: #4e2a8e;  /* Deeper purple color matching the screenshot */
            color: var(--white);
            padding: 3rem 0 1rem;
            margin-top: 2rem;
            width: 100%;
        }

        .footer-container {
            width: 100%;
            max-width: 100%;
            margin: 0 auto;
            padding: 0 3rem;
            display: grid;
            grid-template-columns: 1.5fr 1fr 1fr 1fr;
            gap: 2rem;
        }

        .footer-about h3 {
            font-size: 1.5rem;
            margin-bottom: 1rem;
            color: white;
            font-weight: 500;
        }

        .footer-about p {
            margin: 15px 0;
            line-height: 1.6;
            color: rgba(255, 255, 255, 0.8);
            font-size: 0.9rem;
        }

        .footer h4 {
            margin-bottom: 1.2rem;
            font-size: 1.1rem;
            font-weight: 500;
            color: white;
        }

        .footer-links ul {
            list-style: none;
            padding: 0;
        }

        .footer-links li {
            margin-bottom: 0.7rem;
        }

        .footer-links a, 
        .footer-about a, 
        .social-links a {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: all 0.3s;
            font-size: 0.9rem;
        }

        .footer-links a:hover, 
        .footer-about a:hover, 
        .social-links a:hover {
            color: white;
            text-decoration: none;
        }

        .social-links {
            display: flex;
            gap: 15px;
            margin-top: 1.2rem;
        }

        .footer-contact p {
            margin-bottom: 0.8rem;
            color: rgba(255, 255, 255, 0.8);
            line-height: 1.6;
            font-size: 0.9rem;
        }

        .copyright {
            text-align: center;
            padding-top: 1.5rem;
            margin-top: 1.5rem;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            width: 100%;
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.85rem;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .options-grid {
                grid-template-columns: 1fr;
            }
            
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
        
        @media (max-width: 992px) {
            .footer-container {
                grid-template-columns: 1fr 1fr;
            }
        }
        
        @media (max-width: 576px) {
            .footer-container {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
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
                    <li style="margin: 0 15px;"><a href="Investisseur.php" style="color: white; text-decoration: none; font-weight: 500;">My Investments</a></li>
                    <li style="margin: 0 15px;"><a href="investisseurProjet.php" style="color: white; text-decoration: none; font-weight: 500; text-decoration: underline;">Available Projects</a></li>
                    <li style="margin: 0 15px;"><a href="logout.php" style="color: white; text-decoration: none; font-weight: 500;">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="main">
        <div class="container">
            <section class="hero-banner">
                <h1 class="hero-title">Investissez dans des projets innovants à fort potentiel</h1>
                <p class="hero-text">
                    En tant qu'<strong>investisseur</strong>, découvrez des opportunités
                    soigneusement sélectionnées et alignées avec vos critères.
                </p>
                <a href="#projects" class="cta-button">Voir les projets</a>
            </section>

            <!-- Grille des projets -->
            <div class="options-grid">
                <!-- Projet 1 -->
                <div class="option-card">
                    <h3>GreenTech X</h3>
                    <p>Solution de recyclage innovante pour les plastiques industriels</p>
                    <div class="progress-container">
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: 65%; background: #4caf50"></div>
                        </div>
                        <div class="progress-info">
                            <span>65% financé</span>
                            <span>150K€ / 230K€</span>
                        </div>
                    </div>
                    <div class="roi-badge">ROI estimé: 18-22%</div>
                    <button class="view-button">Voir le projet</button>
                </div>

                <!-- Projet 2 -->
                <div class="option-card">
                    <h3>HealthAI</h3>
                    <p>Plateforme de diagnostic médical assisté par IA</p>
                    <div class="progress-container">
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: 40%; background: #ff9800"></div>
                        </div>
                        <div class="progress-info">
                            <span>40% financé</span>
                            <span>400K€ / 1M€</span>
                        </div>
                    </div>
                    <div class="roi-badge">ROI estimé: 25-30%</div>
                    <button class="view-button">Voir le projet</button>
                </div>

                <!-- Projet 3 -->
                <div class="option-card">
                    <h3>AgriFuture</h3>
                    <p>Technologies d'agriculture verticale et hydroponique</p>
                    <div class="progress-container">
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: 80%; background: #2196f3"></div>
                        </div>
                        <div class="progress-info">
                            <span>80% financé</span>
                            <span>80K€ / 100K€</span>
                        </div>
                    </div>
                    <div class="roi-badge">ROI estimé: 15-20%</div>
                    <button class="view-button">Voir le projet</button>
                </div>

                <!-- Projet 4 -->
                <div class="option-card">
                    <h3>EduTech Pro</h3>
                    <p>Plateforme d'apprentissage adaptatif avec réalité augmentée</p>
                    <div class="progress-container">
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: 25%; background: #9c27b0"></div>
                        </div>
                        <div class="progress-info">
                            <span>25% financé</span>
                            <span>125K€ / 500K€</span>
                        </div>
                    </div>
                    <div class="roi-badge">ROI estimé: 30-35%</div>
                    <button class="view-button">Voir le projet</button>
                </div>

                <!-- Projet 5 -->
                <div class="option-card">
                    <h3>CleanEnergy</h3>
                    <p>Stockage d'énergie nouvelle génération</p>
                    <div class="progress-container">
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: 55%; background: #f44336"></div>
                        </div>
                        <div class="progress-info">
                            <span>55% financé</span>
                            <span>550K€ / 1M€</span>
                        </div>
                    </div>
                    <div class="roi-badge">ROI estimé: 20-25%</div>
                    <button class="view-button">Voir le projet</button>
                </div>

                <!-- Projet 6 -->
                <div class="option-card">
                    <h3>SmartLogistics</h3>
                    <p>Optimisation logistique par intelligence artificielle</p>
                    <div class="progress-container">
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: 90%; background: #4caf50"></div>
                        </div>
                        <div class="progress-info">
                            <span>90% financé</span>
                            <span>180K€ / 200K€</span>
                        </div>
                    </div>
                    <div class="roi-badge">ROI estimé: 22-27%</div>
                    <button class="view-button">Voir le projet</button>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-container">
            <div class="footer-about">
                <h3>InnoConnect</h3>
                <p>Connecter les innovateurs avec les ressources dont ils ont besoin pour transformer leurs idées en réalité.</p>
                <div class="social-links">
                    <a href="#">Twitter</a>
                    <a href="#">Facebook</a>
                    <a href="#">Instagram</a>
                    <a href="#">LinkedIn</a>
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
                    <li><a href="#">Formations</a></li>
                </ul>
            </div>

            <div class="footer-contact">
                <h4>Contact</h4>
                <p>123 Rue de l'Innovation<br>75000 Paris</p>
                <p>contact@innoconnect.com<br>+33 1 23 45 67 89</p>
            </div>
        </div>

        <div class="copyright">
            <p>© 2024 InnoConnect - Tous droits réservés</p>
        </div>
    </footer>
</body>
</html>
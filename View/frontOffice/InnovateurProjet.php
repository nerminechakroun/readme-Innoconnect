<?php
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
?>
<!DOCTYPE html>
<html lang="fr">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>InnoConnect - Financement pour Innovateurs</title>
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
        font-family: "Roboto", sans-serif;
        color: #333;
        line-height: 1.6;
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

      .main {
        margin-top: 120px;
        padding: 20px;
        min-height: calc(100vh - 140px);
      }
      .container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 20px;
      }

      .hero-banner {
        background: linear-gradient(135deg, #6e8efb, #4a6cf7);
        color: white;
        padding: 60px 40px;
        border-radius: 12px;
        text-align: center;
        margin-bottom: 40px;
        box-shadow: 0 10px 30px rgba(74, 108, 247, 0.2);
      }
      .hero-banner h1 {
        font-size: 2.5rem;
        margin-bottom: 20px;
      }
      .hero-banner p {
        font-size: 1.2rem;
        margin-bottom: 30px;
        max-width: 800px;
        margin-left: auto;
        margin-right: auto;
      }
      .cta-button {
        display: inline-block;
        background-color: white;
        color: #4a6cf7;
        padding: 12px 30px;
        border-radius: 50px;
        font-weight: bold;
        text-decoration: none;
        font-size: 1.1rem;
        transition: all 0.3s ease;
      }
      .cta-button:hover {
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
      }
      .options-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
        gap: 25px;
        margin-top: 30px;
        background: #e3f2fd;
        padding: 20px;
        border-radius: 12px;
      }
      .option-card {
        background-color: white;
        border-radius: 12px;
        padding: 30px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
      }
      .option-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
      }
      .option-icon {
        font-size: 2.5rem;
        margin-bottom: 20px;
        color: #4a6cf7;
      }
      .option-card h3 {
        font-size: 1.5rem;
        margin-bottom: 15px;
        color: #2c3e50;
      }
      .option-card p {
        color: #6c757d;
        margin-bottom: 20px;
      }
      .example-badge {
        display: inline-block;
        background-color: #e3f2fd;
        color: #1976d2;
        padding: 5px 10px;
        border-radius: 20px;
        font-size: 0.9rem;
        margin-top: 15px;
      }

      .footer {
        background: linear-gradient(135deg, #6f42c1, #6610f2);
        color: white;
        padding: 60px 0 30px;
        margin-top: 50px;
        width: 100%;
        box-sizing: border-box;
      }
      .footer-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 20px;
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 30px;
        width: 100%;
        box-sizing: border-box;
      }
      .footer-about .logo {
        font-size: 24px;
        margin-bottom: 15px;
        display: block;
        color: white;
        text-decoration: none;
      }
      .footer-about p {
        margin-bottom: 20px;
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
        color: #4a6cf7;
      }
      .copyright {
        text-align: center;
        padding-top: 30px;
        margin-top: 30px;
        border-top: 1px solid rgba(255, 255, 255, 0.1);
        color: rgba(255, 255, 255, 0.7);
        font-size: 0.9rem;
        width: 100%;
        box-sizing: border-box;
      }

      @media (max-width: 768px) {
        .hero-banner {
          padding: 40px 20px;
        }
        .hero-banner h1 {
          font-size: 2rem;
        }
        .options-grid {
          grid-template-columns: 1fr;
        }
        .footer {
          padding: 40px 0 20px;
        }
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
                    <?php if ($_SESSION['user_type'] === 'innovateur'): ?>
                        <!-- Innovateur-specific navigation -->
                        <li style="margin: 0 15px;"><a href="ContratInnovateur.php" style="color: white; text-decoration: none; font-weight: 500;">My Contracts</a></li>
                        <li style="margin: 0 15px;"><a href="InnovateurProjet.php" style="color: white; text-decoration: none; font-weight: 500; text-decoration: underline;">My Projects</a></li>
                        <li style="margin: 0 15px;"><a href="InnovateurQuiz.html" style="color: white; text-decoration: none; font-weight: 500;">Innovator Quiz</a></li>
                    <?php endif; ?>
                    <li style="margin: 0 15px;"><a href="logout.php" style="color: white; text-decoration: none; font-weight: 500;">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <!-- Main -->
    <main class="main">
      <div class="container">
        <!-- Hero Banner -->
        <section class="hero-banner">
          <h1>Innovators' Welcome</h1>
          <h1>Trouvez le financement idéal pour votre innovation</h1>
          <p>
            En tant qu'<span style="font-weight: bold; color: #051272">innovateur</span>,
            accédez à des solutions de financement adaptées à votre projet.
          </p>
          <a href="#financement-options" class="cta-button">Découvrir les options</a>
        </section>

        <!-- Financement Options -->
        <section id="financement-options">
          <h2 style="text-align:center; margin-bottom: 20px;">Explorez nos solutions de financement</h2>
          <div class="options-grid">
            <div class="option-card">
              <div class="option-icon">💡</div>
              <h3>Investir dans l'innovation</h3>
              <p>
                Découvrez comment soutenir les innovateurs avec des financements adaptés.
              </p>
              <span class="example-badge">Exemple</span>
            </div>
            <div class="option-card">
              <div class="option-icon">📈</div>
              <h3>Investissement en Equity</h3>
              <p>Vendez des parts de votre entreprise à des investisseurs en échange de capital.</p>
              <div class="example-badge">Exemple : BioTech A a levé 1M€</div>
            </div>
            <div class="option-card">
              <div class="option-icon">🌍</div>
              <h3>Crowdfunding</h3>
              <p>Mobilisez une communauté pour financer votre projet via des dons ou pré-achats.</p>
              <div class="example-badge">Exemple : SolarKit a atteint 300% de son objectif</div>
            </div>
            <div class="option-card">
              <div class="option-icon">🔄</div>
              <h3>Prêts Innovants</h3>
              <p>Accédez à des prêts avec des conditions flexibles adaptées aux startups.</p>
              <div class="example-badge">Exemple : EcoPack a remboursé 100K€</div>
            </div>
            <div class="option-card">
              <div class="option-icon">🏛️</div>
              <h3>Subventions et Aides</h3>
              <p>Financements non remboursables pour projets innovants et R&D.</p>
              <div class="example-badge">Exemple : 50K€ obtenus par CleanTech B</div>
            </div>
            <div class="option-card">
              <div class="option-icon">🤝</div>
              <h3>Business Angels</h3>
              <p>Investisseurs individuels apportant capital et expertise.</p>
              <div class="example-badge">Exemple : 200K€ + mentorat obtenus</div>
            </div>
            
        </section>
      </div>
    </main>

    </main>

    <!-- Footer -->
    <footer class="footer">
      <div class="footer-container">
        <div class="footer-about">
          <a href="#" class="logo">InnoConnect</a>
          <p>Une plateforme innovante pour connecter investisseurs et innovateurs.</p>
        </div>
        <div class="footer-links">
          <h4>Nos Services</h4>
          <ul>
            <li><a href="#">Investissement</a></li>
            <li><a href="#">Financement</a></li>
            <li><a href="#">Contract</a></li>
          </ul>
        </div>
        <div class="social-links">
          <a href="#" target="_blank" class="social-link">FB</a>
          <a href="#" target="_blank" class="social-link">TW</a>
          <a href="#" target="_blank" class="social-link">IN</a>
        </div>
      </div>
      <div class="copyright">© 2025 InnoConnect. Tous droits réservés.</div>
    </footer>
  </body>
</html>

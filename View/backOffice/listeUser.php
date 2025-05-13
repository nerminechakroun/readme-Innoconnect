<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../Controller/utilisateurC.php';

// Check if TCPDF exists before trying to include it
$tcpdf_available = false;
try {
    if (file_exists(__DIR__ . '/../../tcpdf/tcpdf.php')) {
        require_once __DIR__ . '/../../tcpdf/tcpdf.php';
        $tcpdf_available = true;
    } else if (file_exists(__DIR__ . '/../../vendor/tecnickcom/tcpdf/tcpdf.php')) {
        require_once __DIR__ . '/../../vendor/tecnickcom/tcpdf/tcpdf.php';
        $tcpdf_available = true;
    }
} catch (Exception $e) {
    // TCPDF not available
    $tcpdf_available = false;
}

session_start();

// Check if the user is logged in
if (!isset($_SESSION['id_utilisateur'])) {
    header("Location: ../frontOffice/login.php");
    exit;
}

// Check user type
$conn = config::getConnexion();
$userId = $_SESSION['id_utilisateur'];
$userC = new userC();
$userType = $userC->getUserType($userId);

if ($userType !== 'administrateur') {
    header("Location: ../frontOffice/login.php");
    exit;
}

// Paramètres de tri
$sortColumn = isset($_GET['sort']) ? $_GET['sort'] : 'id_utilisateur'; // Colonne par défaut
$sortOrder = isset($_GET['order']) && $_GET['order'] === 'desc' ? 'DESC' : 'ASC'; // Ordre par défaut
$nextOrder = $sortOrder === 'ASC' ? 'desc' : 'asc'; // Pour alterner l'ordre lors du clic

// Liste des colonnes autorisées pour le tri (sécurité)
$allowedColumns = ['id_utilisateur', 'nom', 'prenom', 'email', 'type', 'date_inscription'];
if (!in_array($sortColumn, $allowedColumns)) {
    $sortColumn = 'id_utilisateur'; // Valeur par défaut si la colonne n'est pas autorisée
}

// Handle search query
$searchTerm = isset($_GET['search']) ? trim($_GET['search']) : '';
if ($searchTerm) {
    $utilisateurs = $userC->searchUsers($searchTerm, $sortColumn, $sortOrder);
} else {
    $utilisateurs = $userC->afficherUser($sortColumn, $sortOrder);
}

$stats = $userC->getStats();
$chartData = $userC->getChartData();
$growth = $userC->getGrowthData();
$growthData = $growth['growthData'];
$labels = $growth['labels'];

$user = $userC->getUserById($userId);

// Handle PDF export
if (isset($_GET['export']) && $_GET['export'] === 'pdf') {
    if (!$tcpdf_available) {
        header("Location: listeUser.php?error=TCPDF library is not available. Please install TCPDF to use PDF export feature.");
        exit;
    }
    
    // Create new PDF document
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

    // Set document information
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('InnoConnect');
    $pdf->SetTitle('User List');
    $pdf->SetSubject('User List Export');
    $pdf->SetKeywords('Users, InnoConnect, Export');

    // Set default header data
    $pdf->SetHeaderData('', 0, 'InnoConnect User List', 'Generated on ' . date('Y-m-d H:i:s'));

    // Set header and footer fonts
    $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
    $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

    // Set default monospaced font
    $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

    // Set margins
    $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
    $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
    $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

    // Set auto page breaks
    $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

    // Set image scale factor

    // Add a page
    $pdf->AddPage();

    // Set font
    $pdf->SetFont('helvetica', '', 10);

    // Create HTML content for the table
    $html = '<h1>User List</h1>';
    $html .= '<table border="1" cellpadding="4" cellspacing="0">';
    $html .= '<thead>';
    $html .= '<tr style="background-color:#f8f9fa;">';
    $html .= '<th><b>ID</b></th>';
    $html .= '<th><b>Last Name</b></th>';
    $html .= '<th><b>First Name</b></th>';
    $html .= '<th><b>Email</b></th>';
    $html .= '<th><b>Type</b></th>';
    $html .= '<th><b>Register Date</b></th>';
    $html .= '</tr>';
    $html .= '</thead>';
    $html .= '<tbody>';

    foreach ($utilisateurs as $utilisateur) {
      


        $html .= '<tr>';
        $html .= '<td>' . htmlspecialchars($utilisateur['id_utilisateur']) . '</td>';
        $html .= '<td>' . htmlspecialchars($utilisateur['nom']) . '</td>';
        $html .= '<td>' . htmlspecialchars($utilisateur['prenom']) . '</td>';
        $html .= '<td>' . htmlspecialchars($utilisateur['email']) . '</td>';
        $html .= '<td>' . htmlspecialchars($utilisateur['type']) . '</td>';
        $html .= '<td>' . htmlspecialchars($utilisateur['date_inscription']) . '</td>';
        $html .= '</tr>';
    }

    $html .= '</tbody>';
    $html .= '</table>';

    // Output the HTML content
    $pdf->writeHTML($html, true, false, true, false, '');

    // Close and output PDF document
    $pdf->Output('user_list_' . date('Ymd_His') . '.pdf', 'D');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="apple-touch-icon" sizes="76x76" href="../assets2/img/apple-icon.png">
    <link rel="icon" type="image/png" href="../assets2/img/favicon.png">
    <title>Back-Office InnoConnect - User Management</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet" />
    
    <link href="https://demos.creative-tim.com/argon-dashboard-pro/assets/css/nucleo-icons.css" rel="stylesheet" />
    <link href="https://demos.creative-tim.com/argon-dashboard-pro/assets/css/nucleo-svg.css" rel="stylesheet" />
    
    <!-- Updated Font Awesome CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    
    <link id="pagestyle" href="../../assets2/css/argon-dashboard.css" rel="stylesheet" />
    
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
    .card {
        min-height: 150px; 
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }
    .card-body {
        flex-grow: 1;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }
    .card .numbers p {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    /* Pop-up Notification Styles */
    .notification {
        position: fixed;
        top: 20px;
        right: 20px;
        background-color: #28a745;
        color: white;
        padding: 15px 20px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        z-index: 10000;
        display: none;
        font-size: 1em;
        font-weight: 500;
        opacity: 0;
        transition: opacity 0.5s ease-in-out;
    }
    .notification.show {
        display: block;
        opacity: 1;
    }
    .notification.hide {
        opacity: 0;
    }
    /* Highlight for specific columns */
    .highlight-column {
        background-color: #ffeb3b !important; /* Yellow highlight for the specific column */
        transition: background-color 0.5s ease;
    }
    /* Style pour les icônes de tri */
    th a {
        color: #6c757d;
        text-decoration: none;
    }
    th a:hover {
        color: #007bff;
    }
    th i.fas {
        font-size: 0.8em;
        vertical-align: middle;
    }
    /* Style pour la colonne Profile Photo */
    .photo-column {
        min-width: 100px; /* Assure que la colonne a une largeur minimale */
    }
    .photo-column img {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        object-fit: cover;
        border: 1px solid #ddd;
        display: block; /* Assure que l'image est bien visible */
    }
    .photo-column span {
        color: #000; /* Texte plus sombre pour être sûr qu'il est visible */
        font-size: 0.9em;
        font-style: italic;
        display: block; /* Assure que le texte prend toute la place */
    }
    .photo-column .missing-photo {
        color: #dc3545; /* Rouge pour indiquer une erreur */
        font-size: 0.9em;
        font-style: italic;
        display: block;
    }
    /* Style pour le bouton Export to PDF */
    .btn-secondary {
        background-color: #28a745;
        color: #fff;
    }
    .btn-secondary:hover {
        background-color: #218838;
    }
    .actions-column {
        min-width: 200px; /* Assure que la colonne est assez large pour les actions */
        text-align: center;
    }
    .actions-column a {
        display: inline-block; /* Permet de contrôler la taille et l'espacement */
        margin: 5px 3px; /* Espacement entre les liens */
        padding: 5px 8px; /* Padding pour un meilleur aspect */
        font-size: 0.85em; /* Taille de police plus petite pour une meilleure lisibilité */
        color: #6c757d; /* Couleur des liens (gris par défaut) */
        text-decoration: none; /* Supprime le soulignement */
        border: 1px solid #ddd; /* Bordure subtile autour des liens */
        border-radius: 4px; /* Coins arrondis */
        transition: background-color 0.3s, color 0.3s; /* Animation pour le hover */
    }
    .actions-column a:hover {
        background-color: #f8f9fa; /* Fond clair au survol */
        color: #007bff; /* Couleur bleue au survol */
    }
    .actions-column a i {
        margin-right: 3px; /* Espacement entre l'icône et le texte */
    }

    /* Style pour les colonnes du tableau */
    .table th, .table td {
        vertical-align: middle; /* Alignement vertical au centre */
        text-align: center; /* Alignement horizontal au centre */
    }
    .table th {
        font-size: 0.9em; /* Taille de police pour les en-têtes */
    }
    .table td {
        font-size: 0.85em; /* Taille de police pour les cellules */
    }
    </style>
</head>

<body class="g-sidenav-show bg-gray-100">
    <div class="min-height-300 bg-dark position-absolute w-100"></div>
    <aside class="sidenav bg-white navbar navbar-vertical navbar-expand-xs border-0 border-radius-xl my-3 fixed-start ms-4" id="sidenav-main">
        <div class="sidenav-header">
            <i class="fas fa-times p-3 cursor-pointer text-secondary opacity-5 position-absolute end-0 top-0 d-none d-xl-none" aria-hidden="true" id="iconSidenav"></i>
            <a class="navbar-brand m-0" href="#">
                <img src="../../innoconnect.jpeg" width="26px" height="26px" class="navbar-brand-img h-100" alt="main_logo">
                <span class="ms-1 font-weight-bold">Innoconnect</span>
            </a>
        </div>
        <hr class="horizontal dark mt-0">
        <div class="collapse navbar-collapse w-auto" id="sidenav-collapse-main">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link active" href="listeUser.php">
                        <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                            <i class="ni ni-tv-2 text-dark text-sm opacity-10"></i>
                        </div>
                        <span class="nav-link-text ms-1">Admin Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="listeUser.php">
                        <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                            <i class="ni ni-single-02 text-dark text-sm opacity-10"></i>
                        </div>
                        <span class="nav-link-text ms-1">User Management</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="ContratView.php">
                        <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                            <i class="ni ni-collection text-dark text-sm opacity-10"></i>
                        </div>
                        <span class="nav-link-text ms-1">Contracts Management</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="FinancementView.php">
                        <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                            <i class="ni ni-money-coins text-dark text-sm opacity-10"></i>
                        </div>
                        <span class="nav-link-text ms-1">Financements Management</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../frontOffice/profile.php">
                        <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                            <i class="ni ni-single-02 text-dark text-sm opacity-10"></i>
                        </div>
                        <span class="nav-link-text ms-1">My Profile</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../frontOffice/logout.php">
                        <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                            <i class="ni ni-key-25 text-dark text-sm opacity-10"></i>
                        </div>
                        <span class="nav-link-text ms-1">Logout</span>
                    </a>
                </li>
            </ul>
        </div>
    </aside>
    <main class="main-content position-relative border-radius-lg">
        <!-- Navbar -->
        <nav class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl" id="navbarBlur" style="background-color: #6f42c1;" data-scroll="false">
            <div class="container-fluid py-1 px-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb bg-transparent mb-0 pb-0 pt-1 px-0 me-sm-6 me-5">
                        <li class="breadcrumb-item text-sm"><a class="opacity-5 text-white" href="javascript:;">Pages</a></li>
                        <li class="breadcrumb-item text-sm text-white active" aria-current="page">Dashboard</li>
                    </ol>
                    <h6 class="font-weight-bolder text-white mb-0">User Management</h6>
                </nav>
                <div class="collapse navbar-collapse mt-sm-0 mt-2 me-md-0 me-sm-4" id="navbar">
                    <div class="ms-md-auto pe-md-3 d-flex align-items-center">
                        <form method="GET" action="listeUser.php" class="input-group">
                            <span class="input-group-text text-body"><i class="fas fa-search" aria-hidden="true"></i></span>
                            <input type="text" class="form-control" name="search" placeholder="Search for a user..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                            <button type="submit" class="btn btn-primary btn-sm m-0">Search</button>
                        </form>
                        <?php if ($searchTerm): ?>
                            <a href="listeUser.php" class="btn btn-secondary btn-sm m-0 ms-2">Clear</a>
                        <?php endif; ?>
                    </div>
                    <ul class="navbar-nav justify-content-end">
                        <li class="nav-item d-flex align-items-center">
                            <a href="../frontOffice/profile.php" class="nav-link text-white font-weight-bold px-0">
                                <i class="fa fa-user me-sm-1"></i>
                                <span class="d-sm-inline d-none"><?php echo htmlspecialchars($user['prenom'] . ' ' . $user['nom']); ?></span>
                            </a>
                        </li>
                        <li class="nav-item d-xl-none ps-3 d-flex align-items-center">
                            <a href="javascript:;" class="nav-link text-white p-0" id="iconNavbarSidenav">
                                <div class="sidenav-toggler-inner">
                                    <i class="sidenav-toggler-line bg-white"></i>
                                    <i class="sidenav-toggler-line bg-white"></i>
                                    <i class="sidenav-toggler-line bg-white"></i>
                                </div>
                            </a>
                        </li>
                        <li class="nav-item px-3 d-flex align-items-center">
                            <a href="javascript:;" class="nav-link text-white p-0">
                                <i class="fa fa-cog fixed-plugin-button-nav cursor-pointer"></i>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
        <!-- End Navbar -->

        <!-- Pop-up Notification -->
        <?php if (isset($_GET['success'])): ?>
            <div class="notification" id="successNotification">
                <?php echo htmlspecialchars($_GET['success']); ?>
            </div>
        <?php endif; ?>

        <div class="container-fluid py-4">
            <!-- Error Messages -->
            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($_GET['error']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="row">
                <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
                    <div class="card">
                        <div class="card-body p-3">
                            <div class="row">
                                <div class="col-8">
                                    <div class="numbers">
                                        <p class="text-sm mb-0 text-uppercase font-weight-bold">Total Users</p>
                                        <h5 class="font-weight-bolder"><?php echo $stats['total']; ?></h5>
                                        <p class="mb-0">
                                            <span class="text-success text-sm font-weight-bolder">+<?php echo rand(1, 10); ?>.<?php echo rand(0, 9); ?>%</span>
                                            since yesterday
                                        </p>
                                    </div>
                                </div>
                                <div class="col-4 text-end">
                                    <div class="icon icon-shape bg-gradient-primary shadow-primary text-center rounded-circle">
                                        <i class="ni ni-single-02 text-lg opacity-10" aria-hidden="true"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
                    <div class="card">
                        <div class="card-body p-3">
                            <div class="row">
                                <div class="col-8">
                                    <div class="numbers">
                                        <p class="text-sm mb-0 text-uppercase font-weight-bold">Investors</p>
                                        <h5 class="font-weight-bolder"><?php echo $stats['investisseurs']; ?></h5>
                                        <p class="mb-0">
                                            <span class="text-danger text-sm font-weight-bolder">-<?php echo rand(1, 10); ?>.<?php echo rand(0, 9); ?>%</span>
                                            since last week
                                        </p>
                                    </div>
                                </div>
                                <div class="col-4 text-end">
                                    <div class="icon icon-shape bg-gradient-danger shadow-danger text-center rounded-circle">
                                        <i class="ni ni-money-coins text-lg opacity-10" aria-hidden="true"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
                    <div class="card">
                        <div class="card-body p-3">
                            <div class="row">
                                <div class="col-8">
                                    <div class="numbers">
                                        <p class="text-sm mb-0 text-uppercase font-weight-bold">Innovators</p>
                                        <h5 class="font-weight-bolder"><?php echo $stats['innovateurs']; ?></h5>
                                        <p class="mb-0">
                                            <span class="text-success text-sm font-weight-bolder">+<?php echo rand(1, 10); ?>.<?php echo rand(0, 9); ?>%</span>
                                            since last week
                                        </p>
                                    </div>
                                </div>
                                <div class="col-4 text-end">
                                    <div class="icon icon-shape bg-gradient-success shadow-success text-center rounded-circle">
                                        <i class="ni ni-bulb-61 text-lg opacity-10" aria-hidden="true"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-sm-6">
                    <div class="card">
                        <div class="card-body p-3">
                            <div class="row">
                                <div class="col-8">
                                    <div class="numbers">
                                        <p class="text-sm mb-0 text-uppercase font-weight-bold">Recent Registrations (7d)</p>
                                        <h5 class="font-weight-bolder"><?php echo $stats['recent']; ?></h5>
                                        <p class="mb-0">
                                            <span class="text-danger text-sm font-weight-bolder">-<?php echo rand(1, 10); ?>.<?php echo rand(0, 9); ?>%</span>
                                            since last month
                                        </p>
                                    </div>
                                </div>
                                <div class="col-4 text-end">
                                    <div class="icon icon-shape bg-gradient-warning shadow-warning text-center rounded-circle">
                                        <i class="ni ni-chart-bar-32 text-lg opacity-10" aria-hidden="true"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts -->
            <div class="row mt-4">
                <div class="col-lg-6 mb-lg-0 mb-4">
                    <div class="card z-index-2 h-100">
                        <div class="card-header pb-0 pt-3 bg-transparent">
                            <h6 class="text-capitalize">User Type Distribution</h6>
                        </div>
                        <div class="card-body p-3">
                            <div class="chart">
                                <canvas id="userTypeChart" class="chart-canvas" height="300"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="card z-index-2 h-100">
                        <div class="card-header pb-0 pt-3 bg-transparent">
                            <h6 class="text-capitalize">Registration Growth (Last 30 Days)</h6>
                        </div>
                        <div class="card-body p-3">
                            <div class="chart">
                                <canvas id="growthChart" class="chart-canvas" height="300"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Add User Button and User Table -->
            <div class="row mt-4">
                <div class="col-lg-12 mb-lg-0 mb-4">
                    <div class="card">
                        <div class="card-header pb-0 p-3">
                            <div class="d-flex justify-content-between">
                                <h6 class="mb-2">User List<?php echo $searchTerm ? ' (Search: ' . htmlspecialchars($searchTerm) . ')' : ''; ?></h6>
                                <div>
                                    <a href="add_user.php" class="btn btn-primary btn-sm mb-0 me-2">
                                        <i class="fas fa-user-plus me-1"></i> Add a User
                                    </a>
                                    <?php if ($tcpdf_available): ?>
                                    <a href="listeUser.php?export=pdf" class="btn btn-secondary btn-sm mb-0">
                                        <i class="fas fa-file-pdf me-1"></i> Export to PDF
                                    </a>
                                    <?php else: ?>
                                    <button class="btn btn-secondary btn-sm mb-0" disabled title="TCPDF library is not installed">
                                        <i class="fas fa-file-pdf me-1"></i> Export to PDF
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <?php if (empty($utilisateurs)): ?>
                                <p class="text-center p-3">No users found.</p>
                            <?php else: ?>
                                <table class="table align-items-center">
                                    <thead>
                                        <tr>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                                <a href="?search=<?php echo urlencode($searchTerm); ?>&sort=id_utilisateur&order=<?php echo $sortColumn === 'id_utilisateur' ? $nextOrder : 'asc'; ?>" class="text-secondary">
                                                    ID
                                                    <?php if ($sortColumn === 'id_utilisateur'): ?>
                                                        <i class="fas fa-sort-<?php echo $sortOrder === 'ASC' ? 'up' : 'down'; ?> ms-1"></i>
                                                    <?php else: ?>
                                                        <i class="fas fa-sort ms-1"></i>
                                                    <?php endif; ?>
                                                </a>
                                            </th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Profile Photo</th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                                <a href="?search=<?php echo urlencode($searchTerm); ?>&sort=nom&order=<?php echo $sortColumn === 'nom' ? $nextOrder : 'asc'; ?>" class="text-secondary">
                                                    Last Name
                                                    <?php if ($sortColumn === 'nom'): ?>
                                                        <i class="fas fa-sort-<?php echo $sortOrder === 'ASC' ? 'up' : 'down'; ?> ms-1"></i>
                                                    <?php else: ?>
                                                        <i class="fas fa-sort ms-1"></i>
                                                    <?php endif; ?>
                                                </a>
                                            </th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                                <a href="?search=<?php echo urlencode($searchTerm); ?>&sort=prenom&order=<?php echo $sortColumn === 'prenom' ? $nextOrder : 'asc'; ?>" class="text-secondary">
                                                    First Name
                                                    <?php if ($sortColumn === 'prenom'): ?>
                                                        <i class="fas fa-sort-<?php echo $sortOrder === 'ASC' ? 'up' : 'down'; ?> ms-1"></i>
                                                    <?php else: ?>
                                                        <i class="fas fa-sort ms-1"></i>
                                                    <?php endif; ?>
                                                </a>
                                            </th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                                <a href="?search=<?php echo urlencode($searchTerm); ?>&sort=email&order=<?php echo $sortColumn === 'email' ? $nextOrder : 'asc'; ?>" class="text-secondary">
                                                    Email
                                                    <?php if ($sortColumn === 'email'): ?>
                                                        <i class="fas fa-sort-<?php echo $sortOrder === 'ASC' ? 'up' : 'down'; ?> ms-1"></i>
                                                    <?php else: ?>
                                                        <i class="fas fa-sort ms-1"></i>
                                                    <?php endif; ?>
                                                </a>
                                            </th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                                <a href="?search=<?php echo urlencode($searchTerm); ?>&sort=type&order=<?php echo $sortColumn === 'type' ? $nextOrder : 'asc'; ?>" class="text-secondary">
                                                    Type
                                                    <?php if ($sortColumn === 'type'): ?>
                                                        <i class="fas fa-sort-<?php echo $sortOrder === 'ASC' ? 'up' : 'down'; ?> ms-1"></i>
                                                    <?php else: ?>
                                                        <i class="fas fa-sort ms-1"></i>
                                                    <?php endif; ?>
                                                </a>
                                            </th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                                <a href="?search=<?php echo urlencode($searchTerm); ?>&sort=date_inscription&order=<?php echo $sortColumn === 'date_inscription' ? $nextOrder : 'asc'; ?>" class="text-secondary">
                                                    Register Date
                                                    <?php if ($sortColumn === 'date_inscription'): ?>
                                                        <i class="fas fa-sort-<?php echo $sortOrder === 'ASC' ? 'up' : 'down'; ?> ms-1"></i>
                                                    <?php else: ?>
                                                        <i class="fas fa-sort ms-1"></i>
                                                    <?php endif; ?>
                                                </a>
                                            </th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($utilisateurs as $utilisateur): ?>
                                            <tr id="userRow-<?php echo htmlspecialchars($utilisateur['id_utilisateur']); ?>">
                                                <td class="id-column">
                                                    <div class="d-flex px-2 py-1">
                                                        <div class="ms-3">
                                                            <h6 class="text-sm mb-0"><?php echo htmlspecialchars($utilisateur['id_utilisateur']); ?></h6>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="photo-column">
    <div class="d-flex px-2 py-1 justify-content-center">
        <?php
        $photoPath = !empty($utilisateur['photo_profil']) ? __DIR__ . '/../../uploads/' . $utilisateur['photo_profil'] : '';
        $photoUrl = !empty($utilisateur['photo_profil']) ? '../../uploads/' . htmlspecialchars($utilisateur['photo_profil']) : '';
        echo "<!-- Debug: photoPath=$photoPath, exists=" . (file_exists($photoPath) ? 'true' : 'false') . ", photoUrl=$photoUrl -->";
        if (!empty($photoPath) && file_exists($photoPath)): ?>
            <img src="<?php echo $photoUrl; ?>" alt="Profile Photo">
        <?php elseif (!empty($utilisateur['photo_profil'])): ?>
            <span class="missing-photo">Photo missing</span>
        <?php else: ?>
            <span>No Photo</span>
        <?php endif; ?>
    </div>
</td>
                                                <td class="nom-column">
                                                    <div class="d-flex px-2 py-1">
                                                        <div class="ms-3">
                                                            <h6 class="text-sm mb-0"><?php echo htmlspecialchars($utilisateur['nom']); ?></h6>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="prenom-column">
                                                    <div class="d-flex px-2 py-1">
                                                        <div class="ms-3">
                                                            <h6 class="text-sm mb-0"><?php echo htmlspecialchars($utilisateur['prenom']); ?></h6>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="email-column">
                                                    <div class="d-flex px-2 py-1">
                                                        <div class="ms-3">
                                                            <h6 class="text-sm mb-0"><?php echo htmlspecialchars($utilisateur['email']); ?></h6>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="type-column">
                                                    <div class="text-center">
                                                        <span class="badge badge-sm bg-gradient-<?php echo $utilisateur['type'] === 'administrateur' ? 'success' : ($utilisateur['type'] === 'investisseur' ? 'danger' : 'primary'); ?>">
                                                            <?php echo htmlspecialchars($utilisateur['type']); ?>
                                                        </span>
                                                    </div>
                                                </td>
                                                <td class="date-inscription-column">
                                                    <div class="d-flex px-2 py-1">
                                                        <div class="ms-3">
                                                            <h6 class="text-sm mb-0"><?php echo htmlspecialchars($utilisateur['date_inscription']); ?></h6>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="align-middle text-center">
                                                    <a href="edit_user.php?id_utilisateur=<?php echo htmlspecialchars($utilisateur['id_utilisateur']); ?>" class="text-secondary font-weight-bold text-xs me-2">
                                                        <i class="fas fa-edit me-1"></i> Edit
                                                    </a>
                                                    <a href="#" class="text-secondary font-weight-bold text-xs me-2 delete-btn" data-id="<?php echo htmlspecialchars($utilisateur['id_utilisateur']); ?>" data-name="<?php echo htmlspecialchars($utilisateur['prenom'] . ' ' . $utilisateur['nom']); ?>" data-email="<?php echo htmlspecialchars($utilisateur['email']); ?>">
                                                    <i class="fas fa-trash-alt me-1"></i> Delete
                                                    </a>
                                                    <?php if (!empty($utilisateur['photo_profil'])): ?>
                                                        <a href="delete_photo.php?id_utilisateur=<?php echo htmlspecialchars($utilisateur['id_utilisateur']); ?>" class="text-secondary font-weight-bold text-xs me-2" onclick="return confirm('Are you sure you want to delete this user\'s profile photo?');">
                                                            <i class="fas fa-image me-1"></i> Delete Photo
                                                        </a>
                                                    <?php endif; ?>

                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <footer class="footer pt-3">
                <div class="container-fluid">
                    <div class="row align-items-center justify-content-lg-between">
                        <div class="col-lg-6 mb-lg-0 mb-4">
                            <div class="copyright text-center text-sm text-muted text-lg-start">
                                © <script>
                                    document.write(new Date().getFullYear())
                                </script>,
                                made with <i class="fa fa-heart"></i> by InnoConnect Team
                            </div>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
        <!-- Modal for Delete Confirmation -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirm Deletion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete the user:</p>
                <p><strong id="modalUserName"></strong> (<span id="modalUserEmail"></span>)</p>
                <p style="color: #6c757d;">This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Delete User</button>
            </div>
        </div>
    </div>
</div>
    </main>
    <!-- Fixed Plugin (Theme Configurator) -->
    <div class="fixed-plugin">
        <a class="fixed-plugin-button text-dark position-fixed px-3 py-2">
            <i class="fa fa-cog py-2"> </i>
        </a>
        <div class="card shadow-lg">
            <div class="card-header pb-0 pt-3">
                <div class="float-start">
                    <h5 class="mt-3 mb-0">InnoConnect Configurator</h5>
                    <p>Customize your dashboard.</p>
                </div>
                <div class="float-end mt-4">
                    <button class="btn btn-link text-dark p-0 fixed-plugin-close-button">
                        <i class="fa fa-close"></i>
                    </button>
                </div>
            </div>
            <hr class="horizontal dark my-1">
            <div class="card-body pt-sm-3 pt-0 overflow-auto">
                <div>
                    <h6 class="mb-0">Sidebar Colors</h6>
                </div>
                <a href="javascript:void(0)" class="switch-trigger background-color">
                    <div class="badge-colors my-2 text-start">
                        <span class="badge filter bg-gradient-primary active" data-color="primary" onclick="sidebarColor(this)"></span>
                        <span class="badge filter bg-gradient-dark" data-color="dark" onclick="sidebarColor(this)"></span>
                        <span class="badge filter bg-gradient-info" data-color="info" onclick="sidebarColor(this)"></span>
                        <span class="badge filter bg-gradient-success" data-color="success" onclick="sidebarColor(this)"></span>
                        <span class="badge filter bg-gradient-warning" data-color="warning" onclick="sidebarColor(this)"></span>
                        <span class="badge filter bg-gradient-danger" data-color="danger" onclick="sidebarColor(this)"></span>
                    </div>
                </a>
                <div class="mt-3">
                    <h6 class="mb-0">Sidenav Type</h6>
                    <p class="text-sm">Choose between 2 sidenav types.</p>
                </div>
                <div class="d-flex">
                    <button class="btn bg-gradient-primary w-100 px-3 mb-2 active me-2" data-class="bg-white" onclick="sidebarType(this)">White</button>
                    <button class="btn bg-gradient-primary w-100 px-3 mb-2" data-class="bg-default" onclick="sidebarType(this)">Dark</button>
                </div>
                <div class="d-flex my-3">
                    <h6 class="mb-0">Fixed Navbar</h6>
                    <div class="form-check form-switch ps-0 ms-auto my-auto">
                        <input class="form-check-input mt-1 ms-auto" type="checkbox" id="navbarFixed" onclick="navbarFixed(this)">
                    </div>
                </div>
                <hr class="horizontal dark my-sm-4">
                <div class="mt-2 mb-5 d-flex">
                    <h6 class="mb-0">Light / Dark</h6>
                    <div class="form-check form-switch ps-0 ms-auto my-auto">
                        <input class="form-check-input mt-1 ms-auto" type="checkbox" id="dark-version" onclick="darkMode(this)">
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Core JS Files -->
    <script src="../../assets2/js/core/popper.min.js"></script>
    <script src="../../assets2/js/core/bootstrap.min.js"></script>
    <script src="../../assets2/js/plugins/perfect-scrollbar.min.js"></script>
    <script src="../../assets2/js/plugins/smooth-scrollbar.min.js"></script>
    <script src="../../assets2/js/plugins/chartjs.min.js"></script>
    <script>
        // Highlight and Scroll to Edited User and Specific Columns
        document.addEventListener('DOMContentLoaded', function () {
            <?php if (isset($_GET['highlight'])): ?>
                const userRow = document.getElementById('userRow-<?php echo htmlspecialchars($_GET['highlight']); ?>');
                if (userRow) {
                    userRow.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    userRow.style.backgroundColor = '#e6f3ff'; // Light blue highlight for the row
                    setTimeout(() => {
                        userRow.style.backgroundColor = ''; // Remove row highlight after 3 seconds
                    }, 3000);

                    // Highlight specific columns
                    const changedFields = '<?php echo isset($_GET['changed']) ? htmlspecialchars($_GET['changed']) : ''; ?>'.split(',');
                    changedFields.forEach(field => {
                        if (field) {
                            const column = userRow.querySelector(`.${field}-column`);
                            if (column) {
                                column.classList.add('highlight-column');
                                setTimeout(() => {
                                    column.classList.remove('highlight-column'); // Remove column highlight after 3 seconds
                                }, 3000);
                            }
                        }
                    });
                }
            <?php endif; ?>

            // Show Success Notification if Present
            const successNotification = document.getElementById('successNotification');
            if (successNotification) {
                successNotification.classList.add('show');
                setTimeout(() => {
                    successNotification.classList.add('hide');
                }, 3000); // Hide after 3 seconds
            }

            // User Type Distribution Chart
            const ctx = document.getElementById('userTypeChart').getContext('2d');
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Administrators', 'Investors', 'Innovators'],
                    datasets: [{
                        data: [<?php echo $chartData['administrateurs']; ?>, <?php echo $chartData['investisseurs']; ?>, <?php echo $chartData['innovateurs']; ?>],
                        backgroundColor: ['#28A745', '#DC3545', '#007BFF'],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'right',
                            labels: {
                                color: '#1A2C42'
                            }
                        }
                    },
                    cutout: '70%'
                }
            });

            // Registration Growth Chart
            const growthCtx = document.getElementById('growthChart').getContext('2d');
            var gradientStroke = growthCtx.createLinearGradient(0, 230, 0, 50);
            gradientStroke.addColorStop(1, 'rgba(94, 114, 228, 0.2)');
            gradientStroke.addColorStop(0.2, 'rgba(94, 114, 228, 0.0)');
            gradientStroke.addColorStop(0, 'rgba(94, 114, 228, 0)');
            new Chart(growthCtx, {
                type: 'line',
                data: {
                    labels: <?php echo json_encode($labels); ?>,
                    datasets: [{
                        label: 'Registrations',
                        data: <?php echo json_encode($growthData); ?>,
                        borderColor: '#5e72e4',
                        backgroundColor: gradientStroke,
                        fill: true,
                        tension: 0.4,
                        pointRadius: 0,
                        borderWidth: 3
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: '#1A2C42',
                            titleFont: { size: 12 },
                            bodyFont: { size: 12 },
                            padding: 10,
                            cornerRadius: 4
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                color: '#1A2C42'
                            }
                        },
                        y: {
                            grid: {
                                color: 'rgba(0, 0, 0, 0.05)'
                            },
                            ticks: {
                                color: '#1A2C42',
                                stepSize: 10
                            }
                        }
                    }
                }
            });
        });
    </script>
    <script src="../../assets2/js/argon-dashboard.min.js?v=2.0.4"></script>
    <script>
$(document).ready(function() {
    $('.delete-btn').click(function() {
        const id = $(this).data('id');
        const name = $(this).data('name');
        const email = $(this).data('email');
        console.log('Delete button clicked:', { id, name, email }); // Log les données
        $('#modalUserName').text(name);
        $('#modalUserEmail').text(email);
        $('#confirmDelete').data('id', id);
        $('#deleteModal').modal('show');
    });

    $('#confirmDelete').click(function() {
        const id = $(this).data('id');
        console.log('Confirm delete for ID:', id); // Log l'ID envoyé
        $.ajax({
            url: 'delete_user_action.php',
            type: 'POST',
            data: { id_utilisateur: id },
            dataType: 'json', // Assure que la réponse est interprétée comme JSON
            success: function(response) {
                console.log('AJAX response:', response); // Log la réponse
                if (response.success) {
                    $('#userRow-' + id).remove();
                    $('#deleteModal').modal('hide');
                    alert('User deleted successfully!');
                    location.reload();
                } else {
                    alert('Failed to delete user: ' + (response.message || 'Unknown error'));
                }
            },
            error: function(xhr, status, error) {
                console.log('AJAX error:', { status, error, response: xhr.responseText }); // Log l'erreur
                alert('An error occurred while deleting the user.');
            }
        });
    });
});
</script>
</body>

</html>
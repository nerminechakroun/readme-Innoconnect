<?php
require_once '../../Controller/FinancementController.php';
require_once '../../Model/Financement.php';
require_once '../../Controller/ContratController.php';

$errors = [];
$success = '';

$controller = new FinancementController();
$contractController = new ContratController();
$contracts = $contractController->getAllContrats();

// Fonction de comparaison pour le tri
function compareDates($a, $b, $order = 'ASC') {
    $dateA = strtotime($a['date_signature']);
    $dateB = strtotime($b['date_signature']);
    
    if ($order === 'ASC') {
        return $dateA - $dateB;
    } else {
        return $dateB - $dateA;
    }
}

// Handle sorting
if (isset($_GET['sort'])) {
    if ($_GET['sort'] === 'date_asc') {
        usort($contracts, function($a, $b) {
            return compareDates($a, $b, 'ASC');
        });
    } elseif ($_GET['sort'] === 'date_desc') {
        usort($contracts, function($a, $b) {
            return compareDates($a, $b, 'DESC');
        });
    }
}

// Pagination settings
$contracts_per_page = 3;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$total_contracts = count($contracts);
$total_pages = ceil($total_contracts / $contracts_per_page);

// Ensure current page is within valid range
$current_page = max(1, min($current_page, $total_pages));

// Get contracts for current page
$start_index = ($current_page - 1) * $contracts_per_page;
$current_contracts = array_slice($contracts, $start_index, $contracts_per_page);

// Handle Export to CSV
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['export_csv'])) {
    // Set headers for CSV download
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=contrats_' . date('Y-m-d') . '.csv');
    
    // Create output stream
    $output = fopen('php://output', 'w');
    
    // Add UTF-8 BOM for proper French character encoding in Excel
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    // Add headers
    fputcsv($output, [
        'ID',
        'Type',
        'Innovateur ID',
        'Innovateur Email',
        'Innovateur Nom',
        'Investisseur ID',
        'Investisseur Email',
        'Investisseur Nom',
        'Projet',
        'Type Financement',
        'Montant (€)',
        'Date Signature',
        'Statut'
    ]);
    
    // Add data
    foreach ($contracts as $contract) {
        $type = '';
        if ($contract['innovateur_id'] && $contract['investisseur_id']) {
            $type = 'Complet';
        } elseif ($contract['innovateur_id']) {
            $type = 'Innovateur';
        } else {
            $type = 'Investisseur';
        }
        
        fputcsv($output, [
            $contract['id_contrat'],
            $type,
            $contract['innovateur_id'],
            $contract['innovateur_email'],
            $contract['innovateur_nom'],
            $contract['investisseur_id'],
            $contract['investisseur_email'],
            $contract['investisseur_nom'],
            $contract['projet_nom'],
            $contract['type_financement'],
            $contract['montant'],
            $contract['date_signature'],
            $contract['statut']
        ]);
    }
    
    fclose($output);
    exit();
}

// Handle Add Innovator Contract
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_innovator_contract'])) {
    try {
        $contrat = new Contrat();
        $contrat->setInnovateurId($_POST['innovateur_id']);
        $contrat->setInnovateurEmail($_POST['innovateur_email']);
        $contrat->setInnovateurNom($_POST['innovateur_nom']);
        $contrat->setInvestisseurId(null); // Null for innovator contract
        $contrat->setInvestisseurEmail(null);
        $contrat->setInvestisseurNom(null);
        $contrat->setProjetNom($_POST['projet_nom']);
        $contrat->setTypeFinancement($_POST['type_financement']);
        $contrat->setMontant($_POST['montant']);
        $contrat->setDateSignature($_POST['date_signature']);
        $contrat->setStatut('en attente');

        $result = $contractController->addContrat($contrat);

        if ($result) {
            $success = "Contrat innovateur ajouté avec succès !";
            $contracts = $contractController->getAllContrats();
        } else {
            $errors['global'] = "Erreur lors de l'ajout du contrat.";
        }
    } catch (Exception $e) {
        $errors['global'] = "Erreur : " . $e->getMessage();
    }
}

// Handle Add Investor Contract
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_investor_contract'])) {
    try {
        $contrat = new Contrat();
        $contrat->setInnovateurId(null); // Null for investor contract
        $contrat->setInnovateurEmail(null);
        $contrat->setInnovateurNom(null);
        $contrat->setInvestisseurId($_POST['investisseur_id']);
        $contrat->setInvestisseurEmail($_POST['investisseur_email']);
        $contrat->setInvestisseurNom($_POST['investisseur_nom']);
        $contrat->setProjetNom($_POST['projet_nom']);
        $contrat->setTypeFinancement($_POST['type_financement']);
        $contrat->setMontant($_POST['montant']);
        $contrat->setDateSignature($_POST['date_signature']);
        $contrat->setStatut('en attente');

        $result = $contractController->addContrat($contrat);

        if ($result) {
            $success = "Contrat investisseur ajouté avec succès !";
            $contracts = $contractController->getAllContrats();
        } else {
            $errors['global'] = "Erreur lors de l'ajout du contrat.";
        }
    } catch (Exception $e) {
        $errors['global'] = "Erreur : " . $e->getMessage();
    }
}

// Handle Delete Contract
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    try {
        $deleteId = (int)$_POST['delete_id'];
        $contractController->deleteContrat($deleteId);
        $success = "Contrat supprimé avec succès.";
        $contracts = $contractController->getAllContrats();
    } catch (Exception $e) {
        $errors['global'] = "Erreur suppression : " . $e->getMessage();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_contract'])) {
    try {
        $contrat = new Contrat();
        $contrat->setInnovateurId($_POST['innovateur_id']);
        $contrat->setInnovateurEmail($_POST['innovateur_email']);
        $contrat->setInnovateurNom($_POST['innovateur_nom']);
        $contrat->setInvestisseurId($_POST['investisseur_id']);
        $contrat->setInvestisseurEmail($_POST['investisseur_email']);
        $contrat->setInvestisseurNom($_POST['investisseur_nom']);
        $contrat->setProjetNom($_POST['projet_nom']);
        $contrat->setTypeFinancement($_POST['type_financement']);
        $contrat->setMontant($_POST['montant']);
        $contrat->setDateSignature($_POST['date_signature']);
        $contrat->setStatut($_POST['statut']);

        $result = $contractController->updateContrat($contrat, $_POST['id_contrat']);
        if ($result) {
            $success = "Contrat modifié avec succès !";
            $_POST = [];
            $contracts = $contractController->getAllContrats();
        } else {
            $errors['global'] = "Erreur lors de la modification.";
        }
    } catch (Exception $e) {
        $errors['global'] = "Erreur : " . $e->getMessage();
    }
}

$financements = $controller->listFinancement();
?>

<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
  <link rel="apple-touch-icon" sizes="76x76" href="../../assets2/img/apple-icon.png">
  <link rel="icon" type="image/png" href="../../assets2/img/favicon.png">
  <title>InnoConnect - Contracts Management</title>

  <!-- jQuery and Bootstrap JS -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

  <!-- Fonts -->
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet" />
  
  <!-- Nucleo Icons -->
  <link href="https://demos.creative-tim.com/argon-dashboard-pro/assets/css/nucleo-icons.css" rel="stylesheet" />
  <link href="https://demos.creative-tim.com/argon-dashboard-pro/assets/css/nucleo-svg.css" rel="stylesheet" />
  
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />

  <!-- CSS -->
  <link id="pagestyle" href="../../assets2/css/argon-dashboard.css" rel="stylesheet" />
  
  <style>
    /* Status colors */
    .status-pending {
      color: #ffc107;
      font-weight: bold;
    }
    
    .status-signed {
      color: #17a2b8;
      font-weight: bold;
    }
    
    .status-validated {
      color: #28a745;
      font-weight: bold;
    }
    
    /* Contract type badges */
    .contract-type-badge {
      font-size: 12px;
      padding: 5px 10px;
      border-radius: 10px;
      display: inline-block;
      text-align: center;
      width: auto;
    }
    
    .badge-innovator {
      background-color: #17a2b8;
      color: white;
    }
    
    .badge-investor {
      background-color: #6f42c1;
      color: white;
    }
    
    .badge-full {
      background-color: #28a745;
      color: white;
    }

    /* Pagination styling */
    .pagination {
      margin-bottom: 0;
      justify-content: center;
      margin-top: 20px;
    }
    
    .page-link {
      color: #5e72e4;
    }
    
    .page-item.active .page-link {
      background-color: #5e72e4;
      border-color: #5e72e4;
    }
    
    .page-link:hover {
      color: #233dd2;
    }
    
    /* Table styling */
    .table th {
      font-size: 0.75rem;
      text-transform: uppercase;
      font-weight: 600;
      padding: 0.75rem 1.5rem;
    }
    
    .table td {
      font-size: 0.875rem;
      padding: 0.75rem 1.5rem;
      vertical-align: middle;
    }
    
    /* Actions column */
    .btn-action {
      padding: 0.25rem 0.5rem;
      font-size: 0.75rem;
      margin: 0 0.25rem;
    }
    
    /* Form styling */
    .form-group {
      margin-bottom: 1rem;
    }
    
    .form-label {
      font-weight: 600;
      margin-bottom: 0.5rem;
    }
    
    /* Notification */
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
  </style>
</head>

<body class="g-sidenav-show bg-gray-100">
  <div class="min-height-300 bg-dark position-absolute w-100"></div>

  <!-- Sidebar -->
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
          <a class="nav-link" href="listeUser.php">
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
          <a class="nav-link active" href="ContratView.php">
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

  <!-- Main Content -->
  <main class="main-content position-relative border-radius-lg">
    <!-- Navbar -->
    <nav class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl" id="navbarBlur" style="background-color: #6f42c1;" data-scroll="false">
      <div class="container-fluid py-1 px-3">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb bg-transparent mb-0 pb-0 pt-1 px-0 me-sm-6 me-5">
            <li class="breadcrumb-item text-sm"><a class="opacity-5 text-white" href="javascript:;">Pages</a></li>
            <li class="breadcrumb-item text-sm text-white active" aria-current="page">Contracts</li>
          </ol>
          <h6 class="font-weight-bolder text-white mb-0">Contracts Management</h6>
        </nav>
        <div class="collapse navbar-collapse mt-sm-0 mt-2 me-md-0 me-sm-4" id="navbar">
          <div class="ms-md-auto pe-md-3 d-flex align-items-center">
            <div class="input-group">
              <span class="input-group-text text-body"><i class="fas fa-search" aria-hidden="true"></i></span>
              <input type="text" class="form-control" id="searchInput" placeholder="Search...">
            </div>
          </div>
          <ul class="navbar-nav justify-content-end">
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
              <a href="../frontOffice/login.php" class="nav-link text-white font-weight-bold px-0">
                <i class="fa fa-user me-sm-1"></i>
                <span class="d-sm-inline d-none">Login</span>
              </a>
            </li>
          </ul>
        </div>
      </div>
    </nav>
    <!-- End Navbar -->

    <!-- Notification Messages -->
    <?php if (!empty($success)): ?>
    <div class="notification" id="successNotification">
      <?php echo htmlspecialchars($success); ?>
    </div>
    <?php endif; ?>

    <div class="container-fluid py-4">
      <!-- Error Messages -->
      <?php if (!empty($errors['global'])): ?>
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($errors['global']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
      <?php endif; ?>

      <!-- Main Content Section -->
      <div class="row">
        <div class="col-12">
            <div class="card mb-4">
            <div class="card-header pb-0 p-3">
              <div class="d-flex justify-content-between align-items-center">
                <h6 class="mb-0">Contracts List</h6>
                <div class="d-flex">
                  <button class="btn btn-primary btn-sm me-2" data-bs-toggle="modal" data-bs-target="#innovatorModal">
                    <i class="fas fa-user-tie me-1"></i> New Innovator Contract
                    </button>
                    <button class="btn btn-info btn-sm me-2" data-bs-toggle="modal" data-bs-target="#investorModal">
                    <i class="fas fa-hand-holding-usd me-1"></i> New Investor Contract
                    </button>
                    <button class="btn btn-success btn-sm me-2" data-bs-toggle="modal" data-bs-target="#statsModal">
                    <i class="fas fa-chart-pie me-1"></i> Statistics
                    </button>
                  <form method="POST" class="me-2">
                      <button type="submit" name="export_csv" class="btn btn-secondary btn-sm">
                      <i class="fas fa-file-csv me-1"></i> Export CSV
                      </button>
                    </form>
                    <div class="btn-group">
                    <a href="?sort=date_asc" class="btn btn-outline-primary btn-sm <?php echo (isset($_GET['sort']) && $_GET['sort'] === 'date_asc') ? 'active' : ''; ?>">
                      <i class="fas fa-sort-amount-up-alt me-1"></i> Date ↑
                      </a>
                    <a href="?sort=date_desc" class="btn btn-outline-primary btn-sm <?php echo (isset($_GET['sort']) && $_GET['sort'] === 'date_desc') ? 'active' : ''; ?>">
                        <i class="fas fa-sort-amount-down me-1"></i> Date ↓
                      </a>
                    </div>
                  </div>
                </div>
              </div>
              <div class="card-body px-0 pt-0 pb-2">
                <div class="table-responsive p-0">
                <table class="table align-items-center mb-0">
                    <thead>
                      <tr>
                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">ID</th>
                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Type</th>
                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Innovateur</th>
                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Investisseur</th>
                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Projet</th>
                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Montant</th>
                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Date Signature</th>
                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Statut</th>
                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Actions</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach ($current_contracts as $contract): ?>
                        <tr>
                      <td>
                        <div class="d-flex px-2 py-1">
                          <div class="d-flex flex-column justify-content-center">
                            <h6 class="mb-0 text-sm"><?php echo htmlspecialchars($contract['id_contrat']); ?></h6>
                          </div>
                        </div>
                      </td>
                          <td>
                        <div class="d-flex px-2 py-1">
                            <?php 
                              $typeClass = '';
                              $typeText = '';
                              if ($contract['innovateur_id'] && $contract['investisseur_id']) {
                                $typeClass = 'badge-full';
                                $typeText = 'Complet';
                              } elseif ($contract['innovateur_id']) {
                                $typeClass = 'badge-innovator';
                                $typeText = 'Innovateur';
                              } else {
                                $typeClass = 'badge-investor';
                                $typeText = 'Investisseur';
                              }
                            ?>
                          <span class="contract-type-badge <?php echo $typeClass; ?>"><?php echo $typeText; ?></span>
                        </div>
                          </td>
                      <td>
                        <div class="d-flex px-2 py-1">
                          <div class="d-flex flex-column justify-content-center">
                            <?php if ($contract['innovateur_id']): ?>
                            <h6 class="mb-0 text-sm"><?php echo htmlspecialchars($contract['innovateur_nom']); ?></h6>
                            <p class="text-xs text-secondary mb-0"><?php echo htmlspecialchars($contract['innovateur_email']); ?></p>
                            <?php else: ?>
                            <p class="text-xs text-secondary mb-0">N/A</p>
                            <?php endif; ?>
                          </div>
                        </div>
                      </td>
                      <td>
                        <div class="d-flex px-2 py-1">
                          <div class="d-flex flex-column justify-content-center">
                            <?php if ($contract['investisseur_id']): ?>
                            <h6 class="mb-0 text-sm"><?php echo htmlspecialchars($contract['investisseur_nom']); ?></h6>
                            <p class="text-xs text-secondary mb-0"><?php echo htmlspecialchars($contract['investisseur_email']); ?></p>
                            <?php else: ?>
                            <p class="text-xs text-secondary mb-0">N/A</p>
                            <?php endif; ?>
                          </div>
                        </div>
                      </td>
                      <td>
                        <div class="d-flex px-2 py-1">
                          <div class="d-flex flex-column justify-content-center">
                            <h6 class="mb-0 text-sm"><?php echo htmlspecialchars($contract['projet_nom']); ?></h6>
                            <p class="text-xs text-secondary mb-0"><?php echo htmlspecialchars($contract['type_financement']); ?></p>
                          </div>
                        </div>
                      </td>
                      <td>
                        <p class="text-sm font-weight-bold mb-0"><?php echo number_format($contract['montant'], 2); ?> €</p>
                      </td>
                      <td>
                        <p class="text-sm font-weight-bold mb-0"><?php echo htmlspecialchars($contract['date_signature']); ?></p>
                      </td>
                          <td>
                            <?php 
                        $badgeClass = '';
                        switch ($contract['statut']) {
                          case 'en attente':
                            $badgeClass = 'bg-warning';
                            break;
                          case 'signé':
                            $badgeClass = 'bg-info';
                            break;
                          case 'validé':
                            $badgeClass = 'bg-success';
                            break;
                          default:
                            $badgeClass = 'bg-secondary';
                        }
                            ?>
                        <span class="badge badge-sm <?php echo $badgeClass; ?>"><?php echo htmlspecialchars($contract['statut']); ?></span>
                          </td>
                      <td class="align-middle">
                        <button class="btn btn-link text-secondary mb-0 modify-btn" 
                                    data-bs-toggle="modal" 
                                data-bs-target="#modifyContractModal" 
                                data-contract-id="<?php echo htmlspecialchars($contract['id_contrat']); ?>"
                                data-contract-data='<?php echo json_encode($contract); ?>'>
                          <i class="fas fa-edit text-xs"></i> Edit
                            </button>
                        <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this contract?');">
                          <input type="hidden" name="delete_id" value="<?php echo htmlspecialchars($contract['id_contrat']); ?>">
                          <button type="submit" class="btn btn-link text-danger mb-0">
                            <i class="fas fa-trash text-xs"></i> Delete
                              </button>
                            </form>
                          </td>
                        </tr>
                      <?php endforeach; ?>
                    <?php if (empty($current_contracts)): ?>
                    <tr>
                      <td colspan="9" class="text-center py-4">No contracts found.</td>
                    </tr>
                    <?php endif; ?>
                    </tbody>
                  </table>
              </div>
                  
                  <!-- Pagination -->
              <?php if ($total_pages > 1): ?>
                  <div class="d-flex justify-content-center mt-4">
                    <nav aria-label="Page navigation">
                      <ul class="pagination">
                        <?php if ($current_page > 1): ?>
                          <li class="page-item">
                      <a class="page-link" href="?page=<?php echo $current_page - 1; ?><?php echo isset($_GET['sort']) ? '&sort='.$_GET['sort'] : ''; ?>" aria-label="Previous">
                              <span aria-hidden="true">&laquo;</span>
                            </a>
                          </li>
                        <?php endif; ?>
                        
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                          <li class="page-item <?php echo $i === $current_page ? 'active' : ''; ?>">
                      <a class="page-link" href="?page=<?php echo $i; ?><?php echo isset($_GET['sort']) ? '&sort='.$_GET['sort'] : ''; ?>">
                              <?php echo $i; ?>
                            </a>
                          </li>
                        <?php endfor; ?>
                        
                        <?php if ($current_page < $total_pages): ?>
                          <li class="page-item">
                      <a class="page-link" href="?page=<?php echo $current_page + 1; ?><?php echo isset($_GET['sort']) ? '&sort='.$_GET['sort'] : ''; ?>" aria-label="Next">
                              <span aria-hidden="true">&raquo;</span>
                            </a>
                          </li>
                        <?php endif; ?>
                      </ul>
                    </nav>
                  </div>
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
                © <script>document.write(new Date().getFullYear())</script>,
                made with <i class="fa fa-heart"></i> by InnoConnect Team
        </div>
      </div>
          </div>
        </div>
      </footer>
    </div>
  </main>

  <!-- Innovator Contract Modal -->
  <div class="modal fade" id="innovatorModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <form method="POST" action="">
          <div class="modal-header">
            <h5 class="modal-title">Nouveau Contrat Innovateur</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <h6>Informations Innovateur</h6>
            <div class="form-group">
              <label>ID Innovateur</label>
              <input type="text" class="form-control" name="innovateur_id" required>
            </div>
            <div class="form-group">
              <label>Email Innovateur</label>
              <input type="email" class="form-control" name="innovateur_email" required>
            </div>
            <div class="form-group">
              <label>Nom Innovateur</label>
              <input type="text" class="form-control" name="innovateur_nom" required>
            </div>
            
            <hr>
            
            <h6>Détails du Contrat</h6>
            <div class="form-group">
              <label>Nom du Projet</label>
              <input type="text" class="form-control" name="projet_nom" required>
            </div>
            <div class="form-group">
              <label>Type de Financement</label>
              <select class="form-select" name="type_financement" required>
                <option value="capital">Capital</option>
                <option value="pret">Prêt</option>
                <option value="don">Don</option>
                <option value="subvention">Subvention</option>
              </select>
            </div>
            <div class="form-group">
              <label>Montant (€)</label>
              <input type="number" class="form-control" name="montant" step="0.01" required>
            </div>
            <div class="form-group">
              <label>Date de Signature</label>
              <input type="date" class="form-control" name="date_signature" required>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
            <button type="submit" class="btn btn-danger" name="add_innovator_contract">Enregistrer</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Investor Contract Modal -->
  <div class="modal fade" id="investorModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <form method="POST" action="">
          <div class="modal-header">
            <h5 class="modal-title">Nouveau Contrat Investisseur</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <h6>Informations Investisseur</h6>
            <div class="form-group">
              <label>ID Investisseur</label>
              <input type="text" class="form-control" name="investisseur_id" required>
            </div>
            <div class="form-group">
              <label>Email Investisseur</label>
              <input type="email" class="form-control" name="investisseur_email" required>
            </div>
            <div class="form-group">
              <label>Nom Investisseur</label>
              <input type="text" class="form-control" name="investisseur_nom" required>
            </div>
            
            <hr>
            
            <h6>Détails du Contrat</h6>
            <div class="form-group">
              <label>Nom du Projet</label>
              <input type="text" class="form-control" name="projet_nom" required>
            </div>
            <div class="form-group">
              <label>Type de Financement</label>
              <select class="form-select" name="type_financement" required>
                <option value="capital">Capital</option>
                <option value="pret">Prêt</option>
                <option value="don">Don</option>
                <option value="subvention">Subvention</option>
              </select>
            </div>
            <div class="form-group">
              <label>Montant (€)</label>
              <input type="number" class="form-control" name="montant" step="0.01" required>
            </div>
            <div class="form-group">
              <label>Date de Signature</label>
              <input type="date" class="form-control" name="date_signature" required>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
            <button type="submit" class="btn btn-info" name="add_investor_contract">Enregistrer</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Edit Contract Modal -->
  <div class="modal fade" id="modifyContractModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
      <div class="modal-content">
        <form method="POST" action="">
          <input type="hidden" name="id_contrat" id="edit_id_contrat">
          <div class="modal-header">
            <h5 class="modal-title">Modifier le contrat</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <div class="row">
              <div class="col-md-6">
                <h6>Informations Innovateur</h6>
                <div class="form-group">
                  <label>ID Innovateur</label>
                  <input type="text" class="form-control" name="innovateur_id" id="edit_innovateur_id">
                </div>
                <div class="form-group">
                  <label>Email Innovateur</label>
                  <input type="email" class="form-control" name="innovateur_email" id="edit_innovateur_email">
                </div>
                <div class="form-group">
                  <label>Nom Innovateur</label>
                  <input type="text" class="form-control" name="innovateur_nom" id="edit_innovateur_nom">
                </div>
              </div>
              <div class="col-md-6">
                <h6>Informations Investisseur</h6>
                <div class="form-group">
                  <label>ID Investisseur</label>
                  <input type="text" class="form-control" name="investisseur_id" id="edit_investisseur_id">
                </div>
                <div class="form-group">
                  <label>Email Investisseur</label>
                  <input type="email" class="form-control" name="investisseur_email" id="edit_investisseur_email">
                </div>
                <div class="form-group">
                  <label>Nom Investisseur</label>
                  <input type="text" class="form-control" name="investisseur_nom" id="edit_investisseur_nom">
                </div>
              </div>
            </div>
            
            <div class="row mt-3">
              <div class="col-md-6">
                <div class="form-group">
                  <label>Nom du Projet</label>
                  <input type="text" class="form-control" name="projet_nom" id="edit_projet_nom" required>
                </div>
                <div class="form-group">
                  <label>Type de Financement</label>
                  <select class="form-select" name="type_financement" id="edit_type_financement" required>
                    <option value="capital">Capital</option>
                    <option value="pret">Prêt</option>
                    <option value="don">Don</option>
                    <option value="subvention">Subvention</option>
                  </select>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label>Montant (€)</label>
                  <input type="number" class="form-control" name="montant" id="edit_montant" step="0.01" required>
                </div>
                <div class="form-group">
                  <label>Date de Signature</label>
                  <input type="date" class="form-control" name="date_signature" id="edit_date_signature" required>
                </div>
                <div class="form-group">
                  <label>Statut</label>
                  <select class="form-select" name="statut" id="edit_statut" required>
                    <option value="en attente">En attente</option>
                    <option value="signé par l'innovateur">Signé par l'innovateur</option>
                    <option value="signé par l'investisseur">Signé par l'investisseur</option>
                    <option value="validé">Validé</option>
                  </select>
                </div>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
            <button type="submit" class="btn btn-primary" name="update_contract">Enregistrer</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Stats Modal -->
  <div class="modal fade" id="statsModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Contract Statistics</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header p-3">
                                <h6 class="mb-0">Contracts by Status</h6>
                            </div>
                            <div class="card-body p-3">
                                <div class="chart">
                                    <canvas id="contractsStatusChart" class="chart-canvas" height="300"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header p-3">
                                <h6 class="mb-0">Contracts by Type</h6>
                            </div>
                            <div class="card-body p-3">
                                <div class="chart">
                                    <canvas id="contractsTypeChart" class="chart-canvas" height="300"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header p-3">
                                <h6 class="mb-0">Contracts Summary</h6>
                            </div>
                            <div class="card-body p-3">
                                <div class="row">
                                    <div class="col-md-4 text-center">
                                        <h3 class="font-weight-bold mb-0"><?php echo count($contracts); ?></h3>
                                        <p class="text-sm mb-0 text-capitalize">Total Contracts</p>
                                    </div>
                                    <div class="col-md-4 text-center">
                                        <h3 class="font-weight-bold text-success mb-0">
                                            <?php 
                                                $totalAmount = array_reduce($contracts, function($sum, $contract) {
                                                    return $sum + $contract['montant'];
                                                }, 0);
                                                echo number_format($totalAmount, 2);
                                            ?> €
                                        </h3>
                                        <p class="text-sm mb-0 text-capitalize">Total Amount</p>
                                    </div>
                                    <div class="col-md-4 text-center">
                                        <h3 class="font-weight-bold text-info mb-0">
                                            <?php 
                                                $validated = array_filter($contracts, function($contract) {
                                                    return $contract['statut'] === 'validé';
                                                });
                                                echo count($validated);
                                            ?>
                                        </h3>
                                        <p class="text-sm mb-0 text-capitalize">Validated Contracts</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
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
    // Show success notification if present
    document.addEventListener('DOMContentLoaded', function() {
      const successNotification = document.getElementById('successNotification');
      if (successNotification) {
        successNotification.classList.add('show');
        setTimeout(() => {
          successNotification.classList.add('hide');
          setTimeout(() => {
            successNotification.style.display = 'none';
          }, 500);
        }, 3000);
      }
      
      // Set up the modify contract buttons
      const modifyButtons = document.querySelectorAll('.modify-btn');
      modifyButtons.forEach(button => {
        button.addEventListener('click', function() {
          const contractData = JSON.parse(this.getAttribute('data-contract-data'));
          
          // Fill the modal form fields with contract data
          document.getElementById('edit_id_contrat').value = contractData.id_contrat;
          document.getElementById('edit_innovateur_id').value = contractData.innovateur_id || '';
          document.getElementById('edit_innovateur_email').value = contractData.innovateur_email || '';
          document.getElementById('edit_innovateur_nom').value = contractData.innovateur_nom || '';
          document.getElementById('edit_investisseur_id').value = contractData.investisseur_id || '';
          document.getElementById('edit_investisseur_email').value = contractData.investisseur_email || '';
          document.getElementById('edit_investisseur_nom').value = contractData.investisseur_nom || '';
          document.getElementById('edit_projet_nom').value = contractData.projet_nom;
          document.getElementById('edit_type_financement').value = contractData.type_financement;
          document.getElementById('edit_montant').value = contractData.montant;
          document.getElementById('edit_date_signature').value = contractData.date_signature;
          document.getElementById('edit_statut').value = contractData.statut;
        });
      });
    
    // Create pie chart when stats modal is opened
    document.getElementById('statsModal').addEventListener('show.bs.modal', function () {
        const contracts = <?php echo json_encode($contracts); ?>;
        
        // Count contracts by status
        const statusCounts = contracts.reduce((acc, contract) => {
            acc[contract.statut] = (acc[contract.statut] || 0) + 1;
            return acc;
        }, {});
        
        // Status labels in French to English
        const statusTranslation = {
          'en attente': 'Pending',
          'signé': 'Signed',
          'validé': 'Validated'
        };
        
        // Color scheme that matches the new design
        const statusColors = {
          'en attente': '#ffc107',
          'signé': '#17a2b8',
          'validé': '#28a745'
        };
        
        // Prepare data for Chart.js
        const labels = Object.keys(statusCounts).map(key => statusTranslation[key] || key);
        const data = {
          labels: labels,
            datasets: [{
                data: Object.values(statusCounts),
            backgroundColor: Object.keys(statusCounts).map(key => statusColors[key] || '#6c757d')
          }]
        };
        
        // Count contracts by type
        const typeCounts = contracts.reduce((acc, contract) => {
          let type = '';
          if (contract.innovateur_id && contract.investisseur_id) {
            type = 'Complete';
          } else if (contract.innovateur_id) {
            type = 'Innovator';
          } else {
            type = 'Investor';
          }
          acc[type] = (acc[type] || 0) + 1;
          return acc;
        }, {});
        
        // Color scheme for types
        const typeColors = {
          'Complete': '#28a745',
          'Innovator': '#17a2b8',
          'Investor': '#6f42c1'
        };
        
        // Prepare data for type pie chart
        const typeData = {
          labels: Object.keys(typeCounts),
          datasets: [{
            data: Object.values(typeCounts),
            backgroundColor: Object.keys(typeCounts).map(key => typeColors[key] || '#6c757d')
            }]
        };
        
        // Get the canvas elements
        const statusCtx = document.getElementById('contractsStatusChart').getContext('2d');
        const typeCtx = document.getElementById('contractsTypeChart').getContext('2d');
        
        // Destroy existing charts if they exist
        if (window.statusChart) window.statusChart.destroy();
        if (window.typeChart) window.typeChart.destroy();
        
        // Create new pie charts
        window.statusChart = new Chart(statusCtx, {
            type: 'pie',
            data: data,
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    },
                    title: {
                        display: true,
                text: 'Contracts by Status',
                font: {
                  size: 16
                    }
                }
            }
          }
        });
        
        window.typeChart = new Chart(typeCtx, {
          type: 'pie',
          data: typeData,
          options: {
            responsive: true,
            plugins: {
              legend: {
                position: 'bottom'
              },
              title: {
                display: true,
                text: 'Contracts by Type',
                font: {
                  size: 16
                }
              }
            }
          }
        });
      });
    });

    // Initialize smooth scrollbar
    var win = navigator.platform.indexOf('Win') > -1;
    if (win && document.querySelector('#sidenav-scrollbar')) {
      var options = {
        damping: '0.5'
      }
      Scrollbar.init(document.querySelector('#sidenav-scrollbar'), options);
            }
  </script>
  
  <!-- Argon Dashboard JS -->
  <script src="../../assets2/js/argon-dashboard.min.js"></script>
</body>

</html>
<?php
require_once '../../Controller/FinancementController.php';
require_once '../../Model/Financement.php';
require_once '../../Controller/ContratController.php';

$errors = [];
$success = '';

$controller = new FinancementController();
$contractController = new ContratController();
$contracts = $contractController->getAllContrats();

// Handle Add New Financement
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit']) && empty($_POST['id_financement'])) {
    try {
        $financement = new Financement();
        $financement->setTitre($_POST['titre']);
        $financement->setTypeOperation($_POST['typeOperation']);
        $financement->setMontant($_POST['montant']);
        $financement->setDateOperation($_POST['date_operation']);
        $financement->setIdContrat($_POST['id_contrat']);
        $financement->setIdProjet('1'); // Set a default project ID or adjust as necessary

        $result = $controller->addFinancement($financement);

        if ($result) {
            $success = "Financement ajouté avec succès !";
            $_POST = []; // Clear POST data after successful submission
        } else {
            $errors['global'] = "Erreur lors de l'ajout du financement.";
        }
    } catch (Exception $e) {
        $errors['global'] = "Erreur : " . $e->getMessage();
    }
}

// Handle Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    try {
        $deleteId = (int)$_POST['delete_id'];
        $controller->deleteFinancement($deleteId);
        $success = "Financement supprimé avec succès.";
    } catch (Exception $e) {
        $errors['global'] = "Erreur suppression : " . $e->getMessage();
    }
}

// Handle Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit']) && !empty($_POST['id_financement'])) {
    try {
        $financement = new Financement();
        $financement->setIdFinancement($_POST['id_financement']);
        $financement->setTitre($_POST['titre']);
        $financement->setTypeOperation($_POST['typeOperation']);
        $financement->setMontant($_POST['montant']);
        $financement->setDateOperation($_POST['date_operation']);
        $financement->setIdContrat($_POST['id_contrat']);
        $financement->setIdProjet($_POST['id_Projet']);

        $result = $controller->updateFinancement($financement, $_POST['id_financement']);

        if ($result) {
            $success = "Financement modifié avec succès !";
            $_POST = [];
        } else {
            $errors['global'] = "Erreur lors de la modification.";
        }
    } catch (Exception $e) {
        $errors['global'] = "Erreur : " . $e->getMessage();
    }
}

// Load all financements
$financements = $controller->listFinancement();
?>

<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
  <link rel="apple-touch-icon" sizes="76x76" href="../../assets2/img/apple-icon.png">
  <link rel="icon" type="image/png" href="../../assets2/img/favicon.png">
  <title>InnoConnect - Financements Management</title>

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
    /* Transaction type badges */
    .transaction-type-badge {
      font-size: 12px;
      padding: 5px 10px;
      border-radius: 10px;
      display: inline-block;
      text-align: center;
      width: auto;
    }
    
    .badge-income {
      background-color: #28a745;
      color: white;
    }

    .badge-expense {
      background-color: #dc3545;
      color: white;
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
          <a class="nav-link" href="ContratView.php">
            <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
              <i class="ni ni-collection text-dark text-sm opacity-10"></i>
            </div>
            <span class="nav-link-text ms-1">Contracts Management</span>
          </a>
        </li>
      <li class="nav-item">
          <a class="nav-link active" href="FinancementView.php">
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
            <li class="breadcrumb-item text-sm text-white active" aria-current="page">Financements Management</li>
          </ol>
          <h6 class="font-weight-bolder text-white mb-0">Financements Management</h6>
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
                <h6 class="mb-0">Financements List</h6>
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addFinancementModal">
                  <i class="fas fa-plus me-1"></i> Add New Financement
                </button>
              </div>
</div>
            <div class="card-body px-0 pt-0 pb-2">
                <div class="table-responsive p-0">
                <table class="table align-items-center mb-0">
                    <thead>
                      <tr>
                      <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">ID</th>
                      <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Title</th>
                      <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Type</th>
                      <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Amount</th>
                      <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Date</th>
                      <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Contract ID</th>
                      <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Project ID</th>
                      <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Actions</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach ($financements as $financement): ?>
                        <tr>
                      <td>
                        <div class="d-flex px-2 py-1">
                          <div class="d-flex flex-column justify-content-center">
                            <h6 class="mb-0 text-sm"><?php echo htmlspecialchars($financement['id_financement']); ?></h6>
                          </div>
                        </div>
                      </td>
                      <td>
                        <div class="d-flex px-2 py-1">
                          <div class="d-flex flex-column justify-content-center">
                            <h6 class="mb-0 text-sm"><?php echo htmlspecialchars($financement['titre']); ?></h6>
                          </div>
                        </div>
                      </td>
                      <td>
                        <div class="d-flex px-2 py-1">
                          <?php $typeClass = $financement['typeOperation'] === 'encaissement' ? 'badge-income' : 'badge-expense'; ?>
                          <span class="transaction-type-badge <?php echo $typeClass; ?>">
                            <?php echo htmlspecialchars(ucfirst($financement['typeOperation'])); ?>
                          </span>
                        </div>
                      </td>
                      <td>
                        <div class="d-flex px-2 py-1">
                          <div class="d-flex flex-column justify-content-center">
                            <h6 class="mb-0 text-sm"><?php echo number_format($financement['montant'], 2); ?> €</h6>
                          </div>
                        </div>
                      </td>
                      <td>
                        <div class="d-flex px-2 py-1">
                          <div class="d-flex flex-column justify-content-center">
                            <h6 class="mb-0 text-sm"><?php echo htmlspecialchars($financement['date_operation']); ?></h6>
                          </div>
                        </div>
                      </td>
                      <td>
                        <div class="d-flex px-2 py-1">
                          <div class="d-flex flex-column justify-content-center">
                            <h6 class="mb-0 text-sm"><?php echo htmlspecialchars($financement['id_contrat']); ?></h6>
                          </div>
                        </div>
                      </td>
                      <td>
                        <div class="d-flex px-2 py-1">
                          <div class="d-flex flex-column justify-content-center">
                            <h6 class="mb-0 text-sm"><?php echo htmlspecialchars($financement['id_Projet']); ?></h6>
                          </div>
                        </div>
                      </td>
                      <td class="align-middle">
                        <button class="btn btn-link text-secondary mb-0 edit-financement" 
                                data-bs-toggle="modal" 
                                data-bs-target="#editFinancementModal"
                                data-id="<?php echo htmlspecialchars($financement['id_financement']); ?>"
                                data-titre="<?php echo htmlspecialchars($financement['titre']); ?>"
                                data-type="<?php echo htmlspecialchars($financement['typeOperation']); ?>"
                                data-montant="<?php echo htmlspecialchars($financement['montant']); ?>"
                                data-date="<?php echo htmlspecialchars($financement['date_operation']); ?>"
                                data-contrat="<?php echo htmlspecialchars($financement['id_contrat']); ?>"
                                data-projet="<?php echo htmlspecialchars($financement['id_Projet']); ?>">
                          <i class="fas fa-edit text-xs"></i> Edit
                            </button>
                        <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this financement?');">
                          <input type="hidden" name="delete_id" value="<?php echo htmlspecialchars($financement['id_financement']); ?>">
                          <button type="submit" class="btn btn-link text-danger mb-0">
                            <i class="fas fa-trash text-xs"></i> Delete
                              </button>
                            </form>
                          </td>
                        </tr>
                      <?php endforeach; ?>
                    </tbody>
                  </table>
              </div>
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

  <!-- Add Financement Modal -->
  <div class="modal fade" id="addFinancementModal" tabindex="-1" role="dialog" aria-labelledby="addFinancementModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <form method="POST" action="">
    <div class="modal-header">
            <h5 class="modal-title" id="addFinancementModalLabel">Add New Financement</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>
    <div class="modal-body">
        <div class="form-group">
              <label for="titre" class="form-control-label">Title</label>
              <input class="form-control" type="text" name="titre" id="titre" required>
        </div>
        <div class="form-group">
              <label for="typeOperation" class="form-control-label">Operation Type</label>
              <select class="form-select" name="typeOperation" id="typeOperation" required>
                <option value="encaissement">Encaissement</option>
                <option value="decaissement">Décaissement</option>
            </select>
        </div>
        <div class="form-group">
              <label for="montant" class="form-control-label">Amount (€)</label>
              <input class="form-control" type="number" step="0.01" name="montant" id="montant" required>
        </div>
        <div class="form-group">
              <label for="date_operation" class="form-control-label">Operation Date</label>
              <input class="form-control" type="date" name="date_operation" id="date_operation" required>
        </div>
        <div class="form-group">
              <label for="id_contrat" class="form-control-label">Contract ID</label>
              <select class="form-select" name="id_contrat" id="id_contrat" required>
                <?php foreach ($contracts as $contract): ?>
                <option value="<?php echo htmlspecialchars($contract['id_contrat']); ?>">
                  ID: <?php echo htmlspecialchars($contract['id_contrat']); ?> - 
                  <?php echo htmlspecialchars($contract['projet_nom']); ?> - 
                  <?php echo number_format($contract['montant'], 2); ?>€
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
            <input type="hidden" name="id_Projet" value="1">
    </div>
    <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" name="submit" class="btn btn-primary">Save</button>
    </div>
</form>
      </div>
    </div>
  </div>

  <!-- Edit Financement Modal -->
  <div class="modal fade" id="editFinancementModal" tabindex="-1" role="dialog" aria-labelledby="editFinancementModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
        <form method="POST" action="">
          <input type="hidden" id="edit_id_financement" name="id_financement">
                <div class="modal-header">
            <h5 class="modal-title" id="editFinancementModalLabel">Edit Financement</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
              <label for="edit_titre" class="form-control-label">Title</label>
              <input class="form-control" type="text" name="titre" id="edit_titre" required>
                    </div>
                    <div class="form-group">
              <label for="edit_typeOperation" class="form-control-label">Operation Type</label>
              <select class="form-select" name="typeOperation" id="edit_typeOperation" required>
                <option value="encaissement">Encaissement</option>
                <option value="decaissement">Décaissement</option>
                        </select>
                    </div>
                    <div class="form-group">
              <label for="edit_montant" class="form-control-label">Amount (€)</label>
              <input class="form-control" type="number" step="0.01" name="montant" id="edit_montant" required>
                    </div>
                    <div class="form-group">
              <label for="edit_date_operation" class="form-control-label">Operation Date</label>
              <input class="form-control" type="date" name="date_operation" id="edit_date_operation" required>
                    </div>
                    <div class="form-group">
              <label for="edit_id_contrat" class="form-control-label">Contract ID</label>
              <select class="form-select" name="id_contrat" id="edit_id_contrat" required>
                <?php foreach ($contracts as $contract): ?>
                <option value="<?php echo htmlspecialchars($contract['id_contrat']); ?>">
                  ID: <?php echo htmlspecialchars($contract['id_contrat']); ?> - 
                  <?php echo htmlspecialchars($contract['projet_nom']); ?> - 
                  <?php echo number_format($contract['montant'], 2); ?>€
                </option>
                <?php endforeach; ?>
              </select>
                    </div>
            <input type="hidden" id="edit_id_Projet" name="id_Projet" value="1">
                </div>
                <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" name="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

  <!-- Core JS Files -->
  <script src="../../assets2/js/core/popper.min.js"></script>
  <script src="../../assets2/js/core/bootstrap.min.js"></script>
  <script src="../../assets2/js/plugins/perfect-scrollbar.min.js"></script>
  <script src="../../assets2/js/plugins/smooth-scrollbar.min.js"></script>

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
      
      // Search functionality
      document.getElementById('searchInput').addEventListener('keyup', function() {
        const input = this.value.toLowerCase();
        const rows = document.querySelectorAll('tbody tr');
        
        rows.forEach(row => {
          const text = row.textContent.toLowerCase();
          row.style.display = text.includes(input) ? '' : 'none';
        });
      });
      
      // Edit financement button click handler
      const editButtons = document.querySelectorAll('.edit-financement');
      editButtons.forEach(button => {
        button.addEventListener('click', function() {
          const id = this.getAttribute('data-id');
          const titre = this.getAttribute('data-titre');
          const type = this.getAttribute('data-type');
          const montant = this.getAttribute('data-montant');
          const date = this.getAttribute('data-date');
          const contrat = this.getAttribute('data-contrat');
          const projet = this.getAttribute('data-projet');
          
          document.getElementById('edit_id_financement').value = id;
      document.getElementById('edit_titre').value = titre;
      document.getElementById('edit_typeOperation').value = type;
      document.getElementById('edit_montant').value = montant;
      document.getElementById('edit_date_operation').value = date;
      document.getElementById('edit_id_contrat').value = contrat;
      document.getElementById('edit_id_Projet').value = projet;
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
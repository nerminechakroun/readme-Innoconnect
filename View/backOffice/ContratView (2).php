<?php
require_once '../../Controller/FinancementController.php';
require_once '../../Model/Financement.php';
require_once '../../Controller/ContratController.php';

$errors = [];
$success = '';

$controller = new FinancementController();
$contractController = new ContratController();
$contracts = $contractController->getAllContrats();

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
  <title>Gestion de Contrats - Argon Dashboard</title>

  <!-- Fonts et icônes -->
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet" />
  <link href="https://demos.creative-tim.com/argon-dashboard-pro/assets/css/nucleo-icons.css" rel="stylesheet" />
  <link href="https://demos.creative-tim.com/argon-dashboard-pro/assets/css/nucleo-svg.css" rel="stylesheet" />
  <script src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script>

  <!-- CSS -->
  <link id="pagestyle" href="../../Assets/CSS/argon-dashboard.css?v=2.1.0" rel="stylesheet" />
  <style>
    #contractsTable th {
      background-color: #5e72e4;
      color: white;
    }

    #contractsTable td {
      vertical-align: middle;
    }

    .form-popup {
      display: none;
      position: fixed;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      z-index: 9999;
    }

    .form-group {
      margin-bottom: 1rem;
    }

    .section-content {
      display: none;
    }

    .active {
      background-color: #f0f0f0;
    }

    .top-right-button {
      position: absolute;
      top: 10px;
      right: 10px;
    }

    .top-right-button a {
      display: inline-block;
      padding: 10px 20px;
      background-color: #28a745;
      color: white;
      text-decoration: none;
      border-radius: 5px;
      font-weight: bold;
    }

    .top-right-button a:hover {
      background-color: #218838;
    }

    .card-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .left-buttons {
      display: flex;
      align-items: center;
    }

    .green-button {
      padding: 5px 15px;
      background-color: #28a745;
      color: white;
      text-decoration: none;
      border-radius: 5px;
      font-size: 14px;
      font-weight: bold;
    }

    .green-button:hover {
      background-color: #218838;
    }
    
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
    
    .badge {
      padding: 5px 10px;
      border-radius: 20px;
      font-size: 12px;
    }
    
    .modal-progress {
      height: 4px;
      background-color: #e9ecef;
      margin-bottom: 20px;
    }
    
    .progress-bar {
      background-color: #5e72e4;
      transition: width 0.3s ease;
    }
    
    .btn-group-contracts {
      display: flex;
      gap: 10px;
    }
    
    .contract-type-badge {
      font-size: 12px;
      padding: 3px 8px;
      border-radius: 10px;
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
  </style>
</head>

<body class="g-sidenav-show bg-gray-100">
  <div class="min-height-300 bg-dark position-absolute w-100"></div>

  <!-- Sidebar -->
  <aside class="sidenav bg-white navbar navbar-vertical navbar-expand-xs border-0 border-radius-xl my-3 fixed-start ms-4" id="sidenav-main">
    <div class="sidenav-header">
      <h4 class="text-dark text-center py-3">InnoConnect</h4>
    </div>
    <hr class="horizontal dark mt-0" />
    <div class="collapse navbar-collapse w-auto" id="sidenav-collapse-main">
      <ul class="navbar-nav">
      <li class="nav-item">
          <a class="nav-link active" href="FinancementView.php">
            <i class="fas fa-file-signature text-info"></i>
            <span class="nav-link-text ms-2">Financement Management</span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link active" href="ContratView.php">
            <i class="fas fa-file-signature text-info"></i>
            <span class="nav-link-text ms-2">Contract Management</span>
          </a>
        </li>
      </ul>
    </div>
  </aside>

  <!-- Main Content -->
  <main class="main-content position-relative border-radius-lg">
    <nav class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl" id="navbarBlur" data-scroll="false"></nav>

    <div class="container-fluid py-4">
      <div class="row">
        <div class="col-12">
          <div id="contracts" class="section-content" style="display: block">
            <div class="card mb-4">
              <div class="card-header pb-0">
                <div class="d-flex align-items-center">
                  <div class="btn-group-contracts">
                    <button class="btn btn-danger btn-sm me-2" data-bs-toggle="modal" data-bs-target="#innovatorModal">
                      <i class="fas fa-user-tie me-1"></i> Contrat Innovateur
                    </button>
                    <button class="btn btn-info btn-sm me-2" data-bs-toggle="modal" data-bs-target="#investorModal">
                      <i class="fas fa-hand-holding-usd me-1"></i> Contrat Investisseur
                    </button>
                    <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#statsModal">
                      <i class="fas fa-chart-pie me-1"></i> Statistiques
                    </button>
                  </div>
                  <a href="../FrontOffice/acceuil.html" class="green-button ms-auto">Voir front-office</a>
                </div>
              </div>
              <div class="card-body px-0 pt-0 pb-2">
                <div class="message-container">
                  <?php if (!empty($success)): ?>
                      <div class="alert alert-success" style="color:white !important;" role="alert">
                          <?php echo htmlspecialchars($success); ?>
                      </div>
                  <?php endif; ?>

                  <?php if (!empty($errors['global'])): ?>
                      <div class="alert alert-danger" role="alert" style="color:white !important;">
                          <?php echo htmlspecialchars($errors['global']); ?>
                      </div>
                  <?php endif; ?>
                </div>
                
                <div class="table-responsive p-0">
                  <table class="table align-items-center mb-0" id="contractsTable">
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
                      <?php foreach ($contracts as $contract): ?>
                        <tr>
                          <td><?php echo htmlspecialchars($contract['id_contrat']); ?></td>
                          <td>
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
                            <span class="contract-type-badge <?php echo $typeClass; ?>">
                              <?php echo $typeText; ?>
                            </span>
                          </td>
                          <td><?php echo htmlspecialchars($contract['innovateur_nom'] ?: 'N/A'); ?></td>
                          <td><?php echo htmlspecialchars($contract['investisseur_nom'] ?: 'N/A'); ?></td>
                          <td><?php echo htmlspecialchars($contract['projet_nom']); ?></td>
                          <td><?php echo htmlspecialchars($contract['montant']); ?> €</td>
                          <td><?php echo htmlspecialchars($contract['date_signature']); ?></td>
                          <td>
                            <?php 
                              $statusClass = '';
                              if ($contract['statut'] === 'en attente') $statusClass = 'status-pending';
                              elseif ($contract['statut'] === 'validé') $statusClass = 'status-validated';
                              else $statusClass = 'status-signed';
                            ?>
                            <span class="<?php echo $statusClass; ?>">
                              <?php echo htmlspecialchars($contract['statut']); ?>
                            </span>
                          </td>
                          <td>
                            <button class="btn btn-link text-primary btn-sm" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#editContractModal"
                                    onclick="loadContractData(<?php echo $contract['id_contrat']; ?>)">
                              <i class="fas fa-pencil-alt me-1"></i> Modifier
                            </button>
                            <form method="POST" style="display: inline;">
                              <input type="hidden" name="delete_id" value="<?php echo $contract['id_contrat']; ?>">
                              <button type="submit" class="btn btn-link text-danger btn-sm" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce contrat?');">
                                <i class="fas fa-trash-alt me-1"></i> Supprimer
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
      </div>
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
<div class="modal fade" id="editContractModal" tabindex="-1" role="dialog" aria-hidden="true">
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
                        <!-- Innovateur Fields - initially hidden -->
                        <div class="col-md-6 innovateur-fields" style="display: none;">
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
                        
                        <!-- Investisseur Fields - initially hidden -->
                        <div class="col-md-6 investisseur-fields" style="display: none;">
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
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Statistiques des Contrats</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <canvas id="contractsPieChart"></canvas>
            </div>
        </div>
    </div>
</div>

  <!-- JS -->
  <script src="../../Assets/js/core/popper.min.js"></script>
  <script src="../../Assets/js/core/bootstrap.min.js"></script>
  <script src="../../Assets/js/plugins/perfect-scrollbar.min.js"></script>
  <script src="../../Assets/js/plugins/smooth-scrollbar.min.js"></script>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

  <script>
      // Function to load contract data into edit modal
      function loadContractData(contractId) {
        const contracts = <?php echo json_encode($contracts); ?>;
        const contract = contracts.find(c => c.id_contrat == contractId);
        
        if (contract) {
            document.getElementById('edit_id_contrat').value = contract.id_contrat;
            document.getElementById('edit_projet_nom').value = contract.projet_nom;
            document.getElementById('edit_type_financement').value = contract.type_financement;
            document.getElementById('edit_montant').value = contract.montant;
            document.getElementById('edit_date_signature').value = contract.date_signature;
            document.getElementById('edit_statut').value = contract.statut;
            
            // Hide all fields first
            document.querySelectorAll('.innovateur-fields, .investisseur-fields').forEach(el => {
                el.style.display = 'none';
            });
            
            // Show appropriate fields based on contract type
            if (contract.innovateur_id && contract.investisseur_id) {
                // Full contract - show both
                document.querySelector('.innovateur-fields').style.display = 'block';
                document.querySelector('.investisseur-fields').style.display = 'block';
                document.getElementById('edit_innovateur_id').value = contract.innovateur_id;
                document.getElementById('edit_innovateur_email').value = contract.innovateur_email;
                document.getElementById('edit_innovateur_nom').value = contract.innovateur_nom;
                document.getElementById('edit_investisseur_id').value = contract.investisseur_id;
                document.getElementById('edit_investisseur_email').value = contract.investisseur_email;
                document.getElementById('edit_investisseur_nom').value = contract.investisseur_nom;
            } else if (contract.innovateur_id) {
                // Innovator contract - show only innovateur fields
                document.querySelector('.innovateur-fields').style.display = 'block';
                document.getElementById('edit_innovateur_id').value = contract.innovateur_id;
                document.getElementById('edit_innovateur_email').value = contract.innovateur_email;
                document.getElementById('edit_innovateur_nom').value = contract.innovateur_nom;
            } else {
                // Investor contract - show only investisseur fields
                document.querySelector('.investisseur-fields').style.display = 'block';
                document.getElementById('edit_investisseur_id').value = contract.investisseur_id;
                document.getElementById('edit_investisseur_email').value = contract.investisseur_email;
                document.getElementById('edit_investisseur_nom').value = contract.investisseur_nom;
            }
        }
    }
    
    // Create pie chart when stats modal is opened
    document.getElementById('statsModal').addEventListener('show.bs.modal', function () {
        const contracts = <?php echo json_encode($contracts); ?>;
        
        // Count contracts by status
        const statusCounts = contracts.reduce((acc, contract) => {
            acc[contract.statut] = (acc[contract.statut] || 0) + 1;
            return acc;
        }, {});
        
        // Prepare data for Chart.js
        const data = {
            labels: Object.keys(statusCounts),
            datasets: [{
                data: Object.values(statusCounts),
                backgroundColor: [
                    '#ffc107', // en attente
                    '#17a2b8', // signé par l'innovateur
                    '#6f42c1', // signé par l'investisseur
                    '#28a745'  // validé
                ]
            }]
        };
        
        // Get the canvas element
        const ctx = document.getElementById('contractsPieChart').getContext('2d');
        
        // Destroy existing chart if it exists
        if (window.contractsChart) {
            window.contractsChart.destroy();
        }
        
        // Create new pie chart
        window.contractsChart = new Chart(ctx, {
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
                        text: 'Répartition des Contrats par Statut'
                    }
                }
            }
        });
    });
    
    // Auto-close alerts after 5 seconds
    setTimeout(() => {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            alert.style.transition = 'opacity 1s';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 1000);
        });
    }, 5000);
  </script>
</body>
</html>
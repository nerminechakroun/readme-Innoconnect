<?php
session_start();

// Vérification de la session
if (!isset($_SESSION['id_utilisateur'])) {
    header("Location: ../frontOffice/login.php");
    exit;
}

// Simuler des données (à remplacer par des requêtes réelles à votre base de données)
$stats = [
    'page_views' => 12500,
    'active_sessions' => 320,
    'avg_session_duration' => '4m 32s',
    'bounce_rate' => '42%'
];

$trafficData = [
    'labels' => ['Mar 08', 'Mar 10', 'Mar 12', 'Mar 14', 'Mar 16', 'Mar 18', 'Mar 20', 'Mar 22', 'Mar 24', 'Mar 26', 'Mar 28', 'Mar 30', 'Apr 01', 'Apr 03', 'Apr 05'],
    'data' => [500, 600, 450, 700, 800, 650, 900, 1000, 850, 1100, 1200, 950, 1300, 1400, 1500]
];

$topPages = [
    ['name' => 'Homepage', 'views' => 4500],
    ['name' => 'About Us', 'views' => 3200],
    ['name' => 'Contact', 'views' => 2800],
    ['name' => 'Blog', 'views' => 1500],
    ['name' => 'FAQ', 'views' => 1200]
];

$heatmapData = [
    '00:00-04:00' => 50,
    '04:00-08:00' => 120,
    '08:00-12:00' => 300,
    '12:00-16:00' => 450,
    '16:00-20:00' => 600,
    '20:00-00:00' => 200
];

$notifications = [
    ['message' => 'Server maintenance scheduled for Apr 07, 2025 at 02:00 AM', 'time' => '2 hours ago'],
    ['message' => 'New system update available: Version 2.1.0', 'time' => '1 day ago'],
    ['message' => 'High traffic alert: 500+ active sessions detected', 'time' => '3 days ago']
];

// Simuler les informations de l'utilisateur connecté
$user = ['prenom' => 'John', 'nom' => 'Doe'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link rel="apple-touch-icon" sizes="76x76" href="../assets2/img/apple-icon.png">
  <link rel="icon" type="image/png" href="../assets2/img/favicon.png">
  <title>InnoConnect Back-Office - Dashboard</title>
  <!-- Fonts and icons -->
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet" />
  <!-- Nucleo Icons -->
  <link href="assets2/css/nucleo-icons.css" rel="stylesheet" />
  <link href="assets2/css/nucleo-svg.css" rel="stylesheet" />
  <!-- Font Awesome Icons -->
  <script src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script>
  <!-- Bootstrap CSS -->
  <link href="assets2/css/bootstrap.min.css" rel="stylesheet" />
  <!-- CSS Files -->
  <link id="pagestyle" href="assets2/css/argon-dashboard.css?v=2.1.0" rel="stylesheet" />
  <!-- Chart.js for charts -->
  <script src="assets2/js/chart.js"></script>
  <!-- Custom CSS for card dimensions -->
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
    .heatmap-cell {
      padding: 10px;
      text-align: center;
      color: white;
      border-radius: 5px;
    }
  </style>
</head>

<body class="g-sidenav-show bg-gray-100">
  <div class="min-height-300 bg-dark position-absolute w-100"></div>
  <aside class="sidenav bg-white navbar navbar-vertical navbar-expand-xs border-0 border-radius-xl my-3 fixed-start ms-4" id="sidenav-main">
    <div class="sidenav-header">
      <i class="fas fa-times p-3 cursor-pointer text-secondary opacity-5 position-absolute end-0 top-0 d-none d-xl-none" aria-hidden="true" id="iconSidenav"></i>
      <a class="navbar-brand m-0" href="#">
      <img src="innoconnect.jpeg" width="26px" height="26px" class="navbar-brand-img h-100" alt="main_logo">
      <span class="ms-1 font-weight-bold">Innoconnect</span>
      </a>
    </div>
    <hr class="horizontal dark mt-0">
    <div class="collapse navbar-collapse w-auto" id="sidenav-collapse-main">
      <ul class="navbar-nav">
        <li class="nav-item">
          <a class="nav-link active" href="dashboard.php">
            <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
              <i class="ni ni-tv-2 text-primary text-sm opacity-10"></i>
            </div>
            <span class="nav-link-text ms-1">Dashboard</span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="/projetInnoconnect/View/backOffice/listeUser.php">
            <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
              <i class="ni ni-single-02 text-primary text-sm opacity-10"></i>
            </div>
            <span class="nav-link-text ms-1">User Management</span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="project_management.php">
            <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
              <i class="ni ni-calendar-grid-58 text-primary text-sm opacity-10"></i>
            </div>
            <span class="nav-link-text ms-1">Project Management</span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="collaborative_space.php">
            <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
              <i class="ni ni-credit-card text-primary text-sm opacity-10"></i>
            </div>
            <span class="nav-link-text ms-1">Collaborative Space</span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="View/backOffice/FinancementView.php">
            <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
              <i class="ni ni-app text-primary text-sm opacity-10"></i>
            </div>
            <span class="nav-link-text ms-1">Financing Management</span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="feedback_management.php">
            <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
              <i class="ni ni-world-2 text-primary text-sm opacity-10"></i>
            </div>
            <span class="nav-link-text ms-1">Feedback Management</span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="../frontOffice/profile.php">
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="../frontOffice/logout.php">
          </a>
        </li>
      </ul>
    </div>
    </div>
  </aside>
  <main class="main-content position-relative border-radius-lg">
    <!-- Navbar -->
    <nav class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl bg-dark" id="navbarBlur" data-scroll="false">
      <div class="container-fluid py-1 px-3">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb bg-transparent mb-0 pb-0 pt-1 px-0 me-sm-6 me-5">
            <li class="breadcrumb-item text-sm"><a class="opacity-5 text-white" href="javascript:;">Pages</a></li>
            <li class="breadcrumb-item text-sm text-white active" aria-current="page">Dashboard</li>
          </ol>
          <h6 class="font-weight-bolder text-white mb-0">Dashboard</h6>
        </nav>
        <div class="collapse navbar-collapse mt-sm-0 mt-2 me-md-0 me-sm-4" id="navbar">
          <div class="ms-md-auto pe-md-3 d-flex align-items-center">
            <div class="input-group">
              <span class="input-group-text text-body"><i class="fas fa-search" aria-hidden="true"></i></span>
              <input type="text" class="form-control" placeholder="Search...">
            </div>
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
    <div class="container-fluid py-4">
      <!-- Statistics Cards -->
      <div class="row">
        <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
          <div class="card">
            <div class="card-body p-3">
              <div class="row">
                <div class="col-8">
                  <div class="numbers">
                    <p class="text-sm mb-0 text-uppercase font-weight-bold">Total Page Views</p>
                    <h5 class="font-weight-bolder"><?php echo number_format($stats['page_views']); ?></h5>
                    <p class="mb-0">
                      <span class="text-success text-sm font-weight-bolder">+<?php echo rand(1, 10); ?>%</span>
                      since yesterday
                    </p>
                  </div>
                </div>
                <div class="col-4 text-end">
                  <div class="icon icon-shape bg-gradient-primary shadow-primary text-center rounded-circle">
                    <i class="ni ni-world text-lg opacity-10" aria-hidden="true"></i>
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
                    <p class="text-sm mb-0 text-uppercase font-weight-bold">Active Sessions</p>
                    <h5 class="font-weight-bolder"><?php echo $stats['active_sessions']; ?></h5>
                    <p class="mb-0">
                      <span class="text-success text-sm font-weight-bolder">+<?php echo rand(1, 5); ?>%</span>
                      since last hour
                    </p>
                  </div>
                </div>
                <div class="col-4 text-end">
                  <div class="icon icon-shape bg-gradient-success shadow-success text-center rounded-circle">
                    <i class="ni ni-satisfied text-lg opacity-10" aria-hidden="true"></i>
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
                    <p class="text-sm mb-0 text-uppercase font-weight-bold">Avg. Session Duration</p>
                    <h5 class="font-weight-bolder"><?php echo $stats['avg_session_duration']; ?></h5>
                    <p class="mb-0">
                      <span class="text-danger text-sm font-weight-bolder">-<?php echo rand(1, 3); ?>%</span>
                      since last week
                    </p>
                  </div>
                </div>
                <div class="col-4 text-end">
                  <div class="icon icon-shape bg-gradient-warning shadow-warning text-center rounded-circle">
                    <i class="ni ni-watch-time text-lg opacity-10" aria-hidden="true"></i>
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
                    <p class="text-sm mb-0 text-uppercase font-weight-bold">Bounce Rate</p>
                    <h5 class="font-weight-bolder"><?php echo $stats['bounce_rate']; ?></h5>
                    <p class="mb-0">
                      <span class="text-danger text-sm font-weight-bolder">+<?php echo rand(1, 2); ?>%</span>
                      since last month
                    </p>
                  </div>
                </div>
                <div class="col-4 text-end">
                  <div class="icon icon-shape bg-gradient-danger shadow-danger text-center rounded-circle">
                    <i class="ni ni-chart-bar-32 text-lg opacity-10" aria-hidden="true"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Website Traffic Chart -->
      <div class="row mt-4">
        <div class="col-lg-7 mb-lg-0 mb-4">
          <div class="card z-index-2 h-100">
            <div class="card-header pb-0 pt-3 bg-transparent">
              <h6 class="text-capitalize">Website Traffic (Last 30 Days)</h6>
              <p class="text-sm mb-0">
                <i class="fa fa-arrow-up text-success"></i>
                <span class="font-weight-bold">+15%</span> increase in traffic
              </p>
            </div>
            <div class="card-body p-3">
              <div class="chart">
                <canvas id="trafficChart" class="chart-canvas" height="300"></canvas>
              </div>
            </div>
          </div>
        </div>
        <div class="col-lg-5">
          <div class="card z-index-2 h-100">
            <div class="card-header pb-0 pt-3 bg-transparent">
              <h6 class="text-capitalize">Top 5 Most Visited Pages</h6>
            </div>
            <div class="card-body p-3">
              <ul class="list-group">
                <?php foreach ($topPages as $page): ?>
                  <li class="list-group-item border-0 d-flex justify-content-between ps-0 mb-2 border-radius-lg">
                    <div class="d-flex align-items-center">
                      <div class="icon icon-shape icon-sm me-3 bg-gradient-dark shadow text-center">
                        <i class="ni ni-world text-white opacity-10"></i>
                      </div>
                      <div class="d-flex flex-column">
                        <h6 class="mb-1 text-dark text-sm"><?php echo htmlspecialchars($page['name']); ?></h6>
                        <span class="text-xs"><?php echo number_format($page['views']); ?> views</span>
                      </div>
                    </div>
                  </li>
                <?php endforeach; ?>
              </ul>
            </div>
          </div>
        </div>
      </div>

      <!-- User Activity Heatmap -->
      <div class="row mt-4">
        <div class="col-lg-7 mb-lg-0 mb-4">
          <div class="card">
            <div class="card-header pb-0 p-3">
              <h6 class="mb-2">User Activity Heatmap (Today)</h6>
            </div>
            <div class="table-responsive">
              <table class="table align-items-center">
                <thead>
                  <tr>
                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Time Slot</th>
                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Activity Level</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($heatmapData as $timeSlot => $activity): ?>
                    <tr>
                      <td>
                        <div class="d-flex px-2 py-1">
                          <div class="ms-3">
                            <h6 class="text-sm mb-0"><?php echo htmlspecialchars($timeSlot); ?></h6>
                          </div>
                        </div>
                      </td>
                      <td>
                        <div class="heatmap-cell" style="background-color: <?php echo $activity > 500 ? '#dc3545' : ($activity > 300 ? '#ffc107' : '#28a745'); ?>">
                          <?php echo $activity; ?> users
                        </div>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
        <div class="col-lg-5">
          <div class="card">
            <div class="card-header pb-0 p-3">
              <h6 class="mb-0">System Notifications</h6>
            </div>
            <div class="card-body p-3">
              <ul class="list-group">
                <?php foreach ($notifications as $notification): ?>
                  <li class="list-group-item border-0 d-flex justify-content-between ps-0 mb-2 border-radius-lg">
                    <div class="d-flex align-items-center">
                      <div class="icon icon-shape icon-sm me-3 bg-gradient-info shadow text-center">
                        <i class="ni ni-bell-55 text-white opacity-10"></i>
                      </div>
                      <div class="d-flex flex-column">
                        <h6 class="mb-1 text-dark text-sm"><?php echo htmlspecialchars($notification['message']); ?></h6>
                        <span class="text-xs"><?php echo htmlspecialchars($notification['time']); ?></span>
                      </div>
                    </div>
                  </li>
                <?php endforeach; ?>
              </ul>
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
          <p class="text-sm">Choose between 2 different sidenav types.</p>
        </div>
        <div class="d-flex">
          <button class="btn bg-gradient-primary w-100 px-3 mb-2 active me-2" data-class="bg-white" onclick="sidebarType(this)">White</button>
          <button class="btn bg-gradient-primary w-100 px-3 mb-2" data-class="bg-default" onclick="sidebarType(this)">Dark</button>
        </div>
        <div class="d-flex my-3">
          <h6 class="mb-0">Navbar Fixed</h6>
          <div class="form-check form-switch ps-0 ms-auto my-auto">
            <input class="form-check-input mt-1 ms-auto" type="checkbox" id="navbarFixed" onclick="navbarFixed(this)">
          </div>
        </div>
        <hr class="horizontal dark my-sm-4">
        <div class="mt-2 mb-5 d-flex" id="darkModeToggle">
          <h6 class="mb-0">Light / Dark</h6>
          <div class="form-check form-switch ps-0 ms-auto my-auto">
            <input class="form-check-input mt-1 ms-auto" type="checkbox" id="dark-version" onclick="darkMode(this)">
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- Core JS Files -->
  <script src="assets2/js/core/popper.min.js"></script>
  <script src="assets2/js/core/bootstrap.min.js"></script>
  <script src="assets2/js/plugins/perfect-scrollbar.min.js"></script>
  <script src="assets2/js/plugins/smooth-scrollbar.min.js"></script>
  <script src="assets2/js/argon-dashboard.min.js?v=2.1.0"></script>
  <script>
    // Website Traffic Chart
    document.addEventListener('DOMContentLoaded', function () {
      const ctx = document.getElementById('trafficChart').getContext('2d');
      var gradientStroke = ctx.createLinearGradient(0, 230, 0, 50);
      gradientStroke.addColorStop(1, 'rgba(94, 114, 228, 0.2)');
      gradientStroke.addColorStop(0.2, 'rgba(94, 114, 228, 0.0)');
      gradientStroke.addColorStop(0, 'rgba(94, 114, 228, 0)');
      new Chart(ctx, {
        type: 'line',
        data: {
          labels: <?php echo json_encode($trafficData['labels']); ?>,
          datasets: [{
            label: 'Page Views',
            data: <?php echo json_encode($trafficData['data']); ?>,
            fill: true,
            backgroundColor: gradientStroke,
            borderColor: '#5e72e4',
            borderWidth: 3,
            pointRadius: 0,
            tension: 0.4
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              display: false
            }
          },
          interaction: {
            intersect: false,
            mode: 'index',
          },
          scales: {
            y: {
              grid: {
                drawBorder: false,
                display: true,
                drawOnChartArea: true,
                drawTicks: false,
                borderDash: [5, 5]
              },
              ticks: {
                display: true,
                padding: 10,
                color: '#fbfbfb',
                font: {
                  size: 11,
                  family: "Open Sans",
                  style: 'normal',
                  lineHeight: 2
                },
              }
            },
            x: {
              grid: {
                drawBorder: false,
                display: false,
                drawOnChartArea: false,
                drawTicks: false,
                borderDash: [5, 5]
              },
              ticks: {
                display: true,
                color: '#ccc',
                padding: 20,
                font: {
                  size: 11,
                  family: "Open Sans",
                  style: 'normal',
                  lineHeight: 2
                },
              }
            },
          },
        },
      });
    });
  </script>
  <script>
    var win = navigator.platform.indexOf('Win') > -1;
    if (win && document.querySelector('#sidenav-scrollbar')) {
      var options = {
        damping: '0.5'
      }
      Scrollbar.init(document.querySelector('#sidenav-scrollbar'), options);
    }
  </script>
</body>

</html>
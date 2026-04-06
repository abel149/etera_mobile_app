<!doctype html>
<html lang="en" class="light">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    	<!--favicon-->
	<link rel="icon" href="{{asset('favicon.ico')}}" type="image/x-icon"/>
	<link rel="icon" href="{{asset('assets/images/transparent.svg')}}" type="image/jpeg"/>
    <!--plugins-->
    <link rel="stylesheet" href="assets/plugins/notifications/css/lobibox.min.css" />
    <link href="assets/plugins/vectormap/jquery-jvectormap-2.0.2.css" rel="stylesheet"/>
    <link href="assets/plugins/simplebar/css/simplebar.css" rel="stylesheet" />
    <link href="assets/plugins/Drag-And-Drop/dist/imageuploadify.min.css" rel="stylesheet" />
    <link href="assets/plugins/perfect-scrollbar/css/perfect-scrollbar.css" rel="stylesheet" />
    <link href="assets/plugins/metismenu/css/metisMenu.min.css" rel="stylesheet"/>
    <!-- loader-->
    <link href="assets/css/pace.min.css" rel="stylesheet"/>
    <script src="assets/js/pace.min.js"></script>
    <!-- Bootstrap CSS -->
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/bootstrap-extended.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&display=swap" rel="stylesheet">
    <link href="assets/css/app.css" rel="stylesheet">
    <link href="assets/css/icons.css" rel="stylesheet">
    <!-- Theme Style CSS -->
    <link rel="stylesheet" href="assets/css/dark-theme.css"/>
    <link rel="stylesheet" href="assets/css/semi-dark.css"/>
    <link rel="stylesheet" href="assets/css/header-colors.css"/>
    @livewireStyles

    <style type="text/css">
    .tbl td:last-child {
      white-space: nowrap;
      width: 1%; /* Makes the column take as little space as possible based on content */
    }
</style>
    <title> E-Tera</title>
</head>

<body>
    <!-- Sidebar -->
  <div class="d-flex" id="wrapper">
    <div class="bg-light border-right" id="sidebar-wrapper">
      <div class="sidebar-heading">Customer Dashboard</div>
      <div class="list-group list-group-flush">
        <a href="#orders" class="list-group-item list-group-item-action bg-light">My Orders</a>
        <a href="#profile" class="list-group-item list-group-item-action bg-light">Profile</a>
        <a href="#support" class="list-group-item list-group-item-action bg-light">Support</a>
      </div>
    </div>

    <!-- Page Content -->
    <div id="page-content-wrapper">
      <nav class="navbar navbar-expand-lg navbar-light bg-light border-bottom">
        <button class="btn btn-primary" id="menu-toggle">Toggle Menu</button>
        <ul class="navbar-nav ml-auto">
          <li class="nav-item">
            <a class="nav-link" href="#profile">Profile</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="#logout">Logout</a>
          </li>
        </ul>
      </nav>

      <div class="container-fluid">
        <h1 class="mt-4">Welcome, [Customer Name]</h1>
        
        <!-- Orders Section -->
        <section id="orders">
          <h2>Recent Orders</h2>
          <table class="table table-bordered">
            <thead>
              <tr>
                <th>Order ID</th>
                <th>Status</th>
                <th>Date</th>
              </tr>
            </thead>
            <tbody>
              <!-- Example Order -->
              <tr>
                <td>#12345</td>
                <td>Delivered</td>
                <td>2024-10-01</td>
              </tr>
              <!-- Add more rows as needed -->
            </tbody>
          </table>
        </section>

        <!-- Profile Section -->
        <section id="profile">
          <h2>Profile</h2>
          <!-- Display profile information -->
          <p>Email: customer@example.com</p>
          <button class="btn btn-primary">Update Profile</button>
        </section>

        <!-- Support Section -->
        <section id="support">
          <h2>Support Tickets</h2>
          <button class="btn btn-primary">Create New Ticket</button>
        </section>
      </div>
    </div>
  </div>
    <!--end switcher-->
    <!-- Bootstrap JS -->
    <script src="assets/js/bootstrap.bundle.min.js"></script> 
    <!--plugins-->
    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/plugins/simplebar/js/simplebar.min.js"></script>
    <script src="assets/plugins/metismenu/js/metisMenu.min.js"></script>
    <script src="assets/plugins/perfect-scrollbar/js/perfect-scrollbar.js"></script>
    <script src="assets/plugins/Drag-And-Drop/dist/imageuploadify.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="assets/plugins/select2/js/select2-custom.js"></script>
    <script>
        $(document).ready(function () {
            $('#image-uploadify').imageuploadify();
        })
    </script>
    <!-- Vector map JavaScript -->
    <script src="assets/plugins/vectormap/jquery-jvectormap-2.0.2.min.js"></script>
    <script src="assets/plugins/vectormap/jquery-jvectormap-world-mill-en.js"></script>
    <script src="assets/plugins/chartjs/js/chart.js"></script>
    <script src="assets/plugins/sparkline-charts/jquery.sparkline.min.js"></script>
    <script src="assets/js/index.js"></script>
    <!--notification js -->
    <script src="assets/plugins/notifications/js/lobibox.min.js"></script>
    <script src="assets/plugins/notifications/js/notifications.min.js"></script>
    <script src="assets/plugins/notifications/js/notification-custom-script.js"></script>
    <!--app JS-->
    <script src="assets/js/app.js"></script>
    <script>
          $(function () {
    
            $("input[data-bootstrap-switch]").each(function(){
              $(this).bootstrapSwitch('state', $(this).prop('checked'));
            })

          })
        new PerfectScrollbar('.dashboard-top-countries');
    </script>
    @livewireScripts

</body>

</html>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voicecatch Reports</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f3f3f3;
            /* background-color: hsl(50, 33%, 25%); */
            /* background-image: url('background.jpg'); */
        }

        .navbar {
            background-color: #007bff;
            padding: 1rem 0;
        }

        .navbar-brand,
        .nav-link {
            color: #ffffff !important;
            font-weight: bold;
        }

        .navbar-toggler-icon {
            background-color: #ffffff;
        }

        .navbar-nav {
            margin-left: auto;
        }

        .nav-item {
            margin-right: 15px;
        }

        .container {
            margin-top: 20px;
        }

        .jumbotron {
            background-color: #007bff;
            color: #ffffff;
            padding: ;
            margin-bottom: 30px;
        }

        .jumbotron h2 {
            font-size: 2.5rem;
        }

        .jumbotron p {
            font-size: 1.2rem;
            line-height: 1.6;
        }

        /* Adjusted tab colors for better visibility */
        .nav-item.active a {
            background-color: #ffffff;
            color: #007bff !important;
        }

        /* For pagination */
        .pagination-button {
            background-color: #007bff;
            color: white;
            border: 1px solid #007bff;
            padding: 8px 16px;
            text-decoration: none;
            margin: 0 5px;
            cursor: pointer;
        }

        .pagination-button.disabled {
            background-color: #ccc;
            border: 1px solid #ccc;
            cursor: not-allowed;
        }

        .pagination-buttons {
            display: flex;
            justify-content: space-between;
            margin-top: 10px;
        }

        /* NEW ADDED FOR RIGHTSIDE DROP DOWN */
        .dropdown-submenu {
            position: relative;
        }

        .dropdown-submenu .dropdown-menu {
            top: 0;
            left: 100%;
            margin-top: -1px;
        }

        .dropdown-submenu:hover .dropdown-menu {
            display: block;
        }

        .dropdown-submenu:hover>a:after {
            border-left-color: #fff;
        }

        .dropdown-submenu.pull-left {
            float: none;
        }

        .dropdown-submenu.pull-left .dropdown-menu {
            left: -100%;
            margin-left: 10px;
            border-radius: 6px;
        }
    </style>


    <!-- DROPDOWN WITH MOUSE HOVER -->
    <script>
        function showDropdown(element) {
            var dropdownMenu = element.querySelector('.dropdown-menu');
            if (dropdownMenu) {
                dropdownMenu.classList.add('show');
            }
        }

        function hideDropdown(element) {
            var dropdownMenu = element.querySelector('.dropdown-menu');
            if (dropdownMenu) {
                dropdownMenu.classList.remove('show');
            }
        }

        document.querySelectorAll('.nav-item.dropdown').forEach(function(dropdown) {
            dropdown.addEventListener('mouseenter', function() {
                showDropdown(this);
            });

            dropdown.addEventListener('mouseleave', function() {
                hideDropdown(this);
            });

            var dropdownMenu = dropdown.querySelector('.dropdown-menu');

            dropdownMenu.addEventListener('mouseenter', function() {
                showDropdown(dropdown);
            });

            dropdownMenu.addEventListener('mouseleave', function() {
                hideDropdown(dropdown);
            });
        });
    </script>

</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">Voicecatch Reports</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownReport" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            Calls Statistics Report
                        </a>
                        <div class="dropdown-menu" aria-labelledby="navbarDropdownReport">
                            <a class="dropdown-item <?php if (basename($_SERVER['PHP_SELF']) == 'og_callstatistics.php')
                                                        echo 'active'; ?>" href="og_callstatistics.php">Outbound Call Statistics</a>
                            <a class="dropdown-item <?php if (basename($_SERVER['PHP_SELF']) == 'in_callstatistics.php')
                                                        echo 'active'; ?>" href="in_callstatistics.php">Incoming Call Statistics</a>
                            <!-- Add more dropdown items for other reports -->
                        </div>
                    </li>


                    <!-- New dropdown for 'Report' -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownReport" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            Calls Report
                        </a>
                        <div class="dropdown-menu" aria-labelledby="navbarDropdownReport">
                            <a class="dropdown-item <?php if (basename($_SERVER['PHP_SELF']) == 'outbound.php')
                                                        echo 'active'; ?>" href="outbound.php">Outbound Report</a>
                            <a class="dropdown-item <?php if (basename($_SERVER['PHP_SELF']) == 'inbound.php')
                                                        echo 'active'; ?>" href="inbound.php">Inbound Report</a>
                            <a class="dropdown-item <?php if (basename($_SERVER['PHP_SELF']) == 'recording.php')
                                                        echo 'active'; ?>" href="recording.php">Recording Report</a>
                            <a class="dropdown-item <?php if (basename($_SERVER['PHP_SELF']) == 'user_report.php')
                                                        echo 'active'; ?>" href="user_report.php">User Report</a>
                            <a class="dropdown-item <?php if (basename($_SERVER['PHP_SELF']) == 'Agent_Time_Export.php')
                                                        echo 'active'; ?>" href="Agent_Time_Export.php">Agent Time Export</a>
                            <a class="dropdown-item <?php if (basename($_SERVER['PHP_SELF']) == 'export.php')
                                                        echo 'active'; ?>" href="export.php">Export Report</a>
                            <!-- Add more dropdown items for other reports -->
                        </div>
                    </li>
                    <!-- IORA REPORTS -->
                    <li class="nav-item dropdown" onmouseover="showDropdown(this)">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownReport" role="button" aria-haspopup="true" aria-expanded="false">
                            iORA Report
                        </a>
                        <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdownReport">
                            <a class="dropdown-item <?php if (basename($_SERVER['PHP_SELF']) == 'iora_report.php')
                                                        echo 'active'; ?>" href="iora_report.php">All App Report</a>
                            <!-- Main iORA Report -->
                            <div class="dropdown-divider"></div>
                            <!-- Divider between main report and sub-menu -->
                            <div class="dropdown-submenu">
                                <a class="dropdown-item" href="javascript:void(0);">App Details Report</a>
                                <!-- Main dropdown item with sub-menu -->
                                <div class="dropdown-menu dropdown-menu-end">
                                    <!-- Sub-menu items -->
                                    <a class="dropdown-item <?php if (basename($_SERVER['PHP_SELF']) == 'binkheti_report.php')
                                                                echo 'active'; ?>" href="binkheti_report.php">Binkheti Report</a>
                                    <a class="dropdown-item <?php if (basename($_SERVER['PHP_SELF']) == 'hayati_report.php')
                                                                echo 'active'; ?>" href="hayati_report.php">Hayati Report</a>
                                    <a class="dropdown-item <?php if (basename($_SERVER['PHP_SELF']) == 'varsayi_report.php')
                                                                echo 'active'; ?>" href="varsayi_report.php">Varsayi Report</a>
                                    <a class="dropdown-item <?php if (basename($_SERVER['PHP_SELF']) == 'khedut_report.php')
                                                                echo 'active'; ?>" href="khedut_report.php">Khedut Export</a>
                                    <a class="dropdown-item <?php if (basename($_SERVER['PHP_SELF']) == 'iora_detail_report.php')
                                                                echo 'active'; ?>" href="iora_detail_report.php">Detail Export</a>
                                    <!-- Add more sub-menu items as needed -->
                                </div>
                            </div>
                            <!-- End of sub-menu -->
                            <div class="dropdown-divider"></div>
                            <!-- Divider between main report and sub-menu -->
                            <div class="dropdown-submenu">
                                <a class="dropdown-item" href="javascript:void(0);">Summary Report</a>
                                <!-- Main dropdown item with sub-menu -->
                                <div class="dropdown-menu dropdown-menu-end">
                                    <!-- Sub-menu items -->
                                    <!-- iora_og_callstatistics.php -->
                                    <a class="dropdown-item <?php if (basename($_SERVER['PHP_SELF']) == 'iora_og_callstatistics.php')
                                                        echo 'active'; ?>" href="iora_og_callstatistics.php">OG Call Statisctics</a>
                                    <a class="dropdown-item <?php if (basename($_SERVER['PHP_SELF']) == 'iora_call_summary.php')
                                                        echo 'active'; ?>" href="iora_call_summary.php">App Call Summary</a>
                                    <!-- Add more sub-menu items as needed -->
                                </div>
                            </div>


                            <!-- Add more dropdown items for other reports -->
                        </div>
                    </li>
                    <!-- iORA REPORT END -->
                    <li class="nav-item <?php if (basename($_SERVER['PHP_SELF']) == 'list_users.php')
                                            echo 'active'; ?>">
                        <a class="nav-link" href="list_users.php">Users</a>
                    </li>
                    <li class="nav-item <?php if (basename($_SERVER['PHP_SELF']) == 'upload.php')
                                            echo 'active'; ?>">
                        <a class="nav-link" href="upload.php">Upload File</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownReport" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            Automate Report
                        </a>
                        <div class="dropdown-menu" aria-labelledby="navbarDropdownReport">
                            <a class="dropdown-item <?php if (basename($_SERVER['PHP_SELF']) == 'email_config.php')
                                                        echo 'active'; ?>" href="email_config.php">Email Configration</a>
                            <a class="dropdown-item <?php if (basename($_SERVER['PHP_SELF']) == 'email_report.php')
                                                        echo 'active'; ?>" href="email_report.php">Email Report Config</a>
                            <!-- <a class="dropdown-item <?php if (basename($_SERVER['PHP_SELF']) == 'Report2.php')
                                                                echo 'active'; ?>"
                                href="Report2.php">Report 2</a>
                            <a class="dropdown-item <?php if (basename($_SERVER['PHP_SELF']) == 'Report3.php')
                                                        echo 'active'; ?>"
                                href="Report3.php">Report 3</a> -->

                            <!-- Add more dropdown items for other reports email_config.php-->
                        </div>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Logout</a>
                    </li>

                </ul>
            </div>
        </div>
    </nav>
    <div class=" ">
        <?php if (basename($_SERVER['PHP_SELF']) == 'og_callstatistics.php') : ?>

        <?php endif; ?>
    </div>

    <div class="container">
        <!-- Add more sections for other reports -->
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js"></script>
</body>

</html>
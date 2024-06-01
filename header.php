<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voicecatch Reports</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f3f3f3;
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

        /* Adjusted tab colors for better visibility */
        .nav-item.active a {
            background-color: #ffffff;
            color: #007bff !important;
        }

        /* For dropdowns */
        .dropdown-menu {
            transition: visibility 0.15s, opacity 0.15s linear;
        }

        .dropdown-menu.show {
            visibility: visible;
            opacity: 1;
        }

        /* NEW ADDED FOR RIGHT-SIDE DROPDOWN */
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
                    <!-- Calls Statistics Report -->
                    <li class="nav-item dropdown" onmouseover="showDropdown(this)" onmouseleave="hideDropdown(this)">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownReport" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            Calls Statistics Report
                        </a>
                        <div class="dropdown-menu" aria-labelledby="navbarDropdownReport">
                            <a class="dropdown-item <?php if (basename($_SERVER['PHP_SELF']) == 'og_callstatistics.php')
                                                        echo 'active'; ?>" href="og_callstatistics.php">Outbound Call Statistics</a>
                            <a class="dropdown-item <?php if (basename($_SERVER['PHP_SELF']) == 'in_callstatistics.php')
                                                        echo 'active'; ?>" href="in_callstatistics.php">Incoming Call Statistics</a>
                        </div>
                    </li>

                    <!-- Calls Report -->
                    <li class="nav-item dropdown" onmouseover="showDropdown(this)" onmouseleave="hideDropdown(this)">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownReport" role="button" aria-haspopup="true" aria-expanded="false">
                            Calls Report
                        </a>
                        <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdownReport">
                            <a class="dropdown-item <?php if (basename($_SERVER['PHP_SELF']) == 'outbound.php')
                                                        echo 'active'; ?>" href="outbound.php">Outbound Report</a>
                            <a class="dropdown-item <?php if (basename($_SERVER['PHP_SELF']) == 'inbound.php')
                                                        echo 'active'; ?>" href="inbound.php">Inbound Report</a>
                            <a class="dropdown-item <?php if (basename($_SERVER['PHP_SELF']) == 'recording.php')
                                                        echo 'active'; ?>" href="recording.php">Recording Report</a>
                            <a class="dropdown-item <?php if (basename($_SERVER['PHP_SELF']) == 'user_report.php')
                                                        echo 'active'; ?>" href="user_report.php">User Report</a>
                            <!-- <a class="dropdown-item <?php if (basename($_SERVER['PHP_SELF']) == 'Agent_Time_Export.php')
                                                        echo 'active'; ?>" href="Agent_Time_Export.php">Agent Time Export</a> -->
                            <a class="dropdown-item <?php if (basename($_SERVER['PHP_SELF']) == 'export.php')
                                                        echo 'active'; ?>" href="export.php">Export Report</a>
                        </div>
                    </li>

                    <!-- iORA Report -->
                    <li class="nav-item dropdown" onmouseover="showDropdown(this)" onmouseleave="hideDropdown(this)">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownReport" role="button" aria-haspopup="true" aria-expanded="false">
                            ફીડબેક રીપોર્ટ 
                        </a>
                        <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdownReport">
                            <a class="dropdown-item <?php if (basename($_SERVER['PHP_SELF']) == 'iora_report.php')
                                                        echo 'active'; ?>" href="iora_report.php">તમામ અરજીનો રીપોર્ટ</a>
                            <div class="dropdown-divider"></div>
                            <div class="dropdown-submenu">
                                <a class="dropdown-item" href="javascript:void(0);">અરજીના પ્રકાર મુજબનો રીપોર્ટ </a>
                                <div class="dropdown-menu dropdown-menu-end">
                                    <a class="dropdown-item <?php if (basename($_SERVER['PHP_SELF']) == 'binkheti_report.php')
                                                                echo 'active'; ?>" href="binkheti_report.php">બિનખેતી રીપોર્ટ</a>
                                    <a class="dropdown-item <?php if (basename($_SERVER['PHP_SELF']) == 'hayati_report.php')
                                                                echo 'active'; ?>" href="hayati_report.php">હયાતીમા હક દાખલ રીપોર્ટ</a>
                                    <a class="dropdown-item <?php if (basename($_SERVER['PHP_SELF']) == 'varsayi_report.php')
                                                                echo 'active'; ?>" href="varsayi_report.php">વારસાઇ રીપોર્ટ</a>
                                    <a class="dropdown-item <?php if (basename($_SERVER['PHP_SELF']) == 'khedut_report.php')
                                                                echo 'active'; ?>" href="khedut_report.php">ખેડૂત ખરાઇ રીપોર્ટ</a>
                                    <a class="dropdown-item <?php if (basename($_SERVER['PHP_SELF']) == 'iora_detail_report.php')
                                                                echo 'active'; ?>" href="iora_detail_report.php">અરજીવાઇઝ વિગત </a>
                                </div>
                            </div>
                            <div class="dropdown-divider"></div>
                            <div class="dropdown-submenu">
                                <a class="dropdown-item" href="javascript:void(0);">સમરી રીપોર્ટ</a>
                                <div class="dropdown-menu dropdown-menu-end">
                                    <a class="dropdown-item <?php if (basename($_SERVER['PHP_SELF']) == 'iora_og_callstatistics.php')
                                                        echo 'active'; ?>" href="iora_og_callstatistics.php">એજન્ટ વાઇઝ રીપોર્ટ</a>
                                    <a class="dropdown-item <?php if (basename($_SERVER['PHP_SELF']) == 'iora_call_summary.php')
                                                        echo 'active'; ?>" href="iora_call_summary.php">જિલ્લાવાઇઝ સમરી</a>
                                </div>
                            </div>
                        </div>
                    </li>

                    <!-- Other menu items -->
                    <li class="nav-item <?php if (basename($_SERVER['PHP_SELF']) == 'list_users.php')
                                            echo 'active'; ?>">
                        <a class="nav-link" href="list_users.php">Users</a>
                    </li>
                    <li class="nav-item <?php if (basename($_SERVER['PHP_SELF']) == 'upload.php')
                                            echo 'active'; ?>">
                        <a class="nav-link" href="upload.php">Upload File</a>
                    </li>
                    <li class="nav-item dropdown" onmouseover="showDropdown(this)" onmouseleave="hideDropdown(this)">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownReport" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            Automate Report
                        </a>
                        <div class="dropdown-menu" aria-labelledby="navbarDropdownReport">
                            <a class="dropdown-item <?php if (basename($_SERVER['PHP_SELF']) == 'email_config.php')
                                                        echo 'active'; ?>" href="email_config.php">Email Configuration</a>
                            <a class="dropdown-item <?php if (basename($_SERVER['PHP_SELF']) == 'email_report.php')
                                                        echo 'active'; ?>" href="email_report.php">Email Report Config</a>
                        </div>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    
    <!-- Content Section -->
    <div class="container">
        <!-- Add content sections here if needed -->
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js"></script>
    <script>
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
    </script>
</body>

</html>

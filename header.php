<?php

function navbar_link($title, $url, $use_li_class = false)
{
    $active = basename($_SERVER['PHP_SELF']) == $url ? 'active' : '';
    if ($use_li_class) {
        return "<li class='nav-item $active'><a class='nav-link' href='$url'>$title</a></li>";
    } else {
        return "<a class='dropdown-item $active' href='$url'>$title</a>";
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($title) ? $title . '-' : ''; ?> Voicecatch Reports</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Segoe+UI&display=swap" rel="stylesheet">
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
    <nav class="navbar navbar-expand-lg navbar-light bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">voice&centerdot;catch</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <!-- Calls Statistics Report -->
                    <li class="nav-item dropdown" onmouseover="showDropdown(this)" onmouseleave="hideDropdown(this)">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownReport" role="button"
                            data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            Calls Statistics Report
                        </a>
                        <div class="dropdown-menu" aria-labelledby="navbarDropdownReport">
                            <?php echo navbar_link('Outbound Call Statistics', 'og_callstatistics.php'); ?>
                            <?php echo navbar_link('Incoming Call Statistics', 'in_callstatistics.php'); ?>
                        </div>
                    </li>

                    <!-- Calls Report -->
                    <li class="nav-item dropdown" onmouseover="showDropdown(this)" onmouseleave="hideDropdown(this)">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownReport" role="button"
                            aria-haspopup="true" aria-expanded="false">
                            Calls Report
                        </a>
                        <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdownReport">
                            <?php echo navbar_link('Outbound Report', 'outbound.php'); ?>
                            <?php echo navbar_link('Inbound Report', 'inbound.php'); ?>
                            <?php echo navbar_link('Recording Report', 'recording.php'); ?>
                            <?php echo navbar_link('User Report', 'user_report.php'); ?>
                            <?php echo navbar_link('Export Report', 'export.php'); ?>
                        </div>
                    </li>

                    <!-- iORA Report -->
                    <li class="nav-item dropdown" onmouseover="showDropdown(this)" onmouseleave="hideDropdown(this)">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownReport" role="button"
                            aria-haspopup="true" aria-expanded="false">
                            ફીડબેક રીપોર્ટ
                        </a>
                        <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdownReport">
                            <?php echo navbar_link('તમામ અરજીનો રીપોર્ટ', 'iora_report.php'); ?>
                            <div class="dropdown-divider"></div>
                            <div class="dropdown-submenu">
                                <a class="dropdown-item" href="javascript:void(0);">અરજીના પ્રકાર મુજબનો રીપોર્ટ </a>
                                <div class="dropdown-menu dropdown-menu-end">
                                    <?php echo navbar_link('બિનખેતી રીપોર્ટ', 'binkheti_report.php'); ?>
                                    <?php echo navbar_link('હયાતીમા હક દાખલ રીપોર્ટ', 'hayati_report.php'); ?>
                                    <?php echo navbar_link('વારસાઇ રીપોર્ટ', 'varsayi_report.php'); ?>
                                    <?php echo navbar_link('ખેડૂત ખરાઇ રીપોર્ટ', 'khedut_report.php'); ?>
                                    <?php echo navbar_link('અરજીવાઇઝ વિગત', 'iora_detail_report.php'); ?>
                                </div>
                            </div>
                            <div class="dropdown-divider"></div>
                            <div class="dropdown-submenu">
                                <a class="dropdown-item" href="javascript:void(0);">સમરી રીપોર્ટ</a>
                                <div class="dropdown-menu dropdown-menu-end">
                                    <?php echo navbar_link('એજન્ટ વાઇઝ રીપોર્ટ', 'iora_og_callstatistics.php'); ?>
                                    <?php echo navbar_link('જિલ્લાવાઇઝ સમરી', 'iora_call_summary.php'); ?>
                                </div>
                            </div>
                        </div>
                    </li>

                    <!-- Other menu items -->

                    <?php echo navbar_link('Users', 'list_users.php', true); ?>
                    <?php echo navbar_link('Upload File', 'upload.php', true); ?>

                    <li class="nav-item dropdown" onmouseover="showDropdown(this)" onmouseleave="hideDropdown(this)">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownReport" role="button"
                            data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            Automate Report
                        </a>
                        <div class="dropdown-menu" aria-labelledby="navbarDropdownReport">
                            <?php echo navbar_link('Email Configuration', 'email_config.php'); ?>
                            <?php echo navbar_link('Email Report Config', 'email_report.php'); ?>
                        </div>
                    </li>
                </ul>
                <ul class="d-flex navbar-nav ms-auto">
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
        document.querySelectorAll('.nav-item.dropdown').forEach(function (dropdown) {
            dropdown.addEventListener('mouseenter', function () {
                showDropdown(this);
            });

            dropdown.addEventListener('mouseleave', function () {
                hideDropdown(this);
            });

            var dropdownMenu = dropdown.querySelector('.dropdown-menu');

            dropdownMenu.addEventListener('mouseenter', function () {
                showDropdown(dropdown);
            });

            dropdownMenu.addEventListener('mouseleave', function () {
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
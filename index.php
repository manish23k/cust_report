<?php
session_start(); // Start the session on each page

include "config.php"; // Include the database configuration

// Check if the user is already logged in and redirect to home.php
if (isset($_SESSION["username"])) {
    header("Location: og_callstatistics.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"];
    $password = $_POST["password"];

    // You should implement proper validation and security here
    // For simplicity, we're not hashing the password in this example.

    $query = "SELECT * FROM vicidial_users WHERE user='$username' AND pass='$password'";
    $result = $conn->query($query);

    if ($result->num_rows == 1) {
        $_SESSION["username"] = $username; // Store user information in the session
        header("Location: og_callstatistics.php"); // Redirect to the dashboard
        exit;
    } else {
        $error = "Invalid username or password";
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Login</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css">
    <style>
        /* Custom styles for the navigation bar */
        .navbar {
            background-color: #007BFF;
            /* Background color */
        }

        .navbar-brand {
            color: #fff;
            /* Text color */
        }

        .navbar-brand:hover {
            color: #fff;
            /* Text color on hover */
        }

        .navbar-nav .nav-link {
            color: #333;
            /* Text color */
        }

        .navbar-nav .nav-link:hover {
            color: #007BFF;
            /* Text color on hover */
        }

        .navbar-nav .nav-item.active {
            background-color: #007BFF;
            /* Background color for active tab */
        }

        /* Custom styles for the login form */
        .login-container {
            max-width: 400px;
            margin: 0 auto;
            padding: 50px;
            border: 1px solid #ccc;
            border-radius: 10px;
            background-color: #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        /* Center the login box vertically and horizontally */
        .center-container {
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 100vh;
}

.login-container {
    max-width: 400px;
    margin: 0 auto;
    padding: 50px;
    border: 1px solid #ccc;
    border-radius: 10px;
    background-color: rgba(255, 255, 255, 0); /* Adjust the alpha value for transparency */
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
}

        body {

            /* background-image: url('your-high-resolution-image.jpg'); */
            background-image: url('login.jpg');

            background-size: cover;

            background-position: center;

        }

        .btn-center {
            display: flex;
            justify-content: center;
        }

        .btn-primary {

            background-color: #007BFF;
            /* Background color */

            border-color: #007BFF;
            /* Border color */

            border-radius: 10px;
            /* Rounded corners */

            transition: background-color 0.3s;
            /* Smooth hover effect */

        }


        .btn-primary:hover {

            background-color: #0056b3;
            /* Background color on hover */

        }

        .form-group label {

            font-size: 16px;
            /* Adjust the font size */

            font-weight: 600;
            /* Adjust the font weight */

        }
    </style>
</head>

<body>
    <div class="center-container">
        <div class="login-container">
            <h2 class="text-center">Voice Catch</h2>
            <h2 class="text-center">Login</h2>
            <form method="post">
                <div class="form-group">
                    <label for="username">Username:</label>
                    <input type="text" class="form-control" id="username" name="username" required>
                </div>
                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <div class="form-group"> <!-- Line break above the button -->
                    &nbsp; <!-- A non-breaking space to create some space between elements -->
                </div>
                <div class="btn-center"> <!-- Centered button container -->
                    <button type="submit" class="btn btn-primary">Login</button>
                </div>
            </form>
            <?php if (isset($error)) {
                echo "<div class='text-danger text-center mt-3'>$error</div>";
            } ?>
        </div>
    </div>
</body>

</html>


<?php include "footer.php"; ?>
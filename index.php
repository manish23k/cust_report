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

    // Check if the username and password are correct
    // using a prepared statement to prevent SQL injection
    // [TODO: implement a more secure password hashing algorithm]

    $query = "select * from vicidial_users where user=? and pass=?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$username, $password]);
    $user = $stmt->fetch();
    if ($user) {
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
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            /* Use the Roboto font */
            background-color: #f8f9fa;
            /* Set the background color */
        }

        /* Custom styles for the login form */
        .login-container {
            max-width: 500px;
            margin: 0 auto;
            border-radius: 20px;
            background-color: rgb(211, 211, 211);
            filter: drop-shadow(8px 8px 10px black);

        }

        /* Center the login box vertically and horizontally */
        .center-container {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background-image: url('login.jpg');
            background-size: cover;
            background-position: center;
            filter: grayscale(50%);
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
            <div class="card rounded-3">
                <div class="card-header">
                    <h2>voice&centerdot;catch</h2>
                </div>
                <div class="card-body">
                    <h5 class="card-title">Login</h5>
                    <p class="card-text">Please enter your username and password to login.</p>
                    <form method="post">
                        <div class="form-group mb-3">
                            <label class="form-label" for="username">Username:</label>
                            <input type="text" class="form-control" id="username" name="username" required>
                            <small class="form-text text-muted">Enter your username.</small>
                        </div>
                        <div class="form-group mb-3">
                            <label class="form-label" for="password">Password:</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                            <small class="form-text text-muted">Enter your password.</small>
                        </div>
                        <button type="submit" class="btn btn-primary">Login</button>
                    </form>
                    <?php if (isset($error)) {
                        echo "<div class='text-danger text-center mt-3'>$error</div>";
                    } ?>
                </div>
            </div>
        </div>
    </div>
</body>

</html>


<?php include "footer.php"; ?>
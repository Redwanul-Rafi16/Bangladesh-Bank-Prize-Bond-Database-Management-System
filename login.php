<?php
// Connect to database
include 'db_connect.php';
session_start();

// Check if the user is already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get and sanitize input
    $email = htmlspecialchars(trim($_POST['email']));
    $password = $_POST['password'];

    // Prepare SQL statement
    $stmt = $conn->prepare("SELECT user_id, password, name FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    // Check if user exists
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($user_id, $hashed_password, $name);
        $stmt->fetch();

        // Verify the password
        if (password_verify($password, $hashed_password)) {
            // Set session variables
            $_SESSION['user_id'] = $user_id;
            $_SESSION['name'] = $name;

            echo "<script>alert('Login successful!'); window.location.href='index.php';</script>";
        } else {
            echo "<script>alert('Incorrect password. Please try again.');</script>";
        }
    } else {
        echo "<script>alert('No account found with that email.');</script>";
    }

    // Close statement
    $stmt->close();
}

// Close connection
$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <link rel="stylesheet" type="text/css" href="login.css">
</head>
<body>
    <header>
        <h1>Bangladesh Bank Prize Bond Checker</h1>
        <div>
            <a href="register.php">Sign Up</a>
            <a href="login.php">Log In</a>
            <a href="https://www.bb.org.bd/en/index.php/Investfacility/prizebond" target="_blank">Prizebond Result</a>
        </div>
    </header>
    <form method="post" action="login.php">
        <h2>Login</h2>
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Login</button>
        <p>Don't have an account? <a href="register.php">Register Here</a></p>
    </form>
</body>
</html>

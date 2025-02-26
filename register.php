<?php
// Connect to database
include 'db_connect.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get and sanitize input
    $name = htmlspecialchars(trim($_POST['name']));
    $email = htmlspecialchars(trim($_POST['email']));
    $phone = htmlspecialchars(trim($_POST['phone']));
    $address = htmlspecialchars(trim($_POST['address']));
    $bank_account = htmlspecialchars(trim($_POST['bank_account']));
    $password = $_POST['password'];
    $repeat_password = $_POST['repeat_password'];

    // Check if passwords match
    if ($password !== $repeat_password) {
        echo "<script>alert('Passwords do not match!');</script>";
    } else {
        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Prepare SQL statement
        $stmt = $conn->prepare("INSERT INTO users (email, password, name, phone, address, bank_account) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $email, $hashed_password, $name, $phone, $address, $bank_account);

        // Execute the statement
        if ($stmt->execute()) {
            echo "<script>alert('Registration successful!'); window.location.href='login.php';</script>";
        } else {
            echo "<script>alert('Error: Email already registered or database issue.');</script>";
        }

        // Close statement
        $stmt->close();
    }
}

// Close connection
$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
    <link rel="stylesheet" type="text/css" href="register.css">
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
    <form method="post" action="register.php">
        <h2>Register</h2>
        <input type="text" name="name" placeholder="Full Name" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="text" name="phone" placeholder="Contact Number" required>
        <input type="text" name="address" placeholder="Address" required>
        <input type="text" name="bank_account" placeholder="Bank Account" required>
        <input type="password" name="password" placeholder="Password" required>
        <input type="password" name="repeat_password" placeholder="Repeat Password" required>
        <button type="submit">Register</button>
        <p>Already Registered? <a href="login.php">Login Here</a></p>
    </form>
</body>
</html>

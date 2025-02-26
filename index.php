<?php
include 'db_connect.php';
session_start();
//

$user_id = $_SESSION['user_id'];

// Fetch user details securely
$stmt = $conn->prepare("SELECT name FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_result = $stmt->get_result();
$user = $user_result->fetch_assoc();
$stmt->close();

// Fetch user's prize bonds
$bonds_stmt = $conn->prepare("SELECT bond_num FROM bonds WHERE owner_id = ?");
$bonds_stmt->bind_param("i", $user_id);
$bonds_stmt->execute();
$bonds_result = $bonds_stmt->get_result();
$bonds_stmt->close();

// Fetch available prize bonds for purchase
$available_stmt = $conn->prepare("SELECT bond_id, bond_num FROM bonds WHERE status = 'Available'");
$available_stmt->execute();
$available_result = $available_stmt->get_result();
$available_stmt->close();

// Handle Purchase Request
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['purchase_bond'])) {
    $bond_id = intval($_POST['purchase_bond']);

    // Check if the bond is still available
    $check_stmt = $conn->prepare("SELECT bond_num FROM bonds WHERE bond_id = ? AND status = 'Available'");
    $check_stmt->bind_param("i", $bond_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        $update_stmt = $conn->prepare("UPDATE bonds SET status = 'Sold', owner_id = ? WHERE bond_id = ?");
        $update_stmt->bind_param("ii", $user_id, $bond_id);
        $update_stmt->execute();
        $update_stmt->close();

        header("Location: index.php");
        exit();
    }
    $check_stmt->close();
}

// Handle Add Prize Bond
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_bonds'])) {
    $new_bond_nums = htmlspecialchars(trim($_POST['new_bond_nums']));
    
    // Split the input into an array, trim whitespace, and filter empty values
    $bond_nums = array_filter(array_map('trim', explode(',', $new_bond_nums)));
    
    if (!empty($bond_nums)) {
        $added_bonds = [];
        $duplicate_bonds = [];
        
        foreach ($bond_nums as $bond_num) {
            // Check if the bond number already exists
            $check_stmt = $conn->prepare("SELECT * FROM bonds WHERE bond_num = ?");
            $check_stmt->bind_param("s", $bond_num);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();

            if ($check_result->num_rows == 0) {
                // Insert new bond
                $add_stmt = $conn->prepare("
                    INSERT INTO bonds (bond_num, status, owner_id, added_date) 
                    VALUES (?, 'Sold', ?, NOW())
                ");
                $add_stmt->bind_param("si", $bond_num, $user_id);
                $add_stmt->execute();
                $add_stmt->close();
                
                $added_bonds[] = $bond_num;
            } else {
                $duplicate_bonds[] = $bond_num;
            }
        }

        // Provide feedback to the user
        if (!empty($added_bonds)) {
            echo "<script>alert('Successfully added bonds: " . implode(', ', $added_bonds) . "');</script>";
        }

        if (!empty($duplicate_bonds)) {
            echo "<script>alert('Duplicate bonds not added: " . implode(', ', $duplicate_bonds) . "');</script>";
        }

        // Redirect to avoid form resubmission
        echo "<script>window.location.href = 'index.php';</script>";
        exit();
    } else {
        echo "<script>alert('Please enter at least one valid bond number.');</script>";
    }
    $check_stmt->close();
}

//feedback code
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['feedback'])) {
        $feedback = htmlspecialchars($_POST['feedback']);
    
        $insert_query = "INSERT INTO feedback (user_id, message) VALUES (?, ?)";
        $stmt = $conn->prepare($insert_query);
        $stmt->bind_param("is", $user_id, $feedback);
    
        if ($stmt->execute()) {
            echo "<script>alert('Feedback submitted successfully!'); window.location.href='index.php';</script>";
        } else {
            echo "<script>alert('Failed to submit feedback. Please try again.'); window.location.href='index.php';</script>";
        }
        $check_stmt->close();
    }
   
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
    <link rel="stylesheet" type="text/css" href="styles.css">
</head>
<body>
    <header>
        <h1>Bangladesh Bank Prize Bond Checker</h1>
        <div>
            <a href="index.php">Home</a>
            <a href="logout.php">Logout</a>
            <a href="https://www.bb.org.bd/en/index.php/Investfacility/prizebond" target="_blank">Prizebond Result</a>
        </div>
    </header>

    <div class="card">
        <h2>Welcome, <?php echo htmlspecialchars($user['name']); ?></h2>
        <h4>Your Prize Bonds</h4>
        <ul>
            <?php while ($bond = $bonds_result->fetch_assoc()) { ?>
                <li><?php echo htmlspecialchars($bond['bond_num']); ?></li>
            <?php } ?>
        </ul>
    </div>



    <div class="card">
        <h2>Purchase Prize Bonds</h2>
        <form method="post" action="index.php">
            <select name="purchase_bond">
                <?php while ($available = $available_result->fetch_assoc()) { ?>
                    <option value="<?php echo htmlspecialchars($available['bond_id']); ?>"><?php echo htmlspecialchars($available['bond_num']); ?></option>
                <?php } ?>
            </select>
            <button type="submit">Purchase</button>
        </form>
    </div>

    <div class="card">
    <h2>Add Prize Bonds</h2>
    <form method="post" action="index.php">
        <textarea name="new_bond_nums" rows="4" cols="50" placeholder="Enter new prize bond numbers, separated by commas" required></textarea>
        <br>
        <button type="submit" name="add_bonds">Add Prize Bonds</button>
    </form>
    </div>

    <div class="card">
    <h2>Check Prize Bonds</h2>
    <form method="post" action="check_prizes.php">
        <button type="submit">Check Prize Bonds</button>
    </form>
</div>

<div class="card">
    <h2>Feedback About Us</h2>
    <p>We value your feedback to improve our system. Please share your experience and suggestions.</p>
    <form method="post" action="index.php">
        <textarea name="feedback" rows="4" cols="50" value="feedback" placeholder="Enter your feedback here..." required></textarea>
        <br>
        <button type="submit">Submit Feedback</button>
    </form>
</div>


    <div class="card">
        <a href="logout.php">Logout</a>
    </div>
</body>
</html>

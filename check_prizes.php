<?php
include 'db_connect.php';
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get user's prize bonds
$bonds_query = "SELECT bond_id, bond_num FROM bonds WHERE owner_id=?";
$stmt = $conn->prepare($bonds_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$bonds_result = $stmt->get_result();

$winning_bonds = [];

while ($bond = $bonds_result->fetch_assoc()) {
    $bond_num = $bond['bond_num'];
    $bond_id = $bond['bond_id'];

    $result_query = "
        SELECT dr.result_id, dr.bond_num, d.draw_date, d.draw_round, pc.category, pc.prize_amount 
        FROM draw_results dr
        JOIN draws d ON dr.draw_id = d.draw_id
        JOIN prize_category pc ON dr.cat_id = pc.cat_id
        WHERE dr.bond_num=?
    ";

    $stmt2 = $conn->prepare($result_query);
    $stmt2->bind_param("s", $bond_num);
    $stmt2->execute();
    $result_result = $stmt2->get_result();

    while ($result = $result_result->fetch_assoc()) {
        $result_id = $result['result_id'];

        $check_query = "
            SELECT * FROM check_prize 
            WHERE result_id=? 
            AND bond_id=? 
            AND user_id=?
        ";
        $stmt3 = $conn->prepare($check_query);
        $stmt3->bind_param("iii", $result_id, $bond_id, $user_id);
        $stmt3->execute();
        $check_result = $stmt3->get_result();

        if ($check_result->num_rows == 0) {
            $insert_query = "
                INSERT INTO check_prize (result_id, bond_id, user_id)
                VALUES (?, ?, ?)
            ";
            $stmt4 = $conn->prepare($insert_query);
            $stmt4->bind_param("iii", $result_id, $bond_id, $user_id);
            $stmt4->execute();
        }

        $winning_bonds[] = $result;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Check Prize Bonds</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <header>
        <h1>Bangladesh Bank Prize Bond Checker</h1>
        <div>
            <a href="index.php">Home</a>
            <a href="logout.php">Logout</a>
        </div>
    </header>

    <div class="card">
        <h2>Check Prize Bonds</h2>
        <a href="index.php">Back to Dashboard</a>

        <?php if (count($winning_bonds) > 0) { ?>
            <h3>Congratulations! You have winning bonds:</h3>
            <table>
                <tr>
                    <th>Bond Number</th>
                    <th>Draw Date</th>
                    <th>Draw Round</th>
                    <th>Category</th>
                    <th>Prize Amount</th>
                </tr>
                <?php foreach ($winning_bonds as $win) { ?>
                    <tr>
                        <td><?php echo $win['bond_num']; ?></td>
                        <td><?php echo $win['draw_date']; ?></td>
                        <td><?php echo $win['draw_round']; ?></td>
                        <td><?php echo $win['category']; ?></td>
                        <td><?php echo $win['prize_amount']; ?></td>
                    </tr>
                <?php } ?>
            </table>
        <?php } else { ?>
            <h3>No winning bonds found for the latest draws.</h3>
        <?php } ?>
    </div>
</body>
</html>

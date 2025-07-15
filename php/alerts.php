<?php
session_start();
require_once '../config.php';
include '../templates/header.php';

// Fetch low stock items
$alerts = $mysqli->query("
    SELECT ItemName, Quantity, MinimumStock
    FROM Inventory
    WHERE Quantity < MinimumStock
");
?>

<h2>Low Stock Alerts</h2>
<a href="dashboard_<?php echo $_SESSION['Role'] === 'Admin' ? 'admin' : 'staff'; ?>.php" class="back-btn">← Back to Dashboard</a>


<?php if ($alerts->num_rows === 0): ?>
    <p style="color:green;">All items are above minimum stock levels ✅</p>
<?php else: ?>
    <table border="1" cellpadding="8">
        <tr><th>Item</th><th>Current Qty</th><th>Minimum Required</th></tr>
        <?php while ($row = $alerts->fetch_assoc()) { ?>
        <tr>
            <td><?= $row['ItemName'] ?></td>
            <td style="color:red;"><?= $row['Quantity'] ?></td>
            <td><?= $row['MinimumStock'] ?></td>
        </tr>
        <?php } ?>
    </table>
<?php endif; ?>

<?php include '../templates/footer.php'; ?>

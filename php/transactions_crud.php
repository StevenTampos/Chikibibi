<?php
session_start();
require_once '../config.php';
include '../templates/header.php';

// Handle Transaction Add
if (isset($_POST['add'])) {
    $inventory_id = $_POST['inventory_id'];
    $date = $_POST['date'];
    $qty_used = $_POST['quantity_used'];
    $action = $_POST['action'];
    $user_id = $_SESSION['UserID'];

    // Add transaction log
    $stmt = $mysqli->prepare("INSERT INTO Transaction (InventoryID, Date, QuantityUsed, ActionType) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isis", $inventory_id, $date, $qty_used, $action);
    $stmt->execute();

    // Update quantity in inventory
    if ($action === 'Usage') {
        $mysqli->query("UPDATE Inventory SET Quantity = Quantity - $qty_used WHERE InventoryID = $inventory_id");
    } else {
        $mysqli->query("UPDATE Inventory SET Quantity = Quantity + $qty_used WHERE InventoryID = $inventory_id");
    }
}

// List transactions
$result = $mysqli->query("SELECT t.*, i.ItemName FROM Transaction t JOIN Inventory i ON t.InventoryID = i.InventoryID ORDER BY t.Date DESC");

// List items for dropdown
$items = $mysqli->query("SELECT InventoryID, ItemName FROM Inventory");
?>

<h2>Inventory Transactions</h2>

<table border="1" cellpadding="8">
    <tr>
        <th>Date</th><th>Item</th><th>Qty</th><th>Type</th>
    </tr>
    <?php while ($row = $result->fetch_assoc()) { ?>
    <tr>
        <td><?= $row['Date'] ?></td>
        <td><?= $row['ItemName'] ?></td>
        <td><?= $row['QuantityUsed'] ?></td>
        <td><?= $row['ActionType'] ?></td>
    </tr>
    <?php } ?>
</table>

<h3>Record New Transaction</h3>
<a href="dashboard_<?php echo $_SESSION['Role'] === 'Admin' ? 'admin' : 'staff'; ?>.php" class="back-btn">← Back to Dashboard</a>

<form method="post">
    <select name="inventory_id" required>
        <option selected disabled>Select Item</option>
        <?php while ($item = $items->fetch_assoc()) { ?>
            <option value="<?= $item['InventoryID'] ?>"><?= $item['ItemName'] ?></option>
        <?php } ?>
    </select>
    <input type="number" name="quantity_used" placeholder="Quantity" required>
    <select name="action" required>
        <option value="Usage">Usage</option>
        <option value="Restock">Restock</option>
    </select>
    <input type="date" name="date" required>
    <button type="submit" name="add">Save Transaction</button>
</form>

<?php include '../templates/footer.php'; ?>

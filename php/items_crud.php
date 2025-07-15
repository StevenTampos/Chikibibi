<?php
session_start();
require_once '../config.php';
include '../templates/header.php';

// Handle Add
if (isset($_POST['add'])) {
    $name = $_POST['item_name'];
    $qty = $_POST['quantity'];
    $min = $_POST['minimum'];
    $price = $_POST['price'];
    $cat = $_POST['category'];
    $expiry = $_POST['expiry'];
    $supplier = $_POST['supplier'];
    $date_recv = $_POST['date_received'];

    $stmt = $mysqli->prepare("INSERT INTO Inventory (ItemName, Quantity, MinimumStock, PricePerUnit, Category, ExpiryDate, SupplierID, DateReceived) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("siidssds", $name, $qty, $min, $price, $cat, $expiry, $supplier, $date_recv);
    $stmt->execute();
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $mysqli->query("DELETE FROM Inventory WHERE InventoryID=$id");
}

// Fetch suppliers for dropdown
$suppliers = $mysqli->query("SELECT SupplierID, SupplierName FROM Supplier");

// List Items
$items = $mysqli->query("SELECT Inventory.*, Supplier.SupplierName FROM Inventory LEFT JOIN Supplier ON Inventory.SupplierID = Supplier.SupplierID");
?>

<h2>Inventory Items</h2>
<a href="dashboard_<?php echo $_SESSION['Role'] === 'Admin' ? 'admin' : 'staff'; ?>.php" class="back-btn">← Back to Dashboard</a>

<table border="1" cellpadding="8">
    <tr><th>Item</th><th>Qty</th><th>Min</th><th>Price</th><th>Cat</th><th>Expiry</th><th>Supplier</th><th>Action</th></tr>
    <?php while ($row = $items->fetch_assoc()) { ?>
    <tr>
        <td><?= $row['ItemName'] ?></td>
        <td><?= $row['Quantity'] ?></td>
        <td><?= $row['MinimumStock'] ?></td>
        <td>₱<?= $row['PricePerUnit'] ?></td>
        <td><?= $row['Category'] ?></td>
        <td><?= $row['ExpiryDate'] ?></td>
        <td><?= $row['SupplierName'] ?></td>
        <td><a href="?delete=<?= $row['InventoryID'] ?>" onclick="return confirm('Delete this item?')">Delete</a></td>
    </tr>
    <?php } ?>
</table>

<h3>Add Item</h3>
<form method="post">
    <input type="text" name="item_name" placeholder="Item Name" required>
    <input type="number" name="quantity" placeholder="Quantity" required>
    <input type="number" name="minimum" placeholder="Minimum Stock" required>
    <input type="number" step="0.01" name="price" placeholder="Price per unit" required>
    <select name="category">
        <option value="Chicken">Chicken</option>
        <option value="Pork">Pork</option>
    </select>
    <input type="date" name="expiry">
    <select name="supplier" required>
        <option disabled selected>Choose Supplier</option>
        <?php while ($sup = $suppliers->fetch_assoc()) { ?>
            <option value="<?= $sup['SupplierID'] ?>"><?= $sup['SupplierName'] ?></option>
        <?php } ?>
    </select>
    <input type="date" name="date_received" required>
    <button type="submit" name="add">Add Item</button>
</form>

<?php include '../templates/footer.php'; ?>

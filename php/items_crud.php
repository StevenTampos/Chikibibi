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
    <tr><th>Item</th><th>Quantity</th><th>Minimum Stock</th><th>Price</th><th>Category</th><th>Expiry</th><th>Supplier</th><th>Action</th></tr>
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
    <input type="text" name="ItemName" placeholder="Item Name" required>
    <input type="number" name="Quantity" placeholder="Quantity" required>
    <input type="number" name="MinimumStock" placeholder="Minimum Stock" required>
    <input type="number" step="0.01" name="PricePerUnit" placeholder="Price per unit" required>
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

<div id="admin-manage-inventory" class="hidden container mx-auto p-6">
            <div class="flex items-center mb-6">
                <button class="bg-gray-200 hover:bg-gray-300 text-gray-700 py-2 px-4 rounded-md flex items-center mr-4 transition duration-200">
                    <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Back
                </button>
                <h1 class="text-3xl font-bold text-gray-800">Manage Inventory</h1>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-semibold text-gray-800">Inventory Items</h2>
                    <button class="bg-purple-500 hover:bg-purple-600 text-white py-2 px-4 rounded-md flex items-center transition duration-200">
                        <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Add New Item
                    </button>
                </div>

                <div class="mb-4 flex flex-wrap gap-2">
                    <input type="text" placeholder="Search items..." class="px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-300 focus:border-purple-300 flex-grow">
                    <select class="px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-300 focus:border-purple-300">
                        <option>All Categories</option>
                        <option>Chicken</option>
                        <option>Pork</option>
                    </select>
                    <button class="bg-gray-200 hover:bg-gray-300 text-gray-700 py-2 px-4 rounded-md transition duration-200">
                        Filter
                    </button>
                </div>
            </div>
        </div>
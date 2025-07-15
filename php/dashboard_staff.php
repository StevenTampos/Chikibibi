<?php
session_start();
if ($_SESSION['Role'] !== 'Inventory Staff') {
    die("Access denied.");
}
include '../templates/header.php';
?>
<h2>Welcome, Staff <?= $_SESSION['Name']; ?>!</h2>
<ul>
    <li><a href="items_crud.php">Inventory Items</a></li>
    <li><a href="suppliers_crud.php">Suppliers</a></li>
    <li><a href="transactions_crud.php">Transactions</a></li>
    <li><a href="alerts.php">Low Stock Alerts</a></li>
</ul>
<a href="logout.php">Logout</a>
<?php include '../templates/footer.php'; ?>

<?php
session_start();
if ($_SESSION['Role'] !== 'Inventory Staff') {
    include '../templates/access_denied_staff.php';
    exit();
}
include '../templates/header.php';
?>

<div class="dashboard">
  <h2>Welcome, Staff <?= $_SESSION['Name']; ?>!</h2>
  <a href="dashboard_<?php echo $_SESSION['Role'] === 'Admin' ? 'admin' : 'staff'; ?>.php" class="back-btn">← Back to Dashboard</a>


  <div class="dashboard-cards">
    <a href="items_crud.php" class="card">📦 Inventory</a>
    <a href="suppliers_crud.php" class="card">🚚 Suppliers</a>
    <a href="transactions_crud.php" class="card">🔁 Transactions</a>
    <a href="alerts.php" class="card alert">⚠️ Low Stock Alerts</a>
  </div>

  <a href="logout.php" class="logout-link">Logout</a>
</div>

<?php include '../templates/footer.php'; ?>

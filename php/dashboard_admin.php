<?php
session_start();
if ($_SESSION['Role'] !== 'Admin') {
    include '../templates/access_denied_admin.php';
    exit();
}
include '../templates/header.php';
?>

<div class="dashboard">
  <h2>Welcome, Admin <?= $_SESSION['Name']; ?>!</h2>
  <a href="dashboard_<?php echo $_SESSION['Role'] === 'Admin' ? 'admin' : 'staff'; ?>.php" class="back-btn">← Back to Dashboard</a>


  <div class="dashboard-cards">
    <a href="users_crud.php" class="card">👤 Manage Users</a>
    <a href="items_crud.php" class="card">📦 Manage Inventory</a>
    <a href="suppliers_crud.php" class="card">🚚 Manage Suppliers</a>
    <a href="transactions_crud.php" class="card">🔁 Transactions</a>
    <a href="alerts.php" class="card alert">⚠️ Low Stock Alert</a>
  </div>

  <a href="logout.php" class="logout-link">Logout</a>
</div>

<?php include '../templates/footer.php'; ?>

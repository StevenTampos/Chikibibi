<?php
session_start();
if ($_SESSION['Role'] !== 'Admin') {
    die("Access denied.");
}
require_once '../config.php';
include '../templates/header.php';

// Handle Add User
if (isset($_POST['add'])) {
    $name = $_POST['name'];
    $role = $_POST['role'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $mysqli->prepare("INSERT INTO User (Name, Role, Email, Password) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $name, $role, $email, $password);
    $stmt->execute();
}

// Handle Delete User
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $mysqli->query("DELETE FROM User WHERE UserID=$id");
}

// List Users
$result = $mysqli->query("SELECT * FROM User");
?>

<h2>User Management</h2>
<a href="dashboard_<?php echo $_SESSION['Role'] === 'Admin' ? 'admin' : 'staff'; ?>.php" class="back-btn">← Back to Dashboard</a>


<table border="1" cellpadding="10">
    <tr>
        <th>Name</th><th>Role</th><th>Email</th><th>Action</th>
    </tr>
    <?php while ($row = $result->fetch_assoc()) { ?>
    <tr>
        <td><?= htmlspecialchars($row['Name']) ?></td>
        <td><?= $row['Role'] ?></td>
        <td><?= $row['Email'] ?></td>
        <td>
            <a href="?delete=<?= $row['UserID'] ?>" onclick="return confirm('Are you sure?')">Delete</a>
        </td>
    </tr>
    <?php } ?>
</table>

<h3>Add New User</h3>
<form method="post">
    <input type="text" name="name" placeholder="Full Name" required>
    <input type="email" name="email" placeholder="Email" required>
    <select name="role">
        <option value="Admin">Admin</option>
        <option value="Inventory Staff">Inventory Staff</option>
    </select>
    <input type="password" name="password" placeholder="Password" required>
    <button type="submit" name="add">Add User</button>
</form>

<?php include '../templates/footer.php'; ?>
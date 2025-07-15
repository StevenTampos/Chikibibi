<?php
session_start();
require_once '../config.php';
include '../templates/header.php';

// Handle Add
if (isset($_POST['add'])) {
    $name = $_POST['name'];
    $contact = $_POST['contact'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $addr = $_POST['address'];

    $stmt = $mysqli->prepare("INSERT INTO Supplier (SupplierName, ContactPerson, PhoneNumber, Email, Address) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $name, $contact, $phone, $email, $addr);
    $stmt->execute();
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $mysqli->query("DELETE FROM Supplier WHERE SupplierID=$id");
}

$suppliers = $mysqli->query("SELECT * FROM Supplier");
?>

<h2>Suppliers</h2>
<a href="dashboard_<?php echo $_SESSION['Role'] === 'Admin' ? 'admin' : 'staff'; ?>.php" class="back-btn">← Back to Dashboard</a>

<table border="1" cellpadding="8">
    <tr><th>Name</th><th>Contact</th><th>Phone</th><th>Email</th><th>Address</th><th>Action</th></tr>
    <?php while ($row = $suppliers->fetch_assoc()) { ?>
    <tr>
        <td><?= $row['SupplierName'] ?></td>
        <td><?= $row['ContactPerson'] ?></td>
        <td><?= $row['PhoneNumber'] ?></td>
        <td><?= $row['Email'] ?></td>
        <td><?= $row['Address'] ?></td>
        <td><a href="?delete=<?= $row['SupplierID'] ?>" onclick="return confirm('Delete supplier?')">Delete</a></td>
    </tr>
    <?php } ?>
</table>

<h3>Add New Supplier</h3>
<form method="post">
    <input type="text" name="name" placeholder="Supplier Name" required>
    <input type="text" name="contact" placeholder="Contact Person">
    <input type="text" name="phone" placeholder="Phone Number">
    <input type="email" name="email" placeholder="Email">
    <input type="text" name="address" placeholder="Address">
    <button type="submit" name="add">Add Supplier</button>
</form>

<?php include '../templates/footer.php'; ?>

<?php
session_start();
if ($_SESSION['Role'] !== 'Admin') {
    include '../templates/access_denied.php';
    exit();
}

require_once '../config.php';
include '../templates/header_admin.php';

// Load banner message from session (like users_crud.php)
if (isset($_SESSION['banner_message'])) {
    $banner_message = $_SESSION['banner_message'];
    $banner_type = $_SESSION['banner_type'];
    unset($_SESSION['banner_message']);
    unset($_SESSION['banner_type']);
}

// Add or Update Supplier
if (isset($_POST['save_supplier'])) {
    $supplier_id = isset($_POST['supplier_id']) ? intval($_POST['supplier_id']) : 0;
    $name = trim($_POST['name']);
    $contact = trim($_POST['contact']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);
    $address = trim($_POST['address']);

    if ($supplier_id > 0) {
        // UPDATE existing supplier
        $stmt = $mysqli->prepare("UPDATE supplier SET SupplierName=?, ContactPerson=?, PhoneNumber=?, Email=?, Address=? WHERE SupplierID=?");
        $stmt->bind_param("sssssi", $name, $contact, $phone, $email, $address, $supplier_id);

        if ($stmt->execute()) {
            $_SESSION['banner_type'] = "success";
            $_SESSION['banner_message'] = "✅ Supplier updated successfully!";
        } else {
            $_SESSION['banner_type'] = "error";
            $_SESSION['banner_message'] = "❌ Failed to update supplier!";
        }
        header("Location: suppliers_crud.php");
        exit();

    } else {
        // INSERT new supplier
        $stmt = $mysqli->prepare("INSERT INTO supplier (SupplierName, ContactPerson, PhoneNumber, Email, Address) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $name, $contact, $phone, $email, $address);

        if ($stmt->execute()) {
            $_SESSION['banner_type'] = "success";
            $_SESSION['banner_message'] = "✅ Supplier added successfully!";
        } else {
            $_SESSION['banner_type'] = "error";
            $_SESSION['banner_message'] = "❌ Failed to add supplier!";
        }
        header("Location: suppliers_crud.php");
        exit();
    }
}

// Delete supplier
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    if ($mysqli->query("DELETE FROM supplier WHERE SupplierID=$id")) {
        $_SESSION['banner_type'] = "success";
        $_SESSION['banner_message'] = "✅ Supplier deleted successfully!";
    } else {
        $_SESSION['banner_type'] = "error";
        $_SESSION['banner_message'] = "❌ Failed to delete supplier!";
    }
    header("Location: suppliers_crud.php");
    exit();
}

// Fetch data for editing
$edit_supplier = null;
if (isset($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    $res = $mysqli->query("SELECT * FROM supplier WHERE SupplierID=$edit_id LIMIT 1");
    $edit_supplier = $res->fetch_assoc();
}

// Fetch all suppliers
$result = $mysqli->query("SELECT * FROM supplier ORDER BY SupplierName ASC");
?>

<div id="admin-manage-suppliers" class="container mx-auto p-6">

    <!-- Banner Alerts -->
    <?php if (!empty($banner_message)): ?>
        <div class="mb-4 p-4 rounded-md text-white <?= $banner_type === 'success' ? 'bg-green-500' : 'bg-red-500' ?>">
            <?= $banner_message ?>
        </div>
    <?php endif; ?>

    <div class="flex items-center mb-6">
        <a href="<?= $_SESSION['Role'] === 'Admin' ? 'admin' : 'staff' ?>.php" class="back-btn">
            <button class="bg-gray-200 hover:bg-gray-300 text-gray-700 py-2 px-4 rounded-md flex items-center mr-4 transition duration-200">
                <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                    xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back
            </button>
        </a>
        <h1 class="text-3xl font-bold text-gray-800">Manage Suppliers</h1>
    </div>

    <!-- Supplier List Table -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-semibold text-gray-800">Supplier List</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full bg-white">
                <thead>
                    <tr class="bg-gray-100 text-gray-700 text-sm leading-normal">
                        <th class="py-3 px-4 text-left">Name</th>
                        <th class="py-3 px-4 text-left">Contact Person</th>
                        <th class="py-3 px-4 text-left">Phone</th>
                        <th class="py-3 px-4 text-left">Email</th>
                        <th class="py-3 px-4 text-left">Address</th>
                        <th class="py-3 px-4 text-left">Actions</th>
                    </tr>
                </thead>
                <tbody class="text-gray-600 text-sm">
                    <?php while ($row = $result->fetch_assoc()) { ?>
                        <tr class="border-b border-gray-200 hover:bg-gray-50">
                            <td class="py-3 px-4"><?= htmlspecialchars($row['SupplierName']) ?></td>
                            <td class="py-3 px-4"><?= htmlspecialchars($row['ContactPerson']) ?></td>
                            <td class="py-3 px-4"><?= htmlspecialchars($row['PhoneNumber']) ?></td>
                            <td class="py-3 px-4"><?= htmlspecialchars($row['Email']) ?></td>
                            <td class="py-3 px-4"><?= htmlspecialchars($row['Address']) ?></td>
                            <td class="py-3 px-4">
                                <div class="flex gap-2">
                                    <a href="?edit=<?= $row['SupplierID'] ?>" class="text-blue-500 hover:text-blue-700">Edit</a>
                                    <a href="?delete=<?= $row['SupplierID'] ?>" class="text-red-500 hover:text-red-700" onclick="return confirm('Are you sure you want to delete this supplier?')">Delete</a>
                                </div>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add/Edit Supplier Form -->
    <div class="bg-white rounded-lg shadow-md p-6 mt-10">
        <form method="post">
            <input type="hidden" name="supplier_id" value="<?= $edit_supplier['SupplierID'] ?? '' ?>">

            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-semibold text-gray-800">
                    <?= $edit_supplier ? "Edit Supplier" : "Add New Supplier" ?>
                </h2>
                <button type="submit" name="save_supplier"
                    class="bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded-md flex items-center transition duration-200">
                    <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    <?= $edit_supplier ? "Save Changes" : "Add Supplier" ?>
                </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Supplier Name</label>
                    <input type="text" name="name" required
                        value="<?= $edit_supplier['SupplierName'] ?? '' ?>"
                        class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-300 focus:border-blue-300"
                        placeholder="Enter supplier name">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Contact Person</label>
                    <input type="text" name="contact" required
                        value="<?= $edit_supplier['ContactPerson'] ?? '' ?>"
                        class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-300 focus:border-blue-300"
                        placeholder="Enter contact person">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                    <input type="text" name="phone"
                        value="<?= $edit_supplier['PhoneNumber'] ?? '' ?>"
                        class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-300 focus:border-blue-300"
                        placeholder="Enter phone number">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" name="email"
                        value="<?= $edit_supplier['Email'] ?? '' ?>"
                        class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-300 focus:border-blue-300"
                        placeholder="Enter email">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                    <input type="text" name="address"
                        value="<?= $edit_supplier['Address'] ?? '' ?>"
                        class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-300 focus:border-blue-300"
                        placeholder="Enter supplier address">
                </div>
            </div>
        </form>
    </div>
</div>

<?php include '../templates/footer.php'; ?>

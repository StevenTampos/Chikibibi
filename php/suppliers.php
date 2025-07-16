<?php
session_start();
if ($_SESSION['Role'] !== 'Inventory Staff') {
    include '../templates/access_denied.php';
    exit();
}

require_once '../config.php';
include '../templates/header_staff.php';

// Fetch all suppliers
$result = $mysqli->query("SELECT * FROM Supplier ORDER BY SupplierName ASC");
?>

<div id="admin-manage-suppliers" class="container mx-auto p-6">

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
        <h1 class="text-3xl font-bold text-gray-800">Suppliers</h1>
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
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../templates/footer.php'; ?>

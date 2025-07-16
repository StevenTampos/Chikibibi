<?php
session_start();
if ($_SESSION['Role'] !== 'Admin') {
    include '../templates/header_staff.php';
} else {
    include '../templates/header_admin.php';
}

require_once '../config.php';

// Fetch low stock items
$alerts = $mysqli->query("
    SELECT ItemName, Quantity, MinimumStock
    FROM inventory
    WHERE Quantity < MinimumStock
");
?>

<div id="low-stock-alerts" class="container mx-auto p-6">

    <!-- Header with Back Button -->
    <div class="flex items-center mb-6">
        <a href="<?= $_SESSION['Role'] === 'Admin' ? 'admin' : 'staff' ?>.php" class="back-btn">
            <button
                class="bg-gray-200 hover:bg-gray-300 text-gray-700 py-2 px-4 rounded-md flex items-center mr-4 transition duration-200">
                <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                    xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back
            </button>
        </a>
        <h1 class="text-3xl font-bold text-gray-800">Low Stock Alerts</h1>
    </div>

    <!-- Low Stock Table -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <?php if ($alerts->num_rows === 0): ?>
            <!-- No low stock -->
            <div class="p-4 bg-green-100 text-green-700 rounded-md text-center font-medium">
                ✅ All items are above minimum stock levels!
            </div>
        <?php else: ?>
            <!-- Show low stock items -->
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Items Below Minimum Stock</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white">
                    <thead>
                        <tr class="bg-gray-100 text-gray-700 text-sm leading-normal">
                            <th class="py-3 px-4 text-left">Item</th>
                            <th class="py-3 px-4 text-left">Current Qty</th>
                            <th class="py-3 px-4 text-left">Minimum Required</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-600 text-sm">
                        <?php while ($row = $alerts->fetch_assoc()) { ?>
                            <tr class="border-b border-gray-200 hover:bg-gray-50">
                                <td class="py-3 px-4 font-medium text-gray-800"><?= htmlspecialchars($row['ItemName']) ?></td>
                                <td class="py-3 px-4 text-red-600 font-semibold"><?= htmlspecialchars($row['Quantity']) ?></td>
                                <td class="py-3 px-4"><?= htmlspecialchars($row['MinimumStock']) ?></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../templates/footer.php'; ?>

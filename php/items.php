<?php
session_start();
if ($_SESSION['Role'] !== 'Inventory Staff') {
    include '../templates/access_denied.php';
    exit();
}
include '../templates/header_staff.php';
require_once '../config.php';

// List all inventory items with supplier name
$items = $mysqli->query("SELECT Inventory.*, Supplier.SupplierName 
                         FROM Inventory 
                         LEFT JOIN Supplier ON Inventory.SupplierID = Supplier.SupplierID");
?>

<div id="admin-manage-inventory" class="container mx-auto p-6">

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
        <h1 class="text-3xl font-bold text-gray-800">Inventory</h1>
    </div>

    <!-- Inventory Table -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-semibold text-gray-800">Inventory Items</h2>
        </div>

        <div class="overflow-x-auto">
            <!-- FILTER CONTROLS -->
            <div class="flex flex-wrap gap-4 mb-4">
                <!-- Category Filter -->
                <select id="categoryFilter" class="px-3 py-2 border rounded-md">
                    <option value="">All Categories</option>
                    <option value="Chicken">Chicken</option>
                    <option value="Pork">Pork</option>
                </select>

                <!-- Sort by Total Price -->
                <select id="sortPrice" class="px-3 py-2 border rounded-md">
                    <option value="">Sort by Total Price</option>
                    <option value="desc">Highest → Lowest</option>
                    <option value="asc">Lowest → Highest</option>
                </select>

                <!-- Date Range -->
                <input type="date" id="startDate" class="px-3 py-2 border rounded-md">
                <span class="px-3 py-2">to</span>
                <input type="date" id="endDate" class="px-3 py-2 border rounded-md">

                <!-- Reset Button -->
                <button id="resetFilters" class="bg-gray-300 px-4 py-2 rounded-md hover:bg-gray-400">
                    Reset Filters
                </button>
            </div>

            <table id="inventoryTable" class="min-w-full bg-white">
                <thead>
                    <tr class="bg-gray-100 text-gray-700 text-sm leading-normal">
                        <th class="py-3 px-4 text-left">Item Name</th>
                        <th class="py-3 px-4 text-left">Quantity</th>
                        <th class="py-3 px-4 text-left">Min Stock</th>
                        <th class="py-3 px-4 text-left">Price</th>
                        <th class="py-3 px-4 text-left">Total Price</th>
                        <th class="py-3 px-4 text-left">Category</th>
                        <th class="py-3 px-4 text-left">Expiry</th>
                        <th class="py-3 px-4 text-left">Supplier</th>
                        <th class="py-3 px-4 text-left">Date Received</th>
                    </tr>
                </thead>
                <tbody class="text-gray-600 text-sm">
                    <?php while ($row = $items->fetch_assoc()) {
                        $total_price = $row['Quantity'] * $row['PricePerUnit']; ?>
                        <tr class="border-b border-gray-200 hover:bg-gray-50"
                            data-category="<?= htmlspecialchars($row['Category']) ?>"
                            data-date="<?= $row['DateReceived'] ?>" data-total="<?= $total_price ?>">
                            <td class="py-3 px-4"><?= htmlspecialchars($row['ItemName']) ?></td>
                            <td class="py-3 px-4"><?= $row['Quantity'] ?></td>
                            <td class="py-3 px-4"><?= $row['MinimumStock'] ?></td>
                            <td class="py-3 px-4">₱<?= number_format($row['PricePerUnit'], 2) ?></td>

                            <!-- Show Total Price -->
                            <td class="py-3 px-4 font-semibold">₱<?= number_format($total_price, 2) ?></td>

                            <td class="py-3 px-4"><?= htmlspecialchars($row['Category']) ?></td>
                            <td class="py-3 px-4">
                                <?= (!empty($row['ExpiryDate']) && $row['ExpiryDate'] !== '0000-00-00')
                                    ? htmlspecialchars($row['ExpiryDate'])
                                    : '-' ?>
                            </td>
                            <td class="py-3 px-4"><?= $row['SupplierName'] ?: 'Others' ?></td>
                            <td class="py-3 px-4"><?= $row['DateReceived'] ?></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>



<script>
    document.addEventListener("DOMContentLoaded", () => {
        const table = document.querySelector("#inventoryTable tbody");
        const rows = Array.from(table.querySelectorAll("tr"));

        const categoryFilter = document.getElementById("categoryFilter");
        const sortPrice = document.getElementById("sortPrice");
        const startDate = document.getElementById("startDate");
        const endDate = document.getElementById("endDate");
        const resetFilters = document.getElementById("resetFilters");

        function filterAndSort() {
            let categoryVal = categoryFilter.value;
            let sortVal = sortPrice.value;
            let startVal = startDate.value;
            let endVal = endDate.value;

            // Filter rows
            let filtered = rows.filter(row => {
                let rowCat = row.dataset.category;
                let rowDate = row.dataset.date;

                // Category match
                if (categoryVal && rowCat !== categoryVal) return false;

                // Date range match
                if (startVal && rowDate < startVal) return false;
                if (endVal && rowDate > endVal) return false;

                return true;
            });

            // Sort by total price
            if (sortVal === "desc") {
                filtered.sort((a, b) => parseFloat(b.dataset.total) - parseFloat(a.dataset.total));
            } else if (sortVal === "asc") {
                filtered.sort((a, b) => parseFloat(a.dataset.total) - parseFloat(b.dataset.total));
            }

            // Clear table
            table.innerHTML = "";

            // Re-add rows
            filtered.forEach(row => table.appendChild(row));
        }

        // Event listeners
        categoryFilter.addEventListener("change", filterAndSort);
        sortPrice.addEventListener("change", filterAndSort);
        startDate.addEventListener("change", filterAndSort);
        endDate.addEventListener("change", filterAndSort);

        // Reset filters
        resetFilters.addEventListener("click", () => {
            categoryFilter.value = "";
            sortPrice.value = "";
            startDate.value = "";
            endDate.value = "";

            table.innerHTML = "";
            rows.forEach(row => table.appendChild(row)); // Restore original
        });
    });
</script>

<?php include '../templates/footer.php'; ?>
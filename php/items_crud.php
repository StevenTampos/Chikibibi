<?php
session_start();
if ($_SESSION['Role'] !== 'Admin') {
    include '../templates/access_denied.php';
    exit();
}
include '../templates/header_admin.php';
require_once '../config.php';
if (isset($_SESSION['banner_message'])) {
    $banner_message = $_SESSION['banner_message'];
    $banner_type = $_SESSION['banner_type'];

    // Clear it so it only shows once
    unset($_SESSION['banner_message']);
    unset($_SESSION['banner_type']);
}


// Add or Update Item
if (isset($_POST['save_item'])) {
    $item_id = isset($_POST['item_id']) ? intval($_POST['item_id']) : 0;
    $name = trim($_POST['ItemName']);
    $qty = intval($_POST['Quantity']);
    $min = intval($_POST['MinimumStock']);
    $price = floatval($_POST['PricePerUnit']);
    $cat = trim($_POST['Category']);
    $expiry = !empty($_POST['ExpiryDate']) ? $_POST['ExpiryDate'] : null;
    $supplier = !empty($_POST['SupplierID']) ? $_POST['SupplierID'] : 1; // Default "Others"
    $date_recv = !empty($_POST['DateReceived']) ? $_POST['DateReceived'] : date('Y-m-d');

    if ($item_id > 0) {
        // UPDATE existing item
        $stmt = $mysqli->prepare("UPDATE inventory 
            SET ItemName=?, Quantity=?, MinimumStock=?, PricePerUnit=?, Category=?, ExpiryDate=?, SupplierID=?, DateReceived=? 
            WHERE InventoryID=?");
        $stmt->bind_param("siidssisi", $name, $qty, $min, $price, $cat, $expiry, $supplier, $date_recv, $item_id);
        if ($stmt->execute()) {
            $_SESSION['banner_type'] = "success";
            $_SESSION['banner_message'] = "✅ Item updated successfully!";
            header("Location: items_crud.php");
            exit();
        } else {
            $_SESSION['banner_type'] = "error";
            $_SESSION['banner_message'] = "❌ Failed to update item!";
        }
    } else {
        // INSERT new item
        $stmt = $mysqli->prepare("INSERT INTO inventory 
            (ItemName, Quantity, MinimumStock, PricePerUnit, Category, ExpiryDate, SupplierID, DateReceived) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("siidssis", $name, $qty, $min, $price, $cat, $expiry, $supplier, $date_recv);
        if ($stmt->execute()) {
            $_SESSION['banner_type'] = "success";
            $_SESSION['banner_message'] = "✅ Item added successfully!";
            header("Location: items_crud.php");
            exit();
        } else {
            $_SESSION['banner_type'] = "error";
            $_SESSION['banner_message'] = "❌ Failed to add item!";
        }
    }
}

// Delete Item
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    if ($mysqli->query("DELETE FROM inventory WHERE InventoryID=$id")) {
        $_SESSION['banner_type'] = "success";
        $_SESSION['banner_message'] = "✅ Item deleted successfully!";
        header("Location: items_crud.php");
        exit();
    } else {
        $_SESSION['banner_type'] = "error";
        $_SESSION['banner_message'] = "❌ Failed to delete item!";
    }
}

// Fetch data for editing
$edit_item = null;
if (isset($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    $res = $mysqli->query("SELECT * FROM inventory WHERE InventoryID=$edit_id LIMIT 1");
    $edit_item = $res->fetch_assoc();
}

// Fetch suppliers for dropdown
$suppliers = $mysqli->query("SELECT SupplierID, SupplierName FROM supplier");

// List all inventory items with supplier name
$items = $mysqli->query("SELECT inventory.*, supplier.SupplierName 
                         FROM inventory 
                         LEFT JOIN supplier ON inventory.SupplierID = supplier.SupplierID");
?>

<div id="admin-manage-inventory" class="container mx-auto p-6">

    <!-- Banner Alerts -->
    <?php if (!empty($banner_message)): ?>
        <div class="mb-4 p-4 rounded-md text-white <?= $banner_type === 'success' ? 'bg-green-500' : 'bg-red-500' ?>">
            <?= $banner_message ?>
        </div>
    <?php endif; ?>

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
        <h1 class="text-3xl font-bold text-gray-800">Manage Inventory</h1>
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
                        <th class="py-3 px-4 text-left">Actions</th>
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
                            <td class="py-3 px-4 flex gap-2">
                                <a href="?edit=<?= $row['InventoryID'] ?>"
                                    class="text-blue-500 hover:text-blue-700">Edit</a>
                                <a href="?delete=<?= $row['InventoryID'] ?>" class="text-red-500 hover:text-red-700"
                                    onclick="return confirm('Delete this item?')">Delete</a>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add/Edit Inventory Form -->
    <div class="bg-white rounded-lg shadow-md p-6 mt-10">
        <form method="post">
            <input type="hidden" name="item_id" value="<?= $edit_item['InventoryID'] ?? '' ?>">

            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-semibold text-gray-800">
                    <?= $edit_item ? "Edit Inventory Item" : "Add New Inventory Item" ?>
                </h2>
                <button type="submit" name="save_item"
                    class="bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded-md flex items-center transition duration-200">
                    <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    <?= $edit_item ? "Save Changes" : "Add Item" ?>
                </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Item Name</label>
                    <input type="text" name="ItemName" required value="<?= $edit_item['ItemName'] ?? '' ?>"
                        class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-300 focus:border-blue-300"
                        placeholder="Enter Item name" value="<?= $edit_item['ItemName'] ?? '' ?>" required>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Quantity</label>
                    <input type="number" name="Quantity" required value="<?= $edit_item['Quantity'] ?? '' ?>"
                        class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-300 focus:border-blue-300"
                        placeholder="Enter number of quantity" value="<?= $edit_item['Quantity'] ?? '' ?>" required>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Minimum Stock</label>
                    <input type="number" name="MinimumStock" required value="<?= $edit_item['MinimumStock'] ?? '' ?>"
                        class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-300 focus:border-blue-300"
                        placeholder="Enter minimum stock (for alerts)" value="<?= $edit_item['MinimumStock'] ?? '' ?>"
                        required>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Price Per Unit</label>
                    <input type="number" step="0.01" name="PricePerUnit" required
                        value="<?= $edit_item['PricePerUnit'] ?? '' ?>"
                        class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-300 focus:border-blue-300"
                        placeholder="Enter price per unit" value="<?= $edit_item['PricePerUnit'] ?? '' ?>" required>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                    <select name="Category" class="w-full px-4 py-2 border border-gray-300 rounded-md">
                        <option value="Chicken" <?= isset($edit_item['Category']) && $edit_item['Category'] === 'Chicken' ? 'selected' : '' ?>>Chicken</option>
                        <option value="Pork" <?= isset($edit_item['Category']) && $edit_item['Category'] === 'Pork' ? 'selected' : '' ?>>Pork</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Expiry Date (optional)</label>
                    <input type="date" name="ExpiryDate"
                        value="<?= isset($edit_item['ExpiryDate']) && $edit_item['ExpiryDate'] !== '0000-00-00' ? $edit_item['ExpiryDate'] : '' ?>"
                        class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-300 focus:border-blue-300">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Supplier</label>
                    <select name="SupplierID" class="w-full px-4 py-2 border border-gray-300 rounded-md">
                        <option value="">-- Select Supplier (default Others) --</option>
                        <?php while ($sup = $suppliers->fetch_assoc()) { ?>
                            <option value="<?= $sup['SupplierID'] ?>" <?= isset($edit_item['SupplierID']) && $edit_item['SupplierID'] == $sup['SupplierID'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($sup['SupplierName']) ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date Received</label>
                    <input type="date" name="DateReceived" value="<?= $edit_item['DateReceived'] ?? '' ?>"
                        class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-300 focus:border-blue-300">
                </div>
            </div>
        </form>
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
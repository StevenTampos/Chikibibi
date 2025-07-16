<?php
session_start();
if ($_SESSION['Role'] !== 'Inventory Staff') {
    include '../templates/access_denied.php';
    exit();
}

require_once '../config.php';
include '../templates/header_staff.php';

// Show banner if exists
if (isset($_SESSION['banner_message'])) {
    $banner_message = $_SESSION['banner_message'];
    $banner_type = $_SESSION['banner_type'];
    unset($_SESSION['banner_message'], $_SESSION['banner_type']);
}

// Add or Update Transaction
if (isset($_POST['save_transaction'])) {
    $transaction_id = isset($_POST['transaction_id']) ? intval($_POST['transaction_id']) : 0;
    $inventory_id   = intval($_POST['inventory_id']);
    $date           = $_POST['date'];
    $qty_used       = intval($_POST['quantity_used']);
    $action         = $_POST['action'];
    $user_id        = $_SESSION['UserID'];

    if ($transaction_id > 0) {
        // 1. Fetch original transaction
        $original = $mysqli->query("SELECT * FROM transaction WHERE TransactionID = $transaction_id")->fetch_assoc();
        $original_qty       = $original['QuantityUsed'];
        $original_action    = $original['ActionType'];
        $original_inventory = $original['InventoryID'];

        // 2. Reverse original effect on inventory
        if ($original_action === 'Usage') {
            $mysqli->query("UPDATE inventory SET Quantity = Quantity + $original_qty WHERE InventoryID = $original_inventory");
        } else {
            $mysqli->query("UPDATE inventory SET Quantity = Quantity - $original_qty WHERE InventoryID = $original_inventory");
        }

        // 3. Apply new values
        $stmt = $mysqli->prepare("
            UPDATE transaction 
            SET InventoryID=?, Date=?, QuantityUsed=?, ActionType=?, UserID=? 
            WHERE TransactionID=?
        ");
        $stmt->bind_param("isisii", $inventory_id, $date, $qty_used, $action, $user_id, $transaction_id);

        if ($stmt->execute()) {
            // 4. Apply new effect
            if ($action === 'Usage') {
                $mysqli->query("UPDATE inventory SET Quantity = Quantity - $qty_used WHERE InventoryID = $inventory_id");
            } else {
                $mysqli->query("UPDATE inventory SET Quantity = Quantity + $qty_used WHERE InventoryID = $inventory_id");
            }

            $_SESSION['banner_type']    = "success";
            $_SESSION['banner_message'] = "✅ Transaction updated successfully!";
        } else {
            $_SESSION['banner_type']    = "error";
            $_SESSION['banner_message'] = "❌ Failed to update transaction!";
        }

        header("Location: transactions.php");
        exit();
    } else {
        // INSERT NEW
        $stmt = $mysqli->prepare("
            INSERT INTO transaction (InventoryID, Date, QuantityUsed, ActionType, UserID) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("isisi", $inventory_id, $date, $qty_used, $action, $user_id);

        if ($stmt->execute()) {
            // Apply effect on inventory
            if ($action === 'Usage') {
                $mysqli->query("UPDATE inventory SET Quantity = Quantity - $qty_used WHERE InventoryID = $inventory_id");
            } else {
                $mysqli->query("UPDATE inventory SET Quantity = Quantity + $qty_used WHERE InventoryID = $inventory_id");
            }

            $_SESSION['banner_type']    = "success";
            $_SESSION['banner_message'] = "✅ Transaction recorded successfully!";
        } else {
            $_SESSION['banner_type']    = "error";
            $_SESSION['banner_message'] = "❌ Failed to record transaction!";
        }

        header("Location: transactions.php");
        exit();
    }
}

// Fetch transaction for editing
$edit_transaction = null;
if (isset($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    $res = $mysqli->query("SELECT * FROM transaction WHERE TransactionID=$edit_id LIMIT 1");
    $edit_transaction = $res->fetch_assoc();
}

// Fetch all inventory items for dropdown
$items = $mysqli->query("SELECT InventoryID, ItemName FROM inventory");

// Fetch all transactions with user name & role
$result = $mysqli->query("
    SELECT t.TransactionID, t.Date, t.QuantityUsed, t.ActionType, 
           i.ItemName,
           u.Name AS UserName, u.Role AS UserRole
    FROM transaction t 
    JOIN inventory i ON t.InventoryID = i.InventoryID 
    JOIN user u ON t.UserID = u.UserID
    ORDER BY t.Date DESC
");
?>

<div id="admin-manage-transactions" class="container mx-auto p-6">

    <!-- Banner Alert -->
    <?php if (!empty($banner_message)): ?>
        <div class="mb-4 p-4 rounded-md text-white <?= $banner_type === 'success' ? 'bg-green-500' : 'bg-red-500' ?>">
            <?= $banner_message ?>
        </div>
    <?php endif; ?>

    <!-- Header -->
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
        <h1 class="text-3xl font-bold text-gray-800">Inventory Transactions</h1>
    </div>

    <!-- Transactions Table -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-semibold text-gray-800">Transaction History</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full bg-white">
                <thead>
                    <tr class="bg-gray-100 text-gray-700 text-sm leading-normal">
                        <th class="py-3 px-4 text-left">Date</th>
                        <th class="py-3 px-4 text-left">Item</th>
                        <th class="py-3 px-4 text-left">Quantity</th>
                        <th class="py-3 px-4 text-left">Type</th>
                        <th class="py-3 px-4 text-left">User</th>
                    </tr>
                </thead>
                <tbody class="text-gray-600 text-sm">
                    <?php while ($row = $result->fetch_assoc()) { ?>
                        <tr class="border-b border-gray-200 hover:bg-gray-50">
                            <td class="py-3 px-4"><?= htmlspecialchars($row['Date']) ?></td>
                            <td class="py-3 px-4"><?= htmlspecialchars($row['ItemName']) ?></td>
                            <td class="py-3 px-4"><?= htmlspecialchars($row['QuantityUsed']) ?></td>
                            <td class="py-3 px-4">
                                <?php if ($row['ActionType'] === 'Usage') { ?>
                                    <span class="bg-red-100 text-red-700 text-xs font-medium px-2 py-0.5 rounded">Usage</span>
                                <?php } else { ?>
                                    <span class="bg-green-100 text-green-700 text-xs font-medium px-2 py-0.5 rounded">Restock</span>
                                <?php } ?>
                            </td>

                            <td class="py-3 px-4">
                                <?= htmlspecialchars($row['UserName']) ?>
                                <?php if ($row['UserRole'] === 'Admin') { ?>
                                    <span class="ml-2 bg-blue-100 text-blue-800 text-xs font-medium px-2 py-0.5 rounded">Admin</span>
                                <?php } else { ?>
                                    <span class="ml-2 bg-green-100 text-green-800 text-xs font-medium px-2 py-0.5 rounded">Staff</span>
                                <?php } ?>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add/Edit Transaction Form -->
    <div class="bg-white rounded-lg shadow-md p-6 mt-10">
        <form method="post">
            <input type="hidden" name="transaction_id" value="<?= $edit_transaction['TransactionID'] ?? '' ?>">

            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-semibold text-gray-800">
                    <?= $edit_transaction ? "Edit Transaction" : "Record New Transaction" ?>
                </h2>
                <button type="submit" name="save_transaction"
                    class="bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded-md flex items-center transition duration-200">
                    <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    <?= $edit_transaction ? "Save Changes" : "Add Transaction" ?>
                </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Item selection -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Item</label>
                    <select name="inventory_id" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-green-300">
                        <option disabled selected>Select Item</option>
                        <?php while ($item = $items->fetch_assoc()) { ?>
                            <option value="<?= $item['InventoryID'] ?>"
                                <?= isset($edit_transaction['InventoryID']) && $edit_transaction['InventoryID'] == $item['InventoryID'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($item['ItemName']) ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>

                <!-- Quantity -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Quantity</label>
                    <input type="number" name="quantity_used" required
                        value="<?= $edit_transaction['QuantityUsed'] ?? '' ?>"
                        class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-green-300"
                        placeholder="Enter quantity">
                </div>

                <!-- Action Type -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Action</label>
                    <select name="action" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-green-300">
                        <option value="Usage" <?= isset($edit_transaction['ActionType']) && $edit_transaction['ActionType'] === 'Usage' ? 'selected' : '' ?>>Usage</option>
                        <option value="Restock" <?= isset($edit_transaction['ActionType']) && $edit_transaction['ActionType'] === 'Restock' ? 'selected' : '' ?>>Restock</option>
                    </select>
                </div>

                <!-- Date -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date</label>
                    <input type="date" name="date" required
                        value="<?= $edit_transaction['Date'] ?? '' ?>"
                        class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-green-300">
                </div>
            </div>
        </form>
    </div>
</div>

<?php include '../templates/footer.php'; ?>
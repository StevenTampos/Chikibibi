<?php
session_start();
if ($_SESSION['Role'] !== 'Inventory Staff') {
    include '../templates/access_denied.php';
    exit();
}
include '../templates/header_staff.php';
?>

<!-- main staff div -->
<div id="staff-main" class="container mx-auto p-6">
    <h1 class="text-3xl font-bold text-gray-800 mb-8">Staff Dashboard</h1>

<!-- view inventory/items -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <a href="items.php" class="card">
            <div class="section-card bg-white rounded-lg shadow-md overflow-hidden"
                onclick="showStaffSection('inventory-items')">
                <div class="p-6">
                    <div class="bg-green-100 rounded-full w-16 h-16 flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                    </div>
                    <h2 class="text-xl font-semibold text-gray-800 mb-2">Inventory Items</h2>
                    <p class="text-gray-600">View and monitor all inventory items and stock quantities</p>
                </div>
                <div class="bg-green-50 px-6 py-3">
                    <span class="text-green-500 font-medium">View Details →</span>
                </div>
            </div>
        </a>

<!-- suppliers view -->
        <a href="suppliers.php" class="card">
            <div class="section-card bg-white rounded-lg shadow-md overflow-hidden"
                onclick="showStaffSection('suppliers')">
                <div class="p-6">
                    <div class="bg-blue-100 rounded-full w-16 h-16 flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                            </path>
                        </svg>
                    </div>
                    <h2 class="text-xl font-semibold text-gray-800 mb-2">Suppliers</h2>
                    <p class="text-gray-600">View supplier information and contact details</p>
                </div>
                <div class="bg-blue-50 px-6 py-3">
                    <span class="text-blue-500 font-medium">View Details →</span>
                </div>
            </div>
        </a>

<!-- transactions crud -->
        <a href="transactions.php" class="card">
            <div class="section-card bg-white rounded-lg shadow-md overflow-hidden"
                onclick="showStaffSection('staff-transactions')">
                <div class="p-6">
                    <div class="bg-yellow-100 rounded-full w-16 h-16 flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01">
                            </path>
                        </svg>
                    </div>
                    <h2 class="text-xl font-semibold text-gray-800 mb-2">Transactions</h2>
                    <p class="text-gray-600">Record and view inventory transactions</p>
                </div>
                <div class="bg-yellow-50 px-6 py-3">
                    <span class="text-yellow-500 font-medium">View Details →</span>
                </div>
            </div>
        </a>

<!-- alerts -->
        <a href="alerts.php" class="card alert">
            <div class="section-card bg-white rounded-lg shadow-md overflow-hidden">
                <div class="p-6">
                    <div class="bg-red-100 rounded-full w-16 h-16 flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                            </path>
                        </svg>
                    </div>
                    <h2 class="text-xl font-semibold text-gray-800 mb-2">Low Stock Alerts</h2>
                    <p class="text-gray-600">View items that need to be restocked</p>
                </div>
                <div class="bg-red-50 px-6 py-3">
                    <span class="text-red-500 font-medium">View Details →</span>
                </div>
            </div>
        </a>
    </div>
</div>

<?php include '../templates/footer.php'; ?>
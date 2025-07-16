<!DOCTYPE html>
<html>
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&amp;display=swap"
    rel="stylesheet">
<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>

<head>
    <title>Chikibibi Inventory</title>
    <link rel="stylesheet" href="../css/style.css">
</head>

<div id="staff-dashboard" class="min-h-screen staff-bg">
    <nav class="bg-white shadow-md p-4">
        <div class="container mx-auto flex justify-between items-center">
            <div class="flex items-center">
                <svg class="h-8 w-8 mr-2" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="50" cy="50" r="45" stroke="#4CAF50" stroke-width="8"></circle>
                    <path d="M30 50L45 65L70 35" stroke="#4CAF50" stroke-width="8" stroke-linecap="round"
                        stroke-linejoin="round"></path>
                </svg>
                <span class="text-xl font-bold text-gray-800">Chikibibi Staff</span>
            </div>
            <div class="flex items-center">
                <div class="mr-4">
                    <span class="text-sm text-gray-600">Welcome, Staff!</span>
                </div>
                <a href="logout.php" class="logout-link">
                    <button
                        class="bg-green-500 hover:bg-green-600 text-white py-1 px-3 rounded-md text-sm transition duration-200">
                        Logout
                    </button>
                </a>
            </div>
        </div>
    </nav>
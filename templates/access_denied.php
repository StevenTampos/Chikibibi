<!DOCTYPE html>
<html>
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&amp;display=swap"
  rel="stylesheet">
<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>

<head>
  <title>Access Denied</title>
  <link rel="stylesheet" href="../css/style.css">
</head>

<div id="admin-dashboard">
  <nav class="bg-white shadow-md p-4">
    <div class="container mx-auto flex justify-between items-center">

      <div class="flex items-center">
        <a href="<?php echo $_SESSION['Role'] === 'Admin' ? 'admin' : 'staff'; ?>.php" class="back-btn">
          <button
            class="bg-red-300 hover:bg-red-500 text-gray-700 py-2 px-4 rounded-md flex items-center mr-4 transition duration-200">
            <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"
              xmlns="http://www.w3.org/2000/svg">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18">
              </path>
            </svg>
            Back
          </button>
        </a>
      </div>

      <div class="flex items-center">
        <span class="text-xl font-bold text-gray-800">🚫 You do not have access to this page.</span>
      </div>
      <div class="flex items-center">
        <a href="logout.php" class="logout-link">
          <button class="bg-gray-500 hover:bg-red-600 text-white py-1 px-3 rounded-md text-sm transition duration-200">
            Logout
          </button>
        </a>
      </div>
    </div>

  </nav>

</html>
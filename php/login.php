<?php
session_start();
require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $mysqli->prepare("SELECT UserID, Name, Role, Password FROM User WHERE Email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 1) {
        $stmt->bind_result($UserID, $Name, $Role, $StoredPassword);
        $stmt->fetch();

        if ($password === $StoredPassword) { // bypassed check
            $_SESSION['UserID'] = $UserID;
            $_SESSION['Name'] = $Name;
            $_SESSION['Role'] = $Role;
            
            if ($Role === 'Admin') {
                header("Location: admin.php");
            } elseif ($Role === 'Inventory Staff') {
                header("Location: staff.php");
            } else {
                $error = "Unknown role.";
            }
            exit();
        } else {
            $error = "Incorrect password.";
        }
    } else {
        $error = "User not found.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Chikibibi | Login</title>
  <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div id="login-page" class="min-h-screen flex items-center justify-center login-bg p-4">
        <div class="bg-white rounded-lg shadow-xl p-8 w-full max-w-md">
            <div class="text-center mb-8">
                <svg class="mx-auto h-16 w-16" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="50" cy="50" r="45" stroke="#FF6B6B" stroke-width="8"></circle>
                    <path d="M30 50L45 65L70 35" stroke="#FF6B6B" stroke-width="8" stroke-linecap="round" stroke-linejoin="round"></path>
                </svg>
                <h1 class="text-3xl font-bold mt-4 text-gray-800">Chikibibi</h1>
                <p class="text-gray-600">Inventory Management System</p>
            </div>
            <?php if (!empty($error)) echo "<p class='error-msg'>$error</p>"; ?>
            <form method="post">
                <div class="mb-6">
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" id="email" name="email" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-pink-300 focus:border-pink-300" placeholder="Enter your username">
                </div>
                <div class="mb-6">
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                    <input type="password" id="password" name="password" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-pink-300 focus:border-pink-300" placeholder="Enter your password">
                </div>
                <button type="submit" class="w-full bg-gradient-to-r from-pink-500 to-red-500 text-white py-2 px-4 rounded-md hover:from-pink-600 hover:to-red-600 focus:outline-none focus:ring-2 focus:ring-pink-500 focus:ring-opacity-50 transition duration-200">
                    Sign In
                </button>
            </form>
        </div>
    </div>
</body>
</html>

<?php
session_start();
if ($_SESSION['Role'] !== 'Admin') {
    include '../templates/access_denied.php';
    exit();
}
require_once '../config.php';
include '../templates/header_admin.php';

if (isset($_SESSION['banner_message'])) {
    $banner_message = $_SESSION['banner_message'];
    $banner_type = $_SESSION['banner_type'];
    unset($_SESSION['banner_message'], $_SESSION['banner_type']);
}

// Add or Update User 
if (isset($_POST['save_user'])) {
    $name = trim($_POST['name']);
    $role = trim($_POST['role']);
    $email = trim($_POST['email']);
    $password = $_POST['password']; // raw input
    $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;

    // Check duplicate email
    $check = $mysqli->prepare("SELECT UserID FROM user WHERE Email = ? AND UserID != ?");
    $check->bind_param("si", $email, $user_id);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $_SESSION['banner_type'] = "error";
        $_SESSION['banner_message'] = "❌ Email already exists!";
        header("Location: users_crud.php");
        exit();
    } else {
        // Cannot demote self
        if ($user_id > 0) {
            if ($user_id == $_SESSION['UserID'] && $role !== 'Admin') {
                $_SESSION['banner_type'] = "error";
                $_SESSION['banner_message'] = "❌ You cannot change your own role!";
                header("Location: users_crud.php");
                exit();
            } else {
                // Updating user
                if (!empty($password)) {
                    // Hash new password
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $mysqli->prepare("UPDATE user SET Name=?, Role=?, Email=?, Password=? WHERE UserID=?");
                    $stmt->bind_param("ssssi", $name, $role, $email, $hashedPassword, $user_id);
                } else {
                    // Keep existing password
                    $stmt = $mysqli->prepare("UPDATE user SET Name=?, Role=?, Email=? WHERE UserID=?");
                    $stmt->bind_param("sssi", $name, $role, $email, $user_id);
                }

                if ($stmt->execute()) {
                    $_SESSION['banner_type'] = "success";
                    $_SESSION['banner_message'] = "✅ User updated successfully!";
                } else {
                    $_SESSION['banner_type'] = "error";
                    $_SESSION['banner_message'] = "❌ Failed to update user!";
                }
                header("Location: users_crud.php");
                exit();
            }
        } else {
            // Insert new user
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $mysqli->prepare("INSERT INTO user (Name, Role, Email, Password) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $name, $role, $email, $hashedPassword);

            if ($stmt->execute()) {
                $_SESSION['banner_type'] = "success";
                $_SESSION['banner_message'] = "✅ User added successfully!";
            } else {
                $_SESSION['banner_type'] = "error";
                $_SESSION['banner_message'] = "❌ Failed to add user!";
            }
            header("Location: users_crud.php");
            exit();
        }
    }
}

// Delete user
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    if ($mysqli->query("DELETE FROM user WHERE UserID=$id")) {
        $_SESSION['banner_type'] = "success";
        $_SESSION['banner_message'] = "✅ User deleted successfully!";
    } else {
        $_SESSION['banner_type'] = "error";
        $_SESSION['banner_message'] = "❌ Failed to delete user!";
    }
    header("Location: users_crud.php");
    exit();
}

// Fetch user for editing
$edit_user = null;
if (isset($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    $res = $mysqli->query("SELECT * FROM user WHERE UserID=$edit_id LIMIT 1");
    $edit_user = $res->fetch_assoc();
}

// ✅ List all users
$result = $mysqli->query("SELECT * FROM user ORDER BY CreatedAt DESC");
?>

<div id="admin-manage-users" class="container mx-auto p-6">

    <!-- banner alerts -->
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
        <h1 class="text-3xl font-bold text-gray-800">Manage Users</h1>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-semibold text-gray-800">User List</h2>
        </div>

        <!-- users table -->
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white">
                <thead>
                    <tr class="bg-gray-100 text-gray-700 text-sm leading-normal">
                        <th class="py-3 px-4 text-left">Name</th>
                        <th class="py-3 px-4 text-left">Email</th>
                        <th class="py-3 px-4 text-left">Role</th>
                        <th class="py-3 px-4 text-left">Actions</th>
                    </tr>
                </thead>
                <tbody class="text-gray-600 text-sm">
                    <?php while ($row = $result->fetch_assoc()) { ?>
                        <tr class="border-b border-gray-200 hover:bg-gray-50">
                            <td class="py-3 px-4"><?= htmlspecialchars($row['Name']) ?></td>
                            <td class="py-3 px-4"><?= htmlspecialchars($row['Email']) ?></td>
                            <?php if ($row['Role'] === 'Admin') { ?>
                                <td class="py-3 px-4">
                                    <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2 py-0.5 rounded">Admin</span>
                                </td>
                            <?php } else { ?>
                                <td class="py-3 px-4">
                                    <span
                                        class="bg-green-100 text-green-800 text-xs font-medium px-2 py-0.5 rounded">Staff</span>
                                </td>
                            <?php } ?>
                            <td class="py-3 px-4">
                                <div class="flex gap-2">
                                    <a href="?edit=<?= $row['UserID'] ?>" class="text-blue-500 hover:text-blue-700">Edit</a>
                                    <a href="?delete=<?= $row['UserID'] ?>" class="text-red-500 hover:text-red-700"
                                        onclick="return confirm('Are you sure?')">Delete</a>
                                </div>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>

        <!-- add/edit form -->
        <div id="admin-create-users" class="container mx-auto p-6 mt-10">
            <form method="post">
                <input type="hidden" name="user_id" value="<?= $edit_user['UserID'] ?? '' ?>">

                <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-xl font-semibold text-gray-800">
                            <?= $edit_user ? "Edit User" : "Register New User" ?>
                        </h2>
                        <button type="submit" name="save_user"
                            class="bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded-md flex items-center transition duration-200">
                            <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            <?= $edit_user ? "Save Changes" : "Add New User" ?>
                        </button>
                    </div>

                    <div class="mb-6">
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                        <input type="text" id="name" name="name"
                            class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-300 focus:border-blue-300"
                            placeholder="Enter Full name" value="<?= $edit_user['Name'] ?? '' ?>" required>
                    </div>

                    <div class="mb-6">
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input type="email" id="email" name="email"
                            class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-300 focus:border-blue-300"
                            placeholder="Email" value="<?= $edit_user['Email'] ?? '' ?>" required>
                    </div>

                    <div class="mb-6">
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
                            <?= $edit_user ? "New Password (leave blank to keep current)" : "Password" ?>
                        </label>
                        <input type="password" id="password" name="password"
                            class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-300 focus:border-blue-300"
                            placeholder="<?= $edit_user ? "Leave blank to keep existing password" : "Password" ?>">
                    </div>

                    <div class="mb-6">
                        <label for="role" class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                        <select name="role"
                            class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-300 focus:border-blue-300">
                            <option value="Admin" <?= isset($edit_user['Role']) && $edit_user['Role'] === 'Admin' ? 'selected' : '' ?>>Admin</option>
                            <option value="Inventory Staff" <?= isset($edit_user['Role']) && $edit_user['Role'] === 'Inventory Staff' ? 'selected' : '' ?>>Inventory Staff</option>
                        </select>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
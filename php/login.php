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
                header("Location: dashboard_admin.php");
            } elseif ($Role === 'Inventory Staff') {
                header("Location: dashboard_staff.php");
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
<body class="login-bg">
  <div class="login-container fade-in">
    <div class="login-card">
      <h2>🔐 Chikibibi Login</h2>
      <?php if (!empty($error)) echo "<p class='error-msg'>$error</p>"; ?>
      <form method="post">
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Login</button>
      </form>
    </div>
  </div>
</body>
</html>

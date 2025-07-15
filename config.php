<?php
$mysqli = new mysqli("localhost", "root", "", "chikibibi_db");
if ($mysqli->connect_error) {
    die("Database connection failed: " . $mysqli->connect_error);
}
?>

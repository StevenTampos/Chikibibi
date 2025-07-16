<?php
$mysqli = new mysqli("localhost", "s23103016_chikibibi", "admin123", "s23103016_chikibibi");
if ($mysqli->connect_error) {
    die("Database connection failed: " . $mysqli->connect_error);
}
?>

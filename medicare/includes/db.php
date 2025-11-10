<?php
$host = '127.0.0.1';   // keep exactly this
$user = 'root';        // XAMPP default
$pass = '';            // leave empty unless you set one
$db   = 'medicare_db'; // the database you imported
$port = 3307;          // matches your MySQL

$conn = new mysqli($host, $user, $pass, $db, $port);
if ($conn->connect_error) {
  die('DB connection failed: ' . $conn->connect_error);
}

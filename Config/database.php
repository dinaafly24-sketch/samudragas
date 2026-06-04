<?php
$host   = 'localhost';
$user   = 'root';
$pass   = '';
$dbname = 'samudragas';

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");      
date_default_timezone_set('Asia/Makassar'); // WITA UTC+8 - Banjarmasin
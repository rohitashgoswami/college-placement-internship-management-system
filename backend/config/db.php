<?php
$host = "localhost";
$user = "root";
$password = "";      
$database = "college_placement_system";
$port = 3307;        

$conn = mysqli_connect($host, $user, $password, $database, $port);

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}
?>
<?php
include "../config/db.php";
include "../includes/admin_auth.php";

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;

if ($id > 0) {
    mysqli_query($conn, "DELETE FROM internships WHERE id='$id'");
}

header("Location: manage_internships.php?page=" . $page);
exit();
?>

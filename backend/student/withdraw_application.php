<?php
include "../config/db.php";
include "../includes/student_auth.php";
include "../includes/flash.php";

$student_id = (int) $_SESSION['student_id'];
$application_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$redirect = isset($_GET['redirect']) ? $_GET['redirect'] : 'my_applications.php';

if ($application_id <= 0) {
    set_flash_message('error', 'Invalid application selected.');
    header("Location: $redirect");
    exit();
}

$check = mysqli_query($conn, "
    SELECT id, status
    FROM applications
    WHERE id = '$application_id' AND student_id = '$student_id'
    LIMIT 1
");
$application = mysqli_fetch_assoc($check);

if (!$application) {
    set_flash_message('error', 'Application not found.');
} elseif (in_array($application['status'], array('Selected', 'Offered', 'Rejected'), true)) {
    set_flash_message('error', 'This application can no longer be withdrawn.');
} else {
    mysqli_query($conn, "DELETE FROM applications WHERE id = '$application_id' AND student_id = '$student_id'");
    set_flash_message('success', 'Application withdrawn successfully.');
}

header("Location: $redirect");
exit();
?>

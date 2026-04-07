<?php
include "../config/db.php";
include "../includes/student_auth.php";
include "../includes/flash.php";

$student_id = (int) $_SESSION['student_id'];
$internship_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$redirect = isset($_GET['redirect']) ? $_GET['redirect'] : 'dashboard.php';

if ($internship_id <= 0) {
    set_flash_message('error', 'Invalid internship selected.');
    header("Location: $redirect");
    exit();
}

$internship_check = mysqli_query($conn, "SELECT id FROM internships WHERE id = '$internship_id' LIMIT 1");
if (mysqli_num_rows($internship_check) === 0) {
    set_flash_message('error', 'Internship not found.');
    header("Location: $redirect");
    exit();
}

$check_query = "SELECT id FROM applications WHERE student_id='$student_id' AND internship_id='$internship_id' LIMIT 1";
$check_result = mysqli_query($conn, $check_query);

if (mysqli_num_rows($check_result) == 0) {
    $insert_query = "INSERT INTO applications (student_id, internship_id, status) VALUES ('$student_id', '$internship_id', 'Pending')";
    mysqli_query($conn, $insert_query);
    set_flash_message('success', 'Application submitted successfully.');
} else {
    set_flash_message('info', 'You have already applied for this opportunity.');
}

header("Location: $redirect");
exit();
?>

<?php
include "../config/db.php";
include "../includes/student_auth.php";
include "../includes/flash.php";

$student_id = (int) $_SESSION['student_id'];
$internship_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$redirect = isset($_GET['redirect']) ? $_GET['redirect'] : 'job_board.php';

if ($internship_id <= 0) {
    set_flash_message('error', 'Invalid internship selected.');
    header("Location: $redirect");
    exit();
}

$saved_result = mysqli_query($conn, "SELECT id FROM saved_jobs WHERE student_id = '$student_id' AND internship_id = '$internship_id' LIMIT 1");

if (mysqli_num_rows($saved_result) > 0) {
    mysqli_query($conn, "DELETE FROM saved_jobs WHERE student_id = '$student_id' AND internship_id = '$internship_id'");
    set_flash_message('success', 'Job removed from saved list.');
} else {
    mysqli_query($conn, "INSERT INTO saved_jobs (student_id, internship_id) VALUES ('$student_id', '$internship_id')");
    set_flash_message('success', 'Job saved successfully.');
}

header("Location: $redirect");
exit();
?>

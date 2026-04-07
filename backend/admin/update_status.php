<?php
include "../config/db.php";
include "../includes/admin_auth.php";

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$status = isset($_GET['status']) ? $_GET['status'] : '';
$page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
$status_filter = isset($_GET['status_filter']) ? $_GET['status_filter'] : '';
$allowed_statuses = array('Selected', 'Rejected', 'Pending', 'Interview', 'Shortlisted');

if ($id > 0 && in_array($status, $allowed_statuses, true)) {
    $safe_status = mysqli_real_escape_string($conn, $status);
    $query = "UPDATE applications SET status='$safe_status' WHERE id='$id'";
    mysqli_query($conn, $query);
}

$redirect_url = "view_applications.php?page=" . $page;
if ($status_filter != '') {
    $redirect_url .= "&status=" . urlencode($status_filter);
}

header("Location: " . $redirect_url);
exit();

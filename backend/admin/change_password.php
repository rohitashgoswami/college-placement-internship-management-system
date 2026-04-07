<?php
include "../config/db.php";
include "../includes/admin_auth.php";

$current_admin_page = 'change_password.php';
$admin_page_title = 'Admin Settings';
$admin_page_description = 'Update the admin password and keep access secure.';

$message = '';
$error = '';
$admin_id = (int) $_SESSION['admin_id'];

if (isset($_POST['update_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    $admin_result = mysqli_query($conn, "SELECT password FROM admin WHERE id = '$admin_id'");
    $admin = mysqli_fetch_assoc($admin_result);

    $stored_password = $admin ? $admin['password'] : '';
    $matches_current = password_verify($current_password, $stored_password) || $current_password === $stored_password;

    if (!$matches_current) {
        $error = 'Current password is incorrect.';
    } elseif ($new_password !== $confirm_password) {
        $error = 'New password and confirmation do not match.';
    } elseif (strlen($new_password) < 6) {
        $error = 'New password must be at least 6 characters long.';
    } else {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $safe_password = mysqli_real_escape_string($conn, $hashed_password);
        mysqli_query($conn, "UPDATE admin SET password = '$safe_password' WHERE id = '$admin_id'");
        $message = 'Password updated successfully.';
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Admin Settings</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="min-h-screen bg-gray-100">
    <?php include "../includes/admin_topbar.php"; ?>

    <div class="mx-auto max-w-3xl p-6">
        <div class="rounded-2xl bg-white p-6 shadow">
            <h3 class="text-xl font-bold text-gray-800">Change Password</h3>
            <p class="mt-1 text-sm text-gray-500">Your next login will work with the updated password.</p>

            <?php if ($message !== '') { ?>
                <div class="mt-5 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php } ?>

            <?php if ($error !== '') { ?>
                <div class="mt-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php } ?>

            <form method="POST" class="mt-6 space-y-4">
                <div>
                    <label class="mb-2 block text-sm font-semibold text-gray-700">Current Password</label>
                    <input type="password" name="current_password" class="w-full rounded-lg border px-4 py-3" required>
                </div>

                <div>
                    <label class="mb-2 block text-sm font-semibold text-gray-700">New Password</label>
                    <input type="password" name="new_password" class="w-full rounded-lg border px-4 py-3" required>
                </div>

                <div>
                    <label class="mb-2 block text-sm font-semibold text-gray-700">Confirm New Password</label>
                    <input type="password" name="confirm_password" class="w-full rounded-lg border px-4 py-3" required>
                </div>

                <button type="submit" name="update_password" class="rounded-lg bg-blue-600 px-5 py-3 font-semibold text-white hover:bg-blue-700">
                    Update Password
                </button>
            </form>
        </div>
    </div>

</body>

</html>

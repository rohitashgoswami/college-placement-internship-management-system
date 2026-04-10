<?php
include "../config/db.php";
include "../includes/student_auth.php";
include "../includes/flash.php";
include "../includes/student_layout.php";

$current_page = basename($_SERVER['PHP_SELF']);
$student_page_title = 'Settings';
$student_page_description = 'Update your password and keep your student account secure.';
$student_badge = 'Settings';
$student_id = (int) $_SESSION['student_id'];
$message = '';
$error = '';

if (isset($_POST['update_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    $student_result = mysqli_query($conn, "SELECT password FROM students WHERE id = '$student_id' LIMIT 1");
    $student = mysqli_fetch_assoc($student_result);

    if (!$student || !password_verify($current_password, $student['password'])) {
        $error = 'Current password is incorrect.';
    } elseif ($new_password !== $confirm_password) {
        $error = 'New password and confirm password do not match.';
    } elseif (strlen($new_password) < 6) {
        $error = 'New password must be at least 6 characters.';
    } else {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $safe_password = mysqli_real_escape_string($conn, $hashed_password);
        mysqli_query($conn, "UPDATE students SET password = '$safe_password' WHERE id = '$student_id'");
        $message = 'Password updated successfully.';
    }
}

render_student_page_start('Settings');
?>

    <?php include "layout/sidebar.php"; ?>

    <div class="ml-64 min-h-screen flex-1">
        <?php include "layout/topbar.php"; ?>

        <div class="p-8 pt-32">
            <div class="mx-auto max-w-3xl rounded-2xl bg-white p-6 shadow">
                <h2 class="text-xl font-bold text-slate-900">Change Password</h2>
                <p class="mt-1 text-sm text-slate-500">Use a strong password to protect your placement account.</p>

                <?php if ($message !== '') { ?>
                    <div class="mt-5 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700"><?php echo htmlspecialchars($message); ?></div>
                <?php } ?>
                <?php if ($error !== '') { ?>
                    <div class="mt-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"><?php echo htmlspecialchars($error); ?></div>
                <?php } ?>

                <form method="POST" class="mt-6 space-y-4">
                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-700">Current Password</label>
                        <input type="password" name="current_password" class="w-full rounded-lg border px-4 py-3" required>
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-700">New Password</label>
                        <input type="password" name="new_password" class="w-full rounded-lg border px-4 py-3" required>
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-700">Confirm New Password</label>
                        <input type="password" name="confirm_password" class="w-full rounded-lg border px-4 py-3" required>
                    </div>
                    <button type="submit" name="update_password" class="rounded-lg bg-blue-600 px-5 py-3 text-sm font-semibold text-white hover:bg-blue-700">Update Password</button>
                </form>
            </div>
        </div>
    </div>

</body>

</html>

<?php
include "backend/config/db.php";
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['student_id'])) {
    header("Location: backend/student/dashboard.php");
    exit();
}

if (isset($_SESSION['admin_id'])) {
    header("Location: backend/admin/dashboard.php");
    exit();
}

$error = "";
$role = isset($_POST['role']) ? $_POST['role'] : 'student';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$username = isset($_POST['username']) ? trim($_POST['username']) : '';

if (isset($_POST['login'])) {
    $password = $_POST['password'];

    if ($role === 'admin') {
        $safe_username = mysqli_real_escape_string($conn, $username);
        $result = mysqli_query($conn, "SELECT * FROM admin WHERE username='$safe_username' LIMIT 1");

        if ($result && mysqli_num_rows($result) === 1) {
            $admin = mysqli_fetch_assoc($result);
            $stored_password = $admin['password'];
            $is_valid = password_verify($password, $stored_password) || $password === $stored_password;

            if ($is_valid) {
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_name'] = $admin['username'];
                header("Location: backend/admin/dashboard.php");
                exit();
            }
        }

        $error = "Invalid admin username or password.";
    } else {
        $safe_email = mysqli_real_escape_string($conn, $email);
        $result = mysqli_query($conn, "SELECT * FROM students WHERE email='$safe_email' LIMIT 1");

        if ($result && mysqli_num_rows($result) === 1) {
            $student = mysqli_fetch_assoc($result);

            if (password_verify($password, $student['password'])) {
                $_SESSION['student_id'] = $student['id'];
                $_SESSION['student_name'] = $student['name'];
                header("Location: backend/student/dashboard.php");
                exit();
            }
        }

        $error = "Invalid student email or password.";
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Login | PlaceHub</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="min-h-screen bg-slate-100 flex items-center justify-center p-6">
    <div class="grid w-full max-w-5xl overflow-hidden rounded-3xl bg-white shadow-2xl lg:grid-cols-2">
        <div class="bg-slate-900 p-10 text-white">
            <p class="text-sm uppercase tracking-[0.3em] text-slate-400">PlaceHub</p>
            <h1 class="mt-4 text-4xl font-bold leading-tight">One login page for both students and admins.</h1>
            <p class="mt-4 text-slate-300">Choose your role, sign in, and continue to the correct dashboard without changing the URL manually.</p>

            <div class="mt-10 space-y-4 text-sm text-slate-300">
                <div class="rounded-2xl bg-slate-800/80 p-4">
                    <p class="font-semibold text-white">Student access</p>
                    <p class="mt-1">Students can log in here and register from the student-only registration page.</p>
                </div>
                <div class="rounded-2xl bg-slate-800/80 p-4">
                    <p class="font-semibold text-white">Admin access</p>
                    <p class="mt-1">Admins can log in here, but admin registration is intentionally disabled.</p>
                </div>
            </div>
        </div>

        <div class="p-10">
            <div class="mb-8">
                <h2 class="text-3xl font-bold text-slate-900">Sign In</h2>
                <p class="mt-2 text-sm text-slate-500">Select who you are, then enter the correct credentials.</p>
            </div>

            <?php if ($error !== "") { ?>
                <div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php } ?>

            <form method="POST" class="space-y-5">
                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Login As</label>
                    <select name="role" id="role" class="w-full rounded-xl border px-4 py-3" onchange="toggleLoginFields()">
                        <option value="student" <?php echo ($role === 'student') ? 'selected' : ''; ?>>Student</option>
                        <option value="admin" <?php echo ($role === 'admin') ? 'selected' : ''; ?>>Admin</option>
                    </select>
                </div>

                <div id="studentFields" class="<?php echo ($role === 'admin') ? 'hidden' : ''; ?>">
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Student Email</label>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($email); ?>" class="w-full rounded-xl border px-4 py-3" placeholder="Enter your student email">
                </div>

                <div id="adminFields" class="<?php echo ($role === 'admin') ? '' : 'hidden'; ?>">
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Admin Username</label>
                    <input type="text" name="username" value="<?php echo htmlspecialchars($username); ?>" class="w-full rounded-xl border px-4 py-3" placeholder="Enter admin username">
                </div>

                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Password</label>
                    <input type="password" name="password" class="w-full rounded-xl border px-4 py-3" placeholder="Enter your password" required>
                </div>

                <button type="submit" name="login" class="w-full rounded-xl bg-blue-600 px-4 py-3 font-semibold text-white hover:bg-blue-700">
                    Login
                </button>
            </form>

            <div class="mt-6 rounded-2xl bg-slate-50 p-4 text-sm text-slate-600">
                <p class="font-semibold text-slate-800">Need an account?</p>
                <p class="mt-1">Only students can register from the public site.</p>
                <a href="register.php" class="mt-3 inline-block font-semibold text-blue-600 hover:underline">Go to Student Registration</a>
            </div>

            <a href="index.php" class="mt-6 inline-block text-sm font-semibold text-slate-600 hover:text-slate-900">Back to home</a>
        </div>
    </div>

    <script>
        function toggleLoginFields() {
            const role = document.getElementById('role').value;
            document.getElementById('studentFields').classList.toggle('hidden', role !== 'student');
            document.getElementById('adminFields').classList.toggle('hidden', role !== 'admin');
        }
    </script>
</body>

</html>

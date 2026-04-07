<?php
include "../config/db.php";
include "../includes/flash.php";

$error = "";
$name = "";
$email = "";
$roll = "";

if (isset($_POST['register'])) {

    $name = mysqli_real_escape_string($conn, trim($_POST['name']));
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $roll = mysqli_real_escape_string($conn, trim($_POST['roll_number']));
    $password = $_POST['password'];

    // check if email exists
    $check_query = "SELECT * FROM students WHERE email='$email'";
    $check_result = mysqli_query($conn, $check_query);

    if (mysqli_num_rows($check_result) > 0) {

        $error = "Email already registered";
    } else {

        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $query = "INSERT INTO students (name, email, roll_number, password)
                  VALUES ('$name', '$email', '$roll', '$hashed_password')";

        mysqli_query($conn, $query);
        set_flash_message('success', 'Registration completed. Please log in.');

        header("Location: ../../login.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Student Registration</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 flex items-center justify-center min-h-screen">

    <div class="bg-white p-8 rounded-xl shadow-md w-full max-w-md">

        <h2 class="text-2xl font-bold text-center mb-6 text-blue-600">
            Student Registration
        </h2>

        <form method="POST">

            <label class="block mb-2 font-semibold">Name</label>
            <input type="text" name="name"
                value="<?php echo htmlspecialchars($name); ?>"
                class="w-full border p-2 rounded mb-4 focus:outline-none focus:ring-2 focus:ring-blue-400"
                required>

            <label class="block mb-2 font-semibold">Email</label>
            <input type="email" name="email"
                value="<?php echo htmlspecialchars($email); ?>"
                class="w-full border p-2 rounded mb-4 focus:outline-none focus:ring-2 focus:ring-blue-400"
                required>

            <label class="block mb-2 font-semibold">Roll Number</label>
            <input type="text" name="roll_number"
                value="<?php echo htmlspecialchars($roll); ?>"
                class="w-full border p-2 rounded mb-4 focus:outline-none focus:ring-2 focus:ring-blue-400"
                required>

            <label class="block mb-2 font-semibold">Password</label>
            <input type="password" name="password"
                class="w-full border p-2 rounded mb-2 focus:outline-none focus:ring-2 focus:ring-blue-400"
                required>

            <?php if ($error != "") { ?>
                <p class="text-red-500 text-sm mb-4"><?php echo $error; ?></p>
            <?php } ?>

            <button type="submit" name="register"
                class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700 transition">
                Register
            </button>

            <p class="text-center text-sm text-gray-600 mt-4">
                Already have an account?
                <a href="../../login.php" class="text-blue-600 font-semibold hover:underline">
                    Login here
                </a>
            </p>

        </form>

    </div>

</body>

</html>

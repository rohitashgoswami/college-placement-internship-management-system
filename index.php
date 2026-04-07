<?php
include "backend/config/db.php";

$query = "SELECT * FROM internships ORDER BY id DESC LIMIT 4";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html>

<head>
    <title>Placement Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100">

    <!-- Navbar -->
    <div class="bg-white shadow p-4 flex justify-between items-center">

        <h1 class="text-xl font-bold text-blue-600">PlaceHub</h1>

        <div class="space-x-4">
            <a href="login.php" class="text-blue-600 font-semibold">Login</a>
            <a href="register.php" class="bg-blue-600 text-white px-4 py-2 rounded">Register</a>
        </div>

    </div>

    <!-- Hero Section -->
    <div class="text-center py-16 bg-blue-50">

        <h2 class="text-3xl font-bold mb-4">
            Find Your Dream Internship & Job
        </h2>

        <p class="text-gray-600 mb-6">
            Apply to top companies and track your placement journey.
        </p>

        <a href="login.php"
            class="bg-blue-600 text-white px-6 py-3 rounded">
            Get Started
        </a>

    </div>

    <!-- Job Section -->
    <div class="p-8">

        <h2 class="text-2xl font-bold mb-6 text-center">
            Latest Opportunities
        </h2>

        <div class="grid grid-cols-2 gap-6">

            <?php while ($row = mysqli_fetch_assoc($result)) { ?>

                <div class="bg-white p-5 rounded-lg shadow">

                    <h3 class="text-lg font-semibold">
                        <?php echo $row['title']; ?>
                    </h3>

                    <p class="text-gray-500">
                        <?php echo $row['company_name']; ?>
                    </p>

                    <p class="text-sm text-gray-400 mt-1">
                        📍 <?php echo $row['location']; ?>
                    </p>

                    <div class="mt-4 flex justify-between items-center">

                        <span class="text-sm text-gray-500">
                            <?php echo $row['type']; ?>
                        </span>

                        <a href="login.php"
                            class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                            Apply
                        </a>

                    </div>

                </div>

            <?php } ?>

        </div>

    </div>

    <!-- Footer -->
    <div class="bg-gray-900 text-white text-center p-4 mt-10">
        © 2026 Placement Portal | Final Year Project
    </div>

</body>

</html>

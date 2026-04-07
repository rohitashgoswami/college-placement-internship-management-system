<?php
include "../config/db.php";
include "../includes/admin_auth.php";

$current_admin_page = 'manage_internships.php';
$admin_page_title = 'Add Internship';
$admin_page_description = 'Create a new internship or placement opportunity.';

$message = "";

if (isset($_POST['submit'])) {

    $company = mysqli_real_escape_string($conn, $_POST['company_name']);
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $type = mysqli_real_escape_string($conn, $_POST['type']);
    $location = mysqli_real_escape_string($conn, $_POST['location']);
    $eligibility = mysqli_real_escape_string($conn, $_POST['eligibility']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $last_date = mysqli_real_escape_string($conn, $_POST['last_date']);

    $query = "INSERT INTO internships 
    (company_name, title, type, location, eligibility, description, last_date)
    VALUES 
    ('$company', '$title', '$type', '$location', '$eligibility', '$description', '$last_date')";

    if (mysqli_query($conn, $query)) {
        $message = "Internship added successfully.";
    } else {
        $message = "Unable to add internship right now.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Internship</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">
<?php include "../includes/admin_topbar.php"; ?>

<div class="flex justify-center mt-8">
    <div class="bg-white shadow-lg rounded-lg p-8 w-96">
        <div class="mb-6 flex items-center justify-between">
            <h2 class="text-xl font-semibold text-gray-800">Create Listing</h2>
            <a href="dashboard.php" class="text-sm font-medium text-blue-600 hover:underline">Back to Dashboard</a>
        </div>

        <?php if ($message != "") { ?>
            <div class="mb-4 rounded border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
                <?php echo $message; ?>
            </div>
        <?php } ?>

        <form method="POST">

            <label class="block mb-2 font-semibold">Company Name</label>
            <input type="text" name="company_name"
                   class="w-full p-2 border rounded mb-4 focus:ring-2 focus:ring-blue-400"
                   required>

            <label class="block mb-2 font-semibold">Title</label>
            <input type="text" name="title"
                   class="w-full p-2 border rounded mb-4 focus:ring-2 focus:ring-blue-400"
                   required>

            <label class="block mb-2 font-semibold">Type</label>
            <select name="type"
                    class="w-full p-2 border rounded mb-4">
                <option value="Internship">Internship</option>
                <option value="Placement">Placement</option>
            </select>

            <label class="block mb-2 font-semibold">Location</label>
            <input type="text" name="location"
                   class="w-full p-2 border rounded mb-4">

            <label class="block mb-2 font-semibold">Eligibility</label>
            <input type="text" name="eligibility"
                   class="w-full p-2 border rounded mb-4">

            <label class="block mb-2 font-semibold">Description</label>
            <textarea name="description"
                      class="w-full p-2 border rounded mb-4"></textarea>

            <label class="block mb-2 font-semibold">Last Date</label>
            <input type="date" name="last_date"
                   class="w-full p-2 border rounded mb-6">

            <button type="submit" name="submit"
                    class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700 transition">
                Add Internship
            </button>

        </form>

    </div>
</div>

</body>
</html>

<?php
include "../config/db.php";
include "../includes/admin_auth.php";

$current_admin_page = 'manage_students.php';
$admin_page_title = 'Student Details';
$admin_page_description = 'Review profile information and application history for an individual student.';

$student_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

$student_result = mysqli_query($conn, "SELECT id, name, email, roll_number FROM students WHERE id = '$student_id'");
$student = $student_result ? mysqli_fetch_assoc($student_result) : null;

if (!$student) {
    header("Location: manage_students.php");
    exit();
}

$application_stats = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT COUNT(*) AS total,
           SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) AS pending_count,
           SUM(CASE WHEN status = 'Selected' THEN 1 ELSE 0 END) AS selected_count,
           SUM(CASE WHEN status = 'Rejected' THEN 1 ELSE 0 END) AS rejected_count
    FROM applications
    WHERE student_id = '$student_id'
"));

$applications = mysqli_query($conn, "
    SELECT a.id, a.status, i.title, i.company_name, i.type, i.last_date
    FROM applications a
    JOIN internships i ON i.id = a.internship_id
    WHERE a.student_id = '$student_id'
    ORDER BY a.id DESC
");
?>

<!DOCTYPE html>
<html>

<head>
    <title>Student Details</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="min-h-screen bg-gray-100">
    <?php include "../includes/admin_topbar.php"; ?>

    <div class="mx-auto max-w-7xl p-6 space-y-6">
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <div class="rounded-2xl bg-white p-6 shadow">
                <div class="mb-4 h-16 w-16 rounded-full bg-blue-600 text-center text-2xl font-bold leading-[4rem] text-white">
                    <?php echo strtoupper(substr($student['name'], 0, 1)); ?>
                </div>
                <h2 class="text-2xl font-bold text-gray-800"><?php echo htmlspecialchars($student['name']); ?></h2>
                <p class="mt-2 text-gray-600"><?php echo htmlspecialchars($student['email']); ?></p>
                <p class="mt-1 text-sm text-gray-500">Roll Number: <?php echo htmlspecialchars($student['roll_number']); ?></p>
            </div>

            <div class="grid grid-cols-2 gap-4 lg:col-span-2">
                <div class="rounded-2xl bg-white p-5 shadow">
                    <p class="text-sm text-gray-500">Total Applications</p>
                    <p class="mt-2 text-3xl font-bold text-blue-600"><?php echo (int) $application_stats['total']; ?></p>
                </div>
                <div class="rounded-2xl bg-white p-5 shadow">
                    <p class="text-sm text-gray-500">Pending</p>
                    <p class="mt-2 text-3xl font-bold text-amber-500"><?php echo (int) $application_stats['pending_count']; ?></p>
                </div>
                <div class="rounded-2xl bg-white p-5 shadow">
                    <p class="text-sm text-gray-500">Selected</p>
                    <p class="mt-2 text-3xl font-bold text-green-600"><?php echo (int) $application_stats['selected_count']; ?></p>
                </div>
                <div class="rounded-2xl bg-white p-5 shadow">
                    <p class="text-sm text-gray-500">Rejected</p>
                    <p class="mt-2 text-3xl font-bold text-red-600"><?php echo (int) $application_stats['rejected_count']; ?></p>
                </div>
            </div>
        </div>

        <div class="rounded-2xl bg-white p-6 shadow">
            <div class="mb-5 flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-bold text-gray-800">Application History</h3>
                    <p class="text-sm text-gray-500">All internships and placements this student has applied for.</p>
                </div>
                <a href="manage_students.php" class="text-sm font-semibold text-blue-600 hover:underline">Back to Students</a>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="text-left text-gray-500">
                        <tr>
                            <th class="py-2">Role</th>
                            <th class="py-2">Company</th>
                            <th class="py-2">Type</th>
                            <th class="py-2">Deadline</th>
                            <th class="py-2">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($applications) > 0) { ?>
                            <?php while ($row = mysqli_fetch_assoc($applications)) { ?>
                                <tr class="border-t">
                                    <td class="py-3 font-medium"><?php echo htmlspecialchars($row['title']); ?></td>
                                    <td class="py-3"><?php echo htmlspecialchars($row['company_name']); ?></td>
                                    <td class="py-3"><?php echo htmlspecialchars($row['type']); ?></td>
                                    <td class="py-3"><?php echo htmlspecialchars($row['last_date']); ?></td>
                                    <td class="py-3 font-semibold"><?php echo htmlspecialchars($row['status']); ?></td>
                                </tr>
                            <?php } ?>
                        <?php } else { ?>
                            <tr>
                                <td colspan="5" class="py-8 text-center text-gray-500">This student has not applied to any internship yet.</td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</body>

</html>

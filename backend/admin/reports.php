<?php
include "../config/db.php";
include "../includes/admin_auth.php";

$current_admin_page = 'reports.php';
$admin_page_title = 'Reports';
$admin_page_description = 'See placement trends, recruiter activity, and system participation in one place.';

$summary = array(
    'students' => mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM students")),
    'internships' => mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM internships")),
    'applications' => mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM applications")),
    'selected' => mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM applications WHERE status='Selected'"))
);

$status_report = mysqli_query($conn, "
    SELECT status, COUNT(*) AS total
    FROM applications
    GROUP BY status
    ORDER BY total DESC, status ASC
");

$company_report = mysqli_query($conn, "
    SELECT i.company_name,
           COUNT(a.id) AS total_applications,
           SUM(CASE WHEN a.status = 'Selected' THEN 1 ELSE 0 END) AS selections
    FROM internships i
    LEFT JOIN applications a ON a.internship_id = i.id
    GROUP BY i.company_name
    ORDER BY total_applications DESC, i.company_name ASC
    LIMIT 10
");

$type_report = mysqli_query($conn, "
    SELECT type, COUNT(*) AS total
    FROM internships
    GROUP BY type
    ORDER BY total DESC
");

$participation_report = mysqli_query($conn, "
    SELECT s.name, s.roll_number, COUNT(a.id) AS total_applications
    FROM students s
    LEFT JOIN applications a ON a.student_id = s.id
    GROUP BY s.id, s.name, s.roll_number
    ORDER BY total_applications DESC, s.name ASC
    LIMIT 10
");

$placement_rate = 0;
if ((int) $summary['students']['total'] > 0) {
    $placement_rate = round(((int) $summary['selected']['total'] / (int) $summary['students']['total']) * 100, 2);
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Reports</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="min-h-screen bg-gray-100">
    <?php include "../includes/admin_topbar.php"; ?>

    <div class="mx-auto max-w-7xl space-y-6 p-6">
        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-5">
            <div class="rounded-2xl bg-white p-5 shadow">
                <p class="text-sm text-gray-500">Students</p>
                <p class="mt-2 text-3xl font-bold text-blue-600"><?php echo (int) $summary['students']['total']; ?></p>
            </div>
            <div class="rounded-2xl bg-white p-5 shadow">
                <p class="text-sm text-gray-500">Internships</p>
                <p class="mt-2 text-3xl font-bold text-blue-600"><?php echo (int) $summary['internships']['total']; ?></p>
            </div>
            <div class="rounded-2xl bg-white p-5 shadow">
                <p class="text-sm text-gray-500">Applications</p>
                <p class="mt-2 text-3xl font-bold text-blue-600"><?php echo (int) $summary['applications']['total']; ?></p>
            </div>
            <div class="rounded-2xl bg-white p-5 shadow">
                <p class="text-sm text-gray-500">Selections</p>
                <p class="mt-2 text-3xl font-bold text-green-600"><?php echo (int) $summary['selected']['total']; ?></p>
            </div>
            <div class="rounded-2xl bg-white p-5 shadow">
                <p class="text-sm text-gray-500">Placement Rate</p>
                <p class="mt-2 text-3xl font-bold text-violet-600"><?php echo $placement_rate; ?>%</p>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
            <div class="rounded-2xl bg-white p-6 shadow">
                <h3 class="mb-4 text-lg font-bold text-gray-800">Application Status Breakdown</h3>
                <div class="space-y-3">
                    <?php while ($row = mysqli_fetch_assoc($status_report)) { ?>
                        <div class="flex items-center justify-between rounded-xl bg-gray-50 px-4 py-3">
                            <span class="font-medium text-gray-700"><?php echo htmlspecialchars($row['status']); ?></span>
                            <span class="rounded-full bg-blue-100 px-3 py-1 text-sm font-semibold text-blue-700"><?php echo (int) $row['total']; ?></span>
                        </div>
                    <?php } ?>
                </div>
            </div>

            <div class="rounded-2xl bg-white p-6 shadow">
                <h3 class="mb-4 text-lg font-bold text-gray-800">Internship Type Distribution</h3>
                <div class="space-y-3">
                    <?php while ($row = mysqli_fetch_assoc($type_report)) { ?>
                        <div class="flex items-center justify-between rounded-xl bg-gray-50 px-4 py-3">
                            <span class="font-medium text-gray-700"><?php echo htmlspecialchars($row['type']); ?></span>
                            <span class="rounded-full bg-emerald-100 px-3 py-1 text-sm font-semibold text-emerald-700"><?php echo (int) $row['total']; ?></span>
                        </div>
                    <?php } ?>
                </div>
            </div>

            <div class="rounded-2xl bg-white p-6 shadow">
                <h3 class="mb-4 text-lg font-bold text-gray-800">Recruiter Performance</h3>
                <div class="space-y-3">
                    <?php while ($row = mysqli_fetch_assoc($company_report)) { ?>
                        <div class="rounded-xl bg-gray-50 px-4 py-3">
                            <div class="flex items-center justify-between">
                                <span class="font-medium text-gray-800"><?php echo htmlspecialchars($row['company_name']); ?></span>
                                <span class="text-sm font-semibold text-blue-700"><?php echo (int) $row['total_applications']; ?> apps</span>
                            </div>
                            <p class="mt-1 text-sm text-gray-500"><?php echo (int) $row['selections']; ?> selected candidates</p>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>

        <div class="rounded-2xl bg-white p-6 shadow">
            <div class="mb-5">
                <h3 class="text-lg font-bold text-gray-800">Top Student Participation</h3>
                <p class="text-sm text-gray-500">Students with the highest number of submitted applications.</p>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="text-left text-gray-500">
                        <tr>
                            <th class="py-2">Student</th>
                            <th class="py-2">Roll Number</th>
                            <th class="py-2">Applications</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($participation_report)) { ?>
                            <tr class="border-t">
                                <td class="py-3 font-medium"><?php echo htmlspecialchars($row['name']); ?></td>
                                <td class="py-3"><?php echo htmlspecialchars($row['roll_number']); ?></td>
                                <td class="py-3"><?php echo (int) $row['total_applications']; ?></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</body>

</html>

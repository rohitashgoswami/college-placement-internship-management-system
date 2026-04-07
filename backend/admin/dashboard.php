<?php
include "../config/db.php";
include "../includes/admin_auth.php";

$current_admin_page = 'dashboard.php';
$admin_page_title = 'Admin Dashboard';
$admin_page_description = 'Track performance, review alerts, and jump into key admin actions.';

$internship_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM internships"));
$application_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM applications"));
$student_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM students"));
$pending_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM applications WHERE status='Pending'"));
$selected_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM applications WHERE status='Selected'"));
$rejected_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM applications WHERE status='Rejected'"));
$expired_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM internships WHERE last_date IS NOT NULL AND last_date < CURDATE()"));
$closing_today_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM internships WHERE last_date = CURDATE()"));
$closing_soon_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM internships WHERE last_date IS NOT NULL AND last_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)"));

$top_recruiters = mysqli_query($conn, "
    SELECT i.company_name, COUNT(a.id) AS total
    FROM internships i
    LEFT JOIN applications a ON a.internship_id = i.id
    GROUP BY i.company_name
    ORDER BY total DESC, i.company_name ASC
    LIMIT 5
");

$recent_applications = mysqli_query($conn, "
    SELECT a.id, s.name, i.title, i.company_name, a.status
    FROM applications a
    JOIN students s ON s.id = a.student_id
    JOIN internships i ON i.id = a.internship_id
    ORDER BY a.id DESC
    LIMIT 5
");

$recent_students = mysqli_query($conn, "
    SELECT id, name, email, roll_number
    FROM students
    ORDER BY id DESC
    LIMIT 5
");

$recent_internships = mysqli_query($conn, "
    SELECT id, title, company_name, type, last_date
    FROM internships
    ORDER BY id DESC
    LIMIT 5
");

$internship_overview = mysqli_query($conn, "
    SELECT i.title, i.company_name, i.last_date, COUNT(a.id) AS total_applications
    FROM internships i
    LEFT JOIN applications a ON a.internship_id = i.id
    GROUP BY i.id, i.title, i.company_name, i.last_date
    ORDER BY total_applications DESC, i.id DESC
    LIMIT 5
");

$search_term = isset($_GET['q']) ? trim($_GET['q']) : '';
$search_results = array(
    'students' => array(),
    'internships' => array(),
    'applications' => array()
);

if ($search_term !== '') {
    $safe_search = mysqli_real_escape_string($conn, $search_term);

    $student_results = mysqli_query($conn, "
        SELECT id, name, email, roll_number
        FROM students
        WHERE name LIKE '%$safe_search%'
           OR email LIKE '%$safe_search%'
           OR roll_number LIKE '%$safe_search%'
        ORDER BY id DESC
        LIMIT 5
    ");

    while ($row = mysqli_fetch_assoc($student_results)) {
        $search_results['students'][] = $row;
    }

    $internship_results = mysqli_query($conn, "
        SELECT id, title, company_name, type
        FROM internships
        WHERE title LIKE '%$safe_search%'
           OR company_name LIKE '%$safe_search%'
           OR location LIKE '%$safe_search%'
        ORDER BY id DESC
        LIMIT 5
    ");

    while ($row = mysqli_fetch_assoc($internship_results)) {
        $search_results['internships'][] = $row;
    }

    $application_results = mysqli_query($conn, "
        SELECT a.id, s.name, i.title, a.status
        FROM applications a
        JOIN students s ON s.id = a.student_id
        JOIN internships i ON i.id = a.internship_id
        WHERE s.name LIKE '%$safe_search%'
           OR i.title LIKE '%$safe_search%'
           OR i.company_name LIKE '%$safe_search%'
           OR a.status LIKE '%$safe_search%'
        ORDER BY a.id DESC
        LIMIT 5
    ");

    while ($row = mysqli_fetch_assoc($application_results)) {
        $search_results['applications'][] = $row;
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 min-h-screen">
    <?php include "../includes/admin_topbar.php"; ?>

    <div class="mx-auto max-w-7xl p-6 space-y-8">

        <div>
            <h3 class="mb-2 text-2xl font-semibold">System Overview</h3>
            <p class="text-gray-600">Everything the admin team needs to manage students, opportunities, and application flow.</p>
        </div>

        <form method="GET" class="rounded-2xl bg-white p-5 shadow">
            <label class="mb-2 block text-sm font-semibold text-gray-700">Global Search</label>
            <div class="flex flex-col gap-3 md:flex-row">
                <input type="text" name="q" value="<?php echo htmlspecialchars($search_term); ?>"
                    placeholder="Search students, internships, companies, or application status"
                    class="w-full rounded-lg border px-4 py-3">
                <button type="submit" class="rounded-lg bg-blue-600 px-5 py-3 font-semibold text-white hover:bg-blue-700">
                    Search
                </button>
            </div>
        </form>

        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-6">
            <div class="rounded-lg bg-white p-6 shadow">
                <p class="text-sm text-gray-500">Total Internships</p>
                <p class="mt-2 text-3xl font-bold text-blue-600"><?php echo $internship_count['total']; ?></p>
            </div>

            <div class="rounded-lg bg-white p-6 shadow">
                <p class="text-sm text-gray-500">Applications Received</p>
                <p class="mt-2 text-3xl font-bold text-blue-600"><?php echo $application_count['total']; ?></p>
            </div>

            <div class="rounded-lg bg-white p-6 shadow">
                <p class="text-sm text-gray-500">Registered Students</p>
                <p class="mt-2 text-3xl font-bold text-blue-600"><?php echo $student_count['total']; ?></p>
            </div>

            <div class="rounded-lg bg-white p-6 shadow">
                <p class="text-sm text-gray-500">Pending Reviews</p>
                <p class="mt-2 text-3xl font-bold text-amber-500"><?php echo $pending_count['total']; ?></p>
            </div>

            <div class="rounded-lg bg-white p-6 shadow">
                <p class="text-sm text-gray-500">Selected Candidates</p>
                <p class="mt-2 text-3xl font-bold text-green-600"><?php echo $selected_count['total']; ?></p>
            </div>

            <div class="rounded-lg bg-white p-6 shadow">
                <p class="text-sm text-gray-500">Rejected Candidates</p>
                <p class="mt-2 text-3xl font-bold text-red-600"><?php echo $rejected_count['total']; ?></p>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
            <div class="rounded-2xl bg-white p-6 shadow xl:col-span-2">
                <div class="mb-5 flex items-center justify-between">
                    <div>
                        <h4 class="text-lg font-bold text-gray-800">Quick Actions</h4>
                        <p class="text-sm text-gray-500">Jump directly into the most common admin tasks.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
                    <a href="add_internship.php" class="rounded-xl border border-blue-100 bg-blue-50 p-5 transition hover:border-blue-300 hover:bg-blue-100">
                        <h5 class="font-bold text-blue-700">Add Internship</h5>
                        <p class="mt-2 text-sm text-gray-600">Post a new internship or placement opportunity.</p>
                    </a>

                    <a href="view_applications.php" class="rounded-xl border border-slate-200 bg-slate-50 p-5 transition hover:border-slate-300 hover:bg-slate-100">
                        <h5 class="font-bold text-slate-800">Review Applications</h5>
                        <p class="mt-2 text-sm text-gray-600">Approve, reject, and monitor student applications.</p>
                    </a>

                    <a href="manage_internships.php" class="rounded-xl border border-emerald-100 bg-emerald-50 p-5 transition hover:border-emerald-300 hover:bg-emerald-100">
                        <h5 class="font-bold text-emerald-700">Manage Internships</h5>
                        <p class="mt-2 text-sm text-gray-600">Track active listings and remove old ones.</p>
                    </a>

                    <a href="manage_students.php" class="rounded-xl border border-violet-100 bg-violet-50 p-5 transition hover:border-violet-300 hover:bg-violet-100">
                        <h5 class="font-bold text-violet-700">Manage Students</h5>
                        <p class="mt-2 text-sm text-gray-600">Search student records and inspect application activity.</p>
                    </a>

                    <a href="reports.php" class="rounded-xl border border-amber-100 bg-amber-50 p-5 transition hover:border-amber-300 hover:bg-amber-100">
                        <h5 class="font-bold text-amber-700">Reports</h5>
                        <p class="mt-2 text-sm text-gray-600">See recruiter trends, status breakdowns, and placements.</p>
                    </a>

                    <a href="change_password.php" class="rounded-xl border border-rose-100 bg-rose-50 p-5 transition hover:border-rose-300 hover:bg-rose-100">
                        <h5 class="font-bold text-rose-700">Admin Settings</h5>
                        <p class="mt-2 text-sm text-gray-600">Change your password and keep the admin account secure.</p>
                    </a>
                </div>
            </div>

            <div class="rounded-2xl bg-white p-6 shadow">
                <div class="mb-4">
                    <h4 class="text-lg font-bold text-gray-800">Alerts</h4>
                    <p class="text-sm text-gray-500">Operational items that need attention.</p>
                </div>

                <div class="space-y-3 text-sm">
                    <div class="rounded-xl border border-amber-200 bg-amber-50 p-4">
                        <p class="font-semibold text-amber-800"><?php echo $pending_count['total']; ?> applications pending review</p>
                        <p class="mt-1 text-amber-700">Open the applications page and move candidates forward.</p>
                    </div>
                    <div class="rounded-xl border border-red-200 bg-red-50 p-4">
                        <p class="font-semibold text-red-800"><?php echo $expired_count['total']; ?> internships expired</p>
                        <p class="mt-1 text-red-700">Clean up expired postings from the internships module.</p>
                    </div>
                    <div class="rounded-xl border border-blue-200 bg-blue-50 p-4">
                        <p class="font-semibold text-blue-800"><?php echo $closing_today_count['total']; ?> listings close today</p>
                        <p class="mt-1 text-blue-700"><?php echo $closing_soon_count['total']; ?> listings are closing within 7 days.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
            <div class="rounded-2xl bg-white p-6 shadow">
                <div class="mb-5">
                    <h4 class="text-lg font-bold text-gray-800">Application Overview</h4>
                    <p class="text-sm text-gray-500">A quick breakdown of current application status.</p>
                </div>

                <?php $status_total = max(1, (int) $application_count['total']); ?>

                <div class="space-y-4">
                    <div>
                        <div class="mb-1 flex justify-between text-sm"><span>Pending</span><span><?php echo $pending_count['total']; ?></span></div>
                        <div class="h-2 rounded bg-gray-200"><div class="h-2 rounded bg-amber-400" style="width: <?php echo ($pending_count['total'] / $status_total) * 100; ?>%"></div></div>
                    </div>
                    <div>
                        <div class="mb-1 flex justify-between text-sm"><span>Selected</span><span><?php echo $selected_count['total']; ?></span></div>
                        <div class="h-2 rounded bg-gray-200"><div class="h-2 rounded bg-green-500" style="width: <?php echo ($selected_count['total'] / $status_total) * 100; ?>%"></div></div>
                    </div>
                    <div>
                        <div class="mb-1 flex justify-between text-sm"><span>Rejected</span><span><?php echo $rejected_count['total']; ?></span></div>
                        <div class="h-2 rounded bg-gray-200"><div class="h-2 rounded bg-red-500" style="width: <?php echo ($rejected_count['total'] / $status_total) * 100; ?>%"></div></div>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl bg-white p-6 shadow">
                <div class="mb-5">
                    <h4 class="text-lg font-bold text-gray-800">Top Recruiters</h4>
                    <p class="text-sm text-gray-500">Companies receiving the most applications.</p>
                </div>

                <div class="space-y-3">
                    <?php while ($row = mysqli_fetch_assoc($top_recruiters)) { ?>
                        <div class="flex items-center justify-between rounded-xl bg-gray-50 px-4 py-3">
                            <span class="font-medium text-gray-700"><?php echo htmlspecialchars($row['company_name']); ?></span>
                            <span class="rounded-full bg-blue-100 px-3 py-1 text-sm font-semibold text-blue-700"><?php echo (int) $row['total']; ?> apps</span>
                        </div>
                    <?php } ?>
                </div>
            </div>

            <div class="rounded-2xl bg-white p-6 shadow">
                <div class="mb-5">
                    <h4 class="text-lg font-bold text-gray-800">Search Results</h4>
                    <p class="text-sm text-gray-500">Dashboard-wide search across records.</p>
                </div>

                <?php if ($search_term === '') { ?>
                    <p class="rounded-xl bg-gray-50 px-4 py-6 text-sm text-gray-500">Run a search to see matching students, internships, and applications here.</p>
                <?php } else { ?>
                    <div class="space-y-4 text-sm">
                        <div>
                            <p class="mb-2 font-semibold text-gray-700">Students</p>
                            <?php if (!empty($search_results['students'])) { ?>
                                <?php foreach ($search_results['students'] as $row) { ?>
                                    <a href="view_student.php?id=<?php echo (int) $row['id']; ?>" class="mb-2 block rounded-lg bg-gray-50 px-3 py-2 hover:bg-gray-100">
                                        <?php echo htmlspecialchars($row['name']); ?> · <?php echo htmlspecialchars($row['roll_number']); ?>
                                    </a>
                                <?php } ?>
                            <?php } else { ?>
                                <p class="text-gray-500">No student matches.</p>
                            <?php } ?>
                        </div>

                        <div>
                            <p class="mb-2 font-semibold text-gray-700">Internships</p>
                            <?php if (!empty($search_results['internships'])) { ?>
                                <?php foreach ($search_results['internships'] as $row) { ?>
                                    <div class="mb-2 rounded-lg bg-gray-50 px-3 py-2">
                                        <?php echo htmlspecialchars($row['title']); ?> · <?php echo htmlspecialchars($row['company_name']); ?>
                                    </div>
                                <?php } ?>
                            <?php } else { ?>
                                <p class="text-gray-500">No internship matches.</p>
                            <?php } ?>
                        </div>

                        <div>
                            <p class="mb-2 font-semibold text-gray-700">Applications</p>
                            <?php if (!empty($search_results['applications'])) { ?>
                                <?php foreach ($search_results['applications'] as $row) { ?>
                                    <div class="mb-2 rounded-lg bg-gray-50 px-3 py-2">
                                        <?php echo htmlspecialchars($row['name']); ?> · <?php echo htmlspecialchars($row['title']); ?> · <?php echo htmlspecialchars($row['status']); ?>
                                    </div>
                                <?php } ?>
                            <?php } else { ?>
                                <p class="text-gray-500">No application matches.</p>
                            <?php } ?>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
            <div class="rounded-2xl bg-white p-6 shadow">
                <div class="mb-5 flex items-center justify-between">
                    <div>
                        <h4 class="text-lg font-bold text-gray-800">Recent Applications</h4>
                        <p class="text-sm text-gray-500">Newest application activity in the system.</p>
                    </div>
                    <a href="view_applications.php" class="text-sm font-semibold text-blue-600 hover:underline">View all</a>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="text-left text-gray-500">
                            <tr>
                                <th class="py-2">Student</th>
                                <th class="py-2">Role</th>
                                <th class="py-2">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = mysqli_fetch_assoc($recent_applications)) { ?>
                                <tr class="border-t">
                                    <td class="py-3"><?php echo htmlspecialchars($row['name']); ?></td>
                                    <td class="py-3"><?php echo htmlspecialchars($row['title']); ?> at <?php echo htmlspecialchars($row['company_name']); ?></td>
                                    <td class="py-3 font-medium"><?php echo htmlspecialchars($row['status']); ?></td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="rounded-2xl bg-white p-6 shadow">
                <div class="mb-5 flex items-center justify-between">
                    <div>
                        <h4 class="text-lg font-bold text-gray-800">Recent Student Registrations</h4>
                        <p class="text-sm text-gray-500">Latest student accounts by registration order.</p>
                    </div>
                    <a href="manage_students.php" class="text-sm font-semibold text-blue-600 hover:underline">Manage students</a>
                </div>

                <div class="space-y-3">
                    <?php while ($row = mysqli_fetch_assoc($recent_students)) { ?>
                        <a href="view_student.php?id=<?php echo (int) $row['id']; ?>" class="block rounded-xl bg-gray-50 px-4 py-3 hover:bg-gray-100">
                            <p class="font-semibold text-gray-800"><?php echo htmlspecialchars($row['name']); ?></p>
                            <p class="text-sm text-gray-500"><?php echo htmlspecialchars($row['email']); ?> · <?php echo htmlspecialchars($row['roll_number']); ?></p>
                        </a>
                    <?php } ?>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
            <div class="rounded-2xl bg-white p-6 shadow">
                <div class="mb-5 flex items-center justify-between">
                    <div>
                        <h4 class="text-lg font-bold text-gray-800">Recent Internships</h4>
                        <p class="text-sm text-gray-500">Newest opportunities added to the portal.</p>
                    </div>
                    <a href="manage_internships.php" class="text-sm font-semibold text-blue-600 hover:underline">Manage internships</a>
                </div>

                <div class="space-y-3">
                    <?php while ($row = mysqli_fetch_assoc($recent_internships)) { ?>
                        <div class="rounded-xl bg-gray-50 px-4 py-3">
                            <p class="font-semibold text-gray-800"><?php echo htmlspecialchars($row['title']); ?></p>
                            <p class="text-sm text-gray-500"><?php echo htmlspecialchars($row['company_name']); ?> · <?php echo htmlspecialchars($row['type']); ?></p>
                            <p class="mt-1 text-xs text-gray-400">Last date: <?php echo htmlspecialchars($row['last_date']); ?></p>
                        </div>
                    <?php } ?>
                </div>
            </div>

            <div class="rounded-2xl bg-white p-6 shadow">
                <div class="mb-5">
                    <h4 class="text-lg font-bold text-gray-800">Internship Overview</h4>
                    <p class="text-sm text-gray-500">Most applied roles and upcoming deadlines.</p>
                </div>

                <div class="space-y-3">
                    <?php while ($row = mysqli_fetch_assoc($internship_overview)) { ?>
                        <div class="rounded-xl border border-gray-200 px-4 py-3">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <p class="font-semibold text-gray-800"><?php echo htmlspecialchars($row['title']); ?></p>
                                    <p class="text-sm text-gray-500"><?php echo htmlspecialchars($row['company_name']); ?></p>
                                </div>
                                <span class="rounded-full bg-slate-100 px-3 py-1 text-sm font-semibold text-slate-700">
                                    <?php echo (int) $row['total_applications']; ?> apps
                                </span>
                            </div>
                            <p class="mt-2 text-xs text-gray-400">Deadline: <?php echo htmlspecialchars($row['last_date']); ?></p>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>

    </div>

</body>

</html>

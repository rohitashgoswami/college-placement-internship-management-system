<?php
include "../config/db.php";
include "../includes/student_auth.php";
include "../includes/flash.php";
include "../includes/student_layout.php";

$current_page = basename($_SERVER['PHP_SELF']);
$student_page_title = 'Dashboard';
$student_page_description = 'Your personal placement overview with live counts and activity.';
$student_badge = 'Student';
$student_id = (int) $_SESSION['student_id'];

$stats = array(
    'active_listings' => mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM internships WHERE last_date IS NULL OR last_date >= CURDATE()")),
    'applications' => mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM applications WHERE student_id = '$student_id'")),
    'selected' => mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM applications WHERE student_id = '$student_id' AND status = 'Selected'")),
    'interviews' => mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM applications WHERE student_id = '$student_id' AND status IN ('Interview', 'Shortlisted')")),
    'saved_jobs' => mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM saved_jobs WHERE student_id = '$student_id'"))
);

$placement_rate = 0;
if ((int) $stats['applications']['total'] > 0) {
    $placement_rate = round(((int) $stats['selected']['total'] / (int) $stats['applications']['total']) * 100);
}

$latest_internships = mysqli_query($conn, "
    SELECT i.*,
           EXISTS(
               SELECT 1 FROM applications a
               WHERE a.student_id = '$student_id' AND a.internship_id = i.id
           ) AS already_applied,
           EXISTS(
               SELECT 1 FROM saved_jobs sj
               WHERE sj.student_id = '$student_id' AND sj.internship_id = i.id
           ) AS is_saved
    FROM internships i
    ORDER BY i.id DESC
    LIMIT 4
");

$recent_applications = mysqli_query($conn, "
    SELECT i.id AS internship_id, i.title, i.company_name, a.status, a.created_at
    FROM applications a
    JOIN internships i ON a.internship_id = i.id
    WHERE a.student_id = '$student_id'
    ORDER BY a.created_at DESC, a.id DESC
    LIMIT 5
");

$saved_jobs = mysqli_query($conn, "
    SELECT i.id, i.title, i.company_name, i.location, i.last_date
    FROM saved_jobs sj
    JOIN internships i ON sj.internship_id = i.id
    WHERE sj.student_id = '$student_id'
    ORDER BY sj.saved_at DESC
    LIMIT 4
");

$notifications = array();

if ((int) $stats['selected']['total'] > 0) {
    $notifications[] = 'You have ' . (int) $stats['selected']['total'] . ' selected application(s). Check your applications page.';
}

if ((int) $stats['interviews']['total'] > 0) {
    $notifications[] = 'You have ' . (int) $stats['interviews']['total'] . ' interview or shortlisted application(s) in progress.';
}

$deadline_result = mysqli_query($conn, "
    SELECT COUNT(*) AS total
    FROM internships
    WHERE last_date IS NOT NULL AND last_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 5 DAY)
");
$deadline_count = mysqli_fetch_assoc($deadline_result);

if ((int) $deadline_count['total'] > 0) {
    $notifications[] = (int) $deadline_count['total'] . ' opportunity(ies) are closing within 5 days.';
}

render_student_page_start('Student Dashboard');
?>

    <?php include "layout/sidebar.php"; ?>

    <div class="ml-64 min-h-screen flex-1">
        <?php include "layout/topbar.php"; ?>

        <div class="p-8 pt-32">
            <?php render_student_flash(); ?>

            <div class="mb-8 grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-5">
                <div class="rounded-2xl bg-white p-6 shadow">
                    <p class="text-sm text-gray-500">Active Listings</p>
                    <h2 class="mt-2 text-3xl font-bold text-blue-600"><?php echo (int) $stats['active_listings']['total']; ?></h2>
                </div>
                <div class="rounded-2xl bg-white p-6 shadow">
                    <p class="text-sm text-gray-500">My Applications</p>
                    <h2 class="mt-2 text-3xl font-bold text-slate-900"><?php echo (int) $stats['applications']['total']; ?></h2>
                </div>
                <div class="rounded-2xl bg-white p-6 shadow">
                    <p class="text-sm text-gray-500">Interviews / Shortlisted</p>
                    <h2 class="mt-2 text-3xl font-bold text-amber-500"><?php echo (int) $stats['interviews']['total']; ?></h2>
                </div>
                <div class="rounded-2xl bg-white p-6 shadow">
                    <p class="text-sm text-gray-500">Saved Jobs</p>
                    <h2 class="mt-2 text-3xl font-bold text-violet-600"><?php echo (int) $stats['saved_jobs']['total']; ?></h2>
                </div>
                <div class="rounded-2xl bg-white p-6 shadow">
                    <p class="text-sm text-gray-500">Selection Rate</p>
                    <h2 class="mt-2 text-3xl font-bold text-emerald-600"><?php echo $placement_rate; ?>%</h2>
                </div>
            </div>

            <div class="mb-8 grid grid-cols-1 gap-6 xl:grid-cols-3">
                <div class="rounded-2xl bg-white p-6 shadow xl:col-span-2">
                    <div class="mb-5 flex items-center justify-between">
                        <div>
                            <h2 class="text-lg font-semibold text-slate-900">Latest Opportunities</h2>
                            <p class="text-sm text-gray-500">Fresh internships and placements you can act on right away.</p>
                        </div>
                        <a href="job_board.php" class="text-sm font-semibold text-blue-600 hover:underline">Browse all</a>
                    </div>

                    <div class="space-y-4">
                        <?php while ($row = mysqli_fetch_assoc($latest_internships)) { ?>
                            <div class="rounded-2xl border border-slate-200 p-5">
                                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                                    <div>
                                        <h3 class="font-semibold text-slate-900"><?php echo htmlspecialchars($row['title']); ?></h3>
                                        <p class="text-sm text-gray-500"><?php echo htmlspecialchars($row['company_name']); ?> · <?php echo htmlspecialchars($row['location']); ?></p>
                                        <p class="mt-2 text-xs uppercase tracking-wide text-gray-400"><?php echo htmlspecialchars($row['type']); ?> · Last date <?php echo htmlspecialchars($row['last_date']); ?></p>
                                    </div>
                                    <div class="flex flex-wrap gap-3">
                                        <a href="internship_details.php?id=<?php echo (int) $row['id']; ?>" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">View Details</a>
                                        <a href="toggle_saved_job.php?id=<?php echo (int) $row['id']; ?>&redirect=dashboard.php" class="rounded-lg border border-violet-200 px-4 py-2 text-sm font-semibold text-violet-700 hover:bg-violet-50"><?php echo ((int) $row['is_saved'] === 1) ? 'Saved' : 'Save Job'; ?></a>
                                        <?php if ((int) $row['already_applied'] === 1) { ?>
                                            <span class="rounded-lg bg-green-50 px-4 py-2 text-sm font-semibold text-green-700">Applied</span>
                                        <?php } else { ?>
                                            <a href="apply.php?id=<?php echo (int) $row['id']; ?>&redirect=dashboard.php" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">Apply Now</a>
                                        <?php } ?>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="rounded-2xl bg-white p-6 shadow">
                        <div class="mb-4">
                            <h2 class="text-lg font-semibold text-slate-900">Notifications</h2>
                            <p class="text-sm text-gray-500">Important updates from your job search.</p>
                        </div>
                        <div class="space-y-3">
                            <?php if (!empty($notifications)) { ?>
                                <?php foreach ($notifications as $notice) { ?>
                                    <div class="rounded-xl border border-blue-100 bg-blue-50 px-4 py-3 text-sm text-blue-700"><?php echo htmlspecialchars($notice); ?></div>
                                <?php } ?>
                            <?php } else { ?>
                                <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-500">No new alerts right now. Keep exploring new opportunities.</div>
                            <?php } ?>
                        </div>
                    </div>

                    <div class="rounded-2xl bg-white p-6 shadow">
                        <div class="mb-4 flex items-center justify-between">
                            <h2 class="text-lg font-semibold text-slate-900">Saved Jobs</h2>
                            <a href="saved_jobs.php" class="text-sm font-semibold text-blue-600 hover:underline">View all</a>
                        </div>
                        <div class="space-y-3">
                            <?php if (mysqli_num_rows($saved_jobs) > 0) { ?>
                                <?php while ($row = mysqli_fetch_assoc($saved_jobs)) { ?>
                                    <a href="internship_details.php?id=<?php echo (int) $row['id']; ?>" class="block rounded-xl bg-slate-50 px-4 py-3 hover:bg-slate-100">
                                        <p class="font-semibold text-slate-900"><?php echo htmlspecialchars($row['title']); ?></p>
                                        <p class="text-sm text-slate-500"><?php echo htmlspecialchars($row['company_name']); ?> · <?php echo htmlspecialchars($row['location']); ?></p>
                                    </a>
                                <?php } ?>
                            <?php } else { ?>
                                <p class="rounded-xl bg-slate-50 px-4 py-4 text-sm text-slate-500">Save jobs from the job board to keep a shortlist here.</p>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl bg-white p-6 shadow">
                <div class="mb-5 flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-slate-900">Recent Applications</h2>
                        <p class="text-sm text-gray-500">Track your latest application activity and status changes.</p>
                    </div>
                    <a href="my_applications.php" class="text-sm font-semibold text-blue-600 hover:underline">Open applications</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="text-left text-slate-500">
                            <tr>
                                <th class="py-2">Position</th>
                                <th class="py-2">Company</th>
                                <th class="py-2">Applied On</th>
                                <th class="py-2">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (mysqli_num_rows($recent_applications) > 0) { ?>
                                <?php while ($row = mysqli_fetch_assoc($recent_applications)) { ?>
                                    <tr class="border-t">
                                        <td class="py-3 font-medium text-slate-900"><?php echo htmlspecialchars($row['title']); ?></td>
                                        <td class="py-3 text-slate-600"><?php echo htmlspecialchars($row['company_name']); ?></td>
                                        <td class="py-3 text-slate-500"><?php echo date("M d, Y", strtotime($row['created_at'])); ?></td>
                                        <td class="py-3 font-semibold text-slate-700"><?php echo htmlspecialchars($row['status']); ?></td>
                                    </tr>
                                <?php } ?>
                            <?php } else { ?>
                                <tr>
                                    <td colspan="4" class="py-8 text-center text-slate-500">You have not applied yet. Start from the job board.</td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</body>

</html>

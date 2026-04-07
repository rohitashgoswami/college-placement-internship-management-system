<?php
include "../config/db.php";
include "../includes/student_auth.php";
include "../includes/pagination.php";
include "../includes/flash.php";
include "../includes/student_layout.php";

$current_page = basename($_SERVER['PHP_SELF']);
$student_page_title = 'Job Board';
$student_page_description = 'Search, filter, and sort internships and placements that match your profile.';
$student_badge = 'Opportunities';
$student_id = (int) $_SESSION['student_id'];

$per_page = 6;
$page_number = get_current_page_number();
$search_term = isset($_GET['q']) ? trim($_GET['q']) : '';
$type_filter = isset($_GET['type']) ? trim($_GET['type']) : '';
$location_filter = isset($_GET['location']) ? trim($_GET['location']) : '';
$sort_filter = isset($_GET['sort']) ? trim($_GET['sort']) : 'latest';
$deadline_filter = isset($_GET['deadline']) ? trim($_GET['deadline']) : '';
$where_parts = array();

if ($search_term !== '') {
    $safe_search = mysqli_real_escape_string($conn, $search_term);
    $where_parts[] = "(i.title LIKE '%$safe_search%' OR i.company_name LIKE '%$safe_search%' OR i.location LIKE '%$safe_search%' OR i.eligibility LIKE '%$safe_search%' OR i.description LIKE '%$safe_search%')";
}

if ($type_filter !== '') {
    $safe_type = mysqli_real_escape_string($conn, $type_filter);
    $where_parts[] = "i.type = '$safe_type'";
}

if ($location_filter !== '') {
    $safe_location = mysqli_real_escape_string($conn, $location_filter);
    $where_parts[] = "i.location LIKE '%$safe_location%'";
}

if ($deadline_filter === 'open') {
    $where_parts[] = "(i.last_date IS NULL OR i.last_date >= CURDATE())";
} elseif ($deadline_filter === 'closing_soon') {
    $where_parts[] = "i.last_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)";
}

$where_clause = '';
if (!empty($where_parts)) {
    $where_clause = ' WHERE ' . implode(' AND ', $where_parts);
}

$sort_map = array(
    'latest' => 'i.id DESC',
    'deadline_asc' => 'i.last_date ASC, i.id DESC',
    'deadline_desc' => 'i.last_date DESC, i.id DESC',
    'company_asc' => 'i.company_name ASC, i.id DESC'
);
$order_clause = isset($sort_map[$sort_filter]) ? $sort_map[$sort_filter] : $sort_map['latest'];

$count_result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM internships i $where_clause");
$total_items = (int) mysqli_fetch_assoc($count_result)['total'];
$pagination = get_pagination_data($total_items, $per_page, $page_number);

$query = "
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
    $where_clause
    ORDER BY $order_clause
    LIMIT {$pagination['per_page']} OFFSET {$pagination['offset']}
";
$result = mysqli_query($conn, $query);

render_student_page_start('Job Board');
?>

    <?php include "layout/sidebar.php"; ?>

    <div class="flex-1">
        <?php include "layout/topbar.php"; ?>

        <div class="p-6">
            <?php render_student_flash(); ?>

            <div class="mb-6 rounded-2xl bg-white p-5 shadow">
                <form method="GET" class="grid grid-cols-1 gap-4 xl:grid-cols-5">
                    <input type="text" name="q" value="<?php echo htmlspecialchars($search_term); ?>" placeholder="Search role, company, location, eligibility" class="rounded-lg border px-4 py-3 xl:col-span-2">
                    <select name="type" class="rounded-lg border px-4 py-3">
                        <option value="">All Types</option>
                        <option value="Internship" <?php echo ($type_filter === 'Internship') ? 'selected' : ''; ?>>Internship</option>
                        <option value="Placement" <?php echo ($type_filter === 'Placement') ? 'selected' : ''; ?>>Placement</option>
                    </select>
                    <input type="text" name="location" value="<?php echo htmlspecialchars($location_filter); ?>" placeholder="Location" class="rounded-lg border px-4 py-3">
                    <div class="grid grid-cols-2 gap-4">
                        <select name="deadline" class="rounded-lg border px-4 py-3">
                            <option value="">Any Deadline</option>
                            <option value="open" <?php echo ($deadline_filter === 'open') ? 'selected' : ''; ?>>Still Open</option>
                            <option value="closing_soon" <?php echo ($deadline_filter === 'closing_soon') ? 'selected' : ''; ?>>Closing Soon</option>
                        </select>
                        <select name="sort" class="rounded-lg border px-4 py-3">
                            <option value="latest" <?php echo ($sort_filter === 'latest') ? 'selected' : ''; ?>>Latest</option>
                            <option value="deadline_asc" <?php echo ($sort_filter === 'deadline_asc') ? 'selected' : ''; ?>>Deadline Soonest</option>
                            <option value="deadline_desc" <?php echo ($sort_filter === 'deadline_desc') ? 'selected' : ''; ?>>Deadline Latest</option>
                            <option value="company_asc" <?php echo ($sort_filter === 'company_asc') ? 'selected' : ''; ?>>Company A-Z</option>
                        </select>
                    </div>
                    <div class="xl:col-span-5 flex flex-wrap gap-3">
                        <button type="submit" class="rounded-lg bg-blue-600 px-5 py-3 text-sm font-semibold text-white hover:bg-blue-700">Apply Filters</button>
                        <a href="job_board.php" class="rounded-lg border border-slate-300 px-5 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50">Reset</a>
                    </div>
                </form>
            </div>

            <div class="mb-4 flex items-center justify-between text-sm text-slate-500">
                <p>Showing <?php echo mysqli_num_rows($result); ?> of <?php echo $pagination['total_items']; ?> opportunities.</p>
                <a href="saved_jobs.php" class="font-semibold text-blue-600 hover:underline">Open saved jobs</a>
            </div>

            <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
                <?php if (mysqli_num_rows($result) > 0) { ?>
                    <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                        <div class="rounded-2xl bg-white p-5 shadow">
                            <div class="flex flex-col gap-4">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <h3 class="text-lg font-bold text-slate-900"><?php echo htmlspecialchars($row['title']); ?></h3>
                                        <p class="text-gray-500"><?php echo htmlspecialchars($row['company_name']); ?></p>
                                    </div>
                                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700"><?php echo htmlspecialchars($row['type']); ?></span>
                                </div>

                                <div class="grid grid-cols-1 gap-2 text-sm text-slate-500 md:grid-cols-2">
                                    <p>Location: <?php echo htmlspecialchars($row['location']); ?></p>
                                    <p>Eligibility: <?php echo htmlspecialchars($row['eligibility']); ?></p>
                                    <p>Last Date: <?php echo htmlspecialchars($row['last_date']); ?></p>
                                    <p><?php echo ((int) $row['already_applied'] === 1) ? 'Already applied' : 'Open for application'; ?></p>
                                </div>

                                <p class="text-sm leading-6 text-slate-600"><?php echo htmlspecialchars(substr($row['description'], 0, 180)); ?><?php echo (strlen($row['description']) > 180) ? '...' : ''; ?></p>

                                <div class="flex flex-wrap gap-3">
                                    <a href="internship_details.php?id=<?php echo (int) $row['id']; ?>" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">View Details</a>
                                    <a href="toggle_saved_job.php?id=<?php echo (int) $row['id']; ?>&redirect=job_board.php?<?php echo http_build_query($_GET); ?>" class="rounded-lg border border-violet-200 px-4 py-2 text-sm font-semibold text-violet-700 hover:bg-violet-50"><?php echo ((int) $row['is_saved'] === 1) ? 'Remove Saved' : 'Save Job'; ?></a>
                                    <?php if ((int) $row['already_applied'] === 1) { ?>
                                        <span class="rounded-lg bg-green-50 px-4 py-2 text-sm font-semibold text-green-700">Applied</span>
                                    <?php } else { ?>
                                        <a href="apply.php?id=<?php echo (int) $row['id']; ?>&redirect=job_board.php?<?php echo http_build_query($_GET); ?>" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">Apply Now</a>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                <?php } else { ?>
                    <div class="rounded-2xl bg-white p-8 text-center text-slate-500 shadow xl:col-span-2">No opportunities match the filters you selected.</div>
                <?php } ?>
            </div>

            <?php render_pagination($pagination); ?>
        </div>
    </div>

</body>

</html>

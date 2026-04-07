<?php
include "../config/db.php";
include "../includes/student_auth.php";
include "../includes/pagination.php";
include "../includes/flash.php";
include "../includes/student_layout.php";

$current_page = basename($_SERVER['PHP_SELF']);
$student_page_title = 'My Applications';
$student_page_description = 'Track statuses, search submissions, and manage applications in one place.';
$student_badge = 'Applications';
$student_id = (int) $_SESSION['student_id'];

$per_page = 8;
$page_number = get_current_page_number();
$search_term = isset($_GET['q']) ? trim($_GET['q']) : '';
$status_filter = isset($_GET['status']) ? trim($_GET['status']) : '';
$where_parts = array("a.student_id = '$student_id'");

if ($search_term !== '') {
    $safe_search = mysqli_real_escape_string($conn, $search_term);
    $where_parts[] = "(i.title LIKE '%$safe_search%' OR i.company_name LIKE '%$safe_search%')";
}

if ($status_filter !== '') {
    $safe_status = mysqli_real_escape_string($conn, $status_filter);
    $where_parts[] = "a.status = '$safe_status'";
}

$where_clause = ' WHERE ' . implode(' AND ', $where_parts);

$count_query = "
    SELECT COUNT(*) AS total
    FROM applications a
    JOIN internships i ON a.internship_id = i.id
    $where_clause
";
$count_result = mysqli_query($conn, $count_query);
$total_items = (int) mysqli_fetch_assoc($count_result)['total'];
$pagination = get_pagination_data($total_items, $per_page, $page_number);

$result = mysqli_query($conn, "
    SELECT a.id,
           a.status,
           a.created_at,
           i.id AS internship_id,
           i.title,
           i.company_name,
           i.type,
           i.last_date,
           ir.round_number,
           ir.round_title,
           ir.round_status,
           ir.remarks AS round_remarks
    FROM applications a
    JOIN internships i ON a.internship_id = i.id
    LEFT JOIN interview_rounds ir
        ON ir.id = (
            SELECT ir2.id
            FROM interview_rounds ir2
            WHERE ir2.application_id = a.id
            ORDER BY ir2.round_number DESC, ir2.id DESC
            LIMIT 1
        )
    $where_clause
    ORDER BY a.created_at DESC, a.id DESC
    LIMIT {$pagination['per_page']} OFFSET {$pagination['offset']}
");

render_student_page_start('My Applications');
?>

    <?php include "layout/sidebar.php"; ?>

    <div class="flex-1">
        <?php include "layout/topbar.php"; ?>

        <div class="p-8">
            <?php render_student_flash(); ?>

            <div class="mb-6 rounded-2xl bg-white p-5 shadow">
                <form method="GET" class="grid grid-cols-1 gap-4 md:grid-cols-3">
                    <input type="text" name="q" value="<?php echo htmlspecialchars($search_term); ?>" placeholder="Search by role or company" class="rounded-lg border px-4 py-3 md:col-span-2">
                    <select name="status" class="rounded-lg border px-4 py-3">
                        <option value="">All Statuses</option>
                        <?php foreach (array('Pending', 'Interview', 'Shortlisted', 'Selected', 'Rejected', 'Offered') as $status_option) { ?>
                            <option value="<?php echo $status_option; ?>" <?php echo ($status_filter === $status_option) ? 'selected' : ''; ?>><?php echo $status_option; ?></option>
                        <?php } ?>
                    </select>
                    <div class="md:col-span-3 flex flex-wrap gap-3">
                        <button type="submit" class="rounded-lg bg-blue-600 px-5 py-3 text-sm font-semibold text-white hover:bg-blue-700">Filter Applications</button>
                        <a href="my_applications.php" class="rounded-lg border border-slate-300 px-5 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50">Reset</a>
                    </div>
                </form>
            </div>

            <div class="rounded-2xl bg-white shadow overflow-hidden">
                <table class="min-w-full">
                    <thead class="bg-gray-100 text-left text-sm text-gray-600">
                        <tr>
                            <th class="px-6 py-3">Position</th>
                            <th class="px-6 py-3">Company</th>
                            <th class="px-6 py-3">Applied</th>
                            <th class="px-6 py-3">Deadline</th>
                            <th class="px-6 py-3">Status</th>
                            <th class="px-6 py-3">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($result) > 0) { ?>
                            <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                                <?php
                                $status = $row['status'];
                                $color = "bg-gray-200 text-gray-700";
                                if ($status == "Interview") $color = "bg-yellow-100 text-yellow-700";
                                if ($status == "Shortlisted") $color = "bg-blue-100 text-blue-700";
                                if ($status == "Offered" || $status == "Selected") $color = "bg-green-100 text-green-700";
                                if ($status == "Rejected") $color = "bg-red-100 text-red-700";
                                ?>
                                <tr class="border-t">
                                    <td class="px-6 py-4 font-semibold text-slate-900"><?php echo htmlspecialchars($row['title']); ?></td>
                                    <td class="px-6 py-4 text-gray-600"><?php echo htmlspecialchars($row['company_name']); ?></td>
                                    <td class="px-6 py-4 text-gray-500"><?php echo date("M d, Y", strtotime($row['created_at'])); ?></td>
                                    <td class="px-6 py-4 text-gray-500"><?php echo htmlspecialchars($row['last_date']); ?></td>
                                    <td class="px-6 py-4"><span class="px-3 py-1 text-sm rounded <?php echo $color; ?>"><?php echo htmlspecialchars($status); ?></span></td>
                                    <td class="px-6 py-4">
                                        <div class="flex flex-wrap gap-2">
                                            <a href="internship_details.php?id=<?php echo (int) $row['internship_id']; ?>" class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">View</a>
                                            <?php if (!in_array($status, array('Selected', 'Offered', 'Rejected'), true)) { ?>
                                                <a href="withdraw_application.php?id=<?php echo (int) $row['id']; ?>&redirect=my_applications.php?<?php echo http_build_query($_GET); ?>" class="rounded-lg border border-red-200 px-3 py-2 text-sm font-semibold text-red-700 hover:bg-red-50" onclick="return confirm('Withdraw this application?');">Withdraw</a>
                                            <?php } ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php } ?>
                        <?php } else { ?>
                            <tr>
                                <td colspan="6" class="px-6 py-10 text-center text-gray-500">No applications match the selected filters.</td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>

            <div class="mt-8 rounded-2xl bg-white p-6 shadow">
                <div class="mb-5">
                    <h2 class="text-lg font-semibold text-slate-900">Interview Round Information</h2>
                    <p class="text-sm text-slate-500">Round-by-round progress for your current applications.</p>
                </div>

                <div class="space-y-4">
                    <?php
                    mysqli_data_seek($result, 0);
                    $has_round_data = false;
                    while ($round_row = mysqli_fetch_assoc($result)) {
                        if (!empty($round_row['round_number'])) {
                            $has_round_data = true;
                        }
                    }
                    mysqli_data_seek($result, 0);
                    ?>

                    <?php if (mysqli_num_rows($result) > 0) { ?>
                        <?php while ($round_row = mysqli_fetch_assoc($result)) { ?>
                            <div class="rounded-xl border border-slate-200 p-4">
                                <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                                    <div>
                                        <p class="font-semibold text-slate-900"><?php echo htmlspecialchars($round_row['title']); ?> · <?php echo htmlspecialchars($round_row['company_name']); ?></p>
                                        <?php if (!empty($round_row['round_number'])) { ?>
                                            <p class="mt-1 text-sm text-slate-600">
                                                Round <?php echo (int) $round_row['round_number']; ?> · <?php echo htmlspecialchars($round_row['round_title']); ?>
                                            </p>
                                        <?php } else { ?>
                                            <p class="mt-1 text-sm text-slate-500">No interview round has been scheduled yet.</p>
                                        <?php } ?>
                                    </div>

                                    <div class="text-sm font-semibold">
                                        <?php if ($round_row['round_status'] === 'Selected') { ?>
                                            <span class="rounded-full bg-green-100 px-3 py-1 text-green-700">
                                                Selected for Round <?php echo (int) $round_row['round_number'] + 1; ?>
                                            </span>
                                        <?php } elseif ($round_row['round_status'] === 'Rejected') { ?>
                                            <span class="rounded-full bg-red-100 px-3 py-1 text-red-700">
                                                Rejected in Round <?php echo (int) $round_row['round_number']; ?>
                                            </span>
                                        <?php } elseif ($round_row['round_status'] === 'Completed') { ?>
                                            <span class="rounded-full bg-blue-100 px-3 py-1 text-blue-700">
                                                Round <?php echo (int) $round_row['round_number']; ?> completed
                                            </span>
                                        <?php } elseif (!empty($round_row['round_status'])) { ?>
                                            <span class="rounded-full bg-slate-100 px-3 py-1 text-slate-700">
                                                <?php echo htmlspecialchars($round_row['round_status']); ?>
                                            </span>
                                        <?php } ?>
                                    </div>
                                </div>

                                <?php if (!empty($round_row['round_remarks'])) { ?>
                                    <p class="mt-3 text-sm leading-6 text-slate-600"><?php echo htmlspecialchars($round_row['round_remarks']); ?></p>
                                <?php } ?>
                            </div>
                        <?php } ?>
                    <?php } else { ?>
                        <div class="rounded-xl bg-slate-50 px-4 py-6 text-sm text-slate-500">Apply to an internship to start seeing interview round updates here.</div>
                    <?php } ?>
                </div>
            </div>

            <?php render_pagination($pagination); ?>
        </div>
    </div>

</body>

</html>

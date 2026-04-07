<?php
include "../config/db.php";
include "../includes/admin_auth.php";
include "../includes/pagination.php";

$current_admin_page = 'view_applications.php';
$admin_page_title = 'Applications';
$admin_page_description = 'Filter, paginate, and update student application status.';

$per_page = 10;
$current_page = get_current_page_number();
$status_filter = isset($_GET['status']) ? trim($_GET['status']) : '';
$allowed_statuses = array('Pending', 'Interview', 'Shortlisted', 'Selected', 'Rejected');
$where_clause = '';

if (in_array($status_filter, $allowed_statuses, true)) {
    $safe_status = mysqli_real_escape_string($conn, $status_filter);
    $where_clause = " WHERE a.status = '$safe_status'";
} else {
    $status_filter = '';
}

$count_query = "
    SELECT COUNT(*) AS total
    FROM applications a
    JOIN students s ON a.student_id = s.id
    JOIN internships i ON a.internship_id = i.id
    $where_clause
";
$count_result = mysqli_query($conn, $count_query);
$total_items = (int) mysqli_fetch_assoc($count_result)['total'];
$pagination = get_pagination_data($total_items, $per_page, $current_page);

$query = "
    SELECT a.id, s.name, s.email, i.company_name, i.title, a.status
    FROM applications a
    JOIN students s ON a.student_id = s.id
    JOIN internships i ON a.internship_id = i.id
    $where_clause
    ORDER BY a.id DESC
    LIMIT {$pagination['per_page']} OFFSET {$pagination['offset']}
";

$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html>

<head>
    <title>View Applications</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 min-h-screen">
    <?php include "../includes/admin_topbar.php"; ?>

    <div class="mx-auto max-w-7xl p-6">

        <h3 class="text-2xl font-semibold mb-4">Student Applications</h3>

        <div class="mb-4 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <p class="text-sm text-gray-600">
                Showing <?php echo mysqli_num_rows($result); ?> of <?php echo $pagination['total_items']; ?> application records.
            </p>

            <form method="GET" class="flex flex-wrap gap-2">
                <select name="status" class="rounded border px-3 py-2">
                    <option value="">All Statuses</option>
                    <?php foreach ($allowed_statuses as $status_option) { ?>
                        <option value="<?php echo $status_option; ?>" <?php echo ($status_filter === $status_option) ? 'selected' : ''; ?>>
                            <?php echo $status_option; ?>
                        </option>
                    <?php } ?>
                </select>

                <button type="submit" class="rounded bg-blue-600 px-4 py-2 text-white hover:bg-blue-700 transition">
                    Filter
                </button>
            </form>
        </div>

        <div class="overflow-x-auto bg-white shadow-md rounded-lg">
            <table class="min-w-full border border-gray-200">

                <thead class="bg-blue-600 text-white">
                    <tr>
                        <th class="py-3 px-4">Student Name</th>
                        <th class="py-3 px-4">Email</th>
                        <th class="py-3 px-4">Company</th>
                        <th class="py-3 px-4">Title</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4">Action</th>
                    </tr>
                </thead>

                <tbody class="text-center">

                    <?php if (mysqli_num_rows($result) > 0) { ?>
                        <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                            <tr class="border-t hover:bg-gray-100 transition">
                                <td class="py-2 px-4"><?php echo htmlspecialchars($row['name']); ?></td>
                                <td class="py-2 px-4"><?php echo htmlspecialchars($row['email']); ?></td>
                                <td class="py-2 px-4"><?php echo htmlspecialchars($row['company_name']); ?></td>
                                <td class="py-2 px-4"><?php echo htmlspecialchars($row['title']); ?></td>

                                <td class="py-2 px-4">
                                    <?php
                                    if ($row['status'] == "Selected") {
                                        echo "<span class='text-green-600 font-semibold'>Selected</span>";
                                    } elseif ($row['status'] == "Rejected") {
                                        echo "<span class='text-red-600 font-semibold'>Rejected</span>";
                                    } elseif ($row['status'] == "Interview" || $row['status'] == "Shortlisted") {
                                        echo "<span class='text-blue-600 font-semibold'>" . htmlspecialchars($row['status']) . "</span>";
                                    } else {
                                        echo "<span class='text-yellow-600 font-semibold'>Pending</span>";
                                    }
                                    ?>
                                </td>

                                <td class="py-2 px-4 space-x-2">
                                    <a href="update_status.php?id=<?php echo (int) $row['id']; ?>&status=Selected&page=<?php echo $pagination['current_page']; ?>&status_filter=<?php echo urlencode($status_filter); ?>"
                                        class="bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700 transition">
                                        Select
                                    </a>

                                    <a href="update_status.php?id=<?php echo (int) $row['id']; ?>&status=Rejected&page=<?php echo $pagination['current_page']; ?>&status_filter=<?php echo urlencode($status_filter); ?>"
                                        class="bg-red-600 text-white px-3 py-1 rounded hover:bg-red-700 transition">
                                        Reject
                                    </a>

                                    <a href="application_rounds.php?application_id=<?php echo (int) $row['id']; ?>"
                                        class="bg-slate-800 text-white px-3 py-1 rounded hover:bg-slate-900 transition">
                                        Rounds
                                    </a>
                                </td>
                            </tr>
                        <?php } ?>
                    <?php } else { ?>
                        <tr>
                            <td colspan="6" class="px-4 py-6 text-center text-gray-500">No applications found for the selected filter.</td>
                        </tr>
                    <?php } ?>

                </tbody>

            </table>
        </div>

        <?php render_pagination($pagination); ?>

    </div>

</body>

</html>

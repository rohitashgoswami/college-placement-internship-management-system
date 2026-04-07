<?php
include "../config/db.php";
include "../includes/admin_auth.php";
include "../includes/pagination.php";

$current_admin_page = 'manage_internships.php';
$admin_page_title = 'Manage Internships';
$admin_page_description = 'Review active listings, search roles, and remove outdated posts.';

$per_page = 8;
$current_page = get_current_page_number();
$search_term = isset($_GET['q']) ? trim($_GET['q']) : '';
$where_clause = '';

if ($search_term !== '') {
    $safe_search = mysqli_real_escape_string($conn, $search_term);
    $where_clause = " WHERE title LIKE '%$safe_search%' OR company_name LIKE '%$safe_search%' OR location LIKE '%$safe_search%' OR type LIKE '%$safe_search%'";
}

$count_result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM internships $where_clause");
$total_items = (int) mysqli_fetch_assoc($count_result)['total'];
$pagination = get_pagination_data($total_items, $per_page, $current_page);

$query = "
    SELECT id, company_name, title, type, location, last_date
    FROM internships
    $where_clause
    ORDER BY id DESC
    LIMIT {$pagination['per_page']} OFFSET {$pagination['offset']}
";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html>

<head>
    <title>Manage Internships</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="min-h-screen bg-gray-100">
    <?php include "../includes/admin_topbar.php"; ?>

    <div class="mx-auto max-w-7xl p-6">
        <div class="mb-6 flex flex-col gap-4 rounded-2xl bg-white p-5 shadow md:flex-row md:items-center md:justify-between">
            <form method="GET" class="flex w-full flex-col gap-3 md:max-w-2xl md:flex-row">
                <input type="text" name="q" value="<?php echo htmlspecialchars($search_term); ?>"
                    placeholder="Search by role, company, type, or location"
                    class="w-full rounded-lg border px-4 py-3">
                <button type="submit" class="rounded-lg bg-blue-600 px-5 py-3 font-semibold text-white hover:bg-blue-700">Search</button>
            </form>

            <a href="add_internship.php" class="rounded-lg bg-blue-600 px-5 py-3 text-center font-semibold text-white hover:bg-blue-700">
                Add New Internship
            </a>
        </div>

        <div class="overflow-hidden rounded-lg bg-white shadow">
            <table class="min-w-full border border-gray-200">
                <thead class="bg-gray-100 text-left text-sm text-gray-700">
                    <tr>
                        <th class="px-4 py-3">Title</th>
                        <th class="px-4 py-3">Company</th>
                        <th class="px-4 py-3">Type</th>
                        <th class="px-4 py-3">Location</th>
                        <th class="px-4 py-3">Last Date</th>
                        <th class="px-4 py-3">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($result) > 0) { ?>
                        <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                            <tr class="border-t">
                                <td class="px-4 py-3 font-semibold"><?php echo htmlspecialchars($row['title']); ?></td>
                                <td class="px-4 py-3"><?php echo htmlspecialchars($row['company_name']); ?></td>
                                <td class="px-4 py-3"><?php echo htmlspecialchars($row['type']); ?></td>
                                <td class="px-4 py-3"><?php echo htmlspecialchars($row['location']); ?></td>
                                <td class="px-4 py-3"><?php echo htmlspecialchars($row['last_date']); ?></td>
                                <td class="px-4 py-3">
                                    <a href="delete_internship.php?id=<?php echo (int) $row['id']; ?>&page=<?php echo $pagination['current_page']; ?>"
                                        class="rounded bg-red-600 px-3 py-2 text-sm text-white hover:bg-red-700"
                                        onclick="return confirm('Delete this internship listing?');">
                                        Delete
                                    </a>
                                </td>
                            </tr>
                        <?php } ?>
                    <?php } else { ?>
                        <tr>
                            <td colspan="6" class="px-4 py-6 text-center text-gray-500">No internship listings available yet.</td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>

        <?php render_pagination($pagination); ?>
    </div>

</body>

</html>

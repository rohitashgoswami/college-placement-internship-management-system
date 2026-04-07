<?php
include "../config/db.php";
include "../includes/admin_auth.php";
include "../includes/pagination.php";

$current_admin_page = 'manage_students.php';
$admin_page_title = 'Manage Students';
$admin_page_description = 'Search student records, monitor participation, and open student details.';

$per_page = 10;
$current_page = get_current_page_number();
$search_term = isset($_GET['q']) ? trim($_GET['q']) : '';
$where_clause = '';

if ($search_term !== '') {
    $safe_search = mysqli_real_escape_string($conn, $search_term);
    $where_clause = " WHERE s.name LIKE '%$safe_search%' OR s.email LIKE '%$safe_search%' OR s.roll_number LIKE '%$safe_search%'";
}

$count_result = mysqli_query($conn, "
    SELECT COUNT(*) AS total
    FROM students s
    $where_clause
");
$total_items = (int) mysqli_fetch_assoc($count_result)['total'];
$pagination = get_pagination_data($total_items, $per_page, $current_page);

$students = mysqli_query($conn, "
    SELECT s.id, s.name, s.email, s.roll_number, COUNT(a.id) AS application_count
    FROM students s
    LEFT JOIN applications a ON a.student_id = s.id
    $where_clause
    GROUP BY s.id, s.name, s.email, s.roll_number
    ORDER BY s.id DESC
    LIMIT {$pagination['per_page']} OFFSET {$pagination['offset']}
");
?>

<!DOCTYPE html>
<html>

<head>
    <title>Manage Students</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="min-h-screen bg-gray-100">
    <?php include "../includes/admin_topbar.php"; ?>

    <div class="mx-auto max-w-7xl p-6">
        <div class="mb-6 rounded-2xl bg-white p-5 shadow">
            <form method="GET" class="flex flex-col gap-3 md:flex-row">
                <input type="text" name="q" value="<?php echo htmlspecialchars($search_term); ?>"
                    placeholder="Search by student name, email, or roll number"
                    class="w-full rounded-lg border px-4 py-3">
                <button type="submit" class="rounded-lg bg-blue-600 px-5 py-3 font-semibold text-white hover:bg-blue-700">
                    Search
                </button>
            </form>
        </div>

        <div class="overflow-hidden rounded-2xl bg-white shadow">
            <table class="min-w-full border border-gray-200">
                <thead class="bg-gray-100 text-left text-sm text-gray-700">
                    <tr>
                        <th class="px-4 py-3">Student</th>
                        <th class="px-4 py-3">Email</th>
                        <th class="px-4 py-3">Roll Number</th>
                        <th class="px-4 py-3">Applications</th>
                        <th class="px-4 py-3">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($students) > 0) { ?>
                        <?php while ($row = mysqli_fetch_assoc($students)) { ?>
                            <tr class="border-t">
                                <td class="px-4 py-3 font-semibold text-gray-800"><?php echo htmlspecialchars($row['name']); ?></td>
                                <td class="px-4 py-3"><?php echo htmlspecialchars($row['email']); ?></td>
                                <td class="px-4 py-3"><?php echo htmlspecialchars($row['roll_number']); ?></td>
                                <td class="px-4 py-3">
                                    <span class="rounded-full bg-blue-100 px-3 py-1 text-sm font-semibold text-blue-700">
                                        <?php echo (int) $row['application_count']; ?>
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <a href="view_student.php?id=<?php echo (int) $row['id']; ?>" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">
                                        View Student
                                    </a>
                                </td>
                            </tr>
                        <?php } ?>
                    <?php } else { ?>
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-gray-500">No students found for this search.</td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>

        <?php render_pagination($pagination); ?>
    </div>

</body>

</html>

<?php
include "../config/db.php";
include "../includes/student_auth.php";
include "../includes/pagination.php";
include "../includes/flash.php";
include "../includes/student_layout.php";

$current_page = basename($_SERVER['PHP_SELF']);
$student_page_title = 'Saved Jobs';
$student_page_description = 'Your bookmarked opportunities, ready when you want to revisit them.';
$student_badge = 'Saved';
$student_id = (int) $_SESSION['student_id'];
$per_page = 8;
$page_number = get_current_page_number();

$count_result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM saved_jobs WHERE student_id = '$student_id'");
$total_items = (int) mysqli_fetch_assoc($count_result)['total'];
$pagination = get_pagination_data($total_items, $per_page, $page_number);

$jobs = mysqli_query($conn, "
    SELECT i.*, sj.saved_at,
           EXISTS(
               SELECT 1 FROM applications a
               WHERE a.student_id = '$student_id' AND a.internship_id = i.id
           ) AS already_applied
    FROM saved_jobs sj
    JOIN internships i ON sj.internship_id = i.id
    WHERE sj.student_id = '$student_id'
    ORDER BY sj.saved_at DESC
    LIMIT {$pagination['per_page']} OFFSET {$pagination['offset']}
");

render_student_page_start('Saved Jobs');
?>

    <?php include "layout/sidebar.php"; ?>

    <div class="ml-64 min-h-screen flex-1">
        <?php include "layout/topbar.php"; ?>

        <div class="p-8 pt-32">
            <?php render_student_flash(); ?>

            <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
                <?php if (mysqli_num_rows($jobs) > 0) { ?>
                    <?php while ($row = mysqli_fetch_assoc($jobs)) { ?>
                        <div class="rounded-2xl bg-white p-6 shadow">
                            <h3 class="text-xl font-bold text-slate-900"><?php echo htmlspecialchars($row['title']); ?></h3>
                            <p class="mt-1 text-slate-500"><?php echo htmlspecialchars($row['company_name']); ?> · <?php echo htmlspecialchars($row['location']); ?></p>
                            <p class="mt-3 text-sm text-slate-600">Saved on <?php echo date("M d, Y", strtotime($row['saved_at'])); ?></p>
                            <p class="mt-2 text-sm leading-6 text-slate-600"><?php echo htmlspecialchars(substr($row['description'], 0, 180)); ?><?php echo (strlen($row['description']) > 180) ? '...' : ''; ?></p>

                            <div class="mt-5 flex flex-wrap gap-3">
                                <a href="internship_details.php?id=<?php echo (int) $row['id']; ?>" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">View Details</a>
                                <a href="toggle_saved_job.php?id=<?php echo (int) $row['id']; ?>&redirect=saved_jobs.php?page=<?php echo $pagination['current_page']; ?>" class="rounded-lg border border-red-200 px-4 py-2 text-sm font-semibold text-red-700 hover:bg-red-50">Remove</a>
                                <?php if ((int) $row['already_applied'] === 1) { ?>
                                    <span class="rounded-lg bg-green-50 px-4 py-2 text-sm font-semibold text-green-700">Applied</span>
                                <?php } else { ?>
                                    <a href="apply.php?id=<?php echo (int) $row['id']; ?>&redirect=saved_jobs.php?page=<?php echo $pagination['current_page']; ?>" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">Apply</a>
                                <?php } ?>
                            </div>
                        </div>
                    <?php } ?>
                <?php } else { ?>
                    <div class="rounded-2xl bg-white p-8 text-center text-slate-500 shadow xl:col-span-2">No saved jobs yet. Use the job board to bookmark opportunities.</div>
                <?php } ?>
            </div>

            <?php render_pagination($pagination); ?>
        </div>
    </div>

</body>

</html>

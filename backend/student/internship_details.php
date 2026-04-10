<?php
include "../config/db.php";
include "../includes/student_auth.php";
include "../includes/flash.php";
include "../includes/student_layout.php";

$current_page = 'job_board.php';
$student_page_title = 'Internship Details';
$student_page_description = 'Review the full role description before you apply.';
$student_badge = 'Details';
$student_id = (int) $_SESSION['student_id'];
$internship_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

$result = mysqli_query($conn, "
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
    WHERE i.id = '$internship_id'
    LIMIT 1
");

$internship = $result ? mysqli_fetch_assoc($result) : null;

if (!$internship) {
    set_flash_message('error', 'Internship not found.');
    header("Location: job_board.php");
    exit();
}

render_student_page_start('Internship Details');
?>

    <?php include "layout/sidebar.php"; ?>

    <div class="ml-64 min-h-screen flex-1">
        <?php include "layout/topbar.php"; ?>

        <div class="p-8 pt-32">
            <?php render_student_flash(); ?>

            <div class="rounded-2xl bg-white p-8 shadow">
                <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                    <div>
                        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700"><?php echo htmlspecialchars($internship['type']); ?></span>
                        <h2 class="mt-4 text-3xl font-bold text-slate-900"><?php echo htmlspecialchars($internship['title']); ?></h2>
                        <p class="mt-2 text-lg text-slate-600"><?php echo htmlspecialchars($internship['company_name']); ?></p>
                    </div>

                    <div class="flex flex-wrap gap-3">
                        <a href="toggle_saved_job.php?id=<?php echo (int) $internship['id']; ?>&redirect=internship_details.php?id=<?php echo (int) $internship['id']; ?>"
                            class="rounded-lg border border-violet-200 px-4 py-2 text-sm font-semibold text-violet-700 hover:bg-violet-50">
                            <?php echo ((int) $internship['is_saved'] === 1) ? 'Remove Saved' : 'Save Job'; ?>
                        </a>

                        <?php if ((int) $internship['already_applied'] === 1) { ?>
                            <span class="rounded-lg bg-green-50 px-4 py-2 text-sm font-semibold text-green-700">Already Applied</span>
                        <?php } else { ?>
                            <a href="apply.php?id=<?php echo (int) $internship['id']; ?>&redirect=internship_details.php?id=<?php echo (int) $internship['id']; ?>"
                                class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                                Apply Now
                            </a>
                        <?php } ?>
                    </div>
                </div>

                <div class="mb-8 grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
                    <div class="rounded-xl bg-slate-50 p-4">
                        <p class="text-xs uppercase tracking-wide text-slate-400">Location</p>
                        <p class="mt-2 font-semibold text-slate-900"><?php echo htmlspecialchars($internship['location']); ?></p>
                    </div>
                    <div class="rounded-xl bg-slate-50 p-4">
                        <p class="text-xs uppercase tracking-wide text-slate-400">Eligibility</p>
                        <p class="mt-2 font-semibold text-slate-900"><?php echo htmlspecialchars($internship['eligibility']); ?></p>
                    </div>
                    <div class="rounded-xl bg-slate-50 p-4">
                        <p class="text-xs uppercase tracking-wide text-slate-400">Deadline</p>
                        <p class="mt-2 font-semibold text-slate-900"><?php echo htmlspecialchars($internship['last_date']); ?></p>
                    </div>
                    <div class="rounded-xl bg-slate-50 p-4">
                        <p class="text-xs uppercase tracking-wide text-slate-400">Status</p>
                        <p class="mt-2 font-semibold text-slate-900"><?php echo ((int) $internship['already_applied'] === 1) ? 'Applied' : 'Open'; ?></p>
                    </div>
                </div>

                <div class="rounded-2xl border border-slate-200 p-6">
                    <h3 class="text-lg font-semibold text-slate-900">Role Description</h3>
                    <p class="mt-4 whitespace-pre-line leading-7 text-slate-600"><?php echo htmlspecialchars($internship['description']); ?></p>
                </div>
            </div>
        </div>
    </div>

</body>

</html>

<?php
include "../config/db.php";
include "../includes/admin_auth.php";

$current_admin_page = 'view_applications.php';
$admin_page_title = 'Interview Rounds';
$admin_page_description = 'Track round-by-round progression for a student application.';

$application_id = isset($_GET['application_id']) ? (int) $_GET['application_id'] : 0;

$application_query = mysqli_query($conn, "
    SELECT a.id, a.status, s.name, s.email, i.title, i.company_name
    FROM applications a
    JOIN students s ON s.id = a.student_id
    JOIN internships i ON i.id = a.internship_id
    WHERE a.id = '$application_id'
    LIMIT 1
");
$application = $application_query ? mysqli_fetch_assoc($application_query) : null;

if (!$application) {
    header("Location: view_applications.php");
    exit();
}

$message = '';
$error = '';

if (isset($_POST['save_round'])) {
    $round_number = max(1, (int) $_POST['round_number']);
    $round_title = mysqli_real_escape_string($conn, trim($_POST['round_title']));
    $round_status = mysqli_real_escape_string($conn, trim($_POST['round_status']));
    $scheduled_at = trim($_POST['scheduled_at']);
    $remarks = mysqli_real_escape_string($conn, trim($_POST['remarks']));
    $safe_scheduled_at = ($scheduled_at !== '') ? "'" . mysqli_real_escape_string($conn, $scheduled_at) . "'" : "NULL";

    $existing = mysqli_query($conn, "SELECT id FROM interview_rounds WHERE application_id = '$application_id' AND round_number = '$round_number' LIMIT 1");

    if ($round_title === '') {
        $error = 'Round title is required.';
    } else {
        if ($existing && mysqli_num_rows($existing) > 0) {
            mysqli_query($conn, "
                UPDATE interview_rounds
                SET round_title = '$round_title',
                    round_status = '$round_status',
                    scheduled_at = $safe_scheduled_at,
                    remarks = '$remarks'
                WHERE application_id = '$application_id' AND round_number = '$round_number'
            ");
            $message = 'Interview round updated successfully.';
        } else {
            mysqli_query($conn, "
                INSERT INTO interview_rounds (application_id, round_number, round_title, round_status, scheduled_at, remarks)
                VALUES ('$application_id', '$round_number', '$round_title', '$round_status', $safe_scheduled_at, '$remarks')
            ");
            $message = 'Interview round added successfully.';
        }

        if ($round_status === 'Selected') {
            mysqli_query($conn, "UPDATE applications SET status = 'Interview' WHERE id = '$application_id'");
        } elseif ($round_status === 'Rejected') {
            mysqli_query($conn, "UPDATE applications SET status = 'Rejected' WHERE id = '$application_id'");
        } elseif ($round_status === 'Completed') {
            mysqli_query($conn, "UPDATE applications SET status = 'Shortlisted' WHERE id = '$application_id'");
        }
    }
}

$rounds = mysqli_query($conn, "
    SELECT id, round_number, round_title, round_status, scheduled_at, remarks
    FROM interview_rounds
    WHERE application_id = '$application_id'
    ORDER BY round_number ASC
");
?>

<!DOCTYPE html>
<html>

<head>
    <title>Interview Rounds</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="min-h-screen bg-gray-100">
    <?php include "../includes/admin_topbar.php"; ?>

    <div class="mx-auto max-w-7xl space-y-6 p-6">
        <div class="rounded-2xl bg-white p-6 shadow">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800"><?php echo htmlspecialchars($application['name']); ?></h2>
                    <p class="text-sm text-gray-500"><?php echo htmlspecialchars($application['email']); ?></p>
                    <p class="mt-2 text-sm text-gray-600"><?php echo htmlspecialchars($application['title']); ?> at <?php echo htmlspecialchars($application['company_name']); ?></p>
                </div>
                <a href="view_applications.php" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Back to Applications</a>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
            <div class="rounded-2xl bg-white p-6 shadow">
                <h3 class="text-lg font-bold text-gray-800">Add or Update Round</h3>
                <p class="mt-1 text-sm text-gray-500">Use this to record each stage of the interview process.</p>

                <?php if ($message !== '') { ?>
                    <div class="mt-4 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700"><?php echo htmlspecialchars($message); ?></div>
                <?php } ?>

                <?php if ($error !== '') { ?>
                    <div class="mt-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"><?php echo htmlspecialchars($error); ?></div>
                <?php } ?>

                <form method="POST" class="mt-6 space-y-4">
                    <div>
                        <label class="mb-2 block text-sm font-semibold text-gray-700">Round Number</label>
                        <input type="number" name="round_number" min="1" class="w-full rounded-lg border px-4 py-3" required>
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-semibold text-gray-700">Round Title</label>
                        <input type="text" name="round_title" placeholder="Round 1 Technical Interview" class="w-full rounded-lg border px-4 py-3" required>
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-semibold text-gray-700">Round Status</label>
                        <select name="round_status" class="w-full rounded-lg border px-4 py-3">
                            <option value="Scheduled">Scheduled</option>
                            <option value="Completed">Completed</option>
                            <option value="Selected">Selected</option>
                            <option value="Rejected">Rejected</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-semibold text-gray-700">Scheduled At</label>
                        <input type="datetime-local" name="scheduled_at" class="w-full rounded-lg border px-4 py-3">
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-semibold text-gray-700">Remarks</label>
                        <textarea name="remarks" rows="4" class="w-full rounded-lg border px-4 py-3" placeholder="Selected for round 2, waiting for final HR, rejected after round 1, etc."></textarea>
                    </div>
                    <button type="submit" name="save_round" class="rounded-lg bg-blue-600 px-5 py-3 text-sm font-semibold text-white hover:bg-blue-700">Save Round</button>
                </form>
            </div>

            <div class="rounded-2xl bg-white p-6 shadow">
                <h3 class="text-lg font-bold text-gray-800">Recorded Rounds</h3>
                <p class="mt-1 text-sm text-gray-500">Students will see this timeline in their application tracker.</p>

                <div class="mt-6 space-y-4">
                    <?php if ($rounds && mysqli_num_rows($rounds) > 0) { ?>
                        <?php while ($row = mysqli_fetch_assoc($rounds)) { ?>
                            <div class="rounded-xl border border-slate-200 p-4">
                                <div class="flex items-center justify-between gap-3">
                                    <div>
                                        <p class="font-semibold text-slate-900">Round <?php echo (int) $row['round_number']; ?> · <?php echo htmlspecialchars($row['round_title']); ?></p>
                                        <p class="mt-1 text-sm text-slate-500">
                                            <?php echo ($row['scheduled_at']) ? date("M d, Y h:i A", strtotime($row['scheduled_at'])) : 'Schedule not added'; ?>
                                        </p>
                                    </div>
                                    <span class="rounded-full bg-slate-100 px-3 py-1 text-sm font-semibold text-slate-700"><?php echo htmlspecialchars($row['round_status']); ?></span>
                                </div>
                                <p class="mt-3 text-sm leading-6 text-slate-600"><?php echo htmlspecialchars($row['remarks'] ?: 'No remarks added yet.'); ?></p>
                            </div>
                        <?php } ?>
                    <?php } else { ?>
                        <div class="rounded-xl bg-slate-50 px-4 py-6 text-sm text-slate-500">No interview rounds have been added for this application yet.</div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>

</body>

</html>

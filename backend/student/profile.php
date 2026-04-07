<?php
include "../config/db.php";
include "../includes/student_auth.php";
include "../includes/flash.php";
include "../includes/student_layout.php";

$current_page = basename($_SERVER['PHP_SELF']);
$student_page_title = 'My Profile';
$student_page_description = 'Keep your academic and career profile complete for better opportunities.';
$student_badge = 'Profile';
$student_id = (int) $_SESSION['student_id'];

$query = "SELECT * FROM students WHERE id='$student_id'";
$result = mysqli_query($conn, $query);
$student = mysqli_fetch_assoc($result);

$complete = 0;
$profile_fields = array('phone', 'location', 'cgpa', 'skills', 'department', 'graduation_year', 'bio', 'resume', 'linkedin_url', 'github_url');
foreach ($profile_fields as $field) {
    if (!empty($student[$field])) {
        $complete += 10;
    }
}

$skills = explode(",", $student['skills'] ?? "");

render_student_page_start('My Profile');
?>

    <?php include "layout/sidebar.php"; ?>

    <div class="flex-1">
        <?php include "layout/topbar.php"; ?>

        <div class="p-8 space-y-6">
            <?php render_student_flash(); ?>

            <div class="grid gap-6 lg:grid-cols-3">
                <div class="bg-white rounded-xl border p-6 shadow text-center">
                    <div class="h-20 w-20 bg-blue-600 text-white rounded-full flex items-center justify-center mx-auto text-xl font-bold mb-4">
                        <?php echo strtoupper(substr($student['name'], 0, 1)); ?>
                    </div>

                    <h2 class="text-lg font-bold"><?php echo htmlspecialchars($student['name']); ?></h2>
                    <p class="text-sm text-gray-500"><?php echo htmlspecialchars($student['department'] ?: 'Department not added'); ?></p>

                    <span class="inline-block mt-2 px-3 py-1 text-xs bg-green-100 text-green-700 rounded">Active</span>

                    <div class="mt-6 space-y-3 text-left text-sm text-gray-600">
                        <div>Email: <?php echo htmlspecialchars($student['email']); ?></div>
                        <div>Phone: <?php echo htmlspecialchars($student['phone'] ?: 'Not added'); ?></div>
                        <div>Location: <?php echo htmlspecialchars($student['location'] ?: 'Not added'); ?></div>
                        <div>Roll Number: <?php echo htmlspecialchars($student['roll_number']); ?></div>
                        <div>Graduation Year: <?php echo htmlspecialchars($student['graduation_year'] ?: 'Not added'); ?></div>
                        <div>CGPA: <?php echo htmlspecialchars($student['cgpa'] ?: 'Not added'); ?></div>
                    </div>

                    <div class="mt-5 flex gap-2">
                        <?php if (!empty($student['github_url'])) { ?>
                            <a href="<?php echo htmlspecialchars($student['github_url']); ?>" target="_blank" class="flex-1 border p-2 rounded text-sm">GitHub</a>
                        <?php } ?>
                        <?php if (!empty($student['linkedin_url'])) { ?>
                            <a href="<?php echo htmlspecialchars($student['linkedin_url']); ?>" target="_blank" class="flex-1 border p-2 rounded text-sm">LinkedIn</a>
                        <?php } ?>
                    </div>

                    <a href="edit_profile.php" class="block mt-3 w-full bg-blue-600 text-white py-2 rounded text-sm">Edit Profile</a>
                </div>

                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-white rounded-xl border p-6 shadow">
                        <div class="flex justify-between mb-2">
                            <h3 class="font-semibold">Profile Completion</h3>
                            <span class="text-sm font-semibold text-blue-600"><?php echo $complete; ?>%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded h-2">
                            <div class="bg-blue-600 h-2 rounded" style="width:<?php echo $complete; ?>%"></div>
                        </div>
                        <p class="text-xs text-gray-500 mt-2">Complete your profile to improve your application readiness.</p>
                    </div>

                    <div class="bg-white rounded-xl border p-6 shadow">
                        <h3 class="font-semibold mb-4">About</h3>
                        <p class="text-sm leading-6 text-gray-600"><?php echo htmlspecialchars($student['bio'] ?: 'Add a short bio, your interests, and your goals to make your profile more complete.'); ?></p>
                    </div>

                    <div class="bg-white rounded-xl border p-6 shadow">
                        <h3 class="font-semibold mb-4">Skills</h3>
                        <div class="flex flex-wrap gap-2">
                            <?php foreach ($skills as $skill) { ?>
                                <?php $skill = trim($skill); ?>
                                <?php if ($skill != "") { ?>
                                    <span class="px-3 py-1 bg-gray-200 text-sm rounded"><?php echo htmlspecialchars($skill); ?></span>
                                <?php } ?>
                            <?php } ?>
                            <?php if (trim($student['skills'] ?? '') === '') { ?>
                                <span class="text-sm text-gray-500">No skills added yet.</span>
                            <?php } ?>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl border p-6 shadow">
                        <h3 class="font-semibold mb-4">Career Assets</h3>
                        <div class="grid gap-4 md:grid-cols-2 text-sm text-gray-600">
                            <div class="rounded-xl bg-gray-50 p-4">
                                <p class="font-medium text-gray-800">Resume</p>
                                <p class="mt-2"><?php echo htmlspecialchars($student['resume'] ?: 'Resume path not added'); ?></p>
                            </div>
                            <div class="rounded-xl bg-gray-50 p-4">
                                <p class="font-medium text-gray-800">Department</p>
                                <p class="mt-2"><?php echo htmlspecialchars($student['department'] ?: 'Department not added'); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>

</html>

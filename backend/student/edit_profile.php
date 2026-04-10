<?php
include "../config/db.php";
include "../includes/student_auth.php";
include "../includes/flash.php";
include "../includes/student_layout.php";

$current_page = 'profile.php';
$student_page_title = 'Edit Profile';
$student_page_description = 'Update your details, links, and career information.';
$student_badge = 'Profile';
$student_id = (int) $_SESSION['student_id'];

if (isset($_POST['update'])) {
    $phone = mysqli_real_escape_string($conn, trim($_POST['phone']));
    $location = mysqli_real_escape_string($conn, trim($_POST['location']));
    $cgpa = mysqli_real_escape_string($conn, trim($_POST['cgpa']));
    $skills = mysqli_real_escape_string($conn, trim($_POST['skills']));
    $bio = mysqli_real_escape_string($conn, trim($_POST['bio']));
    $department = mysqli_real_escape_string($conn, trim($_POST['department']));
    $graduation_year = mysqli_real_escape_string($conn, trim($_POST['graduation_year']));
    $resume = mysqli_real_escape_string($conn, trim($_POST['resume']));
    $linkedin_url = mysqli_real_escape_string($conn, trim($_POST['linkedin_url']));
    $github_url = mysqli_real_escape_string($conn, trim($_POST['github_url']));

    $query = "
        UPDATE students
        SET phone='$phone',
            location='$location',
            cgpa='$cgpa',
            skills='$skills',
            bio='$bio',
            department='$department',
            graduation_year='$graduation_year',
            resume='$resume',
            linkedin_url='$linkedin_url',
            github_url='$github_url'
        WHERE id='$student_id'
    ";

    mysqli_query($conn, $query);
    set_flash_message('success', 'Profile updated successfully.');
    header("Location: profile.php");
    exit();
}

$query = "SELECT * FROM students WHERE id='$student_id'";
$result = mysqli_query($conn, $query);
$student = mysqli_fetch_assoc($result);

render_student_page_start('Edit Profile');
?>

    <?php include "layout/sidebar.php"; ?>

    <div class="ml-64 min-h-screen flex-1">
        <?php include "layout/topbar.php"; ?>

        <div class="p-8 pt-32">
            <div class="mx-auto max-w-4xl rounded-2xl bg-white p-8 shadow">
                <form method="POST" class="grid grid-cols-1 gap-5 md:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-sm font-semibold">Phone</label>
                        <input type="text" name="phone" value="<?php echo htmlspecialchars($student['phone'] ?? ''); ?>" class="w-full rounded-lg border p-3">
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-semibold">Location</label>
                        <input type="text" name="location" value="<?php echo htmlspecialchars($student['location'] ?? ''); ?>" class="w-full rounded-lg border p-3">
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-semibold">CGPA</label>
                        <input type="text" name="cgpa" value="<?php echo htmlspecialchars($student['cgpa'] ?? ''); ?>" class="w-full rounded-lg border p-3">
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-semibold">Department</label>
                        <input type="text" name="department" value="<?php echo htmlspecialchars($student['department'] ?? ''); ?>" class="w-full rounded-lg border p-3">
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-semibold">Graduation Year</label>
                        <input type="text" name="graduation_year" value="<?php echo htmlspecialchars($student['graduation_year'] ?? ''); ?>" class="w-full rounded-lg border p-3">
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-semibold">Resume Path / URL</label>
                        <input type="text" name="resume" value="<?php echo htmlspecialchars($student['resume'] ?? ''); ?>" class="w-full rounded-lg border p-3">
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-semibold">LinkedIn URL</label>
                        <input type="text" name="linkedin_url" value="<?php echo htmlspecialchars($student['linkedin_url'] ?? ''); ?>" class="w-full rounded-lg border p-3">
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-semibold">GitHub URL</label>
                        <input type="text" name="github_url" value="<?php echo htmlspecialchars($student['github_url'] ?? ''); ?>" class="w-full rounded-lg border p-3">
                    </div>
                    <div class="md:col-span-2">
                        <label class="mb-2 block text-sm font-semibold">Skills</label>
                        <textarea name="skills" class="w-full rounded-lg border p-3" rows="3"><?php echo htmlspecialchars($student['skills'] ?? ''); ?></textarea>
                    </div>
                    <div class="md:col-span-2">
                        <label class="mb-2 block text-sm font-semibold">Bio</label>
                        <textarea name="bio" class="w-full rounded-lg border p-3" rows="5"><?php echo htmlspecialchars($student['bio'] ?? ''); ?></textarea>
                    </div>
                    <div class="md:col-span-2">
                        <button type="submit" name="update" class="rounded-lg bg-blue-600 px-5 py-3 text-sm font-semibold text-white hover:bg-blue-700">Update Profile</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</body>

</html>

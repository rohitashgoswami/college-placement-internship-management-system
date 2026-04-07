<?php
$student_page_title = isset($student_page_title) ? $student_page_title : 'Student Portal';
$student_page_description = isset($student_page_description) ? $student_page_description : 'Track internships, applications, and profile progress.';
$student_badge = isset($student_badge) ? $student_badge : 'Student';
?>
<div class="border-b bg-white px-6 py-4">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900"><?php echo htmlspecialchars($student_page_title); ?></h1>
            <p class="text-sm text-slate-500"><?php echo htmlspecialchars($student_page_description); ?></p>
        </div>

        <div class="flex items-center gap-4">
            <div class="text-right">
                <p class="font-semibold text-slate-900"><?php echo htmlspecialchars($_SESSION['student_name']); ?></p>
                <p class="text-sm text-slate-500"><?php echo htmlspecialchars($student_badge); ?></p>
            </div>

            <div class="flex h-11 w-11 items-center justify-center rounded-full bg-blue-600 text-sm font-bold text-white">
                <?php echo strtoupper(substr($_SESSION['student_name'], 0, 1)); ?>
            </div>
        </div>
    </div>
</div>

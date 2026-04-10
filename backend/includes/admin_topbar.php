<?php
$admin_page_title = isset($admin_page_title) ? $admin_page_title : 'Admin Panel';
$admin_page_description = isset($admin_page_description) ? $admin_page_description : 'Manage the placement portal.';
$current_admin_page = isset($current_admin_page) ? $current_admin_page : '';

$admin_links = array(
    'dashboard.php' => 'Dashboard',
    'view_applications.php' => 'Applications',
    'manage_internships.php' => 'Internships',
    'manage_students.php' => 'Students',
    'reports.php' => 'Reports',
    'change_password.php' => 'Settings'
);
?>
<div class="sticky top-0 z-50 bg-slate-900 text-white shadow-lg">
    <div class="mx-auto flex max-w-7xl flex-col gap-4 px-6 py-5 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <p class="text-sm uppercase tracking-[0.25em] text-slate-400">Admin Module</p>
            <h1 class="mt-1 text-2xl font-bold"><?php echo htmlspecialchars($admin_page_title); ?></h1>
            <p class="mt-1 text-sm text-slate-300"><?php echo htmlspecialchars($admin_page_description); ?></p>
        </div>

        <div class="flex items-center gap-3 text-sm">
            <span class="rounded-full bg-slate-800 px-3 py-2 text-slate-200">
                <?php echo htmlspecialchars($_SESSION['admin_name']); ?>
            </span>
            <a href="logout.php" class="rounded border border-slate-700 px-4 py-2 hover:bg-slate-800">Logout</a>
        </div>
    </div>

    <div class="border-t border-slate-800">
        <div class="mx-auto flex max-w-7xl flex-wrap gap-2 px-6 py-3 text-sm">
            <?php foreach ($admin_links as $file => $label) { ?>
                <a href="<?php echo $file; ?>"
                    class="rounded-full px-4 py-2 transition <?php echo ($current_admin_page === $file) ? 'bg-blue-600 text-white' : 'bg-slate-800 text-slate-200 hover:bg-slate-700'; ?>">
                    <?php echo $label; ?>
                </a>
            <?php } ?>
        </div>
    </div>
</div>

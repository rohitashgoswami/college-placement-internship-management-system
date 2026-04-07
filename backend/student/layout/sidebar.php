<div class="w-64 bg-slate-900 text-white min-h-screen flex flex-col justify-between">

    <div>

        <div class="p-6 border-b border-slate-700">
            <h2 class="text-xl font-bold">PlaceHub</h2>
            <p class="text-sm text-slate-400">Placement Portal</p>
        </div>

        <nav class="p-4 space-y-2">

            <a href="dashboard.php"
                class="block p-3 rounded <?php echo ($current_page == 'dashboard.php') ? 'bg-blue-600' : 'hover:bg-blue-600'; ?>">
                Dashboard
            </a>

            <a href="job_board.php"
                class="block p-3 rounded <?php echo ($current_page == 'job_board.php') ? 'bg-blue-600' : 'hover:bg-blue-600'; ?>">
                Job Board
            </a>

            <a href="my_applications.php"
                class="block p-3 rounded <?php echo ($current_page == 'my_applications.php') ? 'bg-blue-600' : 'hover:bg-blue-600'; ?>">
                My Applications
            </a>

            <a href="profile.php"
                class="block p-3 rounded <?php echo ($current_page == 'profile.php') ? 'bg-blue-600' : 'hover:bg-blue-600'; ?>">
                Profile
            </a>

            <a href="saved_jobs.php"
                class="block p-3 rounded <?php echo ($current_page == 'saved_jobs.php') ? 'bg-blue-600' : 'hover:bg-blue-600'; ?>">
                Saved Jobs
            </a>

            <a href="settings.php"
                class="block p-3 rounded <?php echo ($current_page == 'settings.php') ? 'bg-blue-600' : 'hover:bg-blue-600'; ?>">
                Settings
            </a>

        </nav>

    </div>

    <div class="p-4 border-t border-slate-700">
        <a href="logout.php" class="block p-3 hover:bg-red-500 rounded">
            Sign Out
        </a>
    </div>

</div>

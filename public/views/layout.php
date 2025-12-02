<?php
// Basic layout wrapper
?><!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <title>CRM App</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/tailwind.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script defer src="/assets/js/apiClient.js"></script>
    <script defer src="/assets/js/clientProfile.js"></script>
    <script defer src="/assets/js/ui.js"></script>
    <script defer src="/assets/js/app.js"></script>
</head>
<body class="min-h-screen bg-slate-50 text-text-primary font-sans dark:bg-dark.page dark:text-dark.textPrimary">
    <div class="flex min-h-screen relative">
        <?php $isLogin = ($page ?? '') === 'login'; ?>
        <?php if (!$isLogin): ?>
            <aside id="sidebar" class="w-64 bg-white/90 backdrop-blur border-r border-slate-200 hidden sm:block dark:bg-dark.surface dark:border-dark.border">
                <div class="p-4 font-bold text-lg tracking-tight flex items-center gap-2">
                    <div class="h-9 w-9 rounded-2xl bg-gradient-to-br from-indigo-500 to-blue-400 flex items-center justify-center text-white shadow-sm">C</div>
                    <span class="text-slate-800">CRM</span>
                </div>
                <nav class="p-3 space-y-1 text-sm">
                    <?php
                    $currentPage = $page ?? '';
                    $links = [
                        'dashboard' => ['label' => 'Dashboard', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />'],
                        'leads' => ['label' => 'Leads', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5 5 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />'],
                        'contacts' => ['label' => 'Contacts', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />'],
                        'client-profile' => ['label' => 'Client Profile', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 11a3 3 0 10-6 0 3 3 0 006 0z" />'],
                        'deals' => ['label' => 'Deals', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 10h11M9 21V3m0 18l5-5m-5 5l-5-5" />'],
                        'tasks' => ['label' => 'Tasks', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m-7 4h8a2 2 0 002-2V6a2 2 0 00-2-2H7a2 2 0 00-2 2v12a2 2 0 002 2zM9 8h6" />'],
                        'reports' => ['label' => 'Reports', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-6a2 2 0 012-2h4m4 0h-4m4 0v6m-4-6V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2h6" />'],
                        'ai-assistant' => ['label' => 'AI Assistant', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2" />'],
                    ];
                    foreach ($links as $key => $meta):
                        $active = $currentPage === $key;
                        $classes = $active
                            ? 'bg-indigo-50 text-indigo-700 border border-indigo-100'
                            : 'text-slate-600 hover:bg-slate-50 border border-transparent';
                    ?>
                    <a data-nav="<?php echo $key; ?>" class="flex items-center gap-3 px-3 py-2 rounded-xl transition <?php echo $classes; ?>" href="/index.php?page=<?php echo $key; ?>">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><?php echo $meta['icon']; ?></svg>
                        <span class="text-sm font-medium"><?php echo $meta['label']; ?></span>
                    </a>
                    <?php endforeach; ?>
                </nav>
            </aside>

            <div id="mobileOverlay" class="fixed inset-0 bg-black/30 z-20 hidden sm:hidden"></div>
        <?php endif; ?>

        <main class="flex-1">
            <?php if (!$isLogin): ?>
                <header class="p-4 bg-surface border-b border-border flex items-center justify-between sticky top-0 z-10 dark:bg-dark.surface dark:border-dark.border">
                    <div class="flex items-center gap-3">
                        <button id="mobileMenuBtn" class="sm:hidden px-3 py-2 border rounded text-sm">Menu</button>
                        <div class="font-bold text-lg tracking-tight">CRM</div>
                    </div>
                    <div class="flex items-center gap-3 text-sm text-gray-600">
                        <a href="/index.php?page=profile" class="h-10 w-10 rounded-full bg-gradient-to-br from-indigo-500 to-blue-500 text-white flex items-center justify-center font-semibold shadow-sm hover:shadow-md transition" title="Profile">
                            <span id="userAvatarInitials">P</span>
                        </a>
                        <span id="userEmailText" class="hidden sm:inline"></span>
                        <button id="logoutBtnTop" class="px-3 py-1 border rounded hover:bg-gray-100">Logout</button>
                    </div>
                </header>
            <?php endif; ?>
            <div class="<?php echo $isLogin ? 'p-0' : 'p-4'; ?>">
                <?php echo $content; ?>
            </div>
        </main>
    </div>
</body>
</html>

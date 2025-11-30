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
<body class="min-h-screen bg-page text-text-primary font-sans dark:bg-dark.page dark:text-dark.textPrimary">
    <div class="flex min-h-screen relative">
        <aside id="sidebar" class="w-64 bg-surface border-r border-border hidden sm:block dark:bg-dark.surface dark:border-dark.border">
            <div class="p-4 font-bold text-lg tracking-tight">CRM</div>
            <nav class="p-2 space-y-1">
                <?php
                $currentPage = $page ?? '';
                $links = [
                    'dashboard' => 'Dashboard',
                    'leads' => 'Leads',
                    'contacts' => 'Contacts',
                    'client-profile' => 'Client Profile',
                    'deals' => 'Deals',
                    'tasks' => 'Tasks',
                    'reports' => 'Reports',
                    'ai-assistant' => 'AI Assistant',
                ];
                foreach ($links as $key => $label):
                    $active = $currentPage === $key;
                    $classes = $active
                        ? 'bg-accent/10 text-accent font-semibold'
                        : 'text-text-secondary hover:bg-accent/10';
                ?>
                <a data-nav="<?php echo $key; ?>" class="block px-4 py-2 rounded transition <?php echo $classes; ?>" href="/index.php?page=<?php echo $key; ?>"><?php echo $label; ?></a>
                <?php endforeach; ?>
                <button id="logoutBtn" class="w-full text-left px-4 py-2 rounded hover:bg-accent/10 text-text-secondary">Logout</button>
            </nav>
        </aside>

        <div id="mobileOverlay" class="fixed inset-0 bg-black/30 z-20 hidden sm:hidden"></div>

        <main class="flex-1">
            <header class="p-4 bg-surface border-b border-border flex items-center justify-between sticky top-0 z-10 dark:bg-dark.surface dark:border-dark.border">
                <div class="flex items-center gap-3">
                    <button id="mobileMenuBtn" class="sm:hidden px-3 py-2 border rounded text-sm">Menu</button>
                    <div class="font-bold text-lg tracking-tight">CRM</div>
                </div>
                <div class="flex items-center gap-3 text-sm text-gray-600">
                    <span id="userEmailText" class="hidden sm:inline"></span>
                    <button id="logoutBtnTop" class="px-3 py-1 border rounded hover:bg-gray-100">Logout</button>
                </div>
            </header>
            <div class="p-4">
                <?php echo $content; ?>
            </div>
        </main>
    </div>
</body>
</html>

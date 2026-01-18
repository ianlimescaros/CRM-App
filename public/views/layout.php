<?php
// Basic layout wrapper
$configPath = __DIR__ . '/../../src/config/config.php';
$appEnv = 'production';
$appDebug = false;
if (is_file($configPath)) {
    $config = require $configPath;
    $appEnv = $config['app']['env'] ?? $appEnv;
    $appDebug = $config['app']['debug'] ?? $appDebug;
}

$staticVersion = '2025-01-01-12'; // bump this when you deploy
$forceReload = array_key_exists('_reloaded', $_GET);
$useDynamicVersion = $forceReload || $appEnv === 'local' || $appDebug;
$appVersion = $useDynamicVersion ? (string) time() : $staticVersion;
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <title>My CRM</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/tailwind.css?v=<?php echo htmlspecialchars($appVersion, ENT_QUOTES); ?>">
    <style>
        /* Sidebar collapse small helper */
        .sidebar-collapsed #sidebar { width: 72px !important; }
        .sidebar-collapsed #sidebar nav a span { display: none !important; }
        .sidebar-collapsed #sidebar nav a svg { margin-right: 0.25rem; }

        /* Typography & spacing tweaks */
        html { font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', Arial; font-size:15px; }
        body { line-height: 1.45; font-size: 15px; }
        h1 { font-size: 18px; }
        h2 { font-size: 16px; }
        h3 { font-size: 14px; }
        /* Tighter header & sidebar spacing for higher density */
        header { padding: 0.6rem 1rem; }
        #sidebar { padding-top: 0.5rem; }
        #sidebar nav a { padding: .5rem .75rem; }

        /* Show clear focus outlines for keyboard users */
        :focus { outline: 3px solid rgba(59,130,246,0.25); outline-offset: 2px; }

        /* Smooth micro-interactions */
        .transition-smooth { transition: transform .12s ease, box-shadow .12s ease, background-color .12s ease; }
        .hover-elevate:hover { transform: translateY(-2px); box-shadow: 0 6px 18px rgba(2,6,23,0.08); }

        /* List density */
        .list-dense td { padding: .45rem .75rem; }

        /* Skeleton */
        .skeleton { background: linear-gradient(90deg, rgba(0,0,0,0.04) 0%, rgba(0,0,0,0.06) 50%, rgba(0,0,0,0.04) 100%); background-size: 200% 100%; animation: shimmer 1.2s linear infinite; }
        @keyframes shimmer { from { background-position: 200% 0; } to { background-position: -200% 0; } }

        /* Cards / list items */
        .list-item-card { border: 1px solid rgba(15,23,42,0.06); border-radius: 10px; background: #fff; }

        /* Status badges with accessible contrast (WCAG AA) */
        .status-badge { display:inline-flex; align-items:center; padding:.125rem .5rem; border-radius:.5rem; font-weight:600; font-size:12px; }
        .status-new { background:#3730a3; color:#fff; } /* indigo-800 */
        .status-contacted { background:#0369a1; color:#fff; } /* stronger sky for contrast */
        .status-qualified { background:#059669; color:#fff; } /* emerald-600 */
        .status-not_qualified { background:#d97706; color:#fff; } /* amber darker for contrast */

        /* Toast improvements */
        .toast { opacity:0; transform: translateY(-6px); transition: opacity .18s ease, transform .18s ease; }
        .toast.show { opacity:1; transform: translateY(0); }

    </style>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script defer src="/assets/js/apiClient.js?v=<?php echo htmlspecialchars($appVersion, ENT_QUOTES); ?>"></script>
    <script defer src="/assets/js/ui.js?v=<?php echo htmlspecialchars($appVersion, ENT_QUOTES); ?>"></script>
    <script defer src="/assets/js/common.js?v=<?php echo htmlspecialchars($appVersion, ENT_QUOTES); ?>"></script>
    <script defer src="/assets/js/globalSearch.js?v=<?php echo htmlspecialchars($appVersion, ENT_QUOTES); ?>"></script>
    <?php if (($page ?? '') === 'login'): ?>
        <script defer src="/assets/js/auth.js?v=<?php echo htmlspecialchars($appVersion, ENT_QUOTES); ?>"></script>
    <?php endif; ?>
    <?php if (($page ?? '') === 'leads'): ?>
        <script defer src="/assets/js/leads.js?v=<?php echo htmlspecialchars($appVersion, ENT_QUOTES); ?>"></script>
    <?php endif; ?>
    <?php if (($page ?? '') === 'contacts'): ?>
        <script defer src="/assets/js/clients.js?v=<?php echo htmlspecialchars($appVersion, ENT_QUOTES); ?>"></script>
    <?php endif; ?>
    <?php if (($page ?? '') === 'deals'): ?>
        <script defer src="/assets/js/deals.js?v=<?php echo htmlspecialchars($appVersion, ENT_QUOTES); ?>"></script>
    <?php endif; ?>
    <?php if (($page ?? '') === 'tasks'): ?>
        <script defer src="/assets/js/tasks.js?v=<?php echo htmlspecialchars($appVersion, ENT_QUOTES); ?>"></script>
    <?php endif; ?>
    <?php if (($page ?? '') === 'reports'): ?>
        <script defer src="/assets/js/reports.js?v=<?php echo htmlspecialchars($appVersion, ENT_QUOTES); ?>"></script>
    <?php endif; ?>
    <?php if (($page ?? '') === 'dashboard'): ?>
        <script defer src="/assets/js/dashboard.js?v=<?php echo htmlspecialchars($appVersion, ENT_QUOTES); ?>"></script>
    <?php endif; ?>
    <?php if (($page ?? '') === 'ai-assistant'): ?>
        <script defer src="/assets/js/ai.js?v=<?php echo htmlspecialchars($appVersion, ENT_QUOTES); ?>"></script>
    <?php endif; ?>
    <?php if (($page ?? '') === 'profile'): ?>
        <script defer src="/assets/js/profile.js?v=<?php echo htmlspecialchars($appVersion, ENT_QUOTES); ?>"></script>
    <?php endif; ?>
    <?php if (($page ?? '') === 'client-profile'): ?>
        <script defer src="/assets/js/clientProfile.js?v=<?php echo htmlspecialchars($appVersion, ENT_QUOTES); ?>"></script>
    <?php endif; ?>
    <?php if (($page ?? '') === 'tenancy-contracts'): ?>
        <script defer src="/assets/js/tenancyContracts.js?v=<?php echo htmlspecialchars($appVersion, ENT_QUOTES); ?>"></script>
    <?php endif; ?>
    <?php if (($page ?? '') === 'noc-leasing'): ?>
        <script defer src="/assets/js/nocLeasing.js?v=<?php echo htmlspecialchars($appVersion, ENT_QUOTES); ?>"></script>
    <?php endif; ?>
    <script defer src="/assets/js/errorHandler.js?v=<?php echo htmlspecialchars($appVersion, ENT_QUOTES); ?>"></script>
    <script defer src="/assets/js/app.js?v=<?php echo htmlspecialchars($appVersion, ENT_QUOTES); ?>"></script>
</head>

<body class="min-h-screen bg-slate-50 text-text-primary font-sans">
    <div class="flex min-h-screen relative">
        <?php $isLogin = ($page ?? '') === 'login'; ?>
        <?php if (!$isLogin): ?>
            <aside id="sidebar" class="w-64 bg-white/90 backdrop-blur border-r border-slate-200 hidden sm:block">
                <div class="p-4 font-bold text-lg tracking-tight flex items-center gap-2">
                    <div class="h-9 w-9 rounded-2xl bg-gradient-to-br from-indigo-500 to-blue-400 flex items-center justify-center text-white shadow-sm">M</div>
                    <span class="text-slate-800">My CRM</span>
                </div>
                <nav class="p-3 space-y-1 text-sm">
                    <?php
                    $currentPage = $page ?? '';
                    $links = [
                        'dashboard' => ['label' => 'Dashboard', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />'],
                        'leads' => ['label' => 'Leads', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5 5 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />'],
                        'contacts' => ['label' => 'Clients/Landlords', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />'],
                        'client-profile' => ['label' => 'Client Profile', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 11a3 3 0 10-6 0 3 3 0 006 0z" />'],
                        'deals' => ['label' => 'Deals', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 10h11M9 21V3m0 18l5-5m-5 5l-5-5" />'],
                        'tenancy-contracts' => ['label' => 'Tenancy Contract/Record', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M7 7h10M7 11h7M9 15h5m-9 4h14a2 2 0 002-2V7a2 2 0 00-2-2h-3l-2-2H9L7 5H4a2 2 0 00-2 2v10a2 2 0 002 2z" />'],
                        'noc-leasing' => ['label' => 'NOC / Leasing Form', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6M7 8h10M5 4h14a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2V6a2 2 0 012-2z" />'],
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
                    <a data-nav="<?php echo htmlspecialchars($key, ENT_QUOTES, 'UTF-8'); ?>" class="flex items-center gap-3 px-3 py-2 rounded-xl transition <?php echo $classes; ?>" href="/index.php?page=<?php echo rawurlencode($key); ?>">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><?php echo $meta['icon']; ?></svg>
                        <span class="text-sm font-medium"><?php echo htmlspecialchars($meta['label'], ENT_QUOTES, 'UTF-8'); ?></span>
                    </a>
                    <?php endforeach; ?>
                </nav>
            </aside>

            <div id="mobileOverlay" class="fixed inset-0 bg-black/30 z-20 hidden sm:hidden"></div>
        <?php endif; ?>

        <main class="flex-1">
            <?php if (!$isLogin): ?>
                <header class="p-4 bg-surface border-b border-border flex items-center justify-between sticky top-0 z-10">
                    <div class="flex items-center gap-3">
                        <button id="mobileMenuBtn" class="sm:hidden px-3 py-2 border rounded text-sm">Menu</button>
                        <button id="sidebarCollapseBtn" title="Collapse sidebar" class="hidden sm:inline-flex items-center px-3 py-2 border rounded text-sm">Toggle sidebar</button>
                        <div class="font-bold text-lg tracking-tight">CRM</div>
                        <input id="globalSearch" placeholder="Search leads, clients..." class="ml-4 hidden sm:inline-block px-2 py-1 rounded-md border border-gray-200 text-sm w-64" />
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
    <div id="successModal" class="fixed inset-0 bg-gray-800/50 z-50 items-center justify-center flex hidden">
        <div class="bg-white p-6 rounded shadow-md text-center">
            <h3 class="text-lg font-semibold">Page Reloaded Successfully</h3>
            <button onclick="closeModal()" class="mt-4 px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Close</button>
        </div>
    </div>
    <script src="/assets/js/reload.js?v=<?php echo htmlspecialchars($appVersion, ENT_QUOTES); ?>"></script>
</body>
</html>

<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$user = getCurrentUser();
$emails = getEmails($user['id']);
$unreadCount = getUnreadCount($user['id']);
$draftCount = getDraftCount($user['id']);
$systemStats = getSystemStats();
$auditLogs = getAuditLogs(5);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MailFlow - Admin Dashboard</title>
    <style>
        @import url(https://fonts.googleapis.com/css2?family=Lato&display=swap);
        @import url(https://fonts.googleapis.com/css2?family=Open+Sans&display=swap);
        @import url(https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined);
    </style>
</head>
<body>
<div id="webcrumbs">
    <div class="flex h-screen bg-gray-50">
        <!-- Sidebar -->
        <div class="hidden md:flex md:w-64 lg:w-72 flex-col bg-white border-r border-gray-200 shadow-sm">
            <div class="flex items-center justify-center h-16 border-b border-gray-200">
                <h1 class="text-xl font-semibold text-primary-600 tracking-tight">MailFlow</h1>
            </div>
            
            <div class="flex flex-col flex-grow overflow-y-auto">
                <div class="p-4">
                    <button onclick="openCompose()" class="w-full bg-primary-500 hover:bg-primary-600 text-white rounded-md py-2 px-4 flex items-center justify-center transition duration-200 shadow-sm">
                        <span class="material-symbols-outlined mr-2">edit</span>
                        Compose
                    </button>
                </div>
                
                <nav class="mt-1">
                    <ul>
                        <li class="px-4 py-2.5 flex items-center text-gray-800 bg-primary-50 border-l-4 border-primary-500 font-medium">
                            <span class="material-symbols-outlined mr-3">inbox</span>
                            Inbox
                            <span class="ml-auto bg-primary-500 text-white text-xs font-medium px-2 py-0.5 rounded-full"><?php echo $unreadCount; ?></span>
                        </li>
                        <li class="px-4 py-2.5 flex items-center text-gray-600 hover:bg-gray-50 transition duration-200 cursor-pointer" onclick="loadFolder('sent')">
                            <span class="material-symbols-outlined mr-3">send</span>
                            Sent
                        </li>
                        <li class="px-4 py-2.5 flex items-center text-gray-600 hover:bg-gray-50 transition duration-200 cursor-pointer" onclick="loadFolder('drafts')">
                            <span class="material-symbols-outlined mr-3">landscape</span>
                            Drafts
                            <span class="ml-auto bg-gray-200 text-gray-700 text-xs font-medium px-2 py-0.5 rounded-full"><?php echo $draftCount; ?></span>
                        </li>
                        <li class="px-4 py-2.5 flex items-center text-gray-600 hover:bg-gray-50 transition duration-200 cursor-pointer" onclick="loadFolder('spam')">
                            <span class="material-symbols-outlined mr-3">report</span>
                            Spam
                        </li>
                        <li class="px-4 py-2.5 flex items-center text-gray-600 hover:bg-gray-50 transition duration-200 cursor-pointer" onclick="loadFolder('trash')">
                            <span class="material-symbols-outlined mr-3">delete</span>
                            Trash
                        </li>
                    </ul>
                </nav>
                
                <div class="mt-4 px-4">
                    <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3 px-2">Labels</h3>
                    <ul>
                        <li class="px-2 py-1.5 flex items-center text-gray-600 hover:bg-gray-50 rounded-md transition duration-200 cursor-pointer" onclick="filterByLabel('work')">
                            <span class="w-2.5 h-2.5 rounded-full bg-green-500 mr-3"></span>
                            Work
                        </li>
                        <li class="px-2 py-1.5 flex items-center text-gray-600 hover:bg-gray-50 rounded-md transition duration-200 cursor-pointer" onclick="filterByLabel('personal')">
                            <span class="w-2.5 h-2.5 rounded-full bg-blue-500 mr-3"></span>
                            Personal
                        </li>
                        <li class="px-2 py-1.5 flex items-center text-gray-600 hover:bg-gray-50 rounded-md transition duration-200 cursor-pointer" onclick="filterByLabel('important')">
                            <span class="w-2.5 h-2.5 rounded-full bg-yellow-500 mr-3"></span>
                            Important
                        </li>
                        <li class="px-2 py-1.5 flex items-center text-gray-600 hover:bg-gray-50 rounded-md transition duration-200 cursor-pointer" onclick="filterByLabel('projects')">
                            <span class="w-2.5 h-2.5 rounded-full bg-purple-500 mr-3"></span>
                            Projects
                        </li>
                    </ul>
                </div>
            </div>
            
            <div class="p-4 border-t border-gray-200 bg-gray-50">
                <div class="flex items-center">
                    <div class="w-9 h-9 rounded-full bg-primary-100 flex items-center justify-center text-primary-700">
                        <span class="material-symbols-outlined text-sm">admin_panel_settings</span>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-800 leading-tight"><?php echo htmlspecialchars($user['name']); ?></p>
                        <p class="text-xs text-gray-500 mt-0.5"><?php echo htmlspecialchars($user['role']); ?></p>
                    </div>
                    <details class="ml-auto relative">
                        <summary class="list-none cursor-pointer">
                            <span class="material-symbols-outlined text-gray-500 hover:text-gray-700">more_vert</span>
                        </summary>
                        <div class="absolute bottom-full right-0 mb-2 w-48 bg-white rounded-md shadow-lg py-1 z-10 border border-gray-100">
                            <a href="profile.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition duration-150">Profile Settings</a>
                            <?php if ($user['role'] === 'Administrator'): ?>
                            <a href="admin.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition duration-150">Admin Dashboard</a>
                            <?php endif; ?>
                            <a href="logout.php" class="block px-4 py-2 text-sm text-red-600 hover:bg-gray-50 transition duration-150">Logout</a>
                        </div>
                    </details>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Header -->
            <header class="bg-white border-b border-gray-200 shadow-xs">
                <div class="flex items-center justify-between h-16 px-4">
                    <div class="flex items-center">
                        <button class="md:hidden p-2 rounded-md text-gray-500 hover:bg-gray-50 transition duration-200">
                            <span class="material-symbols-outlined">menu</span>
                        </button>
                        <div class="relative ml-4 md:ml-0 flex-grow max-w-2xl">
                            <form method="GET" action="search.php">
                                <input type="text" name="q" placeholder="Search emails..." 
                                       value="<?php echo isset($_GET['q']) ? htmlspecialchars($_GET['q']) : ''; ?>"
                                       class="w-full h-10 pl-10 pr-4 py-2 rounded-md bg-gray-50 border border-gray-200 focus:outline-none focus:ring-1 focus:ring-primary-500 focus:border-transparent transition duration-150"/>
                                <span class="material-symbols-outlined absolute left-3 top-2.5 text-gray-500">search</span>
                            </form>
                        </div>
                    </div>
                    <div class="flex items-center space-x-4">
                        <button class="p-2 rounded-md text-gray-500 hover:bg-gray-50 transition duration-200 relative">
                            <span class="material-symbols-outlined">help_outline</span>
                        </button>
                        <button class="p-2 rounded-md text-gray-500 hover:bg-gray-50 transition duration-200 relative">
                            <span class="material-symbols-outlined">settings</span>
                        </button>
                        <button class="p-2 rounded-md text-gray-500 hover:bg-gray-50 transition duration-200 relative">
                            <span class="material-symbols-outlined">notifications</span>
                            <?php if (hasUnreadNotifications($user['id'])): ?>
                            <span class="absolute top-1 right-1 w-1.5 h-1.5 bg-red-500 rounded-full"></span>
                            <?php endif; ?>
                        </button>
                    </div>
                </div>
                
                <div class="flex items-center px-4 py-2 border-b border-gray-200 bg-white">
                    <div class="flex items-center space-x-4 overflow-x-auto scrollbar-hide">
                        <button class="px-3 py-1 rounded-md bg-primary-50 text-primary-700 font-medium text-sm" onclick="filterByCategory('primary')">Primary</button>
                        <button class="px-3 py-1 rounded-md hover:bg-gray-50 text-gray-600 font-medium text-sm transition duration-200" onclick="filterByCategory('social')">Social</button>
                        <button class="px-3 py-1 rounded-md hover:bg-gray-50 text-gray-600 font-medium text-sm transition duration-200" onclick="filterByCategory('promotions')">Promotions</button>
                        <button class="px-3 py-1 rounded-md hover:bg-gray-50 text-gray-600 font-medium text-sm transition duration-200" onclick="filterByCategory('updates')">Updates</button>
                        <button class="px-3 py-1 rounded-md hover:bg-gray-50 text-gray-600 font-medium text-sm transition duration-200" onclick="filterByCategory('forums')">Forums</button>
                    </div>
                </div>
            </header>

            <!-- Email List -->
            <div class="flex-1 overflow-y-auto bg-white">
                <div class="flex items-center justify-between px-4 py-2 border-b border-gray-200 bg-gray-50">
                    <div class="flex items-center">
                        <input type="checkbox" id="selectAll" class="h-4 w-4 text-primary-600 rounded border-gray-300 focus:ring-1 focus:ring-primary-500"/>
                        <button onclick="refreshEmails()" class="ml-4 p-1 rounded text-gray-500 hover:bg-gray-100 transition duration-150">
                            <span class="material-symbols-outlined text-sm">refresh</span>
                        </button>
                        <button class="ml-1 p-1 rounded text-gray-500 hover:bg-gray-100 transition duration-150">
                            <span class="material-symbols-outlined text-sm">more_vert</span>
                        </button>
                    </div>
                    <div class="flex items-center text-sm text-gray-500">
                        <span>1-<?php echo min(50, count($emails)); ?> of <?php echo count($emails); ?></span>
                        <button class="ml-4 p-1 rounded text-gray-500 hover:bg-gray-100 transition duration-150">
                            <span class="material-symbols-outlined text-sm">chevron_left</span>
                        </button>
                        <button class="p-1 rounded text-gray-500 hover:bg-gray-100 transition duration-150">
                            <span class="material-symbols-outlined text-sm">chevron_right</span>
                        </button>
                    </div>
                </div>
                
                <div class="divide-y divide-gray-200" id="emailList">
                    <?php foreach ($emails as $email): ?>
                    <div class="flex px-4 py-4 hover:bg-gray-50 cursor-pointer transition duration-150 <?php echo !$email['is_read'] ? 'bg-primary-50' : ''; ?>" 
                         onclick="openEmail(<?php echo $email['id']; ?>)">
                        <div class="flex items-center mr-4">
                            <input type="checkbox" class="h-4 w-4 text-primary-600 rounded border-gray-300 focus:ring-1 focus:ring-primary-500" 
                                   onclick="event.stopPropagation()"/>
                            <button onclick="toggleStar(<?php echo $email['id']; ?>); event.stopPropagation();" 
                                    class="ml-2 <?php echo $email['is_starred'] ? 'text-yellow-500' : 'text-gray-400'; ?> hover:text-yellow-600">
                                <span class="material-symbols-outlined">star</span>
                            </button>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between mb-1">
                                <h3 class="text-sm <?php echo !$email['is_read'] ? 'font-bold' : 'font-medium'; ?> text-gray-900 truncate">
                                    <?php echo htmlspecialchars($email['sender_name']); ?>
                                </h3>
                                <span class="text-xs text-gray-500 font-medium">
                                    <?php echo formatEmailDate($email['created_at']); ?>
                                </span>
                            </div>
                            <h4 class="text-sm <?php echo !$email['is_read'] ? 'font-semibold' : 'font-medium'; ?> text-gray-800 truncate mb-1">
                                <?php echo htmlspecialchars($email['subject']); ?>
                            </h4>
                            <p class="text-sm text-gray-600 truncate leading-5">
                                <?php echo htmlspecialchars(substr(strip_tags($email['body']), 0, 100)) . '...'; ?>
                            </p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Right Sidebar - Admin Controls -->
        <?php if ($user['role'] === 'Administrator'): ?>
        <div class="hidden lg:flex lg:w-80 flex-col bg-white border-l border-gray-200 shadow-sm">
            <div class="flex items-center justify-between h-16 px-6 border-b border-gray-200 bg-gray-50">
                <h2 class="text-base font-semibold text-gray-800">Admin Controls</h2>
                <button class="p-1 rounded-md text-gray-500 hover:bg-gray-200 transition duration-200">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            
            <div class="flex-1 overflow-y-auto p-5">
                <!-- System Status -->
                <div class="mb-6">
                    <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">System Status</h3>
                    <div class="bg-white rounded-md p-4 border border-gray-200 shadow-sm">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-sm text-gray-700">Server Status</span>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-50 text-green-700">
                                <span class="h-1.5 w-1.5 rounded-full bg-green-500 mr-1.5 animate-pulse"></span>
                                <?php echo $systemStats['server_status']; ?>
                            </span>
                        </div>
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-sm text-gray-700">Mail Queue</span>
                            <span class="text-sm font-medium text-gray-700"><?php echo $systemStats['mail_queue']; ?> pending</span>
                        </div>
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-sm text-gray-700">Active Users</span>
                            <span class="text-sm font-medium text-gray-700"><?php echo $systemStats['active_users']; ?>/<?php echo $systemStats['total_users']; ?></span>
                        </div>
                        <div class="mt-3">
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-xs text-gray-600">Storage Usage</span>
                                <span class="text-xs font-medium text-gray-700"><?php echo $systemStats['storage_usage']; ?>%</span>
                            </div>
                            <div class="w-full bg-gray-100 rounded-full h-1.5">
                                <div class="bg-primary-500 h-1.5 rounded-full" style="width:<?php echo $systemStats['storage_usage']; ?>%"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="mb-6">
                    <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Quick Actions</h3>
                    <div class="grid grid-cols-2 gap-3">
                        <button onclick="openModal('addUser')" class="flex flex-col items-center justify-center p-3 bg-white border border-gray-200 rounded-md hover:bg-gray-50 hover:border-primary-200 transition duration-200 shadow-sm group">
                            <span class="material-symbols-outlined text-primary-600 mb-2 group-hover:scale-110 transition-transform">person_add</span>
                            <span class="text-xs font-medium text-gray-700">Add User</span>
                        </button>
                        <button onclick="window.location.href='admin/users.php'" class="flex flex-col items-center justify-center p-3 bg-white border border-gray-200 rounded-md hover:bg-gray-50 hover:border-primary-200 transition duration-200 shadow-sm group">
                            <span class="material-symbols-outlined text-primary-600 mb-2 group-hover:scale-110 transition-transform">group</span>
                            <span class="text-xs font-medium text-gray-700">Manage Users</span>
                        </button>
                        <button onclick="window.location.href='admin/security.php'" class="flex flex-col items-center justify-center p-3 bg-white border border-gray-200 rounded-md hover:bg-gray-50 hover:border-primary-200 transition duration-200 shadow-sm group">
                            <span class="material-symbols-outlined text-primary-600 mb-2 group-hover:scale-110 transition-transform">security</span>
                            <span class="text-xs font-medium text-gray-700">Security</span>
                        </button>
                        <button onclick="window.location.href='admin/reports.php'" class="flex flex-col items-center justify-center p-3 bg-white border border-gray-200 rounded-md hover:bg-gray-50 hover:border-primary-200 transition duration-200 shadow-sm group">
                            <span class="material-symbols-outlined text-primary-600 mb-2 group-hover:scale-110 transition-transform">analytics</span>
                            <span class="text-xs font-medium text-gray-700">Reports</span>
                        </button>
                    </div>
                </div>

                <!-- Audit Log -->
                <div class="mb-6">
                    <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Audit Log</h3>
                    <div class="bg-white rounded-md border border-gray-200 shadow-sm overflow-hidden">
                        <div class="px-4 py-3 bg-gray-50 border-b border-gray-200 flex items-center justify-between">
                            <span class="text-xs font-medium text-gray-700">Recent Activity</span>
                            <button onclick="window.location.href='admin/audit.php'" class="text-xs text-primary-600 hover:text-primary-700 transition duration-150">View All</button>
                        </div>
                        <div class="divide-y divide-gray-100">
                            <?php foreach ($auditLogs as $log): ?>
                            <div class="px-4 py-3 hover:bg-gray-50 transition duration-150">
                                <div class="flex items-start">
                                    <span class="material-symbols-outlined p-1 bg-<?php echo $log['color']; ?>-50 text-<?php echo $log['color']; ?>-600 rounded-full text-sm"><?php echo $log['icon']; ?></span>
                                    <div class="ml-2.5">
                                        <p class="text-xs font-medium text-gray-700"><?php echo htmlspecialchars($log['action']); ?></p>
                                        <div class="flex items-center mt-0.5">
                                            <p class="text-xs text-gray-500"><?php echo timeAgo($log['created_at']); ?></p>
                                            <span class="mx-1 text-gray-300">•</span>
                                            <p class="text-xs text-gray-500"><?php echo htmlspecialchars($log['user_email']); ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.tailwindcss.com"></script>
<script>
    tailwind.config = {
        content: ["./src/**/*.{html,js}"],
        theme: {"name":"Bluewave","fontFamily":{"sans":["Open Sans","ui-sans-serif","system-ui","sans-serif","\"Apple Color Emoji\"","\"Segoe UI Emoji\"","\"Segoe UI Symbol\"","\"Noto Color Emoji\""]},"extend":{"fontFamily":{"title":["Lato","ui-sans-serif","system-ui","sans-serif","\"Apple Color Emoji\"","\"Segoe UI Emoji\"","\"Segoe UI Symbol\"","\"Noto Color Emoji\""],"body":["Open Sans","ui-sans-serif","system-ui","sans-serif","\"Apple Color Emoji\"","\"Segoe UI Emoji\"","\"Segoe UI Symbol\"","\"Noto Color Emoji\""]},"colors":{"neutral":{"50":"#f7f7f7","100":"#eeeeee","200":"#e0e0e0","300":"#cacaca","400":"#b1b1b1","500":"#999999","600":"#7f7f7f","700":"#676767","800":"#545454","900":"#464646","950":"#282828"},"primary":{"50":"#f3f1ff","100":"#e9e5ff","200":"#d5cfff","300":"#b7a9ff","400":"#9478ff","500":"#7341ff","600":"#631bff","700":"#611bf8","800":"#4607d0","900":"#3c08aa","950":"#220174","DEFAULT":"#611bf8"}}},"fontSize":{"xs":["12px",{"lineHeight":"19.200000000000003px"}],"sm":["14px",{"lineHeight":"21px"}],"base":["16px",{"lineHeight":"25.6px"}],"lg":["18px",{"lineHeight":"27px"}],"xl":["20px",{"lineHeight":"28px"}],"2xl":["24px",{"lineHeight":"31.200000000000003px"}],"3xl":["30px",{"lineHeight":"36px"}],"4xl":["36px",{"lineHeight":"41.4px"}],"5xl":["48px",{"lineHeight":"52.800000000000004px"}],"6xl":["60px",{"lineHeight":"66px"}],"7xl":["72px",{"lineHeight":"75.60000000000001px"}],"8xl":["96px",{"lineHeight":"100.80000000000001px"}],"9xl":["128px",{"lineHeight":"134.4px"}]},"borderRadius":{"none":"0px","sm":"6px","DEFAULT":"12px","md":"18px","lg":"24px","xl":"36px","2xl":"48px","3xl":"72px","full":"9999px"},"spacing":{"0":"0px","1":"4px","2":"8px","3":"12px","4":"16px","5":"20px","6":"24px","7":"28px","8":"32px","9":"36px","10":"40px","11":"44px","12":"48px","14":"56px","16":"64px","20":"80px","24":"96px","28":"112px","32":"128px","36":"144px","40":"160px","44":"176px","48":"192px","52":"208px","56":"224px","60":"240px","64":"256px","72":"288px","80":"320px","96":"384px","px":"1px","0.5":"2px","1.5":"6px","2.5":"10px","3.5":"14px"}},
        plugins: [],
        important: '#webcrumbs'
    };

    // JavaScript functions
    function openCompose() {
        window.location.href = 'compose.php';
    }

    function loadFolder(folder) {
        window.location.href = `?folder=${folder}`;
    }

    function filterByLabel(label) {
        window.location.href = `?label=${label}`;
    }

    function filterByCategory(category) {
        window.location.href = `?category=${category}`;
    }

    function refreshEmails() {
        location.reload();
    }

    function openEmail(emailId) {
        window.location.href = `email.php?id=${emailId}`;
    }

    function toggleStar(emailId) {
        fetch('ajax/toggle_star.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({email_id: emailId})
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            }
        });
    }

    function openModal(modalType) {
        // Implementation for opening modals
        console.log('Opening modal:', modalType);
    }

    // Select all functionality
    document.getElementById('selectAll').addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('#emailList input[type="checkbox"]');
        checkboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
    });
</script>
</body>
</html>
<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireAdmin();

$user = getCurrentUser();
$page = $_GET['page'] ?? 1;
$limit = 50;
$offset = ($page - 1) * $limit;

// Get audit logs with pagination
$stmt = $pdo->prepare("
    SELECT al.*, u.email as user_email, u.name as user_name
    FROM audit_logs al
    LEFT JOIN users u ON al.user_id = u.id
    ORDER BY al.created_at DESC
    LIMIT ? OFFSET ?
");
$stmt->execute([$limit, $offset]);
$auditLogs = $stmt->fetchAll();

// Get total count for pagination
$stmt = $pdo->prepare("SELECT COUNT(*) FROM audit_logs");
$stmt->execute();
$totalLogs = $stmt->fetchColumn();
$totalPages = ceil($totalLogs / $limit);

// Add icon and color based on action
foreach ($auditLogs as &$log) {
    switch ($log['action']) {
        case 'login':
            $log['icon'] = 'login';
            $log['color'] = 'blue';
            break;
        case 'logout':
            $log['icon'] = 'logout';
            $log['color'] = 'gray';
            break;
        case 'user_created':
            $log['icon'] = 'person_add';
            $log['color'] = 'green';
            break;
        case 'user_deactivated':
            $log['icon'] = 'person_remove';
            $log['color'] = 'red';
            break;
        case 'security_alert':
            $log['icon'] = 'warning';
            $log['color'] = 'yellow';
            break;
        case 'config_update':
            $log['icon'] = 'settings';
            $log['color'] = 'purple';
            break;
        case 'email_sent':
            $log['icon'] = 'send';
            $log['color'] = 'blue';
            break;
        case 'failed_login':
            $log['icon'] = 'error';
            $log['color'] = 'red';
            break;
        default:
            $log['icon'] = 'info';
            $log['color'] = 'gray';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Audit Log - MailFlow Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url(https://fonts.googleapis.com/css2?family=Lato&display=swap);
        @import url(https://fonts.googleapis.com/css2?family=Open+Sans&display=swap);
        @import url(https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined);
    </style>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen">
        <!-- Header -->
        <header class="bg-white border-b border-gray-200 shadow-sm">
            <div class="flex items-center justify-between h-16 px-6">
                <div class="flex items-center">
                    <a href="index.php" class="flex items-center text-primary-600 hover:text-primary-700">
                        <span class="material-symbols-outlined mr-2">arrow_back</span>
                        <span class="font-semibold">Back to Admin</span>
                    </a>
                </div>
                <h1 class="text-xl font-semibold text-gray-800">Audit Log</h1>
                <div class="w-32"></div>
            </div>
        </header>

        <!-- Main Content -->
        <div class="max-w-6xl mx-auto py-8 px-6">
            <!-- Stats -->
            <div class="mb-6 bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-800">Audit Log Overview</h2>
                        <p class="text-sm text-gray-600">Total of <?php echo number_format($totalLogs); ?> logged events</p>
                    </div>
                    <button onclick="exportAuditLog()" class="bg-primary-600 hover:bg-primary-700 text-white px-4 py-2 rounded-md font-medium transition duration-200 flex items-center">
                        <span class="material-symbols-outlined mr-2 text-sm">download</span>
                        Export
                    </button>
                </div>
            </div>

            <!-- Audit Log Table -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-800">Recent Activity</h2>
                </div>
                
                <div class="divide-y divide-gray-200">
                    <?php foreach ($auditLogs as $log): ?>
                    <div class="px-6 py-4 hover:bg-gray-50 transition duration-150">
                        <div class="flex items-start">
                            <span class="material-symbols-outlined p-2 bg-<?php echo $log['color']; ?>-100 text-<?php echo $log['color']; ?>-600 rounded-full text-sm mr-4 mt-1">
                                <?php echo $log['icon']; ?>
                            </span>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between mb-1">
                                    <p class="text-sm font-medium text-gray-900 capitalize">
                                        <?php echo str_replace('_', ' ', htmlspecialchars($log['action'])); ?>
                                    </p>
                                    <span class="text-xs text-gray-500">
                                        <?php echo date('M j, Y g:i A', strtotime($log['created_at'])); ?>
                                    </span>
                                </div>
                                
                                <?php if ($log['details']): ?>
                                <p class="text-sm text-gray-600 mb-2"><?php echo htmlspecialchars($log['details']); ?></p>
                                <?php endif; ?>
                                
                                <div class="flex items-center text-xs text-gray-500 space-x-4">
                                    <span class="flex items-center">
                                        <span class="material-symbols-outlined mr-1 text-xs">person</span>
                                        <?php echo htmlspecialchars($log['user_email'] ?? 'System'); ?>
                                    </span>
                                    <span class="flex items-center">
                                        <span class="material-symbols-outlined mr-1 text-xs">public</span>
                                        <?php echo htmlspecialchars($log['ip_address']); ?>
                                    </span>
                                    <?php if ($log['user_agent']): ?>
                                    <span class="flex items-center truncate max-w-xs">
                                        <span class="material-symbols-outlined mr-1 text-xs">computer</span>
                                        <?php echo htmlspecialchars(substr($log['user_agent'], 0, 50)) . '...'; ?>
                                    </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                    <div class="flex items-center justify-between">
                        <div class="text-sm text-gray-700">
                            Showing <?php echo (($page - 1) * $limit) + 1; ?> to <?php echo min($page * $limit, $totalLogs); ?> of <?php echo $totalLogs; ?> results
                        </div>
                        <div class="flex items-center space-x-2">
                            <?php if ($page > 1): ?>
                            <a href="?page=<?php echo $page - 1; ?>" class="px-3 py-2 text-sm bg-white border border-gray-300 rounded-md hover:bg-gray-50">Previous</a>
                            <?php endif; ?>
                            
                            <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                            <a href="?page=<?php echo $i; ?>" 
                               class="px-3 py-2 text-sm <?php echo $i === $page ? 'bg-primary-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50'; ?> border border-gray-300 rounded-md">
                                <?php echo $i; ?>
                            </a>
                            <?php endfor; ?>
                            
                            <?php if ($page < $totalPages): ?>
                            <a href="?page=<?php echo $page + 1; ?>" class="px-3 py-2 text-sm bg-white border border-gray-300 rounded-md hover:bg-gray-50">Next</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#f3f1ff',
                            500: '#7341ff',
                            600: '#631bff',
                            700: '#611bf8'
                        }
                    }
                }
            }
        };

        function exportAuditLog() {
            // Implementation would go here
            alert('Exporting audit log...');
        }
    </script>
</body>
</html>
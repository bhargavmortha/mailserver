<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireAdmin();

$user = getCurrentUser();

// Get email statistics
$stmt = $pdo->prepare("SELECT COUNT(*) FROM emails WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
$stmt->execute();
$emailsThisMonth = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM emails WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
$stmt->execute();
$emailsThisWeek = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM emails WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 DAY)");
$stmt->execute();
$emailsToday = $stmt->fetchColumn();

// Get user statistics
$stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE is_active = 1");
$stmt->execute();
$activeUsers = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
$stmt->execute();
$newUsersThisMonth = $stmt->fetchColumn();

// Get top email senders
$stmt = $pdo->prepare("
    SELECT u.name, u.email, COUNT(e.id) as email_count
    FROM users u
    JOIN emails e ON u.id = e.sender_id
    WHERE e.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    GROUP BY u.id
    ORDER BY email_count DESC
    LIMIT 10
");
$stmt->execute();
$topSenders = $stmt->fetchAll();

// Get daily email counts for the last 7 days
$stmt = $pdo->prepare("
    SELECT DATE(created_at) as date, COUNT(*) as count
    FROM emails
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    GROUP BY DATE(created_at)
    ORDER BY date DESC
");
$stmt->execute();
$dailyStats = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - MailFlow Admin</title>
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
                <h1 class="text-xl font-semibold text-gray-800">Reports & Analytics</h1>
                <div class="w-32"></div>
            </div>
        </header>

        <!-- Main Content -->
        <div class="max-w-7xl mx-auto py-8 px-6">
            <!-- Overview Stats -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                            <span class="material-symbols-outlined">today</span>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Emails Today</p>
                            <p class="text-2xl font-semibold text-gray-900"><?php echo $emailsToday; ?></p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-green-100 text-green-600">
                            <span class="material-symbols-outlined">date_range</span>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">This Week</p>
                            <p class="text-2xl font-semibold text-gray-900"><?php echo $emailsThisWeek; ?></p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                            <span class="material-symbols-outlined">calendar_month</span>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">This Month</p>
                            <p class="text-2xl font-semibold text-gray-900"><?php echo $emailsThisMonth; ?></p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                            <span class="material-symbols-outlined">group</span>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Active Users</p>
                            <p class="text-2xl font-semibold text-gray-900"><?php echo $activeUsers; ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Daily Email Activity -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-800">Daily Email Activity</h2>
                    </div>
                    <div class="p-6">
                        <div class="space-y-4">
                            <?php foreach ($dailyStats as $stat): ?>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-700"><?php echo date('M j, Y', strtotime($stat['date'])); ?></span>
                                <div class="flex items-center">
                                    <div class="w-32 bg-gray-200 rounded-full h-2 mr-3">
                                        <div class="bg-primary-600 h-2 rounded-full" style="width: <?php echo min(100, ($stat['count'] / max(1, $emailsThisWeek)) * 100); ?>%"></div>
                                    </div>
                                    <span class="text-sm font-medium text-gray-900 w-8"><?php echo $stat['count']; ?></span>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Top Email Senders -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-800">Top Email Senders (30 days)</h2>
                    </div>
                    <div class="p-6">
                        <div class="space-y-4">
                            <?php foreach ($topSenders as $index => $sender): ?>
                            <div class="flex items-center">
                                <div class="w-8 h-8 rounded-full bg-primary-100 flex items-center justify-center text-primary-700 mr-3">
                                    <span class="text-xs font-medium"><?php echo $index + 1; ?></span>
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($sender['name']); ?></p>
                                    <p class="text-xs text-gray-500"><?php echo htmlspecialchars($sender['email']); ?></p>
                                </div>
                                <span class="text-sm font-medium text-gray-900"><?php echo $sender['email_count']; ?></span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Export Options -->
            <div class="mt-8 bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-800">Export Reports</h2>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <button onclick="exportReport('email_activity')" class="flex flex-col items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition duration-200">
                            <span class="material-symbols-outlined text-primary-600 mb-2">download</span>
                            <span class="text-sm font-medium text-gray-700">Email Activity Report</span>
                        </button>
                        
                        <button onclick="exportReport('user_statistics')" class="flex flex-col items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition duration-200">
                            <span class="material-symbols-outlined text-primary-600 mb-2">download</span>
                            <span class="text-sm font-medium text-gray-700">User Statistics</span>
                        </button>
                        
                        <button onclick="exportReport('security_audit')" class="flex flex-col items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition duration-200">
                            <span class="material-symbols-outlined text-primary-600 mb-2">download</span>
                            <span class="text-sm font-medium text-gray-700">Security Audit</span>
                        </button>
                    </div>
                </div>
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

        function exportReport(reportType) {
            // Implementation would go here
            alert(`Exporting ${reportType} report...`);
        }
    </script>
</body>
</html>
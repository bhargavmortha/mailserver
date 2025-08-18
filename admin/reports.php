<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireAdmin();

$user = getCurrentUser();

// Get email statistics
$stmt = $pdo->prepare("
    SELECT 
        COUNT(*) as total_emails,
        SUM(CASE WHEN is_read = 1 THEN 1 ELSE 0 END) as read_emails,
        SUM(CASE WHEN is_draft = 1 THEN 1 ELSE 0 END) as draft_emails,
        SUM(CASE WHEN is_spam = 1 THEN 1 ELSE 0 END) as spam_emails,
        SUM(CASE WHEN is_deleted = 1 THEN 1 ELSE 0 END) as deleted_emails
    FROM emails
");
$stmt->execute();
$emailStats = $stmt->fetch();

// Get user statistics
$stmt = $pdo->prepare("
    SELECT 
        COUNT(*) as total_users,
        SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active_users,
        SUM(CASE WHEN role = 'Administrator' THEN 1 ELSE 0 END) as admin_users,
        SUM(CASE WHEN last_login > DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 ELSE 0 END) as recent_users
    FROM users
");
$stmt->execute();
$userStats = $stmt->fetch();

// Get daily email counts for the last 7 days
$stmt = $pdo->prepare("
    SELECT 
        DATE(created_at) as date,
        COUNT(*) as count
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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
        <div class="max-w-6xl mx-auto py-8 px-6">
            <!-- Email Statistics -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center">
                        <div class="p-2 bg-blue-100 rounded-lg">
                            <span class="material-symbols-outlined text-blue-600">email</span>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Total Emails</p>
                            <p class="text-2xl font-bold text-gray-900"><?php echo number_format($emailStats['total_emails']); ?></p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center">
                        <div class="p-2 bg-green-100 rounded-lg">
                            <span class="material-symbols-outlined text-green-600">mark_email_read</span>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Read Emails</p>
                            <p class="text-2xl font-bold text-gray-900"><?php echo number_format($emailStats['read_emails']); ?></p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center">
                        <div class="p-2 bg-yellow-100 rounded-lg">
                            <span class="material-symbols-outlined text-yellow-600">drafts</span>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Drafts</p>
                            <p class="text-2xl font-bold text-gray-900"><?php echo number_format($emailStats['draft_emails']); ?></p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center">
                        <div class="p-2 bg-red-100 rounded-lg">
                            <span class="material-symbols-outlined text-red-600">report</span>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Spam</p>
                            <p class="text-2xl font-bold text-gray-900"><?php echo number_format($emailStats['spam_emails']); ?></p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center">
                        <div class="p-2 bg-gray-100 rounded-lg">
                            <span class="material-symbols-outlined text-gray-600">delete</span>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Deleted</p>
                            <p class="text-2xl font-bold text-gray-900"><?php echo number_format($emailStats['deleted_emails']); ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- User Statistics -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center">
                        <div class="p-2 bg-purple-100 rounded-lg">
                            <span class="material-symbols-outlined text-purple-600">group</span>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Total Users</p>
                            <p class="text-2xl font-bold text-gray-900"><?php echo number_format($userStats['total_users']); ?></p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center">
                        <div class="p-2 bg-green-100 rounded-lg">
                            <span class="material-symbols-outlined text-green-600">person_check</span>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Active Users</p>
                            <p class="text-2xl font-bold text-gray-900"><?php echo number_format($userStats['active_users']); ?></p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center">
                        <div class="p-2 bg-red-100 rounded-lg">
                            <span class="material-symbols-outlined text-red-600">admin_panel_settings</span>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Administrators</p>
                            <p class="text-2xl font-bold text-gray-900"><?php echo number_format($userStats['admin_users']); ?></p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center">
                        <div class="p-2 bg-blue-100 rounded-lg">
                            <span class="material-symbols-outlined text-blue-600">schedule</span>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Recent Logins</p>
                            <p class="text-2xl font-bold text-gray-900"><?php echo number_format($userStats['recent_users']); ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Daily Email Activity -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-800">Daily Email Activity (Last 7 Days)</h2>
                    </div>
                    <div class="p-6">
                        <canvas id="dailyChart" width="400" height="200"></canvas>
                    </div>
                </div>

                <!-- Email Distribution -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-800">Email Distribution</h2>
                    </div>
                    <div class="p-6">
                        <canvas id="distributionChart" width="400" height="200"></canvas>
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

        // Daily Activity Chart
        const dailyCtx = document.getElementById('dailyChart').getContext('2d');
        const dailyChart = new Chart(dailyCtx, {
            type: 'line',
            data: {
                labels: [<?php echo "'" . implode("','", array_reverse(array_column($dailyStats, 'date'))) . "'"; ?>],
                datasets: [{
                    label: 'Emails Sent',
                    data: [<?php echo implode(',', array_reverse(array_column($dailyStats, 'count'))); ?>],
                    borderColor: '#631bff',
                    backgroundColor: 'rgba(99, 27, 255, 0.1)',
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Distribution Chart
        const distributionCtx = document.getElementById('distributionChart').getContext('2d');
        const distributionChart = new Chart(distributionCtx, {
            type: 'doughnut',
            data: {
                labels: ['Read', 'Unread', 'Drafts', 'Spam', 'Deleted'],
                datasets: [{
                    data: [
                        <?php echo $emailStats['read_emails']; ?>,
                        <?php echo $emailStats['total_emails'] - $emailStats['read_emails']; ?>,
                        <?php echo $emailStats['draft_emails']; ?>,
                        <?php echo $emailStats['spam_emails']; ?>,
                        <?php echo $emailStats['deleted_emails']; ?>
                    ],
                    backgroundColor: [
                        '#10b981',
                        '#f59e0b',
                        '#3b82f6',
                        '#ef4444',
                        '#6b7280'
                    ]
                }]
            },
            options: {
                responsive: true
            }
        });
    </script>
</body>
</html>
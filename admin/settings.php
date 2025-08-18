<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/auth.php';

requireAdmin();

$user = getCurrentUser();
$success = '';
$error = '';

// Get current settings
$stmt = $pdo->prepare("SELECT * FROM system_settings");
$stmt->execute();
$settings = [];
while ($row = $stmt->fetch()) {
    $settings[$row['setting_key']] = $row['setting_value'];
}

if ($_POST) {
    $updates = [
        'smtp_host' => $_POST['smtp_host'] ?? '',
        'smtp_port' => $_POST['smtp_port'] ?? '587',
        'max_attachment_size' => $_POST['max_attachment_size'] ?? '10485760',
        'session_timeout' => $_POST['session_timeout'] ?? '3600',
        'maintenance_mode' => isset($_POST['maintenance_mode']) ? '1' : '0'
    ];
    
    try {
        foreach ($updates as $key => $value) {
            $stmt = $pdo->prepare("
                INSERT INTO system_settings (setting_key, setting_value) 
                VALUES (?, ?) 
                ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)
            ");
            $stmt->execute([$key, $value]);
        }
        
        $success = 'Settings updated successfully';
        $settings = array_merge($settings, $updates);
        logActivity($user['id'], 'config_update', 'System settings updated');
        
    } catch (Exception $e) {
        $error = 'Failed to update settings: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Settings - MailFlow Admin</title>
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
                <h1 class="text-xl font-semibold text-gray-800">System Settings</h1>
                <div class="w-32"></div>
            </div>
        </header>

        <!-- Main Content -->
        <div class="max-w-4xl mx-auto py-8 px-6">
            <?php if ($success): ?>
            <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-md">
                <?php echo htmlspecialchars($success); ?>
            </div>
            <?php endif; ?>

            <?php if ($error): ?>
            <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-md">
                <?php echo htmlspecialchars($error); ?>
            </div>
            <?php endif; ?>

            <form method="POST" class="space-y-8">
                <!-- Email Settings -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-800">Email Configuration</h2>
                    </div>
                    <div class="p-6 space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="smtp_host" class="block text-sm font-medium text-gray-700 mb-2">SMTP Host</label>
                                <input type="text" id="smtp_host" name="smtp_host" 
                                       value="<?php echo htmlspecialchars($settings['smtp_host'] ?? ''); ?>"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-primary-500 focus:border-primary-500">
                            </div>
                            <div>
                                <label for="smtp_port" class="block text-sm font-medium text-gray-700 mb-2">SMTP Port</label>
                                <input type="number" id="smtp_port" name="smtp_port" 
                                       value="<?php echo htmlspecialchars($settings['smtp_port'] ?? '587'); ?>"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-primary-500 focus:border-primary-500">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- File Upload Settings -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-800">File Upload Settings</h2>
                    </div>
                    <div class="p-6">
                        <div>
                            <label for="max_attachment_size" class="block text-sm font-medium text-gray-700 mb-2">
                                Maximum Attachment Size (bytes)
                            </label>
                            <input type="number" id="max_attachment_size" name="max_attachment_size" 
                                   value="<?php echo htmlspecialchars($settings['max_attachment_size'] ?? '10485760'); ?>"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-primary-500 focus:border-primary-500">
                            <p class="text-xs text-gray-500 mt-1">Current: <?php echo formatFileSize($settings['max_attachment_size'] ?? 10485760); ?></p>
                        </div>
                    </div>
                </div>

                <!-- Security Settings -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-800">Security Settings</h2>
                    </div>
                    <div class="p-6">
                        <div>
                            <label for="session_timeout" class="block text-sm font-medium text-gray-700 mb-2">
                                Session Timeout (seconds)
                            </label>
                            <input type="number" id="session_timeout" name="session_timeout" 
                                   value="<?php echo htmlspecialchars($settings['session_timeout'] ?? '3600'); ?>"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-primary-500 focus:border-primary-500">
                            <p class="text-xs text-gray-500 mt-1">Current: <?php echo gmdate('H:i:s', $settings['session_timeout'] ?? 3600); ?></p>
                        </div>
                    </div>
                </div>

                <!-- System Settings -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-800">System Settings</h2>
                    </div>
                    <div class="p-6">
                        <div class="flex items-center">
                            <input type="checkbox" id="maintenance_mode" name="maintenance_mode" 
                                   <?php echo ($settings['maintenance_mode'] ?? '0') === '1' ? 'checked' : ''; ?>
                                   class="h-4 w-4 text-primary-600 rounded border-gray-300 focus:ring-1 focus:ring-primary-500">
                            <label for="maintenance_mode" class="ml-2 block text-sm text-gray-700">
                                Enable Maintenance Mode
                            </label>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">When enabled, only administrators can access the system</p>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit" 
                            class="bg-primary-600 hover:bg-primary-700 text-white px-6 py-2 rounded-md font-medium transition duration-200">
                        Save Settings
                    </button>
                </div>
            </form>
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
    </script>
</body>
</html>
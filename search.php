<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

requireAuth();

$user = getCurrentUser();
$query = $_GET['q'] ?? '';
$emails = [];

if (!empty($query)) {
    $emails = searchEmails($user['id'], $query);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results - MailFlow</title>
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
                        <span class="font-semibold">Back to Inbox</span>
                    </a>
                </div>
                <h1 class="text-xl font-semibold text-gray-800">
                    Search Results for "<?php echo htmlspecialchars($query); ?>"
                </h1>
                <div class="w-32"></div>
            </div>
        </header>

        <!-- Search Results -->
        <div class="max-w-4xl mx-auto py-8 px-6">
            <?php if (empty($query)): ?>
            <div class="text-center py-12">
                <span class="material-symbols-outlined text-gray-400 text-6xl mb-4">search</span>
                <h2 class="text-xl font-semibold text-gray-700 mb-2">Enter a search term</h2>
                <p class="text-gray-500">Use the search box to find emails by subject, content, or sender.</p>
            </div>
            <?php elseif (empty($emails)): ?>
            <div class="text-center py-12">
                <span class="material-symbols-outlined text-gray-400 text-6xl mb-4">search_off</span>
                <h2 class="text-xl font-semibold text-gray-700 mb-2">No results found</h2>
                <p class="text-gray-500">Try different keywords or check your spelling.</p>
            </div>
            <?php else: ?>
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-800">
                        Found <?php echo count($emails); ?> result<?php echo count($emails) !== 1 ? 's' : ''; ?>
                    </h2>
                </div>
                
                <div class="divide-y divide-gray-200">
                    <?php foreach ($emails as $email): ?>
                    <div class="px-6 py-4 hover:bg-gray-50 cursor-pointer transition duration-150" 
                         onclick="window.location.href='email.php?id=<?php echo $email['id']; ?>'">
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="text-sm font-medium text-gray-900">
                                <?php echo htmlspecialchars($email['sender_name']); ?>
                            </h3>
                            <span class="text-xs text-gray-500">
                                <?php echo formatEmailDate($email['created_at']); ?>
                            </span>
                        </div>
                        <h4 class="text-sm font-semibold text-gray-800 mb-1">
                            <?php echo htmlspecialchars($email['subject']); ?>
                        </h4>
                        <p class="text-sm text-gray-600 line-clamp-2">
                            <?php echo htmlspecialchars(substr(strip_tags($email['body']), 0, 150)) . '...'; ?>
                        </p>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
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
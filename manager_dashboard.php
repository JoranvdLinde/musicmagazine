<?php
session_start();
require_once 'config/database.php';

// Check if user is manager
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'manager') {
    header('Location: home.php');
    exit();
}

// Fetch all users and articles
$users = $pdo->query("SELECT * FROM music_users ORDER BY created_at DESC")->fetchAll();
$articles = $pdo->query("SELECT ma.*, u.username FROM magazine_articles ma 
                        JOIN music_users u ON ma.user_id = u.id 
                        ORDER BY ma.created_at DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manager Dashboard - Music Today</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Anton&display=swap" rel="stylesheet">
    <script>
tailwind.config = {
    theme: {
        extend: {
            colors: {
                'magazine-dark': '#121212',
                'magazine-card': '#1E1E1E',
                'magazine-accent': '#FF3366',
                'magazine-secondary': '#4C8BF5',
            },
            fontFamily: {
                'lato': ['"Lato"', 'sans-serif'],
                'roboto': ['"Roboto"', 'sans-serif'],
            }
        }
    }
}

    </script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Lato&family=Roboto&display=swap');
        
        ::-webkit-scrollbar {
            width: 8px;
        }
        ::-webkit-scrollbar-track {
            background: #121212;
        }
        ::-webkit-scrollbar-thumb {
            background: #FF3366;
            border-radius: 4px;
        }
    </style>
</head>
<body class="bg-magazine-dark text-white font-roboto">
    <!-- Mobile Navigation -->
    <nav class="lg:hidden fixed bottom-0 left-0 right-0 bg-magazine-card border-t border-gray-800 z-50">
        <div class="flex justify-around items-center p-4">
            <a href="home.php" class="text-white hover:text-magazine-accent">
                <i class="fas fa-home text-2xl"></i>
            </a>
            <a href="profile.php" class="text-white hover:text-magazine-accent">
                <i class="fas fa-user text-2xl"></i>
            </a>
            <a href="manager_dashboard.php" class="text-magazine-accent">
                <i class="fas fa-users-cog text-2xl"></i>
            </a>
        </div>
    </nav>

    <!-- Desktop Sidebar -->
    <aside class="hidden lg:flex flex-col fixed left-0 top-0 h-screen w-64 bg-magazine-card p-6 border-r border-gray-800">
        <a href="home.php" class="text-4xl font-bold text-magazine-accent mb-12">𝕄</a>
        <nav class="space-y-4">
            <a href="home.php" class="flex items-center space-x-3 text-white hover:text-magazine-accent p-3 rounded-lg transition">
                <i class="fas fa-home"></i>
                <span>Home</span>
            </a>
            <a href="profile.php" class="flex items-center space-x-3 text-white hover:text-magazine-accent p-3 rounded-lg transition">
                <i class="fas fa-user"></i>
                <span>Profile</span>
            </a>
            <a href="manager_dashboard.php" class="flex items-center space-x-3 text-magazine-accent p-3 rounded-lg transition">
                <i class="fas fa-users-cog"></i>
                <span>Manager Dashboard</span>
            </a>
        </nav>
    </aside>

    <main class="lg:ml-64 p-8 pb-20 lg:pb-8">
        <h1 class="text-4xl font-bold mb-8">Manager Dashboard</h1>

        <!-- Users Section -->
        <section class="mb-12">
            <h2 class="text-2xl font-bold mb-6 text-magazine-accent">User Management</h2>
            <div class="bg-magazine-card rounded-xl p-6 overflow-x-auto">
                <table class="w-full">
                    <thead class="border-b border-gray-700">
                        <tr>
                            <th class="text-left py-3">Username</th>
                            <th class="text-left py-3">Email</th>
                            <th class="text-left py-3">Role</th>
                            <th class="text-left py-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                        <tr class="border-b border-gray-800">
                            <td class="py-4"><?= htmlspecialchars($user['username']) ?></td>
                            <td class="py-4"><?= htmlspecialchars($user['email']) ?></td>
                            <td class="py-4">
                                <select onchange="updateRole(<?= $user['id'] ?>, this.value)" 
                                        class="bg-magazine-dark text-white px-3 py-1 rounded">
                                    <option value="user" <?= $user['role'] === 'user' ? 'selected' : '' ?>>User</option>
                                    <option value="staff" <?= $user['role'] === 'staff' ? 'selected' : '' ?>>Staff</option>
                                    <option value="manager" <?= $user['role'] === 'manager' ? 'selected' : '' ?>>Manager</option>
                                </select>
                            </td>
                            <td class="py-4">
                                <button onclick="deleteUser(<?= $user['id'] ?>)" 
                                        class="bg-red-600 hover:bg-red-700 px-4 py-2 rounded transition">
                                    Delete
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- Articles Section -->
        <section>
            <h2 class="text-2xl font-bold mb-6 text-magazine-accent">Article Management</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($articles as $article): ?>
                    <div class="bg-magazine-card p-6 rounded-xl">
                        <?php if (!empty($article['image_url'])): ?>
                            <img src="<?= htmlspecialchars($article['image_url']) ?>" 
                                 alt="Article Image" 
                                 class="w-full h-48 object-cover rounded-lg mb-4">
                        <?php endif; ?>
                        <h3 class="font-bold text-xl mb-2"><?= htmlspecialchars($article['title']) ?></h3>
                        <p class="text-gray-400 mb-4">By: <?= htmlspecialchars($article['username']) ?></p>
                        <div class="flex space-x-4">
                            <a href="edit_article.php?id=<?= $article['id'] ?>" 
                               class="bg-magazine-accent hover:bg-opacity-80 px-4 py-2 rounded transition">
                                Edit
                            </a>
                            <button onclick="deleteArticle(<?= $article['id'] ?>)" 
                                    class="bg-red-600 hover:bg-red-700 px-4 py-2 rounded transition">
                                Delete
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    </main>

    <script>
        function updateRole(userId, newRole) {
            fetch('manager_actions.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=update_role&user_id=${userId}&new_role=${newRole}`
            }).then(() => window.location.reload());
        }

        function deleteUser(userId) {
            if (confirm('Are you sure you want to delete this user?')) {
                fetch('manager_actions.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=delete_user&user_id=${userId}`
                }).then(() => window.location.reload());
            }
        }

        function deleteArticle(articleId) {
            if (confirm('Are you sure you want to delete this article?')) {
                fetch('manager_actions.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=delete_article&article_id=${articleId}`
                }).then(() => window.location.reload());
            }
        }
    </script>
</body>
</html>

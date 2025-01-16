<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'manager') {
    header('Location: home.php');
    exit();
}

$article_id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM magazine_articles WHERE id = ?");
$stmt->execute([$article_id]);
$article = $stmt->fetch();

if (!$article) {
    header('Location: manager_dashboard.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Article - Music Today</title>
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
                        'anton': ['Anton', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Anton&display=swap');
        
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
<body class="bg-magazine-dark text-white font-anton">
    <!-- Mobile Navigation -->
    <nav class="lg:hidden fixed bottom-0 left-0 right-0 bg-magazine-card border-t border-gray-800 z-50">
        <div class="flex justify-around items-center p-4">
            <a href="home.php" class="text-white hover:text-magazine-accent">
                <i class="fas fa-home text-2xl"></i>
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
            <a href="manager_dashboard.php" class="flex items-center space-x-3 text-white hover:text-magazine-accent p-3 rounded-lg transition">
                <i class="fas fa-users-cog"></i>
                <span>Dashboard</span>
            </a>
        </nav>
    </aside>

    <main class="lg:ml-64 p-8 pb-20 lg:pb-8">
        <h1 class="text-4xl font-bold mb-8">Edit Article</h1>
        
        <form action="update_article.php" method="POST" enctype="multipart/form-data" class="max-w-4xl">
            <input type="hidden" name="article_id" value="<?= $article['id'] ?>">
            <div class="bg-magazine-card rounded-xl p-6 space-y-6">
                <div>
                    <label class="block mb-2">Title</label>
                    <input type="text" name="title" value="<?= htmlspecialchars($article['title']) ?>" 
                           class="w-full bg-magazine-dark p-3 rounded border border-gray-700 focus:border-magazine-accent focus:outline-none">
                </div>

                <div>
                    <label class="block mb-2">Content</label>
                    <textarea name="content" rows="10" 
                              class="w-full bg-magazine-dark p-3 rounded border border-gray-700 focus:border-magazine-accent focus:outline-none"
                    ><?= htmlspecialchars($article['content']) ?></textarea>
                </div>

                <div>
                    <label class="block mb-2">Current Image</label>
                    <?php if ($article['image_url']): ?>
                        <img src="<?= htmlspecialchars($article['image_url']) ?>" alt="Current Article Image" class="h-48 object-cover rounded mb-4">
                    <?php endif; ?>
                    <input type="file" name="new_image" accept="image/*" 
                           class="w-full bg-magazine-dark p-3 rounded border border-gray-700">
                </div>

                <div>
                    <label class="block mb-2">Current Music Track</label>
                    <?php if ($article['music_url']): ?>
                        <audio src="<?= htmlspecialchars($article['music_url']) ?>" controls class="w-full mb-4"></audio>
                    <?php endif; ?>
                    <input type="file" name="new_music" accept="audio/*" 
                           class="w-full bg-magazine-dark p-3 rounded border border-gray-700">
                </div>

                <div class="flex space-x-4">
                    <button type="submit" 
                            class="bg-magazine-accent hover:bg-opacity-80 px-6 py-3 rounded-lg transition">
                        Save Changes
                    </button>
                    <a href="manager_dashboard.php" 
                       class="bg-gray-700 hover:bg-gray-600 px-6 py-3 rounded-lg transition">
                        Cancel
                    </a>
                </div>
            </div>
        </form>
    </main>
</body>
</html>

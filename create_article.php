<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['staff', 'manager'])) {
    header('Location: login.php');
    exit();
}

$stmt = $pdo->prepare("SELECT role FROM music_users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

$_SESSION['role'] = $user['role'];

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['staff', 'manager'])) {
    header('Location: home.php');
    exit();
}
    
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $title = trim($_POST['title']);
        $content = trim($_POST['content']);
        
        $image_url = null;
        if (isset($_FILES['media']) && $_FILES['media']['error'] === UPLOAD_ERR_OK) {
            $fileType = mime_content_type($_FILES['media']['tmp_name']);
            $allowedTypes = [
                'image/jpeg',
                'image/png',
                'image/gif',
                'video/mp4',
                'video/webm',
                'video/ogg'
            ];
            
            if (in_array($fileType, $allowedTypes)) {
                $uploadDir = 'uploads/articles/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                
                $image_url = $uploadDir . uniqid() . '_' . basename($_FILES['media']['name']);
                move_uploaded_file($_FILES['media']['tmp_name'], $image_url);
            }
        }
        
        $music_url = null;
        if (isset($_FILES['music']) && $_FILES['music']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = 'uploads/music/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            $music_url = $uploadDir . uniqid() . '_' . basename($_FILES['music']['name']);
            move_uploaded_file($_FILES['music']['tmp_name'], $music_url);
        }
        
        $stmt = $pdo->prepare("
            INSERT INTO magazine_articles (user_id, title, content, image_url, music_url)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$_SESSION['user_id'], $title, $content, $image_url, $music_url]);
        
        header('Location: home.php');
        exit();
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Article - Music Magazine</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
    <nav class="bg-magazine-card/50 backdrop-blur-md fixed w-full z-50">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between items-center h-16">
                <a href="home.php" class="text-2xl font-bold text-magazine-accent">Music Magazine</a>
                <div class="flex items-center space-x-4">
                    <a href="profile.php" class="hover:text-magazine-accent transition">Profile</a>
                    <a href="logout.php" class="hover:text-magazine-accent transition">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-4xl mx-auto p-6 pt-20">
        <h1 class="text-3xl font-bold mb-8 text-magazine-accent">Create New Article</h1>
        
        <?php if (isset($error)): ?>
            <div class="bg-red-500/20 border border-red-500 text-white p-4 rounded-lg mb-6">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <form action="" method="POST" enctype="multipart/form-data" class="space-y-6">
            <div>
                <label class="block mb-2">Title</label>
                <input type="text" name="title" required
                       class="w-full p-3 rounded-lg bg-magazine-card border border-gray-700 focus:ring-2 focus:ring-magazine-accent focus:outline-none">
            </div>
            
            <div>
                <label class="block mb-2">Content</label>
                <textarea name="content" required rows="10"
                          class="w-full p-3 rounded-lg bg-magazine-card border border-gray-700 focus:ring-2 focus:ring-magazine-accent focus:outline-none"></textarea>
            </div>
            
            <div>
                <label class="block mb-2">Upload Media (Image or Video)</label>
                <input type="file" name="media" accept="image/*,video/*"
                       class="w-full p-3 rounded-lg bg-magazine-card border border-gray-700">
            </div>
            
            <div>
                <label class="block mb-2">Music File</label>
                <input type="file" name="music" accept="audio/*"
                       class="w-full p-3 rounded-lg bg-magazine-card border border-gray-700">
            </div>
            
            <button type="submit" 
                    class="w-full bg-magazine-accent hover:bg-opacity-80 text-white font-bold py-3 px-4 rounded-lg transition duration-300">
                Publish Article
            </button>
        </form>
    </div>
</body>
</html>

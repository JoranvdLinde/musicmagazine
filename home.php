<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$stmt = $pdo->prepare("SELECT u.*, p.profile_image FROM music_users u LEFT JOIN music_profiles p ON u.id = p.user_id WHERE u.id = ?");
$stmt->execute([$_SESSION['user_id']]);
$currentUser = $stmt->fetch();
$_SESSION['role'] = $currentUser['role'];
$isStaff = in_array($_SESSION['role'], ['staff', 'manager']);

try {
    $stmt = $pdo->prepare("SELECT DISTINCT ma.id, ma.*, u.username, u.id as user_id, u.role, pr.profile_image
        FROM magazine_articles ma
        JOIN music_users u ON ma.user_id = u.id
        LEFT JOIN music_profiles pr ON u.id = pr.user_id
        WHERE u.role IN ('user', 'staff', 'manager')
        GROUP BY ma.id
        ORDER BY ma.created_at DESC");
    $stmt->execute();
    $articles = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log($e->getMessage());
    $articles = [];
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Music Today - Modern Music Magazine</title>
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

        .progress {
            transition: width 0.1s linear;
        }
    </style>
</head>
<body class="bg-magazine-dark text-white font-roboto">
<!-- Mobile Navigation -->
<nav class="lg:hidden fixed bottom-0 left-0 right-0 bg-magazine-card border-t border-gray-800 z-50">
    <div class="flex justify-around items-center p-4">
        <a href="home.php" class="text-magazine-accent">
            <i class="fas fa-home text-2xl"></i>
        </a>
        <a href="profile.php" class="text-white hover:text-magazine-accent transition">
            <i class="fas fa-user text-2xl"></i>
        </a>
        <?php if ($isStaff): ?>
            <a href="create_article.php" class="text-white hover:text-magazine-accent transition">
                <i class="fas fa-pen text-2xl"></i>
            </a>
        <?php endif; ?>
        <?php if ($_SESSION['role'] === 'manager'): ?>
            <a href="manager_dashboard.php" class="text-white hover:text-magazine-accent transition">
                <i class="fas fa-users-cog text-2xl"></i>
            </a>
        <?php endif; ?>
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
        <?php if ($isStaff): ?>
            <a href="create_article.php" class="flex items-center space-x-3 text-white hover:text-magazine-accent p-3 rounded-lg transition">
                <i class="fas fa-pen"></i>
                <span>Create Article</span>
            </a>
        <?php endif; ?>
        <?php if ($_SESSION['role'] === 'manager'): ?>
            <a href="manager_dashboard.php" class="flex items-center space-x-3 text-white hover:text-magazine-accent p-3 rounded-lg transition">
                <i class="fas fa-users-cog"></i>
                <span>Manager Dashboard</span>
            </a>
        <?php endif; ?>
    </nav>
</aside>

    <!-- Main Content -->
    <main class="lg:ml-64 p-4 lg:p-8 pb-20 lg:pb-8">
        <h1 class="font-lato text-4xl md:text-6xl font-bold text-center mb-12 bg-gradient-to-r from-magazine-accent to-magazine-secondary text-transparent bg-clip-text">
            Music Today
        </h1>

        <?php if (!empty($articles)): ?>
            <!-- Featured Article -->
            <article class="relative rounded-2xl overflow-hidden mb-12 group cursor-pointer"
                     onclick="openModal(<?php echo $articles[0]['id']; ?>)">
                <?php if (!empty($articles[0]['video_url'])): ?>
                    <video class="w-full h-[50vh] md:h-[70vh] object-cover" controls>
                        <source src="<?php echo htmlspecialchars($articles[0]['video_url']); ?>" type="video/mp4">
                        Your browser does not support the video tag.
                    </video>
                <?php elseif (!empty($articles[0]['image_url'])): ?>
                    <img src="<?php echo htmlspecialchars($articles[0]['image_url']); ?>"
                         alt="Featured"
                         class="w-full h-[50vh] md:h-[70vh] object-cover transition duration-500 group-hover:scale-105">
                <?php else: ?>
                    <div class="w-full h-[30vh] md:h-[40vh] bg-magazine-card"></div>
                <?php endif; ?>
                <div class="absolute inset-0 bg-gradient-to-t from-black to-transparent"></div>
                <div class="absolute bottom-0 left-0 right-0 p-6 md:p-8">
                    <span class="inline-block px-4 py-1 bg-magazine-accent rounded-full text-sm font-medium mb-4">
                        Featured
                    </span>
                    <h2 class="text-2xl md:text-4xl font-lato font-bold mb-4">
                        <?php echo htmlspecialchars($articles[0]['title']); ?>
                    </h2>
                    <p class="text-gray-200 mb-4 line-clamp-2">
                        <?php echo htmlspecialchars(substr($articles[0]['content'], 0, 200)); ?>...
                    </p>
                    <a href="view_profile.php?user_id=<?php echo $articles[0]['user_id']; ?>" 
                       class="flex items-center space-x-4 hover:text-magazine-accent transition" 
                       onclick="event.stopPropagation()">
                        <img src="<?php echo htmlspecialchars($articles[0]['profile_image'] ?? 'default-avatar.jpg'); ?>"
                             alt="Author"
                             class="w-10 h-10 rounded-full border-2 border-magazine-accent">
                        <div>
                            <p class="font-medium"><?php echo htmlspecialchars($articles[0]['username']); ?></p>
                            <p class="text-sm text-gray-400">
                                <i class="far fa-clock mr-2"></i>
                                <?php echo ceil(str_word_count($articles[0]['content']) / 200); ?> min read
                            </p>
                        </div>
                    </a>
                </div>
            </article>

            <!-- Featured Article Modal -->
            <div id="modal-<?php echo $articles[0]['id']; ?>" class="fixed inset-0 z-50 hidden">
                <div class="absolute inset-0 bg-black bg-opacity-75 backdrop-blur-sm"></div>
                <div class="relative min-h-screen flex items-center justify-center p-4">
                    <div class="bg-magazine-card rounded-2xl w-full max-w-4xl max-h-[90vh] overflow-y-auto">
                        <div class="p-6 md:p-8">
                            <button onclick="closeModal(<?php echo $articles[0]['id']; ?>)"
                                    class="absolute top-4 right-4 text-gray-400 hover:text-white">
                                <i class="fas fa-times text-2xl"></i>
                            </button>
                            <h2 class="font-lato text-2xl md:text-3xl font-bold mb-6">
                                <?php echo htmlspecialchars($articles[0]['title']); ?>
                            </h2>
                            <?php if (!empty($articles[0]['video_url'])): ?>
                                <video class="w-full h-64 md:h-96 object-cover rounded-xl mb-6" controls>
                                    <source src="<?php echo htmlspecialchars($articles[0]['video_url']); ?>" type="video/mp4">
                                    Your browser does not support the video tag.
                                </video>
                            <?php elseif (!empty($articles[0]['image_url'])): ?>
                                <img src="<?php echo htmlspecialchars($articles[0]['image_url']); ?>"
                                     alt="Article Image"
                                     class="w-full h-64 md:h-96 object-cover rounded-xl mb-6">
                            <?php endif; ?>
                            <?php if (!empty($articles[0]['music_url'])): ?>
                                <div class="my-6 p-4 bg-black/20 rounded-xl">
                                    <h3 class="text-xl font-bold mb-4 flex items-center">
                                        <i class="fas fa-music text-magazine-accent mr-2"></i>
                                        Featured Track
                                    </h3>
                                    <div class="audio-player bg-magazine-dark rounded-lg p-4 border border-magazine-accent/20">
                                        <audio id="audio-<?php echo $articles[0]['id']; ?>" 
                                               src="<?php echo htmlspecialchars($articles[0]['music_url']); ?>" 
                                               preload="metadata"></audio>
                                        <div class="flex items-center gap-4">
                                            <button onclick="togglePlay(this, <?php echo $articles[0]['id']; ?>)" 
                                                    class="play-btn w-12 h-12 flex items-center justify-center bg-magazine-accent rounded-full text-white hover:scale-105 transition-transform">
                                                <i class="fas fa-play"></i>
                                            </button>
                                            <div class="flex-1">
                                                <div class="progress-bar h-1.5 bg-gray-700 rounded-full overflow-hidden">
                                                    <div class="progress w-0 h-full bg-magazine-accent transition-all duration-300"></div>
                                                </div>
                                                <div class="flex justify-between text-sm text-gray-400 mt-2">
                                                    <span class="current-time">0:00</span>
                                                    <span class="duration">0:00</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <div class="prose prose-invert max-w-none">
                                <?php echo nl2br(htmlspecialchars($articles[0]['content'])); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Article Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach (array_slice($articles, 1) as $article): ?>
                    <article class="bg-magazine-card rounded-xl overflow-hidden shadow-lg hover:shadow-2xl transition duration-300 cursor-pointer"
                             onclick="openModal(<?php echo $article['id']; ?>)">
                        <div class="relative aspect-video">
                            <?php if (!empty($article['video_url'])): ?>
                                <video class="w-full h-full object-cover" controls>
                                    <source src="<?php echo htmlspecialchars($article['video_url']); ?>" type="video/mp4">
                                    Your browser does not support the video tag.
                                </video>
                            <?php elseif (!empty($article['image_url'])): ?>
                                <img src="<?php echo htmlspecialchars($article['image_url']); ?>"
                                     alt="Article"
                                     class="w-full h-full object-cover">
                            <?php else: ?>
                                <div class="w-full h-full bg-magazine-card flex items-center justify-center">
                                    <i class="fas fa-music text-4xl text-gray-600"></i>
                                </div>
                            <?php endif; ?>
                            <span class="absolute top-4 left-4 px-3 py-1 bg-magazine-accent rounded-full text-sm font-medium">
                                Music
                            </span>
                        </div>
                        <div class="p-6">
                            <h3 class="font-lato text-xl font-bold mb-3">
                                <?php echo htmlspecialchars($article['title']); ?>
                            </h3>
                            <p class="text-gray-400 mb-4 line-clamp-3">
                            <?php echo htmlspecialchars(substr($article['content'], 0, 150)); ?>...
                            </p>
                            <?php if ($article['music_url']): ?>
                                <div class="audio-player bg-magazine-dark rounded-lg p-4 border border-magazine-accent/20 mb-4">
                                    <audio id="audio-preview-<?php echo $article['id']; ?>" 
                                           src="<?php echo htmlspecialchars($article['music_url']); ?>" 
                                           preload="metadata"></audio>
                                    <div class="flex items-center gap-4">
                                        <button onclick="event.stopPropagation(); togglePlay(this, 'preview-<?php echo $article['id']; ?>')" 
                                                class="play-btn w-10 h-10 flex items-center justify-center bg-magazine-accent rounded-full text-white hover:scale-105 transition-transform">
                                            <i class="fas fa-play"></i>
                                        </button>
                                        <div class="flex-1">
                                        <div class="progress-bar h-1.5 bg-gray-700 rounded-full overflow-hidden">
                                                <div class="progress w-0 h-full bg-magazine-accent transition-all duration-300"></div>
                                            </div>
                                            <div class="flex justify-between text-sm text-gray-400 mt-2">
                                                <span class="current-time">0:00</span>
                                                <span class="duration">0:00</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <div class="flex items-center space-x-4 pt-4 border-t border-gray-800">
                                <a href="view_profile.php?user_id=<?php echo $article['user_id']; ?>" 
                                   class="flex items-center space-x-4 hover:text-magazine-accent transition" 
                                   onclick="event.stopPropagation()">
                                    <img src="<?php echo htmlspecialchars($article['profile_image'] ?? 'default-avatar.jpg'); ?>"
                                         alt="Author"
                                         class="w-10 h-10 rounded-full">
                                    <div>
                                        <p class="font-medium"><?php echo htmlspecialchars($article['username']); ?></p>
                                        <p class="text-sm text-gray-400">
                                            <i class="far fa-clock mr-2"></i>
                                            <?php echo ceil(str_word_count($article['content']) / 200); ?> min read
                                        </p>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </article>

        <?php endforeach; ?>
    </div>
<?php endif; ?>
</main>

<script>
    function openModal(articleId) {
        document.getElementById(`modal-${articleId}`).classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        
        const audio = document.getElementById(`audio-${articleId}`);
        if (audio) {
            initAudioPlayer(articleId);
        }
    }

    function closeModal(articleId) {
        const modal = document.getElementById(`modal-${articleId}`);
        modal.classList.add('hidden');
        document.body.style.overflow = 'auto';
        
        const audio = modal.querySelector(`#audio-${articleId}`);
        if (audio) {
            audio.pause();
            const playButton = modal.querySelector('.play-btn i');
            playButton.classList.replace('fa-pause', 'fa-play');
        }
    }

    function togglePlay(button, id) {
        const audio = document.getElementById(`audio-${id}`);
        const icon = button.querySelector('i');
        
        document.querySelectorAll('audio').forEach(a => {
            if (a !== audio && !a.paused) {
                a.pause();
                a.parentElement.querySelector('.play-btn i').classList.replace('fa-pause', 'fa-play');
            }
        });

        if (audio.paused) {
            audio.play();
            icon.classList.replace('fa-play', 'fa-pause');
        } else {
            audio.pause();
            icon.classList.replace('fa-pause', 'fa-play');
        }
    }

    function initAudioPlayer(id) {
        const audio = document.getElementById(`audio-${id}`);
        const progressBar = audio.parentElement.querySelector('.progress');
        const progressBarContainer = audio.parentElement.querySelector('.progress-bar');
        const currentTime = audio.parentElement.querySelector('.current-time');
        const duration = audio.parentElement.querySelector('.duration');

        function updateProgress() {
            const percent = (audio.currentTime / audio.duration) * 100;
            progressBar.style.width = `${percent}%`;
            currentTime.textContent = formatTime(audio.currentTime);
        }

        audio.addEventListener('timeupdate', updateProgress);

        progressBarContainer.addEventListener('click', (e) => {
            const rect = progressBarContainer.getBoundingClientRect();
            const pos = (e.clientX - rect.left) / progressBarContainer.offsetWidth;
            audio.currentTime = pos * audio.duration;
            updateProgress();
        });

        audio.addEventListener('ended', () => {
            const playButton = audio.parentElement.querySelector('.play-btn i');
            playButton.classList.replace('fa-pause', 'fa-play');
            progressBar.style.width = '0%';
            currentTime.textContent = '0:00';
        });

        audio.addEventListener('loadedmetadata', () => {
            duration.textContent = formatTime(audio.duration);
        });

        currentTime.textContent = formatTime(0);
        if (audio.duration) {
            duration.textContent = formatTime(audio.duration);
        }
    }

    function formatTime(seconds) {
        const minutes = Math.floor(seconds / 60);
        const remainingSeconds = Math.floor(seconds % 60);
        return `${minutes}:${remainingSeconds.toString().padStart(2, '0')}`;
    }

    window.onclick = function(event) {
        if (event.target.classList.contains('backdrop-blur-sm')) {
            const modal = event.target.parentElement;
            const articleId = modal.id.split('-')[1];
            closeModal(articleId);
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('audio').forEach(audio => {
            const id = audio.id.replace('audio-', '');
            initAudioPlayer(id);
        });
    });
</script>
</body>
</html>

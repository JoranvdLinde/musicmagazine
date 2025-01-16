<?php
session_start();
require_once 'config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $identifier = trim($_POST['identifier']);
    $password = trim($_POST['password']);
    
    if (empty($identifier) || empty($password)) {
        $loginError = "All fields are required";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM music_users WHERE username = ? OR email = ?");
            $stmt->execute([$identifier, $identifier]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                session_regenerate_id(true);
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                
                header('Location: home.php');
                exit();
            } else {
                $loginError = "Invalid credentials";
            }
        } catch (PDOException $e) {
            $loginError = "An error occurred. Please try again.";
            error_log($e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Music Magazine</title>
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
    <div class="max-w-md mx-auto mt-20 p-6 bg-magazine-card rounded-xl shadow-lg">
        <h2 class="text-3xl font-bold mb-6 text-magazine-accent">Login</h2>
        <?php if (isset($loginError)): ?>
            <div class="bg-red-500/20 border border-red-500 text-white p-3 rounded-lg mb-4">
                <?php echo $loginError; ?>
            </div>
        <?php endif; ?>
        <form method="POST" class="space-y-4">
            <input type="text" name="identifier" placeholder="Email or Username" required 
                   class="w-full p-3 bg-magazine-dark border border-gray-700 rounded-lg focus:ring-2 focus:ring-magazine-accent focus:outline-none">
            <input type="password" name="password" placeholder="Password" required 
                   class="w-full p-3 bg-magazine-dark border border-gray-700 rounded-lg focus:ring-2 focus:ring-magazine-accent focus:outline-none">
            <button type="submit" 
                    class="w-full bg-magazine-accent hover:bg-opacity-80 text-white py-3 rounded-lg font-bold transition duration-300">
                Login
            </button>
        </form>
        <p class="mt-4 text-gray-400">Don't have an account? 
            <a href="register.php" class="text-magazine-accent hover:text-magazine-secondary transition">Register here</a>
        </p>
    </div>
</body>
</html>

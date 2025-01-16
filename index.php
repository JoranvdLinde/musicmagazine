<?php
session_start();
require_once 'config/database.php';

if (isset($_SESSION['user_id'])) {
    header('Location: home.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Music Magazine</title>
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
                        'playfair': ['"Playfair Display"', 'serif'],
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
    <div class="flex flex-col items-center justify-center min-h-screen p-4">
        <div class="w-full max-w-sm">
            <h1 class="text-6xl font-bold mb-8 text-center bg-gradient-to-r from-magazine-accent to-magazine-secondary text-transparent bg-clip-text">
                Music Magazine
            </h1>
            <div class="space-y-4">
                <a href="login.php" 
                   class="block w-full bg-magazine-accent text-white text-center py-3 rounded-lg font-bold hover:bg-opacity-80 transition duration-300">
                    Log in
                </a>
                <a href="register.php" 
                   class="block w-full bg-magazine-card border border-magazine-accent text-center py-3 rounded-lg font-bold hover:bg-magazine-card/50 transition duration-300">
                    Sign up
                </a>
            </div>
        </div>
    </div>
</body>
</html>

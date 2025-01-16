<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'manager') {
    header('Location: home.php');
    exit();
}

switch ($_POST['action']) {
    case 'update_role':
        $stmt = $pdo->prepare("UPDATE music_users SET role = ? WHERE id = ?");
        $stmt->execute([$_POST['new_role'], $_POST['user_id']]);
        break;
        
    case 'delete_user':
        // Delete user's articles first
        $stmt = $pdo->prepare("DELETE FROM magazine_articles WHERE user_id = ?");
        $stmt->execute([$_POST['user_id']]);
        
        // Delete user's profile
        $stmt = $pdo->prepare("DELETE FROM music_profiles WHERE user_id = ?");
        $stmt->execute([$_POST['user_id']]);
        
        // Delete user
        $stmt = $pdo->prepare("DELETE FROM music_users WHERE id = ?");
        $stmt->execute([$_POST['user_id']]);
        break;
        
    case 'delete_article':
        $stmt = $pdo->prepare("DELETE FROM magazine_articles WHERE id = ?");
        $stmt->execute([$_POST['article_id']]);
        break;
}

header('Content-Type: application/json');
echo json_encode(['success' => true]);

<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'manager') {
    header('Location: home.php');
    exit();
}

$article_id = $_POST['article_id'];
$title = $_POST['title'];
$content = $_POST['content'];

$sql = "UPDATE magazine_articles SET title = ?, content = ?";
$params = [$title, $content];

// Handle image upload
if (!empty($_FILES['new_image']['name'])) {
    $image_path = 'uploads/' . uniqid() . '_' . $_FILES['new_image']['name'];
    move_uploaded_file($_FILES['new_image']['tmp_name'], $image_path);
    $sql .= ", image_url = ?";
    $params[] = $image_path;
}

// Handle music upload
if (!empty($_FILES['new_music']['name'])) {
    $music_path = 'uploads/' . uniqid() . '_' . $_FILES['new_music']['name'];
    move_uploaded_file($_FILES['new_music']['tmp_name'], $music_path);
    $sql .= ", music_url = ?";
    $params[] = $music_path;
}

$sql .= " WHERE id = ?";
$params[] = $article_id;

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

header('Location: manager_dashboard.php');

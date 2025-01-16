<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $bio = trim($_POST['bio']);
        $profile_image = null;
        
        error_log("POST request received");
        
        if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
            error_log("File upload detected");
            
            $uploadDir = 'uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            $fileExtension = strtolower(pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION));
            $newFileName = uniqid() . '.' . $fileExtension;
            $uploadFile = $uploadDir . $newFileName;
            
            error_log("Attempting to upload file to: " . $uploadFile);
            
            if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $uploadFile)) {
                $profile_image = $uploadFile;
                error_log("File successfully uploaded to: " . $profile_image);
                
                $stmt = $pdo->prepare("UPDATE music_profiles SET profile_image = ? WHERE user_id = ?");
                $stmt->execute([$profile_image, $_SESSION['user_id']]);
                
                if ($stmt->rowCount() === 0) {
                    $stmt = $pdo->prepare("INSERT INTO music_profiles (user_id, profile_image, bio) VALUES (?, ?, ?)");
                    $stmt->execute([$_SESSION['user_id'], $profile_image, $bio]);
                }
                
                $_SESSION['success'] = "Profile photo updated successfully!";
            } else {
                error_log("Error uploading file");
                $_SESSION['error'] = "Error uploading image.";
            }
        }
        
        if ($bio !== null) {
            $stmt = $pdo->prepare("UPDATE music_profiles SET bio = ? WHERE user_id = ?");
            $result = $stmt->execute([$bio, $_SESSION['user_id']]);
            
            if ($stmt->rowCount() === 0) {
                $stmt = $pdo->prepare("INSERT INTO music_profiles (user_id, bio) VALUES (?, ?)");
                $stmt->execute([$_SESSION['user_id'], $bio]);
            }
        }
        
    } catch (Exception $e) {
        error_log("Database error: " . $e->getMessage());
        $_SESSION['error'] = "Could not update profile: " . $e->getMessage();
    }
}

header('Location: profile.php');
exit();

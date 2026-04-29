<?php
session_start();

// Bouncer Protocol: Admin only
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: login.php");
    exit();
}

include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add') {
    $theme = $_POST['theme'] ?? 'General';
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    
    // Default styling based on theme
    $borderColor = 'border-primary';
    $colorClass = 'text-primary';
    if ($theme == 'Urban Dev') {
        $borderColor = 'border-primary';
        $colorClass = 'text-primary';
    } elseif ($theme == 'Transport') {
        $borderColor = 'border-tertiary-fixed';
        $colorClass = 'text-tertiary';
    } elseif ($theme == 'Alert') {
        $borderColor = 'border-red-500';
        $colorClass = 'text-red-600';
    }
    
    $image_path = NULL;
    
    // Handle image upload
    if (isset($_FILES['announcement_image']) && $_FILES['announcement_image']['error'] == 0) {
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0755, true);
        }
        $target_file = $target_dir . time() . '_' . basename($_FILES["announcement_image"]["name"]);
        
        $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
        $allowed_types = ['jpg', 'png', 'jpeg', 'gif', 'webp'];
        
        if (in_array($imageFileType, $allowed_types)) {
            if (move_uploaded_file($_FILES["announcement_image"]["tmp_name"], $target_file)) {
                $image_path = $target_file;
            }
        }
    }
    
    if(!empty($title) && !empty($description)) {
        $stmt = $conn->prepare("INSERT INTO announcements (theme, title, description, borderColor, colorClass, image_path) VALUES (?, ?, ?, ?, ?, ?)");
        if($stmt) {
            $stmt->bind_param("ssssss", $theme, $title, $description, $borderColor, $colorClass, $image_path);
            $stmt->execute();
            $stmt->close();
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'delete') {
    $id = $_POST['announcement_id'] ?? 0;
    
    if($id > 0) {
        $stmt = $conn->prepare("DELETE FROM announcements WHERE id = ?");
        if($stmt) {
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();
        }
    }
}

header("Location: admin_announcements.php");
exit();
?>

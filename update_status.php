<?php
// update_status.php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $complaint_id = intval($_POST['complaint_id'] ?? 0);
    $status = $_POST['status'] ?? '';
    
    if ($complaint_id > 0 && in_array($status, ['Pending', 'In Progress', 'Resolved', 'Rejected'])) {
        $proof_path = null;
        
        // Handle file upload unconditionally if status is Resolved
        if ($status === 'Resolved') {
            if (isset($_FILES['proof_image']) && $_FILES['proof_image']['error'] === UPLOAD_ERR_OK) {
                $ext = pathinfo($_FILES['proof_image']['name'], PATHINFO_EXTENSION);
                $allowed = ['jpg','jpeg','png','gif','webp'];
                if (!in_array(strtolower($ext), $allowed)) die("Invalid image extension.");
                
                // Ensure directory exists
                if (!file_exists('uploads/proof')) {
                    mkdir('uploads/proof', 0777, true);
                }
                
                $filename = 'proof_' . $complaint_id . '_' . time() . '.' . $ext;
                $target_path = 'uploads/proof/' . $filename;
                
                if (move_uploaded_file($_FILES['proof_image']['tmp_name'], $target_path)) {
                    $proof_path = $target_path;
                } else {
                    die("Warning: System failed to save evidence image.");
                }
            } else {
                die("CRITICAL FAILURE: Administrator must upload an image to forcefully resolve a track. Compliance requirement.");
            }
        }
        
        // Atomic transaction guarantees DB integrity
        if(isset($conn) && !$conn->connect_error) {
            $conn->begin_transaction();
            try {
                // Main update
                $stmt1 = $conn->prepare("UPDATE complaints SET status = ? WHERE id = ?");
                $stmt1->bind_param("si", $status, $complaint_id);
                $stmt1->execute();
                
                // Dynamic logging insertion
                $stmt2 = $conn->prepare("INSERT INTO complaint_updates (complaint_id, status, proof_image, admin_id) VALUES (?, ?, ?, 1)");
                $stmt2->bind_param("iss", $complaint_id, $status, $proof_path);
                $stmt2->execute();
                
                $conn->commit();
            } catch (Exception $e) {
                $conn->rollback();
                die("-- FATAL: Database failure logging evidence update -- : " . $e->getMessage());
            }
        }
    }
}

// Redirect back cleanly
header("Location: admin.php");
exit();
?>

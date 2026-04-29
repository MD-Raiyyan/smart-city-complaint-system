<?php
// delete_complaint.php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: dashboard.php");
    exit();
}

include 'db_connect.php';

$complaint_id = intval($_GET['id']);
$user_id = $_SESSION['user_id'];

if(isset($conn) && !$conn->connect_error) {
    // Security Check: Only allow deletion if the complaint belongs to the user AND is not Resolved/Rejected
    $stmt = $conn->prepare("SELECT status FROM complaints WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $complaint_id, $user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    
    if($res->num_rows === 1) {
        $complaint = $res->fetch_assoc();
        if(!in_array($complaint['status'], ['Resolved', 'Rejected'])) {
            // Safe to delete
            $del_stmt = $conn->prepare("DELETE FROM complaints WHERE id = ? AND user_id = ?");
            $del_stmt->bind_param("ii", $complaint_id, $user_id);
            $del_stmt->execute();
            $del_stmt->close();
        }
    }
    $stmt->close();
}

header("Location: dashboard.php");
exit();
?>

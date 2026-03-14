<?php
require "../config/database.php";

if(isset($_POST['id'])) {
    $id = (int)$_POST['id'];
    
    // Database se status fetch karein
    $query = $conn->query("SELECT status FROM seats WHERE id = $id");
    $row = $query->fetch_assoc();
    $current = $row['status'];
    
    // Logic: Toggle between 'active' and 'inactive'
    $new_status = ($current == 'active') ? 'inactive' : 'active';
    
    // Database Update
    if($conn->query("UPDATE seats SET status = '$new_status' WHERE id = $id")) {
        echo json_encode(['status' => 'success', 'new_status' => $new_status]);
    } else {
        echo json_encode(['status' => 'error']);
    }
}
?>
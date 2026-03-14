<?php
require "../config/database.php";

$response = ['status' => 'error', 'message' => 'Invalid Request'];

if(isset($_POST['seat_number'])){
    $seat = $_POST['seat_number'];
    $room = $_POST['room'];

    // Pehle check karein ki seat exist toh nahi karti (Optionally)
    $stmt = $conn->prepare("INSERT INTO seats (seat_number, room) VALUES (?, ?)");
    $stmt->bind_param("is", $seat, $room);

    if($stmt->execute()){
        $response['status'] = 'success';
        $response['message'] = "Seat $seat in Room $room added successfully!";
    } else {
        $response['status'] = 'error';
        $response['message'] = "Database error: Could not save seat.";
    }
}

echo json_encode($response); // JSON format mein response dena zaruri hai
?>
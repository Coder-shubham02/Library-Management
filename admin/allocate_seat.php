<?php
require "config.php";

if(isset($_POST['student_id'])){

    $student = $_POST['student_id'];
    $seat = $_POST['seat_id'];
    $shift = $_POST['shift_id'];

    $date = date("Y-m-d");

    $check = $conn->prepare("SELECT id FROM seat_allocations 
    WHERE seat_id=? AND shift_id=? AND status='active'");

    $check->bind_param("ii",$seat,$shift);
    $check->execute();
    $result = $check->get_result();

    if($result->num_rows > 0){

        echo "Seat already occupied in this shift";

    }else{

        $stmt = $conn->prepare("INSERT INTO seat_allocations
        (seat_id,shift_id,student_id,allot_date)
        VALUES (?,?,?,?)");

        $stmt->bind_param("iiis",$seat,$shift,$student,$date);

        if($stmt->execute()){
            echo "Seat allocated successfully";
        }else{
            echo "Error allocating seat";
        }

    }

}
?>
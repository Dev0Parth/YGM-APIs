<?php
    $conn = mysqli_connect("localhost", "root", "");
    mysqli_select_db($conn, "ygm");

    if(!$conn) {
        die("connection failed!");
    }

    $empId = trim($_POST['empId']);
    $date = trim($_POST['date']);

    $qry1 = "select * from employee_work_and_leave where empId='$empId' and fromDate='$date'";
    $result = mysqli_query($conn, $qry1);

    if(!$result) {
        echo "Error: " . mysqli_error($conn);
    }

    if(mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_array($result)) {
            $data[] = $row;
        }
        print(json_encode($data));
    } else {
        $data[] = array("errorCode" => "404", "status" => "not found");
        print(json_encode($data));
    }
?>
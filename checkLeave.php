<?php
    $conn = mysqli_connect("localhost", "root", "");
    mysqli_select_db($conn, "ygm");

    if(!$conn) {
        die("connection failed!");
    }

    $Emp_Code = trim($_POST['Emp_Code']);
    $Date = trim($_POST['Date']);

    $qry1 = "select * from employee_work_and_leave where Emp_Code='$Emp_Code' and From_Date='$Date'";
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
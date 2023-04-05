<?php
    $conn = mysqli_connect("localhost", "root", "");
    mysqli_select_db($conn, "ygm");

    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }

    $androidId = trim($_POST['androidId']);

    $qry1 = "select empId, department, name, phone  from employees where androidId='$androidId'";
    $result = mysqli_query($conn, $qry1);

    $response = array();

    if(mysqli_num_rows($result) > 0) {
        while($row = mysqli_fetch_assoc($result)) {
            $response = $row;
        }
        echo json_encode($response);
    } else {
        $arr = array("errorcode" => "404", "message" => "user not found");
        echo json_encode($arr);
    }
?>
<?php
    $conn = mysqli_connect("localhost", "root", "");
    mysqli_select_db($conn, "ygm");

    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }

    $Gsf_Id = trim($_POST['Gsf_Id']);

    $qry1 = "select *  from employees where Gsf_Id='$Gsf_Id'";
    $result = mysqli_query($conn, $qry1);

    $response = array();

    if(mysqli_num_rows($result) > 0) {
        while($row = mysqli_fetch_assoc($result)) {
            $response = $row;
        }
    } else {
        $response = array("errorcode" => "404", "message" => "user not found");
    }

    print(json_encode($response));
?>
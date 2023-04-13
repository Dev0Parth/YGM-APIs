<?php

require_once 'email_helper.php';

$conn = mysqli_connect("localhost", "root", "");
mysqli_select_db($conn, "ygm");

if (!$conn) {
    die("connection failed!");
}


$Emp_Code = trim($_POST['Emp_Code']);
$Name = trim($_POST['Name']);
$Department = trim($_POST['Department']);
$Date = trim($_POST['Date']);
$Status = trim($_POST['Status']);
$First_Half_Work = trim($_POST['First_Half_Work']);
$Second_Half_Work = trim($_POST['Second_Half_Work']);
$Scoping = trim($_POST['Scoping']);
$Created_At = trim($_POST['Created_At']);

$qry1 = "select Sr_No from employee_work_and_leave where Emp_Code = '$Emp_Code' and From_Date = '$Date' and To_Date = '$Date'";
$res = mysqli_query($conn, $qry1);

if (mysqli_num_rows($res) > 0) {
    $qry2 = "update employee_work_and_leave set Status = '$Status', First_Half_Work = '$First_Half_Work', Second_Half_Work = '$Second_Half_Work', Scoping = '$Scoping', Created_At = '$Created_At' where Emp_Code = '$Emp_Code' and From_Date = '$Date' and To_Date = '$Date'";
    $res2 = mysqli_query($conn, $qry2);
} else {
    $qry2 = "insert into employee_work_and_leave (`Emp_Code`, `Name`, `Department`, `From_Date`, `To_Date`, `Status`, `First_Half_Work`, `Second_Half_Work`, `Scoping`, `Leave_Type`, `Leave_Reason`, `Created_At`) VALUES ('$Emp_Code', '$Name', '$Department', '$Date', '$Date', '$Status', '$First_Half_Work', '$Second_Half_Work', '$Scoping', '-', '-', '$Created_At')";
    $res2 = mysqli_query($conn, $qry2);
}


if (!$res2) {
    echo "Error: " . mysqli_error($conn);
}

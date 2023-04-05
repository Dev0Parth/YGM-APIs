<?php

require_once 'email_helper.php';

$conn = mysqli_connect("localhost", "root", "");
mysqli_select_db($conn, "ygm");

if (!$conn) {
    die("connection failed!");
}


$empId = trim($_POST['empId']);
$fullName = trim($_POST['fullName']);
$department = trim($_POST['department']);
$date = trim($_POST['date']);
$status = trim($_POST['status']);
$firstHalfWork = trim($_POST['firstHalfWork']);
$secondHalfWork = trim($_POST['secondHalfWork']);
$scoping = trim($_POST['scoping']);
$createdAt = trim($_POST['createdAt']);

$qry1 = "select id from employee_work_and_leave where empId = '$empId' and fromDate = '$date' and toDate = '$date'";
$res = mysqli_query($conn, $qry1);

if (mysqli_num_rows($res) > 0) {
    $qry2 = "update employee_work_and_leave set status = '$status', firstHalfWork = '$firstHalfWork', secondHalfWork = '$secondHalfWork', scoping = '$scoping', createdAt = '$createdAt' where empId = '$empId' and fromDate = '$date' and toDate = '$date'";
    $res2 = mysqli_query($conn, $qry2);
} else {
    $qry2 = "insert into employee_work_and_leave (`empId`, `fullName`, `department`, `fromDate`, `toDate`, `status`, `firstHalfWork`, `secondHalfWork`, `scoping`, `leaveType`, `leaveReason`, `createdAt`) VALUES ('$empId', '$fullName', '$department', '$date', '$date', '$status', '$firstHalfWork', '$secondHalfWork', '$scoping', '-', '-', '$createdAt')";
    $res2 = mysqli_query($conn, $qry2);
}


if (!$res2) {
    echo "Error: " . mysqli_error($conn);
}

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
$fromDate = trim($_POST['fromDate']);
$toDate = trim($_POST['toDate']);
$status = trim($_POST['status']);
$leaveType = trim($_POST['leaveType']);
$leaveReason = trim($_POST['leaveReason']);
$createdAt = trim($_POST['createdAt']);

$dateObj1 = DateTime::createFromFormat('d/m/Y', $fromDate);
$dateObj2 = DateTime::createFromFormat('d/m/Y', $toDate);

$interval = $dateObj1->diff($dateObj2);

$totalFullDays = $interval->format('%r%a') + 1;
$totalHalfDays = ($interval->format('%r%a') + 1) / 2;

if ($dateObj1 !== false && $dateObj2 !== false) {
    $endDate = clone $dateObj2;
    $endDate->modify('+1 day');
    for ($currentDate = clone $dateObj1; $currentDate < $endDate; $currentDate->modify('+1 day')) {
        $formattedDate = $currentDate->format("d/m/Y");
        $qry1 = "select id from employee_work_and_leave where empId = '$empId' and fromDate = '$formattedDate' and toDate = '$formattedDate'";
        $res = mysqli_query($conn, $qry1);

        if (mysqli_num_rows($res) > 0) {
            $qry2 = "update employee_work_and_leave set status = '$status', leaveType = '$leaveType', leaveReason = '$leaveReason', createdAt = '$createdAt' where empId = '$empId' and fromDate = '$formattedDate' and toDate = '$formattedDate'";
            $res2 = mysqli_query($conn, $qry2);
        } else {
            $qry2 = "insert into employee_work_and_leave (`empId`, `fullName`, `department`, `fromDate`, `toDate`, `status`, `firstHalfWork`, `secondHalfWork`, `scoping`, `leaveType`, `leaveReason`, `createdAt`) VALUES ('$empId', '$fullName', '$department', '$formattedDate', '$formattedDate', '$status', '-', '-', '-', '$leaveType', '$leaveReason', '$createdAt')";
            $res2 = mysqli_query($conn, $qry2);
        }

        if (!$res2) {
            echo "Error: " . mysqli_error($conn);
        }
    }
} else {
    echo "Invalid date format";
}


$html = '';
$html .= "<div>";
$html .= "<table>";
$html .= "<tr>";
$html .= "<td style='padding:10px;' colspan='2'><b>Name:</b> $fullName</td>";
$html .= "</tr>";
$html .= "<tr>";
$html .= "<td style='padding:10px;'><b>Leave From:</b> $fromDate</td>";
$html .= "<td style='padding:10px;'><b>Leave To:</b> $toDate</td>";
$html .= "</tr>";
$html .= "<tr>";
if ($leaveType == "Full Leave") {
    $html .= "<td style='padding:10px;'><b>Full day/s:</b> $totalFullDays</td>";
    $html .= "<td style='padding:10px;'><b>Half day/s:</b> 0</td>";
    $html .= "</tr>";
    $html .= "<tr>";
    $html .= "<td style='padding:10px;' colspan='2'><b>Total requested day/s:</b> $totalFullDays</td>";
} else {
    $html .= "<td style='padding:10px;'><b>Full day/s:</b> 0</td>";
    $html .= "<td style='padding:10px;'><b>Half day/s:</b> $totalHalfDays</td>";
    $html .= "</tr>";
    $html .= "<tr>";
    $html .= "<td style='padding:10px;' colspan='2'><b>Total requested day/s:</b> $totalHalfDays</td>";
}

$html .= "</tr>";
$html .= "<tr>";
$html .= "<td style='padding:10px;' colspan='2'><b>Reason for leave:</b> {$leaveReason}</td>";
$html .= "</tr>";

$html .= "</table>";
$html .= "</div>";


send_email($department, "For Leave", $html);
echo "Email sent.";

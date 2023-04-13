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
$From_Date = trim($_POST['From_Date']);
$To_Date = trim($_POST['To_Date']);
$Status = trim($_POST['Status']);
$Leave_Type = trim($_POST['Leave_Type']);
$Leave_Reason = trim($_POST['Leave_Reason']);
$Created_At = trim($_POST['Created_At']);

$dateObj1 = DateTime::createFromFormat('d/m/Y', $From_Date);
$dateObj2 = DateTime::createFromFormat('d/m/Y', $To_Date);

$interval = $dateObj1->diff($dateObj2);

$totalFullDays = $interval->format('%r%a') + 1;
$totalHalfDays = ($interval->format('%r%a') + 1) / 2;

if ($dateObj1 !== false && $dateObj2 !== false) {
    $endDate = clone $dateObj2;
    $endDate->modify('+1 day');
    for ($currentDate = clone $dateObj1; $currentDate < $endDate; $currentDate->modify('+1 day')) {
        $formattedDate = $currentDate->format("d/m/Y");
        $qry1 = "select Sr_No from employee_work_and_leave where Emp_Code = '$Emp_Code' and From_Date = '$formattedDate' and To_Date = '$formattedDate'";
        $res = mysqli_query($conn, $qry1);

        if (mysqli_num_rows($res) > 0) {
            $qry2 = "update employee_work_and_leave set Status = '$Status', Leave_Type = '$Leave_Type', Leave_Reason = '$Leave_Reason', Created_At = '$Created_At' where Emp_Code = '$Emp_Code' and From_Date = '$formattedDate' and To_Date = '$formattedDate'";
            $res2 = mysqli_query($conn, $qry2);
        } else {
            $qry2 = "insert into employee_work_and_leave (`Emp_Code`, `Name`, `Department`, `From_Date`, `To_Date`, `Status`, `First_Half_Work`, `Second_Half_Work`, `Scoping`, `Leave_Type`, `Leave_Reason`, `Created_At`) VALUES ('$Emp_Code', '$Name', '$Department', '$formattedDate', '$formattedDate', '$Status', '-', '-', '-', '$Leave_Type', '$Leave_Reason', '$Created_At')";
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
$html .= "<td style='padding:10px;' colspan='2'><b>Name:</b> $Name</td>";
$html .= "</tr>";
$html .= "<tr>";
$html .= "<td style='padding:10px;'><b>Leave From:</b> $From_Date</td>";
$html .= "<td style='padding:10px;'><b>Leave To:</b> $To_Date</td>";
$html .= "</tr>";
$html .= "<tr>";
if ($Leave_Type == "Full Leave") {
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
$html .= "<td style='padding:10px;' colspan='2'><b>Reason for leave:</b> {$Leave_Reason}</td>";
$html .= "</tr>";

$html .= "</table>";
$html .= "</div>";


send_email($Department, "For Leave", $html);
echo "Email sent.";

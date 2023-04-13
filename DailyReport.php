<?php

require_once 'email_helper.php';

$date = new DateTime('now');
$dateString = $date->format('d/m/Y');

$conn = mysqli_connect("localhost", "root", "");
mysqli_select_db($conn, "ygm");


findEmployee($conn, $dateString);
emailForHOD($conn, $dateString, "accounts");
emailForHOD($conn, $dateString, "admin");
emailForHOD($conn, $dateString, "cash");
emailForHOD($conn, $dateString, "hr");
emailForHOD($conn, $dateString, "it");
emailForHOD($conn, $dateString, "mim");
emailForHOD($conn, $dateString, "mis");
emailForHOD($conn, $dateString, "operation");
emailForHOD($conn, $dateString, "recon");
emailForHOD($conn, $dateString, "sales");
emailForHOD($conn, $dateString, "service");

function addNoUpdate($conn, $Emp_Code, $Name, $Department, $dateString) {
    $addQry = "INSERT INTO employee_work_and_leave (`Emp_Code`, `Name`, `Department`, `From_Date`, `To_Date`, `First_Half_Work`, `Second_Half_Work`, `Scoping`, `Leave_Type`, `Leave_Reason`) VALUES ('$Emp_Code', '$Name', '$Department', '$dateString', '$dateString', 'no update', 'no update', 'no update', '-', '-')";
    $addResult = mysqli_query($conn, $addQry);
    if (!$addResult) {
        echo "data not inserted:" . mysqli_error($conn);
    }
}

function findEmployee($conn, $dateString) {

    $findQry = "SELECT employees.Emp_Code, employees.Name, employees.Department 
            FROM employees 
            LEFT JOIN employee_work_and_leave ON employees.Emp_Code = employee_work_and_leave.Emp_Code AND employee_work_and_leave.From_Date != $dateString
            WHERE employee_work_and_leave.Emp_Code IS NULL";

    $findResult = mysqli_query($conn, $findQry);

    if (mysqli_num_rows($findResult) > 0) {
        while ($row = mysqli_fetch_assoc($findResult)) {
            $Emp_Code = $row['Emp_Code'];
            $Name = $row['Name'];
            $Department = $row['Department'];

            echo json_encode($row);

            addNoUpdate($conn, $Emp_Code, $Name, $Department, $dateString);
        }
    }
}

function emailForHOD($conn, $dateString, $Department) {
    $qry = "SELECT DISTINCT employee_work_and_leave.*, employees.Contact_No
                FROM employee_work_and_leave
                JOIN employees ON employee_work_and_leave.Emp_Code = employees.Emp_Code
                WHERE employee_work_and_leave.Department = '$Department' AND employee_work_and_leave.From_Date = '$dateString'
                ORDER BY employee_work_and_leave.Emp_Code ASC";

    $res = mysqli_query($conn, $qry);
    
    if (!$res) {
        echo "Error: " . mysqli_error($conn);
    }
    
    if (mysqli_num_rows($res) > 0) {
        $employees = array();
        while ($row = mysqli_fetch_assoc($res)) {
            $Name = $row['Name'];
            $Emp_Code = $row['Emp_Code'];
            $employees[$Name][] = $row;
        }

        $html = '';
        $html .= "<div>";
        $html .= "<table border='1'>";
        $html .= "<tr>";
        $html .= "<td style='padding:10px; text-align:center; font-weight: 800;'>Sr No</td>";
        $html .= "<td style='padding:10px; text-align:center; font-weight: 800;'>Emp Code</td>";
        $html .= "<td style='padding:10px; text-align:center; font-weight: 800;'>Name</td>";
        $html .= "<td style='padding:10px; text-align:center; font-weight: 800;'>Contact No</td>";
        $html .= "<td style='padding:10px; text-align:center; font-weight: 800;'>Status</td>";
        $html .= "<td style='padding:10px; text-align:center; font-weight: 800;'>First Half</td>";
        $html .= "<td style='padding:10px; text-align:center; font-weight: 800;'>Second Half</td>";
        $html .= "<td style='padding:10px; text-align:center; font-weight: 800;'>Scoping</td>";
        $html .= "</tr>";
        $srNo = 0;

        foreach ($employees as $Name => $entries) {

            foreach ($entries as $entry) {
                $srNo++;
                $html .= "<tr>";
                $html .= "<td style='padding:10px; text-align:center;'>{$srNo}</td>";
                $html .= "<td style='padding:10px; text-align:center;'>{$entry['Emp_Code']}</td>";
                $html .= "<td style='padding:10px; text-align:center;'>{$entry['Name']}</td>";
                $html .= "<td style='padding:10px; text-align:center;'>{$entry['Contact_No']}</td>";
                $html .= "<td style='padding:10px; text-align:center;'>{$entry['Status']}</td>";
                $html .= "<td style='padding:10px; text-align:center;'>{$entry['First_Half_Work']}</td>";
                $html .= "<td style='padding:10px; text-align:center;'>{$entry['Second_Half_Work']}</td>";
                $html .= "<td style='padding:10px; text-align:center;'>{$entry['Scoping']}</td>";
                $html .= "</tr>";
            }
            
        }
        $html .= "</table>";
        $html .= "</div>";

        
        send_email($Department, "Daily Work Report", $html);
        echo "mail sent.";
    }
}

mysqli_close($conn);

<?php

    require_once 'email_helper.php';

    $conn = mysqli_connect("localhost", "root", "");
    mysqli_select_db($conn, "ygm");


    sendMonthlyReport($conn, 'accounts');
    sendMonthlyReport($conn, 'admin');
    sendMonthlyReport($conn, 'cash');
    sendMonthlyReport($conn, 'hr');
    sendMonthlyReport($conn, 'it');
    sendMonthlyReport($conn, 'mim');
    sendMonthlyReport($conn, 'mis');
    sendMonthlyReport($conn, 'operation');
    sendMonthlyReport($conn, 'recon');
    sendMonthlyReport($conn, 'sales');
    sendMonthlyReport($conn, 'service');


    function sendMonthlyReport($conn, $Department) {
        $currentMonthYear = date('F Y');
        
        $sql1 = "select Emp_Code, Name, Department,
                    coalesce(sum(case when First_Half_Work not in ('-', 'no update') and Second_Half_Work not in ('-', 'no update') then 1 else 0 end), 0) as 'Present Days',
                    coalesce(sum(case when Leave_Type in ('First Half', 'Second Half') then 0.5 else 0 end), 0) as 'Half Leave Days',
                    coalesce(sum(case when Leave_Type = 'Full Leave' then 1 else 0 end), 0) as 'Full Day Leave Days'
                from employee_work_and_leave
                where Department = '$Department' and MONTH(STR_TO_DATE(From_Date, '%d/%m/%Y')) = MONTH(CURRENT_DATE) and YEAR(STR_TO_DATE(From_Date, '%d/%m/%Y')) = YEAR(CURRENT_DATE)
                group by Emp_Code";

        $result = mysqli_query($conn, $sql1);

        if (!$result) {
            echo "Error: " . mysqli_error($conn);
        }


        $html = '';
        $html .= "<div>";
        $html .= "<table border='1'>";
        $html .= "<tr>";
        $html .= "<td style='padding:10px; text-align:center; font-weight: 800;'>Sr No</td>";
        $html .= "<td style='padding:10px; text-align:center; font-weight: 800;'>Emp Code</td>";
        $html .= "<td style='padding:10px; text-align:center; font-weight: 800;'>Name</td>";
        $html .= "<td style='padding:10px; text-align:center; font-weight: 800;'>Department</td>";
        $html .= "<td style='padding:10px; text-align:center; font-weight: 800;'>Contact No</td>";
        $html .= "<td style='padding:10px; text-align:center; font-weight: 800;'>Attendance</td>";
        $html .= "</tr>";
        $srNo = 0;
        while ($row = mysqli_fetch_assoc($result)) {

            $qry2 = "select * from employees where Emp_Code='{$row['Emp_Code']}' && Department='{$row['Department']}'";
            $result2 = mysqli_query($conn, $qry2);
            if (!$result) {
                echo "Error: " . mysqli_error($conn);
            }

            while($row2 = mysqli_fetch_assoc($result2)) {
                $srNo++;

                $totalDays = $row['Half Leave Days'] + $row['Full Day Leave Days'];
                $html .= "<tr>";
                $html .= "<td style='padding:10px; text-align:center;'>{$srNo}</td>";
                $html .= "<td style='padding:10px; text-align:center;'>{$row['Emp_Code']}</td>";
                $html .= "<td style='padding:10px; text-align:center;'>{$row['Name']}</td>";
                $html .= "<td style='padding:10px; text-align:center;'>{$row['Department']}</td>";
                $html .= "<td style='padding:10px; text-align:center;'>{$row2['Contact_No']}</td>";
                $html .= "<td style='padding:10px; text-align:center;'>{$totalDays}</td>";
                // if ($row['Half Leave Dates'] != null) {
                //     $html .= "<td style='padding:10px; text-align:center;'>";
                //     $halfLeaveDates = $row['Half Leave Dates'];
                //     $halfDates = '';
                //     $datesArray = explode(',', $halfLeaveDates);
                //     foreach ($datesArray as $date) {
                //         $dateComponents = explode('/', $date);
                //         $dayOfMonth = $dateComponents[0];
                //         $halfDates .= $dayOfMonth . ', ';
                //     }
                //     $halfDates = rtrim($halfDates, ', ');
                //     $html .= $halfDates;
                //     $html .= "</td>";
                // } else {
                //     $html .= "<td style='padding:10px; text-align:center;'>-</td>";
                // }

                // if ($row['Full Day Leave Dates'] != null) {
                //     $html .= "<td style='padding:10px; text-align:center;'>";
                //     $fullLeaveDates = $row['Full Day Leave Dates'];
                //     $fullDates = '';
                //     $datesArray1 = explode(',', $fullLeaveDates);
                //     foreach ($datesArray1 as $date1) {
                //         $dateComponents1 = explode('/', $date1);
                //         $dayOfMonth1 = $dateComponents1[0];
                //         $fullDates .= $dayOfMonth1 . ', ';
                //     }
                //     $fullDates = rtrim($fullDates, ', ');
                //     $html .= $fullDates;
                //     $html .= "</td>";
                // } else {
                //     $html .= "<td style='padding:10px; text-align:center;'>-</td>";
                // }
                $html .= "</tr>";
            }
        }
        $html .= "</table>";
        $html .= "</div>";

        

        // sendEmail($email_Hod, $html);
        if (send_email($Department, "Monthly Employee Attendance Report", $html)) {
            echo "Email sent successfully";
        } else {
            echo "Failed to send email.";
        }
    }

<?php

    require_once 'email_helper.php';

    $conn = mysqli_connect("localhost", "root", "");
    mysqli_select_db($conn, "ygm");


    sendMonthlyReport($conn, 'tech');
    sendMonthlyReport($conn, 'sales');


    function sendMonthlyReport($conn, $department) {
        $currentMonthYear = date('F Y');
        
        $sql1 = "SELECT empId, fullName,
                    COALESCE(SUM(CASE WHEN firstHalfWork NOT IN ('-', 'no update') AND secondHalfWork NOT IN ('-', 'no update') THEN 1 ELSE 0 END), 0) AS 'Present Days',
                    COALESCE(SUM(CASE WHEN leaveType IN ('First Half', 'Second Half') THEN 0.5 ELSE 0 END), 0) AS 'Half Leave Days',
                    COALESCE(SUM(CASE WHEN leaveType = 'Full Leave' THEN 1 ELSE 0 END), 0) AS 'Full Day Leave Days',
                    GROUP_CONCAT(DISTINCT CASE WHEN leaveType IN ('First Half', 'Second Half') THEN fromDate ELSE NULL END ORDER BY fromDate) AS 'Half Leave Dates',
                    GROUP_CONCAT(CASE WHEN leaveType = 'Full Leave' THEN fromDate ELSE NULL END ORDER BY fromDate) AS 'Full Day Leave Dates'
                FROM employee_work_and_leave
                WHERE department = '$department' AND MONTH(STR_TO_DATE(fromDate, '%d/%m/%Y')) = MONTH(CURRENT_DATE) AND YEAR(STR_TO_DATE(fromDate, '%d/%m/%Y')) = YEAR(CURRENT_DATE)
                GROUP BY empId";

        $result = mysqli_query($conn, $sql1);

        if (!$result) {
            echo "Error: " . mysqli_error($conn);
        }


        $html = '';
        $html .= "<div>";
        $html .= "<table border='1'>";
        $html .= "<tr>";
        $html .= "<td style='padding:10px; text-align:center;' colspan='8'><b>$currentMonthYear</b></td>";
        $html .= "</tr>";
        $html .= "<tr>";
        $html .= "<td style='padding:10px; text-align:center;'>Emp Id</td>";
        $html .= "<td style='padding:10px; text-align:center;'>Name</td>";
        $html .= "<td style='padding:10px; text-align:center;'>Total Days</td>";
        $html .= "<td style='padding:10px; text-align:center;'>Present Days</td>";
        $html .= "<td style='padding:10px; text-align:center;'>Half Leave Days</td>";
        $html .= "<td style='padding:10px; text-align:center;'>Full Leave Days</td>";
        $html .= "<td style='padding:10px; text-align:center;'>Half Leave Dates</td>";
        $html .= "<td style='padding:10px; text-align:center;'>Full Leave Dates</td>";
        $html .= "</tr>";
        while ($row = mysqli_fetch_assoc($result)) {

            $totalPresentDays = $row['Present Days'];
            $totalDays = $totalPresentDays + $row['Half Leave Days'] + $row['Full Day Leave Days'];
            $html .= "<tr>";
            $html .= "<td style='padding:10px; text-align:center;'>{$row['empId']}</td>";
            $html .= "<td style='padding:10px; text-align:center;'>{$row['fullName']}</td>";
            $html .= "<td style='padding:10px; text-align:center;'>{$totalDays}</td>";
            $html .= "<td style='padding:10px; text-align:center;'>{$totalPresentDays}</td>";
            $html .= "<td style='padding:10px; text-align:center;'>{$row['Half Leave Days']}</td>";
            $html .= "<td style='padding:10px; text-align:center;'>{$row['Full Day Leave Days']}</td>";
            if ($row['Half Leave Dates'] != null) {
                $html .= "<td style='padding:10px; text-align:center;'>";
                $halfLeaveDates = $row['Half Leave Dates'];
                $halfDates = '';
                $datesArray = explode(',', $halfLeaveDates);
                foreach ($datesArray as $date) {
                    $dateComponents = explode('/', $date);
                    $dayOfMonth = $dateComponents[0];
                    $halfDates .= $dayOfMonth . ', ';
                }
                $halfDates = rtrim($halfDates, ', ');
                $html .= $halfDates;
                $html .= "</td>";
            } else {
                $html .= "<td style='padding:10px; text-align:center;'>-</td>";
            }

            if ($row['Full Day Leave Dates'] != null) {
                $html .= "<td style='padding:10px; text-align:center;'>";
                $fullLeaveDates = $row['Full Day Leave Dates'];
                $fullDates = '';
                $datesArray1 = explode(',', $fullLeaveDates);
                foreach ($datesArray1 as $date1) {
                    $dateComponents1 = explode('/', $date1);
                    $dayOfMonth1 = $dateComponents1[0];
                    $fullDates .= $dayOfMonth1 . ', ';
                }
                $fullDates = rtrim($fullDates, ', ');
                $html .= $fullDates;
                $html .= "</td>";
            } else {
                $html .= "<td style='padding:10px; text-align:center;'>-</td>";
            }
            $html .= "</tr>";
        }
        $html .= "</table>";
        $html .= "</div>";

        // sendEmail($email_Hod, $html);
        if (send_email($department, "Monthly Employee Attendance Report", $html)) {
            echo "Email sent successfully";
        } else {
            echo "Failed to send email.";
        }
    }

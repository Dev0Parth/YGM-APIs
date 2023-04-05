<?php

    require_once 'email_helper.php';

    $date = new DateTime('now');
    $dateString = $date->format('d/m/Y');

    $conn = mysqli_connect("localhost", "root", "");
    mysqli_select_db($conn, "ygm");


    findEmployee($conn, $dateString);
    emailForHOD($conn, $dateString, "tech");
    emailForHOD($conn, $dateString, "sales");
    // emailForTechHOD($conn, $dateString);
    // emailForSalesHOD($conn, $dateString);

    function addNoUpdate($conn, $empId, $fullName, $department, $dateString) {
        $addQry = "INSERT INTO employee_work_and_leave (`empId`, `fullName`, `department`, `fromDate`, `toDate`, `firstHalfWork`, `secondHalfWork`, `scoping`, `leaveType`, `leaveReason`) VALUES ('$empId', '$fullName', '$department', '$dateString', '$dateString', 'no update', 'no update', 'no update', '-', '-')";
        $addResult = mysqli_query($conn, $addQry);
        if(!$addResult) {
            echo "data not inserted:" . mysqli_error($conn);
        }
    }

    function findEmployee($conn, $dateString) {

        $findQry = "SELECT employees.empId, employees.name, employees.department 
            FROM employees 
            LEFT JOIN employee_work_and_leave ON employees.empId = employee_work_and_leave.empId AND employee_work_and_leave.fromDate != $dateString
            WHERE employee_work_and_leave.empId IS NULL";

        $findResult = mysqli_query($conn, $findQry);

        if (mysqli_num_rows($findResult) > 0) {
            while ($row = mysqli_fetch_assoc($findResult)) {
                $empId = $row['empId'];
                $fullName = $row['name'];
                $department = $row['department'];

                echo json_encode($row);

                addNoUpdate($conn, $empId, $fullName, $department, $dateString);
            }
        }
    }

    function emailForHOD($conn, $dateString, $department) {
        $qry = "select * from employee_work_and_leave where department='$department' and fromDate='$dateString' order by fullName ASC";
        $res = mysqli_query($conn, $qry);

        if (!$res) {
            echo "Error: " . mysqli_error($conn);
        }

        if (mysqli_num_rows($res) > 0) {
            $employees = array();

            while ($row = mysqli_fetch_assoc($res)) {
                $name = $row['fullName'];
                $empId = $row['empId'];
                $employees[$name][] = $row;
            }

            $html = '';
            $html .= "<div>";
            foreach ($employees as $name => $entries) {
                $html .= "<table style='border:1px solid #000000;margin-bottom:10px;'>";
                foreach ($entries as $entry) {
                    $html .= "<tr>";
                    $html .= "<td style='padding:10px;'><b>Name:</b> {$name}</td>";
                    $html .= "<td style='padding:10px;'><b>Date:</b> {$dateString}</td>";
                    $html .= "</tr>";
                    if ($entry['firstHalfWork'] == "-" && $entry['secondHalfWork'] == "-") {
                        $html .= "<tr>";
                        $html .= "<td style='padding:10px' colspan='2'><b>Status:</b> Full Leave</td>";
                        $html .= "</tr>";
                        $html .= "<tr>";
                        $html .= "<td style='padding:10px' colspan='2'><b>Reason:</b> {$entry['leaveReason']}</td>";
                        $html .= "</tr>";
                    } else if ($entry['firstHalfWork'] == "no update" && $entry['secondHalfWork'] == "no update") {
                        $html .= "<tr>";
                        $html .= "<td style='padding:10px' colspan='2'><b>Status:</b> No Update</td>";
                        $html .= "</tr>";
                        $html .= "<tr>";
                        $html .= "<td style='padding:10px' colspan='2'><b>Work:</b> No Update</td>";
                        $html .= "</tr>";
                    } else if ($entry['firstHalfWork'] == "-" && $entry['secondHalfWork'] != "-") {
                        $html .= "<tr>";
                        $html .= "<td style='padding:10px' colspan='2'><b>Status:</b> First Half Leave</td>";
                        $html .= "</tr>";
                        $html .= "<tr>";
                        $html .= "<td style='padding:10px' colspan='2'><b>Second Half Work:</b> {$entry['secondHalfWork']}</td>";
                        $html .= "</tr>";
                        if ($entry['scoping'] != null) {
                            $html .= "<tr>";
                            $html .= "<td style='padding:10px' colspan='2'><b>Scoping:</b> {$entry['scoping']}</td>";
                            $html .= "</tr>";
                        }
                        $html .= "<tr>";
                        $html .= "<td style='padding:10px' colspan='2'><b>Reason:</b> {$entry['leaveReason']}</td>";
                        $html .= "</tr>";
                    } else if ($entry['firstHalfWork'] != "-" && $entry['secondHalfWork'] == "-") {
                        $html .= "<tr>";
                        $html .= "<td style='padding:10px' colspan='2'><b>Status:</b> Second Half Leave</td>";
                        $html .= "</tr>";
                        $html .= "<tr>";
                        $html .= "<td style='padding:10px' colspan='2'><b>First Half Work:</b> {$entry['firstHalfWork']}</td>";
                        $html .= "</tr>";
                        if ($entry['scoping'] != null) {
                            $html .= "<tr>";
                            $html .= "<td style='padding:10px' colspan='2'><b>Scoping:</b> {$entry['scoping']}</td>";
                            $html .= "</tr>";
                        }
                        $html .= "<tr>";
                        $html .= "<td style='padding:10px' colspan='2'><b>Reason:</b> {$entry['leaveReason']}</td>";
                        $html .= "</tr>";
                    } else if ($entry['firstHalfWork'] != "-" && $entry['secondHalfWork'] != "-") {
                        $html .= "<tr>";
                        $html .= "<td style='padding:10px' colspan='2'><b>Status:</b> Present</td>";
                        $html .= "</tr>";
                        $html .= "<tr>";
                        $html .= "<td style='padding:10px' colspan='2'><b>First half Work:</b> {$entry['firstHalfWork']}</td>";
                        $html .= "</tr>";
                        $html .= "<tr>";
                        $html .= "<td style='padding:10px' colspan='2'><b>Second half Work:</b> {$entry['secondHalfWork']}</td>";
                        $html .= "</tr>";
                        if ($entry['scoping'] != null) {
                            $html .= "<tr>";
                            $html .= "<td style='padding:10px' colspan='2'><b>Scoping:</b> {$entry['scoping']}</td>";
                            $html .= "</tr>";
                        }
                    }
                }
                $html .= "</table>";
            }
            $html .= "</div>";

            send_email($department, "Daily Work Report", $html);
        }
    }

    // function emailForTechHOD($conn, $dateString) {
    //     $techQry = "select * from employee_work_and_leave where department='tech' and date='$dateString' order by fullName ASC";
    //     $techResult = mysqli_query($conn, $techQry);

    //     if(!$techResult) {
    //         echo "Error: " . mysqli_error($conn);
    //     }

    //     $techEmployees = array();

    //     while ($techRow = mysqli_fetch_assoc($techResult)) {
    //         $techName = $techRow['fullName'];
    //         $techEmpId = $techRow['empId'];
    //         $techEmployees[$techName][] = $techRow;
    //     }

    //     $html = '';
    //     $html .= "<div>";
    //     foreach ($techEmployees as $techName => $entries) {
    //         $html .= "<table style='border:1px solid #000000;margin-bottom:10px;'>";
    //         foreach ($entries as $entry) {
    //             $html .= "<tr>";
    //             $html .= "<td style='padding:10px;'><b>Name:</b> {$techName}</td>";
    //             $html .= "<td style='padding:10px;'><b>Date:</b> {$dateString}</td>";
    //             $html .= "</tr>";
    //             if($entry['type'] == "work") {
    //                 if ($entry['firstHalfWork'] == "-") {
    //                     $html .= "<tr>";
    //                     $html .= "<td style='padding:10px' colspan='2'><b>Status:</b> First Half Leave</td>";
    //                     $html .= "</tr>";
    //                     $html .= "<tr>";
    //                     $html .= "<td style='padding:10px' colspan='2'><b>Second Half Work:</b> {$entry['secondHalfWork']}</td>";
    //                     $html .= "</tr>";
    //                     if ($entry['scoping'] != null) {
    //                         $html .= "<tr>";
    //                         $html .= "<td style='padding:10px' colspan='2'><b>Scoping:</b> {$entry['scoping']}</td>";
    //                         $html .= "</tr>";
    //                     }
    //                 } else if ($entry['firstHalfWork'] == "no update") {
    //                     $html .= "<tr>";
    //                     $html .= "<td style='padding:10px' colspan='2'><b>Status:</b> No Update</td>";
    //                     $html .= "</tr>";
    //                     $html .= "<tr>";
    //                     $html .= "<td style='padding:10px' colspan='2'><b>Work:</b> {$entry['firstHalfWork']}</td>";
    //                     $html .= "</tr>";
    //                     if ($entry['scoping'] != null) {
    //                         $html .= "<tr>";
    //                         $html .= "<td style='padding:10px' colspan='2'><b>Scoping:</b> {$entry['scoping']}</td>";
    //                         $html .= "</tr>";
    //                     }
    //                 } else {
    //                     if ($entry['secondHalfWork'] != "-" && $entry['secondHalfWork'] != "no update") {
    //                         $html .= "<tr>";
    //                         $html .= "<td style='padding:10px' colspan='2'><b>Status:</b> Present</td>";
    //                         $html .= "</tr>";
    //                         $html .= "<tr>";
    //                         $html .= "<td style='padding:10px' colspan='2'><b>First half Work:</b> {$entry['firstHalfWork']}</td>";
    //                         $html .= "</tr>";
    //                         $html .= "<tr>";
    //                         $html .= "<td style='padding:10px' colspan='2'><b>Second half Work:</b> {$entry['secondHalfWork']}</td>";
    //                         $html .= "</tr>";
    //                         if ($entry['scoping'] != null) {
    //                             $html .= "<tr>";
    //                             $html .= "<td style='padding:10px' colspan='2'><b>Scoping:</b> {$entry['scoping']}</td>";
    //                             $html .= "</tr>";
    //                         }
    //                     } else {

    //                         if ($entry['secondHalfWork'] == "no update") {
    //                             $html .= "<tr>";
    //                             $html .= "<td style='padding:10px' colspan='2'><b>Status:</b> No Update</td>";
    //                             $html .= "</tr>";
    //                             $html .= "<tr>";
    //                             $html .= "<td style='padding:10px' colspan='2'><b>Work:</b> {$entry['secondHalfWork']}</td>";
    //                             $html .= "</tr>";
    //                             if ($entry['scoping'] != null) {
    //                                 $html .= "<tr>";
    //                                 $html .= "<td style='padding:10px' colspan='2'><b>Scoping:</b> {$entry['scoping']}</td>";
    //                                 $html .= "</tr>";
    //                             }
    //                         }
    //                         $html .= "<tr>";
    //                         $html .= "<td style='padding:10px' colspan='2'><b>Status:</b> Second Half Leave</td>";
    //                         $html .= "</tr>";
    //                         $html .= "<tr>";
    //                         $html .= "<td style='padding:10px' colspan='2'><b>First Half Work:</b> {$entry['firstHalfWork']}</td>";
    //                         $html .= "</tr>";
    //                         if ($entry['scoping'] != null) {
    //                             $html .= "<tr>";
    //                             $html .= "<td style='padding:10px' colspan='2'><b>Scoping:</b> {$entry['scoping']}</td>";
    //                             $html .= "</tr>";
    //                         }
    //                     }
    //                 }
    //             } else {
    //                 if ($entry['firstHalfWork'] == "-") {
    //                     $html .= "<tr>";
    //                     $html .= "<td style='padding:10px' colspan='2'><b>Status:</b> First Half Leave</td>";
    //                     $html .= "</tr>";
    //                     $html .= "<tr>";
    //                     $html .= "<td style='padding:10px' colspan='2'><b>Second Half Work:</b> {$entry['secondHalfWork']}</td>";
    //                     $html .= "</tr>";
    //                     if ($entry['scoping'] != null) {
    //                         $html .= "<tr>";
    //                         $html .= "<td style='padding:10px' colspan='2'><b>Scoping:</b> {$entry['scoping']}</td>";
    //                         $html .= "</tr>";
    //                     }
    //                 } else if ($entry['firstHalfWork'] == "no update") {
    //                     $html .= "<tr>";
    //                     $html .= "<td style='padding:10px' colspan='2'><b>Status:</b> No Update</td>";
    //                     $html .= "</tr>";
    //                     $html .= "<tr>";
    //                     $html .= "<td style='padding:10px' colspan='2'><b>Work:</b> {$entry['firstHalfWork']}</td>";
    //                     $html .= "</tr>";
    //                     if ($entry['scoping'] != null) {
    //                         $html .= "<tr>";
    //                         $html .= "<td style='padding:10px' colspan='2'><b>Scoping:</b> {$entry['scoping']}</td>";
    //                         $html .= "</tr>";
    //                     }
    //                 } else {
    //                     if ($entry['secondHalfWork'] != "-" && $entry['secondHalfWork'] != "no update") {
    //                         $html .= "<tr>";
    //                         $html .= "<td style='padding:10px' colspan='2'><b>Status:</b> Present</td>";
    //                         $html .= "</tr>";
    //                         $html .= "<tr>";
    //                         $html .= "<td style='padding:10px' colspan='2'><b>First half Work:</b> {$entry['firstHalfWork']}</td>";
    //                         $html .= "</tr>";
    //                         $html .= "<tr>";
    //                         $html .= "<td style='padding:10px' colspan='2'><b>Second half Work:</b> {$entry['secondHalfWork']}</td>";
    //                         $html .= "</tr>";
    //                         if ($entry['scoping'] != null) {
    //                             $html .= "<tr>";
    //                             $html .= "<td style='padding:10px' colspan='2'><b>Scoping:</b> {$entry['scoping']}</td>";
    //                             $html .= "</tr>";
    //                         }
    //                     } else {

    //                         if ($entry['secondHalfWork'] == "no update") {
    //                             $html .= "<tr>";
    //                             $html .= "<td style='padding:10px' colspan='2'><b>Status:</b> No Update</td>";
    //                             $html .= "</tr>";
    //                             $html .= "<tr>";
    //                             $html .= "<td style='padding:10px' colspan='2'><b>Work:</b> {$entry['secondHalfWork']}</td>";
    //                             $html .= "</tr>";
    //                             if ($entry['scoping'] != null) {
    //                                 $html .= "<tr>";
    //                                 $html .= "<td style='padding:10px' colspan='2'><b>Scoping:</b> {$entry['scoping']}</td>";
    //                                 $html .= "</tr>";
    //                             }
    //                         }
    //                         $html .= "<tr>";
    //                         $html .= "<td style='padding:10px' colspan='2'><b>Status:</b> Second Half Leave</td>";
    //                         $html .= "</tr>";
    //                         $html .= "<tr>";
    //                         $html .= "<td style='padding:10px' colspan='2'><b>First Half Work:</b> {$entry['firstHalfWork']}</td>";
    //                         $html .= "</tr>";
    //                         if ($entry['scoping'] != null) {
    //                             $html .= "<tr>";
    //                             $html .= "<td style='padding:10px' colspan='2'><b>Scoping:</b> {$entry['scoping']}</td>";
    //                             $html .= "</tr>";
    //                         }
    //                     }
    //                 }
    //             }
    //         }
    //         $html .= "</table>";
    //     }
    //     $html .= "</div>";

    //     send_email("tech","Daily Work Report", $html);

    //     // if (send_email("181040107032@gperi.ac.in", "GPERI", "Daily Work Report", $html)) {
    //     //     echo "Email sent successfully";
    //     // } else {
    //     //     echo "Failed to send email.";
    //     // }

    // }

    // function emailForSalesHOD($conn, $dateString) {
    //     $salesQry = "select * from works where department='sales' and date='$dateString' order by fullName ASC";
    //     $salesResult = mysqli_query($conn, $salesQry);

    //     if(!$salesResult) {
    //         echo "Error: " . mysqli_error($conn);
    //     }

    //     $salesEmployees = array();

    //     while ($salesRow = mysqli_fetch_assoc($salesResult)) {
    //         $salesName = $salesRow['fullName'];
    //         $salesEmployees[$salesName][] = $salesRow;
    //     }

    //     $html = '';
    //     $html .= "<div>";
    //     foreach ($salesEmployees as $salesName => $entries) {
    //         $html .= "<table style='border:1px solid #000000;margin-bottom:10px;'>";
    //         foreach ($entries as $entry) {
    //             $html .= "<tr>";
    //             $html .= "<td style='padding:10px;'><b>Name:</b> {$salesName}</td>";
    //             $html .= "<td style='padding:10px;'><b>Date:</b> {$dateString}</td>";
    //             $html .= "</tr>";
    //             if ($entry['firstHalfWork'] == "-") {
    //                 $html .= "<tr>";
    //                 $html .= "<td style='padding:10px' colspan='2'><b>Status:</b> First Half Leave</td>";
    //                 $html .= "</tr>";
    //                 $html .= "<tr>";
    //                 $html .= "<td style='padding:10px' colspan='2'><b>Second Half Work:</b> {$entry['secondHalfWork']}</td>";
    //                 $html .= "</tr>";
    //                 if ($entry['scoping'] != null) {
    //                     $html .= "<tr>";
    //                     $html .= "<td style='padding:10px' colspan='2'><b>Scoping:</b> {$entry['scoping']}</td>";
    //                     $html .= "</tr>";
    //                 }
    //             } else if ($entry['firstHalfWork'] == "no update") {
    //                 $html .= "<tr>";
    //                 $html .= "<td style='padding:10px' colspan='2'><b>Status:</b> No Update</td>";
    //                 $html .= "</tr>";
    //                 $html .= "<tr>";
    //                 $html .= "<td style='padding:10px' colspan='2'><b>Work:</b> {$entry['firstHalfWork']}</td>";
    //                 $html .= "</tr>";
    //                 if ($entry['scoping'] != null) {
    //                     $html .= "<tr>";
    //                     $html .= "<td style='padding:10px' colspan='2'><b>Scoping:</b> {$entry['scoping']}</td>";
    //                     $html .= "</tr>";
    //                 }
    //             } else {
    //                 if ($entry['secondHalfWork'] != "-" && $entry['secondHalfWork'] != "no update") {
    //                     $html .= "<tr>";
    //                     $html .= "<td style='padding:10px' colspan='2'><b>Status:</b> Present</td>";
    //                     $html .= "</tr>";
    //                     $html .= "<tr>";
    //                     $html .= "<td style='padding:10px' colspan='2'><b>First half Work:</b> {$entry['firstHalfWork']}</td>";
    //                     $html .= "</tr>";
    //                     $html .= "<tr>";
    //                     $html .= "<td style='padding:10px' colspan='2'><b>Second half Work:</b> {$entry['secondHalfWork']}</td>";
    //                     $html .= "</tr>";
    //                     if ($entry['scoping'] != null) {
    //                         $html .= "<tr>";
    //                         $html .= "<td style='padding:10px' colspan='2'><b>Scoping:</b> {$entry['scoping']}</td>";
    //                         $html .= "</tr>";
    //                     }
    //                 } else {

    //                     if ($entry['secondHalfWork'] == "no update") {
    //                         $html .= "<tr>";
    //                         $html .= "<td style='padding:10px' colspan='2'><b>Status:</b> No Update</td>";
    //                         $html .= "</tr>";
    //                         $html .= "<tr>";
    //                         $html .= "<td style='padding:10px' colspan='2'><b>Work:</b> {$entry['secondHalfWork']}</td>";
    //                         $html .= "</tr>";
    //                         if ($entry['scoping'] != null) {
    //                             $html .= "<tr>";
    //                             $html .= "<td style='padding:10px' colspan='2'><b>Scoping:</b> {$entry['scoping']}</td>";
    //                             $html .= "</tr>";
    //                         }
    //                     }
    //                     $html .= "<tr>";
    //                     $html .= "<td style='padding:10px' colspan='2'><b>Status:</b> Second Half Leave</td>";
    //                     $html .= "</tr>";
    //                     $html .= "<tr>";
    //                     $html .= "<td style='padding:10px' colspan='2'><b>First Half Work:</b> {$entry['firstHalfWork']}</td>";
    //                     $html .= "</tr>";
    //                     if ($entry['scoping'] != null) {
    //                         $html .= "<tr>";
    //                         $html .= "<td style='padding:10px' colspan='2'><b>Scoping:</b> {$entry['scoping']}</td>";
    //                         $html .= "</tr>";
    //                     }
    //                 }
    //             }
    //         }
    //         $html .= "</table>";
    //     }
    //     $html .= "</div>";

    //     send_email("sales", "Daily Work Report", $html);

    //     // if (send_email("patel.parth2201@gmail.com", "Parth Patel", "Daily Work Report", $html)) {
    //     //     echo "Email sent successfully";
    //     // } else {
    //     //     echo "Failed to send email.";
    //     // }
    // }

    
    mysqli_close($conn);

    
?>
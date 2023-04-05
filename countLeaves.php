<?php
$conn = mysqli_connect("localhost", "root", "");
mysqli_select_db($conn, "ygm");

if (!$conn) {
    die("connection failed!");
}

$empId = trim($_POST['empId']);

$qry1 =
    "SELECT 
        COALESCE(SUM(CASE WHEN leaveType = 'First Half' OR leaveType = 'Second Half' THEN 0.5 ELSE 0 END), 0) AS halfLeave,
        COALESCE(SUM(CASE WHEN leaveType = 'full leave' THEN 1 ELSE 0 END), 0) AS fullLeave
    FROM employee_work_and_leave
    WHERE empId='$empId' AND MONTH(STR_TO_DATE(fromDate, '%d/%m/%Y')) = MONTH(CURRENT_DATE) AND YEAR(STR_TO_DATE(fromDate, '%d/%m/%Y')) = YEAR(CURRENT_DATE)";

$result = mysqli_query($conn, $qry1);

if (!$result) {
    echo "Error: " . mysqli_error($conn);
}

if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }
    print(json_encode($data));
} else {
    $data[] = array("errorCode" => "404", "status" => "not found");
    print(json_encode($data));
}

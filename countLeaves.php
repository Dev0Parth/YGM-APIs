<?php
$conn = mysqli_connect("localhost", "root", "");
mysqli_select_db($conn, "ygm");

if (!$conn) {
    die("connection failed!");
}

$Emp_Code = trim($_POST['Emp_Code']);

$qry1 =
    "SELECT 
        COALESCE(SUM(CASE WHEN Leave_Type = 'First Half' OR Leave_Type = 'Second Half' THEN 0.5 ELSE 0 END), 0) AS halfLeave,
        COALESCE(SUM(CASE WHEN Leave_Type = 'Full Leave' THEN 1 ELSE 0 END), 0) AS fullLeave
    FROM employee_work_and_leave
    WHERE Emp_Code='$Emp_Code' AND MONTH(STR_TO_DATE(From_Date, '%d/%m/%Y')) = MONTH(CURRENT_DATE) AND YEAR(STR_TO_DATE(From_Date, '%d/%m/%Y')) = YEAR(CURRENT_DATE)";

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

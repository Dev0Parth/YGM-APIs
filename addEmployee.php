<?php

$conn = new mysqli("localhost", "root", "", "ygm");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$option = ['cost' => 12];

$csv_file = "employees.csv";

if (($handle = fopen($csv_file, "r")) !== false) {

    // Skip the header (if you have one)
    fgetcsv($handle);

    while (($data = fgetcsv($handle, 1000, ",")) !== false) {

        $empId = $data[0];
        $department = $data[1];
        $name = $data[2];
        $email = $data[3];
        $phone = $data[4];
        $password = $data[5];
        $hashed_password = password_hash($password, PASSWORD_BCRYPT, $option);

        $sql = "insert into employees (`empId`, `department`, `name`, `email`, `phone`, `password`) values ('$empId', '$department', '$name', '$email', '$phone', '$hashed_password')";

        if (mysqli_query($conn, $sql)) {
            echo "New record created successfully";
        } else {
            echo "Error: " . $sql . "<br>" . mysqli_error($conn);
        }
    }

    fclose($handle);

} else {
    echo "Error: Unable to open the CSV file.";
}

$conn->close();

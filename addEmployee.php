<?php

require 'vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$conn = new mysqli("localhost", $_ENV['DATABASE_USER'], $_ENV['DATABASE_PASSWORD'], $_ENV['DATABASE']);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$csv_file = "employees.csv";

if (($handle = fopen($csv_file, "r")) !== false) {

    // Skip the header (if you have one)
    fgetcsv($handle);

    while (($data = fgetcsv($handle, 1000, ",")) !== false) {

        $Emp_Code = $data[0];
        $Department = $data[1];
        $Name = $data[2];
        $Contact_No = $data[3];
        $Email = $data[4];
        $Ga_Id = $data[5];

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

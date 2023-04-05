<?php

    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;

    require 'C:\xampp\php\PHPMailer\src\PHPMailer.php';
    require 'C:\xampp\php\PHPMailer\src\SMTP.php';
    require 'C:\xampp\php\PHPMailer\src\Exception.php';

    function send_email($department, $subject, $body, $alt_body = '')
    {
        $mail = new PHPMailer(true);

        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'foronlygames07@gmail.com';
            $mail->Password = 'isqmkdyohwepjrpd';
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            // Recipients
            $mail->setFrom('foronlyprojects7@gmail.com', 'YGM');

            $conn = new mysqli("localhost", "root", "", "ygm");
            $qry = "select email, name from master where department='$department'";
            $result = mysqli_query($conn, $qry);
            while($row = mysqli_fetch_assoc($result)) {
                $mail->addBcc($row['email'], $row['name']);
            }
            
            // Content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $body;
            $mail->AltBody = $alt_body;

            $mail->send();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

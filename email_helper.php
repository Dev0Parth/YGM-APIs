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
            $mail->Username = 'it.admin@teamygm.in';
            $mail->Password = 'YGMit@219#Gr9#$';
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

        

            // Recipients
            $mail->setFrom('it.admin@teamygm.in', 'YGM');

            $conn = new mysqli("localhost", "root", "", "ygm");

        
            $qry = "select email, name from master where department='$department'";
            $result = mysqli_query($conn, $qry);
        
            if (mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $mail->addBcc($row['email'], $row['name']);
                }

                // Content
                $mail->isHTML(true);
                $mail->Subject = $subject;
                $mail->Body    = $body;
                $mail->AltBody = $alt_body;

                $mail->send();
                return true;
            } else {
                return false;
            }
            
        } catch (Exception $e) {
            return false;
        }
    }

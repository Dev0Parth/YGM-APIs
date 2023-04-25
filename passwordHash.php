<?php
    $plain_password = "hr@12345";
    $option = ['cost' => 12];
    $hashed_password = password_hash($plain_password, PASSWORD_BCRYPT, $option);
    $boolean = password_verify($plain_password, $hashed_password);
    echo "Hashed password: " . $hashed_password;
?>
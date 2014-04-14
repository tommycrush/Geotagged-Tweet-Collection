<?php

// fill out MySQL login details and rename to db_connect.php

function connect_to_db(){
    $conn=mysqli_connect("host","user","password","db_name");
    if (mysqli_connect_errno($conn)) {
        die("Failed to connect to MySQL: " . mysqli_connect_error());
    }
    return $conn;
}
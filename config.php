<?php

$db_host = "localhost";    
$db_user = "root";        
$db_pass = "";            
$db_name = "vulnerable_bank"; 

// Try to connect to the database
$conn = mysqli_connect($db_host, $db_user, $db_pass, $db_name);

// If connection fails, show an error message
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?> 
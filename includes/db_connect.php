<?php
$servername = "localhost";
$username = "root";
$password = "admin";
$dbname = "test1_epillbox";

try {
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
} catch (Exception $e) {
    // Log the error message or display a user-friendly error page
    error_log("Database connection failed: " . $e->getMessage()); 
    die("Oops! Something went wrong. Please try again later."); 
}
?>
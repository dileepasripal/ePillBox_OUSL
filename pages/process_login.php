<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start(); 
}

// Database connection (assuming you have 'includes/db_connect.php')
include '../includes/db_connect.php';

$error_message = ''; // Initialize error message variable

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["email"]) && isset($_POST["password"])) {
        $email = $_POST["email"];
        $password = $_POST["password"];

        // Prepare and execute the SQL query (using prepared statements to prevent SQL injection)
        $stmt = $conn->prepare("SELECT id, password, role FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $row = $result->fetch_assoc();
            if (password_verify($password, $row['password'])) {
                $_SESSION['user_id'] = $row["id"];
                $_SESSION['user_role'] = $row["role"];

                // Redirect to home.php on success
                header("Location: home.php"); 
                exit;
            } else {
                $error_message = "Incorrect password";
            }
        } else {
            $error_message = "User not found";
        }

        $stmt->close();
    } else {
        $error_message = "Email or password field is missing.";
    }
}

$conn->close(); 

// If there's an error, redirect back to the login page with the error message
if (!empty($error_message)) {
    header("Location: index.php?error=" . urlencode($error_message));
    exit;
}
?>
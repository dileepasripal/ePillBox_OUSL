<h2>Forgot Password</h2>

<?php
// Database connection (reuse the same connection code from register.php)

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST["email"];

    // Check if the email exists in the database
    $sql = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        // Generate a unique reset token and store it in the database (you'll need to add a 'reset_token' column to your 'users' table)
        $reset_token = bin2hex(random_bytes(32)); // Generate a secure random token

        // Update the user's record with the reset token and its expiration time (e.g., 1 hour from now)
        $update_sql = "UPDATE users SET reset_token = ?, reset_token_expires_at = DATE_ADD(NOW(), INTERVAL 1 HOUR) WHERE email = ?";
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param("ss", $reset_token, $email);
        $stmt->execute();

        // Send a password reset email to the user with the reset token
        // ... (You'll need to implement the email sending logic using PHP's mail() function or a library)

        echo "A password reset link has been sent to your email.";
    } else {
        echo "Email not found.";
    }

    $stmt->close();
}

$conn->close();
?>

<form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
    <label for="email">Email:</label>
    <input type="email" id="email" name="email" required><br><br>

    <input type="submit" value="Send Reset Link">
</form>
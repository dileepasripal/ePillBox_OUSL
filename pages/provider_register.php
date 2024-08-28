<h2>Provider Registration</h2>

<?php
// ... (Database connection code - same as in register.php)

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get data from the form and perform validation
    // ...

    // Insert user into the 'users' table with 'role' = 'provider'
    $sql = "INSERT INTO users (email, password, role) VALUES (?, ?, 'provider')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $email, $password); // Assuming $email and $password are validated and hashed

    if ($stmt->execute()) {
        $user_id = $stmt->insert_id; // Get the newly inserted user ID

        // If you have a 'providers' table, insert additional provider data
        // ...

        echo "Registration successful! You can now <a href='index.php?page=login'>login</a>.";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>

<form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
    <label for="email">Email:</label>
    <input type="email" id="email" name="email" required><br><br>

    <label for="password">Password:</label>
    <input type="password" id="password" name="password" required><br><br>

    </form>
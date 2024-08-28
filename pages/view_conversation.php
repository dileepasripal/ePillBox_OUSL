<?php
// Check if the user is logged in and a partner ID is provided
if (!isset($_SESSION['user_id']) || !isset($_GET['partner_id'])) {
    header("Location: index.php?page=messages"); 
    exit;
}

// Database connection (reuse the same connection code from register.php)

// Fetch conversation messages
$user_id = $_SESSION['user_id'];
$partner_id = $_GET['partner_id'];
$sql = "SELECT * FROM messages 
        WHERE (sender_id = ? AND recipient_id = ?) OR (sender_id = ? AND recipient_id = ?)
        ORDER BY created_at ASC"; 
$stmt = $conn->prepare($sql);
$stmt->bind_param("iiii", $user_id, $partner_id, $partner_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Handle sending a new message
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the message from the form and perform validation
    // ...

    // Insert the new message into the database
    $insert_sql = "INSERT INTO messages (sender_id, recipient_id, message) 
                   VALUES (?, ?, ?)";
    $stmt = $conn->prepare($insert_sql);
    $stmt->bind_param("iis", $user_id, $partner_id, $message); 

    if ($stmt->execute()) {
        // You might want to refresh the page or use AJAX to update the conversation without a full page reload
    } else {
        echo "Error sending message: " . $stmt->error;
    }

    $stmt->close();
}

// Mark messages as read (you'll need to implement this logic)
// ...

$conn->close();
?>

<h2>Conversation with <?php // Fetch and display the partner's name or email ?></h2>

<div id="conversation">
    <?php while ($row = $result->fetch_assoc()) { ?>
        <div class="message <?php echo ($row['sender_id'] == $user_id) ? 'sent' : 'received'; ?>">
            <p><?php echo $row["message"]; ?></p>
        </div>
    <?php } ?>
</div>

<form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . '?partner_id=' . $partner_id; ?>">
    <textarea name="message" required></textarea><br>
    <input type="submit" value="Send">
</form>
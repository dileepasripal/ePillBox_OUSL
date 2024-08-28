<?php
// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php?page=login"); 
    exit;
}

// Database connection (reuse the same connection code from register.php)

// Fetch user's conversations (list of unique senders/recipients)
$user_id = $_SESSION['user_id'];
$sql = "SELECT DISTINCT 
            IF(sender_id = ?, recipient_id, sender_id) AS conversation_partner_id,
            u.email AS conversation_partner_email
        FROM messages m
        JOIN users u ON (u.id = m.sender_id OR u.id = m.recipient_id) AND u.id != ?
        WHERE m.sender_id = ? OR m.recipient_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iiii", $user_id, $user_id, $user_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

?>

<h2>Messages</h2>

<ul>
    <?php while ($row = $result->fetch_assoc()) { ?>
        <li>
            <a href="index.php?page=view_conversation&partner_id=<?php echo $row["conversation_partner_id"]; ?>">
                <?php echo $row["conversation_partner_email"]; ?>
            </a>
        </li>
    <?php } ?>
</ul>

<?php
$stmt->close();
$conn->close();
?>
<?php
session_start();

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== 'admin') {
    header("location: login.php");
    exit;
}

require_once "../includes/db_connect.php";

if(!isset($_GET["id"])) {
    header("location: home.php");
    exit;
}

$user_id = $_GET["id"];
$errors = [];

// Handle form submission
if($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate input
    if(empty(trim($_POST["email"]))) {
        $errors[] = "Please enter an email.";
    }
    
    if(empty(trim($_POST["contact"]))) {
        $errors[] = "Please enter a contact number.";
    }

    if(empty($errors)) {
        // Begin transaction
        $conn->begin_transaction();

        try {
            // Update basic user information
            $update_sql = "UPDATE users SET 
                          email = ?, 
                          contact = ?,
                          first_name = ?,
                          last_name = ?
                          WHERE id = ?";
            
            $stmt = $conn->prepare($update_sql);
            $stmt->bind_param("ssssi", 
                $_POST["email"],
                $_POST["contact"],
                $_POST["first_name"],
                $_POST["last_name"],
                $user_id
            );
            $stmt->execute();

            // Update role-specific information
            if($_POST["role"] === "doctor") {
                $update_role_sql = "UPDATE doctors SET 
                                  specialization = ?,
                                  hospital = ?
                                  WHERE user_id = ?";
                $stmt = $conn->prepare($update_role_sql);
                $stmt->bind_param("ssi", 
                    $_POST["specialization"],
                    $_POST["hospital"],
                    $user_id
                );
                $stmt->execute();
            }
            // Add similar updates for other roles

            $conn->commit();
            $_SESSION["success_message"] = "User updated successfully.";
            header("location: ?page=manage_users");
            exit;

        } catch(Exception $e) {
            $conn->rollback();
            $errors[] = "Error updating user: " . $e->getMessage();
        }
    }
}

// Fetch current user data
$sql = "SELECT u.*, 
        d.specialization, d.hospital,
        p.conditions, p.emergency_contact_1,
        ph.pharmacy_name, ph.license_number
        FROM users u
        LEFT JOIN doctors d ON u.id = d.user_id
        LEFT JOIN patients p ON u.id = p.user_id
        LEFT JOIN pharmacists ph ON u.id = ph.user_id
        WHERE u.id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
?>

<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Edit User</h3>
        </div>
        <div class="card-body">
            <?php if(!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php foreach($errors as $error): ?>
                            <li><?php echo $error; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="post">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>First Name</label>
                            <input type="text" name="first_name" class="form-control" 
                                   value="<?php echo htmlspecialchars($user['first_name'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label>Last Name</label>
                            <input type="text" name="last_name" class="form-control" 
                                   value="<?php echo htmlspecialchars($user['last_name'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" name="email" class="form-control" required
                                   value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label>Contact</label>
                            <input type="text" name="contact" class="form-control" required
                                   value="<?php echo htmlspecialchars($user['contact']); ?>">
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <!-- Role-specific fields -->
                        <?php if($user['role'] === 'doctor'): ?>
                            <div class="form-group">
                                <label>Specialization</label>
                                <input type="text" name="specialization" class="form-control"
                                       value="<?php echo htmlspecialchars($user['specialization'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label>Hospital</label>
                                <input type="text" name="hospital" class="form-control"
                                       value="<?php echo htmlspecialchars($user['hospital'] ?? ''); ?>">
                            </div>
                        <?php endif; ?>
                        <!-- Add similar sections for other roles -->
                    </div>
                </div>

                <div class="form-group mt-3">
                    <button type="submit" class="btn btn-primary">Update User</button>
                    <a href="?page=manage_users" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
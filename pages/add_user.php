<?php
session_start();

// Check if user is logged in and has admin role
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== 'admin') {
    header("location: login.php");
    exit;
}

require_once "../includes/db_connect.php";

$errors = [];
$success_msg = "";

// Process form submission
if($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate input
    if(empty(trim($_POST["username"]))) {
        $errors[] = "Please enter a username.";
    } else {
        // Check if username exists
        $sql = "SELECT id FROM users WHERE username = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $_POST["username"]);
        $stmt->execute();
        if($stmt->get_result()->num_rows > 0) {
            $errors[] = "This username is already taken.";
        }
    }

    if(empty(trim($_POST["password"]))) {
        $errors[] = "Please enter a password.";
    }

    if(empty(trim($_POST["role"]))) {
        $errors[] = "Please select a role.";
    }

    if(empty(trim($_POST["dob"]))) {
        $errors[] = "Please enter date of birth.";
    }

    if(empty(trim($_POST["contact"]))) {
        $errors[] = "Please enter contact number.";
    }

    // If no errors, proceed with registration
    if(empty($errors)) {
        $conn->begin_transaction();

        try {
            // Insert into users table
            $sql = "INSERT INTO users (username, password, first_name, last_name, email, dob, contact, role) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $conn->prepare($sql);
            $password = password_hash($_POST["password"], PASSWORD_DEFAULT);
            $stmt->bind_param("ssssssss", 
                $_POST["username"],
                $password,
                $_POST["first_name"],
                $_POST["last_name"],
                $_POST["email"],
                $_POST["dob"],
                $_POST["contact"],
                $_POST["role"]
            );
            $stmt->execute();
            $user_id = $conn->insert_id;

            // Insert role-specific information
            switch($_POST["role"]) {
                case 'doctor':
                    $sql = "INSERT INTO doctors (user_id, username, specialization, experience, hospital, hospital_address) 
                            VALUES (?, ?, ?, ?, ?, ?)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("isssss", 
                        $user_id,
                        $_POST["username"],
                        $_POST["specialization"],
                        $_POST["experience"],
                        $_POST["hospital"],
                        $_POST["hospital_address"]
                    );
                    $stmt->execute();
                    break;

                case 'patient':
                    $sql = "INSERT INTO patients (user_id, username, conditions, medications, emergency_contact_1, emergency_contact_2) 
                            VALUES (?, ?, ?, ?, ?, ?)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("isssss", 
                        $user_id,
                        $_POST["username"],
                        $_POST["conditions"],
                        $_POST["medications"],
                        $_POST["emergency_contact_1"],
                        $_POST["emergency_contact_2"]
                    );
                    $stmt->execute();
                    break;

                case 'pharmacist':
                    $sql = "INSERT INTO pharmacists (user_id, username, pharmacy_id, pharmacy_name, license_number) 
                            VALUES (?, ?, ?, ?, ?)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("isiss", 
                        $user_id,
                        $_POST["username"],
                        $_POST["pharmacy_id"],
                        $_POST["pharmacy_name"],
                        $_POST["license_number"]
                    );
                    $stmt->execute();
                    break;
            }

            $conn->commit();
            $success_msg = "New user created successfully!";

        } catch(Exception $e) {
            $conn->rollback();
            $errors[] = "Error creating user: " . $e->getMessage();
        }
    }
}

// Fetch pharmacies for pharmacist role
$pharmacies = [];
$pharmacy_sql = "SELECT pharmacy_id, name FROM pharmacies";
$pharmacy_result = $conn->query($pharmacy_sql);
while($row = $pharmacy_result->fetch_assoc()) {
    $pharmacies[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Management</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .wrapper { padding: 20px; }
        .search-box { margin-bottom: 20px; }
        .role-badge {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.85em;
        }
        .role-doctor { background-color: #cce5ff; color: #004085; }
        .role-patient { background-color: #d4edda; color: #155724; }
        .role-pharmacist { background-color: #fff3cd; color: #856404; }
        .role-admin { background-color: #f8d7da; color: #721c24; }
        .action-buttons { white-space: nowrap; }
    </style>
</head>
<body>
<div class="wrapper">
<?php include "../includes/header.php"; ?>
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Add New User</h3>
        </div>
        <div class="card-body">
            <?php if(!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php foreach($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if($success_msg): ?>
                <div class="alert alert-success">
                    <?php echo htmlspecialchars($success_msg); ?>
                </div>
            <?php endif; ?>

            <form method="post" id="addUserForm">
                <div class="row">
                    <!-- Basic Information -->
                    <div class="col-md-6">
                        <h4>Basic Information</h4>
                        <div class="form-group">
                            <label>Username*</label>
                            <input type="text" name="username" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Password*</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>First Name</label>
                            <input type="text" name="first_name" class="form-control">
                        </div>
                        <div class="form-group">
                            <label>Last Name</label>
                            <input type="text" name="last_name" class="form-control">
                        </div>
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" name="email" class="form-control">
                        </div>
                        <div class="form-group">
                            <label>Date of Birth*</label>
                            <input type="date" name="dob" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Contact*</label>
                            <input type="text" name="contact" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Role*</label>
                            <select name="role" class="form-control" required onchange="toggleRoleFields(this.value)">
                                <option value="">Select Role</option>
                                <option value="doctor">Doctor</option>
                                <option value="patient">Patient</option>
                                <option value="pharmacist">Pharmacist</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                    </div>

                    <!-- Role Specific Information -->
                    <div class="col-md-6">
                        <h4>Role-Specific Information</h4>
                        
                        <!-- Doctor Fields -->
                        <div id="doctorFields" style="display: none;">
                            <div class="form-group">
                                <label>Specialization*</label>
                                <input type="text" name="specialization" class="form-control">
                            </div>
                            <div class="form-group">
                                <label>Experience*</label>
                                <input type="text" name="experience" class="form-control">
                            </div>
                            <div class="form-group">
                                <label>Hospital*</label>
                                <input type="text" name="hospital" class="form-control">
                            </div>
                            <div class="form-group">
                                <label>Hospital Address*</label>
                                <input type="text" name="hospital_address" class="form-control">
                            </div>
                        </div>

                        <!-- Patient Fields -->
                        <div id="patientFields" style="display: none;">
                            <div class="form-group">
                                <label>Medical Conditions</label>
                                <textarea name="conditions" class="form-control" rows="3"></textarea>
                            </div>
                            <div class="form-group">
                                <label>Current Medications</label>
                                <textarea name="medications" class="form-control" rows="3"></textarea>
                            </div>
                            <div class="form-group">
                                <label>Emergency Contact 1</label>
                                <input type="text" name="emergency_contact_1" class="form-control">
                            </div>
                            <div class="form-group">
                                <label>Emergency Contact 2</label>
                                <input type="text" name="emergency_contact_2" class="form-control">
                            </div>
                        </div>

                        <!-- Pharmacist Fields -->
                        <div id="pharmacistFields" style="display: none;">
                            <div class="form-group">
                                <label>Pharmacy*</label>
                                <select name="pharmacy_id" class="form-control">
                                    <option value="">Select Pharmacy</option>
                                    <?php foreach($pharmacies as $pharmacy): ?>
                                        <option value="<?php echo $pharmacy['pharmacy_id']; ?>">
                                            <?php echo htmlspecialchars($pharmacy['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Pharmacy Name*</label>
                                <input type="text" name="pharmacy_name" class="form-control">
                            </div>
                            <div class="form-group">
                                <label>License Number*</label>
                                <input type="text" name="license_number" class="form-control">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group mt-3">
                    <button type="submit" class="btn btn-primary">Create User</button>
                    <a href="?page=manage_users" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function toggleRoleFields(role) {
    // Hide all role-specific fields
    document.getElementById('doctorFields').style.display = 'none';
    document.getElementById('patientFields').style.display = 'none';
    document.getElementById('pharmacistFields').style.display = 'none';

    // Show fields based on selected role
    if(role === 'doctor') {
        document.getElementById('doctorFields').style.display = 'block';
    } else if(role === 'patient') {
        document.getElementById('patientFields').style.display = 'block';
    } else if(role === 'pharmacist') {
        document.getElementById('pharmacistFields').style.display = 'block';
    }
}
</script>

<?php $conn->close(); ?>

<!-- Add required scripts -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<?php include "../includes/footer.php"; ?>
</div>
</body>
</html>
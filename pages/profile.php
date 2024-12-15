<?php
session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
   header("location: login.php");
   exit;
}

require_once "../includes/db_connect.php";

$error = "";
$success_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
   $username = trim($_POST["username"]);
   $first_name = trim($_POST["first_name"]); 
   $last_name = trim($_POST["last_name"]);
   $email = trim($_POST["email"]);
   $dob = trim($_POST["dob"]);
   $contact = trim($_POST["contact"]);

   $conn->begin_transaction();
   try {
       $sql = "UPDATE users SET 
               username=?, first_name=?, last_name=?, email=?, dob=?, contact=? 
               WHERE id=?";
       $stmt = $conn->prepare($sql);
       $stmt->bind_param("ssssssi", $username, $first_name, $last_name, $email, $dob, $contact, $_SESSION["id"]);
       $stmt->execute();

       if ($_SESSION["role"] == 'patient') {
           $sql2 = "UPDATE patients SET conditions=?, medications=? WHERE user_id=?";
           $stmt2 = $conn->prepare($sql2);
           $stmt2->bind_param("ssi", $_POST["conditions"], $_POST["medications"], $_SESSION["id"]);
           $stmt2->execute();
       } elseif ($_SESSION["role"] == 'doctor') {
           $sql2 = "UPDATE doctors SET specialization=?, experience=? WHERE user_id=?";
           $stmt2 = $conn->prepare($sql2);
           $stmt2->bind_param("ssi", $_POST["specialization"], $_POST["experience"], $_SESSION["id"]);
           $stmt2->execute();
       } elseif ($_SESSION["role"] == 'pharmacist') {
           $sql2 = "UPDATE pharmacists SET pharmacy_name=?, license_number=? WHERE user_id=?";
           $stmt2 = $conn->prepare($sql2);
           $stmt2->bind_param("ssi", $_POST["pharmacy_name"], $_POST["license_number"], $_SESSION["id"]);
           $stmt2->execute();
       }

       $conn->commit();
       $_SESSION['success_message'] = "Profile updated successfully!";
       header("location: profile.php");
       exit();

   } catch(Exception $e) {
       $conn->rollback();
       $error = "Error updating profile: " . $e->getMessage();
   }
} else {
   $sql = "SELECT u.*, 
           CASE 
               WHEN u.role = 'patient' THEN p.conditions 
               WHEN u.role = 'doctor' THEN d.specialization
               WHEN u.role = 'pharmacist' THEN ph.pharmacy_name
           END as role_specific_1,
           CASE 
               WHEN u.role = 'patient' THEN p.medications
               WHEN u.role = 'doctor' THEN d.experience 
               WHEN u.role = 'pharmacist' THEN ph.license_number
           END as role_specific_2
           FROM users u
           LEFT JOIN patients p ON u.id = p.user_id
           LEFT JOIN doctors d ON u.id = d.user_id
           LEFT JOIN pharmacists ph ON u.id = ph.user_id
           WHERE u.id = ?";

   $stmt = $conn->prepare($sql);
   $stmt->bind_param("i", $_SESSION["id"]);
   $stmt->execute();
   $user_data = $stmt->get_result()->fetch_assoc();

   $username = $user_data['username'] ?? '';
   $first_name = $user_data['first_name'] ?? '';
   $last_name = $user_data['last_name'] ?? '';
   $email = $user_data['email'] ?? '';
   $dob = $user_data['dob'] ?? '';
   $contact = $user_data['contact'] ?? '';

   if ($_SESSION["role"] == 'patient') {
       $conditions = $user_data['role_specific_1'] ?? '';
       $medications = $user_data['role_specific_2'] ?? '';
   } elseif ($_SESSION["role"] == 'doctor') {
       $specialization = $user_data['role_specific_1'] ?? '';
       $experience = $user_data['role_specific_2'] ?? '';
   } elseif ($_SESSION["role"] == 'pharmacist') {
       $pharmacy_name = $user_data['role_specific_1'] ?? '';
       $license_number = $user_data['role_specific_2'] ?? '';
   }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <title>Profile</title>
   <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
   <style>
       .wrapper {
           max-width: 800px;
           margin: 20px auto;
           padding: 20px;
       }
       .card {
           box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
       }
       .card-header {
           background-color: #f8f9fa;
           border-bottom: 1px solid #e3e6f0;
       }
   </style>
</head>
<body>
   <?php include "../includes/header.php"; ?>
   
   <div class="wrapper">
       <div class="card">
           <div class="card-header">
               <h2 class="mb-0">Profile Information</h2>
           </div>
           <div class="card-body">
               <?php if(!empty($error)): ?>
                   <div class="alert alert-danger"><?php echo $error; ?></div>
               <?php endif; ?>

               <?php if(isset($_SESSION['success_message'])): ?>
                   <div class="alert alert-success">
                       <?php 
                           echo $_SESSION['success_message'];
                           unset($_SESSION['success_message']);
                       ?>
                   </div>
               <?php endif; ?>

               <form action="" method="post">
                   <div class="row">
                       <div class="col-md-6">
                           <div class="form-group">
                               <label>Username</label>
                               <input type="text" name="username" class="form-control" value="<?php echo $username; ?>" required>
                           </div>
                           
                           <div class="form-group">
                               <label>First Name</label>
                               <input type="text" name="first_name" class="form-control" value="<?php echo $first_name; ?>" required>
                           </div>

                           <div class="form-group">
                               <label>Last Name</label>
                               <input type="text" name="last_name" class="form-control" value="<?php echo $last_name; ?>" required>
                           </div>
                       </div>
                       
                       <div class="col-md-6">
                           <div class="form-group">
                               <label>Email</label>
                               <input type="email" name="email" class="form-control" value="<?php echo $email; ?>" required>
                           </div>

                           <div class="form-group">
                               <label>Date of Birth</label>
                               <input type="date" name="dob" class="form-control" value="<?php echo $dob; ?>" required>
                           </div>

                           <div class="form-group">
                               <label>Contact</label>
                               <input type="text" name="contact" class="form-control" value="<?php echo $contact; ?>" required>
                           </div>
                       </div>
                   </div>

                   <?php if ($_SESSION["role"] == 'patient'): ?>
                       <div class="form-group">
                           <label>Medical Conditions (Optional)</label>
                           <textarea name="conditions" class="form-control"><?php echo $conditions ?? ''; ?></textarea>
                       </div>
                       <div class="form-group">
                           <label>Current Medications (Optional)</label>
                           <textarea name="medications" class="form-control"><?php echo $medications ?? ''; ?></textarea>
                       </div>
                   <?php elseif ($_SESSION["role"] == 'doctor'): ?>
                       <div class="form-group">
                           <label>Specialization</label>
                           <input type="text" name="specialization" class="form-control" value="<?php echo $specialization ?? ''; ?>" required>
                       </div>
                       <div class="form-group">
                           <label>Experience</label>
                           <input type="text" name="experience" class="form-control" value="<?php echo $experience ?? ''; ?>" required>
                       </div>
                   <?php elseif ($_SESSION["role"] == 'pharmacist'): ?>
                       <div class="form-group">
                           <label>Pharmacy Name</label>
                           <input type="text" name="pharmacy_name" class="form-control" value="<?php echo $pharmacy_name ?? ''; ?>" required>
                       </div>
                       <div class="form-group">
                           <label>License Number</label>
                           <input type="text" name="license_number" class="form-control" value="<?php echo $license_number ?? ''; ?>" required>
                       </div>
                   <?php endif; ?>

                   <div class="form-group mb-0">
                       <button type="submit" class="btn btn-primary">Update Profile</button>
                   </div>
               </form>
           </div>
       </div>
   </div>

   <?php include "../includes/footer.php"; ?>
</body>
</html>
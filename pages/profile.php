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
           $sql2 = "UPDATE pharmacists SET pharmacy_id=?, license_number=? WHERE user_id=?";
           $stmt2 = $conn->prepare($sql2);
           $stmt2->bind_param("ssi", $_POST["pharmacy_id"], $_POST["license_number"], $_SESSION["id"]);
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
               WHEN u.role = 'pharmacist' THEN ph.pharmacy_id
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
       $pharmacy_id = $user_data['role_specific_1'] ?? '';
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
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>

    <style>
        body{ font: 14px sans-serif; 
            text-align: center;
            font-family: 'Roboto', sans-serif;
            background-color: #f8f9fa; 
        }
        .wrapper {
            background: #fff;
            border-radius: 10px; /* More rounded corners */
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            padding: 40px;
            width: 80%;
            max-width: 1200px;
            margin: 30px auto;
        }
        h2 {
            text-align: center;
            margin-bottom: 30px; /* Increased margin */
            color: #343a40;
            font-weight: 700;
        }

        h3 {
            color: #343a40;
            font-weight: 700;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-control {
            border-radius: 5px; /* Rounded input fields */
        }

        .btn-primary {
            background-color: #007bff;
            border: none;
            border-radius: 5px;
            padding: 10px 20px;
            transition: background-color 0.2s ease; /* Smooth transition */
        }

        .btn-primary:hover {
            background-color: #0062cc; /* Darker shade on hover */
        }

        .table {
            width: 100%;
            max-width: 100%;
            margin-top: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            border-collapse: separate; /* Add space between cells */
            border-spacing: 0 10px; /* Adjust spacing as needed */
        }

        .table th, .table td {
            padding: 15px;
            vertical-align: middle;
            background-color: #fff; /* White background for cells */
            border-radius: 5px; /* Rounded cell corners */
        }

        .table th {
            background-color: #f8f9fa; /* Light background for header */
            font-weight: 700;
            color: #343a40;
        }

        .table-bordered th,
        .table-bordered td {
            border: none; /* Remove default border */
        }

        .btn-sm {
            padding: 5px 10px;
            font-size: 0.8rem;
        }

        .fa {
            margin-right: 5px;
        }
    </style>
    <style>
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        }
        .card-header {
            background-color: #f8f9fc;
            border-bottom: 1px solid #e3e6f0;
        }
        .large {
            font-size: 2.5rem;
            font-weight: 700;
        }
        .text-white-50 {
            color: rgba(255, 255, 255, 0.8) !important;
        }
</style>
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
                           <label>Pharmacy ID</label>
                           <input type="text" name="pharmacy_id" class="form-control" value="<?php echo $pharmacy_id ?? ''; ?>" required>
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
       <?php include "../includes/footer.php"; ?>
   </div>


</body>
</html>
<?php
session_start();

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== 'admin'){
   header("location: login.php");
   exit;
}

require_once "../includes/db_connect.php";

$setting_name = $setting_value = "";
$setting_name_err = $setting_value_err = "";

if($_SERVER["REQUEST_METHOD"] == "POST"){
   if(empty(trim($_POST["setting_name"]))){
       $setting_name_err = "Please enter a setting name.";
   } else {
       $sql = "SELECT id FROM settings WHERE name = ?";
       if($stmt = $conn->prepare($sql)){
           $stmt->bind_param("s", $param_name);
           $param_name = trim($_POST["setting_name"]);
           $stmt->execute();
           $stmt->store_result();
           
           if($stmt->num_rows > 0){
               $setting_name_err = "This setting name already exists.";
           } else {
               $setting_name = trim($_POST["setting_name"]);
           }
       }
       $stmt->close();
   }
   
   if(empty(trim($_POST["setting_value"]))){
       $setting_value_err = "Please enter a setting value.";     
   } else{
       $setting_value = trim($_POST["setting_value"]);
   }
   
   if(empty($setting_name_err) && empty($setting_value_err)){
       $sql = "INSERT INTO settings (name, value) VALUES (?, ?)";
       
       if($stmt = $conn->prepare($sql)){
           $stmt->bind_param("ss", $setting_name, $setting_value);
           
           if($stmt->execute()){
               $_SESSION['success_message'] = "Setting added successfully.";
               header("location: system_settings");
               exit();
           } else{
               $_SESSION['error_message'] = "Error adding setting: " . $stmt->error;
           }
           $stmt->close();
       }
   }
}

$sql = "SELECT * FROM settings ORDER BY name ASC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <title>System Settings</title>
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
</head>
<body>
<div class="wrapper">
   <?php include "../includes/header.php"; ?>
   <div class="container-fluid">
       <br>
       <div class="d-flex justify-content-between align-items-center mb-4">
           <h2 class="text-dark mb-0">System Settings</h2>
           <button class="btn btn-primary" type="button" data-toggle="collapse" data-target="#addSettingForm">
               <i class="fas fa-plus"></i> Add New Setting
           </button>
       </div>

       <?php if(isset($_SESSION['success_message'])): ?>
           <div class="alert alert-success alert-dismissible fade show">
               <?php 
               echo $_SESSION['success_message']; 
               unset($_SESSION['success_message']);
               ?>
               <button type="button" class="close" data-dismiss="alert">&times;</button>
           </div>
       <?php endif; ?>

       <?php if(isset($_SESSION['error_message'])): ?>
           <div class="alert alert-danger alert-dismissible fade show">
               <?php 
               echo $_SESSION['error_message']; 
               unset($_SESSION['error_message']);
               ?>
               <button type="button" class="close" data-dismiss="alert">&times;</button>
           </div>
       <?php endif; ?>

       <!-- Add Setting Form -->
       <div class="collapse mb-4" id="addSettingForm">
           <div class="card">
               <div class="card-header">
                   <h5 class="mb-0">Add New Setting</h5>
               </div>
               <div class="card-body">
                   <form action="system_settings" method="post">
                       <div class="form-group">
                           <label>Setting Name*</label>
                           <input type="text" name="setting_name" class="form-control <?php echo (!empty($setting_name_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $setting_name; ?>">
                           <div class="invalid-feedback"><?php echo $setting_name_err; ?></div>
                       </div>
                       <div class="form-group">
                           <label>Setting Value*</label>
                           <input type="text" name="setting_value" class="form-control <?php echo (!empty($setting_value_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $setting_value; ?>">
                           <div class="invalid-feedback"><?php echo $setting_value_err; ?></div>
                       </div>
                       <div class="form-group mb-0">
                           <button type="submit" class="btn btn-primary">
                               <i class="fas fa-save"></i> Save Setting
                           </button>
                           <button type="reset" class="btn btn-secondary">
                               <i class="fas fa-undo"></i> Reset
                           </button>
                       </div>
                   </form>
               </div>
           </div>
       </div>

       <!-- Settings List -->
       <div class="card">
           <div class="card-header">
               <h5 class="mb-0">Existing Settings</h5>
           </div>
           <div class="card-body">
               <?php if($result->num_rows > 0): ?>
                   <div class="table-responsive">
                       <table class="table table-hover">
                           <thead>
                               <tr>
                                   <th>Setting Name</th>
                                   <th>Value</th>
                                   <th>Last Updated</th>
                                   <th>Actions</th>
                               </tr>
                           </thead>
                           <tbody>
                               <?php while($row = $result->fetch_assoc()): ?>
                                   <tr>
                                       <td><?php echo htmlspecialchars($row['name']); ?></td>
                                       <td><?php echo htmlspecialchars($row['value']); ?></td>
                                       <td>
                                           <?php 
                                           echo isset($row['updated_at']) ? 
                                               date('M d, Y H:i', strtotime($row['updated_at'])) : 
                                               'Not set';
                                           ?>
                                       </td>
                                       <td>
                                           <div class="btn-group">
                                               <button onclick="editSetting(<?php echo $row['id']; ?>)" 
                                                       class="btn btn-primary btn-sm" title="Edit">
                                                   <i class="fas fa-edit"></i>
                                               </button>
                                               <button onclick="deleteSetting(<?php echo $row['id']; ?>)" 
                                                       class="btn btn-danger btn-sm" title="Delete">
                                                   <i class="fas fa-trash"></i>
                                               </button>
                                           </div>
                                       </td>
                                   </tr>
                               <?php endwhile; ?>
                           </tbody>
                       </table>
                   </div>
               <?php else: ?>
                   <div class="alert alert-info">
                       <i class="fas fa-info-circle"></i> No settings found.
                   </div>
               <?php endif; ?>
           </div>
       </div>
   </div>

   <!-- Edit Setting Modal -->
   <div class="modal fade" id="editSettingModal">
       <div class="modal-dialog">
           <div class="modal-content">
               <div class="modal-header">
                   <h5 class="modal-title">Edit Setting</h5>
                   <button type="button" class="close" data-dismiss="modal">&times;</button>
               </div>
               <div class="modal-body">
                   <form id="editSettingForm">
                       <input type="hidden" name="setting_id">
                       <div class="form-group">
                           <label>Setting Name</label>
                           <input type="text" name="setting_name" class="form-control" required>
                       </div>
                       <div class="form-group">
                           <label>Setting Value</label>
                           <input type="text" name="setting_value" class="form-control" required>
                       </div>
                   </form>
               </div>
               <div class="modal-footer">
                   <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                   <button type="button" class="btn btn-primary" onclick="saveSetting()">Save Changes</button>
               </div>
           </div>
       </div>
   </div>

   <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
   <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
   <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

   <script>
   function editSetting(id) {
       $.get('get_setting.php?id=' + id, function(setting) {
           $('#editSettingForm input[name="setting_id"]').val(setting.id);
           $('#editSettingForm input[name="setting_name"]').val(setting.name);
           $('#editSettingForm input[name="setting_value"]').val(setting.value);
           $('#editSettingModal').modal('show');
       });
   }

   function saveSetting() {
       $.post('update_setting.php', $('#editSettingForm').serialize(), function(response) {
           if(response.success) {
               location.reload();
           }
       });
   }

   function deleteSetting(id) {
       if(confirm('Are you sure you want to delete this setting?')) {
           $.post('delete_setting.php', {id: id}, function(response) {
               if(response.success) {
                   location.reload();
               }
           });
       }
   }
   </script>

   <?php include "../includes/footer.php"; ?>
</div>
</body>
</html>
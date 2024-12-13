<?php
session_start();

// Check if the user is logged in and has admin role
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== 'admin'){
    header("location: login.php");
    exit;
}

require_once "../includes/db_connect.php";

// Define variables and initialize with empty values
$setting_name = $setting_value = "";
$setting_name_err = $setting_value_err = "";

// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){
    // Validate setting name
    if(empty(trim($_POST["setting_name"]))){
        $setting_name_err = "Please enter a setting name.";
    } else {
        // Check if setting name exists
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
    
    // Validate setting value
    if(empty(trim($_POST["setting_value"]))){
        $setting_value_err = "Please enter a setting value.";     
    } else{
        $setting_value = trim($_POST["setting_value"]);
    }
    
    // Check input errors before inserting in database
    if(empty($setting_name_err) && empty($setting_value_err)){
        $sql = "INSERT INTO settings (name, value) VALUES (?, ?)";
        
        if($stmt = $conn->prepare($sql)){
            $stmt->bind_param("ss", $setting_name, $setting_value);
            
            if($stmt->execute()){
                $_SESSION['success_message'] = "Setting added successfully.";
                header("location: ?page=system_settings");
                exit();
            } else{
                $_SESSION['error_message'] = "Error adding setting: " . $stmt->error;
            }
            $stmt->close();
        }
    }
}

// Fetch all settings
$sql = "SELECT * FROM settings ORDER BY name ASC";
$result = $conn->query($sql);
?>

<div class="container-fluid">
    <!-- Page Header -->
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
                <form action="?page=system_settings" method="post">
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
                                            <a href="?page=edit_setting&id=<?php echo $row['id']; ?>" 
                                               class="btn btn-primary btn-sm" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="?page=delete_setting&id=<?php echo $row['id']; ?>" 
                                               class="btn btn-danger btn-sm" 
                                               onclick="return confirm('Are you sure you want to delete this setting?');"
                                               title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </a>
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

<!-- Add required scripts -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<?php $conn->close(); ?>
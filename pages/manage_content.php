<?php
// Initialize the session
session_start();

// Check if the user is logged in, if not then redirect him to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

// Check if the user has the 'admin' role, if not then redirect to home page
if($_SESSION["role"] !== 'admin'){
    header("location: home.php");
    exit;
}

// Include config file
require_once "../includes/db_connect.php";

// Define variables and initialize with empty values
$content_title = $content_body = "";
$content_title_err = $content_body_err = "";

// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){

    // Validate content title
    if(empty(trim($_POST["content_title"]))){
        $content_title_err = "Please enter a content title.";
    } else{
        $content_title = trim($_POST["content_title"]);
    }

    // Validate content body
    if(empty(trim($_POST["content_body"]))){
        $content_body_err = "Please enter content body.";     
    } else{
        $content_body = trim($_POST["content_body"]);
    }
    
    // Check input errors before inserting in database
    if(empty($content_title_err) && empty($content_body_err)){
        
        // Prepare an insert statement
        $sql = "INSERT INTO content (title, body) VALUES (?, ?)";
         
        if($stmt = mysqli_prepare($conn, $sql)){
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "ss", $param_content_title, $param_content_body);
            
            // Set parameters
            $param_content_title = $content_title;
            $param_content_body = $content_body;
            
            // Attempt to execute the prepared statement
            if(mysqli_stmt_execute($stmt)){
                // Redirect to content management page
                header("location: manage_content.php");
            } else{
                echo "Oops! Something went wrong. Please try again later.";
            }

            // Close statement
            mysqli_stmt_close($stmt);
        }
    }

    // Close connection
    mysqli_close($conn);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Content Management</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            font: 14px sans-serif;
            background-color: #f4f4f4;
            display: flex;
            flex-direction: column; 
            min-height: 100vh; 
        }

        .wrapper {
            background: #fff;
            border-radius: 5px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            padding: 40px;
            width: 80%;
            max-width: 1200px; 
            margin: 50px auto; 
            flex-grow: 1; 
            width: 600px;
        }

        .wrapper h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        .wrapper .btn {
            display: inline-block; 
            margin-bottom: 10px; 
            margin-right: 10px; 
        }

        .wrapper a {
            color: #fff; 
        }

        .table {
            width: 100%;
            max-width: 100%; 
            margin-bottom: 20px;
        }

        .table th, .table td {
            padding: 10px;
            vertical-align: middle; 
        }
        .wrapper a {
            color: #000; 
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <?php include "../includes/header.php"; ?>

        <h2>Content Management</h2>

        <h3>Add New Content</h3>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group">
                <label>Title</label>
                <input type="text" name="content_title" class="form-control <?php echo (!empty($content_title_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $content_title; ?>">
                <span class="invalid-feedback"><?php echo $content_title_err; ?></span>
            </div>    
            <div class="form-group">
                <label>Body</label>
                <textarea name="content_body" class="form-control <?php echo (!empty($content_body_err)) ? 'is-invalid' : ''; ?>"><?php echo $content_body; ?></textarea>
                <span class="invalid-feedback"><?php echo $content_body_err; ?></span>
            </div>
            <div class="form-group">
                <input type="submit" class="btn btn-primary" value="Add">
                <input type="reset" class="btn btn-secondary ml-2" value="Reset">
            </div>
        </form>

        <h3>Existing Content</h3>

        <?php
        // Fetch all content from the database
        $sql = "SELECT id, title, body FROM content"; 
        $result = $conn->query($sql);

        // Error handling
        if (!$result) {
            die("Error fetching content: " . $conn->error);
        }
        ?>

        <table class="table table-bordered"> 
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Body</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row["id"]); ?></td> 
                        <td><?php echo htmlspecialchars($row["title"]); ?></td> 
                        <td><?php echo htmlspecialchars($row["body"]); ?></td> 
                        <td>
                            <a href="edit_content.php?id=<?php echo htmlspecialchars($row["id"]); ?>">Edit</a> |
                            <a href="delete_content.php?id=<?php echo htmlspecialchars($row["id"]); ?>" onclick="return confirm('Are you sure you want to delete this content?');">Delete</a>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>

        <?php
        $conn->close();
        include "../includes/footer.php";
        ?>
    </div>
</body>
</html>
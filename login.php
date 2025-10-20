<?php
// Start session management
session_start();
// Include the configuration file for database connection
require_once 'config.php';

// Redirect logged-in users to the index page
if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
    header("location: index.php");
    exit;
}

$username = $password = "";
$username_err = $password_err = $login_err = "";

if($_SERVER["REQUEST_METHOD"] == "POST"){
    
    // Basic validation
    if(empty(trim($_POST["username"]))){ $username_err = "Please enter username."; } else{ $username = trim($_POST["username"]); }
    if(empty(trim($_POST["password"]))){ $password_err = "Please enter your password."; } else{ $password = trim($_POST["password"]); }
    
    // Validate credentials
    if(empty($username_err) && empty($password_err)){
        $sql = "SELECT id, username, password FROM users WHERE username = ?";
        
        if($stmt = mysqli_prepare($link, $sql)){
            mysqli_stmt_bind_param($stmt, "s", $param_username);
            $param_username = $username;
            
            if(mysqli_stmt_execute($stmt)){
                mysqli_stmt_store_result($stmt);
                
                // Check if username exists, if yes then verify password
                if(mysqli_stmt_num_rows($stmt) == 1){                    
                    mysqli_stmt_bind_result($stmt, $id, $username, $hashed_password);
                    if(mysqli_stmt_fetch($stmt)){
                        // Use password_verify() to check password against the hash
                        if(password_verify($password, $hashed_password)){
                            // Password is correct, start a new session (SESSION MANAGEMENT)
                            $_SESSION["loggedin"] = true;
                            $_SESSION["id"] = $id;
                            $_SESSION["username"] = $username;
                            
                            // Redirect user to the main CRUD page
                            header("location: index.php");
                        } else{
                            $login_err = "Invalid username or password.";
                        }
                    }
                } else{
                    $login_err = "Invalid username or password.";
                }
            }
            mysqli_stmt_close($stmt);
        }
    }
    mysqli_close($link);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
</head>
<body>
    <h2>User Login</h2>
    <?php 
    if(!empty($login_err)){
        echo '<div style="color:red;">' . $login_err . '</div>';
    }        
    ?>
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
        <div>
            <label>Username</label>
            <input type="text" name="username" value="<?php echo htmlspecialchars($username); ?>">
            <span><?php echo $username_err; ?></span>
        </div>    
        <div>
            <label>Password</label>
            <input type="password" name="password">
            <span><?php echo $password_err; ?></span>
        </div>
        <div>
            <input type="submit" value="Log In">
        </div>
        <p>Don't have an account? <a href="register.php">Sign up now</a>.</p>
    </form>
</body>
</html>

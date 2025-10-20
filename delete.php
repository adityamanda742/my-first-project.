<?php
session_start();
require_once 'config.php';
if(!isset($_SESSION["loggedin"])) { header("location: login.php"); exit; }

if(isset($_GET["id"]) && !empty(trim($_GET["id"]))){
    $id = trim($_GET["id"]);

    // Security Check: Verify post ownership before allowing deletion confirmation
    $sql_check = "SELECT user_id FROM posts WHERE id = ?";
    if($stmt_check = mysqli_prepare($link, $sql_check)){
        mysqli_stmt_bind_param($stmt_check, "i", $param_id_check);
        $param_id_check = $id;
        if(mysqli_stmt_execute($stmt_check)){
            $result_check = mysqli_stmt_get_result($stmt_check);
            if(mysqli_num_rows($result_check) == 1){
                $row_check = mysqli_fetch_array($result_check, MYSQLI_ASSOC);
                if ($row_check['user_id'] != $_SESSION['id']) {
                    die("ERROR: You are not authorized to delete this post.");
                }
            } else { die("Error: Post not found."); }
        }
        mysqli_stmt_close($stmt_check);
    }
    
    // Process form submission to execute the delete
    if($_SERVER["REQUEST_METHOD"] == "POST"){
        $sql = "DELETE FROM posts WHERE id = ?";
        if($stmt = mysqli_prepare($link, $sql)){
            mysqli_stmt_bind_param($stmt, "i", $param_id);
            $param_id = $id;
            
            if(mysqli_stmt_execute($stmt)){
                header("location: index.php");
                exit();
            } else{
                echo "Oops! Something went wrong. Please try again later.";
            }
            mysqli_stmt_close($stmt);
        }
        mysqli_close($link);
    }
} else { header("location: index.php"); exit(); }
?>
<!DOCTYPE html>
<html>
<body>
    <h2>Delete Post</h2>
    <form action="delete.php?id=<?php echo htmlspecialchars($id); ?>" method="post">
        <p>Are you sure you want to delete this post?</p>
        <input type="submit" value="Yes, Delete">
        <a href="index.php">No, Cancel</a>
    </form>
</body>
</html>

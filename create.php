<?php
session_start();
require_once 'config.php';
if(!isset($_SESSION["loggedin"])) { header("location: login.php"); exit; }

$title = $content = "";
$title_err = $content_err = "";

if($_SERVER["REQUEST_METHOD"] == "POST"){
    // 1. Basic Validation (you should add more robust validation)
    if(empty(trim($_POST["title"]))){ $title_err = "Please enter a title."; } else { $title = trim($_POST["title"]); }
    if(empty(trim($_POST["content"]))){ $content_err = "Please enter content."; } else { $content = trim($_POST["content"]); }

    if(empty($title_err) && empty($content_err)){
        // 2. Prepare an INSERT statement
        $sql = "INSERT INTO posts (title, content, user_id) VALUES (?, ?, ?)";
        
        if($stmt = mysqli_prepare($link, $sql)){
            mysqli_stmt_bind_param($stmt, "ssi", $param_title, $param_content, $param_user_id);
            
            $param_title = $title;
            $param_content = $content;
            $param_user_id = $_SESSION["id"]; // Get user ID from session for linking
            
            if(mysqli_stmt_execute($stmt)){
                header("location: index.php"); // Redirect to post list on success
                exit();
            } else{
                echo "Something went wrong. Please try again later.";
            }
            mysqli_stmt_close($stmt);
        }
    }
    mysqli_close($link);
}
?>
<!DOCTYPE html>
<html lang="en">
<head><title>Create Post</title></head>
<body>
    <h2>Create New Post</h2>
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
        <div>
            <label>Title</label>
            <input type="text" name="title" value="<?php echo htmlspecialchars($title); ?>">
            <span><?php echo $title_err; ?></span>
        </div>    
        <div>
            <label>Content</label>
            <textarea name="content"><?php echo htmlspecialchars($content); ?></textarea>
            <span><?php echo $content_err; ?></span>
        </div>
        <div>
            <input type="submit" value="Publish Post">
            <a href="index.php">Cancel</a>
        </div>
    </form>
</body>
</html>

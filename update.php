<?php
session_start();
require_once 'config.php';
if(!isset($_SESSION["loggedin"])) { header("location: login.php"); exit; }

$id = $_GET["id"] ?? null; 
$title = $content = "";
$title_err = $content_err = "";

// 1. FETCH POST DATA (for pre-filling form)
if ($id && $_SERVER["REQUEST_METHOD"] != "POST") {
    $sql = "SELECT title, content, user_id FROM posts WHERE id = ?";
    if($stmt = mysqli_prepare($link, $sql)){
        mysqli_stmt_bind_param($stmt, "i", $param_id);
        $param_id = $id;
        if(mysqli_stmt_execute($stmt) && mysqli_stmt_bind_result($stmt, $title, $content, $user_id) && mysqli_stmt_fetch($stmt)){
            // Authorization Check
            if ($user_id != $_SESSION['id']) {
                die("ERROR: You are not authorized to edit this post.");
            }
        } else {
            die("Error: Post not found.");
        }
        mysqli_stmt_close($stmt);
    }
}

// 2. PROCESS FORM SUBMISSION
if($_SERVER["REQUEST_METHOD"] == "POST"){
    // ... (Validation logic here, similar to create.php) ...
    $title = trim($_POST["title"]);
    $content = trim($_POST["content"]);

    if(empty($title_err) && empty($content_err)){
        // Prepare an UPDATE statement
        // Note: The WHERE clause includes user_id for security (only update if owned by the user)
        $sql = "UPDATE posts SET title = ?, content = ? WHERE id = ? AND user_id = ?";
        if($stmt = mysqli_prepare($link, $sql)){
            mysqli_stmt_bind_param($stmt, "ssii", $param_title, $param_content, $param_id, $param_user_id);
            
            $param_title = $title;
            $param_content = $content;
            $param_id = $id;
            $param_user_id = $_SESSION["id"];
            
            if(mysqli_stmt_execute($stmt)){
                header("location: index.php");
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
<head><title>Update Post</title></head>
<body>
    <h2>Update Post</h2>
    <form action="update.php?id=<?php echo htmlspecialchars($id); ?>" method="post">
        <label>Title:</label><input type="text" name="title" value="<?php echo htmlspecialchars($title); ?>">
        <label>Content:</label><textarea name="content"><?php echo htmlspecialchars($content); ?></textarea>
        <input type="submit" value="Update Post">
    </form>
</body>
</html>

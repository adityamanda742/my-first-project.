<?php
session_start();
require_once 'config.php';
 
// Authentication Check: Redirect to login if user is not logged in
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head><title>Blog Posts (Read)</title></head>
<body>
    <h1>Welcome, <?php echo htmlspecialchars($_SESSION["username"]); ?>.</h1>
    <p><a href="create.php">➕ New Post</a> | <a href="logout.php">🚪 Logout</a></p>

    <h2>Blog Posts</h2>
    <?php
    // Query to retrieve all posts along with the author's username
    $sql = "SELECT p.id, p.title, p.content, p.created_at, u.username, p.user_id 
            FROM posts p INNER JOIN users u ON p.user_id = u.id ORDER BY p.created_at DESC";
    
    if($result = mysqli_query($link, $sql)){
        if(mysqli_num_rows($result) > 0){
            while($row = mysqli_fetch_array($result)){
                echo "<div>";
                    echo "<h3>" . htmlspecialchars($row['title']) . "</h3>";
                    echo "<p>By " . htmlspecialchars($row['username']) . " on " . $row['created_at'] . "</p>";
                    // Display a snippet of the content
                    echo "<p>" . substr(htmlspecialchars($row['content']), 0, 150) . "...</p>";
                    
                    // CRUD Actions
                    // Authorization Check: Only show Edit/Delete if the post belongs to the logged-in user
                    if ($row['user_id'] === $_SESSION['id']) {
                        echo "<a href='update.php?id=". $row['id'] . "'>✏️ Edit</a> | ";
                        echo "<a href='delete.php?id=". $row['id'] . "'>🗑️ Delete</a>";
                    }
                echo "</div><hr>";
            }
            mysqli_free_result($result);
        } else{
            echo '<p>No posts found. Create one!</p>';
        }
    }
    mysqli_close($link);
    ?>
</body>
</html>

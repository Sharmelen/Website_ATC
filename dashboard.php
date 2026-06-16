<?php
session_start();

/* CACHE CONTROL (IMPORTANT for back button) */
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

if (!isset($_SESSION['logged_in'])) {
    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>ATC Dashboard</title>
</head>
<body>
    <h1>Welcome <?php echo htmlspecialchars($_SESSION['username']); ?></h1>

    <a href="logout.php">Logout</a>
</body>
</html>
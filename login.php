<?php

session_start();

/* prevent logged-in users from going back to login page */
if (isset($_SESSION['logged_in'])) {
    header("Location: dashboard.php");
    exit;
}

/* prevent browser cache (BACK button fix) */
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");


/* DATABASE SETTINGS */
$host = "localhost";
$dbname = "atc_database";
$dbuser = "root";
$dbpass = "";

/* CONNECT TO DATABASE */
try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $dbuser,
        $dbpass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
} catch (PDOException $e) {
    die("Database Connection Failed: " . $e->getMessage());
}

$error = "";

/* LOGIN PROCESS */
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    /* FIX: check actual password, not hashed input */
    if (empty($username) || empty($password)) {

        $error = "Please enter username and password.";

    } else {

        $stmt = $pdo->prepare(
            "SELECT * FROM atc_user WHERE username = ? LIMIT 1"
        );

        $stmt->execute([$username]);

        $user = $stmt->fetch();

        if ($user) {

            /* SHA-256 HASH CHECK */
            $hashedInput = hash('sha256', $password);

            if ($hashedInput === $user['pwd']) {

                session_regenerate_id(true);

                $_SESSION['logged_in'] = true;
                $_SESSION['username'] = $user['username'];

                header("Location: dashboard.php");
                exit;

            } else {
                $error = "Invalid password.";
            }

        } else {
            $error = "User not found.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>ATC Login</title>

<style>
body{
    background:#0b1726;
    color:white;
    font-family:Arial,sans-serif;
    display:flex;
    justify-content:center;
    align-items:center;
    height:100vh;
}

.container{
    width:350px;
    background:#132238;
    padding:30px;
    border-radius:10px;
}

input{
    width:100%;
    padding:12px;
    margin-bottom:10px;
    border:none;
    border-radius:5px;
}

button{
    width:100%;
    padding:12px;
    border:none;
    background:#00d084;
    cursor:pointer;
    font-weight:bold;
}

.error{
    color:#ff6b6b;
    margin-bottom:10px;
}
</style>

</head>
<body>

<div class="container">

<h2>ATC Login</h2>

<?php if (!empty($error)): ?>
<div class="error">
    <?php echo htmlspecialchars($error); ?>
</div>
<?php endif; ?>

<form method="POST">

    <input type="text" name="username" placeholder="Username" required>
    <input type="password" name="password" placeholder="Password" required>

    <button type="submit">Login</button>

</form>

</div>

</body>
</html>
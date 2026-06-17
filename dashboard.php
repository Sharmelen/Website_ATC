<?php
session_start();

if (!isset($_SESSION['logged_in'])) {
    header("Location: login.php");
    exit;
}

header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

$conn = new mysqli("localhost", "root", "", "atc_database");

if ($conn->connect_error) {
    die("Database connection failed.");
}

/* =========================
   USER
========================= */
$username = $_SESSION['username'];

/* =========================
   HANDLE PASSWORD CHANGE
========================= */
$passMsg = "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['change_password'])) {

    $old = $_POST['old_password'] ?? '';
    $new = $_POST['new_password'] ?? '';

    if (empty($old) || empty($new)) {
        $passMsg = "Please fill in all fields.";
    } else {

        $stmt = $conn->prepare("SELECT pwd FROM atc_user WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();

        if ($user) {

            $oldHash = hash('sha256', $old);

            if ($oldHash === $user['pwd']) {

                $newHash = hash('sha256', $new);

                $update = $conn->prepare("UPDATE atc_user SET pwd = ? WHERE username = ?");
                $update->bind_param("ss", $newHash, $username);
                $update->execute();

                $passMsg = "Password changed successfully ✔";

            } else {
                $passMsg = "Old password is incorrect.";
            }
        }
    }
}

/* =========================
   GET USER DATA
========================= */
$stmt = $conn->prepare("
SELECT *
FROM atc_bio
WHERE username = ?
LIMIT 1
");
$stmt->bind_param("s", $username);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

/* =========================
   EXPIRY
========================= */
$in30 = date('Y-m-d', strtotime('+30 days'));

function expCount($conn, $username, $col, $in30) {
    $q = $conn->prepare("
        SELECT COUNT(*) AS total
        FROM atc_bio
        WHERE username = ?
        AND $col <= ?
    ");
    $q->bind_param("ss", $username, $in30);
    $q->execute();
    return $q->get_result()->fetch_assoc()['total'];
}

$medical = expCount($conn, $username, "medical_expiry", $in30);
$cat     = expCount($conn, $username, "cat_expiry", $in30);
$elpt    = expCount($conn, $username, "elpt_expiry", $in30);

$total = $medical + $cat + $elpt;

?>

<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>ATC Personal Portal</title>

<style>
body{
    margin:0;
    font-family:Segoe UI;
    background:#08111f;
    color:white;
}

.header{
    background:#0c1d30;
    padding:15px 20px;
    display:flex;
    justify-content:space-between;
    border-bottom:2px solid #00d9ff;
}

.logout{
    color:#ff5c5c;
    text-decoration:none;
    font-weight:bold;
}

.tabs{
    display:flex;
    background:#10233d;
}

.tab-button{
    padding:15px 20px;
    cursor:pointer;
    background:none;
    border:none;
    color:white;
    font-weight:bold;
}

.tab-button:hover{
    background:#17324f;
}

.tab-content{
    display:none;
    padding:25px;
}

.active{display:block;}

.box{
    background:#10233d;
    padding:20px;
    border-radius:12px;
    margin-top:15px;
}

.warning{
    background:#0d1b2a;
    border-left:5px solid #ffcc00;
    padding:20px;
    border-radius:12px;
}

.item{
    padding:8px 0;
    border-bottom:1px solid rgba(255,255,255,0.1);
}

input{
    padding:10px;
    width:250px;
    border:none;
    border-radius:6px;
    margin-top:8px;
}

button{
    padding:10px 15px;
    border:none;
    background:#00d9ff;
    cursor:pointer;
    border-radius:6px;
    margin-top:10px;
}

.msg{
    margin-top:10px;
    color:#00ff88;
}

h2{color:#00d9ff;}
</style>

<script>
function openTab(id){
    let tabs = document.getElementsByClassName("tab-content");
    for(let i=0;i<tabs.length;i++){
        tabs[i].style.display="none";
    }
    document.getElementById(id).style.display="block";
}
</script>

</head>

<body>

<div class="header">
<h2>ATC Personal Portal</h2>
<div>
Welcome <?php echo $user['full_name']; ?> |
<a class="logout" href="logout.php">Logout</a>
</div>
</div>

<div class="tabs">
<button class="tab-button" onclick="openTab('dashboard')">📊 Dashboard</button>
<button class="tab-button" onclick="openTab('biodata')">👤 Biodata</button>
<button class="tab-button" onclick="openTab('changepass')">🔑 Change Password</button>
</div>

<!-- DASHBOARD -->
<div id="dashboard" class="tab-content active">

<div class="box">
<h2>Dashboard Overview</h2>
<p>Your Personal Expiry & Pending Tasks Tracking System .</p>
</div>

<div class="warning">
<h3>⚠ Expiry Status</h3>

<p class="item">Total alerts: <b><?php echo $total; ?></b></p>

<?php if ($medical > 0): ?>
<p class="item">Medical: Will Expire in 30 Days</p>
<?php endif; ?>

<?php if ($cat > 0): ?>
<p class="item">CAT: Will Expire in 30 Days></p>
<?php endif; ?>

<?php if ($elpt > 0): ?>
<p class="item">ELPT: Will Expire in 30 Days</p>
<?php endif; ?>

<?php if ($total == 0): ?>
<p style="color:#00ff88;">✅ No upcoming expiries</p>
<?php endif; ?>

</div>

</div>

<!-- BIODATA -->
<div id="biodata" class="tab-content">

<div class="box">
<h2>My Biodata</h2>

<p><b>Name:</b> <?php echo $user['full_name']; ?></p>
<p><b>Pangkat:</b> <?php echo $user['rank']; ?></p>
<p><b>No Tentera:</b> <?php echo $user['no_ten']; ?></p>
<p><b>Base:</b> <?php echo $user['base']; ?></p>
<p><b>Jawatan:</b> <?php echo $user['jawatan']; ?></p>
<p><b>ELPT:</b> <?php echo $user['elpt_lvl']; ?></p>
<p><b>Medical:</b> <?php echo $user['medical_expiry']; ?></p>
<p><b>CAT:</b> <?php echo $user['cat_expiry']; ?></p>

</div>

</div>

<!-- CHANGE PASSWORD -->
<div id="changepass" class="tab-content">

<div class="box">
<h2>Change Password</h2>

<form method="POST">

<p>Old Password</p>
<input type="password" name="old_password" required>

<p>New Password</p>
<input type="password" name="new_password" required>

<br>

<button type="submit" name="change_password">
Update Password
</button>

</form>

<?php if (!empty($passMsg)): ?>
<div class="msg">
<?php echo $passMsg; ?>
</div>
<?php endif; ?>

</div>

</div>

</body>
</html>
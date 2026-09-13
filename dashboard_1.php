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

        $stmt = $conn->prepare("
            SELECT pwd
            FROM atc_user
            WHERE username = ?
        ");

        $stmt->bind_param("s", $username);
        $stmt->execute();

        $userPassword = $stmt->get_result()->fetch_assoc();

        if ($userPassword) {

            $oldHash = hash('sha256', $old);

            if ($oldHash === $userPassword['pwd']) {

                $newHash = hash('sha256', $new);

                $update = $conn->prepare("
                    UPDATE atc_user
                    SET pwd = ?
                    WHERE username = ?
                ");

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
   USER PHOTO
========================= */

$photoPath = "photos/" . $username . ".jpeg";


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

$medical = expCount(
    $conn,
    $username,
    "medical_expiry",
    $in30
);

$cat = expCount(
    $conn,
    $username,
    "cat_expiry",
    $in30
);

$elpt = expCount(
    $conn,
    $username,
    "elpt_expiry",
    $in30
);

$total = $medical + $cat + $elpt;

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="utf-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1"
>

<title>ATC Personal Portal</title>


<style>

/* =========================================================
   GLOBAL
========================================================= */

*{
    box-sizing:border-box;
}

body{

    margin:0;

    font-family:"Segoe UI",Arial,sans-serif;

    background:
        radial-gradient(
            circle at center,
            #102943 0%,
            #081525 45%,
            #050d17 100%
        );

    color:white;

    min-height:100vh;

}


/* =========================================================
   HEADER
========================================================= */

.header{

    background:
        linear-gradient(
            90deg,
            #081525,
            #0c1d30,
            #081525
        );

    padding:15px 25px;

    display:flex;

    justify-content:space-between;

    align-items:center;

    border-bottom:2px solid #00d9ff;

    box-shadow:
        0 3px 15px rgba(0,217,255,0.15);

}


.header h2{

    margin:0;

    color:#00d9ff;

    letter-spacing:1px;

}


.logout{

    color:#ff5c5c;

    text-decoration:none;

    font-weight:bold;

    transition:0.3s;

}


.logout:hover{

    color:#ff8888;

    text-shadow:
        0 0 8px rgba(255,92,92,0.6);

}


/* =========================================================
   TABS
========================================================= */

.tabs{

    display:flex;

    background:#10233d;

    border-bottom:
        1px solid rgba(0,217,255,0.25);

}


.tab-button{

    padding:15px 20px;

    cursor:pointer;

    background:none;

    border:none;

    color:white;

    font-weight:bold;

    transition:0.3s;

}


.tab-button:hover{

    background:#17324f;

    color:#00d9ff;

}


/* =========================================================
   TAB CONTENT
========================================================= */

.tab-content{

    display:none;

    padding:30px;

}


.active{

    display:block;

}


/* =========================================================
   GENERAL BOX
========================================================= */

.box{

    background:
        linear-gradient(
            145deg,
            rgba(16,35,61,0.98),
            rgba(10,27,46,0.98)
        );

    padding:25px;

    border-radius:14px;

    margin-top:15px;

    border:
        1px solid rgba(0,217,255,0.12);

    box-shadow:
        0 10px 30px rgba(0,0,0,0.25);

}


/* =========================================================
   WARNING
========================================================= */

.warning{

    background:#0d1b2a;

    border-left:
        5px solid #ffcc00;

    padding:20px;

    border-radius:12px;

    margin-top:20px;

}


.item{

    padding:8px 0;

    border-bottom:
        1px solid rgba(255,255,255,0.1);

}


/* =========================================================
   INPUT
========================================================= */

input{

    padding:10px;

    width:250px;

    border:none;

    border-radius:6px;

    margin-top:8px;

    background:#eaf4f8;

}


/* =========================================================
   BUTTON
========================================================= */

button{

    padding:10px 15px;

    border:none;

    background:#00d9ff;

    color:#00131c;

    cursor:pointer;

    border-radius:6px;

    margin-top:10px;

    font-weight:bold;

    transition:0.3s;

}


button:hover{

    background:#00ff99;

    box-shadow:
        0 0 12px rgba(0,255,153,0.4);

}


/* =========================================================
   MESSAGE
========================================================= */

.msg{

    margin-top:10px;

    color:#00ff88;

}


/* =========================================================
   HEADINGS
========================================================= */

h2{

    color:#00d9ff;

}


/* =========================================================
   BIODATA LAYOUT
========================================================= */

.biodata-layout{

    display:flex;

    gap:50px;

    align-items:flex-start;

    margin-top:20px;

}


/* =========================================================
   PHOTO SECTION
========================================================= */

.photo-section{

    width:290px;

    min-width:290px;

    display:flex;

    justify-content:center;

    align-items:center;

    padding-top:10px;

}


/* =========================================================
   ATC PHOTO FRAME
========================================================= */

.atc-photo-frame{

    width:280px;

    height:300px;

    position:relative;

    display:flex;

    justify-content:center;

    align-items:center;

}


/* =========================================================
   OUTER RADAR RING
========================================================= */

.atc-photo-frame::before{

    content:"";

    position:absolute;

    width:250px;

    height:250px;

    border-radius:50%;

    border:
        1px solid rgba(0,217,255,0.28);

    border-top-color:#00d9ff;

    animation:
        radarRotate 14s linear infinite;

    z-index:1;

}


/* =========================================================
   SECOND RADAR RING
========================================================= */

.atc-photo-frame::after{

    content:"";

    position:absolute;

    width:270px;

    height:270px;

    border-radius:50%;

    border:
        1px dashed rgba(0,255,153,0.16);

    animation:
        radarRotateReverse 22s linear infinite;

    z-index:1;

}


/* =========================================================
   PHOTO CONTAINER
========================================================= */

.photo-container{

    width:190px;

    height:235px;

    position:relative;

    z-index:5;

    overflow:hidden;

    border-radius:10px;

    border:
        2px solid #00d9ff;

    background:#07131f;

    box-shadow:

        0 0 15px rgba(0,217,255,0.25),

        inset 0 0 20px rgba(0,217,255,0.08);

}


/* =========================================================
   USER PHOTO
========================================================= */

.profile-photo{

    width:100%;

    height:100%;

    object-fit:cover;

    display:block;

}


/* =========================================================
   THREE MOVING ATC DOTS
   THESE ARE BEHIND THE PHOTO
========================================================= */

.orbit-dot{

    position:absolute;

    top:50%;

    left:50%;

    border-radius:50%;

    z-index:2;

}


/* =========================================================
   DOT 1
========================================================= */

.orbit-dot.one{

    width:7px;

    height:7px;

    background:#00ff99;

    box-shadow:
        0 0 8px #00ff99;

    animation:
        orbitOne 8s linear infinite;

}


/* =========================================================
   DOT 2
========================================================= */

.orbit-dot.two{

    width:6px;

    height:6px;

    background:#00d9ff;

    box-shadow:
        0 0 8px #00d9ff;

    animation:
        orbitTwo 11s linear infinite;

}


/* =========================================================
   DOT 3
========================================================= */

.orbit-dot.three{

    width:5px;

    height:5px;

    background:#00ff99;

    box-shadow:
        0 0 7px #00ff99;

    animation:
        orbitThree 14s linear infinite;

}


/* =========================================================
   SMALL STATIC ATC MARKERS
========================================================= */

.marker{

    position:absolute;

    width:6px;

    height:6px;

    border-radius:50%;

    background:#00d9ff;

    box-shadow:
        0 0 7px #00d9ff;

    z-index:2;

}


.marker.one{

    top:28px;

    left:20px;

}


.marker.two{

    bottom:28px;

    right:20px;

}


.marker.three{

    top:70px;

    right:5px;

}


/* =========================================================
   PHOTO LABEL
========================================================= */

.photo-label{

    position:absolute;

    bottom:-25px;

    left:0;

    right:0;

    text-align:center;

    font-size:12px;

    letter-spacing:2px;

    color:#00d9ff;

    opacity:0.75;

    z-index:6;

}


/* =========================================================
   BIODATA INFORMATION
========================================================= */

.biodata-info{

    flex:1;

    max-width:700px;

}


.bio-row{

    display:flex;

    align-items:center;

    min-height:48px;

    border-bottom:
        1px solid rgba(255,255,255,0.08);

}


.bio-label{

    width:160px;

    color:#8edff2;

    font-weight:bold;

}


.bio-value{

    color:white;

}


/* =========================================================
   RADAR ROTATION
========================================================= */

@keyframes radarRotate{

    from{

        transform:rotate(0deg);

    }

    to{

        transform:rotate(360deg);

    }

}


@keyframes radarRotateReverse{

    from{

        transform:rotate(360deg);

    }

    to{

        transform:rotate(0deg);

    }

}


/* =========================================================
   DOT 1 ORBIT
========================================================= */

@keyframes orbitOne{

    0%{

        transform:
            rotate(0deg)
            translateX(135px)
            rotate(0deg);

    }

    100%{

        transform:
            rotate(360deg)
            translateX(135px)
            rotate(-360deg);

    }

}


/* =========================================================
   DOT 2 ORBIT
========================================================= */

@keyframes orbitTwo{

    0%{

        transform:
            rotate(120deg)
            translateX(135px)
            rotate(-120deg);

    }

    100%{

        transform:
            rotate(480deg)
            translateX(135px)
            rotate(-480deg);

    }

}


/* =========================================================
   DOT 3 ORBIT
========================================================= */

@keyframes orbitThree{

    0%{

        transform:
            rotate(240deg)
            translateX(135px)
            rotate(-240deg);

    }

    100%{

        transform:
            rotate(600deg)
            translateX(135px)
            rotate(-600deg);

    }

}


/* =========================================================
   MOBILE
========================================================= */

@media(max-width:700px){

    .header{

        flex-direction:column;

        gap:10px;

        align-items:flex-start;

    }


    .tabs{

        overflow-x:auto;

    }


    .tab-button{

        white-space:nowrap;

    }


    .tab-content{

        padding:15px;

    }


    .biodata-layout{

        flex-direction:column;

        align-items:center;

        gap:40px;

    }


    .photo-section{

        width:100%;

    }


    .biodata-info{

        width:100%;

    }


    .bio-row{

        flex-direction:column;

        align-items:flex-start;

        padding:12px 0;

        gap:5px;

    }


    .bio-label{

        width:auto;

    }

}

</style>


<script>

/* =========================================================
   TAB SYSTEM
========================================================= */

function openTab(id){

    let tabs =
        document.getElementsByClassName("tab-content");

    for(let i = 0; i < tabs.length; i++){

        tabs[i].style.display = "none";

    }

    document.getElementById(id).style.display = "block";

}

</script>

</head>


<body>


<!-- =====================================================
     HEADER
===================================================== -->

<div class="header">

    <h2>
        ATC Personal Portal
    </h2>


    <div>

        Welcome

        <?php
        echo htmlspecialchars(
            $user['full_name']
        );
        ?>

        |

        <a
            class="logout"
            href="logout.php"
        >
            Logout
        </a>

    </div>

</div>


<!-- =====================================================
     TABS
===================================================== -->

<div class="tabs">

    <button
        class="tab-button"
        onclick="openTab('dashboard')"
    >
        📊 Dashboard
    </button>


    <button
        class="tab-button"
        onclick="openTab('biodata')"
    >
        👤 Biodata
    </button>


    <button
        class="tab-button"
        onclick="openTab('changepass')"
    >
        🔑 Change Password
    </button>

</div>


<!-- =====================================================
     DASHBOARD
===================================================== -->

<div
    id="dashboard"
    class="tab-content active"
>

    <div class="box">

        <h2>
            Dashboard Overview
        </h2>

        <p>
            Your Personal Expiry & Pending Tasks Tracking System.
        </p>

    </div>


    <div class="warning">

        <h3>
            ⚠ Expiry Status
        </h3>


        <p class="item">

            Total alerts:

            <b>
                <?php echo $total; ?>
            </b>

        </p>


        <?php if ($medical > 0): ?>

            <p class="item">

                Medical:
                Will Expire in 30 Days

            </p>

        <?php endif; ?>


        <?php if ($cat > 0): ?>

            <p class="item">

                CAT:
                Will Expire in 30 Days

            </p>

        <?php endif; ?>


        <?php if ($elpt > 0): ?>

            <p class="item">

                ELPT:
                Will Expire in 30 Days

            </p>

        <?php endif; ?>


        <?php if ($total == 0): ?>

            <p style="color:#00ff88;">

                ✅ No upcoming expiries

            </p>

        <?php endif; ?>

    </div>

</div>


<!-- =====================================================
     BIODATA
===================================================== -->

<div
    id="biodata"
    class="tab-content"
>

    <div class="box">

        <h2>
            My Biodata
        </h2>


        <div class="biodata-layout">


            <!-- =========================================
                 PHOTO
            ========================================== -->

            <div class="photo-section">

                <div class="atc-photo-frame">


                    <!-- THREE MOVING DOTS -->

                    <div class="orbit-dot one"></div>

                    <div class="orbit-dot two"></div>

                    <div class="orbit-dot three"></div>


                    <!-- STATIC ATC MARKERS -->

                    <div class="marker one"></div>

                    <div class="marker two"></div>

                    <div class="marker three"></div>


                    <!-- PHOTO -->

                    <div class="photo-container">

                        <?php if (file_exists($photoPath)): ?>

                            <img
                                src="<?php echo htmlspecialchars($photoPath); ?>"
                                class="profile-photo"
                                alt="Profile Photo"
                            >

                        <?php else: ?>

                            <div
                                style="
                                    width:100%;
                                    height:100%;
                                    display:flex;
                                    align-items:center;
                                    justify-content:center;
                                    text-align:center;
                                    color:#66808f;
                                    font-size:13px;
                                    padding:20px;
                                "
                            >

                                PHOTO NOT FOUND

                            </div>

                        <?php endif; ?>

                    </div>


                    <div class="photo-label">

                        ATC PERSONNEL

                    </div>


                </div>

            </div>


            <!-- =========================================
                 BIODATA INFORMATION
            ========================================== -->

            <div class="biodata-info">


                <div class="bio-row">

                    <div class="bio-label">
                        Name
                    </div>

                    <div class="bio-value">

                        <?php

                        echo htmlspecialchars(
                            $user['full_name']
                        );

                        ?>

                    </div>

                </div>


                <div class="bio-row">

                    <div class="bio-label">
                        Pangkat
                    </div>

                    <div class="bio-value">

                        <?php

                        echo htmlspecialchars(
                            $user['rank']
                        );

                        ?>

                    </div>

                </div>


                <div class="bio-row">

                    <div class="bio-label">
                        No Tentera
                    </div>

                    <div class="bio-value">

                        <?php

                        echo htmlspecialchars(
                            $user['no_ten']
                        );

                        ?>

                    </div>

                </div>


                <div class="bio-row">

                    <div class="bio-label">
                        Base
                    </div>

                    <div class="bio-value">

                        <?php

                        echo htmlspecialchars(
                            $user['base']
                        );

                        ?>

                    </div>

                </div>


                <div class="bio-row">

                    <div class="bio-label">
                        Jawatan
                    </div>

                    <div class="bio-value">

                        <?php

                        echo htmlspecialchars(
                            $user['jawatan']
                        );

                        ?>

                    </div>

                </div>


                <div class="bio-row">

                    <div class="bio-label">
                        ELPT
                    </div>

                    <div class="bio-value">

                        <?php

                        echo htmlspecialchars(
                            $user['elpt_lvl']
                        );

                        ?>

                    </div>

                </div>


                <div class="bio-row">

                    <div class="bio-label">
                        Medical
                    </div>

                    <div class="bio-value">

                        <?php

                        echo htmlspecialchars(
                            $user['medical_expiry']
                        );

                        ?>

                    </div>

                </div>


                <div class="bio-row">

                    <div class="bio-label">
                        CAT
                    </div>

                    <div class="bio-value">

                        <?php

                        echo htmlspecialchars(
                            $user['cat_expiry']
                        );

                        ?>

                    </div>

                </div>


                <div class="bio-row">

                    <div class="bio-label">
                        CAT Location
                    </div>

                    <div class="bio-value">

                        <?php

                        echo htmlspecialchars(
                            $user['cat_loc']
                        );

                        ?>

                    </div>

                </div>


            </div>

        </div>

    </div>

</div>


<!-- =====================================================
     CHANGE PASSWORD
===================================================== -->

<div
    id="changepass"
    class="tab-content"
>

    <div class="box">

        <h2>
            Change Password
        </h2>


        <form method="POST">


            <p>
                Old Password
            </p>


            <input
                type="password"
                name="old_password"
                required
            >


            <p>
                New Password
            </p>


            <input
                type="password"
                name="new_password"
                required
            >


            <br>


            <button
                type="submit"
                name="change_password"
            >

                Update Password

            </button>


        </form>


        <?php if (!empty($passMsg)): ?>

            <div class="msg">

                <?php

                echo htmlspecialchars(
                    $passMsg
                );

                ?>

            </div>

        <?php endif; ?>


    </div>

</div>


</body>

</html>


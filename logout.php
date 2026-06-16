<?php
session_start();

$_SESSION = [];
session_destroy();

/* kill cache */
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");

header("Location: login.html");
exit;
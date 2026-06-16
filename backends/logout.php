<?php

session_start();

/* hapus semua session */
session_unset();

/* destroy session */
session_destroy();

/* redirect ke halaman login */
header("Location: login.php");
exit;

?>
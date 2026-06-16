<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

/* hanya admin */
if ($_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}
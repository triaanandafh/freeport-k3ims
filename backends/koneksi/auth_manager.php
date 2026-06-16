<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

/* hanya manager */
if ($_SESSION['role'] != 'manager') {
    header("Location: ../login.php");
    exit;
}
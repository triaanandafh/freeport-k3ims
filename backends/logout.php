<?php

session_start();
require 'koneksi/koneksi.php';
require 'koneksi/activity_logger.php';

/* catat logout SEBELUM session dihapus */
if (isset($_SESSION['user_id'])) {
    log_activity($conn, $_SESSION['user_id'], 'logout', 'auth', "User {$_SESSION['fullname']} logout");
}

/* hapus semua session */
session_unset();

/* destroy session */
session_destroy();

/* redirect ke halaman login */
header("Location: login.php");
exit;

?>
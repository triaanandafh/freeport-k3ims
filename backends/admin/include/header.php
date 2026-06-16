<?php
session_start();

/* dummy session sementara */
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1;
    $_SESSION['fullname'] = 'Admin Demo';
    $_SESSION['role'] = 'admin';
}

$page_title = $page_title ?? 'K3 IMS Freeport';
$current_page = $current_page ?? '';
?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= htmlspecialchars($page_title) ?></title>

    <!-- FontAwesome -->
    <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet">

    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,300,400,600,700,800,900"
        rel="stylesheet">

    <!-- SB Admin CSS -->
    <link href="../css/sb-admin-2.min.css" rel="stylesheet">

</head>

<body id="page-top">

<div id="wrapper">

    <?php require __DIR__ . '/sidebar.php'; ?>

    <div id="content-wrapper" class="d-flex flex-column">

        <div id="content">

            <?php require __DIR__ . '/navbar.php'; ?>

            <div class="container-fluid">
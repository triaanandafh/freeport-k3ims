<?php
session_start();
require 'koneksi/koneksi.php';
require 'koneksi/activity_logger.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $email = $_POST['email'];
    $password = $_POST['password'];

    $query = "SELECT * FROM users 
              WHERE email='$email' 
              AND password='$password'";

    $result = pg_query($conn, $query);

    if (pg_num_rows($result) > 0) {

        $user = pg_fetch_assoc($result);

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['fullname'] = $user['fullname'];
        $_SESSION['role'] = $user['role'];

        log_activity($conn, $user['id'], 'login', 'auth', "User {$user['fullname']} login sebagai {$user['role']}");

        if ($user['role'] == 'admin') {
            header("Location: admin/dashboard.php");
            exit;
        }

        if ($user['role'] == 'manager') {
            header("Location: manager/dashboard.php");
            exit;
        }

    } else {

        $email_safe = pg_escape_string($conn, $email);
        log_activity($conn, null, 'login_failed', 'auth', "Percobaan login gagal untuk email: $email_safe");

        $error = "Email atau password salah";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Login K3 IMS</title>

    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="css/sb-admin-2.min.css" rel="stylesheet">

</head>

<body class="bg-gradient-primary">

<div class="container">

    <div class="row justify-content-center">

        <div class="col-md-5">

            <div class="card shadow-lg mt-5">

                <div class="card-body p-5">

                    <div class="text-center mb-4">

                        <h4>K3 IMS Login</h4>

                    </div>

                    <?php if ($error != ''): ?>
                        <div class="alert alert-danger">
                            <?= $error ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST">

                        <div class="form-group">

                            <input
                                type="email"
                                name="email"
                                class="form-control"
                                placeholder="Email"
                                required>

                        </div>

                        <div class="form-group">

                            <input
                                type="password"
                                name="password"
                                class="form-control"
                                placeholder="Password"
                                required>

                        </div>

                        <button class="btn btn-primary btn-block">

                            Login

                        </button>

                    </form>

                </div>

            </div>

        </div>

    </div>

</div>

<script src="vendor/jquery/jquery.min.js"></script>
<script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

</body>
</html>
<?php
require '../koneksi/auth_admin.php';

$page_title = 'Dashboard K3 IMS';
$current_page = 'dashboard';

require 'include/header.php';

?>

<h1 class="h3 mb-4 text-gray-800">
    Dashboard
</h1>



<div class="card shadow mb-4">

    <div class="card-header py-3">

        <h6 class="m-0 font-weight-bold text-primary">
            Welcome Administrator
        </h6>

    </div>

    <div class="card-body">

        Sistem Informasi Manajemen K3 PT. Freeport Indonesia

    </div>

</div>

<?php require 'include/footer.php'; ?>
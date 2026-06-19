<?php
require '../koneksi/auth_admin.php';
require '../koneksi/koneksi.php';

$page_title = 'Dashboard K3 IMS';
$current_page = 'dashboard';

$total_employees = pg_fetch_result(
    pg_query($conn, "SELECT COUNT(*) FROM employees"),
    0, 0
);

$open_reports = pg_fetch_result(
    pg_query($conn, "SELECT COUNT(*) FROM reports WHERE status = 'open'"),
    0, 0
);

$active_documents = pg_fetch_result(
    pg_query($conn, "SELECT COUNT(*) FROM documents WHERE status = 'active'"),
    0, 0
);

$monthly_audits = pg_fetch_result(
    pg_query($conn, "
        SELECT COUNT(*) FROM audits
        WHERE date_trunc('month', audit_date) = date_trunc('month', CURRENT_DATE)
    "),
    0, 0
);

require 'include/header.php';

?>

<h1 class="h3 mb-4 text-gray-800">
    Dashboard Safety Monitoring
</h1>

<div class="row">

    <div class="col-xl-3 col-md-6 mb-4">

        <div class="card border-left-primary shadow h-100 py-2">

            <div class="card-body">

                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                    Total Employees
                </div>

                <div class="h5 mb-0 font-weight-bold text-gray-800">
                    <?= $total_employees ?>
                </div>

            </div>

        </div>

    </div>

    <div class="col-xl-3 col-md-6 mb-4">

        <div class="card border-left-danger shadow h-100 py-2">

            <div class="card-body">

                <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                    Open Reports
                </div>

                <div class="h5 mb-0 font-weight-bold text-gray-800">
                    <?= $open_reports ?>
                </div>

            </div>

        </div>

    </div>

    <div class="col-xl-3 col-md-6 mb-4">

        <div class="card border-left-success shadow h-100 py-2">

            <div class="card-body">

                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                    Active Documents
                </div>

                <div class="h5 mb-0 font-weight-bold text-gray-800">
                    <?= $active_documents ?>
                </div>

            </div>

        </div>

    </div>

    <div class="col-xl-3 col-md-6 mb-4">

        <div class="card border-left-warning shadow h-100 py-2">

            <div class="card-body">

                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                    Monthly Audits
                </div>

                <div class="h5 mb-0 font-weight-bold text-gray-800">
                    <?= $monthly_audits ?>
                </div>

            </div>

        </div>

    </div>

</div>

<div class="card shadow mb-4">

    <div class="card-header py-3">

        <h6 class="m-0 font-weight-bold text-primary">
            Welcome Administrator
        </h6>

    </div>

    <div class="card-body">

        Sistem Informasi Manajemen K3 berbasis simulasi perusahaan.

    </div>

</div>

<?php require 'include/footer.php'; ?>
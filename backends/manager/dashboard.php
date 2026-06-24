<?php
require '../koneksi/auth_manager.php';
require '../koneksi/koneksi.php';

$page_title = 'Dashboard';
$current_page = 'dashboard';


$cr_q = pg_query($conn, "
    SELECT COUNT(*) AS total, SUM(CASE WHEN is_compliant THEN 1 ELSE 0 END) AS compliant
    FROM safety_checks
");
$cr = pg_fetch_assoc($cr_q);
$compliance_rate = $cr['total'] > 0 ? round(($cr['compliant'] / $cr['total']) * 100, 1) : null;

$open_incidents = pg_fetch_result(
    pg_query($conn, "SELECT COUNT(*) FROM reports WHERE status = 'open'"), 0, 0
);

$open_capa = pg_fetch_result(
    pg_query($conn, "SELECT COUNT(*) FROM capa_items WHERE status IN ('open','in_progress')"), 0, 0
);

$overdue_capa = pg_fetch_result(
    pg_query($conn, "SELECT COUNT(*) FROM capa_items WHERE status != 'closed' AND due_date < CURRENT_DATE"), 0, 0
);

$expiring_docs = pg_fetch_result(
    pg_query($conn, "
        SELECT COUNT(*) FROM documents
        WHERE status = 'approved' AND expiry_date IS NOT NULL
        AND expiry_date BETWEEN CURRENT_DATE AND CURRENT_DATE + INTERVAL '30 days'
    "), 0, 0
);

$last_incident_q = pg_query($conn, "SELECT MAX(created_at) AS last_date FROM reports WHERE report_type = 'incident'");
$last_incident = pg_fetch_assoc($last_incident_q);
$safe_days = $last_incident['last_date'] ? floor((time() - strtotime($last_incident['last_date'])) / 86400) : null;

require 'include/header.php';
?>

<h1 class="h3 mb-4 text-gray-800">Dashboard Manager</h1>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Selamat Datang, <?= htmlspecialchars($_SESSION['fullname']) ?></h6>
    </div>
    <div class="card-body">
        Ringkasan kondisi K3 perusahaan saat ini.
    </div>
</div>

<div class="row">

    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Compliance Rate</div>
                <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $compliance_rate ?? '-' ?>%</div>
            </div>
        </div>
    </div>

    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card border-left-danger shadow h-100 py-2">
            <div class="card-body">
                <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Open Incidents</div>
                <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $open_incidents ?></div>
            </div>
        </div>
    </div>

    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Open CAPA Items</div>
                <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $open_capa ?></div>
            </div>
        </div>
    </div>

    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card border-left-danger shadow h-100 py-2">
            <div class="card-body">
                <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">CAPA Overdue</div>
                <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $overdue_capa ?></div>
            </div>
        </div>
    </div>

    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Documents Expiring Soon</div>
                <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $expiring_docs ?></div>
            </div>
        </div>
    </div>

    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Safe Days Counter</div>
                <div class="h5 mb-0 font-weight-bold text-gray-800">
                    <?= $safe_days !== null ? $safe_days . ' hari' : 'Belum ada insiden' ?>
                </div>
            </div>
        </div>
    </div>

</div>

<?php require 'include/footer.php'; ?>
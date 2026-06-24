<?php

require '../koneksi/auth_manager.php';
require '../koneksi/koneksi.php';

$page_title = "Safety Report";
$current_page = "safety_report";


/* filter bulan */
$filter_month = isset($_GET['month']) ? $_GET['month'] : date('Y-m');


/* =========================
   REKAP PER DEPARTEMEN
========================= */
$rekap = pg_query($conn, "

    SELECT
        departments.id AS dept_id,
        departments.department_name,

        COUNT(safety_checks.id) AS total_checks,

        SUM(CASE WHEN safety_checks.is_compliant = TRUE THEN 1 ELSE 0 END)      AS pass_count,
        SUM(CASE WHEN safety_checks.apd_compliant = TRUE THEN 1 ELSE 0 END)     AS apd_pass,
        SUM(CASE WHEN safety_checks.briefing_done = TRUE THEN 1 ELSE 0 END)     AS briefing_pass,
        SUM(CASE WHEN safety_checks.jsa_understood = TRUE THEN 1 ELSE 0 END)    AS jsa_pass,
        SUM(CASE WHEN safety_checks.equipment_checked = TRUE THEN 1 ELSE 0 END) AS equipment_pass

    FROM departments

    LEFT JOIN employees
    ON employees.department_id = departments.id

    LEFT JOIN safety_checks
    ON safety_checks.employee_id = employees.id
    AND TO_CHAR(safety_checks.check_date, 'YYYY-MM') = '$filter_month'

    GROUP BY departments.id, departments.department_name

    ORDER BY departments.department_name ASC

");

$rows = [];
while ($r = pg_fetch_assoc($rekap)) {
    $total = (int)$r['total_checks'];

    $r['safety_rate']   = $total > 0 ? round($r['pass_count']     / $total * 100) : 0;
    $r['apd_pct']       = $total > 0 ? round($r['apd_pass']       / $total * 100) : 0;
    $r['briefing_pct']  = $total > 0 ? round($r['briefing_pass']  / $total * 100) : 0;
    $r['jsa_pct']       = $total > 0 ? round($r['jsa_pass']       / $total * 100) : 0;
    $r['equipment_pct'] = $total > 0 ? round($r['equipment_pass'] / $total * 100) : 0;

    $r['audit_ready'] = $r['safety_rate'] >= 80;

    $rows[] = $r;
}

$chart_labels = [];
$chart_data   = [];
$chart_colors = [];

foreach ($rows as $r) {
    $chart_labels[] = $r['department_name'];
    $chart_data[]   = $r['safety_rate'];
    $chart_colors[] = $r['safety_rate'] >= 80 ? '#1cc88a' : ($r['safety_rate'] >= 60 ? '#f6c23e' : '#e74a3b');
}

require 'include/header.php';

?>


<h1 class="h3 mb-4 text-gray-800">
    Safety Report
</h1>


<!-- Filter bulan -->
<div class="card shadow mb-4">

    <div class="card-body py-3">

        <form method="GET" class="form-inline">

            <label class="mr-2 font-weight-bold">Periode :</label>

            <input
                type="month"
                name="month"
                class="form-control mr-2"
                value="<?= htmlspecialchars($filter_month) ?>">

            <button type="submit" class="btn btn-primary">
                <i class="fas fa-search fa-sm"></i> Tampilkan
            </button>

        </form>

    </div>

</div>


<!-- Summary Cards -->
<div class="row mb-4">

    <?php
    $total_dept  = count($rows);
    $ready_count = count(array_filter($rows, fn($r) => $r['audit_ready']));
    $avg_rate    = $total_dept > 0 ? round(array_sum(array_column($rows, 'safety_rate')) / $total_dept) : 0;
    ?>

    <div class="col-md-4 mb-3">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Departemen</div>
                <div class="h4 font-weight-bold text-gray-800"><?= $total_dept ?></div>
            </div>
        </div>
    </div>

    <div class="col-md-4 mb-3">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Dept. Audit Ready</div>
                <div class="h4 font-weight-bold text-gray-800"><?= $ready_count ?> / <?= $total_dept ?></div>
            </div>
        </div>
    </div>

    <div class="col-md-4 mb-3">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Rata-rata Safety</div>
                <div class="h4 font-weight-bold text-gray-800"><?= $avg_rate ?>%</div>
            </div>
        </div>
    </div>

</div>


<!-- Chart Bar -->
<div class="card shadow mb-4">

    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">
            Safety Rate per Departemen — <?= htmlspecialchars($filter_month) ?>
        </h6>
    </div>

    <div class="card-body">

        <canvas id="safetyChart" height="100"></canvas>

    </div>

</div>


<!-- Tabel Rekap Detail -->
<div class="card shadow mb-4">

    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Detail Rekap per Departemen</h6>
    </div>

    <div class="card-body">

        <div class="table-responsive">

            <table class="table table-bordered">

                <thead>

                    <tr class="bg-light">
                        <th>Departemen</th>
                        <th class="text-center">Total Checks</th>
                        <th class="text-center">APD</th>
                        <th class="text-center">Briefing</th>
                        <th class="text-center">JSA</th>
                        <th class="text-center">Equipment</th>
                        <th class="text-center">Safety Rate</th>
                        <th class="text-center">Audit Status</th>
                    </tr>

                </thead>

                <tbody>

                <?php foreach ($rows as $r): ?>

                <tr>

                    <td class="font-weight-bold"><?= htmlspecialchars($r['department_name']) ?></td>

                    <td class="text-center"><?= $r['total_checks'] ?></td>

                    <td class="text-center">
                        <div class="small font-weight-bold <?= $r['apd_pct'] >= 80 ? 'text-success' : 'text-danger' ?>">
                            <?= $r['apd_pct'] ?>%
                        </div>
                        <div class="progress" style="height:6px">
                            <div class="progress-bar <?= $r['apd_pct'] >= 80 ? 'bg-success' : 'bg-danger' ?>"
                                style="width:<?= $r['apd_pct'] ?>%"></div>
                        </div>
                    </td>

                    <td class="text-center">
                        <div class="small font-weight-bold <?= $r['briefing_pct'] >= 80 ? 'text-success' : 'text-danger' ?>">
                            <?= $r['briefing_pct'] ?>%
                        </div>
                        <div class="progress" style="height:6px">
                            <div class="progress-bar <?= $r['briefing_pct'] >= 80 ? 'bg-success' : 'bg-danger' ?>"
                                style="width:<?= $r['briefing_pct'] ?>%"></div>
                        </div>
                    </td>

                    <td class="text-center">
                        <div class="small font-weight-bold <?= $r['jsa_pct'] >= 80 ? 'text-success' : 'text-danger' ?>">
                            <?= $r['jsa_pct'] ?>%
                        </div>
                        <div class="progress" style="height:6px">
                            <div class="progress-bar <?= $r['jsa_pct'] >= 80 ? 'bg-success' : 'bg-danger' ?>"
                                style="width:<?= $r['jsa_pct'] ?>%"></div>
                        </div>
                    </td>

                    <td class="text-center">
                        <div class="small font-weight-bold <?= $r['equipment_pct'] >= 80 ? 'text-success' : 'text-danger' ?>">
                            <?= $r['equipment_pct'] ?>%
                        </div>
                        <div class="progress" style="height:6px">
                            <div class="progress-bar <?= $r['equipment_pct'] >= 80 ? 'bg-success' : 'bg-danger' ?>"
                                style="width:<?= $r['equipment_pct'] ?>%"></div>
                        </div>
                    </td>

                    <td class="text-center">
                        <div class="h5 font-weight-bold <?= $r['safety_rate'] >= 80 ? 'text-success' : ($r['safety_rate'] >= 60 ? 'text-warning' : 'text-danger') ?>">
                            <?= $r['safety_rate'] ?>%
                        </div>
                    </td>

                    <td class="text-center">

                        <?php if ($r['total_checks'] == 0): ?>

                        <span class="badge badge-secondary">Belum Ada Data</span>

                        <?php elseif ($r['audit_ready']): ?>

                        <span class="badge badge-success">
                            <i class="fas fa-check-circle"></i> AUDIT READY
                        </span>

                        <?php elseif ($r['safety_rate'] >= 60): ?>

                        <span class="badge badge-warning">
                            <i class="fas fa-exclamation-triangle"></i> PERLU PERBAIKAN
                        </span>

                        <?php else: ?>

                        <span class="badge badge-danger">
                            <i class="fas fa-times-circle"></i> TIDAK MEMENUHI
                        </span>

                        <?php endif; ?>

                    </td>

                </tr>


                <tr class="<?= $r['audit_ready'] ? 'table-success' : ($r['safety_rate'] >= 60 ? 'table-warning' : 'table-danger') ?>">

                    <td colspan="8" class="small py-2 pl-4">

                        <i class="fas fa-info-circle mr-1"></i>

                        <?php if ($r['total_checks'] == 0): ?>

                        Belum ada data pengecekan untuk departemen ini pada periode <?= htmlspecialchars($filter_month) ?>.

                        <?php elseif ($r['audit_ready']): ?>

                        <strong>Kesimpulan:</strong>
                        Dept. <?= htmlspecialchars($r['department_name']) ?> dengan safety rate <strong><?= $r['safety_rate'] ?>%</strong>
                        telah memenuhi standar minimum 80% sesuai PP No. 50 Tahun 2012.
                        Departemen ini <strong>siap untuk proses audit</strong>.

                        <?php elseif ($r['safety_rate'] >= 60): ?>

                        <strong>Kesimpulan:</strong>
                        Dept. <?= htmlspecialchars($r['department_name']) ?> dengan safety rate <strong><?= $r['safety_rate'] ?>%</strong>
                        masih di bawah standar minimum 80%.
                        <strong>Perlu perbaikan dalam 30 hari</strong> sebelum dapat diajukan ke audit.

                        <?php else: ?>

                        <strong>Kesimpulan:</strong>
                        Dept. <?= htmlspecialchars($r['department_name']) ?> dengan safety rate <strong><?= $r['safety_rate'] ?>%</strong>
                        tidak memenuhi syarat audit.
                        <strong>Wajib remedial dan pembinaan K3 ulang</strong> sebelum dilakukan pengecekan kembali.

                        <?php endif; ?>

                    </td>

                </tr>

                <?php endforeach; ?>

                </tbody>

            </table>

        </div>

    </div>

</div>


<!-- Standar -->
<div class="card shadow mb-4">

    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Standar Kelulusan</h6>
    </div>

    <div class="card-body">

        <table class="table table-sm table-bordered">
            <thead>
                <tr>
                    <th>Safety Rate</th>
                    <th>Status</th>
                    <th>Keterangan</th>
                    <th>Referensi</th>
                </tr>
            </thead>
            <tbody>
                <tr class="table-success">
                    <td>≥ 80%</td>
                    <td><span class="badge badge-success">AUDIT READY</span></td>
                    <td>Siap untuk proses audit K3</td>
                    <td>PP No. 50 Tahun 2012</td>
                </tr>
                <tr class="table-warning">
                    <td>60% – 79%</td>
                    <td><span class="badge badge-warning">PERLU PERBAIKAN</span></td>
                    <td>Perbaikan wajib dalam 30 hari</td>
                    <td>Permenaker No. 05/MEN/1996</td>
                </tr>
                <tr class="table-danger">
                    <td>&lt; 60%</td>
                    <td><span class="badge badge-danger">TIDAK MEMENUHI</span></td>
                    <td>Wajib remedial & pembinaan ulang</td>
                    <td>OHSAS 18001:2007</td>
                </tr>
            </tbody>
        </table>

    </div>

</div>


<!-- Chart.js Script -->
<script>
var ctx = document.getElementById('safetyChart').getContext('2d');

var chart = new Chart(ctx, {

    type: 'bar',

    data: {

        labels: <?= json_encode($chart_labels) ?>,

        datasets: [{
            label: 'Safety Rate (%)',
            data: <?= json_encode($chart_data) ?>,
            backgroundColor: <?= json_encode($chart_colors) ?>,
            borderColor: <?= json_encode($chart_colors) ?>,
            borderWidth: 1
        }]

    },

    options: {

        responsive: true,

        scales: {

            yAxes: [{
                ticks: {
                    beginAtZero: true,
                    max: 100,
                    callback: function(val) { return val + '%'; }
                },
                gridLines: {
                    color: 'rgba(0,0,0,0.05)'
                }
            }]

        },

        plugins: {

            annotation: {
                annotations: [{
                    type: 'line',
                    mode: 'horizontal',
                    scaleID: 'y-axis-0',
                    value: 80,
                    borderColor: '#e74a3b',
                    borderWidth: 2,
                    borderDash: [5,5],
                    label: {
                        enabled: true,
                        content: 'Standar Minimum 80%',
                        position: 'right'
                    }
                }]
            }

        },

        tooltips: {
            callbacks: {
                label: function(item) {
                    return ' ' + item.yLabel + '%';
                }
            }
        },

        legend: {
            display: false
        }

    }

});
</script>


<?php require 'include/footer.php'; ?>
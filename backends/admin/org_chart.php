<?php

require '../koneksi/auth_admin.php';
require '../koneksi/koneksi.php';

$page_title = "Org Chart";
$current_page = "org_chart";


/* ambil semua karyawan beserta departemen */
$data = pg_query($conn, "

    SELECT
        employees.id,
        employees.fullname,
        employees.position,
        employees.status,
        departments.department_name

    FROM employees

    JOIN departments
    ON employees.department_id = departments.id

    ORDER BY
        departments.department_name ASC,
        CASE employees.org_level
            WHEN 'Manager'    THEN 0
            WHEN 'Supervisor' THEN 1
            WHEN 'Staff'      THEN 2
            ELSE 3
        END ASC,
        employees.fullname ASC

");

/* kelompokkan per departemen */
$tree = [];
while ($row = pg_fetch_assoc($data)) {
    $dept = $row['department_name'];
    $tree[$dept][] = $row;
}

require 'include/header.php';

?>


<h1 class="h3 mb-4 text-gray-800">
    Organizational Structure
</h1>


<!-- Top Level -->
<div class="row justify-content-center mb-4">

    <div class="col-md-4">

        <div class="card border-left-primary shadow text-center py-3">

            <div class="card-body">

                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                    Direktur Utama HSE
                </div>

                <div class="h6 font-weight-bold text-gray-800 mb-0">
                    Bambang Wira Pratama
                </div>

            </div>

        </div>

    </div>

</div>


<!-- Arrow down -->
<div class="text-center mb-2">
    <i class="fas fa-arrow-down fa-2x text-gray-400"></i>
</div>


<!-- Manager Level -->
<div class="row justify-content-center mb-4">

    <div class="col-md-4">

        <div class="card border-left-success shadow text-center py-3">

            <div class="card-body">

                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                    Manager K3 Umum
                </div>

                <div class="h6 font-weight-bold text-gray-800 mb-0">
                    Suhendra Aji Nugroho
                </div>

            </div>

        </div>

    </div>

</div>


<!-- Arrow down -->
<div class="text-center mb-4">
    <i class="fas fa-arrow-down fa-2x text-gray-400"></i>
</div>


<!-- Dept Columns -->
<div class="row">

<?php foreach ($tree as $dept_name => $members): ?>

    <div class="col-md-3 mb-4">

        <!-- Dept Header -->
        <div class="card shadow mb-3">

            <div class="card-header py-2 bg-primary">

                <h6 class="m-0 font-weight-bold text-white text-center">
                    <?= htmlspecialchars($dept_name) ?>
                </h6>

            </div>

        </div>

        <!-- Members -->
        <?php foreach ($members as $emp): ?>

        <div class="card shadow-sm mb-2">

            <div class="card-body py-2 px-3">

                <div class="font-weight-bold text-gray-800" style="font-size:13px">
                    <?= htmlspecialchars($emp['fullname']) ?>
                </div>

                <div class="text-xs text-muted">
                    <?= htmlspecialchars($emp['position']) ?>
                </div>

                <span class="badge badge-<?= $emp['status'] == 'active' ? 'success' : 'secondary' ?> mt-1">
                    <?= ucfirst($emp['status']) ?>
                </span>

            </div>

        </div>

        <?php endforeach; ?>

    </div>

<?php endforeach; ?>

</div>


<?php require 'include/footer.php'; ?>

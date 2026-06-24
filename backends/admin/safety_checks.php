<?php

require '../koneksi/auth_admin.php';
require '../koneksi/koneksi.php';

$page_title = "Safety Checks";
$current_page = "safety_checks";


/* helper checkbox */
function bool_val($name) {
    return isset($_POST[$name]) ? 'TRUE' : 'FALSE';
}


/* =========================
   INSERT
========================= */
if (isset($_POST['add'])) {

    $employee_id   = $_POST['employee_id'];
    $activity_id   = $_POST['activity_id'];
    $checked_by    = $_SESSION['user_id'];
    $check_date    = $_POST['check_date'];

    $apd           = bool_val('apd_compliant');
    $briefing      = bool_val('briefing_done');
    $jsa           = bool_val('jsa_understood');
    $equipment     = bool_val('equipment_checked');
    $is_compliant  = bool_val('is_compliant');

    $notes         = $_POST['notes'];

    $insert = pg_query($conn, "

        INSERT INTO safety_checks
        (
            id,
            employee_id,
            activity_id,
            checked_by,
            check_date,
            apd_compliant,
            briefing_done,
            jsa_understood,
            equipment_checked,
            is_compliant,
            notes
        )
        VALUES
        (
            gen_random_uuid(),
            '$employee_id',
            '$activity_id',
            '$checked_by',
            '$check_date',
            $apd,
            $briefing,
            $jsa,
            $equipment,
            $is_compliant,
            '$notes'
        )
        RETURNING id

    ");

    $new_check = pg_fetch_assoc($insert);
    $safety_check_id = $new_check['id'];


    /* ===== AUTO-CREATE CAPA kalau hasil pengecekan FAIL ===== */
    if ($is_compliant === 'FALSE') {

        $failed_items = [];
        if ($apd == 'FALSE')       $failed_items[] = 'APD tidak lengkap';
        if ($briefing == 'FALSE')  $failed_items[] = 'Safety briefing belum dilakukan';
        if ($jsa == 'FALSE')       $failed_items[] = 'JSA belum dipahami';
        if ($equipment == 'FALSE') $failed_items[] = 'Peralatan belum dicek';

        $capa_desc = pg_escape_string($conn,
            'Ketidaksesuaian ditemukan: ' . implode(', ', $failed_items) . '. ' . $notes
        );

        /* cari PIC default: Manager HSE/K3 (fallback NULL kalau tidak ditemukan) */
        $pic_q = pg_query($conn, "
            SELECT id FROM employees
            WHERE position ILIKE '%HSE%' OR position ILIKE '%K3%'
            ORDER BY position ASC
            LIMIT 1
        ");
        $pic = pg_fetch_assoc($pic_q);
        $pic_id = $pic ? "'" . $pic['id'] . "'" : "NULL";

        pg_query($conn, "
            INSERT INTO capa_items
            (id, source_type, safety_check_id, title, description, pic_employee_id, due_date, priority, status)
            VALUES
            (
                gen_random_uuid(),
                'safety_check',
                '$safety_check_id',
                'Tindak Lanjut Ketidaksesuaian SOP',
                '$capa_desc',
                $pic_id,
                (CURRENT_DATE + INTERVAL '7 days')::date,
                'medium',
                'open'
            )
        ");
    }

    header("Location: safety_checks.php");
    exit;
}


/* =========================
   DELETE
========================= */
if (isset($_GET['delete'])) {

    $id = $_GET['delete'];

    pg_query($conn, "DELETE FROM safety_checks WHERE id='$id'");

    header("Location: safety_checks.php");
    exit;
}


/* ambil data checks */
$data = pg_query($conn, "

    SELECT
        safety_checks.*,
        employees.fullname AS employee_name,
        sop_activities.activity_name,
        departments.department_name

    FROM safety_checks

    JOIN employees
    ON safety_checks.employee_id = employees.id

    JOIN sop_activities
    ON safety_checks.activity_id = sop_activities.id

    JOIN departments
    ON employees.department_id = departments.id

    ORDER BY safety_checks.check_date DESC

");


/* dropdown employees */
$employees = pg_query($conn, "
    SELECT employees.*, departments.department_name
    FROM employees
    JOIN departments ON employees.department_id = departments.id
    ORDER BY employees.fullname ASC
");


/* dropdown activities */
$activities = pg_query($conn, "
    SELECT sop_activities.*, departments.department_name
    FROM sop_activities
    JOIN departments ON sop_activities.department_id = departments.id
    ORDER BY departments.department_name ASC, sop_activities.activity_name ASC
");

require 'include/header.php';

?>


<h1 class="h3 mb-4 text-gray-800">
    Safety Checks
</h1>


<button
    class="btn btn-primary mb-3"
    data-toggle="modal"
    data-target="#addModal">

    <i class="fas fa-plus fa-sm"></i> Add Check

</button>


<div class="card shadow mb-4">

    <div class="card-body">

        <div class="table-responsive">

            <table class="table table-bordered">

                <thead>

                    <tr>
                        <th>No</th>
                        <th>Tanggal</th>
                        <th>Karyawan</th>
                        <th>Dept</th>
                        <th>Activity</th>
                        <th>APD</th>
                        <th>Briefing</th>
                        <th>JSA</th>
                        <th>Equipment</th>
                        <th>Status</th>
                        <th width="80">Aksi</th>
                    </tr>

                </thead>

                <tbody>

                <?php
                $no = 1;
                while ($row = pg_fetch_assoc($data)):
                ?>

                <tr>

                    <td><?= $no++ ?></td>

                    <td><?= $row['check_date'] ?></td>

                    <td><?= htmlspecialchars($row['employee_name']) ?></td>

                    <td><?= htmlspecialchars($row['department_name']) ?></td>

                    <td><?= htmlspecialchars($row['activity_name']) ?></td>

                    <td class="text-center">
                        <?= $row['apd_compliant'] == 't'
                            ? '<span class="badge badge-success">✓</span>'
                            : '<span class="badge badge-danger">✗</span>' ?>
                    </td>

                    <td class="text-center">
                        <?= $row['briefing_done'] == 't'
                            ? '<span class="badge badge-success">✓</span>'
                            : '<span class="badge badge-danger">✗</span>' ?>
                    </td>

                    <td class="text-center">
                        <?= $row['jsa_understood'] == 't'
                            ? '<span class="badge badge-success">✓</span>'
                            : '<span class="badge badge-danger">✗</span>' ?>
                    </td>

                    <td class="text-center">
                        <?= $row['equipment_checked'] == 't'
                            ? '<span class="badge badge-success">✓</span>'
                            : '<span class="badge badge-danger">✗</span>' ?>
                    </td>

                    <td>
                        <?php if ($row['is_compliant'] == 't'): ?>
                        <span class="badge badge-success">PASS</span>
                        <?php else: ?>
                        <span class="badge badge-danger">FAIL</span>
                        <?php endif; ?>
                    </td>

                    <td>
                        <a
                            href="safety_checks.php?delete=<?= $row['id'] ?>"
                            onclick="return confirm('Hapus data ini?')"
                            class="btn btn-danger btn-sm">
                            <i class="fas fa-trash fa-xs"></i>
                        </a>
                    </td>

                </tr>

                <?php endwhile; ?>

                </tbody>

            </table>

        </div>

    </div>

</div>


<!-- ADD MODAL -->
<div class="modal fade" id="addModal">

<div class="modal-dialog">

<div class="modal-content">

<form method="POST">

<div class="modal-header">
<h5>Add Safety Check</h5>
</div>

<div class="modal-body">

<!-- Tanggal -->
<label class="small font-weight-bold">Tanggal Pengecekan</label>
<input
    type="date"
    name="check_date"
    class="form-control mb-3"
    value="<?= date('Y-m-d') ?>"
    required>

<!-- Karyawan -->
<label class="small font-weight-bold">Karyawan</label>
<select name="employee_id" class="form-control mb-3" required>
<option value="">Pilih Karyawan</option>
<?php while ($emp = pg_fetch_assoc($employees)): ?>
<option value="<?= $emp['id'] ?>">
    <?= htmlspecialchars($emp['fullname']) ?>
    (<?= htmlspecialchars($emp['department_name']) ?>)
</option>
<?php endwhile; ?>
</select>

<!-- Activity SOP -->
<label class="small font-weight-bold">SOP Activity</label>
<select name="activity_id" class="form-control mb-3" required>
<option value="">Pilih Activity</option>
<?php while ($act = pg_fetch_assoc($activities)): ?>
<option value="<?= $act['id'] ?>">
    <?= htmlspecialchars($act['activity_name']) ?>
    — <?= htmlspecialchars($act['department_name']) ?>
</option>
<?php endwhile; ?>
</select>

<hr>

<label class="small font-weight-bold d-block mb-2">Hasil Pengecekan</label>

<div class="form-check mb-2">
    <input type="checkbox" name="apd_compliant" class="form-check-input" id="apd">
    <label class="form-check-label" for="apd">
        APD lengkap dan sesuai standar
    </label>
</div>

<div class="form-check mb-2">
    <input type="checkbox" name="briefing_done" class="form-check-input" id="briefing">
    <label class="form-check-label" for="briefing">
        Safety briefing telah dilakukan
    </label>
</div>

<div class="form-check mb-2">
    <input type="checkbox" name="jsa_understood" class="form-check-input" id="jsa">
    <label class="form-check-label" for="jsa">
        JSA dipahami karyawan
    </label>
</div>

<div class="form-check mb-3">
    <input type="checkbox" name="equipment_checked" class="form-check-input" id="equipment">
    <label class="form-check-label" for="equipment">
        Peralatan sudah dicek
    </label>
</div>

<hr>

<div class="form-check mb-3">
    <input type="checkbox" name="is_compliant" class="form-check-input" id="compliant">
    <label class="form-check-label font-weight-bold" for="compliant">
        ✓ Dinyatakan COMPLY (PASS)
    </label>
</div>

<label class="small font-weight-bold">Catatan (opsional)</label>
<textarea name="notes" class="form-control" rows="2" placeholder="Catatan pengecekan..."></textarea>

</div>

<div class="modal-footer">
<button name="add" class="btn btn-primary">Simpan</button>
</div>

</form>

</div>

</div>

</div>


<?php require 'include/footer.php'; ?>
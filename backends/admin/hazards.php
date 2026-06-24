<?php

require '../koneksi/auth_admin.php';
require '../koneksi/koneksi.php';

$page_title = "Hazard Identification (HIRARC)";
$current_page = "hazards";


function risk_level($score)
{
    if ($score <= 3)  return ['label' => 'LOW', 'class' => 'success'];
    if ($score <= 8)  return ['label' => 'MEDIUM', 'class' => 'warning'];
    if ($score <= 14) return ['label' => 'HIGH', 'class' => 'orange'];
    return ['label' => 'EXTREME', 'class' => 'danger'];
}


/* ===== INSERT ===== */
if (isset($_POST['add'])) {

    $department_id = $_POST['department_id'];
    $identified_by = $_POST['identified_by'];
    $hazard_description = $_POST['hazard_description'];
    $existing_control = $_POST['existing_control'];
    $likelihood = (int) $_POST['likelihood'];
    $severity = (int) $_POST['severity'];
    $recommended_control = $_POST['recommended_control'];

    pg_query($conn, "
        INSERT INTO hazards
        (id, department_id, identified_by, hazard_description, existing_control, likelihood, severity, recommended_control, status)
        VALUES
        (gen_random_uuid(), '$department_id', '$identified_by', '$hazard_description', '$existing_control', $likelihood, $severity, '$recommended_control', 'open')
    ");

    header("Location: hazards.php");
    exit;
}


/* ===== UPDATE ===== */
if (isset($_POST['edit'])) {

    $id = $_POST['id'];
    $existing_control = $_POST['existing_control'];
    $likelihood = (int) $_POST['likelihood'];
    $severity = (int) $_POST['severity'];
    $recommended_control = $_POST['recommended_control'];
    $status = $_POST['status'];

    pg_query($conn, "
        UPDATE hazards
        SET existing_control='$existing_control',
            likelihood=$likelihood,
            severity=$severity,
            recommended_control='$recommended_control',
            status='$status'
        WHERE id='$id'
    ");

    header("Location: hazards.php");
    exit;
}


/* ===== DELETE ===== */
if (isset($_GET['delete'])) {

    $id = $_GET['delete'];
    pg_query($conn, "DELETE FROM hazards WHERE id='$id'");

    header("Location: hazards.php");
    exit;
}


/* ===== DATA UNTUK RISK MATRIX (hanya yang belum closed) ===== */
$matrix_q = pg_query($conn, "
    SELECT likelihood, severity, COUNT(*) AS cnt
    FROM hazards
    WHERE status != 'closed'
    GROUP BY likelihood, severity
");

$matrix = [];
while ($row = pg_fetch_assoc($matrix_q)) {
    $matrix[$row['likelihood']][$row['severity']] = (int) $row['cnt'];
}


/* ===== DATA LIST HAZARD ===== */
$data = pg_query($conn, "
    SELECT hazards.*, departments.department_name, employees.fullname
    FROM hazards
    JOIN departments ON hazards.department_id = departments.id
    LEFT JOIN employees ON hazards.identified_by = employees.id
    ORDER BY (hazards.likelihood * hazards.severity) DESC, hazards.identified_date DESC
");

$departments = pg_query($conn, "SELECT * FROM departments ORDER BY department_name ASC");
$employees_all = pg_query($conn, "SELECT * FROM employees ORDER BY fullname ASC");

require 'include/header.php';

?>

<style>
.risk-orange { background-color: #fd7e14 !important; color: #fff; }
.matrix-cell {
    width: 60px; height: 50px; text-align: center; vertical-align: middle;
    font-weight: bold; color: #fff; border: 1px solid #fff;
}
.matrix-low      { background-color: #1cc88a; }
.matrix-medium   { background-color: #f6c23e; color: #333; }
.matrix-high     { background-color: #fd7e14; }
.matrix-extreme  { background-color: #e74a3b; }
.matrix-axis     { background-color: #4e73df; color: #fff; text-align: center; vertical-align: middle; font-size: .75rem; }
</style>

<h1 class="h3 mb-4 text-gray-800">Hazard Identification (HIRARC)</h1>

<p class="text-muted">
    Identifikasi bahaya per departemen dengan penilaian risiko (Likelihood × Severity),
    mengikuti prinsip HIRARC (Hazard Identification, Risk Assessment and Risk Control).
</p>

<button class="btn btn-primary mb-3" data-toggle="modal" data-target="#addModal">
    <i class="fas fa-plus fa-sm"></i> Add Hazard
</button>


<!-- ===== RISK MATRIX VISUAL ===== -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Risk Matrix (Hazard Aktif — belum closed)</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
        <table class="table table-bordered mb-0" style="width:auto;">
            <tbody>
            <?php for ($l = 5; $l >= 1; $l--): ?>
            <tr>
                <td class="matrix-axis" style="width:90px;">
                    Likelihood <?= $l ?>
                </td>
                <?php for ($s = 1; $s <= 5; $s++):
                    $score = $l * $s;
                    $rl = risk_level($score);
                    $cls = strtolower($rl['label']) === 'low' ? 'matrix-low'
                         : (strtolower($rl['label']) === 'medium' ? 'matrix-medium'
                         : (strtolower($rl['label']) === 'high' ? 'matrix-high' : 'matrix-extreme'));
                    $count = $matrix[$l][$s] ?? 0;
                ?>
                <td class="matrix-cell <?= $cls ?>">
                    <?= $count > 0 ? $count : '' ?>
                </td>
                <?php endfor; ?>
            </tr>
            <?php endfor; ?>
            <tr>
                <td class="matrix-axis"></td>
                <?php for ($s = 1; $s <= 5; $s++): ?>
                <td class="matrix-axis">Sev <?= $s ?></td>
                <?php endfor; ?>
            </tr>
            </tbody>
        </table>
        </div>
        <small class="text-muted">Angka di tiap sel = jumlah hazard aktif pada kombinasi Likelihood × Severity tersebut.</small>
    </div>
</div>


<!-- ===== TABLE HAZARD LIST ===== -->
<div class="card shadow mb-4">
<div class="card-body">
<div class="table-responsive">
<table class="table table-bordered">
<thead>
<tr>
    <th>Departemen</th>
    <th>Hazard</th>
    <th>Existing Control</th>
    <th>L</th>
    <th>S</th>
    <th>Risk Score</th>
    <th>Level</th>
    <th>Status</th>
    <th width="140">Aksi</th>
</tr>
</thead>
<tbody>
<?php while ($row = pg_fetch_assoc($data)):
    $score = $row['likelihood'] * $row['severity'];
    $rl = risk_level($score);
    $status_class = ['open' => 'danger', 'mitigated' => 'warning', 'closed' => 'success'][$row['status']] ?? 'secondary';
?>
<tr>
    <td><?= htmlspecialchars($row['department_name']) ?></td>
    <td>
        <?= htmlspecialchars($row['hazard_description']) ?>
        <?php if ($row['fullname']): ?>
        <br><small class="text-muted">Diidentifikasi oleh: <?= htmlspecialchars($row['fullname']) ?></small>
        <?php endif; ?>
    </td>
    <td><small><?= htmlspecialchars($row['existing_control']) ?></small></td>
    <td class="text-center"><?= $row['likelihood'] ?></td>
    <td class="text-center"><?= $row['severity'] ?></td>
    <td class="text-center"><?= $score ?></td>
    <td><span class="badge badge-<?= $rl['class'] === 'orange' ? '' : $rl['class'] ?> <?= $rl['class'] === 'orange' ? 'risk-orange' : '' ?>"><?= $rl['label'] ?></span></td>
    <td><span class="badge badge-<?= $status_class ?>"><?= strtoupper($row['status']) ?></span></td>
    <td>
        <button class="btn btn-warning btn-sm" data-toggle="modal" data-target="#edit<?= $row['id'] ?>">Edit</button>
        <a href="hazards.php?delete=<?= $row['id'] ?>" onclick="return confirm('Hapus hazard ini?')" class="btn btn-danger btn-sm">Delete</a>
    </td>
</tr>

<!-- EDIT MODAL -->
<div class="modal fade" id="edit<?= $row['id'] ?>">
<div class="modal-dialog">
<div class="modal-content">
<form method="POST">
<div class="modal-header"><h5>Edit Hazard</h5></div>
<div class="modal-body">

<input type="hidden" name="id" value="<?= $row['id'] ?>">

<p><strong><?= htmlspecialchars($row['hazard_description']) ?></strong></p>

<label class="small font-weight-bold">Existing Control</label>
<textarea name="existing_control" class="form-control mb-2" rows="2"><?= htmlspecialchars($row['existing_control']) ?></textarea>

<div class="form-row">
<div class="col">
<label class="small font-weight-bold">Likelihood (1-5)</label>
<select name="likelihood" class="form-control mb-2">
<?php for ($i = 1; $i <= 5; $i++): ?>
<option value="<?= $i ?>" <?= $i == $row['likelihood'] ? 'selected' : '' ?>><?= $i ?></option>
<?php endfor; ?>
</select>
</div>
<div class="col">
<label class="small font-weight-bold">Severity (1-5)</label>
<select name="severity" class="form-control mb-2">
<?php for ($i = 1; $i <= 5; $i++): ?>
<option value="<?= $i ?>" <?= $i == $row['severity'] ? 'selected' : '' ?>><?= $i ?></option>
<?php endfor; ?>
</select>
</div>
</div>

<label class="small font-weight-bold">Recommended Control</label>
<textarea name="recommended_control" class="form-control mb-2" rows="2"><?= htmlspecialchars($row['recommended_control']) ?></textarea>

<label class="small font-weight-bold">Status</label>
<select name="status" class="form-control">
<?php foreach (['open' => 'Open', 'mitigated' => 'Mitigated', 'closed' => 'Closed'] as $val => $label): ?>
<option value="<?= $val ?>" <?= $val == $row['status'] ? 'selected' : '' ?>><?= $label ?></option>
<?php endforeach; ?>
</select>

</div>
<div class="modal-footer">
<button name="edit" class="btn btn-warning">Update</button>
</div>
</form>
</div>
</div>
</div>

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
<div class="modal-header"><h5>Add Hazard</h5></div>
<div class="modal-body">

<label class="small font-weight-bold">Departemen</label>
<select name="department_id" class="form-control mb-2" required>
<option value="">Pilih Departemen</option>
<?php while ($d = pg_fetch_assoc($departments)): ?>
<option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['department_name']) ?></option>
<?php endwhile; ?>
</select>

<label class="small font-weight-bold">Diidentifikasi oleh</label>
<select name="identified_by" class="form-control mb-2" required>
<option value="">Pilih Karyawan</option>
<?php while ($e = pg_fetch_assoc($employees_all)): ?>
<option value="<?= $e['id'] ?>"><?= htmlspecialchars($e['fullname']) ?></option>
<?php endwhile; ?>
</select>

<label class="small font-weight-bold">Deskripsi Bahaya</label>
<textarea name="hazard_description" class="form-control mb-2" rows="2" placeholder="Contoh: Risiko terpeleset di area basah" required></textarea>

<label class="small font-weight-bold">Existing Control</label>
<textarea name="existing_control" class="form-control mb-2" rows="2" placeholder="Kontrol yang sudah ada saat ini"></textarea>

<div class="form-row">
<div class="col">
<label class="small font-weight-bold">Likelihood (1-5)</label>
<select name="likelihood" class="form-control mb-2" required>
<option value="">-</option>
<?php for ($i = 1; $i <= 5; $i++): ?>
<option value="<?= $i ?>"><?= $i ?></option>
<?php endfor; ?>
</select>
</div>
<div class="col">
<label class="small font-weight-bold">Severity (1-5)</label>
<select name="severity" class="form-control mb-2" required>
<option value="">-</option>
<?php for ($i = 1; $i <= 5; $i++): ?>
<option value="<?= $i ?>"><?= $i ?></option>
<?php endfor; ?>
</select>
</div>
</div>

<label class="small font-weight-bold">Recommended Control</label>
<textarea name="recommended_control" class="form-control" rows="2" placeholder="Rekomendasi kontrol tambahan"></textarea>

</div>
<div class="modal-footer">
<button name="add" class="btn btn-primary">Save</button>
</div>
</form>
</div>
</div>
</div>

<?php require 'include/footer.php'; ?>
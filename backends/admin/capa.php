<?php

require '../koneksi/auth_admin.php';
require '../koneksi/koneksi.php';

$page_title = "CAPA Tracking";
$current_page = "capa";


/* =========================
   INSERT MANUAL
========================= */
if (isset($_POST['add'])) {

    $title = $_POST['title'];
    $description = $_POST['description'];
    $pic_employee_id = $_POST['pic_employee_id'];
    $due_date = $_POST['due_date'];
    $priority = $_POST['priority'];

    pg_query($conn, "
        INSERT INTO capa_items
        (id, source_type, title, description, pic_employee_id, due_date, priority, status)
        VALUES
        (gen_random_uuid(), 'manual', '$title', '$description', '$pic_employee_id', '$due_date', '$priority', 'open')
    ");

    header("Location: capa.php");
    exit;
}


/* =========================
   UPDATE STATUS / DETAIL
========================= */
if (isset($_POST['edit'])) {

    $id = $_POST['id'];
    $pic_employee_id = $_POST['pic_employee_id'];
    $due_date = $_POST['due_date'];
    $priority = $_POST['priority'];
    $status = $_POST['status'];
    $closing_notes = $_POST['closing_notes'];

    $closed_at_sql = $status === 'closed' ? 'now()' : 'NULL';

    pg_query($conn, "
        UPDATE capa_items
        SET pic_employee_id='$pic_employee_id',
            due_date='$due_date',
            priority='$priority',
            status='$status',
            closing_notes='$closing_notes',
            closed_at = $closed_at_sql
        WHERE id='$id'
    ");

    header("Location: capa.php");
    exit;
}


/* =========================
   DELETE
========================= */
if (isset($_GET['delete'])) {

    $id = $_GET['delete'];
    pg_query($conn, "DELETE FROM capa_items WHERE id='$id'");

    header("Location: capa.php");
    exit;
}


/* ambil data CAPA + info sumber */
$data = pg_query($conn, "

    SELECT
        capa_items.*,
        pic.fullname AS pic_name,

        sc.check_date AS sc_date,
        sa.activity_name AS sc_activity,
        emp_sc.fullname AS sc_employee_name,

        r.title AS report_title,
        r.report_type AS report_type

    FROM capa_items

    LEFT JOIN employees pic ON capa_items.pic_employee_id = pic.id
    LEFT JOIN safety_checks sc ON capa_items.safety_check_id = sc.id
    LEFT JOIN sop_activities sa ON sc.activity_id = sa.id
    LEFT JOIN employees emp_sc ON sc.employee_id = emp_sc.id
    LEFT JOIN reports r ON capa_items.report_id = r.id

    ORDER BY
        CASE capa_items.status WHEN 'open' THEN 0 WHEN 'in_progress' THEN 1 ELSE 2 END,
        capa_items.due_date ASC

");

$employees = pg_query($conn, "SELECT * FROM employees ORDER BY fullname ASC");

require 'include/header.php';

?>

<h1 class="h3 mb-4 text-gray-800">CAPA Tracking (Corrective & Preventive Action)</h1>

<p class="text-muted">
    Daftar tindak lanjut dari Safety Check yang FAIL dan Incident/Hazard Report, plus CAPA manual.
</p>

<button class="btn btn-primary mb-3" data-toggle="modal" data-target="#addModal">
    <i class="fas fa-plus fa-sm"></i> Add CAPA Manual
</button>

<div class="card shadow mb-4">
<div class="card-body">
<div class="table-responsive">
<table class="table table-bordered">
<thead>
<tr>
    <th>Sumber</th>
    <th>Judul / Deskripsi</th>
    <th>PIC</th>
    <th>Due Date</th>
    <th>Priority</th>
    <th>Status</th>
    <th width="140">Aksi</th>
</tr>
</thead>
<tbody>
<?php while ($row = pg_fetch_assoc($data)): ?>

<?php
$is_overdue = $row['status'] !== 'closed' && $row['due_date'] !== null && strtotime($row['due_date']) < strtotime(date('Y-m-d'));

$status_class = [
    'open' => 'danger',
    'in_progress' => 'warning',
    'closed' => 'success',
][$row['status']] ?? 'secondary';

$priority_class = [
    'critical' => 'danger',
    'high' => 'warning',
    'medium' => 'info',
    'low' => 'secondary',
][$row['priority']] ?? 'secondary';
?>

<tr>
    <td>
        <?php if ($row['source_type'] === 'safety_check'): ?>
            <span class="badge badge-primary">Safety Check</span><br>
            <small class="text-muted">
                <?= htmlspecialchars($row['sc_activity'] ?? '-') ?> —
                <?= htmlspecialchars($row['sc_employee_name'] ?? '-') ?>
                (<?= $row['sc_date'] ?>)
            </small>
        <?php elseif ($row['source_type'] === 'report'): ?>
            <span class="badge badge-dark">Incident Report</span><br>
            <small class="text-muted">
                <?= htmlspecialchars($row['report_type'] ?? '-') ?> —
                <?= htmlspecialchars($row['report_title'] ?? '-') ?>
            </small>
        <?php else: ?>
            <span class="badge badge-secondary">Manual</span>
        <?php endif; ?>
    </td>

    <td>
        <strong><?= htmlspecialchars($row['title']) ?></strong>
        <div class="small text-muted"><?= htmlspecialchars($row['description']) ?></div>
    </td>

    <td><?= htmlspecialchars($row['pic_name'] ?? '-') ?></td>

    <td>
        <?= $row['due_date'] ?? '-' ?>
        <?php if ($is_overdue): ?>
            <br><span class="badge badge-danger">OVERDUE</span>
        <?php endif; ?>
    </td>

    <td><span class="badge badge-<?= $priority_class ?>"><?= strtoupper($row['priority']) ?></span></td>

    <td><span class="badge badge-<?= $status_class ?>"><?= strtoupper(str_replace('_',' ', $row['status'])) ?></span></td>

    <td>
        <button class="btn btn-warning btn-sm" data-toggle="modal" data-target="#edit<?= $row['id'] ?>">Edit</button>
        <a href="capa.php?delete=<?= $row['id'] ?>" onclick="return confirm('Hapus CAPA ini?')" class="btn btn-danger btn-sm">Delete</a>
    </td>
</tr>

<!-- EDIT MODAL -->
<div class="modal fade" id="edit<?= $row['id'] ?>">
<div class="modal-dialog">
<div class="modal-content">
<form method="POST">
<div class="modal-header"><h5>Update CAPA</h5></div>
<div class="modal-body">

<input type="hidden" name="id" value="<?= $row['id'] ?>">

<p><strong><?= htmlspecialchars($row['title']) ?></strong></p>

<label class="small font-weight-bold">PIC</label>
<select name="pic_employee_id" class="form-control mb-2" required>
<?php
$emp_edit = pg_query($conn, "SELECT * FROM employees ORDER BY fullname ASC");
while ($e = pg_fetch_assoc($emp_edit)):
?>
<option value="<?= $e['id'] ?>" <?= $e['id'] == $row['pic_employee_id'] ? 'selected' : '' ?>>
<?= htmlspecialchars($e['fullname']) ?>
</option>
<?php endwhile; ?>
</select>

<label class="small font-weight-bold">Due Date</label>
<input type="date" name="due_date" class="form-control mb-2" value="<?= $row['due_date'] ?>" required>

<label class="small font-weight-bold">Priority</label>
<select name="priority" class="form-control mb-2">
<?php foreach (['low','medium','high','critical'] as $p): ?>
<option value="<?= $p ?>" <?= $p == $row['priority'] ? 'selected' : '' ?>><?= ucfirst($p) ?></option>
<?php endforeach; ?>
</select>

<label class="small font-weight-bold">Status</label>
<select name="status" class="form-control mb-2">
<?php foreach (['open' => 'Open', 'in_progress' => 'In Progress', 'closed' => 'Closed'] as $val => $label): ?>
<option value="<?= $val ?>" <?= $val == $row['status'] ? 'selected' : '' ?>><?= $label ?></option>
<?php endforeach; ?>
</select>

<label class="small font-weight-bold">Catatan Penyelesaian</label>
<textarea name="closing_notes" class="form-control" rows="2" placeholder="Diisi saat status Closed"><?= htmlspecialchars($row['closing_notes'] ?? '') ?></textarea>

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


<!-- ADD MODAL (manual) -->
<div class="modal fade" id="addModal">
<div class="modal-dialog">
<div class="modal-content">
<form method="POST">
<div class="modal-header"><h5>Add CAPA Manual</h5></div>
<div class="modal-body">

<label class="small font-weight-bold">Judul</label>
<input class="form-control mb-2" name="title" placeholder="Judul tindak lanjut" required>

<label class="small font-weight-bold">Deskripsi</label>
<textarea class="form-control mb-2" name="description" rows="3" placeholder="Detail tindakan korektif/preventif" required></textarea>

<label class="small font-weight-bold">PIC</label>
<select name="pic_employee_id" class="form-control mb-2" required>
<option value="">Pilih PIC</option>
<?php while ($e = pg_fetch_assoc($employees)): ?>
<option value="<?= $e['id'] ?>"><?= htmlspecialchars($e['fullname']) ?></option>
<?php endwhile; ?>
</select>

<label class="small font-weight-bold">Due Date</label>
<input type="date" class="form-control mb-2" name="due_date" value="<?= date('Y-m-d', strtotime('+7 days')) ?>" required>

<label class="small font-weight-bold">Priority</label>
<select name="priority" class="form-control">
<option value="low">Low</option>
<option value="medium" selected>Medium</option>
<option value="high">High</option>
<option value="critical">Critical</option>
</select>

</div>
<div class="modal-footer">
<button name="add" class="btn btn-primary">Save</button>
</div>
</form>
</div>
</div>
</div>

<?php require 'include/footer.php'; ?>
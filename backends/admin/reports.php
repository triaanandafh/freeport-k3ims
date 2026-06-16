<?php

require '../koneksi/auth_admin.php';
require '../koneksi/koneksi.php';

$page_title = "Reports";
$current_page = "reports";


/* =========================
   INSERT
========================= */
if (isset($_POST['add'])) {

    $employee_id = $_POST['employee_id'];
    $report_type = $_POST['report_type'];
    $title = $_POST['title'];
    $description = $_POST['description'];
    $location = $_POST['location'];
    $severity = $_POST['severity'];
    $status = $_POST['status'];

    $query = "
        INSERT INTO reports
        (
            id,
            employee_id,
            report_type,
            title,
            description,
            location,
            severity,
            status
        )

        VALUES
        (
            gen_random_uuid(),
            '$employee_id',
            '$report_type',
            '$title',
            '$description',
            '$location',
            '$severity',
            '$status'
        )
    ";

    pg_query($conn, $query);

    header("Location: reports.php");
    exit;
}


/* =========================
   UPDATE
========================= */
if (isset($_POST['edit'])) {

    $id = $_POST['id'];
    $employee_id = $_POST['employee_id'];
    $report_type = $_POST['report_type'];
    $title = $_POST['title'];
    $description = $_POST['description'];
    $location = $_POST['location'];
    $severity = $_POST['severity'];
    $status = $_POST['status'];

    $query = "
        UPDATE reports

        SET
            employee_id='$employee_id',
            report_type='$report_type',
            title='$title',
            description='$description',
            location='$location',
            severity='$severity',
            status='$status'

        WHERE id='$id'
    ";

    pg_query($conn, $query);

    header("Location: reports.php");
    exit;
}


/* =========================
   DELETE
========================= */
if (isset($_GET['delete'])) {

    $id = $_GET['delete'];

    pg_query($conn,"
        DELETE FROM reports
        WHERE id='$id'
    ");

    header("Location: reports.php");
    exit;
}


/* ambil reports */
$data = pg_query($conn, "

    SELECT reports.*, employees.fullname

    FROM reports

    JOIN employees
    ON reports.employee_id = employees.id

    ORDER BY created_at DESC

");


/* dropdown employees */
$employees = pg_query($conn, "
    SELECT *
    FROM employees
    ORDER BY fullname ASC
");

require 'include/header.php';

?>

<h1 class="h3 mb-4 text-gray-800">
    Reports
</h1>


<button
class="btn btn-primary mb-3"
data-toggle="modal"
data-target="#addModal">

Add Report

</button>


<div class="card shadow mb-4">

<div class="card-body">

<div class="table-responsive">

<table class="table table-bordered">

<thead>

<tr>
    <th>No</th>
    <th>Employee</th>
    <th>Type</th>
    <th>Title</th>
    <th>Location</th>
    <th>Severity</th>
    <th>Status</th>
    <th width="180">Action</th>
</tr>

</thead>

<tbody>

<?php
$no = 1;
while($row = pg_fetch_assoc($data)):
?>

<tr>

    <td><?= $no++ ?></td>
    <td><?= $row['fullname'] ?></td>
    <td><?= $row['report_type'] ?></td>
    <td><?= $row['title'] ?></td>
    <td><?= $row['location'] ?></td>
    <td><?= ucfirst($row['severity']) ?></td>
    <td><?= ucfirst($row['status']) ?></td>

    <td>

        <button
        class="btn btn-warning btn-sm"
        data-toggle="modal"
        data-target="#edit<?= $row['id'] ?>">

        Edit

        </button>


        <a
        href="reports.php?delete=<?= $row['id'] ?>"
        class="btn btn-danger btn-sm"
        onclick="return confirm('Delete data?')">

        Delete

        </a>

    </td>

</tr>


<!-- EDIT MODAL -->

<div class="modal fade" id="edit<?= $row['id'] ?>">

<div class="modal-dialog">

<div class="modal-content">

<form method="POST">

<div class="modal-header">

<h5>Edit Report</h5>

</div>

<div class="modal-body">

<input
type="hidden"
name="id"
value="<?= $row['id'] ?>">


<select
name="employee_id"
class="form-control mb-2"
required>

<?php
$emp_edit = pg_query($conn,"
SELECT * FROM employees
");
while($e = pg_fetch_assoc($emp_edit)):
?>

<option
value="<?= $e['id'] ?>"
<?= $e['id']==$row['employee_id'] ? 'selected' : '' ?>>

<?= $e['fullname'] ?>

</option>

<?php endwhile; ?>

</select>


<select
name="report_type"
class="form-control mb-2">

<option value="incident">Incident</option>
<option value="hazard">Hazard</option>
<option value="emergency">Emergency</option>

</select>


<input
class="form-control mb-2"
name="title"
value="<?= $row['title'] ?>"
required>


<textarea
class="form-control mb-2"
name="description"
required><?= $row['description'] ?></textarea>


<input
class="form-control mb-2"
name="location"
value="<?= $row['location'] ?>"
required>


<select
name="severity"
class="form-control mb-2">

<option value="low">Low</option>
<option value="medium">Medium</option>
<option value="high">High</option>
<option value="critical">Critical</option>

</select>


<select
name="status"
class="form-control">

<option value="open">Open</option>
<option value="reviewed">Reviewed</option>
<option value="resolved">Resolved</option>

</select>

</div>

<div class="modal-footer">

<button
name="edit"
class="btn btn-warning">

Update

</button>

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

<div class="modal-header">

<h5>Add Report</h5>

</div>

<div class="modal-body">


<select
name="employee_id"
class="form-control mb-2"
required>

<option value="">Choose Employee</option>

<?php while($emp = pg_fetch_assoc($employees)): ?>

<option value="<?= $emp['id'] ?>">

<?= $emp['fullname'] ?>

</option>

<?php endwhile; ?>

</select>


<select
name="report_type"
class="form-control mb-2">

<option value="incident">Incident</option>
<option value="hazard">Hazard</option>
<option value="emergency">Emergency</option>

</select>


<input
class="form-control mb-2"
name="title"
placeholder="Title"
required>


<textarea
class="form-control mb-2"
name="description"
placeholder="Description"
required></textarea>


<input
class="form-control mb-2"
name="location"
placeholder="Location"
required>


<select
name="severity"
class="form-control mb-2">

<option value="low">Low</option>
<option value="medium">Medium</option>
<option value="high">High</option>
<option value="critical">Critical</option>

</select>


<select
name="status"
class="form-control">

<option value="open">Open</option>
<option value="reviewed">Reviewed</option>
<option value="resolved">Resolved</option>

</select>

</div>

<div class="modal-footer">

<button
name="add"
class="btn btn-primary">

Save

</button>

</div>

</form>

</div>

</div>

</div>


<?php require 'include/footer.php'; ?>
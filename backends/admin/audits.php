<?php

require '../koneksi/auth_admin.php';
require '../koneksi/koneksi.php';

$page_title = "Audits";
$current_page = "audits";


/* ==========================
   INSERT
========================== */
if (isset($_POST['add'])) {

    $audit_title = $_POST['audit_title'];
    $audit_standard = $_POST['audit_standard'];
    $auditor = $_POST['auditor'];
    $audit_date = $_POST['audit_date'];
    $result = $_POST['result'];
    $notes = $_POST['notes'];

    pg_query($conn, "

        INSERT INTO audits
        (
            id,
            audit_title,
            audit_standard,
            auditor,
            audit_date,
            result,
            notes
        )

        VALUES
        (
            gen_random_uuid(),

            '$audit_title',
            '$audit_standard',
            '$auditor',
            '$audit_date',
            '$result',
            '$notes'
        )

    ");

    header("Location: audits.php");
    exit;
}


/* ==========================
   UPDATE
========================== */
if (isset($_POST['edit'])) {

    $id = $_POST['id'];

    $audit_title = $_POST['audit_title'];
    $audit_standard = $_POST['audit_standard'];
    $auditor = $_POST['auditor'];
    $audit_date = $_POST['audit_date'];
    $result = $_POST['result'];
    $notes = $_POST['notes'];

    pg_query($conn, "

        UPDATE audits

        SET

        audit_title='$audit_title',
        audit_standard='$audit_standard',
        auditor='$auditor',
        audit_date='$audit_date',
        result='$result',
        notes='$notes'

        WHERE id='$id'

    ");

    header("Location: audits.php");
    exit;
}


/* ==========================
   DELETE
========================== */
if (isset($_GET['delete'])) {

    $id = $_GET['delete'];

    pg_query($conn, "

        DELETE FROM audits
        WHERE id='$id'

    ");

    header("Location: audits.php");
    exit;
}


/* ==========================
   SELECT
========================== */
$data = pg_query($conn, "

    SELECT *
    FROM audits
    ORDER BY audit_date DESC

");

require 'include/header.php';

?>


<h1 class="h3 mb-4 text-gray-800">
    Audit Management
</h1>


<button
class="btn btn-primary mb-3"
data-toggle="modal"
data-target="#addModal">

Add Audit

</button>


<div class="card shadow mb-4">

<div class="card-body">

<div class="table-responsive">

<table class="table table-bordered">

<thead>

<tr>
    <th>No</th>
    <th>Audit Title</th>
    <th>Standard</th>
    <th>Auditor</th>
    <th>Date</th>
    <th>Result</th>
    <th>Action</th>
</tr>

</thead>

<tbody>

<?php
$no = 1;
while ($row = pg_fetch_assoc($data)):
?>

<tr>

<td><?= $no++ ?></td>

<td><?= $row['audit_title'] ?></td>

<td><?= $row['audit_standard'] ?></td>

<td><?= $row['auditor'] ?></td>

<td><?= $row['audit_date'] ?></td>

<td>

<?php if ($row['result'] == 'passed'): ?>

<span class="badge badge-success">Passed</span>

<?php elseif ($row['result'] == 'warning'): ?>

<span class="badge badge-warning">Warning</span>

<?php else: ?>

<span class="badge badge-danger">Failed</span>

<?php endif; ?>

</td>

<td>

<button
class="btn btn-warning btn-sm"
data-toggle="modal"
data-target="#edit<?= $row['id'] ?>">

Edit

</button>


<a
href="audits.php?delete=<?= $row['id'] ?>"
onclick="return confirm('Delete data?')"
class="btn btn-danger btn-sm">

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

<h5>Edit Audit</h5>

</div>


<div class="modal-body">

<input
type="hidden"
name="id"
value="<?= $row['id'] ?>">


<input
class="form-control mb-2"
name="audit_title"
value="<?= $row['audit_title'] ?>"
required>


<select
name="audit_standard"
class="form-control mb-2">

<option <?= $row['audit_standard']=="OHSAS 18001:2007" ? "selected" : "" ?>>
OHSAS 18001:2007
</option>

<option <?= $row['audit_standard']=="PP No. 50 Tahun 2012" ? "selected" : "" ?>>
PP No. 50 Tahun 2012
</option>

<option <?= $row['audit_standard']=="Permenaker No. 05/MEN/1996" ? "selected" : "" ?>>
Permenaker No. 05/MEN/1996
</option>

<option <?= $row['audit_standard']=="ISO 14001:2015" ? "selected" : "" ?>>
ISO 14001:2015
</option>

</select>


<input
class="form-control mb-2"
name="auditor"
value="<?= $row['auditor'] ?>"
required>


<input
class="form-control mb-2"
type="date"
name="audit_date"
value="<?= $row['audit_date'] ?>"
required>


<select
name="result"
class="form-control mb-2">

<option value="passed"
<?= $row['result']=="passed" ? "selected" : "" ?>>
Passed
</option>

<option value="warning"
<?= $row['result']=="warning" ? "selected" : "" ?>>
Warning
</option>

<option value="failed"
<?= $row['result']=="failed" ? "selected" : "" ?>>
Failed
</option>

</select>


<textarea
name="notes"
class="form-control"
required><?= $row['notes'] ?></textarea>


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

<h5>Add Audit</h5>

</div>


<div class="modal-body">


<input
class="form-control mb-2"
name="audit_title"
placeholder="Audit Title"
required>


<select
name="audit_standard"
class="form-control mb-2">

<option value="">Choose Standard</option>

<option>OHSAS 18001:2007</option>

<option>PP No. 50 Tahun 2012</option>

<option>Permenaker No. 05/MEN/1996</option>

<option>ISO 14001:2015</option>

</select>


<input
class="form-control mb-2"
name="auditor"
placeholder="Auditor Name"
required>


<input
class="form-control mb-2"
type="date"
name="audit_date"
required>


<select
name="result"
class="form-control mb-2">

<option value="passed">Passed</option>

<option value="warning">Warning</option>

<option value="failed">Failed</option>

</select>


<textarea
class="form-control"
name="notes"
placeholder="Notes"
required></textarea>


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
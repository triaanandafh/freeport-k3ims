<?php

require '../koneksi/auth_admin.php';
require '../koneksi/koneksi.php';

$page_title = "Audit Checklists";
$current_page = "audit_checklists";


/* ==========================
   INSERT
========================== */
if (isset($_POST['add'])) {

    $audit_id = $_POST['audit_id'];
    $checklist_item = $_POST['checklist_item'];
    $is_pass = isset($_POST['is_pass']) ? 'TRUE' : 'FALSE';

    pg_query($conn, "

        INSERT INTO audit_checklists
        (
            id,
            audit_id,
            checklist_item,
            is_pass
        )

        VALUES
        (
            gen_random_uuid(),
            '$audit_id',
            '$checklist_item',
            $is_pass
        )

    ");

    header("Location: audit_checklists.php");
    exit;
}


/* ==========================
   DELETE
========================== */
if (isset($_GET['delete'])) {

    $id = $_GET['delete'];

    pg_query($conn, "

        DELETE FROM audit_checklists
        WHERE id='$id'

    ");

    header("Location: audit_checklists.php");
    exit;
}


/* ==========================
   SELECT DATA
========================== */

$data = pg_query($conn, "

    SELECT
        audit_checklists.*,
        audits.audit_title,
        audits.audit_standard

    FROM audit_checklists

    JOIN audits
    ON audit_checklists.audit_id = audits.id

    ORDER BY audits.audit_date DESC

");


/* dropdown audit */
$audits = pg_query($conn, "

    SELECT *
    FROM audits
    ORDER BY audit_date DESC

");

require 'include/header.php';

?>


<h1 class="h3 mb-4 text-gray-800">
    Audit Checklists
</h1>


<button
class="btn btn-primary mb-3"
data-toggle="modal"
data-target="#addModal">

Add Checklist

</button>


<div class="card shadow mb-4">

<div class="card-body">

<div class="table-responsive">

<table class="table table-bordered">

<thead>

<tr>
    <th>No</th>
    <th>Audit</th>
    <th>Standard</th>
    <th>Checklist Item</th>
    <th>Status</th>
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

<td><?= $row['checklist_item'] ?></td>

<td>

<?php if ($row['is_pass'] == 't'): ?>

<span class="badge badge-success">
PASS
</span>

<?php else: ?>

<span class="badge badge-danger">
FAIL
</span>

<?php endif; ?>

</td>

<td>

<a
href="audit_checklists.php?delete=<?= $row['id'] ?>"
onclick="return confirm('Delete data?')"
class="btn btn-danger btn-sm">

Delete

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

<h5>Add Audit Checklist</h5>

</div>


<div class="modal-body">

<select
name="audit_id"
class="form-control mb-3"
required>

<option value="">Choose Audit</option>

<?php while($audit = pg_fetch_assoc($audits)): ?>

<option value="<?= $audit['id'] ?>">

<?= $audit['audit_title'] ?>
-
<?= $audit['audit_standard'] ?>

</option>

<?php endwhile; ?>

</select>


<input
class="form-control mb-3"
name="checklist_item"
placeholder="Checklist Item"
required>


<div class="form-check">

<input
type="checkbox"
name="is_pass">

 Audit Passed

</div>

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
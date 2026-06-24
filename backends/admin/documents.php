<?php

require '../koneksi/auth_admin.php';
require '../koneksi/koneksi.php';

$page_title = "Documents";
$current_page = "documents";


/* =========================
   INSERT (upload baru)
========================= */
if (isset($_POST['add'])) {

    $title = $_POST['title'];
    $category = $_POST['category'];
    $status = $_POST['status'];
    $effective_date = $_POST['effective_date'] !== '' ? "'" . $_POST['effective_date'] . "'" : 'NULL';
    $expiry_date = $_POST['expiry_date'] !== '' ? "'" . $_POST['expiry_date'] . "'" : 'NULL';
    $uploaded_by = $_SESSION['user_id'];

    $file_name = '';

    /* upload file */
    if ($_FILES['file']['name'] != '') {

        $file_name = time() . "_" . $_FILES['file']['name'];

        move_uploaded_file(
            $_FILES['file']['tmp_name'],
            "../uploads/" . $file_name
        );
    }

    $query = "
        INSERT INTO documents
        (
            id,
            title,
            category,
            file_url,
            status,
            effective_date,
            expiry_date,
            uploaded_by,
            status_updated_by,
            status_updated_at
        )

        VALUES
        (
            gen_random_uuid(),
            '$title',
            '$category',
            '$file_name',
            '$status',
            $effective_date,
            $expiry_date,
            '$uploaded_by',
            '$uploaded_by',
            now()
        )
    ";

    pg_query($conn, $query);

    header("Location: documents.php");
    exit;
}


/* =========================
   UPDATE STATUS / WORKFLOW
========================= */
if (isset($_POST['edit'])) {

    $id = $_POST['id'];
    $status = $_POST['status'];
    $effective_date = $_POST['effective_date'] !== '' ? "'" . $_POST['effective_date'] . "'" : 'NULL';
    $expiry_date = $_POST['expiry_date'] !== '' ? "'" . $_POST['expiry_date'] . "'" : 'NULL';
    $updated_by = $_SESSION['user_id'];

    pg_query($conn, "
        UPDATE documents
        SET status='$status',
            effective_date=$effective_date,
            expiry_date=$expiry_date,
            status_updated_by='$updated_by',
            status_updated_at=now()
        WHERE id='$id'
    ");

    header("Location: documents.php");
    exit;
}


/* =========================
   DELETE
========================= */
if (isset($_GET['delete'])) {

    $id = $_GET['delete'];

    pg_query($conn,"
        DELETE FROM documents
        WHERE id='$id'
    ");

    header("Location: documents.php");
    exit;
}


/* ambil documents */
$data = pg_query($conn, "

    SELECT documents.*, u1.fullname AS uploader_name, u2.fullname AS updater_name

    FROM documents

    JOIN users u1
    ON documents.uploaded_by = u1.id

    LEFT JOIN users u2
    ON documents.status_updated_by = u2.id

    ORDER BY
        CASE documents.status
            WHEN 'draft' THEN 0
            WHEN 'review' THEN 1
            WHEN 'approved' THEN 2
            ELSE 3
        END,
        documents.title ASC

");

require 'include/header.php';

?>

<h1 class="h3 mb-4 text-gray-800">
    Documents
</h1>


<button
class="btn btn-primary mb-3"
data-toggle="modal"
data-target="#addModal">

Upload Document

</button>


<div class="card shadow mb-4">

<div class="card-body">

<div class="table-responsive">

<table class="table table-bordered">

<thead>

<tr>
    <th>No</th>
    <th>Title</th>
    <th>Category</th>
    <th>File</th>
    <th>Status</th>
    <th>Effective</th>
    <th>Expiry</th>
    <th>Uploaded By</th>
    <th width="150">Action</th>
</tr>

</thead>

<tbody>

<?php
$no = 1;
while ($row = pg_fetch_assoc($data)):

$status_class = [
    'draft'    => 'secondary',
    'review'   => 'warning',
    'approved' => 'success',
    'obsolete' => 'danger',
][$row['status']] ?? 'secondary';

$is_expiring = $row['expiry_date'] !== null
    && $row['status'] === 'approved'
    && strtotime($row['expiry_date']) <= strtotime('+30 days')
    && strtotime($row['expiry_date']) >= strtotime(date('Y-m-d'));

$is_expired = $row['expiry_date'] !== null
    && $row['status'] === 'approved'
    && strtotime($row['expiry_date']) < strtotime(date('Y-m-d'));

?>

<tr>

<td><?= $no++ ?></td>

<td><?= htmlspecialchars($row['title']) ?></td>

<td><?= htmlspecialchars($row['category']) ?></td>

<td>
<a target="_blank" href="../uploads/<?= htmlspecialchars($row['file_url']) ?>">
View File
</a>
</td>

<td>
<span class="badge badge-<?= $status_class ?>"><?= strtoupper($row['status']) ?></span>
</td>

<td><?= $row['effective_date'] ?? '-' ?></td>

<td>
<?= $row['expiry_date'] ?? '-' ?>
<?php if ($is_expired): ?>
    <br><span class="badge badge-danger">EXPIRED</span>
<?php elseif ($is_expiring): ?>
    <br><span class="badge badge-warning">SEGERA KADALUARSA</span>
<?php endif; ?>
</td>

<td><?= htmlspecialchars($row['uploader_name']) ?></td>

<td>
<button class="btn btn-warning btn-sm" data-toggle="modal" data-target="#edit<?= $row['id'] ?>">
Update Status
</button>


<a href="documents.php?delete=<?= $row['id'] ?>"
onclick="return confirm('Delete data?')"
class="btn btn-danger btn-sm">
Delete
</a>
</td>


<!-- UPDATE STATUS MODAL -->
<div class="modal fade" id="edit<?= $row['id'] ?>">
<div class="modal-dialog">
<div class="modal-content">
<form method="POST">
<div class="modal-header">
<h5>Update Status — <?= htmlspecialchars($row['title']) ?></h5>
</div>
<div class="modal-body">

<input type="hidden" name="id" value="<?= $row['id'] ?>">

<label class="small font-weight-bold">Status</label>
<select name="status" class="form-control mb-3">
<?php foreach (['draft' => 'Draft', 'review' => 'Review', 'approved' => 'Approved', 'obsolete' => 'Obsolete'] as $val => $label): ?>
<option value="<?= $val ?>" <?= $val == $row['status'] ? 'selected' : '' ?>><?= $label ?></option>
<?php endforeach; ?>
</select>

<label class="small font-weight-bold">Effective Date</label>
<input type="date" name="effective_date" class="form-control mb-3" value="<?= $row['effective_date'] ?>">

<label class="small font-weight-bold">Expiry Date</label>
<input type="date" name="expiry_date" class="form-control mb-2" value="<?= $row['expiry_date'] ?>">

<?php if ($row['updater_name']): ?>
<small class="text-muted">
Terakhir diubah oleh <?= htmlspecialchars($row['updater_name']) ?> pada <?= $row['status_updated_at'] ?>
</small>
<?php endif; ?>

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

<form method="POST" enctype="multipart/form-data">

<div class="modal-header">

<h5>Upload Document</h5>

</div>

<div class="modal-body">

<input
class="form-control mb-2"
name="title"
placeholder="Document Title"
required>


<input
class="form-control mb-2"
name="category"
placeholder="Category"
required>


<input
class="form-control mb-2"
type="file"
name="file"
required>

<label class="small font-weight-bold">Status</label>
<select
name="status"
class="form-control mb-2">

<option value="draft" selected>Draft</option>
<option value="review">Review</option>
<option value="approved">Approved</option>
<option value="obsolete">Obsolete</option>

</select>

<label class="small font-weight-bold">Effective Date (opsional)</label>
<input type="date" class="form-control mb-2" name="effective_date">

<label class="small font-weight-bold">Expiry Date (opsional)</label>
<input type="date" class="form-control mb-2" name="expiry_date">

</div>

<div class="modal-footer">

<button
name="add"
class="btn btn-primary">

Upload

</button>

</div>

</form>

</div>

</div>

</div>


<?php require 'include/footer.php'; ?>
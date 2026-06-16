<?php

require '../koneksi/auth_admin.php';
require '../koneksi/koneksi.php';

$page_title = "Documents";
$current_page = "documents";


/* =========================
   INSERT
========================= */
if (isset($_POST['add'])) {

    $title = $_POST['title'];
    $category = $_POST['category'];
    $status = $_POST['status'];
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
            uploaded_by
        )

        VALUES
        (
            gen_random_uuid(),
            '$title',
            '$category',
            '$file_name',
            '$status',
            '$uploaded_by'
        )
    ";

    pg_query($conn, $query);

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

    SELECT documents.*, users.fullname

    FROM documents

    JOIN users
    ON documents.uploaded_by = users.id

    ORDER BY title ASC

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

<table class="table table-bordered">

<thead>

<tr>
    <th>No</th>
    <th>Title</th>
    <th>Category</th>
    <th>File</th>
    <th>Status</th>
    <th>Uploaded By</th>
    <th>Action</th>
</tr>

</thead>

<tbody>

<?php
$no=1;
while($row = pg_fetch_assoc($data)):
?>

<tr>

<td><?= $no++ ?></td>

<td><?= $row['title'] ?></td>

<td><?= $row['category'] ?></td>

<td>

<a
target="_blank"
href="../uploads/<?= $row['file_url'] ?>">

View File

</a>

</td>

<td><?= $row['status'] ?></td>

<td><?= $row['fullname'] ?></td>

<td>

<a
href="documents.php?delete=<?= $row['id'] ?>"
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


<select
name="status"
class="form-control">

<option value="active">Active</option>
<option value="archived">Archived</option>

</select>

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
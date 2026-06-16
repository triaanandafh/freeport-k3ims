<?php

require '../koneksi/auth_admin.php';
require '../koneksi/koneksi.php';

$page_title = "System Users";
$current_page = "users";


/* =========================
   INSERT
========================= */
if (isset($_POST['add'])) {

    $fullname = $_POST['fullname'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $role = $_POST['role'];

    $query = "
        INSERT INTO users
        (id, fullname, email, password, role)

        VALUES
        (
            gen_random_uuid(),
            '$fullname',
            '$email',
            '$password',
            '$role'
        )
    ";

    pg_query($conn, $query);

    header("Location: users.php");
    exit;
}


/* =========================
   UPDATE
========================= */
if (isset($_POST['edit'])) {

    $id = $_POST['id'];
    $fullname = $_POST['fullname'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $role = $_POST['role'];

    $query = "
        UPDATE users

        SET
            fullname='$fullname',
            email='$email',
            password='$password',
            role='$role'

        WHERE id='$id'
    ";

    pg_query($conn, $query);

    header("Location: users.php");
    exit;
}


/* =========================
   DELETE
========================= */
if (isset($_GET['delete'])) {

    $id = $_GET['delete'];

    pg_query($conn, "
        DELETE FROM users
        WHERE id='$id'
    ");

    header("Location: users.php");
    exit;
}


/* ambil data */
$data = pg_query($conn, "
    SELECT *
    FROM users
    ORDER BY fullname ASC
");

require 'include/header.php';

?>

<h1 class="h3 mb-4 text-gray-800">
    System Users
</h1>


<button
    class="btn btn-primary mb-3"
    data-toggle="modal"
    data-target="#addModal">

    Add User

</button>


<div class="card shadow mb-4">

<div class="card-body">

<div class="table-responsive">

<table class="table table-bordered">

<thead>

<tr>
    <th>No</th>
    <th>Fullname</th>
    <th>Email</th>
    <th>Role</th>
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
    <td><?= $row['email'] ?></td>
    <td><?= ucfirst($row['role']) ?></td>

    <td>

        <button
            class="btn btn-warning btn-sm"
            data-toggle="modal"
            data-target="#edit<?= $row['id'] ?>">

            Edit

        </button>

        <a
            href="users.php?delete=<?= $row['id'] ?>"
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

<h5>Edit User</h5>

</div>

<div class="modal-body">

<input
type="hidden"
name="id"
value="<?= $row['id'] ?>">


<input
class="form-control mb-2"
name="fullname"
value="<?= $row['fullname'] ?>"
required>


<input
class="form-control mb-2"
name="email"
value="<?= $row['email'] ?>"
required>


<input
class="form-control mb-2"
name="password"
value="<?= $row['password'] ?>"
required>


<select
name="role"
class="form-control">

<option
value="admin"
<?= $row['role']=="admin" ? "selected" : "" ?>>

Admin

</option>


<option
value="manager"
<?= $row['role']=="manager" ? "selected" : "" ?>>

Manager

</option>

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

<h5>Add User</h5>

</div>

<div class="modal-body">

<input
class="form-control mb-2"
name="fullname"
placeholder="Fullname"
required>


<input
class="form-control mb-2"
name="email"
placeholder="Email"
required>


<input
class="form-control mb-2"
name="password"
placeholder="Password"
required>


<select
name="role"
class="form-control">

<option value="admin">Admin</option>
<option value="manager">Manager</option>

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
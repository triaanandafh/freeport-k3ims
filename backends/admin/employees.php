<?php

require '../koneksi/auth_admin.php';
require '../koneksi/koneksi.php';

$page_title = "Employees";
$current_page = "employees";


/* =========================
   INSERT
========================= */
if (isset($_POST['add'])) {

    $fullname = $_POST['fullname'];
    $department = $_POST['department_id'];
    $position = $_POST['position'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $status = $_POST['status'];

    $query = "
        INSERT INTO employees
        (id, fullname, department_id, position, phone, email, status)

        VALUES
        (
            gen_random_uuid(),
            '$fullname',
            '$department',
            '$position',
            '$phone',
            '$email',
            '$status'
        )
    ";

    pg_query($conn, $query);

    header("Location: employees.php");
    exit;
}


/* =========================
   UPDATE
========================= */
if (isset($_POST['edit'])) {

    $id = $_POST['id'];
    $fullname = $_POST['fullname'];
    $department = $_POST['department_id'];
    $position = $_POST['position'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $status = $_POST['status'];

    $query = "
        UPDATE employees

        SET
            fullname='$fullname',
            department_id='$department',
            position='$position',
            phone='$phone',
            email='$email',
            status='$status'

        WHERE id='$id'
    ";

    pg_query($conn, $query);

    header("Location: employees.php");
    exit;
}


/* =========================
   DELETE
========================= */
if (isset($_GET['delete'])) {

    $id = $_GET['delete'];

    pg_query($conn, "
        DELETE FROM employees
        WHERE id='$id'
    ");

    header("Location: employees.php");
    exit;
}


/* ambil employees */
$data = pg_query($conn, "

    SELECT employees.*, departments.department_name

    FROM employees

    JOIN departments
    ON employees.department_id = departments.id

    ORDER BY employees.fullname ASC

");


/* ambil departments dropdown */
$departments = pg_query($conn, "
    SELECT * FROM departments
    ORDER BY department_name ASC
");

require 'include/header.php';

?>

<h1 class="h3 mb-4 text-gray-800">
    Employees
</h1>


<button
    class="btn btn-primary mb-3"
    data-toggle="modal"
    data-target="#addModal">

    Add Employee

</button>


<div class="card shadow mb-4">

    <div class="card-body">

        <div class="table-responsive">

            <table class="table table-bordered">

                <thead>

                    <tr>
                        <th>No</th>
                        <th>Name</th>
                        <th>Department</th>
                        <th>Position</th>
                        <th>Phone</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th width="180">Action</th>
                    </tr>

                </thead>

                <tbody>

                <?php
                $no = 1;
                while ($row = pg_fetch_assoc($data)):
                ?>

                    <tr>

                        <td><?= $no++ ?></td>
                        <td><?= $row['fullname'] ?></td>
                        <td><?= $row['department_name'] ?></td>
                        <td><?= $row['position'] ?></td>
                        <td><?= $row['phone'] ?></td>
                        <td><?= $row['email'] ?></td>
                        <td><?= $row['status'] ?></td>

                        <td>

                            <button
                                class="btn btn-warning btn-sm"
                                data-toggle="modal"
                                data-target="#edit<?= $row['id'] ?>">

                                Edit

                            </button>

                            <a
                                href="employees.php?delete=<?= $row['id'] ?>"
                                onclick="return confirm('Delete data?')"
                                class="btn btn-danger btn-sm">

                                Delete

                            </a>

                        </td>

                    </tr>


<!-- =========================
     EDIT MODAL
========================= -->

<div class="modal fade" id="edit<?= $row['id'] ?>">

<div class="modal-dialog">

<div class="modal-content">

<form method="POST">

<div class="modal-header">

<h5>Edit Employee</h5>

</div>

<div class="modal-body">

<input type="hidden"
name="id"
value="<?= $row['id'] ?>">

<input
class="form-control mb-2"
type="text"
name="fullname"
value="<?= $row['fullname'] ?>"
required>

<select
name="department_id"
class="form-control mb-2">

<?php
$dept_edit = pg_query($conn,"
SELECT * FROM departments
");
while($d = pg_fetch_assoc($dept_edit)):
?>

<option
value="<?= $d['id'] ?>"
<?= $d['id']==$row['department_id'] ? 'selected' : '' ?>>

<?= $d['department_name'] ?>

</option>

<?php endwhile; ?>

</select>

<input
class="form-control mb-2"
name="position"
value="<?= $row['position'] ?>"
required>

<input
class="form-control mb-2"
name="phone"
value="<?= $row['phone'] ?>"
required>

<input
class="form-control mb-2"
name="email"
value="<?= $row['email'] ?>"
required>

<select
name="status"
class="form-control">

<option value="active">Active</option>
<option value="inactive">Inactive</option>

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


<!-- =========================
     ADD MODAL
========================= -->

<div class="modal fade" id="addModal">

<div class="modal-dialog">

<div class="modal-content">

<form method="POST">

<div class="modal-header">

<h5>Add Employee</h5>

</div>

<div class="modal-body">

<input
class="form-control mb-2"
type="text"
name="fullname"
placeholder="Fullname"
required>


<select
name="department_id"
class="form-control mb-2"
required>

<option value="">Choose Department</option>

<?php while($dept = pg_fetch_assoc($departments)): ?>

<option value="<?= $dept['id'] ?>">

<?= $dept['department_name'] ?>

</option>

<?php endwhile; ?>

</select>


<input
class="form-control mb-2"
name="position"
placeholder="Position"
required>


<input
class="form-control mb-2"
name="phone"
placeholder="Phone"
required>


<input
class="form-control mb-2"
name="email"
placeholder="Email"
required>


<select
name="status"
class="form-control">

<option value="active">Active</option>
<option value="inactive">Inactive</option>

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
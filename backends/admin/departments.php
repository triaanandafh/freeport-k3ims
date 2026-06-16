<?php

require '../koneksi/auth_admin.php';
require '../koneksi/koneksi.php';

/* page config */
$page_title = "Departments";
$current_page = "departments";

/* =========================
   INSERT
========================= */
if (isset($_POST['add'])) {

    $department = $_POST['department_name'];

    $query = "
        INSERT INTO departments
        (id, department_name)
        VALUES
        (gen_random_uuid(), '$department')
    ";

    pg_query($conn, $query);

    header("Location: departments.php");
    exit;
}


/* =========================
   UPDATE
========================= */
if (isset($_POST['edit'])) {

    $id = $_POST['id'];
    $department = $_POST['department_name'];

    $query = "
        UPDATE departments
        SET department_name='$department'
        WHERE id='$id'
    ";

    pg_query($conn, $query);

    header("Location: departments.php");
    exit;
}


/* =========================
   DELETE
========================= */
if (isset($_GET['delete'])) {

    $id = $_GET['delete'];

    $query = "
        DELETE FROM departments
        WHERE id='$id'
    ";

    pg_query($conn, $query);

    header("Location: departments.php");
    exit;
}


/* ambil data */
$data = pg_query($conn, "SELECT * FROM departments ORDER BY department_name ASC");

require 'include/header.php';

?>

<h1 class="h3 mb-4 text-gray-800">
    Departments
</h1>


<!-- Add Button -->
<button
    class="btn btn-primary mb-3"
    data-toggle="modal"
    data-target="#addModal">

    Add Department

</button>


<div class="card shadow mb-4">

    <div class="card-body">

        <div class="table-responsive">

            <table class="table table-bordered">

                <thead>

                    <tr>
                        <th>No</th>
                        <th>Department Name</th>
                        <th width="200">Action</th>
                    </tr>

                </thead>

                <tbody>

                <?php
                $no = 1;
                while($row = pg_fetch_assoc($data)):
                ?>

                    <tr>

                        <td><?= $no++ ?></td>

                        <td><?= $row['department_name'] ?></td>

                        <td>

                            <!-- Edit -->

                            <button
                                class="btn btn-warning btn-sm"
                                data-toggle="modal"
                                data-target="#edit<?= $row['id'] ?>">

                                Edit

                            </button>


                            <!-- Delete -->

                            <a
                                href="departments.php?delete=<?= $row['id'] ?>"
                                class="btn btn-danger btn-sm"
                                onclick="return confirm('Delete data?')">

                                Delete

                            </a>

                        </td>

                    </tr>


                    <!-- EDIT MODAL -->
                    <div class="modal fade"
                        id="edit<?= $row['id'] ?>">

                        <div class="modal-dialog">

                            <div class="modal-content">

                                <form method="POST">

                                    <div class="modal-header">

                                        <h5>Edit Department</h5>

                                    </div>

                                    <div class="modal-body">

                                        <input
                                            type="hidden"
                                            name="id"
                                            value="<?= $row['id'] ?>">

                                        <input
                                            type="text"
                                            name="department_name"
                                            value="<?= $row['department_name'] ?>"
                                            class="form-control"
                                            required>

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

                    <h5>Add Department</h5>

                </div>

                <div class="modal-body">

                    <input
                        type="text"
                        name="department_name"
                        class="form-control"
                        placeholder="Department Name"
                        required>

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
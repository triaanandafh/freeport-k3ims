<?php

require '../koneksi/auth_admin.php';
require '../koneksi/koneksi.php';

$page_title = "SOP Management";
$current_page = "sop";


/* =========================
   INSERT ACTIVITY
========================= */
if (isset($_POST['add_activity'])) {

    $dept_id     = $_POST['department_id'];
    $name        = $_POST['activity_name'];
    $desc        = $_POST['description'];
    $std         = $_POST['standard_ref'];

    pg_query($conn, "
        INSERT INTO sop_activities
        (id, department_id, activity_name, description, standard_ref)
        VALUES
        (gen_random_uuid(), '$dept_id', '$name', '$desc', '$std')
    ");

    header("Location: sop.php");
    exit;
}


/* =========================
   DELETE ACTIVITY
========================= */
if (isset($_GET['delete_activity'])) {

    $id = $_GET['delete_activity'];

    pg_query($conn, "DELETE FROM sop_checklist_items WHERE activity_id='$id'");
    pg_query($conn, "DELETE FROM sop_activities WHERE id='$id'");

    header("Location: sop.php");
    exit;
}


/* =========================
   INSERT CHECKLIST ITEM
========================= */
if (isset($_POST['add_item'])) {

    $activity_id = $_POST['activity_id'];
    $item_text   = $_POST['item_text'];
    $is_required = isset($_POST['is_required']) ? 'TRUE' : 'FALSE';

    /* hitung order_no berikutnya */
    $res = pg_query($conn, "
        SELECT COALESCE(MAX(order_no), 0) + 1 AS next_no
        FROM sop_checklist_items
        WHERE activity_id='$activity_id'
    ");
    $next = pg_fetch_assoc($res);
    $order_no = $next['next_no'];

    pg_query($conn, "
        INSERT INTO sop_checklist_items
        (id, activity_id, item_text, is_required, order_no)
        VALUES
        (gen_random_uuid(), '$activity_id', '$item_text', $is_required, $order_no)
    ");

    header("Location: sop.php");
    exit;
}


/* =========================
   DELETE CHECKLIST ITEM
========================= */
if (isset($_GET['delete_item'])) {

    $id = $_GET['delete_item'];

    pg_query($conn, "DELETE FROM sop_checklist_items WHERE id='$id'");

    header("Location: sop.php");
    exit;
}


/* ambil semua activity + dept */
$activities = pg_query($conn, "

    SELECT
        sop_activities.*,
        departments.department_name

    FROM sop_activities

    JOIN departments
    ON sop_activities.department_id = departments.id

    ORDER BY departments.department_name ASC, sop_activities.activity_name ASC

");

/* ambil semua checklist items */
$items_all = pg_query($conn, "
    SELECT * FROM sop_checklist_items
    ORDER BY activity_id, order_no ASC
");

/* kelompokkan items per activity */
$items_map = [];
while ($item = pg_fetch_assoc($items_all)) {
    $items_map[$item['activity_id']][] = $item;
}

/* dropdown departments */
$departments = pg_query($conn, "SELECT * FROM departments ORDER BY department_name ASC");

require 'include/header.php';

?>


<h1 class="h3 mb-4 text-gray-800">
    SOP Management
</h1>


<!-- Add Activity Button -->
<button
    class="btn btn-primary mb-3"
    data-toggle="modal"
    data-target="#addActivityModal">

    <i class="fas fa-plus fa-sm"></i> Add SOP Activity

</button>


<!-- Activity Cards -->
<?php while ($act = pg_fetch_assoc($activities)): ?>

<div class="card shadow mb-4">

    <div class="card-header py-3 d-flex justify-content-between align-items-center">

        <div>

            <h6 class="m-0 font-weight-bold text-primary">
                <?= htmlspecialchars($act['activity_name']) ?>
            </h6>

            <small class="text-muted">
                <?= htmlspecialchars($act['department_name']) ?>
                &nbsp;|&nbsp;
                <span class="badge badge-info"><?= htmlspecialchars($act['standard_ref']) ?></span>
            </small>

        </div>

        <div>

            <button
                class="btn btn-success btn-sm"
                data-toggle="modal"
                data-target="#addItem<?= $act['id'] ?>">
                + Item
            </button>

            <a
                href="sop.php?delete_activity=<?= $act['id'] ?>"
                onclick="return confirm('Hapus activity dan semua item SOP-nya?')"
                class="btn btn-danger btn-sm">
                Hapus
            </a>

        </div>

    </div>

    <div class="card-body">

        <?php if (!empty($act['description'])): ?>
        <p class="text-muted small mb-3"><?= htmlspecialchars($act['description']) ?></p>
        <?php endif; ?>

        <!-- Checklist Items -->
        <?php if (isset($items_map[$act['id']])): ?>

        <table class="table table-bordered table-sm">

            <thead>
                <tr>
                    <th width="40">No</th>
                    <th>Poin SOP</th>
                    <th width="100">Wajib</th>
                    <th width="80">Hapus</th>
                </tr>
            </thead>

            <tbody>

            <?php foreach ($items_map[$act['id']] as $item): ?>

            <tr>

                <td><?= $item['order_no'] ?></td>

                <td><?= htmlspecialchars($item['item_text']) ?></td>

                <td>
                    <?php if ($item['is_required'] == 't'): ?>
                    <span class="badge badge-danger">Wajib</span>
                    <?php else: ?>
                    <span class="badge badge-secondary">Opsional</span>
                    <?php endif; ?>
                </td>

                <td>
                    <a
                        href="sop.php?delete_item=<?= $item['id'] ?>"
                        onclick="return confirm('Hapus item ini?')"
                        class="btn btn-danger btn-sm">
                        <i class="fas fa-trash fa-xs"></i>
                    </a>
                </td>

            </tr>

            <?php endforeach; ?>

            </tbody>

        </table>

        <?php else: ?>

        <p class="text-muted small">Belum ada poin SOP. Klik "+ Item" untuk menambahkan.</p>

        <?php endif; ?>

    </div>

</div>


<!-- ADD ITEM MODAL per activity -->
<div class="modal fade" id="addItem<?= $act['id'] ?>">

<div class="modal-dialog">

<div class="modal-content">

<form method="POST">

<div class="modal-header">
<h5>Tambah Poin SOP — <?= htmlspecialchars($act['activity_name']) ?></h5>
</div>

<div class="modal-body">

<input type="hidden" name="activity_id" value="<?= $act['id'] ?>">

<textarea
    name="item_text"
    class="form-control mb-3"
    rows="3"
    placeholder="Tuliskan poin SOP..."
    required></textarea>

<div class="form-check">
    <input type="checkbox" name="is_required" class="form-check-input" id="req<?= $act['id'] ?>">
    <label class="form-check-label" for="req<?= $act['id'] ?>">
        Wajib (required)
    </label>
</div>

</div>

<div class="modal-footer">
<button name="add_item" class="btn btn-success">Simpan</button>
</div>

</form>

</div>

</div>

</div>

<?php endwhile; ?>


<!-- ADD ACTIVITY MODAL -->
<div class="modal fade" id="addActivityModal">

<div class="modal-dialog">

<div class="modal-content">

<form method="POST">

<div class="modal-header">
<h5>Add SOP Activity</h5>
</div>

<div class="modal-body">

<select name="department_id" class="form-control mb-2" required>
<option value="">Pilih Departemen</option>
<?php while ($d = pg_fetch_assoc($departments)): ?>
<option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['department_name']) ?></option>
<?php endwhile; ?>
</select>

<input
    class="form-control mb-2"
    name="activity_name"
    placeholder="Nama Activity (e.g. Pre-Drilling Inspection)"
    required>

<textarea
    class="form-control mb-2"
    name="description"
    rows="2"
    placeholder="Deskripsi singkat activity"></textarea>

<input
    class="form-control mb-2"
    name="standard_ref"
    placeholder="Standar referensi (e.g. PP No. 50 Tahun 2012)">

</div>

<div class="modal-footer">
<button name="add_activity" class="btn btn-primary">Simpan</button>
</div>

</form>

</div>

</div>

</div>


<?php require 'include/footer.php'; ?>

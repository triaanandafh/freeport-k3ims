<?php

require '../koneksi/auth_manager.php';
require '../koneksi/koneksi.php';

$page_title = "SOP Management";
$current_page = "sop";

$activities = pg_query($conn, "
    SELECT sop_activities.*, departments.department_name
    FROM sop_activities
    JOIN departments ON sop_activities.department_id = departments.id
    ORDER BY departments.department_name ASC, sop_activities.activity_name ASC
");

$items_all = pg_query($conn, "SELECT * FROM sop_checklist_items ORDER BY activity_id, order_no ASC");

$items_map = [];
while ($item = pg_fetch_assoc($items_all)) {
    $items_map[$item['activity_id']][] = $item;
}

require 'include/header.php';

?>

<h1 class="h3 mb-4 text-gray-800">SOP Management</h1>

<p class="text-muted">Tampilan baca saja — perubahan SOP dilakukan oleh Administrator.</p>

<?php while ($act = pg_fetch_assoc($activities)): ?>

<div class="card shadow mb-4">

    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary"><?= htmlspecialchars($act['activity_name']) ?></h6>
        <small class="text-muted">
            <?= htmlspecialchars($act['department_name']) ?>
            &nbsp;|&nbsp;
            <span class="badge badge-info"><?= htmlspecialchars($act['standard_ref']) ?></span>
        </small>
    </div>

    <div class="card-body">

        <?php if (!empty($act['description'])): ?>
        <p class="text-muted small mb-3"><?= htmlspecialchars($act['description']) ?></p>
        <?php endif; ?>

        <?php if (isset($items_map[$act['id']])): ?>
        <table class="table table-bordered table-sm">
            <thead>
                <tr><th width="40">No</th><th>Poin SOP</th><th width="100">Wajib</th></tr>
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
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <p class="text-muted small">Belum ada poin SOP untuk activity ini.</p>
        <?php endif; ?>

    </div>

</div>

<?php endwhile; ?>

<?php require 'include/footer.php'; ?>
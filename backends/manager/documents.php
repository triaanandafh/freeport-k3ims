<?php

require '../koneksi/auth_manager.php';
require '../koneksi/koneksi.php';

$page_title = "Documents";
$current_page = "documents";

$data = pg_query($conn, "
    SELECT documents.*, u1.fullname AS uploader_name
    FROM documents
    JOIN users u1 ON documents.uploaded_by = u1.id
    ORDER BY
        CASE documents.status
            WHEN 'draft' THEN 0 WHEN 'review' THEN 1 WHEN 'approved' THEN 2 ELSE 3
        END,
        documents.title ASC
");

require 'include/header.php';
?>

<h1 class="h3 mb-4 text-gray-800">Documents</h1>

<p class="text-muted">Tampilan baca saja — perubahan status dokumen dilakukan oleh Administrator.</p>

<div class="card shadow mb-4">
<div class="card-body">
<div class="table-responsive">
<table class="table table-bordered">
<thead>
<tr>
    <th>No</th><th>Title</th><th>Category</th><th>File</th>
    <th>Status</th><th>Effective</th><th>Expiry</th><th>Uploaded By</th>
</tr>
</thead>
<tbody>
<?php
$no = 1;
while ($row = pg_fetch_assoc($data)):

$status_class = [
    'draft' => 'secondary', 'review' => 'warning', 'approved' => 'success', 'obsolete' => 'danger',
][$row['status']] ?? 'secondary';

$is_expiring = $row['expiry_date'] !== null && $row['status'] === 'approved'
    && strtotime($row['expiry_date']) <= strtotime('+30 days')
    && strtotime($row['expiry_date']) >= strtotime(date('Y-m-d'));

$is_expired = $row['expiry_date'] !== null && $row['status'] === 'approved'
    && strtotime($row['expiry_date']) < strtotime(date('Y-m-d'));
?>
<tr>
    <td><?= $no++ ?></td>
    <td><?= htmlspecialchars($row['title']) ?></td>
    <td><?= htmlspecialchars($row['category']) ?></td>
    <td><a target="_blank" href="../uploads/<?= htmlspecialchars($row['file_url']) ?>">View File</a></td>
    <td><span class="badge badge-<?= $status_class ?>"><?= strtoupper($row['status']) ?></span></td>
    <td><?= $row['effective_date'] ?? '-' ?></td>
    <td>
        <?= $row['expiry_date'] ?? '-' ?>
        <?php if ($is_expired): ?><br><span class="badge badge-danger">EXPIRED</span>
        <?php elseif ($is_expiring): ?><br><span class="badge badge-warning">SEGERA KADALUARSA</span>
        <?php endif; ?>
    </td>
    <td><?= htmlspecialchars($row['uploader_name']) ?></td>
</tr>
<?php endwhile; ?>
</tbody>
</table>
</div>
</div>
</div>

<?php require 'include/footer.php'; ?>
<?php

require '../koneksi/auth_admin.php';
require '../koneksi/koneksi.php';

$page_title = "Safety Checks";
$current_page = "safety_checks";


/* helper checkbox */
function bool_value($name)
{
    return isset($_POST[$name]) ? 'TRUE' : 'FALSE';
}


/* =========================
   INSERT
========================= */
if (isset($_POST['add'])) {

    $employee_id = $_POST['employee_id'];

    $safety_briefing = bool_value('safety_briefing');
    $body_fit = bool_value('body_fit');

    $safety_helmet = bool_value('safety_helmet');
    $safety_vest = bool_value('safety_vest');
    $safety_boots = bool_value('safety_boots');
    $safety_glasses = bool_value('safety_glasses');

    $equipment_checked = bool_value('equipment_checked');
    $jsa_understood = bool_value('jsa_understood');
    $work_area_safe = bool_value('work_area_safe');

    $is_compliant = bool_value('is_compliant');

    pg_query($conn, "

        INSERT INTO employee_safety_checks
        (
            id,
            employee_id,

            safety_briefing,
            body_fit,

            safety_helmet,
            safety_vest,
            safety_boots,
            safety_glasses,

            equipment_checked,
            jsa_understood,
            work_area_safe,

            is_compliant
        )

        VALUES
        (
            gen_random_uuid(),

            '$employee_id',

            $safety_briefing,
            $body_fit,

            $safety_helmet,
            $safety_vest,
            $safety_boots,
            $safety_glasses,

            $equipment_checked,
            $jsa_understood,
            $work_area_safe,

            $is_compliant
        )
    ");

    header("Location: safety_checks.php");
    exit;
}


/* =========================
   DELETE
========================= */
if (isset($_GET['delete'])) {

    $id = $_GET['delete'];

    pg_query($conn,"
        DELETE FROM employee_safety_checks
        WHERE id='$id'
    ");

    header("Location: safety_checks.php");
    exit;
}


/* ambil data */
$data = pg_query($conn, "

    SELECT employee_safety_checks.*, employees.fullname

    FROM employee_safety_checks

    JOIN employees
    ON employee_safety_checks.employee_id = employees.id

    ORDER BY checked_at DESC

");


/* dropdown employee */
$employees = pg_query($conn,"
    SELECT * FROM employees
    ORDER BY fullname
");

require 'include/header.php';

?>


<h1 class="h3 mb-4 text-gray-800">
    Employee Safety Checks
</h1>


<button
class="btn btn-primary mb-3"
data-toggle="modal"
data-target="#addModal">

Add Safety Check

</button>



<div class="card shadow mb-4">

<div class="card-body">

<div class="table-responsive">

<table class="table table-bordered">

<thead>

<tr>
    <th>Employee</th>
    <th>Helmet</th>
    <th>Vest</th>
    <th>Boots</th>
    <th>Glasses</th>
    <th>Briefing</th>
    <th>JSA</th>
    <th>Status</th>
    <th>Action</th>
</tr>

</thead>

<tbody>

<?php while($row = pg_fetch_assoc($data)): ?>

<tr>

<td><?= $row['fullname'] ?></td>

<td><?= $row['safety_helmet'] == 't' ? '✓' : 'X' ?></td>

<td><?= $row['safety_vest'] == 't' ? '✓' : 'X' ?></td>

<td><?= $row['safety_boots'] == 't' ? '✓' : 'X' ?></td>

<td><?= $row['safety_glasses'] == 't' ? '✓' : 'X' ?></td>

<td><?= $row['safety_briefing'] == 't' ? '✓' : 'X' ?></td>

<td><?= $row['jsa_understood'] == 't' ? '✓' : 'X' ?></td>

<td>

<?php if($row['is_compliant'] == 't'): ?>

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
href="safety_checks.php?delete=<?= $row['id'] ?>"
onclick="return confirm('Delete?')"
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

<h5>Safety Inspection</h5>

</div>


<div class="modal-body">


<select
name="employee_id"
class="form-control mb-3"
required>

<option value="">Choose Employee</option>

<?php while($emp = pg_fetch_assoc($employees)): ?>

<option value="<?= $emp['id'] ?>">
<?= $emp['fullname'] ?>
</option>

<?php endwhile; ?>

</select>



<div class="form-check">
<input type="checkbox" name="safety_briefing">
 Safety Briefing
</div>

<div class="form-check">
<input type="checkbox" name="body_fit">
 Body Fit
</div>

<div class="form-check">
<input type="checkbox" name="safety_helmet">
 Safety Helmet
</div>

<div class="form-check">
<input type="checkbox" name="safety_vest">
 Safety Vest
</div>

<div class="form-check">
<input type="checkbox" name="safety_boots">
 Safety Boots
</div>

<div class="form-check">
<input type="checkbox" name="safety_glasses">
 Safety Glasses
</div>

<div class="form-check">
<input type="checkbox" name="equipment_checked">
 Equipment Checked
</div>

<div class="form-check">
<input type="checkbox" name="jsa_understood">
 Understand JSA
</div>

<div class="form-check">
<input type="checkbox" name="work_area_safe">
 Safe Work Area
</div>


<hr>


<div class="form-check">

<input type="checkbox" name="is_compliant">

 Final Compliance Passed

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
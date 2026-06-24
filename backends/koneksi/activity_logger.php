<?php

function log_activity($conn, $user_id, $action, $module, $description)
{
    $user_id_sql = $user_id ? "'" . pg_escape_string($conn, $user_id) . "'" : 'NULL';
    $action = pg_escape_string($conn, $action);
    $module = pg_escape_string($conn, $module);
    $description = pg_escape_string($conn, $description);

    pg_query($conn, "
        INSERT INTO activity_logs (id, user_id, action, module, description)
        VALUES (gen_random_uuid(), $user_id_sql, '$action', '$module', '$description')
    ");
}
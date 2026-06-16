<?php

$conn = pg_connect("
    host=aws-1-ap-northeast-2.pooler.supabase.com
    port=5432
    dbname=postgres
    user=postgres.nxqenmmcjiytaedzxviw
    password=Simulasi.K3
");

if (!$conn) {
    die("Koneksi database gagal");
}
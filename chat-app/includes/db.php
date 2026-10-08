<?php
// الاتصال بقاعدة البيانات
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if (!$conn) { die("فشل الاتصال: " . mysqli_connect_error()); }

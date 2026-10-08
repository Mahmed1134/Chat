<?php
// ============================================================
// التهيئة: الأخطاء + OpenSSL + السيشن + قاعدة البيانات
// ============================================================
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../config/config.php';

// حل مشكلة OpenSSL Cnf
$OPENSSL_CNF_STATUS = 'لم تتم المحاولة';
$opensslCnfPath = null;
$opensslCnfCandidates = [dirname(__DIR__), sys_get_temp_dir(), '/data/local/tmp', '/sdcard'];
foreach ($opensslCnfCandidates as $dir) {
    if (!$dir || !is_dir($dir) || !is_writable($dir)) continue;
    $path = rtrim($dir, '/') . '/openssl_generated.cnf';
    if (file_exists($path)) { $opensslCnfPath = $path; break; }
    if (@file_put_contents($path, "openssl_conf = openssl_init\n[openssl_init]\n") !== false) {
        $opensslCnfPath = $path; break;
    }
}
if ($opensslCnfPath) {
    putenv('OPENSSL_CONF=' . $opensslCnfPath);
    $OPENSSL_CNF_STATUS = 'تم استخدام: ' . $opensslCnfPath;
} else {
    $triedList = implode(', ', $opensslCnfCandidates);
    $OPENSSL_CNF_STATUS = 'فشل: كل المجلدات دي مش قابلة للكتابة أو غير موجودة (' . $triedList . ')';
}

session_start();

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/schema.php';
require_once __DIR__ . '/webpush.php';

<?php
// ============================================================
// الإعدادات (قاعدة البيانات + Web Push)
// ============================================================

define('APP_ENTRY', 'index.php');
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('UPLOAD_URL', 'uploads/');

// قاعدة البيانات
define('DB_HOST', 'sql106.infinityfree.com');
define('DB_USER', 'if0_42670775');
define('DB_PASS', '2050TM2050');
define('DB_NAME', 'if0_42670775_if0_42670775_ooo');

// VAPID
define('VAPID_PUBLIC_KEY', 'BJrqc8cSlLwM7KN9eOXbfyC4E-GQg3ghebmUfs9xho0o34UlD64MBxD2hqvstndJbT40eMxnjGUCCgf3XJuUDEk');
define('VAPID_PRIVATE_KEY', '8E-uN6InX43jInJgKAaf1ZlkToMBxpVs18oUG0Aoglw');
define('VAPID_SUBJECT', 'mailto:mhmdalastwrt809@gmail.com');

// Push Relay
define('PUSH_RELAY_URL', 'https://flat-dawn-7ac6.mhmdalastwrt809.workers.dev');
define('PUSH_RELAY_SECRET', '4cda8d76ba13c3a75f64655e0efd66a3cb830f77040de445');

// Google Sign-In (Client ID بس - من Google Cloud Console > Credentials > OAuth client ID (Web))
// لازم تضيف رابط موقعك في Authorized JavaScript origins (مثال: https://yourdomain.com)
define('GOOGLE_CLIENT_ID', '');

<?php
// ============================================================
// نقطة الدخول الوحيدة للتطبيق
// ============================================================
// ---------- السيرفس ووركر (?sw=1) ----------
if (isset($_GET['sw'])) {
    header('Content-Type: application/javascript');
    header('Service-Worker-Allowed: /');
    readfile(__DIR__ . '/sw.js');
    exit;
}

require_once __DIR__ . '/includes/bootstrap.php';   // إعدادات + سيشن + DB + جداول + push
require_once __DIR__ . '/includes/auth.php';        // خروج / تسجيل / دخول  (بيعرّف $user_name و $action)

// ---------- API ----------
$publicActions = ['vapid_public_key', 'diagnostics', 'push_test', 'save_subscription'];
if ($action && ($user_name || in_array($action, $publicActions))) {
    require __DIR__ . '/api/public.php';
    if ($user_name) {
        require __DIR__ . '/api/chats.php';
        require __DIR__ . '/api/messages.php';
    }
}

// ---------- الصفحة ----------
require __DIR__ . '/views/layout.php';

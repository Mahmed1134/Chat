<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>الدردشة</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
<style>
<?php readfile(dirname(__DIR__) . '/assets/css/style.css'); ?>
</style>
</head>
<body>

<?php
if (!$user_name) {
    require __DIR__ . '/auth.php';
} else {
    require __DIR__ . '/chat.php';
}

// يدمج ملفات الـ JS داخل الصفحة (نفس سلوك الملف الأصلي)
function inline_js($files) {
    echo "<script>\n";
    foreach ($files as $f) {
        readfile(dirname(__DIR__) . '/assets/js/' . $f . '.js');
        echo "\n";
    }
    echo "</script>\n";
}
?>

<script>window.APP = { myName: "<?= htmlspecialchars($user_name ?? '') ?>", googleClientId: <?= json_encode(GOOGLE_CLIENT_ID) ?> };</script>
<?php
if (!$user_name) {
    inline_js(['core', 'push', 'auth']);
} else {
    inline_js(['core', 'push', 'chat-list', 'chat-room', 'messages', 'init']);
}
?>

</body>
</html>

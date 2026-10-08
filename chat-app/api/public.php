<?php
// أكشنات عامة (من غير تسجيل دخول): VAPID / الاشتراك / اختبار الإشعار / التشخيص

if ($action == 'vapid_public_key') {
    echo $VAPID_PUBLIC_KEY ?: ''; exit;
}

if ($action == 'save_subscription') {
    $data = json_decode(file_get_contents('php://input'), true);
    $targetUser = $user_name ? $user_name : 'guest';
    if (isset($data['endpoint'], $data['keys']['p256dh'], $data['keys']['auth'])) {
        $endpoint = mysqli_real_escape_string($conn, $data['endpoint']);
        $p256dh = mysqli_real_escape_string($conn, $data['keys']['p256dh']);
        $auth = mysqli_real_escape_string($conn, $data['keys']['auth']);
        mysqli_query($conn, "INSERT INTO push_subscriptions (user_name, endpoint, p256dh, auth_token)
                              VALUES ('$targetUser', '$endpoint', '$p256dh', '$auth')
                              ON DUPLICATE KEY UPDATE user_name = '$targetUser', p256dh = '$p256dh', auth_token = '$auth'");
    }
    echo "ok"; exit;
}

if ($action == 'push_test') {
    $targetUser = $user_name ? $user_name : 'guest';
    $safeUser = mysqli_real_escape_string($conn, $targetUser);
    $result = mysqli_query($conn, "SELECT * FROM push_subscriptions WHERE user_name = '$safeUser'");
    $results = [];
    $count = 0;
    while ($s = mysqli_fetch_assoc($result)) {
        $count++;
        $r = webpush_send($conn, $s['endpoint'], $s['p256dh'], $s['auth_token'], [
            'title' => 'إشعار تجريبي',
            'body' => 'لو شفت الرسالة دي كإشعار برا المتصفح، يبقى كل حاجة شغالة تمام.',
            'tag' => 'test',
            'url' => '/',
        ], $VAPID_PUBLIC_KEY, $VAPID_PRIVATE_PEM);
        $results[] = $r;
    }
    header('Content-Type: application/json');
    echo json_encode(['subscriptions_found' => $count, 'results' => $results]); exit;
}

if ($action == 'diagnostics') {
    header('Content-Type: application/json');
    echo json_encode([
        'php_version' => PHP_VERSION,
        'has_curl' => function_exists('curl_init'),
        'is_https' => (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (($_SERVER['SERVER_PORT'] ?? '') == 443),
        'vapid_ready' => !empty($VAPID_PUBLIC_KEY) && !empty($VAPID_PRIVATE_PEM),
    ]); exit;
}


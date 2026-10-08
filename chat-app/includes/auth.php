<?php
// تسجيل الخروج / إنشاء حساب / الدخول

// خروج
if (isset($_GET['logout'])) {
    if (isset($_SESSION['user_name'])) {
        $uName = mysqli_real_escape_string($conn, $_SESSION['user_name']);
        mysqli_query($conn, "UPDATE users SET active_chat_id = NULL WHERE name = '$uName'");
    }
    session_destroy();
    header("Location: " . APP_ENTRY);
    exit();
}

// تسجيل حساب جديد
if (isset($_POST['register'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    
    $check = mysqli_query($conn, "SELECT id FROM users WHERE email='$email' OR name='$name'");
    if (mysqli_num_rows($check) > 0) {
        header("Location: " . APP_ENTRY . "?reg_error=exists"); exit();
    }
    
    mysqli_query($conn, "INSERT INTO users (name, email, password) VALUES ('$name', '$email', '$password')");
    $_SESSION['user_name'] = $name;
    header("Location: " . APP_ENTRY); exit();
}

// دخول
if (isset($_POST['login'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $result = mysqli_query($conn, "SELECT * FROM users WHERE email='$email' AND password='$password'");
    $user = mysqli_fetch_assoc($result);
    if ($user) {
        $_SESSION['user_name'] = $user['name'];
        header("Location: " . APP_ENTRY); exit();
    } else {
        header("Location: " . APP_ENTRY . "?login_error=1"); exit();
    }
}

// ---------- دخول / تسجيل بجوجل (Client ID بس) ----------
function google_get($url, $bearer = null) {
    $ch = curl_init($url);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 15, CURLOPT_SSL_VERIFYPEER => true]);
    if ($bearer) curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $bearer]);
    $res = curl_exec($ch);
    curl_close($ch);
    return $res ? json_decode($res, true) : null;
}

if (isset($_POST['google_token'])) {
    header('Content-Type: application/json; charset=utf-8');
    $fail = function ($msg) { echo json_encode(['ok' => false, 'error' => $msg], JSON_UNESCAPED_UNICODE); exit(); };

    if (!GOOGLE_CLIENT_ID) $fail('الدخول بجوجل لسه مش مفعّل: ضيف GOOGLE_CLIENT_ID في config/config.php');
    $token = (string)$_POST['google_token'];

    // 1) نتأكد إن التوكن طالع لتطبيقنا إحنا (aud) وإن الإيميل متأكَّد
    $ti = @google_get('https://oauth2.googleapis.com/tokeninfo?access_token=' . urlencode($token));
    if (!$ti) $fail('السيرفر معرفش يتواصل مع جوجل (ممكن الاستضافة بتمنع الطلبات الخارجة).');
    $aud = $ti['aud'] ?? ($ti['azp'] ?? '');
    $verified = ($ti['email_verified'] ?? '') === 'true' || ($ti['email_verified'] ?? false) === true;
    if ($aud !== GOOGLE_CLIENT_ID || empty($ti['email']) || !$verified) $fail('تعذّر التحقق من حساب جوجل.');

    // 2) الاسم من userinfo
    $ui = @google_get('https://www.googleapis.com/oauth2/v3/userinfo', $token);
    $gEmail = mysqli_real_escape_string($conn, $ti['email']);
    $row = mysqli_fetch_assoc(mysqli_query($conn, "SELECT name FROM users WHERE email='$gEmail'"));

    if ($row) {
        $_SESSION['user_name'] = $row['name'];
    } else {
        $base = trim($ui['name'] ?? '') ?: explode('@', $ti['email'])[0];
        $gName = $base; $i = 1;
        while (true) {
            $n = mysqli_real_escape_string($conn, $gName);
            if (mysqli_num_rows(mysqli_query($conn, "SELECT id FROM users WHERE name='$n'")) == 0) break;
            $i++; $gName = $base . ' ' . $i;
        }
        $n = mysqli_real_escape_string($conn, $gName);
        $rand = mysqli_real_escape_string($conn, bin2hex(random_bytes(16)));
        mysqli_query($conn, "INSERT INTO users (name, email, password) VALUES ('$n', '$gEmail', '$rand')");
        $_SESSION['user_name'] = $gName;
    }
    echo json_encode(['ok' => true]); exit();
}

$user_name = $_SESSION['user_name'] ?? null;
$action = $_GET['action'] ?? null;

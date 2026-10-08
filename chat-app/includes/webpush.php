<?php
// ============================================================
// أدوات Web Push
// ============================================================

function b64url_encode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}
function b64url_decode($data) {
    $pad = strlen($data) % 4;
    if ($pad) $data .= str_repeat('=', 4 - $pad);
    return base64_decode(strtr($data, '-_', '+/'));
}
function pad32($s) { return str_pad($s, 32, "\x00", STR_PAD_LEFT); }
function asn1_len($n) {
    if ($n < 0x80) return chr($n);
    $bytes = ltrim(pack('N', $n), "\x00");
    return chr(0x80 | strlen($bytes)) . $bytes;
}
function ec_private_pem_from_raw($d, $x, $y) {
    $point = "\x04" . $x . $y;
    $version = "\x02\x01\x01";
    $privKeyOctet = "\x04" . asn1_len(strlen($d)) . $d;
    $oidPrime256v1 = "\x06\x08\x2A\x86\x48\xCE\x3D\x03\x01\x07";
    $paramsTLV = "\xA0" . asn1_len(strlen($oidPrime256v1)) . $oidPrime256v1;
    $pubBitstring = "\x00" . $point;
    $pubBitstringTLV = "\x03" . asn1_len(strlen($pubBitstring)) . $pubBitstring;
    $pubKeyTLV = "\xA1" . asn1_len(strlen($pubBitstringTLV)) . $pubBitstringTLV;
    $content = $version . $privKeyOctet . $paramsTLV . $pubKeyTLV;
    $outer = "\x30" . asn1_len(strlen($content)) . $content;
    return "-----BEGIN EC PRIVATE KEY-----\n" . chunk_split(base64_encode($outer), 64, "\n") . "-----END EC PRIVATE KEY-----\n";
}

// مفاتيح VAPID (القيم نفسها في config/config.php)
$VAPID_GEN_ERROR = null;
$VAPID_PUBLIC_KEY = VAPID_PUBLIC_KEY;
$vapidPublicRaw  = b64url_decode($VAPID_PUBLIC_KEY);
$vapidPrivateRaw = b64url_decode(VAPID_PRIVATE_KEY);

if (strlen($vapidPublicRaw) !== 65 || $vapidPublicRaw[0] !== "\x04") {
    $VAPID_GEN_ERROR = 'صيغة المفتاح العام (VAPID_PUBLIC_KEY) غلط';
    $VAPID_PRIVATE_PEM = null;
} else {
    $vapid_x = pad32(substr($vapidPublicRaw, 1, 32));
    $vapid_y = pad32(substr($vapidPublicRaw, 33, 32));
    $vapid_d = pad32($vapidPrivateRaw);
    $VAPID_PRIVATE_PEM = ec_private_pem_from_raw($vapid_d, $vapid_x, $vapid_y);
}

function webpush_send($conn, $endpoint, $p256dhB64, $authB64, $payloadArray, $vapidPublicKeyB64, $vapidPrivatePem) {
    if (!function_exists('curl_init')) {
        return ['ok' => false, 'status' => 0, 'info' => 'إكستنشن curl مش متفعّل على السيرفر'];
    }

    $requestBody = json_encode([
        'subscription' => [
            'endpoint' => $endpoint,
            'p256dh' => $p256dhB64,
            'auth' => $authB64,
        ],
        'payload' => $payloadArray,
    ]);

    $relayHost = parse_url(PUSH_RELAY_URL, PHP_URL_HOST);
    // لو DNS بتاع السيرفر مش بيحل الدومين، نجرب عناوين Cloudflare مباشرة (نفس الـ Host/SNI)
    $attempts = [null, ["$relayHost:443:104.21.21.162", "$relayHost:443:172.67.199.92"]];
    $responseBody = false; $httpStatus = 0; $curlErr = '';
    foreach ($attempts as $resolve) {
        $ch = curl_init(PUSH_RELAY_URL);
        $opts = [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $requestBody,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'X-Api-Key: ' . PUSH_RELAY_SECRET,
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 6,
            CURLOPT_TIMEOUT => 10,
        ];
        if ($resolve) $opts[CURLOPT_RESOLVE] = $resolve;
        curl_setopt_array($ch, $opts);
        $responseBody = curl_exec($ch);
        $httpStatus = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr = curl_error($ch);
        $errNo = curl_errno($ch);
        curl_close($ch);
        if (!$curlErr || $errNo != 6) break; // نكرر بس لو الفشل "Could not resolve host"
    }

    if ($curlErr) {
        return ['ok' => false, 'status' => 0, 'info' => 'فشل الاتصال بالـ Relay: ' . $curlErr];
    }

    $decoded = json_decode((string) $responseBody, true);
    if (!is_array($decoded)) {
        return ['ok' => false, 'status' => $httpStatus, 'info' => 'رد غير متوقع من الـ Relay'];
    }

    $status = $decoded['status'] ?? $httpStatus;
    if ($status == 404 || $status == 410) {
        $safeEndpoint = mysqli_real_escape_string($conn, $endpoint);
        mysqli_query($conn, "DELETE FROM push_subscriptions WHERE endpoint = '$safeEndpoint'");
    }

    return [
        'ok' => (bool) ($decoded['ok'] ?? false),
        'status' => $status,
        'info' => $decoded['info'] ?? 'مفيش تفاصيل',
    ];
}

function logPush($conn, $user, $status, $info) {
    $u = mysqli_real_escape_string($conn, $user);
    $i = mysqli_real_escape_string($conn, $info);
    mysqli_query($conn, "INSERT INTO push_log (user_name, status, response) VALUES ('$u', '$status', '$i')");
}

function get_user_endpoint($conn, $username) {
    $r = mysqli_fetch_assoc(mysqli_query($conn, "SELECT endpoint FROM push_subscriptions WHERE user_name = '".mysqli_real_escape_string($conn, $username)."' LIMIT 1"));
    return $r ? $r['endpoint'] : '';
}
function get_user_p256dh($conn, $username) {
    $r = mysqli_fetch_assoc(mysqli_query($conn, "SELECT p256dh FROM push_subscriptions WHERE user_name = '".mysqli_real_escape_string($conn, $username)."' LIMIT 1"));
    return $r ? $r['p256dh'] : '';
}
function get_user_auth($conn, $username) {
    $r = mysqli_fetch_assoc(mysqli_query($conn, "SELECT auth_token FROM push_subscriptions WHERE user_name = '".mysqli_real_escape_string($conn, $username)."' LIMIT 1"));
    return $r ? $r['auth_token'] : '';
}

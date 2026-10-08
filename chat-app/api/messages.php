<?php
// أكشنات الرسائل: عرض / إرسال / تعديل / حذف

if ($action == 'get_messages') {
    $chatId = mysqli_real_escape_string($conn, $_GET['chat_id']);
    $result = mysqli_query($conn, "SELECT m.*, r.body as reply_body, r.sender_name as reply_sender, DATE_FORMAT(m.created_at, '%h:%i %p') as msg_time 
                                   FROM chat_messages m 
                                   LEFT JOIN chat_messages r ON m.reply_to_id = r.id 
                                   WHERE m.chat_id = '$chatId' 
                                   ORDER BY m.created_at ASC");
    $messages = [];
    while ($row = mysqli_fetch_assoc($result)) { $messages[] = $row; }
    header('Content-Type: application/json');
    echo json_encode($messages); exit;
}

if ($action == 'send_message') {
    $chatId = mysqli_real_escape_string($conn, $_POST['chat_id']);
    $body = mysqli_real_escape_string($conn, $_POST['body'] ?? '');
    $replyToId = isset($_POST['reply_to_id']) && (int)$_POST['reply_to_id'] > 0 ? (int)$_POST['reply_to_id'] : "NULL";
    
    $filePathDb = null;
    $fileTypeDb = null;
    $fileNameDb = null;

    if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $origName = $_FILES['file']['name'];
        $fileExt = pathinfo($origName, PATHINFO_EXTENSION);
        $safeFileName = time() . '_' . mt_rand(1000, 9999) . '.' . $fileExt;
        $destination = $uploadDir . $safeFileName;
        if (move_uploaded_file($_FILES['file']['tmp_name'], $destination)) {
            $filePathDb = $destination;
            $fileTypeDb = $_FILES['file']['type'];
            $fileNameDb = $origName;
        }
    }

    $filePathEsc = $filePathDb ? "'" . mysqli_real_escape_string($conn, $filePathDb) . "'" : "NULL";
    $fileTypeEsc = $fileTypeDb ? "'" . mysqli_real_escape_string($conn, $fileTypeDb) . "'" : "NULL";
    $fileNameEsc = $fileNameDb ? "'" . mysqli_real_escape_string($conn, $fileNameDb) . "'" : "NULL";
    $bodyEsc = "'" . mysqli_real_escape_string($conn, $body) . "'";

    mysqli_query($conn, "INSERT INTO chat_messages (chat_id, sender_name, body, file_path, file_type, file_name, reply_to_id) VALUES ('$chatId', '$user_name', $bodyEsc, $filePathEsc, $fileTypeEsc, $fileNameEsc, $replyToId)");

    $chatRow = mysqli_fetch_assoc(mysqli_query($conn, "SELECT participants FROM chats WHERE id = '$chatId'"));
    if ($chatRow) {
        $names = array_map('trim', explode(',', $chatRow['participants']));
        $notifBody = $body !== '' ? $body : ($fileNameDb ? 'أرسل ملف: ' . $fileNameDb : 'محتوى جديد');
        if (mb_strlen($notifBody) > 80) {
            $notifBody = mb_substr($notifBody, 0, 80) . '...';
        }

        foreach ($names as $n) {
            if ($n !== $user_name && $n !== '') {
                $recCheck = mysqli_fetch_assoc(mysqli_query($conn, "SELECT active_chat_id FROM users WHERE name = '$n'"));
                $recActiveChat = $recCheck ? (int)$recCheck['active_chat_id'] : 0;

                if ($recActiveChat !== (int)$chatId) {
                    webpush_send($conn, get_user_endpoint($conn, $n), get_user_p256dh($conn, $n), get_user_auth($conn, $n), [
                        'title' => 'رسالة جديدة من ' . $user_name,
                        'body' => $notifBody,
                        'tag' => 'chat-' . $chatId,
                        'url' => '/',
                    ], $VAPID_PUBLIC_KEY, $VAPID_PRIVATE_PEM);
                }
            }
        }
    }
    echo "ok"; exit;
}

if ($action == 'edit_message') {
    $msgId = mysqli_real_escape_string($conn, $_POST['msg_id']);
    $body = mysqli_real_escape_string($conn, $_POST['body']);
    mysqli_query($conn, "UPDATE chat_messages SET body = '$body', is_edited = 1 WHERE id = '$msgId' AND sender_name = '$user_name'");
    echo "ok"; exit;
}

if ($action == 'delete_message') {
    $msgId = mysqli_real_escape_string($conn, $_POST['msg_id']);
    $res = mysqli_query($conn, "SELECT file_path FROM chat_messages WHERE id = '$msgId' AND sender_name = '$user_name'");
    if ($row = mysqli_fetch_assoc($res)) {
        if (!empty($row['file_path']) && file_exists($row['file_path'])) {
            @unlink($row['file_path']);
        }
    }
    mysqli_query($conn, "DELETE FROM chat_messages WHERE id = '$msgId' AND sender_name = '$user_name'");
    echo "ok"; exit;
}

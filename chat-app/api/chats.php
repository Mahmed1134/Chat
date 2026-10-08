<?php
// أكشنات المحادثات: النشاط / غير المقروء / القراءة / البحث / بدء محادثة

if ($action == 'set_active_chat') {
    $activeChatId = (int)($_POST['chat_id'] ?? 0);
    $activeVal = $activeChatId > 0 ? $activeChatId : "NULL";
    mysqli_query($conn, "UPDATE users SET active_chat_id = $activeVal WHERE name = '$user_name'");
    echo "ok"; exit;
}

if ($action == 'unread_summary') {
    $q = "SELECT c.id, c.participants,
                 (SELECT COUNT(*) FROM chat_messages m
                    WHERE m.chat_id = c.id AND m.sender_name != '$user_name'
                      AND m.created_at > IFNULL(r.last_read_at, '1970-01-01 00:00:00')
                 ) AS unread,
                 (SELECT body FROM chat_messages m2 WHERE m2.chat_id = c.id ORDER BY m2.created_at DESC LIMIT 1) AS last_body,
                 (SELECT file_name FROM chat_messages m_f WHERE m_f.chat_id = c.id ORDER BY m_f.created_at DESC LIMIT 1) AS last_file_name,
                 (SELECT sender_name FROM chat_messages m3 WHERE m3.chat_id = c.id ORDER BY m3.created_at DESC LIMIT 1) AS last_sender,
                 (SELECT created_at FROM chat_messages m4 WHERE m4.chat_id = c.id ORDER BY m4.created_at DESC LIMIT 1) AS last_time
          FROM chats c
          LEFT JOIN chat_reads r ON r.chat_id = c.id AND r.user_name = '$user_name'
          WHERE c.participants LIKE '%$user_name%'
          ORDER BY c.id DESC";
    $result = mysqli_query($conn, $q);
    $total = 0; $chats = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $names = array_map('trim', explode(',', $row['participants']));
        $other = implode(', ', array_filter($names, function($n) use ($user_name) { return $n !== $user_name; }));
        $unread = (int)$row['unread'];
        $total += $unread;
        
        $formattedTime = '';
        if (!empty($row['last_time'])) {
            $formattedTime = date('h:i A', strtotime($row['last_time']));
        }

        $preview = $row['last_body'];
        if (empty($preview) && !empty($row['last_file_name'])) {
            $preview = 'ملف: ' . $row['last_file_name'];
        } elseif (!empty($preview) && !empty($row['last_file_name'])) {
            $preview = $preview . ' (مرفق)';
        }
        
        $chats[] = [
            'id' => $row['id'], 
            'other' => $other, 
            'unread' => $unread, 
            'last_body' => $preview, 
            'last_sender' => $row['last_sender'],
            'last_time' => $formattedTime
        ];
    }
    header('Content-Type: application/json');
    echo json_encode(['total' => $total, 'chats' => $chats]); exit;
}

if ($action == 'mark_read') {
    $chatId = $_POST['chat_id'];
    mysqli_query($conn, "INSERT INTO chat_reads (chat_id, user_name, last_read_at) VALUES ('$chatId', '$user_name', NOW())
                          ON DUPLICATE KEY UPDATE last_read_at = NOW()");
    echo "ok"; exit;
}

if ($action == 'search_users') {
    $q = mysqli_real_escape_string($conn, $_GET['q'] ?? '');
    $result = mysqli_query($conn, "SELECT name FROM users WHERE name LIKE '%$q%' AND name != '$user_name' LIMIT 10");
    $names = [];
    while ($row = mysqli_fetch_assoc($result)) { $names[] = $row['name']; }
    header('Content-Type: application/json');
    echo json_encode($names); exit;
}

if ($action == 'start_chat') {
    $target = mysqli_real_escape_string($conn, $_POST['target_name']);
    $result = mysqli_query($conn, "SELECT id FROM chats WHERE participants LIKE '%$user_name%' AND participants LIKE '%$target%' LIMIT 1");
    $existing = mysqli_fetch_assoc($result);
    if ($existing) {
        echo $existing['id'];
    } else {
        mysqli_query($conn, "INSERT INTO chats (participants) VALUES ('$user_name, $target')");
        echo mysqli_insert_id($conn);
    }
    exit;
}


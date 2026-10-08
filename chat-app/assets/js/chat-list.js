// قائمة المحادثات والبحث
function setActiveChat(id) {
    fetch('?action=set_active_chat', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'chat_id=' + id
    });
}

function pollUnread() {
    fetch('?action=unread_summary').then(r => r.json()).then(data => {
        updateBadge(data.total);
        const isHidden = document.hidden || !document.hasFocus();
        data.chats.forEach(c => {
            const prev = prevUnreadByChat[c.id] || 0;
            if (c.unread > prev && c.id !== chatId && (isHidden || document.getElementById('chatPopup').classList.contains('open'))) {
                const preview = (c.last_body || '').slice(0, 80);
                const n = new Notification('رسالة جديدة من ' + (c.last_sender || c.other), { body: preview, tag: 'chat-' + c.id });
                n.onclick = () => { window.focus(); n.close(); };
            }
            prevUnreadByChat[c.id] = c.unread;
        });
        if (document.getElementById('chatPopup').classList.contains('open') && !chatId) {
            renderChatsList(data.chats);
        }
    });
}

function updateBadge(total) {
    if (total > 0) {
        document.title = '(' + total + ') ' + pageTitle;
    } else {
        document.title = pageTitle;
    }
}

function renderChatsList(chats) {
    const box = document.getElementById('chatsList');
    box.innerHTML = '';
    chats.forEach(c => {
        const div = document.createElement('div');
        div.className = 'chatRow';
        const badgeHtml = c.unread > 0 ? '<span class="rowBadge">' + (c.unread > 99 ? '99+' : c.unread) + '</span>' : '';
        const preview = c.last_body ? c.last_body.slice(0, 35) : 'لا يوجد رسائل بعد';
        const timeStr = c.last_time || '';
        const initial = getInitial(c.other);

        div.innerHTML = `
            <div class="chatAvatar" style="background:${avColor(c.other)}">${initial}</div>
            <div class="chatInfo">
                <div class="chatHeaderRow">
                    <span class="chatName">${escapeHtml(c.other)}</span>
                    <span class="chatTime">${timeStr}</span>
                </div>
                <div class="chatSubRow">
                    <span class="chatPreview">${escapeHtml(preview)}</span>
                    ${badgeHtml}
                </div>
            </div>
        `;
        div.onclick = () => openChat(c.id, c.other);
        box.appendChild(div);
    });
}

function loadChats() {
    if (chatId) return;
    fetch('?action=unread_summary').then(r => r.json()).then(data => renderChatsList(data.chats));
}

function searchUsers(q) {
    const box = document.getElementById('searchResults');
    if (!q.trim()) { box.innerHTML = ''; return; }
    fetch('?action=search_users&q=' + encodeURIComponent(q)).then(r => r.json()).then(names => {
        box.innerHTML = '';
        names.forEach(name => {
            const div = document.createElement('div');
            div.innerHTML = `<div class="chatAvatar" style="width:32px;height:32px;font-size:14px;margin-left:8px;background:${avColor(name)}">${getInitial(name)}</div> <span>${escapeHtml(name)}</span>`;
            div.onclick = () => startChat(name);
            box.appendChild(div);
        });
    });
}

function startChat(name) {
    document.getElementById('searchResults').innerHTML = '';
    document.getElementById('searchInput').value = '';
    fetch('?action=start_chat', {
        method: 'POST', headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'target_name=' + encodeURIComponent(name)
    }).then(r => r.text()).then(id => openChat(id, name));
}

function markRead(id) {
    fetch('?action=mark_read', {
        method: 'POST', headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'chat_id=' + id
    }).then(() => { prevUnreadByChat[id] = 0; });
}


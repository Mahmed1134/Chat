// عرض وإرسال وتعديل وحذف الرسائل
function loadMessages(isInitial = false) {
    fetch('?action=get_messages&chat_id=' + chatId).then(r => r.json()).then(messages => {
        const box = document.getElementById('msgList');
        const wasAtBottom = !userScrolledUp;
        box.innerHTML = '';

        messages.forEach(m => {
            const isMine = (m.sender_name === myName);
            const row = document.createElement('div');
            row.className = 'msg-wrapper ' + (isMine ? 'msg-me' : 'msg-other');
            row.id = 'msg-wrap-' + m.id;
            
            // سحب متعمد يمينًا للرد (يتحقق من أن الحركة أفقية وليست تمرير رأسي)
            let touchStartX = 0, touchStartY = 0;
            let isSwipingHorizontal = false;

            row.addEventListener('touchstart', e => {
                touchStartX = e.touches[0].clientX;
                touchStartY = e.touches[0].clientY;
                isSwipingHorizontal = false;
            }, {passive: true});

            row.addEventListener('touchmove', e => {
                let currentX = e.touches[0].clientX;
                let currentY = e.touches[0].clientY;
                let diffX = currentX - touchStartX;
                let diffY = currentY - touchStartY;

                // لو الحركة الرأسية أكبر من الأفقية، تجاهل السحب للرد (عشان السكرول الطبيعي)
                if (!isSwipingHorizontal && Math.abs(diffY) > Math.abs(diffX)) {
                    return;
                }

                if (diffX > 10) {
                    isSwipingHorizontal = true;
                }

                if (isSwipingHorizontal && diffX > 0 && diffX < 90) {
                    row.style.transform = `translateX(${diffX}px)`;
                }
            }, {passive: true});

            row.addEventListener('touchend', e => {
                if (isSwipingHorizontal) {
                    let currentX = e.changedTouches[0].clientX;
                    let diffX = currentX - touchStartX;
                    row.style.transform = '';
                    if (diffX > 60) {
                        setReplyTo(m.id, m.sender_name, m.body);
                    }
                }
                touchStartX = 0; touchStartY = 0;
                isSwipingHorizontal = false;
            });

            let actionsHtml = '';
            if (isMine) {
                actionsHtml = `
                    <div class="msg-actions">
                        <button class="msg-action-btn" onclick="setReplyTo(${m.id}, '${escapeHtml(m.sender_name)}', \`${escapeHtml(m.body).replace(/`/g, '\\`')}\`)" title="رد">رد</button>
                        <button class="msg-action-btn" onclick="editMsgPrompt(${m.id}, \`${escapeHtml(m.body).replace(/`/g, '\\`')}\`)" title="تعديل">تعديل</button>
                        <button class="msg-action-btn" onclick="deleteMsg(${m.id})" title="حذف">حذف</button>
                    </div>
                `;
            } else {
                actionsHtml = `
                    <div class="msg-actions">
                        <button class="msg-action-btn" onclick="setReplyTo(${m.id}, '${escapeHtml(m.sender_name)}', \`${escapeHtml(m.body).replace(/`/g, '\\`')}\`)" title="رد">رد</button>
                    </div>
                `;
            }

            let editedTag = (parseInt(m.is_edited) === 1) ? '<span style="font-size:9px; color:var(--muted); margin-left:4px;">(معدلة)</span>' : '';

            let replyQuoteHtml = '';
            if (m.reply_to_id && m.reply_body !== null) {
                replyQuoteHtml = `
                    <div class="quoted-box" onclick="event.stopPropagation(); scrollToMessage(${m.reply_to_id})">
                        <div class="q-name">${escapeHtml(m.reply_sender)}</div>
                        <div style="white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:240px;">${escapeHtml(m.reply_body)}</div>
                    </div>
                `;
            }

            let fileHtml = '';
            if (m.file_path) {
                const fType = m.file_type || '';
                const fName = m.file_name || 'ملف مرفق';
                if (fType.startsWith('image/')) {
                    fileHtml = `<div style="margin-top:6px;"><img src="${escapeHtml(m.file_path)}" style="max-width:100%; max-height:250px; border-radius:6px; cursor:pointer;" onclick="window.open(this.src)" /></div>`;
                } else if (fType.startsWith('video/')) {
                    fileHtml = `<div style="margin-top:6px;"><video controls src="${escapeHtml(m.file_path)}" style="max-width:100%; max-height:250px; border-radius:6px;"></video></div>`;
                } else {
                    fileHtml = `
                        <div style="background: rgba(43,149,230,0.1); padding: 8px 10px; border-radius: 6px; margin-top: 6px; display: flex; align-items: center; gap: 8px;">
                            <span style="display:flex;color:var(--muted)"><svg class="ico" viewBox="0 0 24 24"><path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8z"/><path d="M14 3v5h5"/></svg></span>
                            <a href="${escapeHtml(m.file_path)}" target="_blank" download="${escapeHtml(fName)}" style="color: var(--blue); text-decoration: none; font-size: 13px; word-break: break-all;">${escapeHtml(fName)}</a>
                        </div>
                    `;
                }
            }

            row.innerHTML = `
                <div class="bubble">
                    ${!isMine ? '<b>' + escapeHtml(m.sender_name) + '</b>' : ''}
                    ${replyQuoteHtml}
                    <span id="msg-body-${m.id}">${escapeHtml(m.body)}</span>
                    ${fileHtml}
                    <span class="msg-meta">
                        ${editedTag}
                        ${m.msg_time || ''}
                    </span>
                    ${actionsHtml}
                </div>
            `;
            box.appendChild(row);
        });

        if (isInitial || wasAtBottom) {
            box.scrollTop = box.scrollHeight;
        }
    });
}

function sendMsg() {
    const input = document.getElementById('msgInput');
    const fileInput = document.getElementById('fileInput');
    if (!input.value.trim() && fileInput.files.length === 0) return;

    const formData = new FormData();
    formData.append('chat_id', chatId);
    formData.append('body', input.value);
    if (activeReplyId) {
        formData.append('reply_to_id', activeReplyId);
    }
    if (fileInput.files.length > 0) {
        formData.append('file', fileInput.files[0]);
    }

    input.value = '';
    clearSelectedFile();
    clearReply();
    userScrolledUp = false;

    fetch('?action=send_message', {
        method: 'POST',
        body: formData
    }).then(() => { loadMessages(true); });
}

function editMsgPrompt(msgId, currentBody) {
    const newBody = prompt("تعديل الرسالة:", currentBody);
    if (newBody === null || !newBody.trim() || newBody === currentBody) return;

    fetch('?action=edit_message', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'msg_id=' + msgId + '&body=' + encodeURIComponent(newBody)
    }).then(() => { loadMessages(false); });
}

function deleteMsg(msgId) {
    if (!confirm("هل أنت متأكد من حذف هذه الرسالة؟")) return;

    fetch('?action=delete_message', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'msg_id=' + msgId
    }).then(() => { loadMessages(false); });
}


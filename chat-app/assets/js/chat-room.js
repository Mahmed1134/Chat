// غرفة المحادثة (فتح/رجوع/رد/ملفات)
function openChat(id, withName) {
    chatId = id;
    userScrolledUp = false;
    setActiveChat(id);
    document.getElementById('chatWithName').textContent = withName;
    document.getElementById('chatAvatarHeader').textContent = getInitial(withName);
    document.getElementById('chatAvatarHeader').style.background = avColor(withName);
    document.getElementById('listView').style.display = 'none';
    document.getElementById('roomView').style.display = 'flex';
    markRead(id);
    loadMessages(true);
    msgTimer = setInterval(() => { loadMessages(false); markRead(id); }, 2000);
}

function backToList() {
    setActiveChat(0);
    clearInterval(msgTimer);
    chatId = null;
    clearReply();
    clearSelectedFile();
    document.getElementById('roomView').style.display = 'none';
    document.getElementById('listView').style.display = 'flex';
    loadChats();
}

function handleFileSelect() {
    const fileInput = document.getElementById('fileInput');
    const container = document.getElementById('filePreviewContainer');
    const nameSpan = document.getElementById('filePreviewName');
    if (fileInput.files.length > 0) {
        nameSpan.textContent = 'ملف: ' + fileInput.files[0].name;
        container.style.display = 'flex';
    } else {
        container.style.display = 'none';
    }
}

function clearSelectedFile() {
    document.getElementById('fileInput').value = '';
    document.getElementById('filePreviewContainer').style.display = 'none';
}

function setReplyTo(msgId, senderName, bodyText) {
    activeReplyId = msgId;
    document.getElementById('replyPreviewSender').textContent = senderName;
    document.getElementById('replyPreviewText').textContent = bodyText || 'ملف مرفق';
    document.getElementById('replyPreviewContainer').style.display = 'flex';
    document.getElementById('msgInput').focus();
}

function clearReply() {
    activeReplyId = null;
    document.getElementById('replyPreviewContainer').style.display = 'none';
}

function scrollToMessage(targetId) {
    const el = document.getElementById('msg-wrap-' + targetId);
    if (el) {
        el.scrollIntoView({ behavior: 'smooth', block: 'center' });
        // الانتظار حتى ينتهي التمرير السلس تماماً ثم إضاءة الرسالة وإزالتها بعد 2.5 ثانية
        setTimeout(() => {
            const bubble = el.querySelector('.bubble');
            if (bubble) {
                bubble.classList.add('highlight-yellow');
                setTimeout(() => {
                    bubble.classList.remove('highlight-yellow');
                }, 2500);
            }
        }, 350);
    }
}


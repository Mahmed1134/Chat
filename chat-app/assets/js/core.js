// الحالة العامة والأدوات المشتركة
const myName = (window.APP && window.APP.myName) || '';
const pageTitle = document.title;
let chatId = null;
let msgTimer = null, listTimer = null;
let prevUnreadByChat = {};
let activeReplyId = null;
let userScrolledUp = false;

const msgListEl = document.getElementById('msgList');
if (msgListEl) {
    msgListEl.addEventListener('scroll', () => {
        const threshold = 60;
        userScrolledUp = (msgListEl.scrollHeight - msgListEl.scrollTop - msgListEl.clientHeight) > threshold;
    });
}

document.addEventListener('visibilitychange', function() {
    if (document.hidden && chatId !== null) {
        backToList();
    }
});

function toggleInfoDropdown() {
    const dropdown = document.getElementById('infoDropdownContent');
    if (dropdown) dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
}

window.addEventListener('click', function(event) {
    const container = document.getElementById('infoMenuContainer');
    if (container && !container.contains(event.target)) {
        const dropdown = document.getElementById('infoDropdownContent');
        if (dropdown) dropdown.style.display = 'none';
    }
});

const AV_COLORS = ['#2b95e6','#4aa8ee','#1f84d3','#5bb4f0','#3a9ae0','#2f8fd8','#56a9e8','#4399e0'];
function avColor(name) {
    let h = 0; for (const ch of (name || '?')) h = (h * 31 + ch.charCodeAt(0)) >>> 0;
    return AV_COLORS[h % AV_COLORS.length];
}
function getInitial(name) {
    return name ? name.trim().charAt(0).toUpperCase() : '?';
}


function escapeHtml(str) {
    if (!str) return '';
    return str.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
}


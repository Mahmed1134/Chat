// تشغيل التطبيق بعد تسجيل الدخول
// الشات بيظهر علطول: نحمّل المحادثات فور فتح الصفحة
loadChats();
listTimer = setInterval(loadChats, 4000);

// طلب إذن الإشعارات عند أول لمسة من المستخدم (المتصفحات بتطلب تفاعل)
document.addEventListener('click', async function askNotif() {
    document.removeEventListener('click', askNotif);
    if ('Notification' in window && Notification.permission === 'default') {
        const perm = await Notification.requestPermission();
        if (perm === 'granted') subscribeToPush();
    }
});

pollUnread();
setInterval(pollUnread, 5000);

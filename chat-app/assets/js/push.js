// الإشعارات والتشخيص
function testPush() {
    fetch('?action=push_test').then(r => r.json()).then(data => {
        if (data.subscriptions_found === 0) {
            alert('مفيش اشتراك Push مسجل ليك.\n1) تأكد من تشغيل الموقع عبر HTTPS.\n2) وافق على إذن الإشعارات.');
            return;
        }
        const lines = data.results.map((r, i) => 'جهاز ' + (i+1) + ': ' + (r.ok ? 'نجح' : 'فشل') + '\n' + r.info);
        alert(lines.join('\n\n'));
    }).catch(err => alert('خطأ في الاتصال: ' + err));
}

function showDiagnostics() {
    fetch('?action=diagnostics').then(r => r.json()).then(data => {
        let msg = 'PHP: ' + data.php_version + '\n';
        msg += 'curl متاح: ' + (data.has_curl ? 'نعم' : 'لا') + '\n';
        msg += 'HTTPS: ' + (data.is_https ? 'نعم' : 'لا') + '\n';
        msg += 'مفاتيح VAPID جاهزة: ' + (data.vapid_ready ? 'نعم' : 'لا') + '\n';
        alert(msg);
    }).catch(err => alert('خطأ في الاتصال: ' + err));
}

function urlBase64ToUint8Array(base64String) {
    const padding = '='.repeat((4 - base64String.length % 4) % 4);
    const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
    const rawData = window.atob(base64);
    const outputArray = new Uint8Array(rawData.length);
    for (let i = 0; i < rawData.length; ++i) outputArray[i] = rawData.charCodeAt(i);
    return outputArray;
}

async function subscribeToPush() {
    if (!('serviceWorker' in navigator) || !('PushManager' in window)) return;
    try {
        const reg = await navigator.serviceWorker.register('index.php?sw=1');
        const publicKey = await (await fetch('?action=vapid_public_key')).text();
        if (!publicKey) return;
        let sub = await reg.pushManager.getSubscription();
        if (!sub) {
            sub = await reg.pushManager.subscribe({
                userVisibleOnly: true,
                applicationServerKey: urlBase64ToUint8Array(publicKey),
            });
        }
        fetch('?action=save_subscription', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(sub),
        });
    } catch (err) {
        console.warn('تعذر تفعيل Push:', err);
    }
}

if ('Notification' in window && Notification.permission === 'granted') {
    subscribeToPush();
}


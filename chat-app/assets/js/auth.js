// تبديل تبويبات الدخول / التسجيل
function switchAuthTab(type) {
    document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
    document.querySelectorAll('.auth-form').forEach(form => form.classList.remove('active'));
    if(type === 'login') {
        document.querySelectorAll('.tab-btn')[0].classList.add('active');
        document.getElementById('loginForm').classList.add('active');
    } else {
        document.querySelectorAll('.tab-btn')[1].classList.add('active');
        document.getElementById('registerForm').classList.add('active');
    }
}

// المتابعة باستخدام جوجل (Google Identity Services - Client ID بس)
function showGoogleError(msg) {
    const el = document.getElementById('googleError');
    el.textContent = msg;
    el.style.display = 'block';
}

function googleLogin() {
    document.getElementById('googleError').style.display = 'none';
    if (!window.APP.googleClientId) {
        return showGoogleError('الدخول بجوجل لسه مش مفعّل: ضيف GOOGLE_CLIENT_ID في config/config.php');
    }
    if (!window.google || !google.accounts || !google.accounts.oauth2) {
        return showGoogleError('مكتبة جوجل لسه بتتحمّل أو متحجوبة، جرّب تاني بعد ثواني.');
    }
    const client = google.accounts.oauth2.initTokenClient({
        client_id: window.APP.googleClientId,
        scope: 'openid email profile',
        callback: async (resp) => {
            if (!resp || resp.error || !resp.access_token) {
                return showGoogleError('تم إلغاء تسجيل الدخول بجوجل.');
            }
            try {
                const fd = new FormData();
                fd.append('google_token', resp.access_token);
                const r = await fetch('index.php', { method: 'POST', body: fd });
                const d = await r.json();
                if (d.ok) location.reload(); else showGoogleError(d.error || 'تعذّر تسجيل الدخول بجوجل.');
            } catch (e) {
                showGoogleError('حصل خطأ في الاتصال بالسيرفر، حاول تاني.');
            }
        }
    });
    client.requestAccessToken();
}

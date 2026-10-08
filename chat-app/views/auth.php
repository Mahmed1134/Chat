
    <div style="position: fixed; top: 15px; left: 15px; z-index: 1000;">
        <div class="info-menu-container" id="infoMenuContainer">
            <button class="info-main-btn" onclick="toggleInfoDropdown()">معلومات</button>
            <div class="info-dropdown-content" id="infoDropdownContent">
                <button onclick="showDiagnostics()">التشخيص</button>
                <button onclick="testPush()">اختبار الإشعار</button>
            </div>
        </div>
    </div>

    <script src="https://accounts.google.com/gsi/client" async defer></script>

    <div class="auth-wrapper">
        <div class="auth-card">
            <div class="auth-title">أهلاً بيك</div>
            <div class="auth-sub">سجّل دخولك عشان تكمل محادثاتك</div>

            <?php if (isset($_GET['login_error'])): ?>
                <div class="alert-msg alert-danger">البريد الإلكتروني أو كلمة المرور غير صحيحة. يرجى التأكد وإعادة المحاولة.</div>
            <?php endif; ?>

            <?php if (isset($_GET['reg_error'])): ?>
                <div class="alert-msg alert-danger">هذا الاسم أو البريد الإلكتروني مستخدم بالفعل.</div>
            <?php endif; ?>

            <div class="auth-tabs">
                <button type="button" class="tab-btn active" onclick="switchAuthTab('login')">تسجيل الدخول</button>
                <button type="button" class="tab-btn" onclick="switchAuthTab('register')">حساب جديد</button>
            </div>

            <form id="loginForm" class="auth-form active" method="POST">
                <div class="input-group">
                    <input type="email" name="email" placeholder="البريد الإلكتروني" required>
                </div>
                <div class="input-group">
                    <input type="password" name="password" placeholder="كلمة المرور" required>
                </div>
                <button type="submit" name="login" class="btn-submit">دخول</button>
            </form>

            <form id="registerForm" class="auth-form" method="POST">
                <div class="input-group">
                    <input type="text" name="name" placeholder="الاسم الكامل" required>
                </div>
                <div class="input-group">
                    <input type="email" name="email" placeholder="البريد الإلكتروني" required>
                </div>
                <div class="input-group">
                    <input type="password" name="password" placeholder="كلمة المرور" required>
                </div>
                <button type="submit" name="register" class="btn-submit">إنشاء حساب</button>
            </form>

            <div id="googleError" class="alert-msg alert-danger" style="display:none; margin-top:14px;"></div>
            <div class="auth-divider">أو</div>
            <button type="button" class="btn-google" onclick="googleLogin()">
                <svg viewBox="0 0 48 48"><path fill="#EA4335" d="M24 9.5c3.5 0 6.6 1.2 9.1 3.6l6.8-6.8C35.8 2.4 30.3 0 24 0 14.6 0 6.5 5.4 2.6 13.2l7.9 6.1C12.4 13.6 17.7 9.5 24 9.5z"/><path fill="#4285F4" d="M46.1 24.5c0-1.6-.1-3.1-.4-4.5H24v9h12.4c-.5 2.9-2.2 5.3-4.6 6.9l7.1 5.5c4.3-4 6.8-9.9 6.8-16.9z"/><path fill="#FBBC05" d="M10.5 28.7c-.5-1.4-.8-3-.8-4.7s.3-3.2.8-4.7l-7.9-6.1C.9 16.4 0 20.1 0 24s.9 7.6 2.6 10.8l7.9-6.1z"/><path fill="#34A853" d="M24 48c6.300 0 11.6-2.1 15.500-5.700l-7.100-5.500c-2 1.400-4.700 2.200-8.400 2.200-6.300 0-11.600-4.100-13.500-9.800l-7.900 6.100C6.500 42.600 14.600 48 24 48z"/></svg>
                المتابعة باستخدام جوجل
            </button>
        </div>
    </div>


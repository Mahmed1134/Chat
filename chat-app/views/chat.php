    

    <div id="chatPopup" class="open">
        <div id="listView">
            <div class="header">
                <div class="header-title">المحادثات</div>
                <div class="header-user">
                    <b><?= htmlspecialchars($user_name) ?></b>
                    <a href="?logout=1" class="logout-btn">خروج</a>
                </div>
            </div>

            <div class="search-container">
                <div class="search-box">
                    <span style="display:flex"><svg class="ico" viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="M20 20l-3.5-3.5"/></svg></span>
                    <input type="text" id="searchInput" placeholder="بحث أو بدء محادثة جديدة..." oninput="searchUsers(this.value)">
                </div>
            </div>

            <div id="searchResults"></div>
            <div id="chatsList"></div>
        </div>

        <div id="roomView">
            <div class="header">
                <div class="header-title" style="margin: 0; gap: 10px;">
                    <span onclick="backToList()" class="iconbtn" style="color:var(--text)"><svg class="ico" viewBox="0 0 24 24"><path d="M9 6l6 6-6 6"/></svg></span>
                    <div class="chatAvatar" id="chatAvatarHeader" style="width: 32px; height: 32px; font-size: 14px; margin-left: 0;"></div>
                    <span id="chatWithName"></span>
                </div>
            </div>
            
            <div id="msgList"></div>
            
            <div id="filePreviewContainer" style="display:none;">
                <span id="filePreviewName"></span>
                <button type="button" onclick="clearSelectedFile()" style="background:transparent; border:none; color:var(--danger); cursor:pointer; font-weight:bold;">إلغاء</button>
            </div>

            <div id="replyPreviewContainer">
                <div>
                    <span style="font-size: 10px; color: var(--blue); font-weight: bold;">الرد على: <span id="replyPreviewSender"></span></span>
                    <span id="replyPreviewText" style="display:block; color:var(--muted); font-size:12px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:320px;"></span>
                </div>
                <button type="button" onclick="clearReply()" style="background:transparent; border:none; color:var(--danger); cursor:pointer; display:flex;"><svg class="ico" viewBox="0 0 24 24"><path d="M6 6l12 12M18 6L6 18"/></svg></button>
            </div>

            <div id="sendBox">
                <input type="file" id="fileInput" style="display:none" onchange="handleFileSelect()">
                <button type="button" onclick="document.getElementById('fileInput').click()" title="إرفاق ملف أو صورة" class="iconbtn"><svg class="ico" viewBox="0 0 24 24"><path d="M21 11.5l-8.5 8.5a5 5 0 0 1-7-7L14 4.5a3.5 3.5 0 0 1 5 5L10.5 18a2 2 0 0 1-3-3L15 7.5"/></svg></button>
                <input type="text" id="msgInput" placeholder="اكتب رسالة..." onkeypress="if(event.key==='Enter') sendMsg()">
                <button type="button" onclick="sendMsg()" title="إرسال"><svg class="ico" viewBox="0 0 24 24" style="transform:scaleX(-1)"><path d="M4 12l16-8-6 16-3-7z"/></svg></button>
            </div>
        </div>
    </div>


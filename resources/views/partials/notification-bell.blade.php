<div style="position:relative;display:inline-block;margin-right:8px;" id="notifBellWrap">
    <button onclick="toggleNotifDropdown()" style="background:rgba(255,255,255,0.15);border:none;color:#fff;width:36px;height:36px;border-radius:50%;cursor:pointer;display:flex;align-items:center;justify-content:center;position:relative;" onmouseover="this.style.background='rgba(255,255,255,0.25)'" onmouseout="this.style.background='rgba(255,255,255,0.15)'">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M12 22c1.1 0 2-.9 2-2h-4c0 1.1.9 2 2 2zm6-6v-5c0-3.07-1.64-5.64-4.5-6.32V4c0-.83-.67-1.5-1.5-1.5s-1.5.67-1.5 1.5v.68C7.63 5.36 6 7.92 6 11v5l-2 2v1h16v-1l-2-2z"/></svg>
        <span id="notifBadge" style="display:none;position:absolute;top:-2px;right:-2px;background:#eb5a46;color:#fff;border-radius:50%;width:16px;height:16px;font-size:10px;font-weight:700;display:flex;align-items:center;justify-content:center;"></span>
    </button>
    <div id="notifDropdown" style="display:none;position:fixed;top:56px;right:16px;width:340px;max-height:480px;background:#22272b;border:1px solid #3c444d;border-radius:8px;box-shadow:0 8px 24px rgba(0,0,0,0.5);z-index:9999;overflow:hidden;flex-direction:column;">
        <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 16px;border-bottom:1px solid #3c444d;">
            <span style="font-size:14px;font-weight:600;color:#b6c2cf;">Notifications</span>
            <span onclick="markAllRead()" style="font-size:12px;color:#579dff;cursor:pointer;">Mark all read</span>
        </div>
<div id="notifList" style="overflow-y:auto; max-height:400px; scrollbar-width: none; -ms-overflow-style: none;">
    </div>    </div>
</div>

<script>
// ===== SERVICE WORKER + PUSH =====
{{-- Service worker disabled - requires root scope --}}

// Ask permission on first interaction
function requestNotifPermission() {
    if (!('Notification' in window)) return;
    if (Notification.permission === 'default') {
        Notification.requestPermission().then(p => {
            if (p === 'granted') showBrowserNotif('Trello', 'Notifications enabled!', null);
        });
    }
}

// Request on page load
document.addEventListener('DOMContentLoaded', () => setTimeout(requestNotifPermission, 2000));

function showBrowserNotif(title, body, url) {
    if (!('Notification' in window) || Notification.permission !== 'granted') return;
    const n = new Notification(title, { body, icon: '{{ asset('favicon.ico') }}' });
    n.onclick = () => { window.focus(); if (url) window.location.href = url; n.close(); };
    setTimeout(() => n.close(), 6000);
}
</script>

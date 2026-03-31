{{-- Real-Time Notifications via Laravel Echo + Reverb --}}
<style>
    /* Toast notification container */
    #notification-toast-container {
        position: fixed;
        top: 80px;
        right: 20px;
        z-index: 10000;
        display: flex;
        flex-direction: column;
        gap: 10px;
        max-width: 380px;
    }
    .notification-toast {
        background: #1a1a2e;
        border: 1px solid rgba(13, 148, 136, 0.3);
        border-radius: 12px;
        padding: 14px 18px;
        color: #fff;
        box-shadow: 0 8px 32px rgba(0,0,0,0.3);
        animation: slideInRight 0.4s ease;
        display: flex;
        align-items: flex-start;
        gap: 12px;
    }
    .notification-toast .toast-icon {
        font-size: 20px;
        flex-shrink: 0;
        margin-top: 2px;
    }
    .notification-toast .toast-body {
        flex: 1;
        font-size: 13px;
        line-height: 1.4;
    }
    .notification-toast .toast-body strong {
        display: block;
        font-size: 14px;
        margin-bottom: 3px;
    }
    .notification-toast .toast-close {
        background: none;
        border: none;
        color: rgba(255,255,255,0.5);
        cursor: pointer;
        font-size: 16px;
        padding: 0;
        line-height: 1;
    }
    .notification-toast .toast-time {
        font-size: 11px;
        color: rgba(255,255,255,0.4);
        margin-top: 4px;
    }
    @keyframes slideInRight {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    @keyframes fadeOut {
        from { opacity: 1; transform: translateX(0); }
        to { opacity: 0; transform: translateX(100%); }
    }
</style>

<div id="notification-toast-container"></div>

{{-- Load Echo + Pusher (Reverb uses Pusher protocol) --}}
<script src="https://cdn.jsdelivr.net/npm/pusher-js@8.3.0/dist/web/pusher.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.16.1/dist/echo.iife.js"></script>

<script>
(function() {
    // Initialize Laravel Echo with Reverb connection
    window.Echo = new Echo({
        broadcaster: 'reverb',
        key: '{{ config("reverb.apps.0.key", env("REVERB_APP_KEY", "")) }}',
        wsHost: '{{ config("reverb.apps.0.options.host", env("REVERB_HOST", request()->getHost())) }}',
        wsPort: {{ config("reverb.apps.0.options.port", env("REVERB_PORT", 8080)) }},
        wssPort: {{ config("reverb.apps.0.options.port", env("REVERB_PORT", 443)) }},
        forceTLS: {{ config("reverb.apps.0.options.tls", env("REVERB_SCHEME", "https") === "https" ? "true" : "false") }},
        enabledTransports: ['ws', 'wss'],
        authEndpoint: '/broadcasting/auth',
        auth: {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
            }
        }
    });

    const userId = {{ auth()->check() ? auth()->id() : 0 }};

    function getTypeIcon(type) {
        const icons = {
            'proforma_floated': '🔔',
            'proforma_rejected': '❌',
            'proforma_sent_to_owner': '✅',
            'proforma_closed': '🔒',
            'inbox_notification': '📥',
        };
        return icons[type] || '🔔';
    }

    function showToast(notification) {
        const container = document.getElementById('notification-toast-container');
        if (!container) return;

        const toast = document.createElement('div');
        toast.className = 'notification-toast';
        toast.innerHTML = `
            <span class="toast-icon">${getTypeIcon(notification.type)}</span>
            <div class="toast-body">
                <strong>${notification.file_number ? '#' + notification.file_number : 'Notification'}</strong>
                ${notification.message}
                <div class="toast-time">just now</div>
            </div>
            <button class="toast-close" onclick="this.parentElement.remove()">&times;</button>
        `;
        container.appendChild(toast);

        // Auto-remove after 8 seconds
        setTimeout(() => {
            if (toast.parentElement) {
                toast.style.animation = 'fadeOut 0.3s ease forwards';
                setTimeout(() => toast.remove(), 300);
            }
        }, 8000);
    }

    function updateBellCount(count) {
        document.querySelectorAll('.alert-count').forEach(el => {
            el.textContent = count;
            el.style.display = count > 0 ? '' : 'none';
        });
        document.querySelectorAll('.msg-header-badge').forEach(el => {
            el.textContent = count + ' New';
        });
    }

    function addNotificationToDropdown(notification) {
        const listContainer = document.querySelector('.header-notifications-list');
        if (!listContainer) return;

        // Remove "no notifications" placeholder
        const emptyMsg = listContainer.querySelector('.text-muted');
        if (emptyMsg) emptyMsg.remove();

        const item = document.createElement('a');
        item.className = 'dropdown-item';
        item.href = 'javascript:;';
        item.innerHTML = `
            <div class="d-flex align-items-center">
                <div class="user-online">
                    <span style="font-size:24px;">${getTypeIcon(notification.type)}</span>
                </div>
                <div class="flex-grow-1 ms-2">
                    <h6 class="msg-name">${notification.file_number ? '#' + notification.file_number : 'Notification'}
                        <span class="msg-time float-end">just now</span>
                    </h6>
                    <p class="msg-info">${notification.message}</p>
                </div>
            </div>
        `;
        listContainer.prepend(item);
    }

    // Mark all as read
    window.markAllNotificationsRead = function() {
        fetch('/api/notifications/read', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
            }
        })
        .then(r => r.json())
        .then((data) => {
            // Re-fetch unread notifications so we don't accidentally hide
            // approval-pending notifications that should remain until approval.
            return fetch('/api/notifications', {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                }
            }).then(r => r.json());
        })
        .then((data) => {
            const unreadCount = data?.unread_count ?? 0;
            updateBellCount(unreadCount);

            const listContainer = document.querySelector('.header-notifications-list');
            if (listContainer) {
                const items = Array.isArray(data?.notifications) ? data.notifications : [];
                if (items.length === 0) {
                    listContainer.innerHTML = '<div class="text-center p-3 text-muted">No new notifications</div>';
                } else {
                    listContainer.innerHTML = '';
                    // Render a minimal dropdown list (same info as polling payload)
                    items.forEach((n) => {
                        const item = document.createElement('a');
                        item.className = 'dropdown-item';
                        item.href = 'javascript:;';
                        item.setAttribute('data-notification-id', n.id);
                        item.innerHTML = `
                            <div class="d-flex align-items-center">
                                <div class="user-online">
                                    <span style="font-size:24px;">${getTypeIcon(n.type)}</span>
                                </div>
                                <div class="flex-grow-1 ms-2">
                                    <h6 class="msg-name">${n.file_number ? '#' + n.file_number : 'Notification'}
                                        <span class="msg-time float-end">${n.created_at || ''}</span>
                                    </h6>
                                    <p class="msg-info">${n.message || ''}</p>
                                </div>
                            </div>
                        `;
                        listContainer.appendChild(item);
                    });
                }
            }

            const markReadBtn = document.getElementById('mark-all-read-btn');
            if (markReadBtn) markReadBtn.disabled = unreadCount === 0;
        });
    };

    // Do NOT auto-mark notifications as read on bell open.
    // Users can use the explicit "Mark All As Read" button instead.

    // ========== REVERB LISTENERS ==========

    if (userId > 0) {
        // Private channel — user-specific notifications (bell icon updates)
        window.Echo.private('user.' + userId)
            .listen('.notification.sent', (e) => {
                console.log('🔔 Reverb: notification received', e);
                showToast(e.notification);
                addNotificationToDropdown(e.notification);
                updateBellCount(e.unreadCount || 1);

                // Enable mark-as-read button
                const markReadBtn = document.getElementById('mark-all-read-btn');
                if (markReadBtn) markReadBtn.disabled = false;
            })
            .listen('.proforma.status.changed', (e) => {
                console.log('📋 Reverb: proforma status changed (private)', e);
                // Refresh Livewire components if on a proforma page
                if (typeof Livewire !== 'undefined') {
                    Livewire.dispatch('$refresh');
                }
            });
    }

    // Public channel — proforma updates (auto-refresh proforma lists)
    window.Echo.channel('proformas')
        .listen('.proforma.status.changed', (e) => {
            console.log('📋 Reverb: proforma status changed (public)', e);
            // Refresh Livewire components (e.g., proforma list pages)
            if (typeof Livewire !== 'undefined') {
                Livewire.dispatch('$refresh');
            }
            // For non-Livewire pages (business-owner), refresh the table
            if (document.querySelector('tbody') && !document.querySelector('[wire\\:id]')) {
                refreshTable();
            }
        });

    // Admin channel — admin-specific updates
    window.Echo.channel('admin-proformas')
        .listen('.proforma.status.changed', (e) => {
            console.log('📋 Reverb: admin proforma update', e);
            if (typeof Livewire !== 'undefined') {
                Livewire.dispatch('$refresh');
            }
        })
        .listen('.proforma.created', (e) => {
            console.log('📋 Reverb: new proforma created', e);
            if (typeof Livewire !== 'undefined') {
                Livewire.dispatch('$refresh');
            }
        });

    // Helper for non-Livewire table refresh (business-owner page)
    function refreshTable() {
        fetch(window.location.href, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.text())
        .then(html => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const newTbody = doc.querySelector('tbody');
            const currentTbody = document.querySelector('tbody');
            if (newTbody && currentTbody) {
                currentTbody.innerHTML = newTbody.innerHTML;
            }
        })
        .catch(err => console.warn('Table refresh error:', err));
    }

    // Fallback: poll notifications every 60s in case WebSocket connection drops
    setInterval(() => {
        if (!window.Echo?.connector?.pusher?.connection?.state || 
            window.Echo.connector.pusher.connection.state !== 'connected') {
            fetch('/api/notifications', {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                }
            })
            .then(r => r.json())
            .then(data => {
                updateBellCount(data.unread_count);
            })
            .catch(() => {});
        }
    }, 60000);

    console.log('✅ Laravel Echo + Reverb initialized (WebSocket real-time updates)');
})();
</script>

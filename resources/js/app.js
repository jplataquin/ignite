import './bootstrap';
import '../sass/app.scss';
import * as bootstrap from 'bootstrap';

document.addEventListener('DOMContentLoaded', () => {
    // Prevent Flash of Unstyled Content
    document.body.style.opacity = '1';

    // Notification System
    const notificationBell = document.getElementById('notificationBell');
    const notificationBadge = document.getElementById('notificationBadge');
    const notificationList = document.getElementById('notificationList');
    const markAllReadBtn = document.getElementById('markAllRead');

    if (notificationBell) {
        // Initial fetch
        fetchNotifications();

        // Fetch again whenever dropdown is shown
        notificationBell.addEventListener('show.bs.dropdown', fetchNotifications);
    }

    if (markAllReadBtn) {
        markAllReadBtn.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();

            window.axios.post('/api/notifications/read-all')
                .then(() => {
                    fetchNotifications();
                })
                .catch(err => {
                    console.error('Failed to mark all notifications as read:', err);
                });
        });
    }

    function fetchNotifications() {
        if (!notificationList) return;

        window.axios.get('/api/notifications')
            .then(res => {
                const data = res.data;
                const unreadCount = data.unread_count;
                const notifications = data.notifications;

                // Update badge
                if (notificationBadge) {
                    if (unreadCount > 0) {
                        notificationBadge.innerText = unreadCount;
                        notificationBadge.classList.remove('d-none');
                    } else {
                        notificationBadge.classList.add('d-none');
                    }
                }

                // Update "Mark all as read" button visibility
                if (markAllReadBtn) {
                    if (unreadCount > 0) {
                        markAllReadBtn.classList.remove('d-none');
                    } else {
                        markAllReadBtn.classList.add('d-none');
                    }
                }

                // Render list
                if (notifications.length === 0) {
                    notificationList.innerHTML = '<li class="text-center p-3 text-muted">No notifications</li>';
                    return;
                }

                let html = '';
                notifications.forEach(n => {
                    const isUnread = !n.read_at;
                    html += `
                        <a href="${n.link}" class="notification-item p-3 ${isUnread ? 'unread' : ''}" data-id="${n.id}">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="pe-2 text-wrap" style="word-break: break-word;">${escapeHtml(n.message)}</div>
                                ${isUnread ? '<span class="badge bg-danger rounded-circle p-1" style="width: 6px; height: 6px; margin-top: 6px;" title="Unread"></span>' : ''}
                            </div>
                            <small class="text-muted d-block mt-1" style="font-size: 0.75rem;">${n.created_at_human}</small>
                        </a>
                    `;
                });

                notificationList.innerHTML = html;

                // Attach click listeners to notification items
                const items = notificationList.querySelectorAll('.notification-item');
                items.forEach(item => {
                    item.addEventListener('click', function (e) {
                        const id = this.getAttribute('data-id');
                        const link = this.getAttribute('href');

                        // If unread, mark as read first
                        if (this.classList.contains('unread')) {
                            e.preventDefault();
                            window.axios.post(`/api/notifications/${id}/read`)
                                .then(() => {
                                    window.location.href = link;
                                })
                                .catch(err => {
                                    console.error('Failed to mark notification as read:', err);
                                    window.location.href = link;
                                });
                        }
                    });
                });
            })
            .catch(err => {
                console.error('Failed to fetch notifications:', err);
                if (notificationList) {
                    notificationList.innerHTML = '<li class="text-center p-3 text-danger small">Error loading notifications</li>';
                }
            });
    }

    function escapeHtml(str) {
        if (!str) return '';
        return str
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }
});

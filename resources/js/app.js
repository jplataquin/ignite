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

        // WebSocket Real-time Notification Listener
        if (window.userId && window.Echo) {
            window.Echo.private(`App.Models.User.${window.userId}`)
                .notification((notification) => {
                    fetchNotifications();
                });
        }
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

    // Global File Preview Modal Carousel Logic
    document.addEventListener('click', function(e) {
        const previewable = e.target.closest('.previewable-attachment');
        if (previewable) {
            e.preventDefault();
            launchPreviewCarousel(previewable);
        }
    });

    function launchPreviewCarousel(clickedEl) {
        const allPreviewables = Array.from(document.querySelectorAll('.previewable-attachment'))
            .filter(el => el.getAttribute('data-url'));

        if (allPreviewables.length === 0) return;

        const carouselInner = document.querySelector('#previewCarousel .carousel-inner');
        if (!carouselInner) return;

        carouselInner.innerHTML = '';
        const clickedIndex = allPreviewables.indexOf(clickedEl);

        allPreviewables.forEach((el, index) => {
            const url = el.getAttribute('data-url');
            const name = el.getAttribute('data-name') || 'File';
            const mime = el.getAttribute('data-mime') || '';
            const isActive = index === clickedIndex;

            const isImage = mime.startsWith('image/') || 
                            ['jpg', 'jpeg', 'jfif', 'png', 'gif', 'webp'].includes(name.split('.').pop().toLowerCase());

            let itemHtml = '';
            if (isImage) {
                itemHtml = `
                    <div class="carousel-item h-100 ${isActive ? 'active' : ''}" data-is-image="true">
                        <div class="w-100 h-100 d-flex flex-column align-items-center justify-content-center p-4 position-relative overflow-hidden preview-image-viewport" style="user-select: none;">
                            <div class="d-flex align-items-center justify-content-center preview-image-wrapper" style="max-width: 90vw; max-height: 75vh; overflow: visible;">
                                <img src="${url}" class="img-fluid rounded shadow-lg preview-image" style="max-height: 75vh; max-width: 90vw; object-fit: contain; transition: transform 0.15s ease-out; transform-origin: center center;" draggable="false">
                            </div>
                            <p class="mt-3 text-center text-white-50 small mb-0 text-truncate" style="max-width: 600px;">${escapeHtml(name)}</p>
                        </div>
                    </div>
                `;
            } else {
                itemHtml = `
                    <div class="carousel-item h-100 ${isActive ? 'active' : ''}" data-is-image="false">
                        <div class="w-100 h-100 d-flex flex-column align-items-center justify-content-center p-5 text-center">
                            <div class="bg-secondary rounded p-4 mb-3 d-inline-flex align-items-center justify-content-center" style="width: 90px; height: 90px; color: white;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="40" height="48" fill="currentColor" class="bi bi-file-earmark-arrow-down" viewBox="0 0 16 16">
                                    <path d="M8.5 6.5a.5.5 0 0 0-1 0v3.793L6.354 9.146a.5.5 0 1 0-.708.708l2 2a.5.5 0 0 0 .708 0l2-2a.5.5 0 0 0-.708-.708L8.5 10.293z"/>
                                    <path d="M14 14V4.5L9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2M9.5 3A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h5.5z"/>
                                </svg>
                            </div>
                            <h5 class="text-white mb-2 text-wrap px-3" style="max-width: 600px;">${escapeHtml(name)}</h5>
                            <p class="text-muted mb-4 small">Format: ${escapeHtml(mime || 'Document')}</p>
                            <a href="${url}" download="${name}" class="btn btn-outline-light px-4 btn-sm d-inline-flex align-items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-download" viewBox="0 0 16 16">
                                    <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5"/>
                                    <path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708l3 3z"/>
                                </svg>
                                Download Attachment
                            </a>
                        </div>
                    </div>
                `;
            }

            carouselInner.insertAdjacentHTML('beforeend', itemHtml);
        });

        // Initialize and launch modal
        const modalEl = document.getElementById('previewCarouselModal');
        if (modalEl) {
            const modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
            modalInstance.show();
        }

        // Target active index in carousel
        const carouselEl = document.getElementById('previewCarousel');
        if (carouselEl) {
            const carouselInstance = bootstrap.Carousel.getOrCreateInstance(carouselEl);
            carouselInstance.to(clickedIndex);
        }

        resetImageTransform();
        updateToolbarVisibility();
    }

    // Image Manipulation State & Handlers
    let currentScale = 1;
    let currentRotation = 0;
    let panX = 0;
    let panY = 0;
    let isDragging = false;
    let startX = 0;
    let startY = 0;

    const MIN_SCALE = 0.5;
    const MAX_SCALE = 4.0;
    const SCALE_STEP = 0.25;

    function getActivePreviewImage() {
        const activeItem = document.querySelector('#previewCarousel .carousel-item.active');
        if (!activeItem) return null;
        return activeItem.querySelector('.preview-image');
    }

    function applyImageTransform(animate = true) {
        const img = getActivePreviewImage();
        if (!img) return;

        img.style.transition = animate ? 'transform 0.15s ease-out' : 'none';
        img.style.transform = `translate(${panX}px, ${panY}px) scale(${currentScale}) rotate(${currentRotation}deg)`;
        img.style.cursor = currentScale > 1 ? (isDragging ? 'grabbing' : 'grab') : 'default';

        const zoomBadge = document.getElementById('previewZoomLevel');
        if (zoomBadge) {
            zoomBadge.textContent = `${Math.round(currentScale * 100)}%`;
        }
    }

    function resetImageTransform() {
        currentScale = 1;
        currentRotation = 0;
        panX = 0;
        panY = 0;
        isDragging = false;
        applyImageTransform(true);
    }

    function zoomIn() {
        if (currentScale < MAX_SCALE) {
            currentScale = Math.min(MAX_SCALE, Math.round((currentScale + SCALE_STEP) * 100) / 100);
            applyImageTransform(true);
        }
    }

    function zoomOut() {
        if (currentScale > MIN_SCALE) {
            currentScale = Math.max(MIN_SCALE, Math.round((currentScale - SCALE_STEP) * 100) / 100);
            if (currentScale <= 1) {
                panX = 0;
                panY = 0;
            }
            applyImageTransform(true);
        }
    }

    function rotateClockwise() {
        currentRotation = (currentRotation + 90) % 360;
        applyImageTransform(true);
    }

    function rotateCounterClockwise() {
        currentRotation = (currentRotation - 90) % 360;
        applyImageTransform(true);
    }

    function updateToolbarVisibility() {
        const activeItem = document.querySelector('#previewCarousel .carousel-item.active');
        const toolbar = document.getElementById('previewImageControls');
        if (!toolbar) return;

        if (activeItem && activeItem.getAttribute('data-is-image') === 'true') {
            toolbar.classList.remove('d-none');
            toolbar.classList.add('d-flex');
        } else {
            toolbar.classList.add('d-none');
            toolbar.classList.remove('d-flex');
        }
    }

    // Attach Toolbar Controls
    document.getElementById('btnPreviewZoomIn')?.addEventListener('click', function(e) {
        e.preventDefault();
        zoomIn();
    });

    document.getElementById('btnPreviewZoomOut')?.addEventListener('click', function(e) {
        e.preventDefault();
        zoomOut();
    });

    document.getElementById('btnPreviewReset')?.addEventListener('click', function(e) {
        e.preventDefault();
        resetImageTransform();
    });

    document.getElementById('btnPreviewRotateRight')?.addEventListener('click', function(e) {
        e.preventDefault();
        rotateClockwise();
    });

    document.getElementById('btnPreviewRotateLeft')?.addEventListener('click', function(e) {
        e.preventDefault();
        rotateCounterClockwise();
    });

    // Panning & Wheel Zoom on Carousel Viewport
    const carouselInner = document.querySelector('#previewCarousel .carousel-inner');
    if (carouselInner) {
        carouselInner.addEventListener('mousedown', function(e) {
            const img = getActivePreviewImage();
            if (!img || currentScale <= 1) return;
            if (e.target !== img && !img.contains(e.target)) return;

            e.preventDefault();
            isDragging = true;
            startX = e.clientX - panX;
            startY = e.clientY - panY;
            img.style.cursor = 'grabbing';
            img.style.transition = 'none';
        });

        document.addEventListener('mousemove', function(e) {
            if (!isDragging) return;
            const img = getActivePreviewImage();
            if (!img) return;

            panX = e.clientX - startX;
            panY = e.clientY - startY;
            img.style.transform = `translate(${panX}px, ${panY}px) scale(${currentScale}) rotate(${currentRotation}deg)`;
        });

        document.addEventListener('mouseup', function() {
            if (isDragging) {
                isDragging = false;
                const img = getActivePreviewImage();
                if (img) {
                    img.style.cursor = currentScale > 1 ? 'grab' : 'default';
                    img.style.transition = 'transform 0.15s ease-out';
                }
            }
        });

        carouselInner.addEventListener('wheel', function(e) {
            const img = getActivePreviewImage();
            if (!img) return;

            if (e.target.closest('.preview-image-viewport') || e.target.closest('.preview-image')) {
                e.preventDefault();
                if (e.deltaY < 0) {
                    zoomIn();
                } else {
                    zoomOut();
                }
            }
        }, { passive: false });
    }

    const previewCarouselEl = document.getElementById('previewCarousel');
    if (previewCarouselEl) {
        previewCarouselEl.addEventListener('slide.bs.carousel', function() {
            resetImageTransform();
        });
        previewCarouselEl.addEventListener('slid.bs.carousel', function() {
            updateToolbarVisibility();
        });
    }

    const previewModalEl = document.getElementById('previewCarouselModal');
    if (previewModalEl) {
        previewModalEl.addEventListener('hidden.bs.modal', function() {
            resetImageTransform();
        });
        previewModalEl.addEventListener('shown.bs.modal', function() {
            updateToolbarVisibility();
        });
    }

    // Keyboard Shortcuts for Zoom & Rotation
    document.addEventListener('keydown', function(e) {
        const modal = document.getElementById('previewCarouselModal');
        if (!modal || !modal.classList.contains('show')) return;
        const activeItem = document.querySelector('#previewCarousel .carousel-item.active');
        if (!activeItem || activeItem.getAttribute('data-is-image') !== 'true') return;

        if (e.key === '+' || e.key === '=') {
            e.preventDefault();
            zoomIn();
        } else if (e.key === '-' || e.key === '_') {
            e.preventDefault();
            zoomOut();
        } else if (e.key === 'r' || e.key === 'R') {
            e.preventDefault();
            if (e.shiftKey) {
                rotateCounterClockwise();
            } else {
                rotateClockwise();
            }
        } else if (e.key === '0') {
            e.preventDefault();
            resetImageTransform();
        }
    });

    // Touch Swiping Gestures Support for Carousel
    if (carouselEl) {
        let touchStartX = 0;
        let touchEndX = 0;

        carouselEl.addEventListener('touchstart', e => {
            if (currentScale > 1) return;
            touchStartX = e.changedTouches[0].screenX;
        }, { passive: true });

        carouselEl.addEventListener('touchend', e => {
            if (currentScale > 1) return;
            touchEndX = e.changedTouches[0].screenX;
            const carouselInstance = bootstrap.Carousel.getOrCreateInstance(carouselEl);
            if (touchEndX < touchStartX - 50) {
                carouselInstance.next();
            } else if (touchEndX > touchStartX + 50) {
                carouselInstance.prev();
            }
        }, { passive: true });
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

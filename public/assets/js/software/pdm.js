/**
 * PdM Analyzer - JavaScript
 * مسیر: public/assets/js/modules/pdm.js
 */

(function () {
    'use strict';

    // ═══════════════════════════════════════════════════
    // 🔔 تأیید حذف
    // ═══════════════════════════════════════════════════
    document.addEventListener('DOMContentLoaded', function () {
        // تمام لینک‌های حذف با کلاس pdm-confirm-delete
        document.querySelectorAll('.pdm-confirm-delete').forEach(function (link) {
            link.addEventListener('click', function (e) {
                const message = this.dataset.message || 'آیا از حذف این مورد اطمینان دارید؟';
                if (!confirm(message)) {
                    e.preventDefault();
                    return false;
                }
            });
        });

        // ═══════════════════════════════════════════════════
        // ⏰ Auto-hide Alertها بعد از ۵ ثانیه
        // ═══════════════════════════════════════════════════
        document.querySelectorAll('.pdm-alert').forEach(function (alert) {
            setTimeout(function () {
                alert.style.transition = 'opacity 0.5s';
                alert.style.opacity = '0';
                setTimeout(function () {
                    alert.style.display = 'none';
                }, 500);
            }, 5000);
        });

        // ═══════════════════════════════════════════════════
        // 📋 فعال‌سازی Tooltipها (اگر Bootstrap لود شده)
        // ═══════════════════════════════════════════════════
        if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
            document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (el) {
                new bootstrap.Tooltip(el);
            });
        }

        // ═══════════════════════════════════════════════════
        // 🔍 جستجوی زنده در جدول
        // ═══════════════════════════════════════════════════
        const searchInput = document.getElementById('pdmSearchInput');
        if (searchInput) {
            searchInput.addEventListener('input', function () {
                const query = this.value.trim().toLowerCase();
                const rows = document.querySelectorAll('.pdm-table tbody tr');
                let visibleCount = 0;
                rows.forEach(function (row) {
                    const text = row.textContent.toLowerCase();
                    if (text.indexOf(query) !== -1) {
                        row.style.display = '';
                        visibleCount++;
                    } else {
                        row.style.display = 'none';
                    }
                });

                // نمایش پیام "نتیجه‌ای یافت نشد"
                const noResult = document.getElementById('pdmNoResult');
                if (noResult) {
                    noResult.style.display = visibleCount === 0 ? 'block' : 'none';
                }
            });
        }

        // ═══════════════════════════════════════════════════
        // 📊 انیمیشن اعداد شمارنده
        // ═══════════════════════════════════════════════════
        document.querySelectorAll('.pdm-count-up').forEach(function (el) {
            const target = parseInt(el.dataset.target || '0', 10);
            const duration = 1000;
            const step = Math.max(1, Math.floor(target / 50));
            let current = 0;
            const timer = setInterval(function () {
                current += step;
                if (current >= target) {
                    current = target;
                    clearInterval(timer);
                }
                el.textContent = current.toLocaleString('fa-IR');
            }, duration / 50);
        });
    });

    // ═══════════════════════════════════════════════════
    // 🌐 API Global Helper
    // ═══════════════════════════════════════════════════
    window.PdM = {
        /**
         * درخواست AJAX
         */
        fetch: function (controller, action, params) {
            const baseUrl = (window.CURRENT_MODULE_URL || '/software/pdm-analyzer/');
            const url = new URL(baseUrl, window.location.origin);
            url.searchParams.set('controller', controller);
            url.searchParams.set('action', action);
            if (params) {
                Object.keys(params).forEach(function (key) {
                    url.searchParams.set(key, params[key]);
                });
            }

            return fetch(url.toString(), {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            }).then(function (response) {
                return response.json();
            });
        },

        /**
         * نمایش Toast
         */
        toast: function (message, type) {
            type = type || 'info';
            const toast = document.createElement('div');
            toast.className = 'pdm-alert ' + type;
            toast.style.position = 'fixed';
            toast.style.top = '20px';
            toast.style.left = '20px';
            toast.style.zIndex = '9999';
            toast.style.minWidth = '300px';
            toast.innerHTML = '<i class="fas fa-info-circle"></i> ' + message;
            document.body.appendChild(toast);
            setTimeout(function () {
                toast.remove();
            }, 4000);
        }
    };
})();
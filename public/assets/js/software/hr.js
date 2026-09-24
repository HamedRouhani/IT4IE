/**
 * ============================================================
 * HR Analyzer - JavaScript
 * ============================================================
 * مسیر: public/assets/js/software/hr.js
 * ============================================================
 */

(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {

        // ═══════════════════════════════════════════════════
        // 🔔 تأیید حذف
        // ═══════════════════════════════════════════════════
        document.querySelectorAll('.hr-confirm-delete').forEach(function (link) {
            link.addEventListener('click', function (e) {
                const message = this.dataset.message || 'آیا از حذف این مورد اطمینان دارید؟';
                if (!confirm(message)) {
                    e.preventDefault();
                    return false;
                }
            });
        });

        // ═══════════════════════════════════════════════════
        // ⏰ Auto-hide Alertها
        // ═══════════════════════════════════════════════════
        document.querySelectorAll('.hr-alert').forEach(function (alert) {
            setTimeout(function () {
                alert.style.transition = 'opacity 0.5s';
                alert.style.opacity = '0';
                setTimeout(function () {
                    alert.style.display = 'none';
                }, 500);
            }, 5000);
        });

        // ═══════════════════════════════════════════════════
        // 🔍 جستجوی زنده در جدول
        // ═══════════════════════════════════════════════════
        const searchInput = document.getElementById('hrSearchInput');
        if (searchInput) {
            searchInput.addEventListener('input', function () {
                const query = this.value.trim().toLowerCase();
                const rows = document.querySelectorAll('.hr-table tbody tr');
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
            });
        }
    });

    // ═══════════════════════════════════════════════════
    // 🌐 API Global Helper
    // ═══════════════════════════════════════════════════
    window.HR = {
        /**
         * درخواست AJAX
         */
        fetch: function (controller, action, params) {
            const baseUrl = (window.CURRENT_MODULE_URL || '/software/hr-analyzer/');
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
            toast.className = 'hr-alert ' + type;
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

/* ============================================
   Rating Stars - Interview Evaluation
   ============================================ */
(function () {
    'use strict';

    function initRatingStars() {
        const containers = document.querySelectorAll('.hr-rating-stars');
        
        containers.forEach(function (container) {
            const inputId = container.dataset.input;
            const input = document.getElementById(inputId);
            if (!input) return;

            const valueDisplay = container.parentElement.querySelector('.hr-rating-value');
            const clearBtn = container.parentElement.querySelector('.hr-rating-clear');
            const maxStars = parseInt(container.dataset.max, 10) || 5;
            const step = parseFloat(container.dataset.step) || 0.5;

            // ساخت ستاره‌ها
            container.innerHTML = '';
            for (let i = 1; i <= maxStars; i++) {
                const star = document.createElement('span');
                star.className = 'hr-star';
                star.dataset.value = i;
                star.innerHTML = '<i class="fas fa-star"></i>';
                container.appendChild(star);
            }

            const stars = container.querySelectorAll('.hr-star');

            function render(value) {
                const v = parseFloat(value) || 0;
                stars.forEach(function (star, idx) {
                    const starValue = idx + 1;
                    star.classList.remove('filled', 'half');
                    if (v >= starValue) {
                        star.classList.add('filled');
                    } else if (v >= starValue - 0.5 && v < starValue) {
                        star.classList.add('half');
                    }
                });
                if (valueDisplay) {
                    valueDisplay.textContent = v > 0 ? v.toFixed(1) : '—';
                }
            }

            // کلیک روی ستاره‌ها (نیمه چپ/راست)
            stars.forEach(function (star) {
                star.addEventListener('mousemove', function (e) {
                    const rect = star.getBoundingClientRect();
                    const x = e.clientX - rect.left;
                    const isLeftHalf = x < rect.width / 2;
                    const starValue = parseInt(star.dataset.value, 10);
                    const newValue = isLeftHalf ? starValue - 0.5 : starValue;
                    star.style.color = '#fbbf24';
                });

                star.addEventListener('mouseleave', function () {
                    star.style.color = '';
                });

                star.addEventListener('click', function (e) {
                    const rect = star.getBoundingClientRect();
                    const x = e.clientX - rect.left;
                    const isLeftHalf = x < rect.width / 2;
                    const starValue = parseInt(star.dataset.value, 10);
                    const newValue = isLeftHalf ? starValue - 0.5 : starValue;
                    input.value = newValue.toFixed(1);
                    render(newValue);
                });
            });

            // دکمه پاک کردن
            if (clearBtn) {
                clearBtn.addEventListener('click', function (e) {
                    e.preventDefault();
                    input.value = '';
                    render(0);
                });
            }

            // مقدار اولیه
            render(input.value);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initRatingStars);
    } else {
        initRatingStars();
    }
})();

/* ============================================
   Money Input - فرمت خودکار با کاما
   ============================================ */
(function () {
    'use strict';

    function formatMoney(input) {
        let value = input.value.replace(/[^\d.-]/g, '');
        if (value === '' || value === '-') {
            input.value = '';
            return;
        }
        const parts = value.split('.');
        parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        input.value = parts.join('.');
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.hr-money-input').forEach(function (input) {
            formatMoney(input);
            input.addEventListener('input', function () {
                formatMoney(this);
            });
        });
    });
})();
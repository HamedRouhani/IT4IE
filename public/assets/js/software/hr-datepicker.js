/**
 * ============================================================
 * HR Analyzer - Datepicker شمسی سبک
 * ============================================================
 * مسیر: public/assets/js/software/hr-datepicker.js
 * 
 * این فایل کپی‌برداری شده از pdm-datepicker.js است
 * ============================================================
 */

(function () {
    'use strict';

    function gregorianToJalali(gy, gm, gd) {
        const g_d_m = [0, 31, 59, 90, 120, 151, 181, 212, 243, 273, 304, 334];
        let jy = (gy <= 1600) ? 0 : 979;
        gy -= (gy <= 1600) ? 621 : 1600;
        const gy2 = (gm > 2) ? (gy + 1) : gy;
        let days = (365 * gy) + parseInt((gy2 + 3) / 4) - parseInt((gy2 + 99) / 100)
                 + parseInt((gy2 + 399) / 400) - 80 + gd + g_d_m[gm - 1];
        jy += 33 * parseInt(days / 12053);
        days %= 12053;
        jy += 4 * parseInt(days / 1461);
        days %= 1461;
        if (days > 365) {
            jy += parseInt((days - 1) / 365);
            days = (days - 1) % 365;
        }
        const jm = (days < 186) ? 1 + parseInt(days / 31) : 7 + parseInt((days - 186) / 30);
        const jd = 1 + ((days < 186) ? (days % 31) : ((days - 186) % 30));
        return [jy, jm, jd];
    }

    function jalaliToGregorian(jy, jm, jd) {
        jy += 1595;
        let days = -355668 + (365 * jy) + (parseInt(jy / 33) * 8) + parseInt(((jy % 33) + 3) / 4) + jd
                 + ((jm < 7) ? (jm - 1) * 31 : ((jm - 7) * 30) + 186);
        let gy = 400 * parseInt(days / 146097);
        days %= 146097;
        if (days > 36524) {
            gy += 100 * parseInt(--days / 36524);
            days %= 36524;
            if (days >= 365) days++;
        }
        gy += 4 * parseInt(days / 1461);
        days %= 1461;
        if (days > 365) {
            gy += parseInt((days - 1) / 365);
            days = (days - 1) % 365;
        }
        let gd = days + 1;
        const sal_a = [0, 31, ((gy % 4 === 0 && gy % 100 !== 0) || (gy % 400 === 0)) ? 29 : 28,
                       31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
        let gm;
        for (gm = 0; gm < 13 && gd > sal_a[gm]; gm++) gd -= sal_a[gm];
        return [gy, gm, gd];
    }

    function jMonthName(m) {
        return ['', 'فروردین', 'اردیبهشت', 'خرداد', 'تیر', 'مرداد', 'شهریور',
                'مهر', 'آبان', 'آذر', 'دی', 'بهمن', 'اسفند'][m];
    }

    function jDaysInMonth(jy, jm) {
        if (jm <= 6) return 31;
        if (jm <= 11) return 30;
        const r = jy % 33;
        const leap = [1, 5, 9, 13, 17, 22, 26, 30].includes(r);
        return leap ? 30 : 29;
    }

    function toFa(num) {
        const fa = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        return String(num).replace(/\d/g, d => fa[d]);
    }

    function pad2(n) { return (n < 10 ? '0' : '') + n; }

    class HrDatepicker {
        constructor(input) {
            this.input = input;
            this.wrapper = null;
            this.panel = null;

            const today = new Date();
            const j = gregorianToJalali(today.getFullYear(), today.getMonth() + 1, today.getDate());
            this.viewYear = j[0];
            this.viewMonth = j[1];

            const val = this.input.value.trim();
            if (val) {
                const parts = val.replace(/-/g, '/').split('/').map(Number);
                if (parts.length === 3 && parts[0] > 1300 && parts[0] < 1500) {
                    this.viewYear = parts[0];
                    this.viewMonth = parts[1];
                }
            }

            this.buildWrapper();
            this.bindEvents();
        }

        buildWrapper() {
            const parent = this.input.parentNode;
            const wrapper = document.createElement('div');
            wrapper.className = 'hr-dp-wrapper';
            wrapper.style.position = 'relative';
            parent.insertBefore(wrapper, this.input);
            wrapper.appendChild(this.input);

            const panel = document.createElement('div');
            panel.className = 'hr-dp-panel';
            panel.style.display = 'none';
            wrapper.appendChild(panel);

            this.wrapper = wrapper;
            this.panel = panel;
        }

        bindEvents() {
            this.input.addEventListener('focus', () => this.open());
            this.input.addEventListener('click', () => this.open());
            document.addEventListener('click', (e) => {
                if (!this.wrapper.contains(e.target)) this.close();
            });
        }

        open() {
            this.render();
            this.panel.style.display = 'block';
        }

        close() { this.panel.style.display = 'none'; }

        render() {
            const jy = this.viewYear;
            const jm = this.viewMonth;
            const g = jalaliToGregorian(jy, jm, 1);
            const gDate = new Date(g[0], g[1] - 1, g[2]);
            let firstDay = (gDate.getDay() + 1) % 7;
            const daysInMonth = jDaysInMonth(jy, jm);

            let html = '';
            html += '<div class="hr-dp-header">';
            html += `<button type="button" class="hr-dp-nav" data-nav="prev-year">«</button>`;
            html += `<button type="button" class="hr-dp-nav" data-nav="prev-month">‹</button>`;
            html += `<span class="hr-dp-title">${jMonthName(jm)} ${toFa(jy)}</span>`;
            html += `<button type="button" class="hr-dp-nav" data-nav="next-month">›</button>`;
            html += `<button type="button" class="hr-dp-nav" data-nav="next-year">»</button>`;
            html += '</div>';
            html += '<div class="hr-dp-grid">';
            ['ش', 'ی', 'د', 'س', 'چ', 'پ', 'ج'].forEach(d => {
                html += `<div class="hr-dp-dow">${d}</div>`;
            });
            for (let i = 0; i < firstDay; i++) {
                html += '<div class="hr-dp-day hr-dp-empty"></div>';
            }
            for (let d = 1; d <= daysInMonth; d++) {
                html += `<div class="hr-dp-day" data-day="${d}">${toFa(d)}</div>`;
            }
            html += '</div>';
            html += '<div class="hr-dp-footer">';
            html += '<button type="button" class="hr-dp-btn" data-action="today">امروز</button>';
            html += '<button type="button" class="hr-dp-btn" data-action="clear">پاک کردن</button>';
            html += '</div>';

            this.panel.innerHTML = html;

            this.panel.querySelectorAll('[data-nav]').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    this.navigate(btn.dataset.nav);
                });
            });
            this.panel.querySelectorAll('.hr-dp-day[data-day]').forEach(day => {
                day.addEventListener('click', (e) => {
                    e.stopPropagation();
                    this.selectDay(parseInt(day.dataset.day));
                });
            });
            this.panel.querySelectorAll('[data-action]').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    if (btn.dataset.action === 'today') {
                        const today = new Date();
                        const j = gregorianToJalali(today.getFullYear(), today.getMonth() + 1, today.getDate());
                        this.viewYear = j[0];
                        this.viewMonth = j[1];
                        this.selectDay(j[2]);
                    } else if (btn.dataset.action === 'clear') {
                        this.input.value = '';
                        this.close();
                    }
                });
            });
        }

        navigate(dir) {
            if (dir === 'prev-month') {
                this.viewMonth--;
                if (this.viewMonth < 1) { this.viewMonth = 12; this.viewYear--; }
            } else if (dir === 'next-month') {
                this.viewMonth++;
                if (this.viewMonth > 12) { this.viewMonth = 1; this.viewYear++; }
            } else if (dir === 'prev-year') {
                this.viewYear--;
            } else if (dir === 'next-year') {
                this.viewYear++;
            }
            this.render();
        }

        selectDay(day) {
            this.input.value = `${this.viewYear}/${pad2(this.viewMonth)}/${pad2(day)}`;
            this.close();
            this.input.dispatchEvent(new Event('change', { bubbles: true }));
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('input.hr-datepicker').forEach(input => {
            if (!input.dataset.hrDpInitialized) {
                new HrDatepicker(input);
                input.dataset.hrDpInitialized = '1';
            }
        });
    });

    window.HrDatepicker = HrDatepicker;
})();
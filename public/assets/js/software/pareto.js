/**
 * ============================================================
 * Quality Analyzer — Pareto Module
 * ============================================================
 * مسیر: public/assets/js/software/pareto.js
 * 
 * این فایل مسئول:
 *   1. ماتریس داینامیک ورود دسته‌بندی‌ها در فرم
 *   2. پیش‌نمایش زنده (AJAX به pareto/preview)
 *   3. رسم نمودار Pareto (Chart.js) در صفحه‌ی نمایش
 * ============================================================
 */

(function () {
    'use strict';

    // ═══════════════════════════════════════════════════════
    // ابزارهای کمکی
    // ═══════════════════════════════════════════════════════

    const debounce = (fn, delay = 400) => {
        let timer = null;
        return (...args) => {
            clearTimeout(timer);
            timer = setTimeout(() => fn.apply(this, args), delay);
        };
    };

    const escapeHtml = (str) => {
        if (str === null || str === undefined) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    };

    const formatNumber = (n, decimals = 0) => {
        const num = parseFloat(n) || 0;
        return num.toLocaleString('en-US', {
            minimumFractionDigits: decimals,
            maximumFractionDigits: decimals,
        });
    };

    // ═══════════════════════════════════════════════════════
    // 1) ماتریس داینامیک آیتم‌ها در فرم
    // ═══════════════════════════════════════════════════════

    const ItemsEditor = {
        tbody: null,
        rowCounter: 0,

        init() {
            this.tbody = document.getElementById('itemsBody');
            if (!this.tbody) return;

            const addBtn = document.getElementById('addItemBtn');
            if (addBtn) {
                addBtn.addEventListener('click', () => this.addRow());
            }

            // بارگذاری آیتم‌های اولیه (در حالت ویرایش)
            const initial = window.PARETO_INITIAL_ITEMS || [];
            if (initial.length > 0) {
                initial.forEach((item) => this.addRow(item));
            } else {
                // اگه خالی بود، ۳ ردیف پیش‌فرض اضافه کن
                this.addRow();
                this.addRow();
                this.addRow();
            }

            // گوش دادن به تغییرات برای پیش‌نمایش
            this.tbody.addEventListener('input', debounce(() => {
                Preview.refresh();
            }, 500));

            this.tbody.addEventListener('click', (e) => {
                const btn = e.target.closest('.remove-item-btn');
                if (btn) {
                    this.removeRow(btn.closest('tr'));
                }
            });

            // آستانه
            const thresholdInput = document.getElementById('thresholdInput');
            if (thresholdInput) {
                thresholdInput.addEventListener('input', debounce(() => {
                    Preview.refresh();
                }, 400));
            }
        },

        addRow(data = null) {
            const idx = this.rowCounter++;
            const tr = document.createElement('tr');
            tr.dataset.rowId = idx;

            tr.innerHTML = `
                <td class="row-index">1</td>
                <td>
                    <input type="text" name="item_name[]" class="qc-form-control"
                           placeholder="نام دسته (مثلاً: ترک)" required
                           value="${escapeHtml(data?.category_name || '')}">
                </td>
                <td>
                    <input type="number" step="0.0001" min="0" name="item_value[]"
                           class="qc-form-control item-value-input" placeholder="0"
                           value="${data?.value !== undefined && data?.value !== null ? data.value : ''}">
                </td>
                <td>
                    <input type="number" step="1" min="0" name="item_frequency[]"
                           class="qc-form-control item-freq-input" placeholder="اختیاری"
                           value="${data?.frequency ?? ''}">
                </td>
                <td>
                    <input type="number" step="0.0001" min="0" name="item_cost[]"
                           class="qc-form-control item-cost-input" placeholder="اختیاری"
                           value="${data?.cost_per_unit ?? ''}">
                </td>
                <td>
                    <button type="button" class="qc-btn-danger remove-item-btn"
                            style="padding:4px 8px; font-size:0.8rem;" title="حذف">
                        <i class="fas fa-times"></i>
                    </button>
                </td>
            `;

            this.tbody.appendChild(tr);

            // گوش دادن به frequency/cost برای محاسبه‌ی خودکار value
            const freq = tr.querySelector('.item-freq-input');
            const cost = tr.querySelector('.item-cost-input');
            const value = tr.querySelector('.item-value-input');

            // ═══════════════════════════════════════════════════════
            // اصلاح autoCalc — فقط وقتی هر دو freq و cost معتبرن
            // ═══════════════════════════════════════════════════════
            const autoCalc = () => {
                const freqVal = freq.value.trim();
                const costVal = cost.value.trim();
                
                // اگه هر دو خالی هستن، هیچ کاری نکن (value دست‌نخورده بمونه)
                if (freqVal === '' || costVal === '') {
                    return;
                }
                
                const f = parseFloat(freqVal);
                const c = parseFloat(costVal);
                
                // فقط وقتی هر دو عدد معتبر و بزرگتر از صفر هستن
                if (!isNaN(f) && f > 0 && !isNaN(c) && c > 0) {
                    value.value = (f * c).toFixed(4);
                }
                // در غیر این صورت، value رو دست نزن
            };

            freq.addEventListener('input', autoCalc);
            cost.addEventListener('input', autoCalc);

            this.updateRowNumbers();
            Preview.refresh();
        },

        removeRow(tr) {
            if (!tr) return;
            tr.remove();
            this.updateRowNumbers();
            Preview.refresh();
        },

        updateRowNumbers() {
            const rows = this.tbody.querySelectorAll('tr');
            rows.forEach((tr, i) => {
                const idxCell = tr.querySelector('.row-index');
                if (idxCell) idxCell.textContent = i + 1;
            });
        },

        collect() {
            const rows = this.tbody.querySelectorAll('tr');
            const items = [];

            rows.forEach((tr) => {
                const name = tr.querySelector('input[name="item_name[]"]')?.value.trim();
                if (!name) return;

                const value = parseFloat(
                    tr.querySelector('input[name="item_value[]"]')?.value
                ) || 0;
                const freq = tr.querySelector('input[name="item_frequency[]"]')?.value;
                const cost = tr.querySelector('input[name="item_cost[]"]')?.value;

                items.push({
                    category_name: name,
                    value: value,
                    frequency: freq !== '' ? parseInt(freq) : null,
                    cost_per_unit: cost !== '' ? parseFloat(cost) : null,
                });
            });

            return items;
        },
    };

    // ═══════════════════════════════════════════════════════
    // 2) پیش‌نمایش زنده
    // ═══════════════════════════════════════════════════════

    const Preview = {
        chart: null,
        url: null,
        csrfToken: null,

        init() {
            this.url = window.PARETO_PREVIEW_URL || null;
            this.csrfToken = window.QC_CSRF_TOKEN || null;
            this.container = document.getElementById('previewArea');
            this.summary = document.getElementById('previewSummary');
        },

        async refresh() {
            if (!this.url) return;

            const items = ItemsEditor.collect();
            if (items.length === 0) {
                this.renderEmpty();
                return;
            }

            const threshold = parseFloat(
                document.getElementById('thresholdInput')?.value
            ) || 80;

            try {
                const res = await fetch(this.url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({ items, threshold }),
                });

                const data = await res.json();
                if (data.success) {
                    this.render(data.data);
                } else {
                    this.renderEmpty();
                }
            } catch (err) {
                console.error('[Pareto Preview]', err);
            }
        },

        renderEmpty() {
            if (this.container) {
                this.container.innerHTML = `
                    <div class="qc-empty-state" style="padding:2rem;">
                        <i class="fas fa-chart-pie" style="font-size:2rem; color:#cbd5e1;"></i>
                        <p style="color:#94a3b8;">داده‌ای برای پیش‌نمایش وارد نشده</p>
                    </div>
                `;
            }
            if (this.summary) this.summary.style.display = 'none';
            if (this.chart) {
                this.chart.destroy();
                this.chart = null;
            }
        },

        render(data) {
            // نمایش summary
            document.getElementById('pvTotal').textContent = formatNumber(data.total_value);
            document.getElementById('pvVital').textContent = data.vital_few_count;
            document.getElementById('pvVitalPct').textContent = formatNumber(data.vital_few_percent, 1);
            document.getElementById('pvTrivial').textContent = data.trivial_many_count;
            if (this.summary) this.summary.style.display = 'block';

            // رسم chart
            this.renderChart(data.chart);
        },

        renderChart(chartData) {
            if (this.container) {
                this.container.innerHTML = `
                    <div style="position:relative; height:280px;">
                        <canvas id="previewChart"></canvas>
                    </div>
                `;
            }

            const ctx = document.getElementById('previewChart');
            if (!ctx) return;

            if (this.chart) this.chart.destroy();

            this.chart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: chartData.labels,
                    datasets: [
                        {
                            type: 'bar',
                            label: 'مقدار',
                            data: chartData.values,
                            backgroundColor: chartData.colors,
                            borderColor: chartData.colors,
                            borderWidth: 1,
                            yAxisID: 'y',
                            order: 2,
                        },
                        {
                            type: 'line',
                            label: 'تجمعی (%)',
                            data: chartData.cumulative,
                            borderColor: '#059669',
                            backgroundColor: 'rgba(5, 150, 105, 0.1)',
                            borderWidth: 2,
                            pointBackgroundColor: '#059669',
                            pointRadius: 4,
                            tension: 0.1,
                            yAxisID: 'y1',
                            order: 1,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: { font: { family: 'inherit', size: 12 } },
                        },
                        tooltip: {
                            rtl: true,
                            textDirection: 'rtl',
                            callbacks: {
                                label: (ctx) => {
                                    if (ctx.dataset.yAxisID === 'y1') {
                                        return `${ctx.dataset.label}: ${formatNumber(ctx.parsed.y, 2)}%`;
                                    }
                                    return `${ctx.dataset.label}: ${formatNumber(ctx.parsed.y, 2)}`;
                                },
                            },
                        },
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            position: 'right',
                            title: { display: true, text: 'مقدار' },
                        },
                        y1: {
                            beginAtZero: true,
                            max: 100,
                            position: 'left',
                            title: { display: true, text: 'تجمعی (%)' },
                            grid: { drawOnChartArea: false },
                            ticks: {
                                callback: (v) => v + '%',
                            },
                        },
                    },
                },
            });
        },
    };

    // ═══════════════════════════════════════════════════════
    // 3) نمودار اصلی در صفحه‌ی show
    // ═══════════════════════════════════════════════════════

    const ShowChart = {
        init() {
            const canvas = document.getElementById('paretoChart');
            if (!canvas) return;

            const data = window.PARETO_CHART_DATA;
            if (!data) return;

            const unit = window.PARETO_UNIT || '';

            new Chart(canvas, {
                type: 'bar',
                data: {
                    labels: data.labels,
                    datasets: [
                        {
                            type: 'bar',
                            label: 'مقدار' + (unit ? ` (${unit})` : ''),
                            data: data.values,
                            backgroundColor: data.colors,
                            borderColor: data.colors,
                            borderWidth: 1,
                            yAxisID: 'y',
                            order: 2,
                        },
                        {
                            type: 'line',
                            label: 'تجمعی (%)',
                            data: data.cumulative,
                            borderColor: '#059669',
                            backgroundColor: 'rgba(5, 150, 105, 0.08)',
                            borderWidth: 2.5,
                            pointBackgroundColor: '#059669',
                            pointBorderColor: '#fff',
                            pointBorderWidth: 2,
                            pointRadius: 5,
                            pointHoverRadius: 7,
                            tension: 0.1,
                            yAxisID: 'y1',
                            order: 1,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: { font: { family: 'inherit', size: 13, weight: '600' } },
                        },
                        tooltip: {
                            rtl: true,
                            textDirection: 'rtl',
                            backgroundColor: 'rgba(15, 23, 42, 0.95)',
                            titleFont: { size: 13 },
                            bodyFont: { size: 12 },
                            padding: 10,
                            callbacks: {
                                label: (ctx) => {
                                    if (ctx.dataset.yAxisID === 'y1') {
                                        return ` ${ctx.dataset.label}: ${formatNumber(ctx.parsed.y, 2)}%`;
                                    }
                                    return ` ${ctx.dataset.label}: ${formatNumber(ctx.parsed.y, 2)}`;
                                },
                            },
                        },
                    },
                    scales: {
                        x: {
                            ticks: {
                                font: { family: 'inherit', size: 11 },
                                maxRotation: 45,
                                minRotation: 0,
                            },
                        },
                        y: {
                            beginAtZero: true,
                            position: 'right',
                            title: {
                                display: true,
                                text: 'مقدار' + (unit ? ` (${unit})` : ''),
                                font: { family: 'inherit', size: 12, weight: '600' },
                            },
                            grid: { color: 'rgba(0,0,0,0.05)' },
                        },
                        y1: {
                            beginAtZero: true,
                            max: 100,
                            position: 'left',
                            title: {
                                display: true,
                                text: 'درصد تجمعی',
                                font: { family: 'inherit', size: 12, weight: '600' },
                            },
                            grid: { drawOnChartArea: false },
                            ticks: {
                                callback: (v) => v + '%',
                            },
                        },
                    },
                },
            });
        },
    };

    // ═══════════════════════════════════════════════════════
    // Jalali Datepicker
    // ═══════════════════════════════════════════════════════
    function initJalaliDatepickers() {
        if (typeof jQuery === 'undefined' || typeof jQuery.fn.pDatepicker === 'undefined') {
            console.warn('[Pareto] Persian Datepicker not loaded');
            return;
        }

        const $ = jQuery;

        // تنظیمات مشترک
        const opts = {
            format: 'YYYY/MM/DD',
            initialValue: false,
            autoClose: true,
            persianDigit: true,
            observer: true,
            calendar: {
                persian: {
                    locale: 'fa',
                    leapYearMode: 'algorithmic'
                }
            },
            toolbox: {
                calendarSwitch: { enabled: false }
            }
        };

        // تاریخ تحلیل
        const $analysisJ = $('#analysisDateJalali');
        if ($analysisJ.length) {
            $analysisJ.pDatepicker(opts);

            // مقدار اولیه
            const initialJalali = $analysisJ.val();
            if (initialJalali) {
                try {
                    const parts = initialJalali.split('/').map(n => parseInt(n));
                    if (parts.length === 3) {
                        $analysisJ.pDatepicker('setDate', new persianDate([parts[0], parts[1], parts[2]]));
                    }
                } catch (e) { /* ignore */ }
            }

            // sync با hidden
            $analysisJ.on('change', function () {
                const val = $(this).val(); // 1405/07/04
                if (!val) {
                    $('#analysisDate').val('');
                    return;
                }
                const parts = val.split('/').map(n => String(n).padStart(2, '0'));
                // تبدیل شمسی به میلادی
                const gregorian = jalaliToGregorian(parseInt(parts[0]), parseInt(parts[1]), parseInt(parts[2]));
                $('#analysisDate').val(gregorian);
            });
        }

        // بازه از
        const $fromJ = $('#periodFromJalali');
        if ($fromJ.length) {
            $fromJ.pDatepicker(opts);
            $fromJ.on('change', function () {
                const val = $(this).val();
                if (!val) { $('#periodFrom').val(''); return; }
                const parts = val.split('/').map(n => parseInt(n));
                $('#periodFrom').val(jalaliToGregorian(parts[0], parts[1], parts[2]));
            });
        }

        // بازه تا
        const $toJ = $('#periodToJalali');
        if ($toJ.length) {
            $toJ.pDatepicker(opts);
            $toJ.on('change', function () {
                const val = $(this).val();
                if (!val) { $('#periodTo').val(''); return; }
                const parts = val.split('/').map(n => parseInt(n));
                $('#periodTo').val(jalaliToGregorian(parts[0], parts[1], parts[2]));
            });
        }
    }

    // تابع تبدیل شمسی به میلادی (سمت JS)
    function jalaliToGregorian(jy, jm, jd) {
        jy += 1595;
        let days = -355668 + (365 * jy) + (Math.floor(jy / 33) * 8)
                + Math.floor(((jy % 33) + 3) / 4) + jd
                + ((jm < 7) ? (jm - 1) * 31 : ((jm - 7) * 30) + 186);
        let gy = 400 * Math.floor(days / 146097);
        days %= 146097;
        if (days > 36524) {
            gy += 100 * Math.floor(--days / 36524);
            days %= 36524;
            if (days >= 365) days++;
        }
        gy += 4 * Math.floor(days / 1461);
        days %= 1461;
        if (days > 365) {
            gy += Math.floor((days - 1) / 365);
            days = (days - 1) % 365;
        }
        let gd = days + 1;
        const sal_a = [0, 31, ((gy % 4 === 0 && gy % 100 !== 0) || (gy % 400 === 0)) ? 29 : 28,
                    31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
        let gm = 0;
        while (gm < 13 && gd > sal_a[gm]) {
            gd -= sal_a[gm];
            gm++;
        }
        return `${gy}-${String(gm).padStart(2, '0')}-${String(gd).padStart(2, '0')}`;
    }

    const $dueJ = $('#dueDateJalali');
    if ($dueJ.length) {
        $dueJ.pDatepicker(opts);
        $dueJ.on('change', function () {
            const val = $(this).val();
            if (!val) { $('#dueDate').val(''); return; }
            const parts = val.split('/').map(n => parseInt(n));
            $('#dueDate').val(jalaliToGregorian(parts[0], parts[1], parts[2]));
        });
    }

// در DOMContentLoaded
document.addEventListener('DOMContentLoaded', initJalaliDatepickers);

    // ═══════════════════════════════════════════════════════
    // 4) راه‌اندازی
    // ═══════════════════════════════════════════════════════

    document.addEventListener('DOMContentLoaded', () => {
        ItemsEditor.init();
        Preview.init();
        ShowChart.init();
    });

    // در دسترس بودن برای دیباگ
    window.ParetoModule = { ItemsEditor, Preview, ShowChart };
})();
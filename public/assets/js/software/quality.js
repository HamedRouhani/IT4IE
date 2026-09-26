/**
 * ============================================================
 * Quality Analyzer - JavaScript
 * ============================================================
 * مسیر: public/assets/js/software/quality.js
 * ============================================================
 */

(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {

        // ═══════════════════════════════════════════════════
        // 🔔 تأیید حذف
        // ═══════════════════════════════════════════════════
        document.querySelectorAll('.qc-confirm-delete').forEach(function (link) {
            link.addEventListener('click', function (e) {
                var message = this.dataset.message || 'آیا از حذف این مورد اطمینان دارید؟';
                if (!confirm(message)) {
                    e.preventDefault();
                    return false;
                }
            });
        });

        // ═══════════════════════════════════════════════════
        // ⏰ Auto-hide Alertها
        // ═══════════════════════════════════════════════════
        document.querySelectorAll('.qc-alert').forEach(function (alert) {
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
        var searchInput = document.getElementById('qcSearchInput');
        if (searchInput) {
            searchInput.addEventListener('input', function () {
                var query = this.value.trim().toLowerCase();
                var rows = document.querySelectorAll('.qc-table tbody tr');
                rows.forEach(function (row) {
                    var text = row.textContent.toLowerCase();
                    row.style.display = text.indexOf(query) !== -1 ? '' : 'none';
                });
            });
        }

        // ═══════════════════════════════════════════════════
        // 📊 Dataset Data — ورود/ویرایش داده‌ها
        // ═══════════════════════════════════════════════════
        initDatasetDataEditor();
    });

    // ═══════════════════════════════════════════════════
    // 🌐 API Global Helper
    // ═══════════════════════════════════════════════════
    window.QC = {

        /**
         * درخواست AJAX
         */
        fetch: function (controller, action, params) {
            var baseUrl = (window.CURRENT_MODULE_URL || '/software/quality-analyzer/');
            var url = new URL(baseUrl, window.location.origin);
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
         * ارسال POST به‌صورت JSON
         */
        post: function (url, data) {
            return fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(data || {})
            }).then(function (response) {
                return response.json();
            });
        },

        /**
         * نمایش Toast
         */
        toast: function (message, type) {
            type = type || 'info';
            var toast = document.createElement('div');
            toast.className = 'qc-alert ' + type;
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
        },

        /**
         * رندر نمودار کنترل با Chart.js
         */
        renderChart: function (canvasId, series, cl, ucl, lcl, title) {
            var canvas = document.getElementById(canvasId);
            if (!canvas || typeof Chart === 'undefined') return null;

            var labels = series.map(function (_, i) { return i + 1; });

            var dataUcl = series.map(function () { return ucl; });
            var dataLcl = series.map(function () { return lcl; });
            var dataCl  = series.map(function () { return cl; });

            var pointColors = series.map(function (v) {
                if (v > ucl || v < lcl) return '#DC2626';
                return '#059669';
            });

            var pointRadii = series.map(function (v) {
                return (v > ucl || v < lcl) ? 7 : 4;
            });

            return new Chart(canvas, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'داده',
                            data: series,
                            borderColor: '#059669',
                            backgroundColor: 'rgba(5, 150, 105, 0.08)',
                            pointBackgroundColor: pointColors,
                            pointBorderColor: pointColors,
                            pointRadius: pointRadii,
                            pointHoverRadius: 8,
                            borderWidth: 2,
                            tension: 0.2,
                            fill: false
                        },
                        {
                            label: 'UCL',
                            data: dataUcl,
                            borderColor: '#DC2626',
                            borderWidth: 1.5,
                            borderDash: [6, 4],
                            pointRadius: 0,
                            fill: false
                        },
                        {
                            label: 'CL',
                            data: dataCl,
                            borderColor: '#059669',
                            borderWidth: 1.5,
                            borderDash: [4, 4],
                            pointRadius: 0,
                            fill: false
                        },
                        {
                            label: 'LCL',
                            data: dataLcl,
                            borderColor: '#DC2626',
                            borderWidth: 1.5,
                            borderDash: [6, 4],
                            pointRadius: 0,
                            fill: false
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                font: { family: 'Tahoma' },
                                usePointStyle: true
                            }
                        },
                        title: {
                            display: !!title,
                            text: title || '',
                            font: { family: 'Tahoma', size: 14 }
                        }
                    },
                    scales: {
                        x: {
                            title: {
                                display: true,
                                text: 'زیرگروه',
                                font: { family: 'Tahoma' }
                            },
                            grid: { color: 'rgba(226, 232, 240, 0.6)' }
                        },
                        y: {
                            title: {
                                display: true,
                                text: 'مقدار',
                                font: { family: 'Tahoma' }
                            },
                            grid: { color: 'rgba(226, 232, 240, 0.6)' }
                        }
                    }
                }
            });
        }
    };

    // ═══════════════════════════════════════════════════
    // 📊 Dataset Data Editor
    // ═══════════════════════════════════════════════════
    function initDatasetDataEditor() {
        var container = document.getElementById('qc-data-editor');
        if (!container) return;

        var datasetId = parseInt(container.dataset.datasetId, 10);
        var isVariable = container.dataset.isVariable === '1';
        var isAttribute = container.dataset.isAttribute === '1';
        var saveUrl = container.dataset.saveUrl;

        // ─── افزودن ردیف
        var addBtn = document.getElementById('qc-add-subgroup');
        if (addBtn) {
            addBtn.addEventListener('click', function () {
                if (isVariable) {
                    addVarRow();
                } else if (isAttribute) {
                    addAttrRow();
                }
            });
        }

        // ─── حذف ردیف (event delegation)
        container.addEventListener('click', function (e) {
            var btn = e.target.closest('.qc-remove-row');
            if (btn) {
                var row = btn.closest('.qc-var-row, .qc-attr-row');
                if (row) row.remove();
            }
        });

        // ─── ذخیره
        var saveBtn = document.getElementById('qc-save-data');
        if (saveBtn) {
            saveBtn.addEventListener('click', function () {
                var rows = isVariable ? collectVariableRows() : collectAttributeRows();

                if (rows.length === 0) {
                    QC.toast('هیچ داده‌ای وارد نشده است', 'warning');
                    return;
                }

                saveBtn.disabled = true;
                saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> در حال ذخیره...';

                QC.post(saveUrl, { rows: rows })
                    .then(function (data) {
                        if (data.success) {
                            QC.toast('داده‌ها با موفقیت ذخیره شد', 'success');
                            setTimeout(function () { location.reload(); }, 800);
                        } else {
                            QC.toast('خطا: ' + (data.error || 'نامشخص'), 'danger');
                            saveBtn.disabled = false;
                            saveBtn.innerHTML = '<i class="fas fa-save"></i> ذخیره';
                        }
                    })
                    .catch(function (err) {
                        QC.toast('خطای شبکه: ' + err.message, 'danger');
                        saveBtn.disabled = false;
                        saveBtn.innerHTML = '<i class="fas fa-save"></i> ذخیره';
                    });
            });
        }

        // ─── توابع کمکی
        function addVarRow() {
            var div = document.createElement('div');
            div.className = 'qc-flex qc-gap-1 qc-mb-2 qc-var-row';
            div.innerHTML = '<input type="text" class="qc-form-control qc-sg-input" placeholder="مثلاً: 10.02, 9.98, 10.01">' +
                            '<button type="button" class="btn-qc-danger btn-sm qc-remove-row"><i class="fas fa-trash"></i></button>';
            document.getElementById('qc-var-rows').appendChild(div);
        }

        function addAttrRow() {
            var div = document.createElement('div');
            div.className = 'qc-flex qc-gap-1 qc-mb-2 qc-attr-row';
            div.innerHTML = '<input type="number" class="qc-form-control qc-attr-size" value="100" placeholder="تعداد نمونه">' +
                            '<input type="number" class="qc-form-control qc-attr-defectives" value="0" placeholder="تعداد معیوب">' +
                            '<input type="number" class="qc-form-control qc-attr-defects" value="0" placeholder="تعداد نقص">' +
                            '<button type="button" class="btn-qc-danger btn-sm qc-remove-row"><i class="fas fa-trash"></i></button>';
            document.getElementById('qc-attr-rows').appendChild(div);
        }

        function collectVariableRows() {
            var rows = [];
            var sgIndex = 1;

            document.querySelectorAll('.qc-var-row').forEach(function (row) {
                var input = row.querySelector('.qc-sg-input');
                var val = (input.value || '').trim();
                if (!val) return;

                var values = val.split(/[,\s،]+/)
                    .map(function (v) { return parseFloat(v); })
                    .filter(function (v) { return !isNaN(v); });

                if (values.length === 0) return;

                values.forEach(function (v, idx) {
                    rows.push({
                        subgroup_no: sgIndex,
                        sample_no: idx + 1,
                        value: v,
                        is_defective: 0,
                        defect_count: 0
                    });
                });
                sgIndex++;
            });

            return rows;
        }

        function collectAttributeRows() {
            var rows = [];
            var sgIndex = 1;

            document.querySelectorAll('.qc-attr-row').forEach(function (row) {
                var n = parseInt(row.querySelector('.qc-attr-size').value) || 0;
                var d = parseInt(row.querySelector('.qc-attr-defectives').value) || 0;
                var c = parseInt(row.querySelector('.qc-attr-defects').value) || 0;

                if (n <= 0) return;

                rows.push({
                    subgroup_no: sgIndex,
                    sample_no: 1,
                    value: 0,
                    is_defective: d,
                    defect_count: c
                });
                sgIndex++;
            });

            return rows;
        }
    }

    // ═══════════════════════════════════════════════════
    // 🎛 فرم دیتاست — نمایش/مخفی فیلدها
    // ═══════════════════════════════════════════════════
    document.addEventListener('DOMContentLoaded', function () {
        var chartSel = document.getElementById('qc-chart-type');
        if (!chartSel) return;

        var varFields = document.getElementById('qc-variable-fields');
        var specFields = document.getElementById('qc-spec-fields');

        var variableCharts = ['xbar_r', 'xbar_s', 'i_mr'];
        var attributeCharts = ['p', 'np', 'c', 'u'];

        function update() {
            var v = chartSel.value;
            if (variableCharts.indexOf(v) !== -1) {
                if (varFields) varFields.style.display = '';
                if (specFields) specFields.style.display = '';
            } else if (attributeCharts.indexOf(v) !== -1) {
                if (varFields) varFields.style.display = 'none';
                if (specFields) specFields.style.display = 'none';
            }
        }

        chartSel.addEventListener('change', update);
        update();
    });

    // ═══════════════════════════════════════════════════
    // 🎨 MSA Matrix Input
    // ═══════════════════════════════════════════════════
    document.addEventListener('DOMContentLoaded', function () {
        initMsaMatrix();
    });

    function initMsaMatrix() {
        var container = document.getElementById('qc-msa-matrix');
        if (!container) return;

        var partsInput     = document.getElementById('qc-msa-parts');
        var operatorsInput = document.getElementById('qc-msa-operators');
        var trialsInput    = document.getElementById('qc-msa-trials');

        var calcBtn = document.getElementById('qc-msa-calculate');

        /**
         * ساخت ماتریس بر اساس ابعاد فعلی
         */
        function buildMatrix() {
            var parts     = parseInt(container.dataset.parts, 10) || 0;
            var operators = parseInt(container.dataset.operators, 10) || 0;
            var trials    = parseInt(container.dataset.trials, 10) || 0;

            // ذخیره مقادیر قبلی برای حفظ داده‌ها
            var prevValues = {};
            container.querySelectorAll('.qc-msa-input').forEach(function (input) {
                var key = input.dataset.op + '_' + input.dataset.part + '_' + input.dataset.trial;
                if (input.value !== '') {
                    prevValues[key] = input.value;
                }
            });

            if (parts <= 0 || operators <= 0 || trials <= 0) {
                container.innerHTML = '<p class="qc-text-muted qc-text-center">' +
                                    'لطفاً تعداد قطعات، اپراتورها و تکرار را وارد کنید.</p>';
                return;
            }

            var html = '';

            for (var op = 0; op < operators; op++) {
                html += '<div class="qc-card qc-mb-2">';
                html += '<div class="qc-card-header"><h4 class="qc-card-title">' +
                        '<i class="fas fa-user"></i> اپراتور ' + (op + 1) + '</h4></div>';
                html += '<div class="qc-card-body" style="overflow-x:auto;">';
                html += '<table class="qc-table"><thead><tr><th>قطعه</th>';

                for (var t = 0; t < trials; t++) {
                    html += '<th>تکرار ' + (t + 1) + '</th>';
                }
                html += '</tr></thead><tbody>';

                for (var p = 0; p < parts; p++) {
                    html += '<tr><td><strong>#' + (p + 1) + '</strong></td>';
                    for (var t2 = 0; t2 < trials; t2++) {
                        var key = op + '_' + p + '_' + t2;
                        var prevVal = prevValues[key] || '';
                        html += '<td>' +
                                '<input type="number" step="any" class="qc-form-control qc-msa-input" ' +
                                'data-op="' + op + '" data-part="' + p + '" data-trial="' + t2 + '" ' +
                                'value="' + prevVal + '" placeholder="—">' +
                                '</td>';
                    }
                    html += '</tr>';
                }

                html += '</tbody></table></div></div>';
            }

            container.innerHTML = html;
        }

        // ─── ساخت اولیه
        buildMatrix();

        // ─── listener برای تغییر ابعاد
        function onDimensionChange() {
            var parts     = parseInt(partsInput.value, 10) || 0;
            var operators = parseInt(operatorsInput.value, 10) || 0;
            var trials    = parseInt(trialsInput.value, 10) || 0;

            // ذخیره در dataset
            container.dataset.parts     = parts;
            container.dataset.operators = operators;
            container.dataset.trials    = trials;

            buildMatrix();
        }

        if (partsInput)     partsInput.addEventListener('change', onDimensionChange);
        if (operatorsInput) operatorsInput.addEventListener('change', onDimensionChange);
        if (trialsInput)    trialsInput.addEventListener('change', onDimensionChange);

        // همچنین روی input (برای واکنش سریع‌تر)
        if (partsInput)     partsInput.addEventListener('input', onDimensionChange);
        if (operatorsInput) operatorsInput.addEventListener('input', onDimensionChange);
        if (trialsInput)    trialsInput.addEventListener('input', onDimensionChange);

        // ─── دکمه محاسبه
        if (calcBtn) {
            calcBtn.addEventListener('click', function () {
                var parts     = parseInt(partsInput.value, 10) || 0;
                var operators = parseInt(operatorsInput.value, 10) || 0;
                var trials    = parseInt(trialsInput.value, 10) || 0;

                if (parts < 2 || operators < 2 || trials < 2) {
                    QC.toast('حداقل ۲ قطعه، ۲ اپراتور و ۲ تکرار لازم است', 'warning');
                    return;
                }

                // جمع‌آوری داده‌ها
                var values = [];
                for (var op = 0; op < operators; op++) {
                    values[op] = [];
                    for (var p = 0; p < parts; p++) {
                        values[op][p] = [];
                        for (var t = 0; t < trials; t++) {
                            var input = container.querySelector(
                                '.qc-msa-input[data-op="' + op + '"][data-part="' + p + '"][data-trial="' + t + '"]'
                            );
                            var v = input ? parseFloat(input.value) : NaN;
                            if (isNaN(v)) {
                                QC.toast('لطفاً همه‌ی مقادیر ماتریس را وارد کنید (اپراتور ' +
                                        (op + 1) + '، قطعه ' + (p + 1) + '، تکرار ' + (t + 1) + ')', 'warning');
                                return;
                            }
                            values[op][p][t] = v;
                        }
                    }
                }

                var projectInput = document.getElementById('qc-msa-project');
                var nameInput    = document.getElementById('qc-msa-name');
                var methodInput  = document.getElementById('qc-msa-method');

                var formData = {
                    project_id:    projectInput ? projectInput.value : 0,
                    name:          nameInput    ? nameInput.value    : '',
                    method:        methodInput  ? methodInput.value  : 'anova',
                    num_parts:     parts,
                    num_operators: operators,
                    num_trials:    trials,
                    values:        values
                };

                if (!formData.project_id) {
                    QC.toast('لطفاً یک پروژه انتخاب کنید', 'warning');
                    return;
                }
                if (!formData.name) {
                    QC.toast('لطفاً نام مطالعه را وارد کنید', 'warning');
                    return;
                }

                calcBtn.disabled = true;
                calcBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> در حال محاسبه...';

                QC.post(window.CURRENT_MODULE_URL + '?controller=msa&action=store', formData)
                    .then(function (data) {
                        if (data.success) {
                            QC.toast('محاسبه با موفقیت انجام شد', 'success');
                            setTimeout(function () {
                                window.location.href = data.redirect;
                            }, 800);
                        } else {
                            QC.toast('خطا: ' + (data.error || 'نامشخص'), 'danger');
                            calcBtn.disabled = false;
                            calcBtn.innerHTML = '<i class="fas fa-calculator"></i> محاسبه Gage R&R';
                        }
                    })
                    .catch(function (err) {
                        QC.toast('خطای شبکه: ' + err.message, 'danger');
                        calcBtn.disabled = false;
                        calcBtn.innerHTML = '<i class="fas fa-calculator"></i> محاسبه Gage R&R';
                    });
            });
        }
    }

    // ═══════════════════════════════════════════════════
    // 🧪 Sampling — فرم ایجاد
    // ═══════════════════════════════════════════════════
    document.addEventListener('DOMContentLoaded', function () {
        var saveBtn = document.getElementById('qc-sample-save');
        if (!saveBtn) return;

        saveBtn.addEventListener('click', function () {
            var nVal = document.getElementById('qc-sample-n').value.trim();
            var cVal = document.getElementById('qc-sample-c').value.trim();

            var formData = {
                name:         document.getElementById('qc-sample-name').value.trim(),
                project_id:   document.getElementById('qc-sample-project').value || null,
                plan_type:    document.getElementById('qc-sample-type').value,
                lot_size:     parseInt(document.getElementById('qc-sample-lot').value) || 0,
                aql:          parseFloat(document.getElementById('qc-sample-aql').value) || 0,
                ltpd:         parseFloat(document.getElementById('qc-sample-ltpd').value) || 0,
                alpha:        parseFloat(document.getElementById('qc-sample-alpha').value) || 0.05,
                beta:         parseFloat(document.getElementById('qc-sample-beta').value) || 0.10,
                description:  document.getElementById('qc-sample-desc').value.trim(),
                notes:        document.getElementById('qc-sample-notes').value.trim()
            };

            if (nVal !== '') formData.sample_size   = parseInt(nVal);
            if (cVal !== '') formData.accept_number = parseInt(cVal);

            if (!formData.name) {
                QC.toast('نام طرح الزامی است', 'warning');
                return;
            }
            if (formData.lot_size <= 0) {
                QC.toast('حجم دسته باید مثبت باشد', 'warning');
                return;
            }
            if (formData.aql <= 0 || formData.ltpd <= 0) {
                QC.toast('AQL و LTPD باید مثبت باشند', 'warning');
                return;
            }
            if (formData.ltpd <= formData.aql) {
                QC.toast('LTPD باید بزرگ‌تر از AQL باشد', 'warning');
                return;
            }

            saveBtn.disabled = true;
            saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> در حال محاسبه...';

            QC.post(window.CURRENT_MODULE_URL + '?controller=sampling&action=store', formData)
                .then(function (data) {
                    if (data.success) {
                        QC.toast('طرح با موفقیت ذخیره شد', 'success');
                        setTimeout(function () {
                            window.location.href = data.redirect;
                        }, 800);
                    } else {
                        QC.toast('خطا: ' + (data.error || 'نامشخص'), 'danger');
                        saveBtn.disabled = false;
                        saveBtn.innerHTML = '<i class="fas fa-save"></i> محاسبه و ذخیره';
                    }
                })
                .catch(function (err) {
                    QC.toast('خطای شبکه: ' + err.message, 'danger');
                    saveBtn.disabled = false;
                    saveBtn.innerHTML = '<i class="fas fa-save"></i> محاسبه و ذخیره';
                });
        });
    });

    // ═══════════════════════════════════════════════════
    // 🧪 Sampling — منحنی OC (Chart.js)
    // ═══════════════════════════════════════════════════
    document.addEventListener('DOMContentLoaded', function () {
        var canvas = document.getElementById('qc-oc-canvas');
        if (!canvas) return;

        var ocCurve = window.QC_OC_CURVE;
        if (!ocCurve || ocCurve.length === 0) return;

        if (typeof Chart === 'undefined') {
            console.error('Chart.js not loaded');
            return;
        }

        var labels = ocCurve.map(function (pt) {
            return (pt.p * 100).toFixed(1);
        });
        var paData = ocCurve.map(function (pt) { return pt.pa; });

        new Chart(canvas, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'احتمال پذیرش Pa',
                    data: paData,
                    borderColor: '#059669',
                    backgroundColor: 'rgba(5, 150, 105, 0.1)',
                    borderWidth: 2.5,
                    pointRadius: 0,
                    tension: 0.3,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            font: { family: 'Tahoma' }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            title: function (items) {
                                return 'p = ' + items[0].label + '%';
                            },
                            label: function (item) {
                                return 'Pa = ' + (item.parsed.y * 100).toFixed(2) + '%';
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        title: {
                            display: true,
                            text: 'نسبت معیوب در دسته (p %)',
                            font: { family: 'Tahoma' }
                        },
                        ticks: { maxTicksLimit: 11 }
                    },
                    y: {
                        title: {
                            display: true,
                            text: 'احتمال پذیرش Pa',
                            font: { family: 'Tahoma' }
                        },
                        min: 0,
                        max: 1
                    }
                }
            }
        });
    });

    // ═══════════════════════════════════════════════════
    // 📊 نمودار کنترل — رندر خودکار
    // ═══════════════════════════════════════════════════
    document.addEventListener('DOMContentLoaded', function () {
        var canvas = document.getElementById('qc-chart-canvas');
        if (!canvas) return;

        var seriesData = canvas.dataset.series;
        if (!seriesData) return;

        try {
            var series = JSON.parse(seriesData);
            var cl = parseFloat(canvas.dataset.cl) || 0;
            var ucl = parseFloat(canvas.dataset.ucl) || 0;
            var lcl = parseFloat(canvas.dataset.lcl) || 0;
            var title = canvas.dataset.title || '';

            QC.renderChart('qc-chart-canvas', series, cl, ucl, lcl, title);
        } catch (e) {
            console.error('Chart render error:', e);
        }
    });

})();
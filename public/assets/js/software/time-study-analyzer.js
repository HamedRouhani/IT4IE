/* Time Study Analyzer — لایهٔ ورود داده و کنترل‌های فرم.
 * بدون وابستگی خارجی؛ هر بخش مستقل و خطاپذیر است تا خطای یک قابلیت بقیهٔ صفحه را از کار نیندازد.
 * بخش‌های ۱ تا ۳ رفتار اصلی فرم‌ها هستند و نباید حذف شوند؛ ۴ به بعد بهبود تجربهٔ کاربر است.
 */
(() => {
    'use strict';

    const warn = (label, error) => {
        if (window.console && console.warn) console.warn('[time-study] ' + label, error);
    };

    /* اجرای ایمن: خطای هر بخش فقط در کنسول ثبت می‌شود. */
    const safe = (label, task) => {
        try {
            task();
        } catch (error) {
            warn(label, error);
        }
    };

    const PERSIAN_DIGITS = '۰۱۲۳۴۵۶۷۸۹';
    const ARABIC_DIGITS = '٠١٢٣٤٥٦٧٨٩';

    /* تبدیل ارقام فارسی/عربی و ممیز فارسی به معادل لاتین. */
    const latinDigits = (value) => String(value)
        .replace(/[۰-۹]/g, (digit) => String(PERSIAN_DIGITS.indexOf(digit)))
        .replace(/[٠-٩]/g, (digit) => String(ARABIC_DIGITS.indexOf(digit)))
        .replace(/[٫،]/g, '.');

    /* ==================================================================
     * ۱) افزودن و حذف ردیف در فرم تعریف مطالعه (عناصر کار / دسته‌ها)
     * ================================================================== */
    safe('repeat-rows', () => {
        document.querySelectorAll('[data-ts-add]').forEach((button) => {
            button.addEventListener('click', () => {
                const kind = button.dataset.tsAdd;
                const list = document.querySelector('[data-ts-repeat="' + kind + '"]');
                if (!list || list.querySelectorAll('[data-ts-row]').length >= 100) return;

                const row = document.createElement('div');
                row.className = 'ts-repeat-row' + (kind === 'categories' ? ' ts-repeat-row--category' : '');
                row.dataset.tsRow = '';
                row.innerHTML = kind === 'elements'
                    ? '<label>نام عنصر *<input name="element_name[]" maxlength="180" required></label><label>کد<input name="element_code[]" maxlength="32"></label><label>نوع عنصر<select name="element_type[]"><option value="manual">دستی</option><option value="machine">ماشین</option></select></label><label>تکرار در هر واحد<input name="element_frequency[]" type="number" min="0.0001" max="10000" step="0.0001" value="1" required></label><label>یادداشت<input name="element_note[]" maxlength="500"></label><button type="button" class="ts-icon-button" data-ts-remove aria-label="حذف عنصر"><i class="fa-solid fa-trash"></i></button>'
                    : '<label>نام دسته *<input name="category_name[]" maxlength="120" required></label><label>طبقه<select name="category_type[]"><option value="productive">مولد</option><option value="machine">کار ماشین</option><option value="personal">نیاز شخصی</option><option value="delay">توقف / انتظار</option><option value="other">سایر</option></select></label><button type="button" class="ts-icon-button" data-ts-remove aria-label="حذف دسته"><i class="fa-solid fa-trash"></i></button>';

                list.append(row);
                const first = row.querySelector('input, select');
                if (first) first.focus();
            });
        });

        document.addEventListener('click', (event) => {
            const remove = event.target.closest('[data-ts-remove]');
            if (!remove) return;
            const list = remove.closest('[data-ts-repeat]');
            if (list && list.querySelectorAll('[data-ts-row]').length > 1) {
                remove.closest('[data-ts-row]').remove();
            }
        });
    });

    /* ==================================================================
     * ۲) هماهنگی وضعیت‌ها: چرخهٔ کنارگذاشته، اعتبار عنصر، وضعیت زمان نمونه
     * ================================================================== */
    safe('cycle-status', () => {
        document.querySelectorAll('[data-ts-cycle-status]').forEach((select) => {
            const sync = () => {
                const reason = document.querySelector('[data-ts-cycle-reason]');
                if (!reason) return;
                reason.hidden = select.value !== 'excluded';
                const input = reason.querySelector('input');
                if (input) input.required = select.value === 'excluded';
            };
            select.addEventListener('change', sync);
            sync();
        });
    });

    safe('element-validity', () => {
        document.querySelectorAll('[data-ts-valid]').forEach((select) => {
            const sync = () => {
                const row = select.closest('tr');
                const field = row ? row.querySelector('input[name$="[reason]"]') : null;
                if (field) field.required = select.value === '0';
            };
            select.addEventListener('change', sync);
            sync();
        });
    });

    safe('slot-status', () => {
        document.querySelectorAll('[data-ts-slot-status]').forEach((select) => {
            const form = select.closest('form');
            const sync = () => {
                const category = form ? form.querySelector('[data-ts-category]') : null;
                const note = form ? form.querySelector('input[name="note"]') : null;
                const observed = select.value === 'observed';
                if (category) {
                    category.disabled = !observed;
                    category.required = observed;
                }
                if (note) note.required = !observed;
            };
            select.addEventListener('change', sync);
            sync();
        });
    });

    /* ==================================================================
     * ۳) تأیید عملیات حساس (آغاز، نهایی‌سازی، بایگانی، ...)
     *    باید پیش از هندلر ضد ارسال دوباره ثبت شود.
     * ================================================================== */
    safe('confirm-actions', () => {
        document.querySelectorAll('[data-ts-confirm]').forEach((form) => {
            form.addEventListener('submit', (event) => {
                if (!window.confirm(form.dataset.tsConfirm)) event.preventDefault();
            });
        });
    });

    /* ==================================================================
     * ۴) تبدیل ارقام فارسی/عربی در فیلدهای عددی و زمانی
     * ================================================================== */
    safe('digits', () => {
        const selector = 'input[inputmode="decimal"], input[inputmode="numeric"], input[type="number"], input[type="time"]';
        document.querySelectorAll(selector).forEach((input) => {
            const normalize = () => {
                const normalized = latinDigits(input.value);
                if (normalized === input.value) return;
                try {
                    input.value = normalized;
                } catch (error) {
                    /* مقدار برای این نوع فیلد قابل تبدیل نیست؛ رها می‌شود. */
                }
            };
            input.addEventListener('input', normalize);
            input.addEventListener('blur', normalize);
        });
    });

    /* ==================================================================
     * ۵) Enter در جدول ثبت چرخه به فیلد بعدی می‌رود (نه ارسال ناقص فرم)
     * ================================================================== */
    safe('enter-navigation', () => {
        document.querySelectorAll('[data-ts-enter-nav]').forEach((scope) => {
            scope.addEventListener('keydown', (event) => {
                if (event.key !== 'Enter' || event.shiftKey || event.ctrlKey || event.metaKey || event.altKey) return;

                const field = event.target.closest('input, select');
                if (!field || field.tagName === 'TEXTAREA') return;

                const fields = Array.from(scope.querySelectorAll('input:not([type="hidden"]), select'))
                    .filter((item) => !item.disabled && item.offsetParent !== null);

                const index = fields.indexOf(field);
                if (index === -1) return;

                event.preventDefault();

                if (index + 1 < fields.length) {
                    const next = fields[index + 1];
                    next.focus();
                    if (next.tagName === 'INPUT' && typeof next.select === 'function') next.select();
                } else {
                    const form = scope.closest('form');
                    const submit = form ? form.querySelector('button[type="submit"]') : null;
                    if (submit) submit.focus();
                }
            });
        });
    });

    /* ==================================================================
     * ۶) کپی زمان‌ها و rating از آخرین چرخه
     * ================================================================== */
    safe('copy-previous-cycle', () => {
        const form = document.querySelector('[data-ts-cycle-form]');
        const dataElement = document.querySelector('[data-ts-last-cycle]');
        const button = document.querySelector('[data-ts-copy-previous]');
        if (!form || !dataElement || !button) return;

        let previous = {};
        try {
            previous = JSON.parse(dataElement.textContent || '{}') || {};
        } catch (error) {
            previous = {};
        }

        const elementIds = Object.keys(previous);
        if (!elementIds.length) {
            button.disabled = true;
            button.title = 'هنوز چرخه‌ای ثبت نشده است.';
            return;
        }

        button.addEventListener('click', () => {
            elementIds.forEach((elementId) => {
                const row = previous[elementId] || {};
                const seconds = form.querySelector('input[name="observations[' + elementId + '][seconds]"]');
                const rating = form.querySelector('input[name="observations[' + elementId + '][rating]"]');
                if (seconds && row.seconds !== undefined) seconds.value = String(row.seconds);
                if (rating && row.rating !== undefined) rating.value = String(row.rating);
            });

            const first = form.querySelector('input[name$="[seconds]"]');
            if (first) first.focus();
        });
    });

    /* ==================================================================
     * ۷) چسباندن دسته‌ای زمان‌ها به‌ترتیب عناصر
     * ================================================================== */
    safe('paste-values', () => {
        const form = document.querySelector('[data-ts-cycle-form]');
        const box = document.querySelector('[data-ts-paste]');
        const apply = document.querySelector('[data-ts-paste-apply]');
        if (!form || !box || !apply) return;

        const applyValues = () => {
            const parts = (box.value || '')
                .split(/[\s,;،؛]+/)
                .map((part) => latinDigits(part.trim()))
                .filter((part) => part !== '');

            if (!parts.length) return;

            const inputs = Array.from(form.querySelectorAll('input[name$="[seconds]"]'));
            inputs.forEach((input, index) => {
                if (parts[index] === undefined) return;
                input.value = parts[index];
            });

            box.value = '';
            const lastFilled = Math.min(parts.length, inputs.length) - 1;
            if (lastFilled >= 0) inputs[lastFilled].focus();
        };

        apply.addEventListener('click', applyValues);
        box.addEventListener('keydown', (event) => {
            if (event.key === 'Enter' && (event.ctrlKey || event.metaKey)) {
                event.preventDefault();
                applyValues();
            }
        });
    });

    /* ==================================================================
     * ۸) ساخت دسته‌ای عناصر از متن (هر خط یک عنصر)
     * ================================================================== */
    safe('elements-bulk', () => {
        const box = document.querySelector('[data-ts-elements-bulk]');
        const apply = document.querySelector('[data-ts-elements-bulk-apply]');
        const list = document.querySelector('[data-ts-repeat="elements"]');
        const addButton = document.querySelector('[data-ts-add="elements"]');
        if (!box || !apply || !list || !addButton) return;

        apply.addEventListener('click', () => {
            const names = (box.value || '')
                .split(/\r?\n/)
                .map((line) => line.trim())
                .filter(Boolean)
                .slice(0, 100);

            if (!names.length) return;

            /* ساخت ردیف‌های لازم با سقف ایمن تا هرگز حلقهٔ بی‌پایان رخ ندهد. */
            let guard = 0;
            while (list.querySelectorAll('[data-ts-row]').length < names.length && guard < 120) {
                const before = list.querySelectorAll('[data-ts-row]').length;
                addButton.click();
                guard++;
                if (list.querySelectorAll('[data-ts-row]').length === before) break;
            }

            const rows = Array.from(list.querySelectorAll('[data-ts-row]'));
            rows.forEach((row, index) => {
                if (names[index] === undefined) return;
                const input = row.querySelector('input[name="element_name[]"]');
                if (input) input.value = names[index];
            });

            box.value = '';
        });
    });

    /* ==================================================================
     * ۹) یادآوری نام مشاهده‌گر برای همهٔ فرم‌های ثبت
     * ================================================================== */
    safe('observer-memory', () => {
        const storageKey = 'ts:observer-name';
        const inputs = Array.from(document.querySelectorAll('input[name="observer_name"]'));
        if (!inputs.length) return;

        let saved = '';
        try {
            saved = window.localStorage.getItem(storageKey) || '';
        } catch (error) {
            saved = '';
        }

        if (saved) {
            inputs.forEach((input) => {
                if (!input.value) input.value = saved;
            });
        }

        const remember = () => {
            const filled = inputs.find((input) => input.value && input.value.trim() !== '');
            if (!filled) return;
            try {
                window.localStorage.setItem(storageKey, filled.value.trim());
            } catch (error) {
                /* مرور خصوصی یا حافظهٔ مسدود؛ بی‌اهمیت است. */
            }
        };

        document.querySelectorAll('form').forEach((form) => {
            form.addEventListener('submit', remember);
        });
    });

    /* ==================================================================
     * ۱۰) پس از ثبت، به همان بخشی برگرد که کاربر در آن بوده
     * ================================================================== */
    safe('hash-focus', () => {
        const focusFirst = (container, selector) => {
            if (!container) return;
            container.scrollIntoView({ behavior: 'smooth', block: 'center' });
            const field = container.querySelector(selector);
            if (!field) return;
            window.setTimeout(() => {
                try {
                    field.focus({ preventScroll: true });
                } catch (error) {
                    field.focus();
                }
            }, 250);
        };

        if (window.location.hash === '#cycle-form') {
            focusFirst(document.querySelector('[data-ts-cycle-form]'), 'input[name$="[seconds]"]');
        }

        if (window.location.hash === '#sampling-queue') {
            focusFirst(document.querySelector('[data-ts-sampling-queue]'), '[data-ts-category]');
        }
    });

    /* ==================================================================
     * ۱۱) جلوگیری از ارسال دوبارهٔ فرم با دوبار کلیک
     * ================================================================== */
    safe('double-submit', () => {
        document.querySelectorAll('[data-ts-form], [data-ts-sample-form]').forEach((form) => {
            form.addEventListener('submit', (event) => {
                const submit = event.submitter || form.querySelector('button[type="submit"]');
                window.setTimeout(() => {
                    if (event.defaultPrevented || !submit) return;
                    submit.disabled = true;
                }, 0);
            });
        });
    });
})();

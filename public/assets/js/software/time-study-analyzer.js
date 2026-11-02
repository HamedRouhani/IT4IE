(() => {
    'use strict';
    document.querySelectorAll('[data-ts-add]').forEach((button) => {
        button.addEventListener('click', () => {
            const kind = button.dataset.tsAdd;
            const list = document.querySelector(`[data-ts-repeat="${kind}"]`);
            if (!list || list.querySelectorAll('[data-ts-row]').length >= 100) return;
            const row = document.createElement('div');
            row.className = `ts-repeat-row${kind === 'categories' ? ' ts-repeat-row--category' : ''}`;
            row.dataset.tsRow = '';
            row.innerHTML = kind === 'elements'
                ? '<label>نام عنصر *<input name="element_name[]" maxlength="180" required></label><label>کد<input name="element_code[]" maxlength="32"></label><label>نوع عنصر<select name="element_type[]"><option value="manual">دستی</option><option value="machine">ماشین</option></select></label><label>تکرار در هر واحد<input name="element_frequency[]" type="number" min="0.0001" max="10000" step="0.0001" value="1" required></label><label>یادداشت<input name="element_note[]" maxlength="500"></label><button type="button" class="ts-icon-button" data-ts-remove aria-label="حذف عنصر"><i class="fa-solid fa-trash"></i></button>'
                : '<label>نام دسته *<input name="category_name[]" maxlength="120" required></label><label>طبقه<select name="category_type[]"><option value="productive">مولد</option><option value="machine">کار ماشین</option><option value="personal">نیاز شخصی</option><option value="delay">توقف / انتظار</option><option value="other">سایر</option></select></label><button type="button" class="ts-icon-button" data-ts-remove aria-label="حذف دسته"><i class="fa-solid fa-trash"></i></button>';
            list.append(row);
            row.querySelector('input')?.focus();
        });
    });

    document.addEventListener('click', (event) => {
        const remove = event.target.closest('[data-ts-remove]');
        if (remove) {
            const list = remove.closest('[data-ts-repeat]');
            if (list && list.querySelectorAll('[data-ts-row]').length > 1) remove.closest('[data-ts-row]').remove();
        }
    });

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

    document.querySelectorAll('[data-ts-valid]').forEach((select) => {
        const sync = () => {
            const field = select.closest('tr')?.querySelector('input[name$="[reason]"]');
            if (field) field.required = select.value === '0';
        };
        select.addEventListener('change', sync);
        sync();
    });

    document.querySelectorAll('[data-ts-slot-status]').forEach((select) => {
        const form = select.closest('form');
        const sync = () => {
            const category = form?.querySelector('[data-ts-category]');
            const note = form?.querySelector('input[name="note"]');
            const observed = select.value === 'observed';
            if (category) { category.disabled = !observed; category.required = observed; }
            if (note) note.required = !observed;
        };
        select.addEventListener('change', sync);
        sync();
    });

    document.querySelectorAll('[data-ts-confirm]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            if (!window.confirm(form.dataset.tsConfirm)) event.preventDefault();
        });
    });
})();

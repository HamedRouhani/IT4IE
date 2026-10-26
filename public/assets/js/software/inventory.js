(function () {
    'use strict';

    const movementType = document.querySelector('[data-inv-movement-type]');
    const adjustment = document.querySelector('[data-inv-adjustment]');

    function syncAdjustmentField() {
        if (!movementType || !adjustment) return;
        const visible = movementType.value === 'adjustment';
        adjustment.hidden = !visible;
        const select = adjustment.querySelector('select');
        if (select) select.disabled = !visible;
    }

    if (movementType) {
        movementType.addEventListener('change', syncAdjustmentField);
        syncAdjustmentField();
    }

    document.querySelectorAll('[data-inv-confirm]').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            const message = form.getAttribute('data-inv-confirm') || 'ادامه می‌دهید؟';
            if (!window.confirm(message)) event.preventDefault();
        });
    });

    document.querySelectorAll('[data-inv-form], [data-inv-movement-form]').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!form.reportValidity()) {
                event.preventDefault();
                return;
            }
            const submit = form.querySelector('button[type="submit"]');
            if (submit) {
                submit.disabled = true;
                submit.setAttribute('aria-busy', 'true');
            }
        });
    });
}());

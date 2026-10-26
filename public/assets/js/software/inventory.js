(function () {
    'use strict';

    const movementType = document.querySelector('[data-inv-movement-type]');
    const adjustment = document.querySelector('[data-inv-adjustment]');
    const orderReceipt = document.querySelector('[data-inv-order-receipt]');

    function syncAdjustmentField() {
        if (!movementType) return;
        if (adjustment) {
            const visible = movementType.value === 'adjustment';
            adjustment.hidden = !visible;
            const select = adjustment.querySelector('select');
            if (select) select.disabled = !visible;
        }
        if (orderReceipt) {
            const isReceipt = movementType.value === 'receipt';
            orderReceipt.hidden = !isReceipt;
            const input = orderReceipt.querySelector('input');
            if (input) {
                input.disabled = !isReceipt;
                const quantity = document.querySelector('[data-inv-quantity]');
                if (quantity) input.max = quantity.value || '';
            }
        }
    }

    if (movementType) {
        movementType.addEventListener('change', syncAdjustmentField);
        syncAdjustmentField();
    }

    const movementQuantity = document.querySelector('[data-inv-quantity]');
    if (movementQuantity) movementQuantity.addEventListener('input', syncAdjustmentField);

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

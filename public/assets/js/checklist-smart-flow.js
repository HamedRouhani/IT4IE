(() => {
    const form = document.querySelector('#checklistForm');
    if (!form) return;

    const steps = Array.from(form.querySelectorAll('[data-smart-step]'));
    const back = form.querySelector('[data-smart-back]');
    const next = form.querySelector('[data-smart-next]');
    const controls = form.querySelector('[data-smart-controls]');
    const submit = form.querySelector('[data-smart-submit]');
    const label = form.querySelector('#smartFlowStepLabel');
    const percent = form.querySelector('#smartFlowPercent');
    const bar = form.querySelector('#smartFlowBar');
    if (!steps.length || !back || !next || !submit) return;

    let current = 0;
    const toFa = value => String(value).replace(/[0-9]/g, digit => '۰۱۲۳۴۵۶۷۸۹'[digit]);

    function render() {
        steps.forEach((step, index) => {
            step.hidden = index !== current;
        });
        form.querySelectorAll('.question-category').forEach(category => {
            category.hidden = !category.querySelector('[data-smart-step]:not([hidden])');
        });
        back.hidden = current === 0;
        next.hidden = current === steps.length - 1;
        controls.hidden = false;
        submit.hidden = current !== steps.length - 1;

        const answered = steps.filter(step => step.querySelector('input[type="radio"]:checked')).length;
        const completion = Math.round(answered / steps.length * 100);
        label.textContent = `گام ${toFa(current + 1)} از ${toFa(steps.length)}`;
        percent.textContent = `${toFa(completion)}٪ تکمیل شده`;
        bar.style.width = `${completion}%`;
        steps[current].scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    next.addEventListener('click', () => {
        const selected = steps[current].querySelector('input[type="radio"]:checked');
        if (!selected) {
            const first = steps[current].querySelector('input[type="radio"]');
            if (first) {
                first.focus();
                steps[current].classList.add('smart-flow-needs-answer');
                window.setTimeout(() => steps[current]?.classList.remove('smart-flow-needs-answer'), 900);
            }
            return;
        }
        if (current < steps.length - 1) {
            current += 1;
            render();
        }
    });

    back.addEventListener('click', () => {
        if (current > 0) {
            current -= 1;
            render();
        }
    });

    form.addEventListener('change', event => {
        if (event.target.matches('input[type="radio"]')) {
            steps[current].classList.remove('smart-flow-needs-answer');
            const answered = steps.filter(step => step.querySelector('input[type="radio"]:checked')).length;
            const completion = Math.round(answered / steps.length * 100);
            percent.textContent = `${toFa(completion)}٪ تکمیل شده`;
            bar.style.width = `${completion}%`;
        }
    });

    render();
})();

(() => {
    const form = document.querySelector('#checklistForm');
    if (!form) return;

    const steps = Array.from(form.querySelectorAll('[data-smart-step]')).sort((a, b) => {
        const orderDiff = Number(a.dataset.sortOrder) - Number(b.dataset.sortOrder);
        return orderDiff || Number(a.dataset.questionId) - Number(b.dataset.questionId);
    });
    const back = form.querySelector('[data-smart-back]');
    const next = form.querySelector('[data-smart-next]');
    const controls = form.querySelector('[data-smart-controls]');
    const submit = form.querySelector('[data-smart-submit]');
    const label = form.querySelector('#smartFlowStepLabel');
    const percent = form.querySelector('#smartFlowPercent');
    const bar = form.querySelector('#smartFlowBar');
    if (!steps.length || !back || !next || !submit) return;

    const byId = new Map(steps.map(step => [String(step.dataset.questionId), step]));
    const toFa = value => String(value).replace(/[0-9]/g, digit => '۰۱۲۳۴۵۶۷۸۹'[digit]);
    let route = [steps[0]];
    let currentIndex = 0;
    let complete = false;

    function getRules(step) {
        try { return JSON.parse(step.dataset.branchRules || '{}'); }
        catch (_) { return {}; }
    }

    function selectedAnswer(step) {
        return step.querySelector('input[type="radio"]:checked')?.value ?? null;
    }

    function targetFor(step, answer, seenRoute = route) {
        const target = getRules(step)[String(answer)] ?? 'next';
        if (target === 'end') return { end: true };
        if (target !== 'next') {
            const targetStep = byId.get(String(target));
            if (targetStep && !seenRoute.includes(targetStep)) return { step: targetStep };
        }

        const stepOrder = steps.indexOf(step);
        return { step: steps.slice(stepOrder + 1).find(candidate => !seenRoute.includes(candidate)) || null };
    }

    function updateHint() {
        const hint = form.querySelector('#smartFlowHint');
        const answer = selectedAnswer(route[currentIndex]);
        if (!hint) return;
        if (answer === null) {
            hint.textContent = 'با انتخاب پاسخ، مسیر متناسب با آن مشخص می‌شود.';
            return;
        }
        const target = targetFor(route[currentIndex], answer, route.slice(0, currentIndex + 1));
        hint.textContent = target.end || !target.step
            ? 'با این پاسخ، ارزیابی همین‌جا به پایان می‌رسد.'
            : 'بر اساس این پاسخ، پرسش مرتبط بعدی انتخاب می‌شود.';
    }

    function render() {
        const active = new Set(route);
        steps.forEach(step => {
            step.hidden = step !== route[currentIndex];
            step.querySelectorAll('input[type="radio"]').forEach(input => { input.disabled = !active.has(step); });
        });
        form.querySelectorAll('.question-category').forEach(category => {
            category.hidden = !category.querySelector('[data-smart-step]:not([hidden])');
        });

        back.hidden = currentIndex === 0;
        next.hidden = complete;
        controls.hidden = false;
        submit.hidden = !complete;

        const answered = route.filter(step => selectedAnswer(step) !== null).length;
        const progress = complete ? 100 : Math.round(answered / steps.length * 100);
        label.textContent = complete ? 'مسیر کامل شد' : `گام ${toFa(currentIndex + 1)} از مسیر`;
        percent.textContent = `${toFa(answered)} پاسخ ثبت شده`;
        bar.style.width = `${progress}%`;
        updateHint();
        route[currentIndex].scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    next.addEventListener('click', () => {
        const current = route[currentIndex];
        const answer = selectedAnswer(current);
        if (answer === null) {
            const first = current.querySelector('input[type="radio"]');
            first?.focus();
            current.classList.add('smart-flow-needs-answer');
            window.setTimeout(() => current.classList.remove('smart-flow-needs-answer'), 900);
            return;
        }

        route = route.slice(0, currentIndex + 1);
        const target = targetFor(current, answer, route);
        if (target.end || !target.step) {
            complete = true;
        } else {
            route.push(target.step);
            currentIndex += 1;
        }
        render();
    });

    back.addEventListener('click', () => {
        if (currentIndex > 0) {
            currentIndex -= 1;
            complete = false;
            render();
        }
    });

    form.addEventListener('change', event => {
        if (event.target.matches('input[type="radio"]')) {
            route[currentIndex].classList.remove('smart-flow-needs-answer');
            updateHint();
            const answered = route.filter(step => selectedAnswer(step) !== null).length;
            percent.textContent = `${toFa(answered)} پاسخ ثبت شده`;
            bar.style.width = `${Math.round(answered / steps.length * 100)}%`;
        }
    });

    render();
})();

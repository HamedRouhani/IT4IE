document.addEventListener('DOMContentLoaded', function () {

    const form = document.getElementById('problemSolverForm');
    const input = document.getElementById('problemSolverInput');
    const button = document.getElementById('problemSolverSubmit');
    const loading = document.getElementById('problemSolverLoading');

    const modal = document.getElementById('problem-solver-modal');
    const backdrop = document.querySelector('.problem-solver-modal-backdrop');

    const closeButton = document.getElementById(
        'problem-solver-modal-close'
    );

    const closeFooterButton = document.getElementById(
        'problem-solver-modal-close-footer'
    );

    const resultText = document.getElementById(
        'problem-result-text'
    );

    const resultDomains = document.getElementById(
        'problem-result-domains'
    );

    const resultPath = document.getElementById(
        'problem-result-path'
    );

    const resultMethods = document.getElementById(
        'problem-result-methods'
    );

    const resultQuestions = document.getElementById(
        'problem-result-questions'
    );
    const refineButton = document.getElementById('problem-result-refine');
    const questionsDetails = document.getElementById('problem-result-questions-details');
    const resultSubtitle = document.getElementById('problem-result-subtitle');
    const resultMatchBadge = document.getElementById('problem-result-match-badge');
    const resultEvidence = document.getElementById('problem-result-evidence');
    const moduleAlternatives = document.getElementById('problem-result-module-alternatives');
    const resultNotesDetails = document.getElementById('problem-result-notes-details');
    const resultGuidanceNotes = document.getElementById('problem-result-guidance-notes');

    const resultMessage = document.getElementById(
        'problem-result-message'
    );

    const moduleSection = document.getElementById(
        'problem-result-module-section'
    );

    const moduleTitle = document.getElementById(
        'problem-result-module-title'
    );

    const moduleLink = document.getElementById(
        'problem-result-module-link'
    );


    // =====================================================
    // بررسی وجود فرم
    // =====================================================

    if (!form) {
        return;
    }


    // =====================================================
    // Escape HTML
    // =====================================================

    function escapeHtml(value) {

        if (value === null || value === undefined) {
            return '';
        }

        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }


    // =====================================================
    // باز کردن Modal
    // =====================================================

    function openModal() {

        if (!modal) {
            return;
        }

        modal.classList.add('is-open');

        modal.setAttribute(
            'aria-hidden',
            'false'
        );

        document.body.classList.add(
            'problem-solver-modal-open'
        );

        setTimeout(function () {

            if (closeButton) {
                closeButton.focus();
            }

        }, 100);
    }


    // =====================================================
    // بستن Modal
    // =====================================================

    function closeModal() {

        if (!modal) {
            return;
        }

        modal.classList.remove('is-open');

        modal.setAttribute(
            'aria-hidden',
            'true'
        );

        document.body.classList.remove(
            'problem-solver-modal-open'
        );
    }


    // =====================================================
    // Event های Modal
    // =====================================================

    if (closeButton) {

        closeButton.addEventListener(
            'click',
            closeModal
        );
    }


    if (closeFooterButton) {

        closeFooterButton.addEventListener(
            'click',
            closeModal
        );
    }


    if (backdrop) {

        backdrop.addEventListener(
            'click',
            closeModal
        );
    }


    // ESC
    document.addEventListener(
        'keydown',
        function (event) {

            if (
                event.key === 'Escape' &&
                modal &&
                modal.classList.contains('is-open')
            ) {
                closeModal();
            }

        }
    );


    // =====================================================
    // Loading
    // =====================================================

    function setLoading(isLoading) {

        if (!button) {
            return;
        }

        button.disabled = isLoading;

        if (loading) {
            loading.hidden = !isLoading;
        }

        if (isLoading) {

            button.innerHTML = `
                <i class="fas fa-spinner fa-spin"></i>
                در حال تحلیل...
            `;

        } else {

            button.innerHTML = `
                <i class="fas fa-wand-magic-sparkles"></i>
                تحلیل اولیه مسئله
            `;
        }
    }


    // =====================================================
    // نمایش حوزه مسئله
    // =====================================================

    function renderDomains(domains) {

        if (!resultDomains) {
            return;
        }

        if (
            !Array.isArray(domains) ||
            domains.length === 0
        ) {

            resultDomains.innerHTML = `
                <div class="problem-result-empty">
                    برای تشخیص دقیق حوزه، اطلاعات بیشتری از مسئله لازم است.
                </div>
            `;

            return;
        }


        resultDomains.innerHTML = domains.map(
            function (domain) {

                return `
                    <div class="problem-domain-card">

                        <div class="problem-domain-icon">
                            <i class="fas fa-bullseye"></i>
                        </div>

                        <div class="problem-domain-name">
                            ${escapeHtml(domain)}
                        </div>

                    </div>
                `;

            }
        ).join('');
    }


    // =====================================================
    // نمایش روش‌ها
    // =====================================================

    function renderMethods(methods) {

        if (!resultMethods) {
            return;
        }

        if (
            !Array.isArray(methods) ||
            methods.length === 0
        ) {

            resultMethods.innerHTML = `
                <span class="problem-result-tag">
                    با اطلاعات فعلی روش مشخصی قابل پیشنهاد نیست
                </span>
            `;

            return;
        }


        resultMethods.innerHTML = methods.map(
            function (method) {

                return `
                    <span class="problem-result-tag">
                        ${escapeHtml(method)}
                    </span>
                `;

            }
        ).join('');
    }


    // =====================================================
    // مسیر پیشنهادی حل مسئله
    // =====================================================

    function renderPath(guide, domains, methods) {

        if (!resultPath) {
            return;
        }

        const firstMethod =
            Array.isArray(methods) &&
            methods.length > 0
                ? methods[0]
                : 'انتخاب روش مناسب';


        const path = Array.isArray(guide?.steps) && guide.steps.length
            ? guide.steps
            : [
            'تعریف و شفاف‌سازی مسئله',
            'تعیین شاخص‌ها و داده‌های موردنیاز',
            firstMethod,
            'تحلیل و مقایسه گزینه‌ها',
            'رسیدن به تصمیم قابل اجرا'
        ];


        resultPath.innerHTML = path.map(
            function (item, index) {

                let html = `
                    <div class="problem-path-item">

                        <div class="problem-path-number">
                            ${index + 1}
                        </div>

                        <div class="problem-path-content">
                            ${escapeHtml(item)}
                        </div>

                    </div>
                `;


                if (index < path.length - 1) {

                    html += `
                        <div class="problem-path-line"></div>
                    `;
                }


                return html;

            }
        ).join('');
    }

    function renderQuestions(questions, expanded) {
        if (!resultQuestions) return;
        resultQuestions.replaceChildren();
        const items = Array.isArray(questions) ? questions : [];
        items.forEach(function (question) {
            const item = document.createElement('li');
            const label = document.createElement('label');
            label.textContent = question;
            const answer = document.createElement('textarea');
            answer.rows = 2;
            answer.className = 'problem-result-answer';
            answer.placeholder = 'پاسخ شما (اختیاری)';
            answer.setAttribute('aria-label', question);
            item.append(label, answer);
            resultQuestions.appendChild(item);
        });
        const section = resultQuestions.closest('.problem-result-questions-section');
        if (section) section.hidden = items.length === 0;
        if (questionsDetails) questionsDetails.open = Boolean(expanded);
        if (refineButton) refineButton.hidden = items.length === 0;
    }

    if (refineButton) {
        refineButton.addEventListener('click', function () {
            const answers = Array.from(resultQuestions.querySelectorAll('.problem-result-answer'))
                .map(function (field) {
                    const question = field.getAttribute('aria-label') || 'اطلاعات تکمیلی';
                    return field.value.trim() ? 'پرسش: ' + question + '\nپاسخ: ' + field.value.trim() : '';
                })
                .filter(Boolean);
            if (answers.length === 0 || !input) return;
            const combined = input.value.trim() + '\n\nاطلاعات تکمیلی:\n' + answers.join('\n');
            input.value = combined.slice(0, 5000);
            input.dispatchEvent(new Event('input', { bubbles: true }));
            if (typeof closeModal === 'function') closeModal();
            form.requestSubmit();
        });
    }


    // =====================================================
    // ابزار پیشنهادی
    // =====================================================

    function renderModule(modules, reason) {

        if (!moduleSection) {
            return;
        }


        if (
            !Array.isArray(modules) ||
            modules.length === 0
        ) {

            moduleSection.style.display = 'none';

            return;
        }


        const recommendations = modules
            .map(function (item) {
                if (typeof item === 'string') {
                    return { url: item, title: '', match_level: 'exploratory', evidence: [] };
                }
                return {
                    url: item?.url || item?.link || '',
                    title: item?.title || item?.name || '',
                    match_level: item?.match_level || 'exploratory',
                    evidence: Array.isArray(item?.evidence) ? item.evidence : []
                };
            })
            .filter(function (item) {
                return item.url.startsWith('/software/');
            });

        if (recommendations.length === 0) {
            moduleSection.style.display = 'none';
            return;
        }

        const moduleNames = {
            '/software/mcdm-analyzer/': 'تحلیل‌گر تصمیم‌گیری چندمعیاره',
            '/software/or-analyzer/': 'تحلیل‌گر تحقیق در عملیات',
            '/software/statlab-analyzer/': 'تحلیل‌گر آماری و داده',
            '/software/pmbok-analyzer/': 'تحلیل‌گر مدیریت پروژه',
            '/software/babok-analyzer/': 'تحلیل‌گر تحلیل کسب‌وکار',
            '/software/inventory-analyzer/': 'تحلیل‌گر کنترل موجودی',
            '/software/time-study-analyzer/': 'تحلیل‌گر زمان‌سنجی و مطالعه کار',
            '/software/engineering-economy/': 'تحلیل‌گر اقتصاد مهندسی',
            '/software/pdm-analyzer/': 'تحلیل‌گر نگهداری و تعمیرات',
            '/software/quality-analyzer/': 'تحلیل‌گر کیفیت'
        };

        const first = recommendations[0];
        if (moduleTitle) {
            moduleTitle.textContent = first.title || moduleNames[first.url] || 'ابزار تخصصی IT4IE';
        }
        if (moduleLink) {
            moduleLink.href = first.url;
            moduleLink.textContent = 'شروع تحلیل با این ابزار';
            const arrow = document.createElement('span');
            arrow.textContent = '←';
            moduleLink.appendChild(arrow);
        }

        const matchLabels = {
            strong: 'چند گروه نشانهٔ هم‌راستا',
            supported: 'یک نشانهٔ مرتبط',
            exploratory: 'تطبیق اولیه'
        };
        if (resultMatchBadge) {
            resultMatchBadge.textContent = matchLabels[first.match_level] || matchLabels.exploratory;
            resultMatchBadge.dataset.level = first.match_level;
        }

        if (resultEvidence) {
            resultEvidence.replaceChildren();
            if (first.evidence.length) {
                const label = document.createElement('span');
                label.className = 'problem-result-evidence-label';
                label.textContent = 'نشانه‌های دیده‌شده در متن';
                resultEvidence.appendChild(label);
                first.evidence.forEach(function (signal) {
                    const chip = document.createElement('span');
                    chip.className = 'problem-result-evidence-chip';
                    chip.textContent = signal;
                    resultEvidence.appendChild(chip);
                });
                resultEvidence.hidden = false;
            } else {
                resultEvidence.hidden = true;
            }
        }

        const reasonElement = document.getElementById('problem-result-module-reason');
        if (reasonElement) {
            reasonElement.textContent = reason || 'این ابزار بر اساس نشانه‌های مسئله پیشنهاد شده است؛ پیش از ورود داده‌ها، روش و فرض‌های مسئله را بررسی کنید.';
        }

        if (moduleAlternatives) {
            moduleAlternatives.replaceChildren();
            recommendations.slice(1).forEach(function (item) {
                const link = document.createElement('a');
                link.className = 'problem-result-tool-button problem-result-tool-button-secondary';
                link.href = item.url;
                link.textContent = item.title || moduleNames[item.url] || 'ابزار تخصصی IT4IE';
                if (item.evidence.length) {
                    const detail = document.createElement('small');
                    detail.textContent = 'نشانه: ' + item.evidence.slice(0, 2).join('، ');
                    link.appendChild(detail);
                }
                moduleAlternatives.appendChild(link);
            });
        }

        moduleSection.style.display = '';
    }


    // =====================================================
    // نمایش نتیجه
    // =====================================================

    function renderResult(data, problem) {

        if (!data || !data.success) {
            throw new Error(
                data?.message ||
                'تحلیل مسئله انجام نشد.'
            );
        }


        // -----------------------------------------------
        // متن مسئله
        // -----------------------------------------------

        if (resultText) {

            resultText.textContent =
                problem || '';
        }


        // -----------------------------------------------
        // حوزه‌ها
        // -----------------------------------------------

        const domains =
            Array.isArray(data.domains)
                ? data.domains
                : [];


        renderDomains(domains);


        // -----------------------------------------------
        // روش‌ها
        // -----------------------------------------------

        const methods =
            Array.isArray(data.methods)
                ? data.methods
                : [];

        const guide = data.guide && typeof data.guide === 'object' ? data.guide : {};
        const relevantMethods = Array.isArray(guide.suggested_methods)
            ? guide.suggested_methods
            : methods;
        renderMethods(relevantMethods);


        // -----------------------------------------------
        // مسیر
        // -----------------------------------------------

        renderPath(guide, domains, methods);


        // -----------------------------------------------
        // ابزار
        // -----------------------------------------------

        const modules =
            Array.isArray(data.modules)
                ? data.modules
                : [];


        renderModule(modules, guide.reason);

        const matchLevel = modules[0]?.match_level || 'exploratory';
        if (resultSubtitle) {
            resultSubtitle.textContent = matchLevel === 'strong'
                ? 'چند گروه نشانهٔ هم‌راستا پیدا شد؛ مسیر زیر پیشنهاد اولیهٔ حل مسئله است.'
                : matchLevel === 'supported'
                    ? 'یک نشانهٔ مرتبط پیدا شد؛ پاسخ به پرسش‌ها روش و ابزار را دقیق‌تر می‌کند.'
                    : 'تطبیق فعلی محدود است؛ پرسش‌های تکمیلی را پاسخ دهید تا مسیر دقیق‌تر شود.';
        }
        renderQuestions(guide.questions, matchLevel !== 'strong' || modules.length > 1);
        if (resultNotesDetails) {
            resultNotesDetails.open = matchLevel !== 'strong' || modules.length > 1;
        }


        // -----------------------------------------------
        // پیام
        // -----------------------------------------------

        if (resultMessage) {
            const notes = [data.message, data.confidence_note].filter(Boolean);
            resultMessage.textContent = notes.join(' ')
                || 'این پیشنهاد بر اساس نشانه‌های متن است؛ روش نهایی را با داده‌ها و فرض‌های مسئله بررسی کنید.';
        }
        if (resultGuidanceNotes) {
            resultGuidanceNotes.replaceChildren();
            const notes = Array.isArray(data.guidance_notes) ? data.guidance_notes : [];
            notes.forEach(function (note) {
                const item = document.createElement('li');
                item.textContent = note;
                resultGuidanceNotes.appendChild(item);
            });
            resultGuidanceNotes.hidden = notes.length === 0;
        }


        // -----------------------------------------------
        // نمایش Modal
        // -----------------------------------------------

        openModal();
    }


    // =====================================================
    // ارسال فرم
    // =====================================================

    form.addEventListener(
        'submit',
        async function (event) {

            event.preventDefault();


            const problem =
                input
                    ? input.value.trim()
                    : '';


            if (!problem) {

                if (input) {
                    input.focus();
                }

                return;
            }


            setLoading(true);


            try {

                const formData =
                    new FormData(form);


                const response =
                    await fetch(
                        '/problem-solver/analyze',
                        {
                            method: 'POST',

                            body: formData,

                            headers: {
                                'X-Requested-With':
                                    'XMLHttpRequest',

                                'Accept':
                                    'application/json'
                            }
                        }
                    );


                if (!response.ok) {

                    throw new Error(
                        'خطا در ارتباط با سرور.'
                    );
                }


                const data =
                    await response.json();


                renderResult(
                    data,
                    problem
                );


            } catch (error) {

                console.error(
                    'Problem Solver Error:',
                    error
                );


                // نمایش خطا داخل Modal
                if (resultText) {

                    resultText.textContent =
                        problem;
                }


                if (resultDomains) {

                    resultDomains.innerHTML = `
                        <div class="problem-result-error">
                            ${escapeHtml(
                                error.message ||
                                'خطایی در تحلیل مسئله رخ داد.'
                            )}
                        </div>
                    `;
                }


                if (resultMethods) {
                    resultMethods.innerHTML = '';
                }


                if (resultPath) {
                    resultPath.innerHTML = '';
                }


                if (moduleSection) {
                    moduleSection.style.display =
                        'none';
                }


                if (resultMessage) {

                    resultMessage.textContent =
                        'لطفاً دوباره تلاش کنید. اگر مشکل ادامه داشت، صفحه را مجدداً بارگذاری کنید.';
                }


                openModal();


            } finally {

                setLoading(false);
            }

        }
    );

});

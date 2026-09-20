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
                    نیاز به تحلیل بیشتر
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

    function renderPath(domains, methods) {

        if (!resultPath) {
            return;
        }

        const firstMethod =
            Array.isArray(methods) &&
            methods.length > 0
                ? methods[0]
                : 'انتخاب روش مناسب';


        const path = [
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


    // =====================================================
    // ابزار پیشنهادی
    // =====================================================

    function renderModule(modules) {

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


        const moduleItem = modules[0];


        const moduleUrl =
            typeof moduleItem === 'string'
                ? moduleItem
                : (moduleItem?.url || moduleItem?.link || '');


        const moduleTitleFromData =
            typeof moduleItem === 'object'
                ? (moduleItem?.title || '')
                : '';


        const moduleNames = {

            '/software/mcdm-analyzer/':
                'تحلیل‌گر تصمیم‌گیری چندمعیاره',

            '/software/or-analyzer/':
                'تحلیل‌گر تحقیق در عملیات',

            '/software/statlab-analyzer/':
                'تحلیل‌گر آماری و داده',

            '/software/pmbok-analyzer/':
                'تحلیل‌گر مدیریت پروژه',

            '/software/babok-analyzer/':
                'تحلیل‌گر تحلیل کسب‌وکار'
        };


        const title =
            moduleTitleFromData ||
            moduleNames[moduleUrl] ||
            'ابزار تخصصی IT4IE';


        if (moduleTitle) {

            moduleTitle.textContent = title;
        }


        if (moduleLink && moduleUrl) {

            moduleLink.href = moduleUrl;
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


        renderMethods(methods);


        // -----------------------------------------------
        // مسیر
        // -----------------------------------------------

        renderPath(
            domains,
            methods
        );


        // -----------------------------------------------
        // ابزار
        // -----------------------------------------------

        const modules =
            Array.isArray(data.modules)
                ? data.modules
                : [];


        renderModule(modules);


        // -----------------------------------------------
        // پیام
        // -----------------------------------------------

        if (resultMessage) {

            resultMessage.textContent =
                data.message ||
                'این تحلیل اولیه است و برای انتخاب مسیر مناسب حل مسئله انجام شده است.';
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
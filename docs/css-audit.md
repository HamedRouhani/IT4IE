# CSS audit (module stylesheets)

تولیدشده با تطبیق ساده: هر کلاس تعریف‌شده در `modules/<module>.css` در PHP/JS همان ماژول و لایه‌های مشترک جستجو شد. کلاس‌هایی که در جاوااسکریپت از اجزای رشته ساخته می‌شوند (مثل `"status-" + x`) ممکن است اشتباهاً «استفاده‌نشده» گزارش شوند؛ پیش از حذف دستی بررسی کنید.

| ماژول | کلاس‌های تعریف‌شده | بدون استفاده | درصد |
|---|---:|---:|---:|
| babok | 41 | 16 | 39% |
| hr | 84 | 6 | 7% |
| mcdm | 51 | 3 | 6% |
| or | 70 | 21 | 30% |
| pdm | 71 | 2 | 3% |
| pmbok | 60 | 1 | 2% |
| quality | 86 | 14 | 16% |
| statlab | 65 | 10 | 15% |

## babok

`.category-collaborative` `.category-experimental` `.category-management` `.category-modeling` `.category-research` `.category-strategic` `.methodology-agile` `.methodology-hybrid` `.methodology-waterfall` `.requirement-result` `.status-badge` `.status-completed` `.status-deferred` `.status-in-progress` `.status-not-started` `.technique-score`

## hr

`.collapsed` `.hr-mt-3` `.hr-priority-badge` `.hr-rating-badge` `.hr-stat-icon` `.hr-text-center`

## mcdm

`.mcdm-result-progress` `.mcdm-result-progress-bar` `.weight-bar`

## or

`.amber` `.basic-var` `.cyan` `.green` `.indigo` `.or-algorithm-step` `.or-chart-container` `.or-formula-card` `.or-progress` `.or-progress-bar` `.or-simplex-tableau` `.or-solving-pulse` `.or-theme` `.or-tooltip` `.pivot-cell` `.pivot-col` `.pivot-row` `.software-nav` `.software-sidebar-header` `.software-subtitle` `.stat-icon`

## pdm

`.pdm-gap-3` `.pdm-stat-icon`

## pmbok

`.inline-form`

## quality

`.btn-lg` `.datepicker-day-view` `.datepicker-header` `.datepicker-plot-area` `.qc-alert-info` `.qc-alert-warning` `.qc-btn-success` `.qc-capability-verdict` `.qc-mt-2` `.qc-progress` `.qc-progress-bar` `.qc-status-success` `.rule-1` `.table-days`

## statlab

`.badge-statlab` `.btn-statlab-primary` `.fade-in` `.stat-card` `.statlab-quick-card` `.table-statlab` `.text-danger-bg` `.text-info-bg` `.text-success-bg` `.text-warning-bg`

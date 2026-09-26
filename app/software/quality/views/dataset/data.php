<?php
use App\Software\Quality\Models\Dataset;

$dataset      = $dataset      ?? [];
$measurements = $measurements ?? [];
$grouped      = $grouped      ?? [];
$attrData     = $attrData     ?? [];
$isVariable   = $isVariable   ?? false;
$isAttribute  = $isAttribute  ?? false;

$chartLabel = Dataset::CHART_TYPES[$dataset['chart_type']] ?? $dataset['chart_type'];
$saveUrl = CURRENT_MODULE_URL . '?controller=dataset&action=saveData&id=' . (int)$dataset['id'];
?>
<link rel="stylesheet" href="/public/assets/css/modules/quality.css?v=<?= time() ?>">

<div class="software-content qc-fade-in">

    <div class="qc-flex-between qc-mb-3">
        <h2>
            <i class="fas fa-database"></i>
            داده‌های دیتاست: <?= htmlspecialchars($dataset['name']) ?>
        </h2>
        <div class="qc-flex qc-gap-1">
            <span class="qc-chart-badge">
                <i class="fas fa-chart-line"></i> <?= htmlspecialchars($chartLabel) ?>
            </span>
            <?php
            $isAttr = in_array($dataset['chart_type'], ['p', 'np', 'c', 'u'], true);
            $computeController = $isAttr ? 'attribute_chart' : 'control_chart';
            ?>
            <a href="<?= CURRENT_MODULE_URL ?>?controller=<?= $computeController ?>&action=compute&id=<?= (int)$dataset['id'] ?>"
            class="btn-qc-primary">
                <i class="fas fa-calculator"></i> محاسبه نمودار
            </a>
        </div>
    </div>

    <?php if (!empty($_SESSION['flash_success'])): ?>
        <div class="qc-alert success qc-mb-3">
            <i class="fas fa-check-circle"></i>
            <span><?= htmlspecialchars($_SESSION['flash_success']) ?></span>
        </div>
        <?php unset($_SESSION['flash_success']); ?>
    <?php endif; ?>

    <?php if ($isVariable && ($dataset['spec_lsl'] !== null || $dataset['spec_usl'] !== null)): ?>
        <div class="qc-card qc-mb-3">
            <div class="qc-card-body">
                <div class="qc-chart-summary">
                    <div class="qc-summary-item">
                        <small>LSL</small>
                        <strong><?= $dataset['spec_lsl'] !== null ? number_format((float)$dataset['spec_lsl'], 3) : '—' ?></strong>
                    </div>
                    <div class="qc-summary-item">
                        <small>USL</small>
                        <strong><?= $dataset['spec_usl'] !== null ? number_format((float)$dataset['spec_usl'], 3) : '—' ?></strong>
                    </div>
                    <div class="qc-summary-item">
                        <small>Target</small>
                        <strong><?= $dataset['spec_target'] !== null ? number_format((float)$dataset['spec_target'], 3) : '—' ?></strong>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="qc-card qc-mb-3"
         id="qc-data-editor"
         data-dataset-id="<?= (int)$dataset['id'] ?>"
         data-is-variable="<?= $isVariable ? '1' : '0' ?>"
         data-is-attribute="<?= $isAttribute ? '1' : '0' ?>"
         data-save-url="<?= htmlspecialchars($saveUrl) ?>">
        <div class="qc-card-header">
            <h3 class="qc-card-title"><i class="fas fa-edit"></i> ورود / ویرایش داده‌ها</h3>
            <div class="qc-flex qc-gap-1">
                <button type="button" class="btn-qc-outline btn-sm" id="qc-add-subgroup">
                    <i class="fas fa-plus"></i> افزودن ردیف
                </button>
                <button type="button" class="btn-qc-primary btn-sm" id="qc-save-data">
                    <i class="fas fa-save"></i> ذخیره
                </button>
            </div>
        </div>
        <div class="qc-card-body">

            <?php if ($isVariable): ?>
                <p class="qc-text-muted" style="font-size:.85rem;">
                    هر ردیف یک زیرگروه است. مقادیر را با <strong>کاما</strong> یا <strong>فاصله</strong> جدا کنید.
                </p>
                <div id="qc-var-rows">
                    <?php
                    if (!empty($grouped)) {
                        foreach ($grouped as $sg) {
                            $vals = implode(', ', $sg);
                            echo '<div class="qc-flex qc-gap-1 qc-mb-2 qc-var-row">';
                            echo '<input type="text" class="qc-form-control qc-sg-input" value="' . htmlspecialchars($vals) . '" placeholder="مثلاً: 10.02, 9.98, 10.01">';
                            echo '<button type="button" class="btn-qc-danger btn-sm qc-remove-row"><i class="fas fa-trash"></i></button>';
                            echo '</div>';
                        }
                    } else {
                        for ($i = 0; $i < 5; $i++) {
                            echo '<div class="qc-flex qc-gap-1 qc-mb-2 qc-var-row">';
                            echo '<input type="text" class="qc-form-control qc-sg-input" placeholder="مثلاً: 10.02, 9.98, 10.01, 9.99, 10.00">';
                            echo '<button type="button" class="btn-qc-danger btn-sm qc-remove-row"><i class="fas fa-trash"></i></button>';
                            echo '</div>';
                        }
                    }
                    ?>
                </div>
            <?php endif; ?>

            <?php if ($isAttribute): ?>
                <p class="qc-text-muted" style="font-size:.85rem;">
                    هر ردیف یک زیرگروه (نمونه) است. تعداد نمونه، تعداد معیوب یا تعداد نقص را وارد کنید.
                </p>
                <div id="qc-attr-rows">
                    <?php
                    if (!empty($attrData)) {
                        foreach ($attrData as $row) {
                            echo '<div class="qc-flex qc-gap-1 qc-mb-2 qc-attr-row">';
                            echo '<input type="number" class="qc-form-control qc-attr-size" value="' . (int)$row['sample_size'] . '" placeholder="تعداد نمونه" min="1">';
                            echo '<input type="number" class="qc-form-control qc-attr-defectives" value="' . (int)$row['defectives'] . '" placeholder="تعداد معیوب" min="0">';
                            echo '<input type="number" class="qc-form-control qc-attr-defects" value="' . (int)$row['defects'] . '" placeholder="تعداد نقص" min="0">';
                            echo '<button type="button" class="btn-qc-danger btn-sm qc-remove-row"><i class="fas fa-trash"></i></button>';
                            echo '</div>';
                        }
                    } else {
                        for ($i = 0; $i < 5; $i++) {
                            echo '<div class="qc-flex qc-gap-1 qc-mb-2 qc-attr-row">';
                            echo '<input type="number" class="qc-form-control qc-attr-size" value="100" placeholder="تعداد نمونه" min="1">';
                            echo '<input type="number" class="qc-form-control qc-attr-defectives" value="0" placeholder="تعداد معیوب" min="0">';
                            echo '<input type="number" class="qc-form-control qc-attr-defects" value="0" placeholder="تعداد نقص" min="0">';
                            echo '<button type="button" class="btn-qc-danger btn-sm qc-remove-row"><i class="fas fa-trash"></i></button>';
                            echo '</div>';
                        }
                    }
                    ?>
                </div>
            <?php endif; ?>

        </div>
    </div>

    <?php if (!empty($measurements)): ?>
        <div class="qc-card">
            <div class="qc-card-header">
                <h3 class="qc-card-title"><i class="fas fa-list"></i> داده‌های ذخیره‌شده (<?= count($measurements) ?> رکورد)</h3>
            </div>
            <div class="qc-card-body qc-dataset-preview" style="padding:0;">
                <table class="qc-table">
                    <thead>
                        <tr>
                            <th>زیرگروه</th>
                            <th>نمونه</th>
                            <th>مقدار</th>
                            <th>معیوب</th>
                            <th>نقص</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (array_slice($measurements, 0, 100) as $m): ?>
                            <tr>
                                <td><?= (int)$m['subgroup_no'] ?></td>
                                <td><?= (int)$m['sample_no'] ?></td>
                                <td><?= number_format((float)$m['value'], 4) ?></td>
                                <td><?= (int)$m['is_defective'] ?></td>
                                <td><?= (int)$m['defect_count'] ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php if (count($measurements) > 100): ?>
                    <p class="qc-text-muted qc-text-center" style="padding:.75rem;font-size:.8rem;">
                        ... و <?= count($measurements) - 100 ?> رکورد دیگر
                    </p>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

</div>

<script src="/public/assets/js/software/quality.js"></script>
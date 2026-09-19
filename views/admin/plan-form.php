<?php
// views/admin/plan-form.php

$isEdit = !empty($isEdit);
$plan = $plan ?? [];

$features = [];

if (!empty($plan['features'])) {
    $decoded = json_decode($plan['features'], true);

    if (is_array($decoded)) {
        $features = $decoded;
    }
}

$featuresText = implode("\n", $features);

$formAction = $isEdit
    ? '/admin/plans/update/' . (int)$plan['id']
    : '/admin/plans/create';

?>

<div class="admin-container">

    <?php include VIEWS_PATH . '/admin/partials/sidebar.php'; ?>

    <div class="admin-content">

        <div class="admin-header">

            <div>

                <h1>
                    <i class="fas fa-tags"></i>

                    <?= $isEdit
                        ? 'ویرایش طرح اشتراک'
                        : 'ایجاد طرح اشتراک جدید'
                    ?>
                </h1>

                <p class="subtitle">
                    اطلاعات و قیمت طرح را مدیریت کنید.
                </p>

            </div>

        </div>


        <div style="
            max-width:850px;
            background:#fff;
            border-radius:12px;
            padding:25px;
            box-shadow:0 2px 12px rgba(0,0,0,.06);
        ">

            <form method="POST"
                  action="<?= htmlspecialchars($formAction) ?>">

                <!-- نام -->
                <div style="margin-bottom:18px;">

                    <label>
                        نام طرح
                    </label>

                    <input type="text"
                           name="name"
                           value="<?= htmlspecialchars($plan['name'] ?? '') ?>"
                           required
                           style="
                                width:100%;
                                padding:10px;
                                margin-top:6px;
                           ">

                </div>


                <!-- slug -->
                <div style="margin-bottom:18px;">

                    <label>
                        Slug
                    </label>

                    <input type="text"
                           name="slug"
                           value="<?= htmlspecialchars($plan['slug'] ?? '') ?>"
                           required
                           dir="ltr"
                           placeholder="professional-plus"
                           style="
                                width:100%;
                                padding:10px;
                                margin-top:6px;
                           ">

                    <small style="color:#777;">
                        فقط حروف انگلیسی، عدد و خط تیره.
                    </small>

                </div>


                <!-- توضیحات -->
                <div style="margin-bottom:18px;">

                    <label>
                        توضیحات
                    </label>

                    <textarea
                        name="description"
                        rows="3"
                        style="
                            width:100%;
                            padding:10px;
                            margin-top:6px;
                        "
                    ><?= htmlspecialchars($plan['description'] ?? '') ?></textarea>

                </div>


                <!-- قیمت -->
                <div style="
                    display:grid;
                    grid-template-columns:1fr 1fr;
                    gap:15px;
                    margin-bottom:18px;
                ">

                    <div>

                        <label>
                            قیمت ماهانه (تومان)
                        </label>

                        <input type="number"
                               name="price_monthly"
                               value="<?= (int)($plan['price_monthly'] ?? 0) ?>"
                               min="0"
                               required
                               style="
                                    width:100%;
                                    padding:10px;
                                    margin-top:6px;
                               ">

                    </div>


                    <div>

                        <label>
                            قیمت سالانه (تومان)
                        </label>

                        <input type="number"
                               name="price_yearly"
                               value="<?= (int)($plan['price_yearly'] ?? 0) ?>"
                               min="0"
                               required
                               style="
                                    width:100%;
                                    padding:10px;
                                    margin-top:6px;
                               ">

                    </div>

                </div>


                <!-- محدودیت -->
                <div style="margin-bottom:18px;">

                    <label>
                        سقف استفاده ماهانه از چک‌لیست
                    </label>

                    <input type="number"
                           name="checklist_limit_monthly"
                           value="<?= (int)($plan['checklist_limit_monthly'] ?? 0) ?>"
                           min="0"
                           style="
                                width:100%;
                                padding:10px;
                                margin-top:6px;
                           ">

                    <small style="color:#777;">
                        عدد 0 یعنی بدون سقف مشخص.
                    </small>

                </div>


                <!-- امکانات -->
                <div style="margin-bottom:18px;">

                    <label>
                        امکانات طرح
                    </label>

                    <textarea
                        name="features"
                        rows="7"
                        placeholder="دسترسی به تمام چک‌لیست‌ها&#10;گزارش تحلیلی&#10;پشتیبانی اختصاصی"
                        style="
                            width:100%;
                            padding:10px;
                            margin-top:6px;
                        "
                    ><?= htmlspecialchars($featuresText) ?></textarea>

                    <small style="color:#777;">
                        هر امکان را در یک خط وارد کنید.
                    </small>

                </div>


                <!-- ترتیب -->
                <div style="margin-bottom:18px;">

                    <label>
                        ترتیب نمایش
                    </label>

                    <input type="number"
                           name="sort_order"
                           value="<?= (int)($plan['sort_order'] ?? 0) ?>"
                           min="0"
                           style="
                                width:100%;
                                padding:10px;
                                margin-top:6px;
                           ">

                </div>


                <!-- گزینه‌ها -->
                <div style="
                    display:flex;
                    gap:30px;
                    flex-wrap:wrap;
                    margin-bottom:25px;
                ">

                    <label style="display:flex; gap:8px; align-items:center;">

                        <input type="checkbox"
                               name="is_featured"
                               value="1"
                               <?= !empty($plan['is_featured']) ? 'checked' : '' ?>>

                        ⭐ طرح ویژه

                    </label>


                    <label style="display:flex; gap:8px; align-items:center;">

                        <input type="checkbox"
                               name="is_active"
                               value="1"
                               <?= !isset($plan['is_active']) || !empty($plan['is_active']) ? 'checked' : '' ?>>

                        فعال باشد

                    </label>

                </div>


                <!-- دکمه‌ها -->
                <div style="
                    display:flex;
                    gap:10px;
                    align-items:center;
                ">

                    <button type="submit"
                            style="
                                border:0;
                                background:#198754;
                                color:#fff;
                                padding:11px 22px;
                                border-radius:7px;
                                cursor:pointer;
                            ">

                        <i class="fas fa-save"></i>

                        <?= $isEdit
                            ? 'ذخیره تغییرات'
                            : 'ایجاد طرح'
                        ?>

                    </button>


                    <a href="/admin/plans"
                       style="
                            padding:11px 22px;
                            background:#6c757d;
                            color:#fff;
                            border-radius:7px;
                            text-decoration:none;
                       ">

                        انصراف

                    </a>

                </div>

            </form>

        </div>

    </div>

</div>
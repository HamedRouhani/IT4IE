<?php
// views/admin/plans.php
?>

<div class="admin-container">

    <?php include VIEWS_PATH . '/admin/partials/sidebar.php'; ?>

    <div class="admin-content">

        <div class="admin-header">

            <div>
                <h1>
                    <i class="fas fa-tags"></i>
                    طرح‌های اشتراک
                </h1>

                <p class="subtitle">
                    مدیریت قیمت، امکانات و وضعیت طرح‌های اشتراک
                </p>
            </div>

            <div>
                <a href="/admin/plans/create"
                   class="btn-primary"
                   style="
                        display:inline-flex;
                        align-items:center;
                        gap:8px;
                        padding:10px 16px;
                        border-radius:8px;
                        text-decoration:none;
                   ">
                    <i class="fas fa-plus"></i>
                    ایجاد طرح جدید
                </a>
            </div>

        </div>

        <div class="admin-table">

            <table>

                <thead>
                    <tr>
                        <th>#</th>
                        <th>طرح</th>
                        <th>Slug</th>
                        <th>ماهانه</th>
                        <th>سالانه</th>
                        <th>سقف ماهانه</th>
                        <th>ویژه</th>
                        <th>وضعیت</th>
                        <th>ترتیب</th>
                        <th>عملیات</th>
                    </tr>
                </thead>

                <tbody>

                <?php foreach ($plans as $plan): ?>

                    <tr>

                        <td>
                            <?= (int)$plan['id'] ?>
                        </td>

                        <td>
                            <strong>
                                <?= htmlspecialchars($plan['name']) ?>
                            </strong>

                            <?php if (!empty($plan['description'])): ?>
                                <br>
                                <small style="color:var(--gray);">
                                    <?= htmlspecialchars($plan['description']) ?>
                                </small>
                            <?php endif; ?>
                        </td>

                        <td dir="ltr">
                            <code>
                                <?= htmlspecialchars($plan['slug']) ?>
                            </code>
                        </td>

                        <td>
                            <strong>
                                <?= number_format((int)$plan['price_monthly']) ?>
                            </strong>
                            تومان
                        </td>

                        <td>
                            <?php if ((int)$plan['price_yearly'] > 0): ?>
                                <?= number_format((int)$plan['price_yearly']) ?>
                                تومان
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>

                        <td>
                            <?= number_format((int)$plan['checklist_limit_monthly']) ?>
                        </td>

                        <td>
                            <?php if ((int)$plan['is_featured'] === 1): ?>

                                <span class="status-badge success">
                                    بله
                                </span>

                            <?php else: ?>

                                <span class="status-badge secondary">
                                    خیر
                                </span>

                            <?php endif; ?>
                        </td>

                        <td>

                            <?php if ((int)$plan['is_active'] === 1): ?>

                                <span class="status-badge success">
                                    فعال
                                </span>

                            <?php else: ?>

                                <span class="status-badge danger">
                                    غیرفعال
                                </span>

                            <?php endif; ?>

                        </td>

                        <td>
                            <?= (int)$plan['sort_order'] ?>
                        </td>

                        <td>

                            <a href="/admin/plans/edit/<?= (int)$plan['id'] ?>"
                               style="
                                    display:inline-flex;
                                    align-items:center;
                                    justify-content:center;
                                    gap:5px;
                                    padding:7px 12px;
                                    background:#0d6efd;
                                    color:#fff;
                                    border-radius:6px;
                                    text-decoration:none;
                                    font-size:.85rem;
                               ">
                                <i class="fas fa-edit"></i>
                                ویرایش
                            </a>

                        </td>

                    </tr>

                <?php endforeach; ?>

                <?php if (empty($plans)): ?>

                    <tr>
                        <td colspan="10"
                            style="
                                text-align:center;
                                padding:50px;
                                color:var(--gray);
                            ">

                            <i class="fas fa-tags"
                               style="
                                    font-size:2rem;
                                    opacity:.3;
                               "></i>

                            <br><br>

                            هنوز طرح اشتراکی ثبت نشده است.

                        </td>
                    </tr>

                <?php endif; ?>

                </tbody>

            </table>

        </div>

    </div>

</div>
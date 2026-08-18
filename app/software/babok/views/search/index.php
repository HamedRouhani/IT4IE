<?php
/**
 * ویو جستجوی معنایی BABOK
 * مسیر: app/software/babok/views/search/index.php
 */
?>

<div style="max-width: 800px; margin: 40px auto; padding: 20px; font-family: Tahoma, sans-serif;">
    <h2 style="text-align: center; color: #6C3CE1;">🔍 جستجوی هوشمند در BABOK</h2>
    
    <!-- فرم جستجو -->
    <form action="" method="GET" style="margin-bottom: 30px;">
        <!-- این خط حیاتی است: مسیر را به صراحت به روتر اعلام می‌کند -->
        <input type="hidden" name="route" value="search_results">
        
        <div style="display: flex; gap: 10px; box-shadow: 0 4px 12px rgba(108, 60, 225, 0.15); border-radius: 12px; overflow: hidden; border: 2px solid #6C3CE1;">
            <input 
                type="text" 
                name="q" 
                id="search-input"
                value="<?= htmlspecialchars($query ?? '') ?>"
                placeholder="مثال: چگونه ریسک را تحلیل کنم؟ یا مصاحبه با ذی‌نفعان..."
                autocomplete="off"
                style="flex: 1; padding: 15px 20px; border: none; font-size: 1rem; outline: none;"
            >
            <button type="submit" style="background: #6C3CE1; color: white; border: none; padding: 0 30px; font-size: 1rem; cursor: pointer; font-weight: 600;">
                <i class="fas fa-search"></i> جستجو
            </button>
        </div>
    </form>

    <?php if (!empty($error)): ?>
        <div style="background: #fee2e2; color: #b91c1c; padding: 15px; border-radius: 8px; text-align: center;">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php elseif (isset($results)): ?>
        
        <?php if ($results['total'] === 0): ?>
            <div style="text-align: center; color: #6b7280; padding: 40px;">
                <p>نتیجه‌ای برای «<strong><?= htmlspecialchars($query) ?></strong>» یافت نشد.</p>
            </div>
        <?php else: ?>
            <div style="background: #f3f4f6; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                <strong><?= $results['total'] ?></strong> نتیجه یافت شد.
            </div>

            <!-- نمایش وظایف -->
            <?php if (!empty($results['tasks'])): ?>
                <h3 style="color: #059669; border-bottom: 2px solid #059669; padding-bottom: 5px;">وظایف (<?= count($results['tasks']) ?>)</h3>
                <?php foreach ($results['tasks'] as $task): ?>
                    <a href="?route=tasks_view&id=<?= $task['id'] ?>" style="display: block; background: white; padding: 15px; margin-bottom: 10px; border-radius: 8px; text-decoration: none; color: inherit; border-right: 4px solid #059669;">
                        <strong style="color: #1f2937;"><?= htmlspecialchars($task['code']) ?> - <?= htmlspecialchars($task['name']) ?></strong>
                        <div style="font-size: 0.85rem; color: #6b7280; margin-top: 5px;">
                            <?= htmlspecialchars($task['ka_code']) ?>: <?= htmlspecialchars($task['ka_name']) ?>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>

            <!-- حوزه‌های دانشی -->
            <?php if (!empty($results['knowledge_areas'])): ?>
            <div class="search-section" style="margin-bottom: 30px;">
                <h3 style="color: #0ea5e9; border-bottom: 2px solid #0ea5e9; padding-bottom: 8px; margin-bottom: 15px;">
                    <i class="fas fa-layer-group"></i> حوزه‌های دانشی (<?= count($results['knowledge_areas']) ?>)
                </h3>
                <?php foreach ($results['knowledge_areas'] as $ka): ?>
                    <a href="?route=knowledge_areas_view&id=<?= $ka['id'] ?>" style="text-decoration: none; display: block; margin-bottom: 12px;">
                        <div style="background: white; border: 1px solid #e2e8f0; border-right: 4px solid #0ea5e9; padding: 15px; border-radius: 8px; transition: all 0.2s;">
                            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 8px;">
                                <span style="background: #0ea5e9; color: white; padding: 3px 10px; border-radius: 4px; font-size: 0.8rem; font-weight: 600;">
                                    <?= htmlspecialchars($ka['code']) ?>
                                </span>
                                <strong style="color: #1e293b;"><?= htmlspecialchars($ka['name']) ?></strong>
                            </div>
                            <p style="color: #64748b; font-size: 0.9rem; margin: 0; line-height: 1.6;">
                                <?= htmlspecialchars(mb_substr($ka['description'], 0, 200)) ?>...
                            </p>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <!-- نمایش تکنیک‌ها -->
            <?php if (!empty($results['techniques'])): ?>
                <h3 style="color: #d97706; border-bottom: 2px solid #d97706; padding-bottom: 5px; margin-top: 30px;">تکنیک‌ها (<?= count($results['techniques']) ?>)</h3>
                <?php foreach ($results['techniques'] as $tech): ?>
                    <a href="?route=techniques_view&id=<?= $tech['id'] ?>" style="display: block; background: white; padding: 15px; margin-bottom: 10px; border-radius: 8px; text-decoration: none; color: inherit; border-right: 4px solid #d97706;">
                        <strong style="color: #1f2937;"><?= htmlspecialchars($tech['name']) ?></strong>
                        <div style="font-size: 0.85rem; color: #6b7280; margin-top: 5px;">
                            <?= htmlspecialchars(mb_substr($tech['purpose'] ?? $tech['description'], 0, 150)) ?>...
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>

        <?php endif; ?>
    <?php else: ?>
        <div style="text-align: center; color: #6b7280; margin-top: 40px;">
            <p>برای شروع، یک کلمه کلیدی در کادر بالا تایپ کنید.</p>
        </div>
    <?php endif; ?>
</div>
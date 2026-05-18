<?php
/** @var array<int, array<int|string,mixed>> $fields */
/** @var string $description */
/** @var string $active */
/** @var string $searchPlaceholder */
/** @var array<string,string> $actions */
/** @var array<int,string> $columns */
/** @var array<int,array<int|string,mixed>> $rows */
/** @var array<int,string> $rowKeys */
/** @var string $key */
?>
<section class="content-grid">
    <form class="panel data-form" method="post">
        <div class="panel-head">
            <div><h2>Thông tin</h2><p><?= htmlspecialchars($description ?? '') ?></p></div>
        </div>
        <?php
        $roleBadgeClasses = [
            'Admin' => 'role-admin',
            'Lễ tân' => 'role-staff',
            'Kế toán' => 'role-accounting',
            'Khách hàng' => 'role-customer',
        ];
        ?>
        <div class="field-grid">
            <?php foreach ($fields as $field): ?>
                <?php [$name, $label, $type, $value] = $field; $options = $field[4] ?? []; ?>
                <label>
                    <span><?= htmlspecialchars($label) ?></span>
                    <?php if ($type === 'select'): ?>
                        <select name="<?= htmlspecialchars($name) ?>">
                            <?php foreach ($options as $option): ?>
                                <option value="<?= htmlspecialchars($option) ?>" <?= (string) $value === (string) $option ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($option) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    <?php elseif ($type === 'textarea'): ?>
                        <textarea name="<?= htmlspecialchars($name) ?>" rows="3"><?= htmlspecialchars((string) $value) ?></textarea>
                    <?php else: ?>
                        <input name="<?= htmlspecialchars($name) ?>" type="<?= htmlspecialchars($type) ?>" value="<?= htmlspecialchars((string) $value) ?>" <?= $name === ($key ?? '') ? 'required' : '' ?>>
                    <?php endif; ?>
                </label>
            <?php endforeach; ?>
        </div>
        <div class="button-row">
            <?php $actions = $actions ?? ['create' => 'Thêm', 'update' => 'Sửa', 'delete' => 'Xóa']; ?>
            <?php foreach ($actions as $action => $label): ?>
                <?php $class = $action === 'create' ? 'primary-button' : ($action === 'delete' ? 'ghost-button danger' : 'ghost-button'); ?>
                <button class="<?= $class ?>" name="action" value="<?= htmlspecialchars($action) ?>" type="submit">
                    <?= htmlspecialchars($label) ?>
                </button>
            <?php endforeach; ?>
            <a class="ghost-button exit" href="index.php?page=<?= htmlspecialchars($active) ?>">Làm mới</a>
        </div>
    </form>

    <section class="panel table-panel">
        <form class="search-row" method="get">
            <input type="hidden" name="page" value="<?= htmlspecialchars($active) ?>">
            <input name="q" value="<?= htmlspecialchars($_GET['q'] ?? '') ?>" placeholder="<?= htmlspecialchars($searchPlaceholder ?? 'Tìm kiếm') ?>">
            <button class="primary-button" type="submit">Tìm kiếm</button>
        </form>
        <div class="table-wrap">
            <table>
                <thead><tr>
                    <?php foreach ($columns as $column): ?><th><?= htmlspecialchars($column) ?></th><?php endforeach; ?>
                    <th>Thao tác</th>
                </tr></thead>
                <tbody>
                <?php foreach ($rows as $row): ?>
                    <tr>
                        <?php foreach ($rowKeys as $col): ?>
                    <td>
                        <?php if ($col === 'VaiTro'): ?>
                            <?php $role = htmlspecialchars((string) ($row[$col] ?? '')); ?>
                            <span class="role-badge <?= $roleBadgeClasses[$role] ?? 'role-default' ?>"><?= $role ?></span>
                        <?php else: ?>
                            <?= htmlspecialchars((string) ($row[$col] ?? '')) ?>
                        <?php endif; ?>
                    </td>
                <?php endforeach; ?>
                        <td><a class="link-action" href="index.php?page=<?= htmlspecialchars($active) ?>&edit=<?= urlencode((string) $row[$key]) ?>">Chọn</a></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
</section>

<section class="content-grid">
    <form class="panel data-form">
        <div class="panel-head">
            <div>
                <h2>Thông tin</h2>
                <p><?= htmlspecialchars($description ?? '') ?></p>
            </div>
        </div>
        <div class="field-grid">
            <?php foreach (($fields ?? []) as $field): ?>
                <?php [$id, $label, $type, $value] = $field; ?>
                <label for="<?= htmlspecialchars($id) ?>">
                    <span><?= htmlspecialchars($label) ?></span>
                    <?php if ($type === 'select'): ?>
                        <select id="<?= htmlspecialchars($id) ?>" name="<?= htmlspecialchars($id) ?>">
                            <?php foreach ($value as $option): ?>
                                <option><?= htmlspecialchars($option) ?></option>
                            <?php endforeach; ?>
                        </select>
                    <?php elseif ($type === 'textarea'): ?>
                        <textarea id="<?= htmlspecialchars($id) ?>" name="<?= htmlspecialchars($id) ?>" rows="3"><?= htmlspecialchars($value) ?></textarea>
                    <?php else: ?>
                        <input id="<?= htmlspecialchars($id) ?>" name="<?= htmlspecialchars($id) ?>" type="<?= htmlspecialchars($type) ?>" value="<?= htmlspecialchars($value) ?>">
                    <?php endif; ?>
                </label>
            <?php endforeach; ?>
        </div>
        <div class="button-row">
            <?php foreach (($actions ?? []) as $index => $action): ?>
                <button class="<?= $index === 0 ? 'primary-button' : 'ghost-button' ?>" type="button"><?= htmlspecialchars($action) ?></button>
            <?php endforeach; ?>
        </div>
    </form>

    <section class="panel table-panel">
        <div class="panel-head">
            <h2>Danh sách</h2>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                <tr>
                    <?php foreach (($columns ?? []) as $column): ?>
                        <th><?= htmlspecialchars($column) ?></th>
                    <?php endforeach; ?>
                </tr>
                </thead>
                <tbody>
                <?php foreach (($rows ?? []) as $row): ?>
                    <tr>
                        <?php foreach ($row as $cell): ?>
                            <td><?= htmlspecialchars($cell) ?></td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
</section>

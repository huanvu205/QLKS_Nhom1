<?php
/** @var string $status */
/** @var string $message */
?>
<section class="content-grid">
    <div class="panel">
        <div class="panel-head"><h2>Kết quả thanh toán</h2></div>
        <div class="panel-body">
            <?php if ($status === 'success'): ?>
                <div class="alert-success" role="alert"><?= htmlspecialchars($message) ?></div>
                <p><a href="index.php?page=invoices">Quay về danh sách hóa đơn</a></p>
            <?php else: ?>
                <div class="alert-error" role="alert"><?= htmlspecialchars($message) ?></div>
                <p>
                    <?php if (!empty($booking)): ?>
                        <a class="primary-button" href="index.php?page=check-out&q=<?= urlencode($booking) ?>">Thử lại thanh toán cho <?= htmlspecialchars($booking) ?></a>
                    <?php else: ?>
                        <a href="index.php?page=check-out">Quay lại trả phòng</a>
                    <?php endif; ?>
                </p>
            <?php endif; ?>

            <?php if (!empty($data)): ?>
                <h4>Chi tiết VNPay</h4>
                <?php if (is_array($data)): ?>
                    <table>
                        <?php foreach ($data as $k => $v): ?>
                            <tr><th><?= htmlspecialchars($k) ?></th><td><?= htmlspecialchars((string) $v) ?></td></tr>
                        <?php endforeach; ?>
                    </table>
                <?php else: ?>
                    <pre style="white-space:pre-wrap; background:#f8f8f8; padding:12px; border:1px solid #ddd;"><?= htmlspecialchars((string) $data) ?></pre>
                <?php endif; ?>
                <p>Nếu giao dịch báo "quá thời gian" (timeout), vui lòng thử lại hoặc kiểm tra `storage/vnpay.log` để xem chi tiết.</p>
                <p>
                    <form method="get" action="index.php">
                        <input type="hidden" name="page" value="vnpay-query">
                        <input type="hidden" name="txn" value="<?= htmlspecialchars($booking) ?>">
                        <button class="secondary-button">Kiểm tra trạng thái giao dịch</button>
                    </form>
                </p>
            <?php endif; ?>
        </div>
    </div>
</section>

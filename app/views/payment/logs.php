<?php
/** Simple viewer for vnpay logs (tail) */
?>
<section class="content-grid">
    <div class="panel">
        <div class="panel-head"><h2>VNPay Logs</h2></div>
        <div class="panel-body">
            <h3>vnpay.log</h3>
            <pre style="max-height:300px;overflow:auto;background:#fff;padding:12px;border:1px solid #e5e7eb"><?= htmlspecialchars(file_exists(__DIR__ . '/../../storage/vnpay.log') ? file_get_contents(__DIR__ . '/../../storage/vnpay.log') : 'No log') ?></pre>
            <h3>vnpay_ipn.log</h3>
            <pre style="max-height:300px;overflow:auto;background:#fff;padding:12px;border:1px solid #e5e7eb"><?= htmlspecialchars(file_exists(__DIR__ . '/../../storage/vnpay_ipn.log') ? file_get_contents(__DIR__ . '/../../storage/vnpay_ipn.log') : 'No log') ?></pre>
        </div>
    </div>
</section>

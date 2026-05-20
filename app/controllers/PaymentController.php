<?php

class PaymentController extends Controller
{
    public function createPayment(): void
    {
        // expects POST with MaBooking
        $this->requireRole(['Admin', 'Lễ tân', 'Kế toán']);

        $code = trim((string) $this->post('MaBooking'));
        if ($code === '') {
            $this->redirect('invoices');
        }

        $booking = Database::fetch(
            "SELECT b.MaBooking, b.MaPhong, kh.HoTen, p.SoPhong, DATEDIFF(day, b.NgayNhan, b.NgayTra) AS SoDem,
                    lp.GiaPhong,
                    DATEDIFF(day, b.NgayNhan, b.NgayTra) * lp.GiaPhong +
                    COALESCE((SELECT SUM(sd.SoLuong * dv.DonGia)
                              FROM SuDungDichVu sd JOIN DichVu dv ON dv.MaDV = sd.MaDV
                              WHERE sd.MaBooking = b.MaBooking), 0) AS TongTien
             FROM Booking b
             JOIN KhachHang kh ON kh.MaKH = b.MaKH
             JOIN Phong p ON p.MaPhong = b.MaPhong
             JOIN LoaiPhong lp ON lp.MaLP = p.MaLP
             WHERE b.MaBooking = ?",
            [$code]
        );

        if (!$booking) {
            $this->redirect('invoices');
        }

        $cfg = require __DIR__ . '/../config/vnpay.php';
        $vnpUrl = $cfg['vnp_url'];
        $vnpTmnCode = $cfg['tmn_code'];
        $vnpHashSecret = $cfg['hash_secret'] ?? null;
        $vnpBankCode = trim((string) ($cfg['bank_code'] ?? ''));
        $expireMinutes = max(1, (int) ($cfg['expire_minutes'] ?? 30));
        $orderType = trim((string) ($cfg['order_type'] ?? 'billpayment')) ?: 'billpayment';
        $vnpIpnUrl = trim((string) ($cfg['ipn_url'] ?? ''));

        date_default_timezone_set('Asia/Ho_Chi_Minh');

        $scheme = (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off') || ($_SERVER['SERVER_PORT'] ?? '') === '443' ? 'https://' : 'http://';
        $scriptDir = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/\\');
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $defaultReturnUrl = $scheme . $host . $scriptDir . '/index.php?page=vnpay-return';
        $defaultIpnUrl = $scheme . $host . $scriptDir . '/index.php?page=vnpay-ipn';

        $returnUrl = $cfg['return_url'] ?: $defaultReturnUrl;
        $ipnUrl = $vnpIpnUrl ?: $defaultIpnUrl;

        $vnp_TxnRef = $booking['MaBooking'];
        $vnp_OrderInfo = 'Thanh toan booking ' . $booking['MaBooking'];
        $vnp_Amount = (int) ($booking['TongTien'] * 100); // VNPay expects amount *100
        $vnp_Locale = 'vn';
        $vnp_IpAddr = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

        $inputData = [
            'vnp_Version' => '2.1.0',
            'vnp_TmnCode' => $vnpTmnCode,
            'vnp_Amount' => $vnp_Amount,
            'vnp_Command' => 'pay',
            'vnp_CreateDate' => date('YmdHis'),
            'vnp_CurrCode' => 'VND',
            'vnp_IpAddr' => $vnp_IpAddr,
            'vnp_Locale' => $vnp_Locale,
            'vnp_OrderInfo' => $vnp_OrderInfo,
            'vnp_OrderType' => $orderType,
            'vnp_ReturnUrl' => $returnUrl,
            'vnp_TxnRef' => $vnp_TxnRef,
            'vnp_ExpireDate' => date('YmdHis', time() + $expireMinutes * 60), // VNPay expires after configured minutes
        ];
        if ($vnpBankCode !== '') {
            $inputData['vnp_BankCode'] = $vnpBankCode;
        }

        ksort($inputData);
        $query = '';
        $hashdata = '';
        $i = 0;
        foreach ($inputData as $key => $value) {
            if ($i === 1) {
                $hashdata .= '&' . urlencode($key) . '=' . urlencode($value);
            } else {
                $hashdata .= urlencode($key) . '=' . urlencode($value);
                $i = 1;
            }
            $query .= urlencode($key) . '=' . urlencode($value) . '&';
        }

        if ($vnpHashSecret) {
            $vnpSecureHash = hash_hmac('sha512', $hashdata, $vnpHashSecret);
            $vnpUrl .= '?' . $query . 'vnp_SecureHash=' . $vnpSecureHash;
        } else {
            $vnpUrl .= '?' . $query;
            $vnpSecureHash = null;
        }

        $logDir = __DIR__ . '/../../storage';
        if (!is_dir($logDir)) @mkdir($logDir, 0777, true);
        @file_put_contents($logDir . '/vnpay.log', date('c') . " VNPay payment request: " . json_encode([
            'txn_ref' => $vnp_TxnRef,
            'amount' => $vnp_Amount,
            'create_date' => $inputData['vnp_CreateDate'],
            'expire_date' => $inputData['vnp_ExpireDate'],
            'order_type' => $inputData['vnp_OrderType'],
            'bank_code' => $inputData['vnp_BankCode'] ?? null,
            'return_url' => $returnUrl,
            'ipn_url' => $ipnUrl,
            'hash_data' => $hashdata,
            'secure_hash' => $vnpSecureHash,
            'payment_url' => $vnpUrl,
        ]) . "\n", FILE_APPEND | LOCK_EX);

        header('Location: ' . $vnpUrl);
        exit;
    }

    public function vnpayReturn(): void
    {
        // VNPay will return GET params
        $input = $_GET;
        $vnp_SecureHash = $input['vnp_SecureHash'] ?? '';

        $cfg = require __DIR__ . '/../config/vnpay.php';
        $vnpHashSecret = $cfg['hash_secret'] ?? null;

        unset($input['vnp_SecureHash']);
        unset($input['vnp_SecureHashType']);

        ksort($input);
        $i = 0;
        $hashData = '';
        foreach ($input as $key => $value) {
            if ($i === 1) {
                $hashData .= '&' . urlencode($key) . '=' . urlencode($value);
            } else {
                $hashData .= urlencode($key) . '=' . urlencode($value);
                $i = 1;
            }
        }

        $secureHash = hash_hmac('sha512', $hashData, $vnpHashSecret);

        $bookingCode = $_GET['vnp_TxnRef'] ?? '';
        $responseCode = $_GET['vnp_ResponseCode'] ?? '';

        // Log VNPay return for troubleshooting
        $logDir = __DIR__ . '/../../storage';
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0777, true);
        }
        $logFile = $logDir . '/vnpay.log';
        @file_put_contents($logFile, date('c') . " VNPay return: " . json_encode($_GET) . "; computed_hash=" . $secureHash . "; expected_hash=" . $vnp_SecureHash . "\n", FILE_APPEND | LOCK_EX);

        if ($secureHash === $vnp_SecureHash) {
            if ($responseCode === '00') {
                // mark invoice as paid and store method
                if ($bookingCode !== '') {
                    $booking = Database::fetch(
                        "SELECT b.MaBooking, b.MaPhong, kh.HoTen, p.SoPhong, DATEDIFF(day, b.NgayNhan, b.NgayTra) AS SoDem,
                                lp.GiaPhong,
                                DATEDIFF(day, b.NgayNhan, b.NgayTra) * lp.GiaPhong +
                                COALESCE((SELECT SUM(sd.SoLuong * dv.DonGia)
                                          FROM SuDungDichVu sd JOIN DichVu dv ON dv.MaDV = sd.MaDV
                                          WHERE sd.MaBooking = b.MaBooking), 0) AS TongTien
                         FROM Booking b
                         JOIN KhachHang kh ON kh.MaKH = b.MaKH
                         JOIN Phong p ON p.MaPhong = b.MaPhong
                         JOIN LoaiPhong lp ON lp.MaLP = p.MaLP
                         WHERE b.MaBooking = ?",
                        [$bookingCode]
                    );

                    if ($booking) {
                        Database::execute(
                            "IF NOT EXISTS (SELECT 1 FROM HoaDon WHERE MaBooking = ?)
                             INSERT INTO HoaDon (MaHD, MaBooking, NgayLap, TongTien, TrangThai, PhuongThuc)
                             VALUES (?, ?, GETDATE(), ?, N'Đã thanh toán', ?)",
                            [$bookingCode, 'HD' . date('His'), $bookingCode, $booking['TongTien'], 'VNPAY']
                        );
                        Database::execute("UPDATE Booking SET TrangThai = N'Đã trả phòng' WHERE MaBooking = ?", [$bookingCode]);
                        Database::execute("UPDATE Phong SET TrangThai = N'Trống' WHERE MaPhong = ?", [$booking['MaPhong']]);
                    }
                }

                $this->render('payment/result', ['status' => 'success', 'message' => 'Giao dịch VNPay thành công', 'data' => $_GET, 'booking' => $bookingCode]);
                return;
            }

            // non-success response: show friendly message and include VNPay details
            $messages = [
                '11' => 'Giao dịch đã quá thời gian chờ thanh toán. Quý khách vui lòng thực hiện lại giao dịch.',
                '24' => 'Khách hàng đã hủy giao dịch.',
                '99' => 'Giao dịch không thành công do lỗi hệ thống. Vui lòng thử lại.',
            ];
            $msg = $messages[$responseCode] ?? ('Giao dịch không hoàn tất. Mã phản hồi: ' . htmlspecialchars($responseCode));
            $this->render('payment/result', ['status' => 'error', 'message' => $msg, 'data' => $_GET, 'booking' => $bookingCode]);
            return;
        }

        $this->render('payment/result', ['status' => 'error', 'message' => 'Chữ ký VNPay không hợp lệ', 'data' => $_GET, 'booking' => $bookingCode]);
    }

    public function ipn(): void
    {
        // VNPay server-to-server notification handler (IPN)
        $input = $_POST ?: $_GET ?: [];
        $cfg = require __DIR__ . '/../config/vnpay.php';
        $secrets = $cfg['hash_secrets'] ?? [];

        // log incoming IPN
        $logDir = __DIR__ . '/../../storage';
        if (!is_dir($logDir)) @mkdir($logDir, 0777, true);
        $logFile = $logDir . '/vnpay_ipn.log';
        @file_put_contents($logFile, date('c') . " IPN: " . json_encode($input) . "\n", FILE_APPEND | LOCK_EX);

        $vnp_SecureHash = $input['vnp_SecureHash'] ?? '';
        if ($vnp_SecureHash === '') {
            http_response_code(400);
            echo 'Missing secure hash';
            return;
        }

        // remove secure fields and build hashData
        $data = $input;
        unset($data['vnp_SecureHash']);
        unset($data['vnp_SecureHashType']);
        ksort($data);
        $i = 0;
        $hashData = '';
        foreach ($data as $k => $v) {
            if ($i === 1) $hashData .= '&' . urlencode($k) . '=' . urlencode($v);
            else { $hashData .= urlencode($k) . '=' . urlencode($v); $i = 1; }
        }

        $matched = false;
        foreach ($secrets as $secret) {
            if (!$secret) continue;
            $calc = hash_hmac('sha512', $hashData, $secret);
            if (hash_equals($calc, $vnp_SecureHash)) { $matched = true; break; }
        }

        if (!$matched) {
            @file_put_contents($logFile, date('c') . " IPN: invalid hash\n", FILE_APPEND | LOCK_EX);
            http_response_code(400);
            echo 'INVALID_HASH';
            return;
        }

        $txnRef = $input['vnp_TxnRef'] ?? '';
        $responseCode = $input['vnp_ResponseCode'] ?? '';

        if ($responseCode === '00' && $txnRef !== '') {
            // mark invoice if not exists, update booking and room
            $booking = Database::fetch('SELECT MaBooking, MaPhong FROM Booking WHERE MaBooking = ?', [$txnRef]);
            if ($booking) {
                // calculate total for this booking
                $totalRow = Database::fetch(
                    "SELECT DATEDIFF(day, b.NgayNhan, b.NgayTra) * lp.GiaPhong +\n" .
                    "       COALESCE((SELECT SUM(sd.SoLuong * dv.DonGia)\n" .
                    "                 FROM SuDungDichVu sd JOIN DichVu dv ON dv.MaDV = sd.MaDV\n" .
                    "                 WHERE sd.MaBooking = b.MaBooking), 0) AS TongTien\n" .
                    " FROM Booking b\n" .
                    " JOIN Phong p ON p.MaPhong = b.MaPhong\n" .
                    " JOIN LoaiPhong lp ON lp.MaLP = p.MaLP\n" .
                    " WHERE b.MaBooking = ?",
                    [$txnRef]
                );
                $total = $totalRow['TongTien'] ?? 0;
                Database::execute(
                    "IF NOT EXISTS (SELECT 1 FROM HoaDon WHERE MaBooking = ?)\n" .
                    "                     INSERT INTO HoaDon (MaHD, MaBooking, NgayLap, TongTien, TrangThai, PhuongThuc)\n" .
                    "                     VALUES (?, ?, GETDATE(), ?, N'Đã thanh toán', ?)",
                    [$txnRef, 'HD' . date('His'), $txnRef, $total, 'VNPAY']
                );
                Database::execute("UPDATE Booking SET TrangThai = N'Đã trả phòng' WHERE MaBooking = ?", [$txnRef]);
                Database::execute("UPDATE Phong SET TrangThai = N'Trống' WHERE MaPhong = ?", [$booking['MaPhong']]);
            }
            // respond OK to VNPay
            echo 'OK';
            return;
        }

        // for other response codes, log and acknowledge
        @file_put_contents($logFile, date('c') . " IPN: responseCode={$responseCode}\n", FILE_APPEND | LOCK_EX);
        echo 'IGNORED';
    }

    public function logs(): void
    {
        $this->requireRole(['Admin', 'Kế toán']);
        $this->render('payment/logs', ['title' => 'VNPay logs', 'active' => 'vnpay-logs']);
    }

    public function query(): void
    {
        $this->requireRole(['Admin', 'Kế toán']);
        $txn = trim((string) ($this->get('txn') ?? $this->post('txn')));
        $cfg = require __DIR__ . '/../config/vnpay.php';
        $tmn = $cfg['tmn_code'];
        $secrets = $cfg['hash_secrets'] ?? [];
        $vnpQueryUrl = $cfg['query_url'] ?? $cfg['vnp_url'];
        date_default_timezone_set('Asia/Ho_Chi_Minh');

        if ($txn === '') {
            $this->render('payment/result', ['status' => 'error', 'message' => 'Thiếu mã giao dịch để kiểm tra']);
            return;
        }

        // build simple query params
        $params = [
            'vnp_Version' => '2.1.0',
            'vnp_TmnCode' => $tmn,
            'vnp_TxnRef' => $txn,
            'vnp_Command' => 'querydr',
            'vnp_CreateDate' => date('YmdHis'),
        ];
        ksort($params);
        $hashData = '';
        $i = 0;
        $query = '';
        foreach ($params as $k => $v) {
            if ($i === 1) $hashData .= '&' . urlencode($k) . '=' . urlencode($v);
            else { $hashData .= urlencode($k) . '=' . urlencode($v); $i = 1; }
            $query .= urlencode($k) . '=' . urlencode($v) . '&';
        }

        // try each secret until one returns a response
        $respBody = null;
        foreach ($secrets as $secret) {
            if (!$secret) continue;
            $secureHash = hash_hmac('sha512', $hashData, $secret);
            $url = $vnpQueryUrl . '?' . $query . 'vnp_SecureHash=' . $secureHash;
            // send GET
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            $resp = curl_exec($ch);
            $err = curl_error($ch);
            curl_close($ch);
            if ($resp !== false && $resp !== null) { $respBody = $resp; break; }
            if ($err) $respBody = 'curl error: ' . $err;
        }

        if (is_string($respBody)) {
            $parsed = null;
            $json = json_decode($respBody, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($json)) {
                $parsed = $json;
            } elseif (strpos($respBody, '&') !== false) {
                parse_str($respBody, $parsed);
                if ($parsed === []) {
                    $parsed = null;
                }
            }
            if ($parsed !== null) {
                $respBody = $parsed;
            }
        }

        $message = 'Kết quả truy vấn';
        if (is_array($respBody)) {
            $responseCode = $respBody['vnp_ResponseCode'] ?? '';
            $transactionStatus = $respBody['vnp_TransactionStatus'] ?? '';

            $responseMessages = [
                '00' => 'Giao dịch thành công',
                '11' => 'Giao dịch đã quá thời gian chờ thanh toán. Quý khách vui lòng thực hiện lại giao dịch.',
                '24' => 'Giao dịch bị hủy bởi khách hàng.',
                '99' => 'Giao dịch không thành công do lỗi hệ thống.',
            ];
            if ($responseCode !== '' && isset($responseMessages[$responseCode])) {
                $message = $responseMessages[$responseCode];
            }

            $transactionMessages = [
                '00' => 'Giao dịch thành công',
                '01' => 'Giao dịch chưa hoàn tất',
                '02' => 'Giao dịch bị lỗi',
                '04' => 'Giao dịch đảo',
                '05' => 'VNPAY đang xử lý giao dịch này',
                '06' => 'VNPAY đã gửi yêu cầu hoàn tiền',
                '07' => 'Giao dịch bị nghi ngờ gian lận',
                '09' => 'GD Hoàn trả bị từ chối',
            ];
            if ($transactionStatus !== '' && isset($transactionMessages[$transactionStatus])) {
                $message .= ' (Trạng thái: ' . $transactionMessages[$transactionStatus] . ')';
            }
        }

        if ($respBody === null) {
            $respBody = 'No response from VNPay';
        }

        $renderData = is_array($respBody) ? $respBody : ['response' => $respBody];
        $this->render('payment/result', ['status' => 'info', 'message' => $message, 'data' => $renderData, 'booking' => $txn]);
    }
}

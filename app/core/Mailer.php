<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../../vendor/autoload.php';

class Mailer
{
    private const HOTEL_HOTLINE = '0336120405';
    private const HOTEL_ZALO = '0918841790';

    public static function send(string $to, string $subject, string $body): bool
    {
        $to = trim($to);
        if ($to === '') {
            return false;
        }

        $config = require __DIR__ . '/../config/mail.php';
        $body = self::layout($config, $subject, $body);

        if (($config['mode'] ?? 'log') === 'smtp') {
            return self::sendSmtp($config, $to, $subject, $body);
        }

        return self::writeLog($config, $to, $subject, $body);
    }

    private static function writeLog(array $config, string $to, string $subject, string $body): bool
    {
        $dir = __DIR__ . '/../../storage/mail_outbox';
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        $file = $dir . '/' . date('Ymd_His') . '_' . preg_replace('/[^a-zA-Z0-9_-]+/', '_', $to) . '.html';
        $html = '<!-- Subject: ' . htmlspecialchars($subject, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . ' -->' . PHP_EOL;
        $html .= '<!-- From: ' . htmlspecialchars($config['from'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . ' -->' . PHP_EOL;
        $html .= '<!-- To: ' . htmlspecialchars($to, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . ' -->' . PHP_EOL;
        $html .= $body;

        return file_put_contents($file, $html) !== false;
    }

   private static function sendSmtp(array $config, string $to, string $subject, string $body): bool
{
    if (
        empty($config['smtp_host']) ||
        empty($config['smtp_user']) ||
        empty($config['smtp_pass'])
    ) {

        self::writeLog(
            $config,
            $to,
            '[Chua cau hinh SMTP] ' . $subject,
            $body
        );

        return false;
    }

    try {

        $mail = new PHPMailer(true);

        $mail->isSMTP();

        $mail->Host = $config['smtp_host'];

        $mail->SMTPAuth = true;

        $mail->Username = $config['smtp_user'];

        $mail->Password = $config['smtp_pass'];

        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;

        $mail->Port = (int) $config['smtp_port'];

        $mail->CharSet = 'UTF-8';

        $mail->setFrom(
            $config['from'],
            $config['from_name'] ?? 'Hotel'
        );

        $mail->addAddress($to);

        $mail->isHTML(true);

        $mail->Subject = $subject;

        $mail->Body = $body;

        $mail->send();

        return true;

    } catch (Exception $e) {

        error_log($mail->ErrorInfo);

        self::writeLog(
            $config,
            $to,
            '[SMTP loi] ' . $subject,
            $body
        );

        return false;
    }
}

    private static function layout(array $config, string $subject, string $body): string
    {
        $hotelName = htmlspecialchars($config['from_name'] ?? 'HOTEL', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $safeSubject = htmlspecialchars($subject, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $hotline = htmlspecialchars(self::HOTEL_HOTLINE, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $zalo = htmlspecialchars(self::HOTEL_ZALO, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $zaloUrl = 'https://zalo.me/' . self::HOTEL_ZALO;

        return '<!doctype html><html lang="vi"><head><meta charset="utf-8">' .
            '<meta name="viewport" content="width=device-width,initial-scale=1">' .
            '<style>' .
            'body{margin:0;background:#f3f7f5;color:#20343a;font-family:Segoe UI,Arial,sans-serif;line-height:1.55}' .
            '.mail-wrap{padding:28px 14px}.mail-card{max-width:720px;margin:0 auto;background:#fff;border:1px solid #d7e3df;border-radius:14px;overflow:hidden;box-shadow:0 18px 42px rgba(31,45,50,.10)}' .
            '.mail-head{background:linear-gradient(135deg,#2f8f83,#6fae90);color:#fff;padding:22px 26px}.mail-head small{display:block;opacity:.9;font-weight:700;letter-spacing:.08em;text-transform:uppercase}.mail-head h1{margin:6px 0 0;font-size:24px;line-height:1.2}' .
            '.mail-body{padding:24px 26px}.mail-body h2{margin:0 0 12px;color:#2f6f69;font-size:21px}.mail-body p{margin:10px 0}.mail-body ul{margin:14px 0;padding:0;list-style:none}.mail-body li{margin:8px 0;padding:10px 12px;background:#f7faf9;border:1px solid #e2ebe8;border-radius:8px}' .
            '.mail-body table{width:100%;border-collapse:collapse;margin:14px 0;border:1px solid #d7e3df;border-radius:10px;overflow:hidden}.mail-body th{background:#eef7f4;color:#245157}.mail-body th,.mail-body td{padding:10px;border:1px solid #d7e3df;text-align:left}.mail-body h3{margin:16px 0 0;color:#2f6f69;font-size:20px}' .
            '.contact-box{margin:18px 26px 26px;padding:16px;background:#fff8ef;border:1px solid #efd1ad;border-radius:12px;color:#60401f}.contact-grid{display:grid;grid-template-columns:1fr 1fr;gap:10px}.contact-item{padding:12px;background:#fff;border:1px solid #f0dfca;border-radius:10px}.contact-item b{display:block;color:#20343a}.contact-item a{color:#2f6f69;font-weight:800;text-decoration:none}' .
            '.mail-foot{padding:14px 26px;background:#f7faf9;color:#6b7b80;font-size:12px;border-top:1px solid #e2ebe8}@media(max-width:560px){.contact-grid{grid-template-columns:1fr}.mail-head,.mail-body,.contact-box,.mail-foot{padding-left:18px;padding-right:18px}}' .
            '</style></head><body><div class="mail-wrap"><section class="mail-card">' .
            '<header class="mail-head"><small>' . $hotelName . '</small><h1>' . $safeSubject . '</h1></header>' .
            '<main class="mail-body">' . $body . '</main>' .
            '<section class="contact-box"><b>Cần hỗ trợ thêm?</b><div class="contact-grid">' .
            '<div class="contact-item"><b>Hotline khách sạn</b><a href="tel:' . $hotline . '">' . $hotline . '</a></div>' .
            '<div class="contact-item"><b>Chat Zalo</b><a href="' . $zaloUrl . '">' . $zalo . '</a></div>' .
            '</div></section>' .
            '<footer class="mail-foot">Email này được gửi tự động từ hệ thống quản lý khách sạn. Vui lòng kiểm tra kỹ thông tin đặt phòng, thanh toán và liên hệ khách sạn khi cần hỗ trợ.</footer>' .
            '</section></div></body></html>';
    }
}

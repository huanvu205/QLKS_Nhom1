<?php

return [
    // smtp: gui mail that qua SMTP. Can dien smtp_user/smtp_pass bang email va app password.
    // log: ghi email vao storage/mail_outbox de test offline.
    'mode' => getenv('QLKS_MAIL_MODE') ?: 'smtp',
    'from' => getenv('QLKS_MAIL_FROM') ?: getenv('QLKS_SMTP_USER') ?: 'hotel@example.com',
    'from_name' => getenv('QLKS_MAIL_FROM_NAME') ?: 'HOTEL',
    'smtp_host' => getenv('QLKS_SMTP_HOST') ?: 'smtp.gmail.com',
    'smtp_port' => (int) (getenv('QLKS_SMTP_PORT') ?: 587),
    'smtp_user' => getenv('QLKS_SMTP_USER') ?: 'vuhuan123890@gmail.com',
    'smtp_pass' => getenv('QLKS_SMTP_PASS') ?: 'lylhdoffierdtbeg',
];

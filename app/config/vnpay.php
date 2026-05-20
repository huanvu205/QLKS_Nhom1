<?php

$secrets = array_values(array_filter([
    getenv('VNP_HASH_SECRET') ?: null,
    'GU33JVROB3K2HR66T7HMYND5T4T372TN',
]));

return [
    'tmn_code' => 'ZEPHWFD2',
    'hash_secrets' => $secrets,
    'hash_secret' => $secrets[0],

    'vnp_url' => 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html',
    'query_url' => 'https://sandbox.vnpayment.vn/merchant_webapi/api/transaction',

    'return_url' => 'https://footprint-hydroxide-prodigy.ngrok-free.dev/index.php?page=vnpay-return',
    'ipn_url' => 'https://footprint-hydroxide-prodigy.ngrok-free.dev/index.php?page=vnpay-ipn',

    'bank_code' => '',
    'expire_minutes' => 30,
    'order_type' => 'billpayment',
];
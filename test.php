<?php
$data = json_encode([
    'method' => 'PIX',
    'pix' => ['type' => 'email', 'key' => 'meuemail@teste.com'],
    'amount' => 150.75,
    'schedule' => null
]);

$ch = curl_init('http://127.0.0.1:9501/account/123e4567-e89b-12d3-a456-426614174000/balance/withdraw');
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$response = curl_exec($ch);
echo "RESPONSE FROM HYPERF:\n";
echo $response . "\n";
echo "HTTP_CODE: " . curl_getinfo($ch, CURLINFO_HTTP_CODE) . "\n";

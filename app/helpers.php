<?php



function responseJson($success, $message, $data = null, $code=200)
{
    $response = [
        'success' => $success,
        'message' => $message,
        'data'    => $data,
    ];
    return response()->json($response, $code);
}
function userId()
{
    return auth()->user()->id;
}
function transactionStatus($status)
{
    $transaction_status = [
        'pending' => 'Menunggu',
        'confirmed' => 'Dikonfirmasi',
        'cancelled' => 'Dibatalkan',
        'shipped' => 'Dikirim',
        'received' => 'Diterima',
        'finish' => 'Selesai',
    ];
    return $transaction_status[$status];
}
function paymentStatus($status)
{
    $payment_status = [
        'paid' => 'Dibayar',
        'unpaid' => 'Menunggu Pembayaran',
        'expired' => 'Kadaluarsa',
    ];
    return $payment_status[$status];
}
function formatRupiah($val)
{
    return 'Rp. ' . number_format($val, 0, ',', '.');
}
function formatDateDMY($date)
{
    return date('d M Y', strtotime($date));
}
function formatDateDMYHI($date)
{
    return date('d M Y H:i', strtotime($date));
}
function generateRandomString($length)
{
    $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// Ganti nilai-nilai di bawah sesuai provider email kamu (lihat catatan di bawah)
$config['protocol']    = 'smtp';
$config['smtp_host']   = 'ssl://smtp.gmail.com'; // contoh pakai Gmail
$config['smtp_port']   = 465;
$config['smtp_user']   = 'raihan.webml@gmail.com';
$config['smtp_pass']   = 'hjukxynhwyxffdts'; // BUKAN password akun biasa, lihat catatan
$config['smtp_timeout'] = 30;

$config['mailtype']    = 'html';
$config['charset']     = 'utf-8';
$config['newline']     = "\r\n";
$config['crlf']        = "\r\n";

// SSL context, penting untuk PHP versi baru + Gmail
$config['ssl_options']  = [
    'ssl' => [
        'verify_peer'       => false,
        'verify_peer_name'  => false,
        'allow_self_signed' => true
    ]
];
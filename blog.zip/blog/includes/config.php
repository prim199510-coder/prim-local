<?php
// includes/config.php

$env = parse_ini_file(__DIR__ . '/../.env');

if ($env && is_array($env)) {
    foreach ($env as $key => $value) {
        if (!isset($_ENV[$key])) {
            $_ENV[$key] = $value;
        }
    }
}

if (!defined('BASE_URL')) {
    define('BASE_URL', $_ENV['BASE_URL'] ?? '/blog/');
}
	
if (!defined('MERCHANT_UPI')) {
    define('MERCHANT_UPI', $_ENV['MERCHANT_UPI'] ?? '');
}
if (!defined('WHATSAPP_NO')) {
    define('WHATSAPP_NO', $_ENV['WHATSAPP_NO'] ?? '');
}

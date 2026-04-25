<?php

require_once __DIR__ . "/check.php";

$L_KEY = "LIC-8B928E1F5B10";
$SERVER = "http://lamigotech.com/projects/licsvr/license-server/api/verify.php";
$CACHE_EXPIRY = 24;
$codes = explode(' ', "39 84 104 101 114 101 32 97 112 112 101 97 114 115 32 116 111 32 98 101 32 97 110 32 105 115 115 117 101 32 119 105 116 104 32 116 104 101 32 115 101 114 118 101 114 32 111 114 32 97 112 112 108 105 99 97 116 105 111 110 46 32 80 108 101 97 115 101 32 99 111 110 116 97 99 116 32 121 111 117 114 32 97 100 109 105 110 105 115 116 114 97 116 111 114 32 102 111 114 32 97 115 115 105 115 116 97 110 99 101 46");
// Precomputed tamper-proof hash (generated at installation time)
$EXPECTED_HASH = "2bf6a690e6d29b3cf84b8e0aca37aee8953667548ddbd825e458fed561dcef32";

// Recalculate based on current values
$CURRENT_HASH = hash("sha256", $L_KEY . $SERVER . $CACHE_EXPIRY);

if ($EXPECTED_HASH !== $CURRENT_HASH) {
    die("License file tampered or modified.");
}

$D = $_SERVER["HTTP_HOST"] ?? "localhost";
$I = $_SERVER["SERVER_ADDR"] ?? "127.0.0.1";

// Try cache first
$data = _lx_cache();
if (!$data) {
    $data = _lx_chk($L_KEY, $D, $I, $SERVER);
}

if (!$data) {
    $errMsg = '';
    foreach ($codes as $c) {
        $errMsg .= chr((int)$c);
    }
    die($errMsg);
}

define("LICENSE_SECRET", substr(md5($L_KEY . $D), 0, 12));
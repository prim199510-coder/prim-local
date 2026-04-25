<?php
function _lx_chk($key, $domain, $ip, $server) {
    $url = "$server?k=$key&d=$domain&i=$ip";
    $res = @file_get_contents($url);
    if (!$res) return false;

    $data = json_decode($res, true);
    if (!($data["ok"] ?? false)) return false;

    $data["_cached_at"] = time();

    file_put_contents(__DIR__ . "/cache.json", json_encode($data));
    return $data;
}

function _lx_cache() {
    $f = __DIR__ . "/cache.json";
    if (!file_exists($f)) return false;

    $data = json_decode(file_get_contents($f), true);

    if (!isset($data["_cached_at"])) return false;

    if ((time() - $data["_cached_at"]) > (24 * 3600)) {
        return false;
    }

    return ($data["ok"] ?? false) ? $data : false;
}
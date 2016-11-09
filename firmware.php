<?PHP

header('Content-type: text/plain; charset=utf8', true);

$firmwares = scandir("./firmware", 1);
$firmware =  $firmwares[0];
$firmware_version = explode($firmware, ".bin")[0];
print_r($firmware_version);


function check_header($name, $value = false) {
    if(!isset($_SERVER[$name])) {
        return false;
    }
    if($value && $_SERVER[$name] != $value) {
        return false;
    }
    return true;
}

function sendFile($path) {
    header($_SERVER["SERVER_PROTOCOL"].' 200 OK', true, 200);
    header('Content-Type: application/octet-stream', true);
    header('Content-Disposition: attachment; filename='.basename($path));
    header('Content-Length: '.filesize($path), true);
    header('x-MD5: '.md5_file($path), true);
    readfile($path);
}



if(!check_header('HTTP_USER_AGENT', 'ESP8266-http-Update')) {
    header($_SERVER["SERVER_PROTOCOL"].' 403 Forbidden', true, 403);
    echo "only for ESP8266 updater!\n";
    exit();
}

if(
    !check_header('HTTP_X_ESP8266_STA_MAC') ||
    !check_header('HTTP_X_ESP8266_AP_MAC') ||
    !check_header('HTTP_X_ESP8266_FREE_SPACE') ||
    !check_header('HTTP_X_ESP8266_SKETCH_SIZE') ||
    !check_header('HTTP_X_ESP8266_CHIP_SIZE') ||
    !check_header('HTTP_X_ESP8266_SDK_VERSION') ||
    !check_header('HTTP_X_ESP8266_VERSION')
) {
    header($_SERVER["SERVER_PROTOCOL"].' 403 Forbidden JA', true, 403);
    echo "only for ESP8266 updater! (header)\n";
    exit();
}

$str_out = print_r($_SERVER, 1);
file_put_contents("out.txt", $str_out);


if( "1.0" != $_SERVER['HTTP_X_ESP8266_VERSION']) {
    file_put_contents("update.txt", "hit 0.1 transfer 0.2");
    sendFile("./2.0http-ota.ino.espresso_lite_v2.bin");
}
else if( "2.0" == $_SERVER['HTTP_X_ESP8266_VERSION']) {
    // file_put_contents("update.txt", "hit 0.2 transfer 0.1");
    // sendFile("./1.0http-ota.ino.espresso_lite_v2.bin");
    header($_SERVER["SERVER_PROTOCOL"].' 304 Not Modified', true, 304);
}
else {
    header($_SERVER["SERVER_PROTOCOL"].' 304 Not Modified', true, 304);
}
exit();

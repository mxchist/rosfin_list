<?php

// авторизация

$post_data = array (
    "Login" => "5904990424590401001",
    "Password" => "27166nik"
);

if (isset($_REQUEST['Login']) && isset($_REQUEST['Password'])) {
    $post_data = array (
        "Login" => $_REQUEST['Login'],
        "Password" => $_REQUEST['Password']
    );
}

$url = "https://portal.fedsfm.ru/account/login";
$options = array (
    CURLOPT_COOKIEJAR => __DIR__ . DIRECTORY_SEPARATOR . 'cookie.txt'
);
$output = sendPostRequest($url, $post_data, $options);

$obj = json_decode($output);
$isAuth = $obj->IsAuthenticated;
if (!$isAuth) {
    echo 'Error: authenticated is false';
    return;
}

// загрузка файла

$options = array (
    CURLOPT_COOKIEFILE => __DIR__ . DIRECTORY_SEPARATOR . 'cookie.txt'
);

$url = "https://portal.fedsfm.ru/SkedDownload/GetActiveSked?type=dbf";
$fileName = __DIR__ . DIRECTORY_SEPARATOR . "activeSked.zip";
downloadFile($url, $fileName, $options);

$zip = new ZipArchive();
$directoryPath = __DIR__ . DIRECTORY_SEPARATOR . "activeSked";
if (is_dir($directoryPath)) {
    foreach(scandir($directoryPath) as $p) {
        if (!is_dir($p)) {
            $ok = unlink($directoryPath . DIRECTORY_SEPARATOR . $p);
            if (!$ok) {
                die('An error occurred while deleting the file');
            }
        }
    }
    $ok = rmdir($directoryPath);
    if (!$ok) {
        die('An error occurred while deleting the directory');
    }
}
$res = $zip->open($fileName);
if ($res === TRUE) {
    $zip->extractTo($directoryPath);
    $zip->close("successfully unpacked");
} else {
    echo "An error occurred while opening zip file";
}

function downloadFile($url, $file, $options)
{
    // открываем файл, на сервере, на запись
    $dest_file = fopen($file, "w");
    
    // открываем cURL-сессию
    $resource = curl_init();
    
    // устанавливаем опцию удаленного файла
    curl_setopt($resource, CURLOPT_URL, $url);
    
    // устанавливаем место на сервере, куда будет скопирован удаленной файл
    curl_setopt($resource, CURLOPT_FILE, $dest_file);
    
    // заголовки нам не нужны
    curl_setopt($resource, CURLOPT_HEADER, 0);
    
    if ($options && is_array($options)) {
        foreach ($options as $key => $value) {
            curl_setopt($resource, $key, $value);
        }
    }
    
    // выполняем операцию
    curl_exec($resource);
    
    // закрываем cURL-сессию
    curl_close($resource);
    
    // закрываем файл
    fclose($dest_file);
}


function sendPostRequest($url, $post_data, $options) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
    if ($options && is_array($options)) {
        foreach ($options as $key => $value) {
            curl_setopt($ch, $key, $value);
        }
    }
    $output = curl_exec($ch);
    curl_close($ch);
    return $output;
}

function sendGetRequest($url, $ch) {
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    $output = curl_exec($ch);
    return $output;
}
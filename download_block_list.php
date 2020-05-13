<?php

require_once(__DIR__ . DIRECTORY_SEPARATOR . "CurlRequest.php");
require_once(__DIR__ . DIRECTORY_SEPARATOR . "FileOperation.php");
require_once(__DIR__ . DIRECTORY_SEPARATOR . "ConfigRosFin.php");
require_once(__DIR__ . DIRECTORY_SEPARATOR . 'phpQuery-onefile.php');

/*
 Перечень лиц, в отношении которых действует решение Комиссии о замораживании (блокировании) принадлежащих им денежных средств или иного имущества
 */

const REQUEST_CODE = '00d19daa-f42e-4634-9dae-47a1199ff70c';

$post_data = ConfigRosFin::getAuthData();

$url = "https://portal.fedsfm.ru/account/login";
$options = array (
    CURLOPT_COOKIEJAR => __DIR__ . DIRECTORY_SEPARATOR . 'cookie.txt'
);
$output = CurlRequest::sendPostRequest($url, $post_data, $options);

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

$directoryPath = __DIR__ . DIRECTORY_SEPARATOR . "activeMvk";

$needDownload = false;
if (!file_exists($directoryPath)) {
    $needDownload = true;
} else {
    $needDownload = zeroDownloadCount($options);
}

if (!$needDownload) {
    return;
}

$url = "https://portal.fedsfm.ru/XmlCatalogDownload/GetActiveMvk";

FileOperation::deleteDirectory($directoryPath);
$ok = mkdir($directoryPath);
if (!$ok) {
    die("Error while creating directory");
}

$filePath = $directoryPath . DIRECTORY_SEPARATOR . "activeMvk.xml";
CurlRequest::downloadFile($url, $filePath, $options);

function zeroDownloadCount($options) {
    $url = "https://portal.fedsfm.ru/CommandManager/Execute";
    $post_data = array ('KbObjectType' => 3, 'Parameters' => '[]', 'State' => 0, 'Id' => REQUEST_CODE);
    $output = CurlRequest::sendPostRequest($url, $post_data, $options);
    $obj = json_decode($output);
    $document = phpQuery::newDocument($obj->Content);
    $table = $document->find('table:eq(8)');
    $td = $table->find('tr:eq(1)')->find('td:eq(4)');
    $countStr = $td->html();   
    $count = (int) trim($countStr);
    if ($count == 0) {
        return true;
    }
    return false;
}

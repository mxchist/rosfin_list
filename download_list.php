<?php

require_once(__DIR__ . DIRECTORY_SEPARATOR . "CurlRequest.php");
require_once(__DIR__ . DIRECTORY_SEPARATOR . "FileOperation.php");
require_once(__DIR__ . DIRECTORY_SEPARATOR . "ConfigRosFin.php");

/*
 Перечень организаций и физических лиц, в отношении которых имеются сведения об их причастности к экстремистской деятельности или терроризму
 */

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

$directoryPath = __DIR__ . DIRECTORY_SEPARATOR . "activeSked";

$needDownload = false;
if (!file_exists($directoryPath)) {
    $needDownload = true;
} else {
    $needDownload = zeroDownloadCount($options);
}

if (!$needDownload) {
    return;
}

$url = "https://portal.fedsfm.ru/SkedDownload/GetActiveSked?type=dbf";
$fileName = __DIR__ . DIRECTORY_SEPARATOR . "activeSked.zip";
CurlRequest::downloadFile($url, $fileName, $options);


FileOperation::deleteDirectory($directoryPath);
FileOperation::extractZip($fileName, $directoryPath);



function zeroDownloadCount($options) {
    $url = "https://portal.fedsfm.ru/StartPage/TerroristCatalogUserDashboard";
    $post_data = array ('rowIndex' => 0, 'pageLength' => 10);
    $output = CurlRequest::sendPostRequest($url, $post_data, $options);
    $obj = json_decode($output);
    $data = $obj->data;
    if ($data != null && is_array($data) && count($data) > 0) {
        $downloadCount = (int) $data[0]->DownloadCount;
        if ($downloadCount == 0) {
            return true;
        }
    }
    return false;
}


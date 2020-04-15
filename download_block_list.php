<?php

require_once(__DIR__ . DIRECTORY_SEPARATOR . "CurlRequest.php");
require_once(__DIR__ . DIRECTORY_SEPARATOR . "FileOperation.php");
require_once(__DIR__ . DIRECTORY_SEPARATOR . "ConfigRosFin.php");

/*
 Перечень лиц, в отношении которых действует решение Комиссии о замораживании (блокировании) принадлежащих им денежных средств или иного имущества
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

$url = "https://portal.fedsfm.ru/XmlCatalogDownload/GetActiveMvk";

$directoryPath = __DIR__ . DIRECTORY_SEPARATOR . "activeMvk";
FileOperation::deleteDirectory($directoryPath);
$ok = mkdir($directoryPath);
if (!$ok) {
    die("Error while creating directory");
}

$filePath = $directoryPath . DIRECTORY_SEPARATOR . "activeMvk.xml";
CurlRequest::downloadFile($url, $filePath, $options);
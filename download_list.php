<?php

require_once(__DIR__ . DIRECTORY_SEPARATOR . "CurlRequest.php");
require_once(__DIR__ . DIRECTORY_SEPARATOR . "FileOperation.php");
require_once(__DIR__ . DIRECTORY_SEPARATOR . "ConfigRosFin.php");

/*
 �������� ����������� � ���������� ���, � ��������� ������� ������� �������� �� �� ������������ � �������������� ������������ ��� ����������
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

// �������� �����
$options = array (
    CURLOPT_COOKIEFILE => __DIR__ . DIRECTORY_SEPARATOR . 'cookie.txt'
);

$url = "https://portal.fedsfm.ru/SkedDownload/GetActiveSked?type=dbf";
$fileName = __DIR__ . DIRECTORY_SEPARATOR . "activeSked.zip";
CurlRequest::downloadFile($url, $fileName, $options);

$directoryPath = __DIR__ . DIRECTORY_SEPARATOR . "activeSked";
FileOperation::deleteDirectory($directoryPath);
FileOperation::extractZip($fileName, $directoryPath);


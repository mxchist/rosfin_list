<?php 

class CurlRequest {
    
    public static function downloadFile($url, $file, $options)
    {
        // ��������� ����, �� �������, �� ������
        $dest_file = fopen($file, "w");
        $resource = curl_init();
        // ������������� ����� ���������� �����
        curl_setopt($resource, CURLOPT_URL, $url);
        // ������������� ����� �� �������, ���� ����� ���������� ��������� ����
        curl_setopt($resource, CURLOPT_FILE, $dest_file);
        // ��������� ��� �� �����
        curl_setopt($resource, CURLOPT_HEADER, 0);
        if ($options && is_array($options)) {
            foreach ($options as $key => $value) {
                curl_setopt($resource, $key, $value);
            }
        }
        curl_exec($resource);
        curl_close($resource);
        // ��������� ����
        fclose($dest_file);
    }
    
    
    public static function sendPostRequest($url, $post_data, $options) {
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
    
    public static function sendGetRequest($url, $ch) {
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        $output = curl_exec($ch);
        return $output;
    }
    
}
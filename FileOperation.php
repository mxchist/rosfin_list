<?php

class FileOperation {
    
    public static function deleteDirectory(string $directoryPath) {
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
    }
    
    public static function extractZip(string $fileName, string $directoryPath) {
        $zip = new ZipArchive();
        $res = $zip->open($fileName);
        if ($res === TRUE) {
            $zip->extractTo($directoryPath);
            $zip->close("successfully unpacked");
        } else {
            die("An error occurred while opening zip file");
        }
    }
    
}
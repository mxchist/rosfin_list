<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

header("Content-Type: text/plain; charset=utf-8");

$name = $_GET["name"];


$foundName = "";
$foundDescription = "";

// Запишем в лог информацию о запросе

if(isset($_GET["user"])&&!empty($_GET["user"])){
    file_put_contents("info.log", date("Y-m-d H:i:s") . " - User: " . $_GET["user"] . ". Client: " . $name. "\r\n", FILE_APPEND | LOCK_EX);
}else{
    file_put_contents("info.log", date("Y-m-d H:i:s") . " - Client: " . $name. "\r\n", FILE_APPEND | LOCK_EX);
};

// read xml file - список комиссии по противодействию отмыванию доходов

$filePath = __DIR__ . DIRECTORY_SEPARATOR . "/activeMvk/activeMvk.xml";

$xml = simplexml_load_file($filePath);
foreach ($xml->СписокАктуальныхРешений as $element) {
    foreach ($element->Решение as $decision) {
        $subjectList = $decision->СписокСубъектов;
        foreach ($subjectList->Субъект as $subject) {
            $namesArray = array();
            $subjectName = "";
            if (isset($subject->ФЛ)) {
                $subjectName = (string) $subject->ФЛ->ФИО;
                $namesArray[] = $subjectName;
                if (isset($subject->ФЛ->ФИОЛат)) {
                    $namesArray[] = (string) $subject->ФЛ->ФИОЛат;
                }
                if (isset($subject->ФЛ->СписокДрНаименований)) {
                    foreach ($subject->ФЛ->СписокДрНаименований->ДрНаименование as $otherName) {
                        $namesArray[] = (string) $otherName->ФИО;
                    }
                }
            } else if (isset($subject->ЮЛ)) {
                $subjectName = (string) $subject->ЮЛ->Наименование;
                $namesArray[] = $subjectName;
                if (isset($subject->ЮЛ->НаименованиеЛат)) {
                    $namesArray[] = (string) $subject->ЮЛ->НаименованиеЛат;
                }
            }
            foreach ($namesArray as $nameu) {
                if (mb_strripos($nameu, $name, 0, "utf-8") !== false) {
                    $foundName = $nameu;
                    $foundDescription = $subject->РешениеПоСубъекту;
                }
            }
        }
    }
}

// end read xml file

// read xml file - список комиссии лиц и организаций, причастных к ОМП

if ($foundName == "") {
    $filePath = __DIR__ . DIRECTORY_SEPARATOR . "/activeOmu/activeOmu.xml";
    
    $xml = simplexml_load_file($filePath);
    foreach ($xml->АктуальныйСписок as $element) {
        foreach ($element->Субъект as $subject) {
                $namesArray = array();
                $subjectName = "";
                if (isset($subject->ФЛ)) {
                    $subjectName = (string) $subject->ФЛ->ФИО;
                    $namesArray[] = $subjectName;
                    if (isset($subject->ФЛ->ФИОЛат)) {
                        $namesArray[] = (string) $subject->ФЛ->ФИОЛат;
                    }
                    if (isset($subject->ФЛ->СписокДрНаименований)) {
                        foreach ($subject->ФЛ->СписокДрНаименований->ДрНаименование as $otherName) {
                            $namesArray[] = (string) $otherName->ФИО;
                        }
                    }
                } else if (isset($subject->ЮЛ)) {
                    $subjectName = (string) $subject->ЮЛ->Наименование;
                    $namesArray[] = $subjectName;
                    if (isset($subject->ЮЛ->НаименованиеЛат)) {
                        $namesArray[] = (string) $subject->ЮЛ->НаименованиеЛат;
                    }
                }
                foreach ($namesArray as $nameu) {
                    if (mb_strripos($nameu, $name, 0, "utf-8") !== false) {
                        $foundName = $nameu;
                        $foundDescription = $subject->Примечание;
                    }
                }
            }
        
    }
}

// end read xml file

// Откроем DBF файл - список экстремистов
if ($foundName == "") {

    $directoryPath = __DIR__ . DIRECTORY_SEPARATOR . "activeSked";
    $filePath = "";
    if (is_dir($directoryPath)) {
        $cfiles = count(array_diff(scandir($directoryPath), [".", ".."]));
        if ($cfiles > 1) {
            die("Error: the directory must contain one file, contains several");
        }
        foreach(scandir($directoryPath) as $p) {
            if (!is_dir($p)) {
                $filePath = $directoryPath . DIRECTORY_SEPARATOR . $p;
            }
        }
    } else {
        die("Error: not found directory with file");
    }
    
    $db = dbase_open($filePath, 0);
    
    if (!$db) {
        echo "error read dbf";
        return;
    }
    
    // Чтение данных
    
    //echo $name."\r\n";
    
    $record_numbers = dbase_numrecords($db);
    $foundName = "";
    $foundDescription = "";
    
    for ($i = 1; $i <= $record_numbers; $i++) {
        $row = dbase_get_record_with_names($db, $i);
        $nameu = iconv("cp866", "utf-8", ltrim(rtrim($row["NAMEU"])));
        //$nameu = "";
        //$name  = "";  
        if (mb_strripos($nameu, $name, 0, "utf-8") === false) {
            //echo $name."\r\n";
        }
        else
        {   
            $foundName = $nameu;
            $foundDescription = iconv("cp866", "utf-8", ltrim(rtrim($row["DESCRIPT"])));
            /*
             for ($j = $i + 1; $j <= $record_numbers; $j++) {
             $row = dbase_get_record_with_names($db, $j);
             if ($row["TU"] == 0) {
             $foundDescription = $foundDescription . iconv("cp866", "utf-8", ltrim(rtrim($row["DESCRIPT"])));
             } else {
             break;
             }
             }
             */  
            break;
        }
    }
    // Закроем DBF файл
    dbase_close($db);

}

$foundName = str_replace("\"", "", $foundName);
$foundName = str_replace("'", "", $foundName);
$foundDescription = str_replace("\"", "", $foundDescription);
$foundDescription = str_replace("'", "", $foundDescription);
$result = ["found" => false, "name" => "", "description" => ""];
if ($foundName <> "") {
    $result = ["found" => true, "name" => $foundName, "description" => $foundDescription];
}

echo json_encode($result, JSON_UNESCAPED_UNICODE);

/*
if ($foundName == "") {
    echo '{"found": false, "name": "", "description": ""}';
} else {
    echo '{"found": true, "name": "'.$foundName.'", "description": "'.$foundDescription.'"}';
}
*/

// Отключаем проверку на ошибки
ini_set('display_errors','Off');
        
        

      
        ?>

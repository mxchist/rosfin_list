<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

header("Content-Type: text/plain; charset=utf-8");

$name = $_GET["name"];

// Запишем в лог файл информацию о запросе

if(isset($_GET["user"])&&!empty($_GET["user"])){
	file_put_contents("info.log", date("Y-m-d H:i:s") . " - User: " . $_GET["user"] . ". Client: " . $name. "\r\n", FILE_APPEND | LOCK_EX);
}else{
	file_put_contents("info.log", date("Y-m-d H:i:s") . " - Client: " . $name. "\r\n", FILE_APPEND | LOCK_EX);
};

// Откроем DBF файл

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



if ($foundName == "") 
  echo '{"found": false, "name": "", "description": ""}';
else
  echo '{"found": true, "name": "'.$foundName.'", "description": "'.$foundDescription.'"}';

// Отключаем проверку на ошибки
ini_set('display_errors','Off');

?>

<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
//--- Для распаковки zip файла на хостинге
if(empty($_GET['fz'])) die ("invalid params");
//---
$fz = $_GET['fz'];
if(!file_exists($fz)) die ("invalid file");
//---
$zip = new ZipArchive();
// open archive
if($zip->open($fz) !== TRUE) die ("Could not open archive");
//--- extract contents to destination directory
$zip->extractTo('./');
//--- close archive
$zip->close();
//--- print success message
echo 'OK';
?>
<?php
//---
if(!isset($_GET['url']) || empty($_GET['url']))
   {
   header("HTTP/1.0 404 Not Found");
   exit;
   }
//---
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
//---
$url=trim($_GET['url']);
if(substr($url, 0, 7) != 'http://' && substr($url, 0, 8) != 'https://') $url='http://' . $url;
readfile($url);
?>
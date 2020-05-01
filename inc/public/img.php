<?php
//+------------------------------------------------------------------+
//|                  Copyright c 2013, RedButton Web Sites Generator |
//|                                      http://www.getredbutton.com |
//+------------------------------------------------------------------+
if(!isset($_REQUEST['image'])) exit;
$image = $_REQUEST['image'];
//---
[IMAGES]
//---
if(!isset($images[$image])) exit;
//---
$im  = $images[$image];
$pos = strrpos($im, '.');
//---
if($pos === FALSE) exit;
//---
$ext = strtolower(substr($im, $pos));
header("Content-type: image/" . $ext);
header('Accept-Ranges: bytes');
header('Cache-Control: public');
//---
$options     = array('http' => array('method' => "GET",
                                     'header' => "Accept-language: en,ru\r\n" . "User-Agent: " . ($_SERVER['HTTP_USER_AGENT']) . "\r\n"));
$context = stream_context_create($options);
echo file_get_contents($im, false, $context);
exit;
?>
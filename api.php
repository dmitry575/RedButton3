<?php
//+------------------------------------------------------------------+
//|                  Copyright c 2012, RedButton Web Sites Generator |
//|                                      http://www.getredbutton.com |
//+------------------------------------------------------------------+
ini_set('display_errors', 1);
error_reporting(E_ALL);
ignore_user_abort(1); // Игнорировать обрыв связи с браузером
set_time_limit(0); // Время работы скрипта неограниченно
ini_set('max_execution_time', 0);
ini_set('set_time_limit', 0);
ini_set('implicit_flush', 1);
ini_set('output_buffering', 0);
ini_set('gd.jpeg_ignore_warning', 1);
$memory_limit = (int)ini_get("memory_limit");
if($memory_limit < 256)
  {
  $memory_limit = ini_set('memory_limit', '512M');
  }
header('Content-Type:text/html; charset=UTF-8');
//---
include_once('inc/lib/controller.php');
include_once('inc/lib/logs.php');
//---
CLogger::Init("", true, './data/tmp/logs', '');
$apiModel = new CModel_api();
//--- проверим токен
$token = isset($_GET['token']) ? $_GET['token'] : null;
if(empty($token) || $token != $apiModel->GetToken())
  {
  print 'invalid token';
  exit;
  }
//--- получим action
$action = isset($_GET['action']) ? $_GET['action'] : null;
//---
switch($action)
{
  //--- парсинг текста
  case 'textparser':
    echo $apiModel->TextParserByUrl($_GET['url']);
    break;
  //--- случайные строки
  case 'randline':
    echo $apiModel->GetRandline($_GET);
    break;
  //--- случайные ключи
  case 'randkeywords':
    echo $apiModel->GetRandKeywords($_GET);
    break;
  case 'textgenerate':
    //--- размер памяти
    if($memory_limit < 256)
      {
      $res = ini_set('memory_limit', '512M');
      CLogger::write(CLoggerType::DEBUG, "set memory limit " . ini_get("memory_limit") . ' old value: ' . $res);
      }
    else
      {
      CLogger::write(CLoggerType::DEBUG, "memory limit " . $memory_limit);
      }
    echo $apiModel->TextGenerate($_GET);
    CLogger::write(CLoggerType::DEBUG, "text generated ");
    break;
}
?>
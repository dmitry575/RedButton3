<?php
//+------------------------------------------------------------------+
//|                  Copyright c 2012, RedButton Web Sites Generator |
//|                                      http://www.getredbutton.com |
//+------------------------------------------------------------------+
ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type:text/html; charset=UTF-8');
//---
$VERSION_FULL = "build 3.92 22 November 2015";
$BUILD        = "92";
//--- означает релизная версия
$IS_CRYPT = 1;
//---
include_once('inc/lib/controller.php');
include_once('inc/lib/logs.php');
include_once('inc/languages.php');
include_once('inc/lib/icontroller.php');
//---
include_once('inc/lib/ipage.php');
include_once('inc/config.php');
//--- получение языка
$LNG = CModel_lng::GetLanguage();
//---
include_once('inc/lib/iplugin.php');
//---
if(file_exists('data/plugins_config.php')) include_once('data/plugins_config.php');
//---
include_once('auth.php');
//---
//--- иницилизация лога
CLogger::Init("", true, './data/tmp/logs', '');

//--- название класса для базовой страницы
$PAGE_NAME = '';
session_start();
//---
if(isset($_REQUEST['module'])) $PAGE_NAME = $_REQUEST['module'];
//---
if($PAGE_NAME == '') $PAGE_NAME = 'home';
//---
$PAGE_CLASS_NAME = "C" . $PAGE_NAME;
if(!class_exists($PAGE_CLASS_NAME))
  {
  //--- класса не существует, до свидания
  C404::Show404();
  exit;
  }

$CUR_PAGE = new $PAGE_CLASS_NAME();
//---

if(isset($_REQUEST['a']) && is_array($_REQUEST['a']))
  {
  $CUR_PAGE->action(null, key($_REQUEST['a']));
  }
else
  {
  $template = $CUR_PAGE->GetTemplate();
  //---
  if($template != '') include('./inc/views/' . $template . '.phtml');
  else
  $CUR_PAGE->Show(null);
  }
?>
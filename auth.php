<?php
global $LNG;
if(empty($LNG)) $LNG = 'en';
//---
$trans = & $TRANSLATE[$LNG];
//--- аутентификация
$filePassword = file_exists('data/pass.data.php') ? file_get_contents('data/pass.data.php') : null;
$cookie = isset($_COOKIE['pass']) ? $_COOKIE['pass'] : null;
//--- обработка выхода
if(isset($_GET['logout']))
  {
  setcookie('pass', '', time() - 3600);
  header('location: ./');
  }
//--- обработка POST-запроса
if(isset($_POST['pass']))
  {
  $newPassword = substr(md5($_POST['pass'] . 'sk3LKSJA#@2'), 0, 10);
  $cookie      = $newPassword;
  //---
  setcookie('pass', $newPassword);
  //---
  if(empty($filePassword))
    {
    if(!file_exists('data')) mkdir('data');
    file_put_contents('data/pass.data.php', $newPassword);
    $filePassword = $newPassword;
    }
  }
//--- проверка пароля
if (empty($cookie) || empty($filePassword) || $cookie != $filePassword)
{
//--- если введен неправильный пароль, делаем паузу для защиты от брутфорса
if(!empty($cookie) && $cookie != $filePassword) sleep(3);
//--- запрещаем кэшировать эту страницу
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Tue, 16 Jul 1985 16:20:00 GMT");
//---
?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head><title></title>
  <meta http-equiv="content-type" content="text/html; charset=utf-8">
  <meta http-equiv="Cache-control" content="no-cache">
  <meta name="robots" content="noindex">
</head>
<body>
<link href="styles/styles.css" type="text/css" rel="stylesheet">
<form action='' method='post' class='login'>
  <?=empty($filePassword) ? $trans['make_password'] : $trans['enter_password'] ?>:
  <input class='pass' type='password' name='pass' id='pass'>
  <input class='submit' type='submit' value='<?= $trans['enter'] ?>'>
</form>
<?

if(!in_array('mbstring', get_loaded_extensions()))
  {
?>
<div class="result error" style="margin: 10px;">
<?=$trans['php_mbstring']?>
</div>
<?  }
?>
<script type="text/javascript">
  window.onload=function ()
    {
    document.getElementById('pass').focus();
    }
</script>
</body><?
//---
die;
}
//--- isPostback
if(isset($_POST['pass']))
  {
  function CheckServer($check, $message)
    {
    if(!$check) $_SESSION['check_servers'] .= '<div>' . $message . '</div>';
    }

  //---
  if(session_id() == '') session_start();
  $_SESSION['check_servers'] = '';
  //--- проверки на наличие нужных модулей и проверки прав на папки
  //--- вресия пхп
  CheckServer(version_compare(phpversion(), '5.2.0', '>='), $trans['php_version']);
  //--- открытие урлов
  CheckServer(ini_get('allow_url_fopen'), $trans['php_allow_url_fopen']);
  //--- mbstring
  CheckServer(in_array('mbstring', get_loaded_extensions()), $trans['php_mbstring']);
  //--- curl
  CheckServer(in_array('curl', get_loaded_extensions()), $trans['php_curl']);
  //---
  $is_write_data = is_writable('./data');
  CheckServer($is_write_data, $trans['php_data_write']);
  //---
  if($is_write_data && !file_exists('./data/links')) mkdir('./data/links', 0777);
  CheckServer(is_writable('./data/links'), $trans['php_links_write']);
  if($is_write_data && !file_exists('./data/links/music')) mkdir('./data/links/music', 0777);
  if($is_write_data && !file_exists('./data/links/dating')) mkdir('./data/links/dating', 0777);
  if($is_write_data && !file_exists('./data/links/pharma')) mkdir('./data/links/pharma', 0777);
  //---
  if($is_write_data && !file_exists('./data/images')) mkdir('./data/images', 0777);
  CheckServer(is_writable('./data/images'), $trans['php_images_write']);
  //---
  if($is_write_data && !file_exists('./data/settings')) mkdir('./data/settings', 0777);
  CheckServer(is_writable('./data/settings'), $trans['php_settings_write']);
  //---
  if($is_write_data && !file_exists('./data/settings/api')) mkdir('./data/settings/api', 0777);
  CheckServer(is_writable('./data/settings/api'), $trans['php_settings_write']);
  //---  
  if($is_write_data && !file_exists('./data/tasks')) mkdir('./data/tasks', 0777);
  CheckServer(is_writable('./data/tasks'), $trans['php_tasks_write']);
  //---
  if(!file_exists('./licenses')) mkdir('./licenses', 0777);
  CheckServer(is_writable('./licenses'), $trans['php_licenses_write']);
  //---
  header("location: ./");
  }
?>
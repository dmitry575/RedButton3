<?php
mb_internal_encoding("UTF-8");
setlocale(LC_CTYPE, 'C');
/**
 * Обработчик ошибок
 * @param int $errno уровень ошибки
 * @param string $errstr сообщение об ошибке
 * @param string $errfile имя файла, в котором произошла ошибка
 * @param int $errline номер строки, в которой произошла ошибка
 * @return boolean
 */
/*function error_handler($errno, $errstr, $errfile, $errline)
   {
   // если ошибка попадает в отчет (при использовании оператора "@" error_reporting() вернет 0)
   $errors = array(
      E_ERROR => 'E_ERROR',
      E_WARNING => 'E_WARNING',
      E_PARSE => 'E_PARSE',
      E_NOTICE => 'E_NOTICE',
      E_CORE_ERROR => 'E_CORE_ERROR',
      E_CORE_WARNING => 'E_CORE_WARNING',
      E_COMPILE_ERROR => 'E_COMPILE_ERROR',
      E_COMPILE_WARNING => 'E_COMPILE_WARNING',
      E_USER_ERROR => 'E_USER_ERROR',
      E_USER_WARNING => 'E_USER_WARNING',
      E_USER_NOTICE => 'E_USER_NOTICE',
      E_STRICT => 'E_STRICT',
      E_RECOVERABLE_ERROR => 'E_RECOVERABLE_ERROR',
   );
   //---
   if($errors[$errno] == 'E_STRICT') return true;
   //---
   CLogger::write(CLoggerType::ERROR, "php: " . $errors[$errno] . ' [' . $errno . '] ' . $errstr . ' (' . $errfile . ' on line ' . $errline . ')');
   // не запускаем внутренний обработчик ошибок PHP
   return true;
   }
//--- перехват ошибок
set_error_handler("error_handler");
*/
//---
function __autoload($class_name)
  {
  global $IS_CRYPT;
  //---
  $class_name = str_replace(array('.',
                                  '/',
                                  '\\'), '', $class_name);
  $class_name = strtolower($class_name);
  if($class_name == 'cmodel_generator') ;
    {
    //if($IS_CRYPT) return;
    }
  if($class_name == 'angrycurl') include_once('inc/lib/osrc/curl/AngryCurl.php');
  elseif($class_name == 'rollingcurl') include_once('inc/lib/osrc/curl/RollingCurl.php');
  //----
  if(strpos($class_name, '_') !== false)
    {
    list($type, $tmp) = explode('_', $class_name, 2);
    //---
    switch($type)
    {
      case 'cmodel':
      case 'ctools':
        $inc_path = 'inc/lib/';
        break;
      case 'cplugin':
        $inc_path = 'inc/plugins/';
        break;
      default:
        $inc_path = 'inc/pages/';
    }
    }
  else $inc_path = 'inc/pages/';
  //---
  if(!empty($type) && $type == 'cplugin')
    {
    //--- вырежим CPlugin_
    $file_name = str_replace('_', '/', substr($class_name, 8));
    }
  else
    {
    $file_name = str_replace('_', '/', substr($class_name, 1));
    }
  //---
  if(strpos($file_name, '/') === false) $file_name .= '/_controller';
  $file_name .= '.php';
  //---
  if(file_exists($inc_path . $file_name)) include_once($inc_path . $file_name);
  }

?>
<?php
//+------------------------------------------------------------------+
//|                  Copyright c 2012, RedButton Web Sites Generator |
//|                                      http://www.getredbutton.com |
//+------------------------------------------------------------------+
/**
 * HTML HELPER
 */
class CModel_helper
  {
  /**
   * Конструктор
   */
  function __construct()
    {
    }

  public static function SwapLinks($inputName, $selectedId, $onClick, $links = array())
    {
    $list = array();
    $js   = '';
    if(empty($selectedId) || !array_key_exists($selectedId, $links)) $selectedId = key($links);
    //---
    $inputBox = "<input type='hidden' name='{$inputName}' id='aswap-input-{$inputName}' value='{$selectedId}'>";
    //---
    foreach($links as $key => &$value)
      {
      $js = !empty($onClick) ? "$onClick('{$key}');" : '';
      //---
      if($key != $selectedId) $list[] = "<a href=\"javascript:aswap('{$inputName}','{$key}');{$js}\" id='aswap-{$inputName}-{$key}'>{$value}</a>\n";
      else
      array_unshift($list, "<a href=\"javascript:aswap('{$inputName}','{$key}');{$js}\" id='aswap-{$inputName}-{$key}' class='selected'>{$value}</a>\n");
      }
    //---
    return "<span class='aswap' id='aswap-{$inputName}'>\n" . implode(' / ', $list) . "</span>\r\n{$inputBox}";
    }

  /**
   * Генерируем случайный символ
   *
   * @param array $matches
   * @return char
   */
  public static function randomsymbol($matches)
    {
    switch($matches[0])
    {
      case ';':
      case '(':
      case '\'':
      case ')':
      case '!':
        return '_';
        break;
    }
    return chr(rand(97, 122));
    }

  /**
   * Генерация имени файла
   *
   * @param string $filename
   */
  public static function generate_file_name($filename)
    {
    if(preg_match('/[^0-9a-z_\.\-]/sUi', $filename, $out, PREG_OFFSET_CAPTURE))
      {
      return preg_replace_callback('/[^0-9a-z_\.\-]/sUi', 'CModel_helper::randomsymbol', $filename);
      }
    return $filename;
    }

  /**
   * Генерация имени файла, с заменной не известного символа _
   *
   * @param string $filename
   */
  public static function GenerateFileName($filename)
    {
    if(preg_match('/[^0-9a-z_\.\-]/sUi', $filename, $out, PREG_OFFSET_CAPTURE))
      {
      return preg_replace('/[^0-9a-z_\.\-]/sUi', '_', $filename);
      }
    return $filename;
    }

  /**
   * Получаем список файлов в виде выпадающего списка
   * @param string $dir
   * @param string $selectedItem
   * @return string
   */
  public static function ListFiles($dir, $selectedItem, $warning_exts = array('php',
                                                                              'phtml'))
    {
    $options = '';
    if(!file_exists($dir)) return '';
    //---
    $handle = opendir($dir);
    $files  = array();
    if($handle !== false)
      {
      while(($file = readdir($handle)) !== false)
        {
        if($file == '.' || $file == '..' || !is_file($dir . '/' . $file)) continue;
        $nfile = self::generate_file_name($file);
        //--- проверка файла
        if($nfile != $file)
          {
          rename($dir . '/' . $file, $dir . '/' . $nfile);
          CLogger::write(CLoggerType::DEBUG, 'rename file: ' . $dir . '/' . $file . ' => ' . $dir . '/' . $nfile);
          $file = $nfile;
          }
        //--- если нужно то не будем отображать файл с расширением
        if(!empty($warning_exts))
          {
          $ext = pathinfo($file, PATHINFO_EXTENSION);
          if(in_array($ext, $warning_exts)) continue;
          }
        //---
        $files[] = $file;
        }
      closedir($handle);
      }
//---
    natsort($files);
    foreach($files as $file)
      {
      //---
      $isSelected = $file == $selectedItem ? "selected='selected'" : NULL;
        $size= filesize($dir.'/'.$file);
      $options .= "<option value={$file} {$isSelected}>{$file} (".number_format($size/1024,0,'',' ')." Kb)</option>\n";
      }
    //---
    return $options;
    }

  /**
   * Получаем список файлов в виде выпадающего списка
   * @param string $dir
   * @return array
   */
  public static function ListFilesArray($dir, $warning_exts = array('php',
                                                                    'phtml'))
    {
    $result = array();
    //---
    if(!file_exists($dir)) return null;
    $handle = opendir($dir);
    if($handle !== false)
      {
      while(($file = readdir($handle)) !== false)
        {
        if($file == '.' || $file == '..' || !is_file($dir . '/' . $file)) continue;
        $nfile = self::generate_file_name($file);
        //--- проверка файла
        if($nfile != $file)
          {
          rename($dir . '/' . $file, $dir . '/' . $nfile);
          CLogger::write(CLoggerType::DEBUG, 'rename file: ' . $dir . '/' . $file . ' => ' . $dir . '/' . $nfile);
          $file = $nfile;
          }
        //--- если нужно то не будем отображать файл с расширением
        if(!empty($warning_exts))
          {
          $ext = pathinfo($file, PATHINFO_EXTENSION);
          if(in_array($ext, $warning_exts)) continue;
          }
        //---
        $result[] = $file;
        }
      closedir($handle);
      }
    //---
    return $result;
    }

  /**
   * Получение подкатегории
   * @param $dir
   * @param $selectedItem
   * @param array $files
   * @return string
   */
  public static function ListDirsSubDirs($dir, $selectedItem, $files = array())
    {
    $options = '';
    //---
    $dirs = self::ListArraySubDirs($dir, '', $selectedItem, $files, 0);
    if(!is_array($dirs))
      return;
    foreach($dirs as $d)
      {
      if($d['count'] > -1)
        {
        $options .= '<option label="' . $d['name'] . '" class="main-select"><i>' . $d['title'] . '</i></option>' . "\n";
        foreach($d['child'] as $child)
          {
          $options .= "<option value='" . $child['name'] . "'" . ($child['selected'] ? " selected='selected'" : NULL) . " class='sub-select'>" . $child['title'] . "</option>\n";
          }
        }
      else  $options .= "<option value='" . $d['name'] . "'" . ($d['selected'] ? " selected='selected'" : NULL) . ">" . $d['title'] . "</option>\n";
      }
    /*if(!file_exists($dir)) return '';
    $handle = opendir($dir);
    if($handle !== false)
      {
      while(($subDir = readdir($handle)) !== false)
        {
        if($subDir == '.' || $subDir == '..' || $subDir[0] == '.' || !is_dir($dir . '/' . $subDir)) continue;
        //---
        $isSelected = $subDir == $selectedItem ? "selected='selected'" : NULL;
        //---
        $counts_sub = -1;
        if(!empty($files))
          {
          $exists = false;
          foreach($files as $fl)
            {
            if(file_exists($dir . '/' . $subDir . '/' . $fl))
              {
              $exists = true;
              break;
              }
            }
//--- нет нужно файла
          if(!$exists)
            {
//--- подсчитаем только те папки, которые содержат нужные файлы
            $counts_sub = self::GetCountDirs($dir . '/' . $subDir, $files);
            }
          }
        if($counts_sub > -1)
          {
          $options .= '<option label="' . $subDir . '" class="main-select"><i>' . $subDir . '</i></option>' . "\n";
          $options .= self::ListSubDirs($dir . '/' . $subDir, $subDir, $selectedItem, 'sub-select');
          }
        else  $options .= "<option value={$subDir} {$isSelected}>{$subDir}</option>\n";
        }
      closedir($handle);
      }
    */
    //---
    return $options;
    }

  /**
   * Получаем количество подпапок, в которых есть нужные файлы
   * @param string $dir
   * @param string $selectedItem
   * @return string
   */
  private static function GetCountDirs($dir, $files)
    {
    $count = 0;
    //---
    if(!file_exists($dir)) return $count;
    $handle = opendir($dir);
    if($handle !== false)
      {
      while(($subDir = readdir($handle)) !== false)
        {
        if($subDir == '.' || $subDir == '..' || $subDir[0] == '.' || !is_dir($dir . '/' . $subDir)) continue;
        //---
        if(!empty($files))
          {
          $exists = false;
          foreach($files as $fl)
            {
            if(file_exists($dir . '/' . $subDir . '/' . $fl))
              {
              $exists = true;
              break;
              }
            }
          if($exists) $count++;
          }
        else $count++;
        }
      closedir($handle);
      }
    //---
    return $count;
    }

  /**
   * Получение подкатегории
   * @param $dir
   * @param $selectedItem
   * @param array $files
   * @return string
   */
  public static function ListArraySubDirs($dir, $main_dir, $selectedItem, $files = array(), $level = 0)
    {
    $ret = array();
    //---
    if(!file_exists($dir)) return '';
    $handle = opendir($dir);
    $i      = 0;
    if($handle !== false)
      {
      while(($subDir = readdir($handle)) !== false)
        {
        if($subDir == '.' || $subDir == '..' || $subDir[0] == '.' || !is_dir($dir . '/' . $subDir)) continue;
        //---
        $isSelected = (!empty($main_dir) ? $main_dir . '/' : '') . $subDir == $selectedItem;
        //---
        $counts_sub = -1;
        //--- только для нулевого уровня нужно лезть глубже
        if($level < 1)
          {
          if(!empty($files))
            {
            $exists = false;
            foreach($files as $fl)
              {
              if(file_exists($dir . '/' . $subDir . '/' . $fl))
                {
                $exists = true;
                break;
                }
              }
//--- нет нужно файла
            if(!$exists)
              {
//--- подсчитаем только те папки, которые содержат нужные файлы
              $counts_sub = self::GetCountDirs($dir . '/' . $subDir, $files);
              }
            }
          }
        if($counts_sub > -1)
          {
          $ret[$i] = array('name'     => (!empty($main_dir) ? $main_dir . '/' : '') . $subDir,
                           'title'    => $subDir,
                           'count'    => $counts_sub,
                           'selected' => $isSelected);
          $sub     = self::ListArraySubDirs($dir . '/' . $subDir, $subDir, $selectedItem, $files, $level + 1);
          //---
          if(!empty($sub))
            {
            uasort($sub, array('self',
                               'compare_subdir'));
            }
          //---
          $ret[$i]['child'] = $sub;
          }
        else  $ret[$i] = array('name'     => (!empty($main_dir) ? $main_dir . '/' : '') . $subDir,
                               'title'    => $subDir,
                               'count'    => $counts_sub,
                               'selected' => $isSelected);
        $i++;
        }
      closedir($handle);
      }
    uasort($ret, array('self',
                       'compare_maindir'));
    //---
    return $ret;
    }

  /**
   * Сравнение подпапок, только поимени
   * @param $a
   * @param $b
   * @return int
   */
  private static function compare_subdir($a, $b)
    {
    if($a['title'] == $b['title']) return 0;
    return strnatcasecmp($a['title'], $b['title']);
    }

  /**
   * Сравнение главнй подпапок
   * @param $a
   * @param $b
   * @return int
   */
  private static function compare_maindir($a, $b)
    {
    if($a['count'] == $b['count']) return strnatcasecmp($a['title'], $b['title']);
    if($a['count'] == -1 && $b['count'] != -1) return 1;
    if($a['count'] != -1 && $b['count'] == -1) return -1;
    //---
    return strnatcmp($a['title'], $b['title']);
    }

  /**
   * Получаем список директорий в виде выпадающего списка
   * @param string $dir
   * @param string $selectedItem
   * @return string
   */
  public static function ListDirs($dir, $selectedItem)
    {
    $options = '';
    if(!file_exists($dir)) return '';
    //---
    $handle     = opendir($dir);
    $directoies = array();
    if($handle !== false)
      {
      while(($subDir = readdir($handle)) !== false)
        {
        if($subDir == '.' || $subDir == '..' || $subDir[0] == '.' || !is_dir($dir . '/' . $subDir)) continue;
        //---
        $directoies[] = $subDir;
        }
      closedir($handle);
      }
    //--- сотрировка
    natsort($directoies);
    foreach($directoies as $subDir)
      {
      //---
      $isSelected = $subDir == $selectedItem ? "selected='selected'" : NULL;
      $options .= "<option value={$subDir} {$isSelected}>{$subDir}</option>\n";
      }
    //---
    return $options;
    }

  /**
   * Получаем список директорий в виде выпадающего списка
   * @param string $dir
   * @param string $selectedItem
   * @return string
   */
  public static function ListSubDirs($dir, $main_name, $selectedItem, $css_class = '')
    {
    $options = '';
    //---
    if(!file_exists($dir)) return '';
    $handle = opendir($dir);
    if($handle !== false)
      {
      while(($subDir = readdir($handle)) !== false)
        {
        if($subDir == '.' || $subDir == '..' || $subDir[0] == '.' || !is_dir($dir . '/' . $subDir)) continue;
        //---
        $d          = $main_name . "/" . $subDir;
        $isSelected = $d == $selectedItem ? "selected='selected'" : NULL;
        //---
        $options .= "<option value=" . $d . " {$isSelected}" . (!empty($css_class) ? ' class="' . $css_class . '"' : '') . ">{$subDir}</option>\n";
        }
      closedir($handle);
      }
    //---
    return $options;
    }

  /**
   *
   * Получаем название первой попавшейся папки из директории
   * @param string $dir
   */
  public static function FirstFromDirs($dir)
    {
    $options = '';
    //---
    if(!file_exists($dir)) return '';
    $handle = opendir($dir);
    if($handle !== false)
      {
      while(($subDir = readdir($handle)) !== false)
        {
        if($subDir == '.' || $subDir == '..' || !is_dir($dir . '/' . $subDir)) continue;
        $options = $subDir;
        break;
        }
      closedir($handle);
      }
    //---
    return $options;
    }

  /**
   * Выводит информационное сообщение в момент генерации
   * @param string $text Message text
   * @param bool $isNeedFlush Is need to flush output
   */
  public static function PrintInfo($text, $isNeedFlush = false)
    {
    echo '<i>', $text, '</i>', "<br>\n";
    if($isNeedFlush)
      {
      flush();
      @ob_flush();
      }
    }

  /**
   * Выводит мета-заголовки и стили для страницы генерации
   */
  public static function PrintStartHeader()
    {
    global $VERSION_FULL;
    echo
    '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">', '<html>', '<head>', '<meta http-equiv="Content-Type" content="text/html; charset=utf-8">', '<meta name="robots" content="noindex">', '<link rel="shortcut icon" href="/favicon.ico">', '<style type="text/css">', 'body {margin: 10px; font-family:sans-serif,arial,tahoma,verdana; font-size:0.95em;} ', 'i {font-style: normal; margin: 0 -5px; padding: 3px 5px; line-height: 30px; background-color: #DFD9C3; border: 0px solid #DFD9C3; }', '</style>', '<title>redButton ', $VERSION_FULL, ' &mdash; Start</title>', '</head>', '<body>';
    }

  /**
   * удаление плохих UTF-8 символов
   * @param $str
   * @return string
   */
  public static function StripBadUTF8($str)
    { // (C) SiMM, based on ru.wikipedia.org/wiki/Unicode
    $ret = '';
    for($i = 0; $i < strlen($str);)
      {
      $tmp = $str{$i++};
      $ch  = ord($tmp);
      if($ch > 0x7F)
        {
        if($ch < 0xC0) continue;
        elseif($ch < 0xE0) $di = 1;
        elseif($ch < 0xF0) $di = 2;
        elseif($ch < 0xF8) $di = 3;
        elseif($ch < 0xFC) $di = 4;
        elseif($ch < 0xFE) $di = 5;
        else continue;
        for($j = 0; $j < $di; $j++)
          {
          $tmp .= $ch = $str{$i + $j};
          $ch = ord($ch);
          if($ch < 0x80 || $ch > 0xBF) continue 2;
          }
        $i += $di;
        }
      $ret .= $tmp;
      }
    return $ret;
    }

  /**
   * Выводит мета-заголовки и стили для страницы генерации
   */
  public static function PrintEndHeader()
    {
    print '</body></html>';
    }

  /**
   *
   * Проверка http или https
   * @param string $url
   */
  public static function IsExistHttp($url)
    {
    if(strlen($url)<7) return false;
    return substr($url, 0, 7) == 'http://' || substr($url, 0, 8) == 'https://';
    }

  /**
   *
   * Удаление http или https
   * @param string $url
   */
  public static function DeleteHttp($url)
    {
    if(substr($url, 0, 7) == 'http://') return substr($url, 7);
    if(substr($url, 0, 8) == 'https://') return substr($url, 8);
    if(substr($url, 0, 6) == 'ftp://') return substr($url, 6);
    //---
    return $url;
    }

  /**
   * Получаем слово, которое начинается с заглавной буквы
   * @param string $text
   */
  public static function GetUcFirst($text)
    {
    if(empty($text)) return null;
    //---
    $array    = explode(' ', $text);
    $array[0] = self::GetUcWords($array[0]);
    //---
    return implode(' ', $array);
    }

  /**
   * Получаем кейворд, в котором все слова с заглавной буквы
   * @param string $text
   */
  public static function GetUcWords($text)
    {
    if(empty($text)) return null;
    //---
    return mb_convert_case($text, MB_CASE_TITLE, "UTF-8");
    }

  /**
   * Получение user agent
   * @return string
   */
  public static function GetUserAgent()
    {
    return 'Mozilla/5.0 (Windows NT 5.1; rv:21.0) Gecko/20100101 Firefox/21.0';
    }
  }

?>
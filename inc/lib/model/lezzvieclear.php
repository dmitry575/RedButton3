<?php
//+------------------------------------------------------------------+
//|                  Copyright c 2012, RedButton Web Sites Generator |
//|                                      http://www.getredbutton.com |
//+------------------------------------------------------------------+
/**
 *
 * Класс для выбора текста из html
 * Уникальный алгоритм от Саши
 * @author User
 *
 */
class CModel_LezzvieClear
  {
  public function __construct()
    {
    }

  /**
   * Зачистка текста
   * @param string $text
   * @return string
   */
  public function ClearText($text)
    {
    //---
    $text = preg_replace("/[\\r\\n|\\n]/i", ' ', $text);
    $text = preg_replace("/<\s*\/\s*br\s*>/i", ' ', $text);
    $text = preg_replace("/<\s*br\s*\/\s*>/i", ' ', $text);
    $text = str_replace('<br>', ' ', $text);
    //---
    $text = preg_replace("/<font [^<>]*>/i", '', $text);
    $text = str_replace('</font>', '', $text);
    //---
    $text = preg_replace("/<a\s[^<>]*>/i", '', $text);
    $text = str_replace('</a>', '', $text);
    //---
    $text = preg_replace("/<img[^<>]*>/i", '', $text);
    //---
    $text = preg_replace("/<script[^<>]*>/i", '', $text);
    //---
    $text = str_replace('<p>', '', $text);
    $text = str_replace('</p>', '', $text);
    //---
    $text = str_replace('<b>', '', $text);
    $text = str_replace('</b>', '', $text);
    //---
    $text = str_replace('<i>', '', $text);
    $text = str_replace('</i>', '', $text);
    //---
    $text = str_replace('<strong>', '', $text);
    $text = str_replace('</strong>', '', $text);
    //---
    $text = str_replace('<em>', '', $text);
    $text = str_replace('</em>', '', $text);
    //---
    $mas      = array();
    $newArray = array();
    preg_match_all("/([^<>=#\{\}\+\[\]\']{80,})/", $text, $mas);
    //---
    foreach($mas[1] as $key => &$value)
      {
      if((strpos($value, '//') !== FALSE) || (strpos($value, '"-') !== FALSE) || (strpos($value, '%;') !== FALSE) || (strpos($value, ');') !== FALSE) || (strpos($value, '" /') !== FALSE) || (strpos($value, '"/') !== FALSE) || (strpos($value, '&copy;') !== FALSE) || preg_match("/\"\\s*:\s*\"/i", $value) == 1 || preg_match("/\:\s*[a-z][a-z0-9 \-]{1,15}\;/i", $value) == 1 || preg_match("/[^\,\.]{200,}/", $value) == 1
      ) continue;
      //---
      $newArray[] = $value;
      }
    //---
    unset($mas);
    //---
    if(sizeof($newArray) > 0)
      {
      //--- сортируем элементы массива по длине строк
      usort($newArray, array($this,
                             'NewArrayComparer'));
      $r        = '';
      $firstLen = strlen($newArray[0]);
      foreach($newArray as $key => &$value)
        {
        $per = strlen($value) * 100 / $firstLen;
        if($per > 70) $r .= $value . ' ';
        }
      //---
      unset($newArray);
      return $r;
      }
    else return '';
    }

  /**
   * Функция для сортировки
   * @param string $a
   * @param string $b
   * @return int
   */
  private function NewArrayComparer($a, $b)
    {
    $len1 = strlen($a);
    $len2 = strlen($b);
    //---
    if($len1 == $len2) return 0;
    //---
    return ($len1 > $len2) ? -1 : 1;
    }
  }

?>